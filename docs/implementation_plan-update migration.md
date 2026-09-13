# Consolidate Migrations to Create-Table Migrations Only

Consolidate and squash all database migrations so that each migration file is dedicated solely to creating a table with its complete, final schema (including columns, constraints, and indexes). All incremental alter table, update, repair, and separate migrations will be merged into the base create table migrations and removed.

## User Review Required

> [!IMPORTANT]
> This change removes incremental alter and data-patch migration files and consolidates all schema definitions directly into table creation migrations. When re-running migrations (`php artisan migrate:fresh --seed`), the database schema will be recreated cleanly from scratch.

## Proposed Changes

### Database Migrations

#### [MODIFY] [2022_10_12_100644_create_pegawais_table.php](file:///Users/mac/Projects/sikat/database/migrations/2022_10_12_100644_create_pegawais_table.php)
- Add `$table->unsignedSmallInteger('cuti_hari_tersedia')->default(12);` directly to table definition.

#### [MODIFY] [2026_04_13_004814_create_cuti_layanan_pegawais_table.php](file:///Users/mac/Projects/sikat/database/migrations/2026_04_13_004814_create_cuti_layanan_pegawais_table.php)
- Include all fields directly: `jenis_cuti`, `alasan_cuti`, `alamat_menjalankan_cuti`, `nomor_telepon_cuti`, `tanggal_mulai`, `tanggal_selesai`, `hari_diminta`, `hari_tersedia_saat_usul`.
- Remove legacy migration data-transfer logic.

#### [NEW] [2026_09_05_150000_create_pegawai_identitas_table.php](file:///Users/mac/Projects/sikat/database/migrations/2026_09_05_150000_create_pegawai_identitas_table.php)
- Clean `Schema::create('pegawai_identitas', ...)` migration with `id_wos`, `id_orc`, `id_sinta`, `id_scopus`, `id_garuda`, `id_gscholar`, `nidn`, `nuptk`, `jabatan_fungsional`, timestamps and foreign key cascade to `pegawais`.

#### [DELETE] Incremental / Alter / Data migrations:
- `2026_04_12_183000_update_layanan_pegawai_status_values.php`
- `2026_04_12_220000_add_cuti_fields_to_pegawais_and_layanan_pegawais.php`
- `2026_04_12_224034_remove_cuti_alamt_requirement_from_layanan.php`
- `2026_04_12_225946_update_optional_wording_for_cuti_requirements.php`
- `2026_04_13_000632_add_cuti_date_range_to_layanan_pegawais.php`
- `2026_04_13_005454_repair_cuti_layanan_pegawais_table_schema.php`
- `2026_04_13_070000_normalize_profesor_jabatan_fungsional_values.php`
- `2026_09_05_150000_separate_pegawai_identitas.php`
- `2026_09_12_223500_add_printable_fields_to_cuti_layanan_pegawais.php`

---

## Verification Plan

### Automated Tests
- Run `php artisan test --filter=Pegawai` and feature tests that run in-memory migrations to verify clean creation without errors.
- Test running `php artisan migrate:status` to ensure all 40 migration files are recognized and formatted cleanly.
