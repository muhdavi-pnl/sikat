<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_is_redirected_to_login_when_accessing_dashboard()
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_sees_zero_metrics_when_dashboard_has_no_data()
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('jurusan-jabatan-bar-chart', false);
        $response->assertSee('chart-axis', false);
        $response->assertSee('chart-stack-shell', false);
        $response->assertSee('chart-summary-button', false);
        $response->assertSee('data-jabatan-key="profesor"', false);
        $response->assertSee('Tenaga Pengajar');
        $response->assertSee('Asisten Ahli');
        $response->assertSee('TIK');
        $response->assertDontSee('Teknologi Informasi dan Komputer');
        $response->assertViewHas('pegawais', 0);
        $response->assertViewHas('dokumens', 0);
        $response->assertViewHas('layanans', 0);
        $response->assertViewHas('jabatanFungsionalTotals', function ($totals) {
            return is_array($totals)
                && count($totals) === 5
                && collect($totals)->pluck('count')->every(function ($count) {
                    return (int) $count === 0;
                });
        });
        $response->assertViewHas('jurusanJabatanChart', function ($chart) {
            return is_array($chart)
                && count($chart) === 6
                && collect($chart)->every(function ($row) {
                    return (int) ($row['total'] ?? -1) === 0
                        && is_array($row['distribution'] ?? null)
                        && count($row['distribution']) === 5;
                });
        });
    }

    /** @test */
    public function dashboard_metrics_follow_jurusan_mapping_and_total_counts()
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $this->seedDashboardReferenceData();

        $pegawaiSipilA = $this->insertPegawai('198001010000000001', 'Sipil A', 1, 'asisten ahli');
        $pegawaiSipilB = $this->insertPegawai('198001010000000002', 'Sipil B', 1, 'lektor');
        $pegawaiKimia = $this->insertPegawai('198001010000000003', 'Kimia', 3, 'lektor kepala');
        $pegawaiTik = $this->insertPegawai('198001010000000004', 'TIK', 6, 'profesor');
        $pegawaiTikTambahan = $this->insertPegawai('198001010000000006', 'TIK Tambahan', 6, 'profesor');
        $this->insertPegawai('198001010000000005', 'Outside Mapping', 7, 'asisten ahli');

        DB::table('dokumens')->insert([
            ['id' => 1, 'kode_dokumen' => 'DOC-001', 'nama_dokumen' => 'Dokumen A'],
            ['id' => 2, 'kode_dokumen' => 'DOC-002', 'nama_dokumen' => 'Dokumen B'],
        ]);

        DB::table('dokumen_pegawai')->insert([
            [
                'dokumen_id' => 1,
                'pegawai_id' => $pegawaiSipilA,
                'user_id' => $user->id,
                'file' => 'dok-1.pdf',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dokumen_id' => 2,
                'pegawai_id' => $pegawaiKimia,
                'user_id' => $user->id,
                'file' => 'dok-2.pdf',
                'status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('layanans')->insert([
            [
                'layanan' => 'Layanan A',
                'deskripsi' => 'Deskripsi A',
                'jenis' => 'kepegawaian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'layanan' => 'Layanan B',
                'deskripsi' => 'Deskripsi B',
                'jenis' => 'fungsional',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('jurusan-jabatan-bar-chart', false);
        $response->assertSee('chart-axis', false);
        $response->assertSee('chart-stack-shell', false);
        $response->assertSee('chart-summary-button', false);
        $response->assertSee('data-jabatan-key="profesor"', false);
        $response->assertSee('Tenaga Pengajar');
        $response->assertSee('Asisten Ahli');
        $response->assertSee('TIK');
        $response->assertDontSee('Teknologi Informasi dan Komputer');
        $response->assertViewHas('pegawais', 6);
        $response->assertViewHas('dokumens', 2);
        $response->assertViewHas('layanans', 2);
        $response->assertViewHas('jabatanFungsionalTotals', function ($totals) {
            $totals = collect($totals)->keyBy('key');

            return (int) $totals['tenaga pengajar']['count'] === 0
                && (int) $totals['asisten ahli']['count'] === 1
                && (int) $totals['lektor']['count'] === 1
                && (int) $totals['lektor kepala']['count'] === 1
                && (int) $totals['profesor']['count'] === 2;
        });

        $chart = collect($response->viewData('jurusanJabatanChart'))->keyBy('jurusan_id');

        $sipil = collect($chart[1]['distribution'])->keyBy('key');
        $kimia = collect($chart[3]['distribution'])->keyBy('key');
        $tik = collect($chart[6]['distribution'])->keyBy('key');

        $this->assertSame(2, (int) $chart[1]['total']);
        $this->assertSame(1, (int) $sipil['asisten ahli']['count']);
        $this->assertSame(1, (int) $sipil['lektor']['count']);

        $this->assertSame(1, (int) $chart[3]['total']);
        $this->assertSame(1, (int) $kimia['lektor kepala']['count']);

        $this->assertSame(2, (int) $chart[6]['total']);
        $this->assertSame('TIK', $chart[6]['jurusan_label']);
        $this->assertSame(2, (int) $tik['profesor']['count']);

        $this->assertDatabaseCount('pegawais', 6);
        $this->assertDatabaseCount('dokumen_pegawai', 2);
        $this->assertDatabaseCount('layanans', 2);
    }

    protected function seedDashboardReferenceData(): void
    {
        DB::table('perguruan_tinggis')->insert([
            'id' => 1,
            'perguruan_tinggi' => 'Politeknik Negeri Lhokseumawe',
        ]);

        DB::table('jurusans')->insert([
            ['id' => 1, 'jurusan' => 'Sipil', 'perguruan_tinggi_id' => 1],
            ['id' => 2, 'jurusan' => 'Mesin', 'perguruan_tinggi_id' => 1],
            ['id' => 3, 'jurusan' => 'Kimia', 'perguruan_tinggi_id' => 1],
            ['id' => 4, 'jurusan' => 'Elektro', 'perguruan_tinggi_id' => 1],
            ['id' => 5, 'jurusan' => 'Tata Niaga', 'perguruan_tinggi_id' => 1],
            ['id' => 6, 'jurusan' => 'TIK', 'perguruan_tinggi_id' => 1],
            ['id' => 7, 'jurusan' => 'Other', 'perguruan_tinggi_id' => 1],
        ]);

        DB::table('program_studis')->insert([
            [
                'id' => 1,
                'kode_prodi' => 'SIP01',
                'nama_prodi' => 'Sipil Prodi',
                'tanggal_berdiri' => '2000-01-01',
                'status' => 'Aktif',
                'jenjang' => 'D4',
                'akreditasi' => 'A',
                'jurusan_id' => 1,
            ],
            [
                'id' => 2,
                'kode_prodi' => 'MSN01',
                'nama_prodi' => 'Mesin Prodi',
                'tanggal_berdiri' => '2000-01-01',
                'status' => 'Aktif',
                'jenjang' => 'D4',
                'akreditasi' => 'A',
                'jurusan_id' => 2,
            ],
            [
                'id' => 3,
                'kode_prodi' => 'KIM01',
                'nama_prodi' => 'Kimia Prodi',
                'tanggal_berdiri' => '2000-01-01',
                'status' => 'Aktif',
                'jenjang' => 'D4',
                'akreditasi' => 'A',
                'jurusan_id' => 3,
            ],
            [
                'id' => 4,
                'kode_prodi' => 'ELK01',
                'nama_prodi' => 'Elektro Prodi',
                'tanggal_berdiri' => '2000-01-01',
                'status' => 'Aktif',
                'jenjang' => 'D4',
                'akreditasi' => 'A',
                'jurusan_id' => 4,
            ],
            [
                'id' => 5,
                'kode_prodi' => 'TNI01',
                'nama_prodi' => 'Tata Niaga Prodi',
                'tanggal_berdiri' => '2000-01-01',
                'status' => 'Aktif',
                'jenjang' => 'D4',
                'akreditasi' => 'A',
                'jurusan_id' => 5,
            ],
            [
                'id' => 6,
                'kode_prodi' => 'TIK01',
                'nama_prodi' => 'TIK Prodi',
                'tanggal_berdiri' => '2000-01-01',
                'status' => 'Aktif',
                'jenjang' => 'D4',
                'akreditasi' => 'A',
                'jurusan_id' => 6,
            ],
            [
                'id' => 7,
                'kode_prodi' => 'OTH01',
                'nama_prodi' => 'Other Prodi',
                'tanggal_berdiri' => '2000-01-01',
                'status' => 'Aktif',
                'jenjang' => 'D4',
                'akreditasi' => 'A',
                'jurusan_id' => 7,
            ],
        ]);
    }

    protected function insertPegawai(string $nip, string $nama, int $programStudiId, ?string $jabatanFungsional = null): int
    {
        $pegawaiId = (int) DB::table('pegawais')->insertGetId([
            'nip' => $nip,
            'nama' => $nama,
            'program_studi_id' => $programStudiId,
            'status_pegawai' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($jabatanFungsional !== null) {
            DB::table('pegawai_identitas')->insert([
                'pegawai_id' => $pegawaiId,
                'jabatan_fungsional' => $jabatanFungsional,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $pegawaiId;
    }
}
