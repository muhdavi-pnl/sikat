<?php

namespace Tests\Feature;

use App\Models\Eselon;
use App\Models\JenisJabatan;
use App\Models\Pangkat;
use App\Models\Pegawai;
use App\Models\Pendidikan;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatistikPegawaiFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function guest_is_redirected_to_login_when_accessing_statistik_pegawai()
    {
        $this->get(route('dashboard.statistik'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_statistik_pegawai_with_zero_data()
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);
        $user->assignRole('pegawai');

        $response = $this->actingAs($user)->get(route('dashboard.statistik'));

        $response->assertOk();
        $response->assertSee('Statistik Pegawai');
        $response->assertSee('Grafik Jumlah Pegawai Per Jenis Kelamin');
        $response->assertSee('Grafik Jumlah Pegawai Per Golongan');
        $response->assertSee('Grafik Jumlah Pegawai Per Tingkat Pendidikan');
        $response->assertSee('Grafik Jumlah Pegawai Per Eselon Jabatan');
        $response->assertSee('Grafik Jumlah Pegawai Per Jenis Jabatan');
        $response->assertSee('Rekapitulasi Jumlah Pegawai Per Jenis Kelamin');
        $response->assertSee('Rekapitulasi Jumlah Pegawai Per Golongan');
        $response->assertSee('Rekapitulasi Jumlah Pegawai Per Tingkat Pendidikan');
        $response->assertSee('Rekapitulasi Jumlah Pegawai Per Eselon Jabatan');
        $response->assertSee('Rekapitulasi Jumlah Pegawai Per Jenis Jabatan');
        $response->assertSee('Rekapitulasi Jumlah Pegawai Per Eselon Jabatan dan Jenis Kelamin');

        $response->assertViewHas('totalPegawai', 0);
        $response->assertViewHas('totalDosen', 0);
        $response->assertViewHas('totalTendik', 0);
        $response->assertViewHas('totalLakiLaki', 0);
        $response->assertViewHas('totalPerempuan', 0);
    }

    /** @test */
    public function statistik_pegawai_computes_all_metrics_accurately()
    {
        $pimpinan = User::factory()->create([
            'must_change_password' => false,
        ]);
        $pimpinan->assignRole('pimpinan');

        // Create Tingkat Pendidikan & Pendidikan
        DB::table('tingkat_pendidikans')->insert([
            ['id' => 50, 'tingkat_pendidikan' => 'S-3 / Doktor', 'group_tingkat_pendidikan' => 'S-3'],
            ['id' => 45, 'tingkat_pendidikan' => 'S-2 / Magister', 'group_tingkat_pendidikan' => 'S-2'],
            ['id' => 40, 'tingkat_pendidikan' => 'S-1 / Sarjana', 'group_tingkat_pendidikan' => 'S-1/D-IV'],
        ]);

        $pendidikanS3Id = DB::table('pendidikans')->insertGetId([
            'tingkat_pendidikan_id' => 50,
            'pendidikan' => 'S-3 Ilmu Komputer',
        ]);
        $pendidikanS2Id = DB::table('pendidikans')->insertGetId([
            'tingkat_pendidikan_id' => 45,
            'pendidikan' => 'S-2 Teknik Informatika',
        ]);
        $pendidikanS1Id = DB::table('pendidikans')->insertGetId([
            'tingkat_pendidikan_id' => 40,
            'pendidikan' => 'S-1 Teknik Elektro',
        ]);

        // Create Pangkat
        DB::table('pangkats')->insert([
            ['id' => 31, 'golongan_ruang' => 'III/a', 'pangkat' => 'Penata Muda'],
            ['id' => 41, 'golongan_ruang' => 'IV/a', 'pangkat' => 'Pembina'],
        ]);

        // Create Eselon
        DB::table('eselons')->insert([
            ['id' => '21', 'eselon' => 'II.a', 'eselon_level' => '2', 'jabatan_asn' => 'JABATAN PIMPINAN TINGGI PRATAMA'],
            ['id' => '99', 'eselon' => 'Non Eselon', 'eselon_level' => 'Non Eselon', 'jabatan_asn' => null],
        ]);

        // Create Jenis Jabatan
        DB::table('jenis_jabatans')->insert([
            ['id' => 1, 'jenis_jabatan' => 'Jabatan Struktural'],
            ['id' => 2, 'jenis_jabatan' => 'Jabatan Fungsional Tertentu'],
        ]);

        // Create Unit Kerja with specific order
        $unitKerjaTIKId = DB::table('unit_kerjas')->insertGetId([
            'unit_kerja' => 'Jurusan TIK',
            'order' => 1,
        ]);
        $unitKerjaSipilId = DB::table('unit_kerjas')->insertGetId([
            'unit_kerja' => 'Jurusan Teknik Sipil',
            'order' => 2,
        ]);

        // Pegawai 1: Dosen Laki-laki S3 Gol IV/a Eselon II.a Struktural (Jurusan Teknik Sipil) - PNS
        Pegawai::create([
            'nama' => 'Dr. Budi Santoso',
            'nip' => '198001012005011001',
            'jenis_kelamin' => 1,
            'kelompok_pegawai' => 'dosen',
            'status_pegawai' => 'PNS',
            'pendidikan_id' => $pendidikanS3Id,
            'pangkat_id' => 41,
            'eselon_id' => '21',
            'jenis_jabatan_id' => 1,
            'unit_kerja_id' => $unitKerjaSipilId,
        ]);

        // Pegawai 2: Dosen Perempuan S2 Gol III/a Non Eselon Fungsional (Jurusan Teknik Sipil) - PNS
        Pegawai::create([
            'nama' => 'Siti Aminah, M.T.',
            'nip' => '198502022008012002',
            'jenis_kelamin' => 0,
            'kelompok_pegawai' => 'dosen',
            'status_pegawai' => 'PNS',
            'pendidikan_id' => $pendidikanS2Id,
            'pangkat_id' => 31,
            'eselon_id' => '99',
            'jenis_jabatan_id' => 2,
            'unit_kerja_id' => $unitKerjaSipilId,
        ]);

        // Pegawai 3: Tendik Perempuan S1 Gol III/a Non Eselon Fungsional (Jurusan TIK) - PPPK
        Pegawai::create([
            'nama' => 'Dewi Lestari, S.T.',
            'nip' => '199003032014012003',
            'jenis_kelamin' => 0,
            'kelompok_pegawai' => 'tendik',
            'status_pegawai' => 'PPPK',
            'pendidikan_id' => $pendidikanS1Id,
            'pangkat_id' => 31,
            'eselon_id' => '99',
            'jenis_jabatan_id' => 2,
            'unit_kerja_id' => $unitKerjaTIKId,
        ]);

        $response = $this->actingAs($pimpinan)->get(route('dashboard.statistik'));

        $response->assertOk();
        $response->assertViewHas('totalPegawai', 3);
        $response->assertViewHas('totalDosen', 2);
        $response->assertViewHas('totalTendik', 1);
        $response->assertViewHas('totalLakiLaki', 1);
        $response->assertViewHas('totalPerempuan', 2);

        // Check Rekap Gender
        $response->assertViewHas('rekapGender', function ($rekap) {
            return $rekap['laki_laki']['total'] === 1
                && $rekap['laki_laki']['dosen'] === 1
                && $rekap['laki_laki']['tendik'] === 0
                && $rekap['perempuan']['total'] === 2
                && $rekap['perempuan']['dosen'] === 1
                && $rekap['perempuan']['tendik'] === 1;
        });

        // Check Rekap Unit Kerja per Status Pegawai & Gender (Ordered by order column: TIK first, then Sipil)
        $response->assertViewHas('rekapUnitKerjaGender', function ($unitMatrix) {
            $this->assertEquals('Jurusan TIK', $unitMatrix[0]['unit_kerja']);
            $this->assertEquals('Jurusan Teknik Sipil', $unitMatrix[1]['unit_kerja']);

            $sipil = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan Teknik Sipil');
            $tik = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan TIK');

            return $sipil
                && $sipil['status']['PNS']['laki_laki'] === 1
                && $sipil['status']['PNS']['perempuan'] === 1
                && $sipil['status']['PNS']['subtotal'] === 2
                && $sipil['total'] === 2
                && $tik
                && $tik['status']['PPPK']['laki_laki'] === 0
                && $tik['status']['PPPK']['perempuan'] === 1
                && $tik['status']['PPPK']['subtotal'] === 1
                && $tik['total'] === 1;
        });

        // Check Rekap Golongan Group
        $response->assertViewHas('rekapGolonganGroup', function ($groups) {
            $gol3 = collect($groups)->firstWhere('name', 'Golongan III');
            $gol4 = collect($groups)->firstWhere('name', 'Golongan IV');
            return $gol3 && $gol3['total'] === 2 && $gol4 && $gol4['total'] === 1;
        });

        // Check Rekap Pendidikan
        $response->assertViewHas('rekapPendidikan', function ($pend) {
            $s3 = collect($pend)->firstWhere('key', 's3');
            $s2 = collect($pend)->firstWhere('key', 's2');
            $s1 = collect($pend)->firstWhere('key', 's1');
            return $s3['total'] === 1 && $s2['total'] === 1 && $s1['total'] === 1;
        });

        // Check Rekap Eselon Group
        $response->assertViewHas('rekapEselonGroup', function ($eselons) {
            $esl2 = collect($eselons)->firstWhere('name', 'Eselon II');
            $nonEsl = collect($eselons)->firstWhere('name', 'Non Eselon');
            return $esl2 && $esl2['total'] === 1 && $nonEsl && $nonEsl['total'] === 2;
        });

        // Check Rekap Jenis Jabatan
        $response->assertViewHas('rekapJenisJabatan', function ($jj) {
            $struktural = collect($jj)->firstWhere('label', 'Jabatan Struktural');
            $fungsional = collect($jj)->firstWhere('label', 'Jabatan Fungsional Tertentu');
            return $struktural && $struktural['total'] === 1 && $fungsional && $fungsional['total'] === 2;
        });

        // Check Unit Kerja Matrix: Golongan
        $response->assertViewHas('rekapUnitKerjaGolongan', function ($unitMatrix) {
            $sipil = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan Teknik Sipil');
            $tik = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan TIK');

            return $sipil
                && $sipil['golongan']['Golongan IV']['laki_laki'] === 1
                && $sipil['golongan']['Golongan III']['perempuan'] === 1
                && $sipil['total'] === 2
                && $tik
                && $tik['golongan']['Golongan III']['perempuan'] === 1
                && $tik['total'] === 1;
        });

        // Check Unit Kerja Matrix: Tingkat Pendidikan
        $response->assertViewHas('rekapUnitKerjaPendidikan', function ($unitMatrix) {
            $sipil = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan Teknik Sipil');
            $tik = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan TIK');

            return $sipil
                && $sipil['pendidikan']['s3']['laki_laki'] === 1
                && $sipil['pendidikan']['s2']['perempuan'] === 1
                && $sipil['total'] === 2
                && $tik
                && $tik['pendidikan']['s1']['perempuan'] === 1
                && $tik['total'] === 1;
        });

        // Check Unit Kerja Matrix: Eselon Jabatan
        $response->assertViewHas('rekapUnitKerjaEselon', function ($unitMatrix) {
            $sipil = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan Teknik Sipil');
            $tik = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan TIK');

            return $sipil
                && $sipil['eselon']['Eselon II']['laki_laki'] === 1
                && $sipil['eselon']['Non Eselon']['perempuan'] === 1
                && $sipil['total'] === 2
                && $tik
                && $tik['eselon']['Non Eselon']['perempuan'] === 1
                && $tik['total'] === 1;
        });

        // Check Unit Kerja Matrix: Jenis Jabatan
        $response->assertViewHas('rekapUnitKerjaJenisJabatan', function ($unitMatrix) {
            $sipil = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan Teknik Sipil');
            $tik = collect($unitMatrix)->firstWhere('unit_kerja', 'Jurusan TIK');

            return $sipil
                && $sipil['jenis_jabatan']['Jabatan Struktural']['laki_laki'] === 1
                && $sipil['jenis_jabatan']['Jabatan Fungsional Tertentu']['perempuan'] === 1
                && $sipil['total'] === 2
                && $tik
                && $tik['jenis_jabatan']['Jabatan Fungsional Tertentu']['perempuan'] === 1
                && $tik['total'] === 1;
        });

        // Check Eselon x Gender Matrix
        $response->assertViewHas('rekapEselonGenderMatrix', function ($matrix) {
            $esl2 = collect($matrix)->firstWhere('eselon_group', 'Eselon II');
            $nonEsl = collect($matrix)->firstWhere('eselon_group', 'Non Eselon');
            return $esl2['laki_laki'] === 1 && $esl2['perempuan'] === 0
                && $nonEsl['laki_laki'] === 0 && $nonEsl['perempuan'] === 2;
        });
    }
}
