# Walkthrough - Activity Auditing & System Access Logging

Comprehensive activity auditing and logging has been implemented across the application to ensure system security and prevention. The system captures all system access (successful & failed logins, lockouts, registrations, logouts, password modifications) and all database model changes, recording full account details, IP addresses, user agents, request context, and old vs new attribute diffs.

---

## Key Features Implemented

### 1. Database Audit Architecture
- **Table**: `audit_logs` migration created ([2026_09_13_010000_create_audit_logs_table.php](file:///Users/mac/Projects/sikat/database/migrations/2026_09_13_010000_create_audit_logs_table.php))
- **Model**: [AuditLog.php](file:///Users/mac/Projects/sikat/app/Models/AuditLog.php)
  - Fields tracked: `user_id`, `user_name`, `user_email`, `role`, `event_type`, `action`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `url`, `method`, `status`, `properties`, and timestamps.
  - Scopes for fast query filtering by category, event type, status, user ID, IP address, and date range.

### 2. Dual-Layer Logging & Sensitive Data Redaction
- **Unified Service**: [AuditService.php](file:///Users/mac/Projects/sikat/app/Services/AuditService.php)
  - Records events into the `audit_logs` database table and mirrors them to Laravel structured application logs (`Log::info` / `Log::warning`).
  - Automatic recursive redaction of sensitive data (`password`, `current_password`, `remember_token`, `token`, `secret`, etc.).

### 3. Authentication & System Access Auditing
- **Listeners registered in [EventServiceProvider.php](file:///Users/mac/Projects/sikat/app/Providers/EventServiceProvider.php)**:
  - [LogSuccessfulLogin.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogSuccessfulLogin.php): Captures successful logins with user details, role, guard, and IP.
  - [LogFailedLogin.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogFailedLogin.php): Captures failed logins, attempted credentials/email, and IP.
  - [LogSuccessfulLogout.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogSuccessfulLogout.php): Captures logout events.
  - [LogUserRegistration.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogUserRegistration.php): Captures new user registrations.
  - [LogLockout.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogLockout.php): Captures rate-limit lockout security events.
  - [LogPasswordReset.php](file:///Users/mac/Projects/sikat/app/Listeners/Auth/LogPasswordReset.php): Captures password reset actions.
- **Custom Auth Handlers**:
  - [GoogleAuthenticatedSessionController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Auth/GoogleAuthenticatedSessionController.php): Google OAuth failures and registrations.
  - [ForcedPasswordController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Auth/ForcedPasswordController.php), [PegawaiController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Front/PegawaiController.php), & [UserController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Front/UserController.php): Password update and reset events.

### 4. Automatic Database Modification Auditing
- **Eloquent Concern Trait**: [Auditable.php](file:///Users/mac/Projects/sikat/app/Models/Concerns/Auditable.php)
  - Automatically captures `created`, `updated` (exact field diffs comparing `old_values` and `new_values`), `deleted`, and `restored` events.
  - Attached to application models: `User`, `Pegawai`, `PegawaiIdentitas`, `CareerPath`, `LayananPegawai`, `CutiLayananPegawai`, `DokumenPegawai`, `Dokumen`, `Arsip`, `Layanan`, `Syarat`, `Jabatan`, `UnitKerja`, `Pangkat`, `Pendidikan`, and `PejabatCutiSetting`.

### 5. Audit Log Viewer & Dashboard
- **Route & Controller**: [AuditLogController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Front/AuditLogController.php) under `/admin/audit-logs` restricted to `super-admin|kepegawaian`.
- **View**: [index.blade.php](file:///Users/mac/Projects/sikat/resources/views/audit-log/index.blade.php)
  - KPI summary metrics (Total Logs, Today's Logins, Failed Attempts, Database Modifications).
  - Multi-criteria filter panel (Category, Event Type, Status, User, IP Address, Date Range).
  - Server-side DataTables integration.
  - Interactive modal dialog with side-by-side Before/After diff tables highlighting modified attributes.
- **Sidebar Integration**: Added "Log Audit" navigation link in [sidebar.blade.php](file:///Users/mac/Projects/sikat/resources/views/layouts/sidebar.blade.php).

---

## Verification Results

### Automated Feature Test Suite
Executed test command:
```bash
php artisan test tests/Feature/AuditLogFeatureTest.php
```

Results:
```text
   PASS  Tests\Feature\AuditLogFeatureTest
  ✓ successful login is audited
  ✓ failed login is audited
  ✓ logout is audited
  ✓ user registration is audited
  ✓ lockout is audited
  ✓ model creation update and deletion are audited
  ✓ sensitive attributes are redacted in audit logs
  ✓ super admin and kepegawaian can access audit log dashboard
  ✓ regular pegawai cannot access audit log dashboard
  ✓ audit log detail endpoint returns json
  ✓ datatables ajax filtering works
  ✓ audit log scopes and categories

  Tests: 12 passed (29 assertions)
```

Full application test suite:
```text
  Tests: 164 passed (1196 assertions)
  Duration: 4.32s
```
