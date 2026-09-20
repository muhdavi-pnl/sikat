<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function guest_is_redirected_to_login_when_accessing_dashboard()
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function pimpinan_or_admin_sees_dashboard_pimpinan_with_zero_metrics()
    {
        $pimpinan = User::factory()->create([
            'must_change_password' => false,
        ]);
        $pimpinan->assignRole('pimpinan');

        $response = $this->actingAs($pimpinan)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Pimpinan');
        $response->assertSee('jurusan-jabatan-bar-chart', false);
        $response->assertSee('pegawai-pendidikan-bar-chart', false);
        $response->assertSee('jurusan-sertifikasi-radial-chart', false);
        $response->assertSee('studi-lanjut-donut-chart', false);
        $response->assertSee('Jumlah Dosen Studi Lanjut Berdasarkan Bidang Ilmu');
        $response->assertSee('pendidikan-mode-switch', false);
        $response->assertSee('chart-axis', false);
        $response->assertSee('chart-stack-shell', false);
        $response->assertSee('chart-summary-button', false);
        $response->assertSee('data-jabatan-key="profesor"', false);
        $response->assertSee('Tenaga Pengajar');
        $response->assertSee('Asisten Ahli');
        $response->assertSee('TIK');
        $response->assertViewHas('pegawais', 0);
        $response->assertViewHas('dosens', 0);
        $response->assertViewHas('tendiks', 0);
        $response->assertViewHas('totalStudiLanjutDosen', 0);

        $response->assertViewHas('jabatanFungsionalTotals', function ($totals) {
            return is_array($totals)
                && count($totals) === 5
                && collect($totals)->pluck('count')->every(fn ($count) => (int) $count === 0);
        });

        $response->assertViewHas('pegawaiPendidikanTotals', function ($totals) {
            return is_array($totals)
                && count($totals) === 6
                && collect($totals)->pluck('count')->every(fn ($count) => (int) $count === 0);
        });

        $response->assertViewHas('sertifikasiDosenTotals', function ($totals) {
            return is_array($totals)
                && count($totals) === 2
                && collect($totals)->pluck('count')->every(fn ($count) => (int) $count === 0);
        });

        $response->assertViewHas('studiLanjutTotals', function ($totals) {
            return is_array($totals)
                && count($totals) === 5
                && collect($totals)->pluck('count')->every(fn ($count) => (int) $count === 0);
        });
    }

    /** @test */
    public function pimpinan_dashboard_metrics_follow_jurusan_mapping_and_total_counts()
    {
        $superAdmin = User::factory()->create([
            'must_change_password' => false,
        ]);
        $superAdmin->assignRole('super-admin');

        $this->seedDashboardReferenceData();

        // 1. Dosen Sipil A (S-3, certified, aktif)
        $pegawaiSipilA = $this->insertPegawai('198001010000000001', 'Sipil A', 1, 'asisten ahli', 50, 'dosen', '01', 1);
        // 2. Dosen Sipil B (S-2, uncertified, tugas belajar)
        $pegawaiSipilB = $this->insertPegawai('198001010000000002', 'Sipil B', 1, 'lektor', 45, 'dosen', '03', 0);
        // 3. Dosen Kimia (S-2, uncertified, aktif)
        $pegawaiKimia = $this->insertPegawai('198001010000000003', 'Kimia', 3, 'lektor kepala', 45, 'dosen', '01', 1);
        // 4. Dosen TIK (S-3, certified, aktif)
        $pegawaiTik = $this->insertPegawai('198001010000000004', 'TIK', 6, 'profesor', 50, 'dosen', '01', 0);
        // 5. Dosen TIK Tambahan (S-2, uncertified, aktif)
        $pegawaiTikTambahan = $this->insertPegawai('198001010000000006', 'TIK Tambahan', 6, 'profesor', 45, 'dosen', '01', 1);
        // 6. Dosen Outside Mapping (jurusan 7)
        $this->insertPegawai('198001010000000005', 'Outside Mapping', 7, 'asisten ahli', 45, 'dosen', '01', 1);
        // 7. Tendik Sipil (D-III)
        $tendikSipil = $this->insertTendik('199001010000000001', 'Tendik Sipil', 1, 30, 0);
        // 8. Tendik Pusat (S-1)
        $tendikPusat = $this->insertTendik('199001010000000002', 'Tendik Pusat', null, 40, 1);

        // Seed pegawai_identitas for SERDOS
        DB::table('pegawai_identitas')->insert([
            [
                'pegawai_id' => $pegawaiSipilA,
                'no_serdos' => '1234567890123456',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pegawai_id' => $pegawaiTik,
                'no_serdos' => '9876543210987654',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pegawai_id' => $pegawaiKimia,
                'no_serdos' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Studi Lanjut data
        DB::table('studi_lanjuts')->insert([
            [
                'pegawai_id' => $pegawaiSipilA,
                'bidang_ilmu' => 'STEM',
                'progres' => 'ongoing',
                'jenis_pembiayaan' => 'beasiswa',
                'program_studi' => 'Teknik Sipil',
                'nama_institusi' => 'ITB',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pegawai_id' => $pegawaiSipilB,
                'bidang_ilmu' => 'EKONOMI',
                'progres' => 'ongoing',
                'jenis_pembiayaan' => 'mandiri',
                'program_studi' => 'Manajemen Bisnis',
                'nama_institusi' => 'UI',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pegawai_id' => $pegawaiKimia,
                'bidang_ilmu' => 'STEM',
                'progres' => 'defer',
                'jenis_pembiayaan' => 'beasiswa',
                'program_studi' => 'Teknik Kimia',
                'nama_institusi' => 'ITS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Pimpinan');
        $response->assertSee('jurusan-jabatan-bar-chart', false);
        $response->assertSee('pegawai-pendidikan-bar-chart', false);
        $response->assertSee('jurusan-sertifikasi-radial-chart', false);
        $response->assertSee('studi-lanjut-donut-chart', false);
        $response->assertSee('Jumlah Dosen Studi Lanjut Berdasarkan Bidang Ilmu');
        $response->assertSee('Semua Pegawai');
        $response->assertSee('Dosen Saja');
        $response->assertSee('Tendik Saja');
        $response->assertSee('UPA &amp; Pusat', false);

        $response->assertViewHas('pegawais', 8);
        $response->assertViewHas('dosens', 6);
        $response->assertViewHas('tendiks', 2);
        $response->assertViewHas('totalStudiLanjutDosen', 3);

        // Jabatan Fungsional
        $jabatanTotals = collect($response->viewData('jabatanFungsionalTotals'))->keyBy('key');
        $this->assertSame(0, (int) $jabatanTotals['tenaga pengajar']['count']);
        $this->assertSame(1, (int) $jabatanTotals['asisten ahli']['count']);
        $this->assertSame(1, (int) $jabatanTotals['lektor']['count']);
        $this->assertSame(1, (int) $jabatanTotals['lektor kepala']['count']);
        $this->assertSame(2, (int) $jabatanTotals['profesor']['count']);

        // Pendidikan Summary (All Pegawai)
        $pendidikanTotals = collect($response->viewData('pegawaiPendidikanTotals'))->keyBy('key');
        $this->assertSame(2, (int) $pendidikanTotals['s3']['count']); // Sipil A + TIK
        $this->assertSame(2, (int) $pendidikanTotals['s3']['dosen_count']);
        $this->assertSame(4, (int) $pendidikanTotals['s2']['count']); // Sipil B, Kimia, TIK Tambahan, Outside
        $this->assertSame(1, (int) $pendidikanTotals['s1']['count']); // Tendik Pusat
        $this->assertSame(1, (int) $pendidikanTotals['s1']['tendik_count']);
        $this->assertSame(1, (int) $pendidikanTotals['d3']['count']); // Tendik Sipil
        $this->assertSame(1, (int) $pendidikanTotals['d3']['tendik_count']);

        // Sertifikasi Dosen Summary (in jurusans 1-6)
        $sertifikasiTotals = collect($response->viewData('sertifikasiDosenTotals'))->keyBy('key');
        $this->assertSame(2, (int) $sertifikasiTotals['sudah_sertifikasi']['count']); // Sipil A + TIK
        $this->assertSame(3, (int) $sertifikasiTotals['belum_sertifikasi']['count']); // Sipil B + Kimia + TIK Tambahan

        // Studi Lanjut Summary (grouped by Bidang Studi)
        $studiTotals = collect($response->viewData('studiLanjutTotals'))->keyBy('key');
        $this->assertSame(2, (int) $studiTotals['STEM']['count']); // Sipil A + Kimia
        $this->assertSame(1, (int) $studiTotals['EKONOMI']['count']); // Sipil B
        $this->assertSame(0, (int) $studiTotals['SOSIAL']['count']);
        $this->assertSame(0, (int) $studiTotals['HUMANIORA']['count']);
        $this->assertSame(0, (int) $studiTotals['KEAGAMAAN']['count']);
    }

    /** @test */
    public function pegawai_sees_dashboard_pegawai_with_gender_distribution()
    {
        $pegawaiUser = User::factory()->create([
            'must_change_password' => false,
        ]);
        $pegawaiUser->assignRole('pegawai');

        $this->seedDashboardReferenceData();

        // 5 Laki-laki (4 Dosen, 1 Tendik), 3 Perempuan (2 Dosen, 1 Tendik)
        $this->insertPegawai('198001010000000001', 'Dosen Laki 1', 1, 'asisten ahli', 50, 'dosen', '01', 1);
        $this->insertPegawai('198001010000000002', 'Dosen Laki 2', 2, 'lektor', 45, 'dosen', '01', 1);
        $this->insertPegawai('198001010000000003', 'Dosen Laki 3', 3, 'lektor', 45, 'dosen', '01', 1);
        $this->insertPegawai('198001010000000004', 'Dosen Laki 4', 4, 'profesor', 50, 'dosen', '01', 1);
        $this->insertTendik('199001010000000001', 'Tendik Laki 1', 1, 30, 1);

        $this->insertPegawai('198001010000000005', 'Dosen Perempuan 1', 5, 'lektor', 45, 'dosen', '01', 0);
        $this->insertPegawai('198001010000000006', 'Dosen Perempuan 2', 6, 'profesor', 50, 'dosen', '01', 0);
        $this->insertTendik('199001010000000002', 'Tendik Perempuan 1', null, 40, 0);

        $response = $this->actingAs($pegawaiUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Pegawai');
        $response->assertSee('pegawai-gender-bar-chart', false);
        $response->assertSee('gender-mode-switch', false);
        $response->assertSee('Pegawai Laki-laki');
        $response->assertSee('Pegawai Perempuan');
        $response->assertSee('Jumlah Pegawai Berdasarkan Jenis Kelamin');

        $response->assertViewHas('pegawais', 8);
        $response->assertViewHas('dosens', 6);
        $response->assertViewHas('tendiks', 2);
        $response->assertViewHas('totalLakiLaki', 5);
        $response->assertViewHas('totalPerempuan', 3);

        $genderTotals = collect($response->viewData('pegawaiGenderTotals'))->keyBy('key');
        $this->assertSame(5, (int) $genderTotals['laki_laki']['count']);
        $this->assertSame(4, (int) $genderTotals['laki_laki']['dosen_count']);
        $this->assertSame(1, (int) $genderTotals['laki_laki']['tendik_count']);

        $this->assertSame(3, (int) $genderTotals['perempuan']['count']);
        $this->assertSame(2, (int) $genderTotals['perempuan']['dosen_count']);
        $this->assertSame(1, (int) $genderTotals['perempuan']['tendik_count']);
    }

    protected function seedDashboardReferenceData(): void
    {
        DB::table('perguruan_tinggis')->insert([
            'id' => 1,
            'perguruan_tinggi' => 'Politeknik Negeri Lhokseumawe',
        ]);

        DB::table('tingkat_pendidikans')->insert([
            ['id' => 30, 'tingkat_pendidikan' => 'Diploma III', 'group_tingkat_pendidikan' => 'D-III'],
            ['id' => 40, 'tingkat_pendidikan' => 'S-1', 'group_tingkat_pendidikan' => 'S-1/D-IV'],
            ['id' => 45, 'tingkat_pendidikan' => 'S-2', 'group_tingkat_pendidikan' => 'S-2'],
            ['id' => 50, 'tingkat_pendidikan' => 'S-3', 'group_tingkat_pendidikan' => 'S-3'],
        ]);

        DB::table('pendidikans')->insert([
            ['id' => 30, 'tingkat_pendidikan_id' => 30, 'pendidikan' => 'D3 Teknik', 'perguruan_tinggi_id' => 1],
            ['id' => 40, 'tingkat_pendidikan_id' => 40, 'pendidikan' => 'S1 Komputer', 'perguruan_tinggi_id' => 1],
            ['id' => 45, 'tingkat_pendidikan_id' => 45, 'pendidikan' => 'S2 Teknik', 'perguruan_tinggi_id' => 1],
            ['id' => 50, 'tingkat_pendidikan_id' => 50, 'pendidikan' => 'S3 Ilmu Komputer', 'perguruan_tinggi_id' => 1],
        ]);

        DB::table('kedudukan_pegawais')->insert([
            ['id' => '01', 'kedudukan_pegawai' => 'Aktif'],
            ['id' => '03', 'kedudukan_pegawai' => 'Tugas Belajar'],
        ]);

        DB::table('jurusans')->insert([
            ['id' => 1, 'jurusan' => 'Sipil'],
            ['id' => 2, 'jurusan' => 'Mesin'],
            ['id' => 3, 'jurusan' => 'Kimia'],
            ['id' => 4, 'jurusan' => 'Elektro'],
            ['id' => 5, 'jurusan' => 'Tata Niaga'],
            ['id' => 6, 'jurusan' => 'TIK'],
            ['id' => 7, 'jurusan' => 'Other'],
        ]);

        DB::table('program_studis')->insert([
            ['id' => 1, 'kode_prodi' => 'SIP01', 'nama_prodi' => 'Sipil Prodi', 'status' => 'Aktif', 'jenjang' => 'D4', 'akreditasi' => 'A', 'jurusan_id' => 1],
            ['id' => 2, 'kode_prodi' => 'MSN01', 'nama_prodi' => 'Mesin Prodi', 'status' => 'Aktif', 'jenjang' => 'D4', 'akreditasi' => 'A', 'jurusan_id' => 2],
            ['id' => 3, 'kode_prodi' => 'KIM01', 'nama_prodi' => 'Kimia Prodi', 'status' => 'Aktif', 'jenjang' => 'D4', 'akreditasi' => 'A', 'jurusan_id' => 3],
            ['id' => 4, 'kode_prodi' => 'ELK01', 'nama_prodi' => 'Elektro Prodi', 'status' => 'Aktif', 'jenjang' => 'D4', 'akreditasi' => 'A', 'jurusan_id' => 4],
            ['id' => 5, 'kode_prodi' => 'TNI01', 'nama_prodi' => 'Tata Niaga Prodi', 'status' => 'Aktif', 'jenjang' => 'D4', 'akreditasi' => 'A', 'jurusan_id' => 5],
            ['id' => 6, 'kode_prodi' => 'TIK01', 'nama_prodi' => 'TIK Prodi', 'status' => 'Aktif', 'jenjang' => 'D4', 'akreditasi' => 'A', 'jurusan_id' => 6],
            ['id' => 7, 'kode_prodi' => 'OTH01', 'nama_prodi' => 'Other Prodi', 'status' => 'Aktif', 'jenjang' => 'D4', 'akreditasi' => 'A', 'jurusan_id' => 7],
        ]);
    }

    protected function insertPegawai(string $nip, string $nama, int $programStudiId, ?string $jabatanFungsional = null, ?int $pendidikanId = null, string $kelompok = 'dosen', ?string $kedudukanId = '01', int $jenisKelamin = 1): int
    {
        $jabatanId = null;
        if ($jabatanFungsional !== null) {
            $jabatan = DB::table('jabatans')->where('jabatan', $jabatanFungsional)->first();
            if (!$jabatan) {
                $jabatanId = (int) DB::table('jabatans')->insertGetId([
                    'jabatan' => $jabatanFungsional,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $jabatanId = $jabatan->id;
            }
        }

        return (int) DB::table('pegawais')->insertGetId([
            'nip' => $nip,
            'nama' => $nama,
            'kelompok_pegawai' => $kelompok,
            'program_studi_id' => $programStudiId,
            'jabatan_id' => $jabatanId,
            'pendidikan_id' => $pendidikanId,
            'kedudukan_pegawai_id' => $kedudukanId,
            'jenis_kelamin' => $jenisKelamin,
            'status_pegawai' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function insertTendik(string $nip, string $nama, ?int $programStudiId = null, ?int $pendidikanId = null, int $jenisKelamin = 1): int
    {
        return (int) DB::table('pegawais')->insertGetId([
            'nip' => $nip,
            'nama' => $nama,
            'kelompok_pegawai' => 'tendik',
            'program_studi_id' => $programStudiId,
            'pendidikan_id' => $pendidikanId,
            'kedudukan_pegawai_id' => '01',
            'jenis_kelamin' => $jenisKelamin,
            'status_pegawai' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
