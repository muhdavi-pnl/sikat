# Pegawai Profile Domisili Select2 Checklist

Checklist manual ini dipakai untuk memastikan alur `Alamat Domisili` pada halaman profil pegawai bekerja stabil setelah perbaikan nested Select2.

## Referensi Teknis

- Tampilan profil: `resources/views/pegawai/profile.blade.php`
- Script cascade: `resources/views/pegawai/_domisili-script.blade.php`
- Endpoint AJAX wilayah: `app/Http/Controllers/Front/PegawaiController.php`
- Validasi profil: `app/Http/Requests/PegawaiProfileRequest.php`
- Akun uji / demo: `docs/test-access.md`

## Persiapan

1. Login menggunakan akun role `pegawai` yang sudah terhubung ke data pegawai.
2. Buka halaman `Profil Pegawai`.
3. Pastikan bagian `Alamat Domisili` terlihat dan field berikut tersedia:
   - `Provinsi`
   - `Kabupaten / Kota`
   - `Kecamatan`
   - `Desa / Kelurahan`

## Checklist Fungsional

### 1. State awal form kosong

- [ ] Saat `Provinsi` belum dipilih, field `Kabupaten / Kota` nonaktif.
- [ ] Saat `Kabupaten / Kota` belum dipilih, field `Kecamatan` nonaktif.
- [ ] Saat `Kecamatan` belum dipilih, field `Desa / Kelurahan` nonaktif.
- [ ] Placeholder Select2 tampil sesuai level wilayah.

### 2. Alur nested normal

- [ ] Pilih satu `Provinsi`.
- [ ] Field `Kabupaten / Kota` aktif dan memuat opsi yang sesuai provinsi terpilih.
- [ ] Pilih satu `Kabupaten / Kota`.
- [ ] Field `Kecamatan` aktif dan memuat opsi yang sesuai kabupaten/kota terpilih.
- [ ] Pilih satu `Kecamatan`.
- [ ] Field `Desa / Kelurahan` aktif dan memuat opsi yang sesuai kecamatan terpilih.
- [ ] Pilih satu `Desa / Kelurahan` lalu simpan form.
- [ ] Setelah reload halaman, nilai wilayah yang dipilih tetap tampil lengkap.

### 3. Reset turunan saat parent berubah

- [ ] Pilih rantai lengkap sampai `Desa / Kelurahan`.
- [ ] Ubah nilai `Provinsi` ke provinsi lain.
- [ ] Nilai `Kabupaten / Kota`, `Kecamatan`, dan `Desa / Kelurahan` sebelumnya terhapus.
- [ ] Opsi baru `Kabupaten / Kota` hanya berasal dari provinsi terbaru.
- [ ] Ulangi skenario serupa saat mengganti `Kabupaten / Kota`; `Kecamatan` dan `Desa / Kelurahan` harus ikut reset.
- [ ] Ulangi skenario serupa saat mengganti `Kecamatan`; `Desa / Kelurahan` harus ikut reset.

### 4. Rapid switching / anti race condition

- [ ] Pilih `Provinsi A`, lalu cepat ubah ke `Provinsi B` sebelum daftar `Kabupaten / Kota` selesai dimuat.
- [ ] Pastikan daftar `Kabupaten / Kota` terakhir mengikuti `Provinsi B`, bukan `Provinsi A`.
- [ ] Ulangi pola cepat yang sama untuk level `Kabupaten / Kota` dan `Kecamatan`.
- [ ] Pastikan tidak ada opsi lama yang "menimpa" hasil pilihan terbaru.

### 5. Edit state dan old input

- [ ] Simpan profil dengan rantai wilayah lengkap.
- [ ] Buka ulang halaman profil.
- [ ] Pastikan `Provinsi`, `Kabupaten / Kota`, `Kecamatan`, dan `Desa / Kelurahan` yang tersimpan tetap terpilih.
- [ ] Kirim form dengan error validasi lain yang tidak terkait domisili.
- [ ] Setelah redirect kembali, pilihan domisili terakhir tetap dipertahankan.

### 6. Validasi domisili wajib lengkap

- [ ] Isi `Alamat Domisili`, lalu pilih hanya `Provinsi` tanpa melengkapi level lain.
- [ ] Simpan form.
- [ ] Muncul error validasi untuk level yang belum lengkap.
- [ ] Old input tetap tampil sehingga pengguna tidak perlu memilih ulang dari awal.

### 7. State saat field dikosongkan kembali

- [ ] Setelah memilih rantai domisili, kosongkan `Provinsi`.
- [ ] `Kabupaten / Kota`, `Kecamatan`, dan `Desa / Kelurahan` kembali kosong.
- [ ] Ketiga field turunan kembali nonaktif.

## Hasil yang Diharapkan

Jika seluruh checklist di atas lolos, maka perilaku halaman profil dianggap sudah benar untuk:

- nested Select2 antar level wilayah
- reset child field saat parent berubah
- perlindungan terhadap response AJAX yang terlambat
- preservasi nilai saat edit / redirect old input
- validasi domisili yang konsisten dengan backend

