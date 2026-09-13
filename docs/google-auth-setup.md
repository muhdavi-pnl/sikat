# Google Login / Registration Setup

Panduan ini menjelaskan cara mengaktifkan login dan registrasi menggunakan akun Google pada aplikasi SIKAT (Sistem Informasi Kepegawaian Terintegrasi).

## Ringkasan Implementasi

Fitur Google auth di aplikasi ini menggunakan:

- package `laravel/socialite`
- route redirect: `auth.google.redirect`
- route callback: `auth.google.callback`
- controller: `app/Http/Controllers/Auth/GoogleAuthenticatedSessionController.php`
- konfigurasi provider: `config/services.php`
- kolom penyimpanan akun Google: `users.google_id`

## Prasyarat

Pastikan hal berikut sudah tersedia:

1. aplikasi sudah ter-deploy atau bisa diakses dari URL lokal/public
2. file `.env` dapat diedit
3. migrasi terbaru sudah dijalankan
4. Anda memiliki akun Google Cloud Console

## 1. Jalankan Migrasi

Fitur ini menambahkan kolom `google_id` ke tabel `users`.

Jalankan:

```bash
php artisan migrate
```

## 2. Buat OAuth Client di Google Cloud Console

### A. Buka Google Cloud Console

- buka: `https://console.cloud.google.com/`
- pilih project yang ingin digunakan, atau buat project baru

### B. Aktifkan Google Identity / OAuth

- buka menu **APIs & Services**
- buka **OAuth consent screen**
- isi informasi aplikasi yang diperlukan
- tambahkan email support dan data dasar aplikasi

### C. Buat OAuth Client ID

- buka **APIs & Services > Credentials**
- klik **Create Credentials**
- pilih **OAuth client ID**
- pilih tipe aplikasi: **Web application**

### D. Isi Authorized Redirect URI

Gunakan callback aplikasi ini:

```text
https://your-domain.com/auth/google/callback
```

Untuk lokal, contoh:

```text
http://localhost/auth/google/callback
```

Atau jika local dev Anda memakai port tertentu:

```text
http://127.0.0.1:8000/auth/google/callback
```

> Redirect URI harus **persis sama** dengan nilai `GOOGLE_REDIRECT_URI` di file `.env`.

### E. Simpan Client ID dan Client Secret

Setelah OAuth client dibuat, Google akan memberikan:

- Client ID
- Client Secret

Simpan kedua nilai ini untuk dipakai di `.env`.

## 3. Isi Konfigurasi `.env`

Tambahkan atau perbarui nilai berikut di file `.env`:

```dotenv
APP_URL=https://your-domain.com

GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Contoh lokal:

```dotenv
APP_URL=http://127.0.0.1:8000

GOOGLE_CLIENT_ID=your-local-google-client-id
GOOGLE_CLIENT_SECRET=your-local-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

## 4. Bersihkan Cache Konfigurasi

Setelah `.env` diubah, jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

Jika environment production memakai cache config, lanjutkan dengan:

```bash
php artisan config:cache
```

## 5. Cara Kerja Login / Registrasi Google

Saat user klik tombol Google pada halaman login atau register:

1. user diarahkan ke Google
2. setelah sukses, Google mengembalikan user ke callback aplikasi
3. aplikasi memeriksa email Google
4. aplikasi hanya menerima akun Google dengan email terverifikasi
5. perilaku akun:
   - jika `google_id` sudah ada → user langsung login
   - jika email sudah ada di tabel `users` → akun existing akan di-link ke Google
   - jika email belum ada → aplikasi membuat user baru lalu login

## 6. Perilaku User Baru dari Google

Untuk user baru yang dibuat dari Google:

- `email_verified_at` diisi otomatis
- `google_id` disimpan otomatis
- password random internal dibuat oleh sistem
- user tetap bisa memakai sesi login normal aplikasi setelah berhasil masuk

## 7. Lokasi Tombol Google

Tombol Google tersedia di:

- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`

## 8. Verifikasi Manual

Setelah setup selesai, cek hal berikut:

- [ ] halaman `/login` menampilkan tombol **Login dengan Google**
- [ ] halaman `/register` menampilkan tombol **Register / Login with Google**
- [ ] klik tombol Google mengarah ke halaman consent Google
- [ ] setelah callback, user berhasil masuk ke aplikasi
- [ ] untuk user baru, record `users.google_id` terisi
- [ ] untuk user lama dengan email yang sama, akun lama terhubung ke Google tanpa membuat duplikasi user

## 9. Troubleshooting

### Redirect URI mismatch

Gejala:
- Google menolak callback
- muncul error redirect mismatch

Periksa:
- `GOOGLE_REDIRECT_URI` di `.env`
- **Authorized Redirect URI** di Google Cloud Console
- `APP_URL`

Ketiganya harus konsisten.

### Login gagal setelah kembali dari Google

Periksa:
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`
- cache konfigurasi Laravel sudah dibersihkan

### Akun Google ditolak

Aplikasi saat ini mewajibkan email Google yang terverifikasi.

Jika akun Google tidak memiliki email terverifikasi, login akan ditolak.

### User baru tidak tersimpan

Pastikan migrasi sudah dijalankan:

```bash
php artisan migrate
```

## 10. Regression Coverage

Fitur ini sudah memiliki test coverage di:

- `tests/Feature/Auth/GoogleAuthenticationTest.php`

Cek cepat dengan:

```bash
php artisan test tests/Feature/Auth/GoogleAuthenticationTest.php
```

## 11. File Terkait

- `app/Http/Controllers/Auth/GoogleAuthenticatedSessionController.php`
- `routes/auth.php`
- `config/services.php`
- `.env.example`
- `database/migrations/2026_04_22_210000_add_google_id_to_users_table.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `tests/Feature/Auth/GoogleAuthenticationTest.php`

