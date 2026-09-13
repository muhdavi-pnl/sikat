# Rencana Implementasi: Fitur Kelola Jabatan & Data Seeder Alur Cuti

## Ringkasan Tujuan
1. **Kelola Jabatan**: Memperbarui menu Kelola Jabatan (`/peta-jabatan/manage/jabatan` dan sinkronisasi dengan referensi jabatan) agar pengguna berwenang (`super-admin` dan `kepegawaian`) dapat mengelola seluruh data jabatan secara penuh (Create, Read/Index/Detail, Update, Delete) dengan atribut lengkap (kode jabatan, unit kerja, jenis jabatan, hierarki atasan langsung, kebutuhan formasi, kelas jabatan, jenjang, kualifikasi, uraian tugas, tanggung jawab, dan wewenang).
2. **Data Seeder Proses Cuti**: Membuat seeder alur cuti terintegrasi (`CutiWorkflowSeeder`) yang melengkapi hierarki atasan-bawahan, penetapan Pejabat yang Berwenang Memberikan Cuti (PYBMC), kuota/saldo cuti, dan data sampel pengajuan cuti pada setiap tahapan (Menunggu Atasan, Menunggu PYBMC, Disetujui/Selesai, dan Ditolak) sehingga seluruh alur cuti dapat langsung diverifikasi dan diuji secara menyeluruh.

---

## Proposed Changes

### 1. Form Request & Validation
#### [NEW] [JabatanRequest.php](file:///Users/mac/Projects/sikat/app/Http/Requests/JabatanRequest.php)
- Menangani validasi seluruh atribut data `Jabatan`:
  - `jabatan`: required, string, max:150
  - `kode_jabatan`: nullable, string, max:50
  - `jenis_jabatan_id`: required, exists:jenis_jabatans,id
  - `unit_kerja_id`: nullable, exists:unit_kerjas,id
  - `atasan_langsung_id`: nullable, exists:jabatans,id (dengan validasi agar tidak memilih dirinya sendiri saat update)
  - `kebutuhan_pegawai`: required/nullable integer, min:0
  - `status_jabatan`: nullable, string, max:30
  - `jenjang_jabatan`: nullable, string, max:100
  - `kelas_jabatan`: nullable, integer, min:1, max:20
  - `pangkat_golongan`: nullable, string, max:50
  - `pendidikan_minimal`: nullable, string, max:100
  - `kompetensi`, `ikhtisar_jabatan`, `uraian_tugas`, `tanggung_jawab`, `wewenang`, `persyaratan_jabatan`: nullable, text
  - `beban_kerja`: nullable, string, max:100

---

### 2. Controller & Business Logic
#### [MODIFY] [AdminCrudController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Front/AdminCrudController.php)
- Mengimplementasikan seluruh action untuk slug `jabatan`:
  - `index('jabatan')`: Pencarian (`search`), filter unit kerja, filter jenis jabatan, pagination, serta ringkasan jumlah pegawai.
  - `create('jabatan')`: Menyiapkan data master relasi (`jenisJabatans`, `unitKerjas`, `atasanOptions`).
  - `store('jabatan')`: Menyimpan data baru dengan `JabatanRequest` dan mencatat `AuditLog`.
  - `show('jabatan', $id)`: Menampilkan detail lengkap jabatan, spesifikasi jabatan, hierarki organisasi (atasan dan bawahan), serta daftar pegawai aktif yang menduduki jabatan tersebut.
  - `edit('jabatan', $id)`: Membuka form edit terisi lengkap dengan validasi hierarki.
  - `update('jabatan', $id)`: Memperbarui data jabatan.
  - `destroy('jabatan', $id)`: Menghapus jabatan secara aman dengan proteksi/pengecekan keterikatan pegawai dan bawahan langsung.

#### [MODIFY] [JabatanController.php](file:///Users/mac/Projects/sikat/app/Http/Controllers/Front/JabatanController.php)
- Menyesuaikan `JabatanController` pada menu Master Data Referensi agar konsisten dengan `AdminCrudController` atau mengarahkan ke antarmuka terpadu pengelolaan jabatan.

---

### 3. Tampilan Antarmuka (Blade Views)
#### [MODIFY] [resources/views/peta-jabatan/manage/index.blade.php](file:///Users/mac/Projects/sikat/resources/views/peta-jabatan/manage/index.blade.php)
- Memperbarui tabel daftar jabatan:
  - Form filter dan pencarian real-time (keyword, unit kerja, jenis jabatan, status).
  - Kolom lengkap: Kode & Nama Jabatan, Jenis Jabatan, Unit Kerja, Atasan Langsung, Kebutuhan & Jumlah Terisi, Status Jabatan.
  - Tombol aksi: Tambah Jabatan, Detail Jabatan, Edit Jabatan, Hapus Jabatan (dengan konfirmasi modal/SweetAlert).
  - Paginasi data yang rapi dan responsif.

#### [NEW] [resources/views/peta-jabatan/manage/jabatan-form.blade.php](file:///Users/mac/Projects/sikat/resources/views/peta-jabatan/manage/jabatan-form.blade.php)
- Form Tambah / Edit Jabatan dengan layout grid Stisla yang rapi dan terbagi menjadi 4 seksi:
  1. **Informasi Utama**: Kode Jabatan, Nama Jabatan, Jenis Jabatan, Unit Kerja, Status Jabatan, Jenjang Jabatan.
  2. **Hierarki & Formasi**: Atasan Langsung, Kelas Jabatan, Pangkat/Golongan, Formasi Kebutuhan Pegawai, Beban Kerja.
  3. **Kualifikasi & Persyaratan**: Pendidikan Minimal, Kompetensi, Persyaratan Khusus.
  4. **Tugas & Tanggung Jawab**: Ikhtisar Jabatan, Uraian Tugas, Tanggung Jawab, Wewenang.

#### [NEW] [resources/views/peta-jabatan/manage/jabatan-show.blade.php](file:///Users/mac/Projects/sikat/resources/views/peta-jabatan/manage/jabatan-show.blade.php)
- Halaman detail komprehensif:
  - Card ringkasan profil jabatan & statistik pegawai terisi vs formasi.
  - Hierarki jabatan (Atasan Langsung & Daftar Jabatan Bawahan).
  - Daftar pegawai yang saat ini menduduki jabatan tersebut.
  - Detail spesifikasi jabatan (ikhtisar, uraian tugas, tanggung jawab, wewenang, kualifikasi).

---

### 4. Data Seeder Alur Cuti
#### [NEW] [CutiWorkflowSeeder.php](file:///Users/mac/Projects/sikat/database/seeders/CutiWorkflowSeeder.php)
- Memastikan kelengkapan data alur cuti:
  1. **Hierarki Jabatan**: Mengonfigurasi `atasan_langsung_id` pada jabatan pimpinan (Direktur), ketua jurusan (Ketua Jurusan TIK), dan dosen/staf bawahan.
  2. **Penugasan Pegawai & User**: Menghubungkan user `salahuddintik@pnl.ac.id` ke Pegawai Ketua Jurusan TIK (Atasan), serta user `muhammad.davi@pnl.ac.id` & `jamilah@pnl.ac.id` ke Pegawai Dosen (Bawahan).
  3. **Penetapan PYBMC (`PejabatCutiSetting`)**: Mengaktifkan Direktur sebagai PYBMC resmi.
  4. **Layanan Cuti**: Memastikan Master Layanan Cuti aktif.
  5. **Sampel Pengajuan Cuti (Semua Tahapan)**:
     - Tahap 1: Pengajuan baru berstatus `USULAN` menunggu persetujuan Atasan Langsung (`approval_stage = atasan`).
     - Tahap 2: Pengajuan berstatus `PROSES` yang telah disetujui Atasan dan sedang menunggu persetujuan PYBMC (`approval_stage = pybmc`).
     - Tahap 3: Pengajuan berstatus `SELESAI` yang disetujui penuh oleh Atasan dan PYBMC lengkap dengan data cetak formulir cuti.
     - Tahap 4: Pengajuan berstatus `DITOLAK` dengan alasan penolakan.

#### [MODIFY] [DatabaseSeeder.php](file:///Users/mac/Projects/sikat/database/seeders/DatabaseSeeder.php)
- Mendaftarkan `CutiWorkflowSeeder::class` ke dalam urutan eksekusi seeder utama.

---

## Verification Plan

### Automated Tests
- Menjalankan PHPUnit feature tests untuk modul Peta Jabatan, Kelola Jabatan, dan Cuti:
  ```bash
  php artisan test --filter=PetaJabatanFeatureTest
  php artisan test --filter=CutiMultiStageApprovalTest
  php artisan test
  ```
- Menambahkan unit/feature test baru untuk memastikan:
  - Super Admin dan Kepegawaian dapat melakukan create, edit, update, delete data jabatan.
  - Seeder alur cuti berhasil dijalankan tanpa error dan data cuti di setiap tahap dapat diverifikasi.

### Manual / Integration Verification
- Menjalankan seeder dengan `php artisan db:seed --class=CutiWorkflowSeeder` (atau `migrate:fresh --seed`).
- Memverifikasi login dengan user:
  - `salahuddintik@pnl.ac.id` -> Akses menu **Persetujuan Atasan** melihat pengajuan dari bawahan.
  - `sikat@muhdavi.com` / PYBMC -> Akses menu **Persetujuan PYBMC** dan **Kelola Jabatan**.
- Membuka halaman cetak formulir cuti untuk memastikan nama atasan dan PYBMC tampil sempurna.
