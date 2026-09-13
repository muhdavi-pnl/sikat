# Implementation Plan - Activity Auditing and Logging System

Implement comprehensive activity auditing and logging for all system access in the SIKAT (Sistem Informasi Kepegawaian Terintegrasi) application—covering login attempts (successful, failed, locked out), registrations, logouts, password changes, and database modifications across all models, recording full account details, IP addresses, user agents, and attribute changes (old vs new diffs).

## User Review Required

> [!IMPORTANT]
> - **Audit Retention & Performance**: Audit logs are recorded directly in the MySQL database in an `audit_logs` table and mirrored in Laravel's log files. Sensitive fields (like `password`, `remember_token`) are automatically redacted from stored JSON diffs.
> - **Permissions & Access**: The Audit Log viewer interface will be accessible to `super-admin` and `kepegawaian` roles under `/admin/audit-logs`.

## Proposed Changes

### Database Layer

#### [NEW] [create_audit_logs_table.php](file:///Users/mac/Projects/sikat/database/migrations/2026_09_13_010000_create_audit_logs_table.php)
- Create `audit_logs` migration with columns:
  - `id` (bigint auto-increment)
  - `user_id` (nullable unsignedBigInteger, indexed, foreign key to users.id with nullOnDelete)
  - `user_name` (nullable string, snapshots user name at time of event)
  - `user_email` (nullable string, snapshots user email)
  - `role` (nullable string, snapshots role name)
  - `event_type` (string, indexed: e.g. `auth.login`, `auth.login_failed`, `auth.logout`, `auth.registered`, `auth.lockout`, `auth.password_changed`, `model.created`, `model.updated`, `model.deleted`, etc.)
  - `action` (string: descriptive human-readable label)
  - `auditable_type` (nullable string, indexed for polymorphic relations)
  - `auditable_id` (nullable string/unsignedBigInteger, indexed)
  - `old_values` (nullable json: previous model state for updates/deletes)
  - `new_values` (nullable json: new model state for creates/updates)
  - `ip_address` (nullable string 45, indexed)
  - `user_agent` (nullable text)
  - `url` (nullable text)
  - `method` (nullable string 10)
  - `status` (string: `success`, `failed`, `warning`, indexed)
  - `properties` (nullable json: contextual metadata such as guard, failure reason, oauth provider)
  - `created_at` (timestamp, indexed)
  - `updated_at` (timestamp)

---

### Models & Traits

#### [NEW] [AuditLog.php](file:///Users/mac/Projects/sikat/app/Models/AuditLog.php)
- Eloquent Model for `audit_logs`.
- Relationships: `belongsTo(User::class, 'user_id')`, polymorphic `morphTo('auditable')`.
- Scopes: `forEvent()`, `forUser()`, `byIp()`, `byDateRange()`, `search()`, `byCategory()`.
- Accessors/helpers for badges, formatted dates, summary labels.

#### [NEW] [Auditable.php](file:///Users/mac/Projects/sikat/app/Models/Concerns/Auditable.php)
- Trait for Eloquent models to automatically capture `created`, `updated`, `deleted`, `restored`, and `forceDeleted` events.
- Compares original and changed attributes using `getDirty()` / `getOriginal()`.
- Redacts hidden / sensitive attributes (`password`, `remember_token`, etc.).
- Records user details, IP, URL, and diff in `audit_logs` without breaking transactions if logging fails.

#### [MODIFY] Models in `app/Models/`
- Add `use Auditable;` to key models:
  - [User.php](file:///Users/mac/Projects/sikat/app/Models/User.php)
  - [Pegawai.php](file:///Users/mac/Projects/sikat/app/Models/Pegawai.php)
  - [PegawaiIdentitas.php](file:///Users/mac/Projects/sikat/app/Models/PegawaiIdentitas.php)
  - [CareerPath.php](file:///Users/mac/Projects/sikat/app/Models/CareerPath.php)
  - [LayananPegawai.php](file:///Users/mac/Projects/sikat/app/Models/LayananPegawai.php)
  - [CutiLayananPegawai.php](file:///Users/mac/Projects/sikat/app/Models/CutiLayananPegawai.php)
  - [DokumenPegawai.php](file:///Users/mac/Projects/sikat/app/Models/DokumenPegawai.php)
  - [Dokumen.php](file:///Users/mac/Projects/sikat/app/Models/Dokumen.php)
  - [Arsip.php](file:///Users/mac/Projects/sikat/app/Models/Arsip.php)
  - [Layanan.php](file:///Users/mac/Projects/sikat/app/Models/Layanan.php)
  - [Syarat.php](file:///Users/mac/Projects/sikat/app/Models/Syarat.php)
  - [Jabatan.php](file:///Users/mac/Projects/sikat/app/Models/Jabatan.php)
  - [UnitKerja.php](file:///Users/mac/Projects/sikat/app/Models/UnitKerja.php)
  - [Pangkat.php](file:///Users/mac/Projects/sikat/app/Models/Pangkat.php)
  - [Pendidikan.php](file:///Users/mac/Projects/sikat/app/Models/Pendidikan.php)
  - [PejabatCutiSetting.php](file:///Users/mac/Projects/sikat/app/Models/PejabatCutiSetting.php)

---

### Services & Event Listeners

#### [NEW] [AuditService.php](file:///Users/mac/Projects/sikat/app/Services/AuditService.php)
- Dedicated service for logging auth events, model events, and security events.
- Handles dual-write: database record + standard Laravel structured file log (`Log::info` / `Log::warning`).
- Helper methods: `logAuth()`, `logModel()`, `logSecurity()`, `getClientIp()`, `getUserAgent()`.

#### [NEW] Authentication Listeners
- [LogSuccessfulLogin.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogSuccessfulLogin.php): Listens to `Illuminate\Auth\Events\Login`.
- [LogFailedLogin.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogFailedLogin.php): Listens to `Illuminate\Auth\Events\Failed`.
- [LogSuccessfulLogout.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogSuccessfulLogout.php): Listens to `Illuminate\Auth\Events\Logout`.
- [LogUserRegistration.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogUserRegistration.php): Listens to `Illuminate\Auth\Events\Registered`.
- [LogLockout.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogLockout.php): Listens to `Illuminate\Auth\Events\Lockout`.
- [LogPasswordReset.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogPasswordReset.php): Listens to `Illuminate\Auth\Events\PasswordReset`.

#### [MODIFY] [EventServiceProvider.php](file:///Users/mac/Projects/sikat/app/Providers/EventServiceProvider.php)
- Register the authentication listeners to their respective Laravel Auth events.

#### [MODIFY] [GoogleAuthenticatedSessionController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Auth/GoogleAuthenticatedSessionController.php)
- Add explicit audit logging for Google OAuth login failure and OAuth registration context.

#### [MODIFY] [ForcedPasswordController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Auth/ForcedPasswordController.php) & [UserController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Front/UserController.php)
- Add explicit audit logging for password updates and admin password resets.

---

### UI & Presentation

#### [NEW] [AuditLogController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Front/AuditLogController.php)
- Controller to list, filter, search, view detail, and export audit logs.
- Supports AJAX/JSON detail view modal for comparing old vs new JSON diffs.

#### [NEW] Views in `resources/views/audit-log/`
- `index.blade.php`: Rich dashboard and data table with filters (category, event type, date range, user, IP, search query) and statistics cards (total logs, successful logins, failed attempts, db modifications).
- `show.blade.php` / modal detail template: Visual diff table comparing `old_values` vs `new_values` side-by-side with color badges.

#### [MODIFY] [routes/web.php](file:///Users/mac/Projects/sikat/routes/web.php)
- Add routes for `admin/audit-logs` (`index`, `show`, `export`) restricted to `role:super-admin|kepegawaian`.

#### [MODIFY] [sidebar.blade.php](file:///Users/mac/Projects/sikat/resources/views/layouts/sidebar.blade.php)
- Add "Log Audit & Aktivitas" navigation menu item with shield/history icon under Kepegawaian / Super-Admin.

---

## Verification Plan

### Automated Tests
- Run new and existing feature tests:
  ```bash
  php artisan test tests/Feature/AuditLogFeatureTest.php
  ```
- Test cases to cover:
  1. Successful login logs user details, role, IP address, user agent, event `auth.login`.
  2. Failed login attempt logs attempted email, IP address, user agent, status `failed`, event `auth.login_failed`.
  3. Logout logs event `auth.logout` with IP and user details.
  4. User registration logs event `auth.registered` with user details and IP address.
  5. Rate-limiting lockout logs event `auth.lockout` with throttle key and IP address.
  6. Model creation (`Pegawai`, `User`, `LayananPegawai`, etc.) logs `model.created` with new attributes and actor IP.
  7. Model update logs `model.updated` with exact diff between `old_values` and `new_values`.
  8. Model deletion logs `model.deleted` with previous state.
  9. Sensitive attributes (`password`, `remember_token`) are redacted in logs.
  10. Super-admin and kepegawaian can view audit log dashboard and filter logs.
  11. Unauthorized roles (e.g. regular `pegawai`) cannot access the audit log index.

### Manual Verification
- Log in with valid credentials and observe the audit log.
- Attempt login with invalid credentials and observe failed attempt log with IP.
- Edit a Pegawai profile / record and check the recorded diff in `audit_logs`.
- Inspect the Audit Log UI at `/admin/audit-logs`.
