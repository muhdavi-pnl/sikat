<?php

namespace Database\Seeders;

use App\Models\CutiLayananPegawai;
use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\Layanan;
use App\Models\LayananPegawai;
use App\Models\PejabatCutiSetting;
use App\Models\Pegawai;
use App\Models\PetaJabatan;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CutiWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('Sikat2019');

        // 1. Roles
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai']);
        $atasanRole = Role::firstOrCreate(['name' => 'atasan']);
        $kepegawaianRole = Role::firstOrCreate(['name' => 'kepegawaian']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

        // 2. Unit Kerja
        $unitPnl = UnitKerja::firstOrCreate(
            ['unit_kerja' => 'Politeknik Negeri Lhokseumawe']
        );

        $unitTik = UnitKerja::firstOrCreate(
            ['unit_kerja' => 'Jurusan Teknologi Informasi dan Komputer']
        );

        $unitKepegawaian = UnitKerja::firstOrCreate(
            ['unit_kerja' => 'Bagian Perencanaan, Keuangan, dan Umum']
        );

        // 3. Jenis Jabatan
        $jenisStruktural = JenisJabatan::firstOrCreate(['jenis_jabatan' => 'Jabatan Struktural']);
        $jenisFungsional = JenisJabatan::firstOrCreate(['jenis_jabatan' => 'Jabatan Fungsional Tertentu']);

        // 4. Struktur Jabatan & Hierarki
        // Direktur (Top level / PYBMC)
        $jabatanDirektur = Jabatan::updateOrCreate(
            ['jabatan' => 'Direktur'],
            [
                'kode_jabatan' => 'DIR-01',
                'ikhtisar_jabatan' => 'Memimpin penyelenggaraan pendidikan vokasi, penelitian, dan pengabdian masyarakat di Politeknik Negeri Lhokseumawe.',
            ]
        );
        PetaJabatan::updateOrCreate(
            ['jabatan_id' => $jabatanDirektur->id],
            [
                'unit_kerja_id' => $unitPnl->id,
                'atasan_langsung_id' => null,
                'kebutuhan_pegawai' => 1,
            ]
        );

        // Ketua Jurusan TIK (Atasan Langsung)
        $jabatanKajurTik = Jabatan::updateOrCreate(
            ['jabatan' => 'Ketua Jurusan Teknologi Informasi dan Komputer'],
            [
                'kode_jabatan' => 'KAJUR-TIK',
                'ikhtisar_jabatan' => 'Memimpin dan mengelola kegiatan akademik, sumber daya dosen, dan pengajaran di lingkungan Jurusan TIK.',
            ]
        );
        PetaJabatan::updateOrCreate(
            ['jabatan_id' => $jabatanKajurTik->id],
            [
                'unit_kerja_id' => $unitTik->id,
                'atasan_langsung_id' => $jabatanDirektur->id,
                'kebutuhan_pegawai' => 1,
            ]
        );

        // Dosen TIK (Bawahan langsung dari Ketua Jurusan TIK)
        $jabatanDosen = Jabatan::updateOrCreate(
            ['jabatan' => 'Dosen'],
            [
                'kode_jabatan' => 'DOSEN-TIK',
                'ikhtisar_jabatan' => 'Melaksanakan Tridharma Perguruan Tinggi meliputi pengajaran, penelitian, dan pengabdian kepada masyarakat.',
            ]
        );
        PetaJabatan::updateOrCreate(
            ['jabatan_id' => $jabatanDosen->id],
            [
                'unit_kerja_id' => $unitTik->id,
                'atasan_langsung_id' => $jabatanKajurTik->id,
                'kebutuhan_pegawai' => 25,
            ]
        );

        // Staf Administrasi TIK (Bawahan)
        $jabatanStafTik = Jabatan::updateOrCreate(
            ['jabatan' => 'Staf Administrasi Jurusan TIK'],
            [
                'kode_jabatan' => 'STAF-TIK',
                'ikhtisar_jabatan' => 'Memberikan layanan administrasi akademik dan persuratan di Jurusan TIK.',
            ]
        );
        PetaJabatan::updateOrCreate(
            ['jabatan_id' => $jabatanStafTik->id],
            [
                'unit_kerja_id' => $unitTik->id,
                'atasan_langsung_id' => $jabatanKajurTik->id,
                'kebutuhan_pegawai' => 3,
            ]
        );

        // 5. Pegawai & User Setup
        // A. PYBMC: Direktur
        $userDirektur = User::updateOrCreate(
            ['email' => 'direktur@pnl.ac.id'],
            [
                'name' => 'Prof. Dr. Ir. Direktur PNL, M.T.',
                'password' => $defaultPassword,
            ]
        );
        $userDirektur->syncRoles(['pegawai', 'atasan']);

        $pegawaiDirektur = Pegawai::updateOrCreate(
            ['nip' => '197001011995011001'],
            [
                'nama' => 'Direktur PNL',
                'gelar_depan' => 'Prof. Dr. Ir.',
                'gelar_belakang' => 'M.T.',
                'email' => 'direktur@pnl.ac.id',
                'jenis_kelamin' => 1,
                'jabatan_id' => $jabatanDirektur->id,
                'unit_kerja_id' => $unitPnl->id,
                'user_id' => $userDirektur->id,
                'cuti_hari_tersedia' => 12,
                'status_pegawai' => 'PNS',
            ]
        );


        // Aktifkan PejabatCutiSetting untuk PYBMC
        PejabatCutiSetting::query()->update(['is_active' => false]);
        PejabatCutiSetting::updateOrCreate(
            ['pegawai_id' => $pegawaiDirektur->id],
            [
                'jabatan_label' => 'Direktur Politeknik Negeri Lhokseumawe',
                'is_active' => true,
            ]
        );

        // B. Atasan Langsung: Salahuddin (Ketua Jurusan TIK)
        $userSalahuddin = User::firstOrCreate(
            ['email' => 'salahuddintik@pnl.ac.id'],
            [
                'name' => 'Salahuddin',
                'password' => $defaultPassword,
            ]
        );
        $userSalahuddin->syncRoles(['atasan', 'pegawai']);

        $pegawaiSalahuddin = Pegawai::updateOrCreate(
            ['nip' => '197508152002121002'],
            [
                'nama' => 'Salahuddin',
                'gelar_belakang' => 'S.T., M.T.',
                'email' => 'salahuddintik@pnl.ac.id',
                'jenis_kelamin' => 1,
                'jabatan_id' => $jabatanKajurTik->id,
                'unit_kerja_id' => $unitTik->id,
                'user_id' => $userSalahuddin->id,
                'cuti_hari_tersedia' => 12,
                'status_pegawai' => 'PNS',
            ]
        );

        // C. Bawahan 1: Muhammad Davi (Dosen TIK)
        $userDavi = User::firstOrCreate(
            ['email' => 'muhammad.davi@pnl.ac.id'],
            [
                'name' => 'Muhammad Davi',
                'password' => $defaultPassword,
            ]
        );
        $userDavi->syncRoles(['pegawai']);

        $pegawaiDavi = Pegawai::updateOrCreate(
            ['nip' => '198905102022031006'],
            [
                'nama' => 'Muhammad Davi',
                'gelar_belakang' => 'S.Kom., M.Cs.',
                'email' => 'muhammad.davi@pnl.ac.id',
                'jenis_kelamin' => 1,
                'jabatan_id' => $jabatanDosen->id,
                'unit_kerja_id' => $unitTik->id,
                'user_id' => $userDavi->id,
                'cuti_hari_tersedia' => 12,
                'status_pegawai' => 'PNS',
            ]
        );

        // D. Bawahan 2: Dra. Jamilah (Dosen TIK)
        $userJamilah = User::firstOrCreate(
            ['email' => 'jamilah@pnl.ac.id'],
            [
                'name' => 'Jamilah',
                'password' => $defaultPassword,
            ]
        );
        $userJamilah->syncRoles(['pegawai']);

        $pegawaiJamilah = Pegawai::updateOrCreate(
            ['nip' => '196312311993032004'],
            [
                'nama' => 'Jamilah',
                'gelar_depan' => 'Dra.',
                'gelar_belakang' => 'M.Pd.',
                'email' => 'jamilah@pnl.ac.id',
                'jenis_kelamin' => 0,
                'jabatan_id' => $jabatanDosen->id,
                'unit_kerja_id' => $unitTik->id,
                'user_id' => $userJamilah->id,
                'cuti_hari_tersedia' => 12,
                'status_pegawai' => 'PNS',
            ]
        );

        // 6. Master Layanan Cuti
        $layananCutiTahunan = Layanan::firstOrCreate(
            ['layanan' => 'Layanan Cuti Tahunan Pegawai'],
            [
                'deskripsi' => 'Pengajuan cuti tahunan pegawai sesuai peraturan kepegawaian yang berlaku.',
                'jenis' => 'kepegawaian',
            ]
        );

        $layananCutiSakit = Layanan::firstOrCreate(
            ['layanan' => 'Layanan Cuti Sakit'],
            [
                'deskripsi' => 'Pengajuan cuti karena sakit dengan lampiran surat keterangan dokter.',
                'jenis' => 'kepegawaian',
            ]
        );

        // 7. Seed Sample Cuti Applications (All Stages)

        // SAMPLE 1: TAHAP USULAN (Menunggu Persetujuan Atasan Langsung Salahuddin)
        $usulan1 = LayananPegawai::create([
            'layanan_id' => $layananCutiTahunan->id,
            'pegawai_id' => $pegawaiDavi->id,
            'user_id' => $userDavi->id,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Mengajukan cuti tahunan untuk keperluan acara keluarga penting.',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan1->id,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Acara keluarga di luar kota',
            'alamat_menjalankan_cuti' => 'Banda Aceh, Aceh',
            'nomor_telepon_cuti' => '081234567891',
            'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'hari_diminta' => 3,
            'hari_tersedia_saat_usul' => 12,
            'approval_stage' => 'atasan',
        ]);

        // SAMPLE 2: TAHAP PROSES (Disetujui Atasan Salahuddin, Menunggu PYBMC Direktur)
        $usulan2 = LayananPegawai::create([
            'layanan_id' => $layananCutiTahunan->id,
            'pegawai_id' => $pegawaiJamilah->id,
            'user_id' => $userJamilah->id,
            'status' => LayananPegawai::STATUS_PROSES,
            'catatan_pengusul' => 'Permohonan cuti tahunan 4 hari kerja.',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDay(),
        ]);

        CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan2->id,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Keperluan pengobatan keluarga di Medan',
            'alamat_menjalankan_cuti' => 'Jl. Setiabudi No. 45, Medan',
            'nomor_telepon_cuti' => '085277889900',
            'tanggal_mulai' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(13)->format('Y-m-d'),
            'hari_diminta' => 4,
            'hari_tersedia_saat_usul' => 12,
            'atasan_pegawai_id' => $pegawaiSalahuddin->id,
            'atasan_user_id' => $userSalahuddin->id,
            'atasan_status' => 'disetujui',
            'catatan_atasan' => 'Disetujui. Tugas perkuliahan telah dialihkan secara daring.',
            'atasan_approved_at' => now()->subDay(),
            'approval_stage' => 'pybmc',
        ]);

        // SAMPLE 3: TAHAP SELESAI (Disetujui Penuh oleh Atasan & PYBMC, Siap Cetak Formulir)
        $usulan3 = LayananPegawai::create([
            'layanan_id' => $layananCutiTahunan->id,
            'pegawai_id' => $pegawaiDavi->id,
            'user_id' => $userDavi->id,
            'status' => LayananPegawai::STATUS_SELESAI,
            'catatan_pengusul' => 'Cuti tahunan yang telah disetujui bulan lalu.',
            'processed_at' => now()->subDays(15),
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(15),
        ]);

        CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan3->id,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Libur tahunan dan silaturahmi keluarga',
            'alamat_menjalankan_cuti' => 'Lhokseumawe',
            'nomor_telepon_cuti' => '081234567891',
            'tanggal_mulai' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->subDays(9)->format('Y-m-d'),
            'hari_diminta' => 2,
            'hari_tersedia_saat_usul' => 12,
            'atasan_pegawai_id' => $pegawaiSalahuddin->id,
            'atasan_user_id' => $userSalahuddin->id,
            'atasan_status' => 'disetujui',
            'catatan_atasan' => 'Telah diverifikasi dan disetujui atasan langsung.',
            'atasan_approved_at' => now()->subDays(18),
            'pybmc_pegawai_id' => $pegawaiDirektur->id,
            'pybmc_user_id' => $userDirektur->id,
            'pybmc_status' => 'disetujui',
            'catatan_pybmc' => 'Disetujui penuh oleh Direktur Politeknik Negeri Lhokseumawe.',
            'pybmc_approved_at' => now()->subDays(15),
            'approval_stage' => 'selesai',
        ]);

        // SAMPLE 4: TAHAP DITOLAK (Ditolak Atasan dengan Catatan Penolakan)
        $usulan4 = LayananPegawai::create([
            'layanan_id' => $layananCutiTahunan->id,
            'pegawai_id' => $pegawaiJamilah->id,
            'user_id' => $userJamilah->id,
            'status' => LayananPegawai::STATUS_DITOLAK,
            'catatan_pengusul' => 'Mengajukan cuti 5 hari saat pekan UTS.',
            'catatan_proses' => 'Mohon dijadwalkan ulang setelah pekan Ujian Tengah Semester (UTS) selesai.',
            'processed_at' => now()->subDays(5),
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(5),
        ]);

        CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan4->id,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Keperluan pribadi',
            'alamat_menjalankan_cuti' => 'Lhokseumawe',
            'nomor_telepon_cuti' => '085277889900',
            'tanggal_mulai' => Carbon::now()->addDays(20)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addDays(24)->format('Y-m-d'),
            'hari_diminta' => 5,
            'hari_tersedia_saat_usul' => 12,
            'atasan_pegawai_id' => $pegawaiSalahuddin->id,
            'atasan_user_id' => $userSalahuddin->id,
            'atasan_status' => 'tidak_disetujui',
            'catatan_atasan' => 'Mohon dijadwalkan ulang setelah pekan Ujian Tengah Semester (UTS) selesai.',
            'atasan_approved_at' => now()->subDays(5),
            'approval_stage' => 'ditolak',
        ]);
    }
}
