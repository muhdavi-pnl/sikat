<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataReferensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        Agama
        DB::table('agamas')->insert([
            ['agama' => 'Islam'],
            ['agama' => 'Kristen'],
            ['agama' => 'Katholik'],
            ['agama' => 'Hindu'],
            ['agama' => 'Budha'],
            ['agama' => 'Konghucu'],
            ['agama' => 'Lainnya'],
        ]);

//        Tingkat Pendidikan
        DB::table('tingkat_pendidikans')->insert([
            ['id' => 5, 'tingkat_pendidikan' => 'Sekolah Dasar', 'group_tingkat_pendidikan' => 'SD/MI'],
            ['id' => 10, 'tingkat_pendidikan' => 'SLTP', 'group_tingkat_pendidikan' => 'SLTP/MTs'],
            ['id' => 12, 'tingkat_pendidikan' => 'SLTP Kejuruan', 'group_tingkat_pendidikan' => 'SLTP/MTs'],
            ['id' => 15, 'tingkat_pendidikan' => 'SLTA', 'group_tingkat_pendidikan' => 'SLTA/SMK/MA/D-I'],
            ['id' => 17, 'tingkat_pendidikan' => 'SLTA Kejuruan', 'group_tingkat_pendidikan' => 'SLTA/SMK/MA/D-I'],
            ['id' => 18, 'tingkat_pendidikan' => 'SLTA Keguruan', 'group_tingkat_pendidikan' => 'SLTA/SMK/MA/D-I'],
            ['id' => 20, 'tingkat_pendidikan' => 'Diploma I', 'group_tingkat_pendidikan' => 'SLTA/SMK/MA/D-I'],
            ['id' => 25, 'tingkat_pendidikan' => 'Diploma II', 'group_tingkat_pendidikan' => 'D-II'],
            ['id' => 30, 'tingkat_pendidikan' => 'Diploma III/Sarjana Muda', 'group_tingkat_pendidikan' => 'D-III'],
            ['id' => 35, 'tingkat_pendidikan' => 'Diploma IV', 'group_tingkat_pendidikan' => 'S-1/D-IV'],
            ['id' => 40, 'tingkat_pendidikan' => 'S-1/Sarjana', 'group_tingkat_pendidikan' => 'S-1/D-IV'],
            ['id' => 45, 'tingkat_pendidikan' => 'S-2', 'group_tingkat_pendidikan' => 'S-2'],
            ['id' => 50, 'tingkat_pendidikan' => 'S-3/Doktor', 'group_tingkat_pendidikan' => 'S-3'],
        ]);

//        Pendidikan
        DB::table('pendidikans')->insert([
            ['tingkat_pendidikan_id' => 45, 'pendidikan' => 'S-2 Ilmu Komputer', 'perguruan_tinggi' => 'Universitas Gadjah Mada'],
            ['tingkat_pendidikan_id' => 50, 'pendidikan' => 'S-3 Ilmu Komputer', 'perguruan_tinggi' => 'Universitas Gadjah Mada'],
            ['tingkat_pendidikan_id' => 50, 'pendidikan' => 'S-3 Teknik Elektro dan Informatika', 'perguruan_tinggi' => 'Institut Teknologi Bandung'],
            ['tingkat_pendidikan_id' => 50, 'pendidikan' => 'S-3 Ilmu Komputer', 'perguruan_tinggi' => 'Universitas Indonesia'],
        ]);

//        Perguruan Tinggi
        DB::table('perguruan_tinggis')->insert([
            ['perguruan_tinggi' => 'Politeknik Negeri Lhokseumawe'],
        ]);
//        Jurusan
        DB::table('jurusans')->insert([
            ['perguruan_tinggi_id' => 1,'jurusan' => 'Teknik Sipil'],
            ['perguruan_tinggi_id' => 1,'jurusan' => 'Teknik Mesin'],
            ['perguruan_tinggi_id' => 1,'jurusan' => 'Teknik Kimia'],
            ['perguruan_tinggi_id' => 1,'jurusan' => 'Teknik Elektro'],
            ['perguruan_tinggi_id' => 1,'jurusan' => 'Bisnis'],
            ['perguruan_tinggi_id' => 1,'jurusan' => 'Teknologi Informasi dan Komputer'],
        ]);

//        Program Studi
        DB::table('program_studis')->insert([
//            Teknik Sipil
            ['kode_prodi' => '22303', 'status' => 'Aktif', 'jurusan_id' => 1, 'jenjang' => 'D4', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Rekayasa Konstruksi Bangunan Gedung'],
            ['kode_prodi' => '22302', 'status' => 'Aktif', 'jurusan_id' => 1, 'jenjang' => 'D4', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Rekayasa Konstruksi Jalan dan Jembatan'],
            ['kode_prodi' => '22401', 'status' => 'Aktif', 'jurusan_id' => 1, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Konstruksi Bangunan Gedung'],
            ['kode_prodi' => '22404', 'status' => 'Aktif', 'jurusan_id' => 1, 'jenjang' => 'D3', 'akreditasi' => 'C', 'nama_prodi' => 'Teknologi Konstruksi Bangunan Air'],
            ['kode_prodi' => '22405', 'status' => 'Aktif', 'jurusan_id' => 1, 'jenjang' => 'D3', 'akreditasi' => 'Baik', 'nama_prodi' => 'Teknologi Konstruksi Jalan dan Jembatan'],
            ['kode_prodi' => '22503', 'status' => 'Aktif', 'jurusan_id' => 1, 'jenjang' => 'D2', 'akreditasi' => 'Baik', 'nama_prodi' => 'Pengukuran dan Pengambaran Tapak Bangunan Gedung'],
//            Teknik Mesin
            ['kode_prodi' => '36303', 'status' => 'Aktif', 'jurusan_id' => 2, 'jenjang' => 'D4', 'akreditasi' => 'Baik', 'nama_prodi' => 'Teknologi Rekayasa Pengelasan dan Fabrikasi'],
            ['kode_prodi' => '21301', 'status' => 'Aktif', 'jurusan_id' => 2, 'jenjang' => 'D4', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Rekayasa Manufaktur'],
            ['kode_prodi' => '21401', 'status' => 'Aktif', 'jurusan_id' => 2, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Mesin'],
            ['kode_prodi' => '21402', 'status' => 'Aktif', 'jurusan_id' => 2, 'jenjang' => 'D3', 'akreditasi' => 'C', 'nama_prodi' => 'Teknologi Industri'],
//            Teknik Kimia
            ['kode_prodi' => '24301', 'status' => 'Aktif', 'jurusan_id' => 3, 'jenjang' => 'D4', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Rekayasa Kimia Industri'],
            ['kode_prodi' => '32402', 'status' => 'Aktif', 'jurusan_id' => 3, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Pengolahan Minyak Dan Gas'],
            ['kode_prodi' => '24401', 'status' => 'Aktif', 'jurusan_id' => 3, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Kimia'],
//            Teknik Elektro
            ['kode_prodi' => '20302', 'status' => 'Aktif', 'jurusan_id' => 4, 'jenjang' => 'D4', 'akreditasi' => 'Baik', 'nama_prodi' => 'Teknologi Rekayasa Jaringan Telekomunikasi'],
            ['kode_prodi' => '21312', 'status' => 'Aktif', 'jurusan_id' => 4, 'jenjang' => 'D4', 'akreditasi' => 'Baik', 'nama_prodi' => 'Teknologi Rekayasa Mekatronika'],
            ['kode_prodi' => '20301', 'status' => 'Aktif', 'jurusan_id' => 4, 'jenjang' => 'D4', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Rekayasa Instrumentasi dan Kontrol'],
            ['kode_prodi' => '20305', 'status' => 'Aktif', 'jurusan_id' => 4, 'jenjang' => 'D4', 'akreditasi' => 'C', 'nama_prodi' => 'Teknologi Rekayasa Pembangkit Energi'],
            ['kode_prodi' => '20401', 'status' => 'Aktif', 'jurusan_id' => 4, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Elektronika'],
            ['kode_prodi' => '20403', 'status' => 'Aktif', 'jurusan_id' => 4, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Listrik'],
            ['kode_prodi' => '20402', 'status' => 'Aktif', 'jurusan_id' => 4, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Telekomunikasi'],
//            Tata Niaga
            ['kode_prodi' => '60106', 'status' => 'Aktif', 'jurusan_id' => 5, 'jenjang' => 'S2 Terapan', 'akreditasi' => 'Baik', 'nama_prodi' => 'Keuangan Islam Terapan'],
            ['kode_prodi' => '61306', 'status' => 'Aktif', 'jurusan_id' => 5, 'jenjang' => 'D4', 'akreditasi' => 'B', 'nama_prodi' => 'Akuntansi Lembaga Keuangan Syariah'],
            ['kode_prodi' => '62303', 'status' => 'Aktif', 'jurusan_id' => 5, 'jenjang' => 'D4', 'akreditasi' => 'Baik', 'nama_prodi' => 'Akuntansi Sektor Publik'],
            ['kode_prodi' => '61312', 'status' => 'Aktif', 'jurusan_id' => 5, 'jenjang' => 'D4', 'akreditasi' => '-', 'nama_prodi' => 'Manajemen Keuangan Sektor Publik'],
            ['kode_prodi' => '63411', 'status' => 'Aktif', 'jurusan_id' => 5, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Administrasi Bisnis'],
            ['kode_prodi' => '62401', 'status' => 'Aktif', 'jurusan_id' => 5, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Akuntansi'],
            ['kode_prodi' => '61406', 'status' => 'Aktif', 'jurusan_id' => 5, 'jenjang' => 'D3', 'akreditasi' => 'B', 'nama_prodi' => 'Perbankan Dan Keuangan'],
//            TIK
            ['kode_prodi' => '57301', 'status' => 'Aktif', 'jurusan_id' => 6, 'jenjang' => 'D4', 'akreditasi' => 'B', 'nama_prodi' => 'Teknik Informatika'],
            ['kode_prodi' => '90343', 'status' => 'Aktif', 'jurusan_id' => 6, 'jenjang' => 'D4', 'akreditasi' => 'B', 'nama_prodi' => 'Teknologi Rekayasa Komputer Jaringan'],
            ['kode_prodi' => '58302', 'status' => 'Aktif', 'jurusan_id' => 6, 'jenjang' => 'D4', 'akreditasi' => 'Baik', 'nama_prodi' => 'Teknologi Rekayasa Multimedia'],
        ]);

//        Eselon
        DB::table('eselons')->insert([
            ['id' => '00', 'eselon' => '', 'eselon_level' => '0', 'jabatan_asn' => ''],
            ['id' => '10', 'eselon' => 'I.a', 'eselon_level' => '1', 'jabatan_asn' => 'JABATAN PIMPINAN TINGGI UTAMA'],
            ['id' => '11', 'eselon' => 'I.a', 'eselon_level' => '1', 'jabatan_asn' => 'JABATAN PIMPINAN TINGGI MADYA'],
            ['id' => '12', 'eselon' => 'I.b', 'eselon_level' => '1', 'jabatan_asn' => 'JABATAN PIMPINAN TINGGI MADYA'],
            ['id' => '21', 'eselon' => 'II.a', 'eselon_level' => '2', 'jabatan_asn' => 'JABATAN PIMPINAN TINGGI PRATAMA'],
            ['id' => '22', 'eselon' => 'II.b', 'eselon_level' => '2', 'jabatan_asn' => 'JABATAN PIMPINAN TINGGI PRATAMA'],
            ['id' => '31', 'eselon' => 'III.a', 'eselon_level' => '3', 'jabatan_asn' => 'JABATAN ADMINISTRATOR'],
            ['id' => '32', 'eselon' => 'III.b', 'eselon_level' => '3', 'jabatan_asn' => 'JABATAN ADMINISTRATOR'],
            ['id' => '41', 'eselon' => 'IV.a', 'eselon_level' => '4', 'jabatan_asn' => 'JABATAN PENGAWAS'],
            ['id' => '42', 'eselon' => 'IV.b', 'eselon_level' => '4', 'jabatan_asn' => 'JABATAN PENGAWAS'],
            ['id' => '51', 'eselon' => 'V.a', 'eselon_level' => '5', 'jabatan_asn' => null],
            ['id' => '52', 'eselon' => 'V.b', 'eselon_level' => '5', 'jabatan_asn' => null],
            ['id' => '99', 'eselon' => 'Non Eselon', 'eselon_level' => 'Non Eselon', 'jabatan_asn' => null],
        ]);

//        Jenis Jabatan
        DB::table('jenis_jabatans')->insert([
            ['jenis_jabatan' => 'Jabatan Struktural'],
            ['jenis_jabatan' => 'Jabatan Fungsional Tertentu'],
            ['jenis_jabatan' => 'Jabatan Rangkap (Struktural dan Fungsional)'],
            ['jenis_jabatan' => 'Jabatan Fungsional Umum'],
        ]);

//        Jabatan
        DB::table('jabatans')->insert([
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Direktur'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Wakil Direktur Bidang Akademik, Kemahasiswaan, dan Alumni'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Wakil Direktur Bidang Umum dan Keuangan'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Wakil Direktur Bidang Perencanaan, Kerja Sama, dan Sistem Informasi'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala UPT Perpustakaan'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala UPT Teknologi Informasi dan Komunikasi'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala UPT Bahasa'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala UPT Teknologi Permesinan dan Peralatan Penunjang Akademik'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala UPT Pengembangan Karir Mahasiswa'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala UPT Layanan Uji Kompetensi'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala Pusat Penelitian dan Pengabdian Kepada Masyarakat'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala Pusat Pengembangan Pembelajaran dan Penjaminan Mutu'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Ketua Jurusan Teknik Sipil'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Ketua Jurusan Teknik Mesin'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Ketua Jurusan Teknik Kimia'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Ketua Jurusan Teknik Elektro'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Ketua Jurusan Tata Niaga'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Ketua Jurusan Teknologi Informasi dan Komputer'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => null, 'jabatan' => 'Kepala Laboratorium/Studio/Bengkel'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => 12, 'jabatan' => 'Kepala Bagian Akademik, Kemahasiswaan, dan Perencanaan'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => 9, 'jabatan' => 'Kepala Subbagian Akademik'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => 9, 'jabatan' => 'Kepala Subbagian Kemahasiswaan'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => 9, 'jabatan' => 'Kepala Subbagian Perencanaan dan Kerja Sama'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => 12, 'jabatan' => 'Kepala Bagian Umum, Keuangan, dan Kepegawaian'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => 9, 'jabatan' => 'Kepala Subbagian Tata Usaha dan Barang Milik Negara'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => 9, 'jabatan' => 'Kepala Subbagian Keuangan'],
            ['jenis_jabatan_id' => 1, 'kelas_jabatan' => 9, 'jabatan' => 'Kepala Subbagian Hukum, Tata Laksana, dan Kepegawaian'],
            ['jenis_jabatan_id' => 2, 'kelas_jabatan' => null, 'jabatan' => 'Dosen'],
        ]);

//        Status Perkawinan
        DB::table('status_perkawinans')->insert([
            ['status_perkawinan' => 'Menikah'],
            ['status_perkawinan' => 'Cerai'],
            ['status_perkawinan' => 'Janda/Duda'],
            ['status_perkawinan' => 'Belum Kawin'],
        ]);

//        Kedudukan Pegawai
        DB::table('kedudukan_pegawais')->insert([
            ['id' => '01', 'kedudukan_pegawai' => 'Aktif'],
            ['id' => '02', 'kedudukan_pegawai' => 'CLTN'],
            ['id' => '03', 'kedudukan_pegawai' => 'Tugas Belajar'],
            ['id' => '04', 'kedudukan_pegawai' => 'Pemberhentian Sementara'],
            ['id' => '05', 'kedudukan_pegawai' => 'Penerima Uang Tunggu'],
            ['id' => '06', 'kedudukan_pegawai' => 'Prajurit Wajib'],
            ['id' => '07', 'kedudukan_pegawai' => 'Pejabat Negara'],
            ['id' => '08', 'kedudukan_pegawai' => 'Kepala Desa'],
            ['id' => '09', 'kedudukan_pegawai' => 'Sedang dlm Proses Banding BAPEK'],
            ['id' => '11', 'kedudukan_pegawai' => 'Pegawai Titipan'],
            ['id' => '12', 'kedudukan_pegawai' => 'Pengungsi'],
            ['id' => '13', 'kedudukan_pegawai' => 'Perpanjangan CLTN'],
            ['id' => '14', 'kedudukan_pegawai' => 'PNS yang dinyatakan hilang'],
            ['id' => '15', 'kedudukan_pegawai' => 'PNS kena hukuman disiplin'],
            ['id' => '16', 'kedudukan_pegawai' => 'Pemindahan dalam rangka penurunan Jabatan'],
            ['id' => '20', 'kedudukan_pegawai' => 'Masa Persiapan Pensiun'],
            ['id' => '51', 'kedudukan_pegawai' => 'CPNS yang belum menerima SK CPNS'],
            ['id' => '52', 'kedudukan_pegawai' => 'Tidak Aktif'],
            ['id' => '66', 'kedudukan_pegawai' => 'Diberhentikan'],
            ['id' => '67', 'kedudukan_pegawai' => 'Punah'],
            ['id' => '68', 'kedudukan_pegawai' => 'Eks PNS Timor Timur'],
            ['id' => '69', 'kedudukan_pegawai' => 'TMS Dari Pengadaan'],
            ['id' => '70', 'kedudukan_pegawai' => 'Pembatalan NIP'],
            ['id' => '77', 'kedudukan_pegawai' => 'Pemberhentian tanpa hak pensiun'],
            ['id' => '71', 'kedudukan_pegawai' => 'PPPK Aktif'],
            ['id' => '88', 'kedudukan_pegawai' => 'Pemberhentian dengan hak pensiun'],
            ['id' => '89', 'kedudukan_pegawai' => 'Tidak aktif tetapi diusulkan Pensiun'],
            ['id' => '90', 'kedudukan_pegawai' => 'Tidak Ikut PUPNS 2015'],
            ['id' => '91', 'kedudukan_pegawai' => 'Tindak Pidana/ Tindak Pidana Jabatan'],
            ['id' => '92', 'kedudukan_pegawai' => 'Pemblokiran Data PNS'],
            ['id' => '98', 'kedudukan_pegawai' => 'Mencapai BUP'],
            ['id' => '99', 'kedudukan_pegawai' => 'Pensiun'],
        ]);

//        Pangkat
        DB::table('pangkats')->insert([
            ['id' => '11', 'golongan_ruang' => 'I/a', 'pangkat' => 'Juru Muda'],
            ['id' => '12', 'golongan_ruang' => 'I/b', 'pangkat' => 'Juru Muda Tingkat I'],
            ['id' => '13', 'golongan_ruang' => 'I/c', 'pangkat' => 'Juru'],
            ['id' => '14', 'golongan_ruang' => 'I/d', 'pangkat' => 'Juru Tingkat I'],
            ['id' => '21', 'golongan_ruang' => 'II/a', 'pangkat' => 'Pengatur Muda'],
            ['id' => '22', 'golongan_ruang' => 'II/b', 'pangkat' => 'Pengatur Muda Tingkat I'],
            ['id' => '23', 'golongan_ruang' => 'II/c', 'pangkat' => 'Pengatur'],
            ['id' => '24', 'golongan_ruang' => 'II/d', 'pangkat' => 'Pengatur Tingkat I'],
            ['id' => '31', 'golongan_ruang' => 'III/a', 'pangkat' => 'Penata Muda'],
            ['id' => '32', 'golongan_ruang' => 'III/b', 'pangkat' => 'Penata Muda Tingkat I'],
            ['id' => '33', 'golongan_ruang' => 'III/c', 'pangkat' => 'Penata'],
            ['id' => '34', 'golongan_ruang' => 'III/d', 'pangkat' => 'Penata Tingkat I'],
            ['id' => '41', 'golongan_ruang' => 'IV/a', 'pangkat' => 'Pembina'],
            ['id' => '42', 'golongan_ruang' => 'IV/b', 'pangkat' => 'Pembina Tingkat I'],
            ['id' => '43', 'golongan_ruang' => 'IV/c', 'pangkat' => 'Pembina Utama Muda'],
            ['id' => '44', 'golongan_ruang' => 'IV/d', 'pangkat' => 'Pembina Utama Madya'],
            ['id' => '45', 'golongan_ruang' => 'IV/e', 'pangkat' => 'Pembina Utama'],
        ]);

//        Unit Kerja
        DB::table('unit_kerjas')->insert([
            ['unit_kerja' => 'Politeknik Negeri Lhokseumawe'],
            ['unit_kerja' => 'Bagian Akademik, Kemahasiswaan, dan Perencanaan'],
            ['unit_kerja' => 'Bagian Umum, Keuangan, dan Kepegawaian'],
            ['unit_kerja' => 'UPT Perpustakaan'],
            ['unit_kerja' => 'UPT Teknologi Informasi dan Komunikasi'],
            ['unit_kerja' => 'UPT Bahasa'],
            ['unit_kerja' => 'UPT Teknologi Permesinan dan Peralatan Penunjang Akademik'],
            ['unit_kerja' => 'UPT Pengembangan Karir Mahasiswa'],
            ['unit_kerja' => 'UPT Layanan Uji Kompetensi'],
            ['unit_kerja' => 'Pusat Penelitian dan Pengabdian Kepada Masyarakat'],
            ['unit_kerja' => 'Pusat Pengembangan Pembelajaran dan Penjaminan Mutu'],
            ['unit_kerja' => 'Jurusan Teknik Sipil'],
            ['unit_kerja' => 'Jurusan Teknik Mesin'],
            ['unit_kerja' => 'Jurusan Teknik Kimia'],
            ['unit_kerja' => 'Jurusan Teknik Elektro'],
            ['unit_kerja' => 'Jurusan Tata Niaga'],
            ['unit_kerja' => 'Jurusan Teknologi Informasi dan Komputer'],
        ]);
    }
}
