<?php

namespace Tests\Feature;

use App\Models\CutiLayananPegawai;
use App\Models\Layanan;
use App\Models\LayananPegawai;
use App\Models\Pegawai;
use App\Models\User;
use App\Services\CutiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsnCutiRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-27');

        \Illuminate\Support\Facades\DB::table('kedudukan_pegawais')->insertOrIgnore([
            ['id' => '01', 'kedudukan_pegawai' => 'Aktif'],
            ['id' => '02', 'kedudukan_pegawai' => 'Cuti di Luar Tanggungan Negara'],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createPnsPegawai(array $attributes = []): Pegawai
    {
        $user = User::factory()->create();
        $n2 = $attributes['cuti_n_2'] ?? 0;
        $n1 = $attributes['cuti_n_1'] ?? 0;
        $n = $attributes['cuti_n'] ?? 12;
        unset($attributes['cuti_n_2'], $attributes['cuti_n_1'], $attributes['cuti_n']);

        $pegawai = Pegawai::create(array_merge([
            'user_id' => $user->id,
            'nama' => 'Dr. Budi Santoso, M.Si',
            'nip' => '1985' . str_pad((string) mt_rand(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT),
            'status_pegawai' => 'PNS',
            'tmt_pns' => '2015-01-01',
            'status_payroll' => Pegawai::PAYROLL_AKTIF,
            'kedudukan_pegawai_id' => '01',
        ], $attributes));

        \App\Models\PegawaiCutiQuota::create(['pegawai_id' => $pegawai->id, 'tahun' => 2024, 'hari_tersedia' => $n2]);
        \App\Models\PegawaiCutiQuota::create(['pegawai_id' => $pegawai->id, 'tahun' => 2025, 'hari_tersedia' => $n1]);
        \App\Models\PegawaiCutiQuota::create(['pegawai_id' => $pegawai->id, 'tahun' => 2026, 'hari_tersedia' => $n]);

        return $pegawai;
    }

    private function createPppkPegawai(array $attributes = []): Pegawai
    {
        $user = User::factory()->create();
        $n2 = $attributes['cuti_n_2'] ?? 0;
        $n1 = $attributes['cuti_n_1'] ?? 0;
        $n = $attributes['cuti_n'] ?? 12;
        unset($attributes['cuti_n_2'], $attributes['cuti_n_1'], $attributes['cuti_n']);

        $pegawai = Pegawai::create(array_merge([
            'user_id' => $user->id,
            'nama' => 'Siti Rahma, S.Pd',
            'nip' => '1992' . str_pad((string) mt_rand(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT),
            'status_pegawai' => 'PPPK',
            'tmt_pppk' => '2024-01-01',
            'tanggal_akhir_kontrak' => '2027-12-31',
            'status_payroll' => Pegawai::PAYROLL_AKTIF,
            'kedudukan_pegawai_id' => '01',
        ], $attributes));

        \App\Models\PegawaiCutiQuota::create(['pegawai_id' => $pegawai->id, 'tahun' => 2024, 'hari_tersedia' => $n2]);
        \App\Models\PegawaiCutiQuota::create(['pegawai_id' => $pegawai->id, 'tahun' => 2025, 'hari_tersedia' => $n1]);
        \App\Models\PegawaiCutiQuota::create(['pegawai_id' => $pegawai->id, 'tahun' => 2026, 'hari_tersedia' => $n]);

        return $pegawai;
    }

    public function test_available_jenis_cuti_segmentation_pns_vs_pppk(): void
    {
        $pns = $this->createPnsPegawai();
        $pppk = $this->createPppkPegawai();

        $pnsAllowed = CutiService::getAvailableJenisCutiFor($pns);
        $pppkAllowed = CutiService::getAvailableJenisCutiFor($pppk);

        $this->assertContains(CutiService::JENIS_TAHUNAN, $pnsAllowed);
        $this->assertContains(CutiService::JENIS_BESAR, $pnsAllowed);
        $this->assertContains(CutiService::JENIS_SAKIT, $pnsAllowed);
        $this->assertContains(CutiService::JENIS_MELAHIRKAN, $pnsAllowed);
        $this->assertContains(CutiService::JENIS_ALASAN_PENTING, $pnsAllowed);
        $this->assertContains(CutiService::JENIS_CLTN, $pnsAllowed);

        $this->assertContains(CutiService::JENIS_TAHUNAN, $pppkAllowed);
        $this->assertContains(CutiService::JENIS_SAKIT, $pppkAllowed);
        $this->assertContains(CutiService::JENIS_MELAHIRKAN, $pppkAllowed);
        $this->assertNotContains(CutiService::JENIS_BESAR, $pppkAllowed);
        $this->assertNotContains(CutiService::JENIS_ALASAN_PENTING, $pppkAllowed);
        $this->assertNotContains(CutiService::JENIS_CLTN, $pppkAllowed);
    }

    public function test_cuti_tahunan_tenure_rules_and_pppk_bypass(): void
    {
        $cutiService = app(CutiService::class);

        // PNS with < 1 year tenure is ineligible
        $pnsJunior = $this->createPnsPegawai(['tmt_pns' => '2026-06-01']);
        $resPnsJunior = $cutiService->checkCutiEligibility($pnsJunior, CutiService::JENIS_TAHUNAN, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-05',
        ]);
        $this->assertFalse($resPnsJunior['eligible']);

        // PPPK with < 1 year without valid bypass is ineligible
        $pppkJunior = $this->createPppkPegawai(['tmt_pppk' => '2026-06-01']);
        $resPppkJuniorNoBypass = $cutiService->checkCutiEligibility($pppkJunior, CutiService::JENIS_TAHUNAN, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-05',
        ]);
        $this->assertFalse($resPppkJuniorNoBypass['eligible']);

        // PPPK with < 1 year with PERKAWINAN_PERTAMA <= 6 days is eligible
        $resPppkJuniorBypassValid = $cutiService->checkCutiEligibility($pppkJunior, CutiService::JENIS_TAHUNAN, [
            'tanggal_mulai' => '2026-10-05', // Mon
            'tanggal_selesai' => '2026-10-09', // Fri (5 work days)
            'alasan_pppk_bypass' => CutiLayananPegawai::ALASAN_PPPK_PERKAWINAN_PERTAMA,
        ]);
        $this->assertTrue($resPppkJuniorBypassValid['eligible']);

        // PPPK with < 1 year with bypass > 6 days is rejected
        $resPppkJuniorBypassOverlimit = $cutiService->checkCutiEligibility($pppkJunior, CutiService::JENIS_TAHUNAN, [
            'tanggal_mulai' => '2026-10-05', // Mon
            'tanggal_selesai' => '2026-10-15', // 9 work days
            'alasan_pppk_bypass' => CutiLayananPegawai::ALASAN_PPPK_PERKAWINAN_PERTAMA,
        ]);
        $this->assertFalse($resPppkJuniorBypassOverlimit['eligible']);
    }

    public function test_cuti_besar_eligibility_and_reset_kuota_n_side_effect(): void
    {
        $cutiService = app(CutiService::class);

        // PNS with < 5 years tenure is ineligible
        $pns4th = $this->createPnsPegawai(['tmt_pns' => '2023-01-01']);
        $resPns4th = $cutiService->checkCutiEligibility($pns4th, CutiService::JENIS_BESAR, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-01',
        ]);
        $this->assertFalse($resPns4th['eligible']);

        // PNS with >= 5 years is eligible
        $pnsSenior = $this->createPnsPegawai([
            'tmt_pns' => '2015-01-01',
            'cuti_n' => 12,
        ]);
        $resPnsSenior = $cutiService->checkCutiEligibility($pnsSenior, CutiService::JENIS_BESAR, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-01',
        ]);
        $this->assertTrue($resPnsSenior['eligible']);

        // Test side-effect: reset quota N to 0 on completion
        $cutiDetail = new CutiLayananPegawai([
            'jenis_cuti' => CutiService::JENIS_BESAR,
            'hari_diminta' => 30,
        ]);
        $cutiService->handleCutiCompletedSideEffects($pnsSenior, $cutiDetail);
        $pnsSenior->refresh();
        $this->assertEquals(0, $pnsSenior->getCutiQuotaForYear(2026));
    }

    public function test_cuti_sakit_rules_and_limits(): void
    {
        $cutiService = app(CutiService::class);

        // Gugur kandungan max 45 calendar days
        $pns = $this->createPnsPegawai();
        $resGugurValid = $cutiService->checkCutiEligibility($pns, CutiService::JENIS_SAKIT, [
            'kategori_cuti' => CutiLayananPegawai::KATEGORI_SAKIT_GUGUR_KANDUNGAN,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-10', // 41 calendar days
        ]);
        $this->assertTrue($resGugurValid['eligible']);

        $resGugurOver = $cutiService->checkCutiEligibility($pns, CutiService::JENIS_SAKIT, [
            'kategori_cuti' => CutiLayananPegawai::KATEGORI_SAKIT_GUGUR_KANDUNGAN,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-20', // 51 calendar days
        ]);
        $this->assertFalse($resGugurOver['eligible']);

        // PPPK hard limit 30 working days
        $pppk = $this->createPppkPegawai();
        $resPppkSakitOver = $cutiService->checkCutiEligibility($pppk, CutiService::JENIS_SAKIT, [
            'kategori_cuti' => CutiLayananPegawai::KATEGORI_SAKIT_REGULER,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-12-01', // > 30 work days
        ]);
        $this->assertFalse($resPppkSakitOver['eligible']);

        // PPPK Kecelakaan kerja must not exceed contract end date
        $resPppkKecelakaanOver = $cutiService->checkCutiEligibility($pppk, CutiService::JENIS_SAKIT, [
            'kategori_cuti' => CutiLayananPegawai::KATEGORI_SAKIT_KECELAKAAN_KERJA,
            'tanggal_mulai' => '2027-12-01',
            'tanggal_selesai' => '2028-01-15', // exceeds contract 2027-12-31
        ]);
        $this->assertFalse($resPppkKecelakaanOver['eligible']);
    }

    public function test_cuti_melahirkan_child_count_and_redirection(): void
    {
        $cutiService = app(CutiService::class);
        $pns = $this->createPnsPegawai();
        $pppk = $this->createPppkPegawai();

        // PNS child <= 3 is valid
        $resPnsChild3 = $cutiService->checkCutiEligibility($pns, CutiService::JENIS_MELAHIRKAN, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-12-25',
            'kelahiran_anak_ke' => 3,
        ]);
        $this->assertTrue($resPnsChild3['eligible']);

        // PNS child >= 4 redirected to Cuti Besar Melahirkan
        $resPnsChild4 = $cutiService->checkCutiEligibility($pns, CutiService::JENIS_MELAHIRKAN, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-12-25',
            'kelahiran_anak_ke' => 4,
        ]);
        $this->assertFalse($resPnsChild4['eligible']);
        $this->assertStringContainsString('Cuti Besar Melahirkan', $resPnsChild4['reason']);

        // PPPK child <= 3 valid, child 4 rejected
        $resPppkChild4 = $cutiService->checkCutiEligibility($pppk, CutiService::JENIS_MELAHIRKAN, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-12-25',
            'kelahiran_anak_ke' => 4,
        ]);
        $this->assertFalse($resPppkChild4['eligible']);
    }

    public function test_cuti_alasan_penting_validation(): void
    {
        $cutiService = app(CutiService::class);
        $pns = $this->createPnsPegawai();

        // Missing valid reason
        $resNoReason = $cutiService->checkCutiEligibility($pns, CutiService::JENIS_ALASAN_PENTING, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-10',
            'alasan_cap' => null,
        ]);
        $this->assertFalse($resNoReason['eligible']);

        // Valid reason <= 30 days
        $resValid = $cutiService->checkCutiEligibility($pns, CutiService::JENIS_ALASAN_PENTING, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-15',
            'alasan_cap' => CutiLayananPegawai::ALASAN_CAP_PERKAWINAN_PERTAMA,
        ]);
        $this->assertTrue($resValid['eligible']);

        // Exceeding 30 days
        $resOver = $cutiService->checkCutiEligibility($pns, CutiService::JENIS_ALASAN_PENTING, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-15', // 46 calendar days
            'alasan_cap' => CutiLayananPegawai::ALASAN_CAP_PERKAWINAN_PERTAMA,
        ]);
        $this->assertFalse($resOver['eligible']);
    }

    public function test_cltn_tenure_and_payroll_status_side_effects(): void
    {
        $cutiService = app(CutiService::class);
        $pns = $this->createPnsPegawai([
            'tmt_pns' => '2015-01-01',
            'status_payroll' => Pegawai::PAYROLL_AKTIF,
            'kedudukan_pegawai_id' => '01',
        ]);

        // Eligibility check
        $res = $cutiService->checkCutiEligibility($pns, CutiService::JENIS_CLTN, [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2027-10-01',
        ]);
        $this->assertTrue($res['eligible']);

        // Side effect on completed: freeze payroll and kedudukan 02
        $cutiDetail = new CutiLayananPegawai([
            'jenis_cuti' => CutiService::JENIS_CLTN,
            'hari_diminta' => 365,
        ]);
        $cutiService->handleCutiCompletedSideEffects($pns, $cutiDetail);
        $pns->refresh();
        $this->assertEquals(Pegawai::PAYROLL_NON_AKTIF, $pns->status_payroll);
        $this->assertEquals('02', $pns->kedudukan_pegawai_id);

        // Side effect on cancelled: restore payroll and kedudukan 01
        $cutiService->handleCutiCancelledSideEffects($pns, $cutiDetail);
        $pns->refresh();
        $this->assertEquals(Pegawai::PAYROLL_AKTIF, $pns->status_payroll);
        $this->assertEquals('01', $pns->kedudukan_pegawai_id);
    }

    public function test_kompensasi_cuti_bersama_adds_to_annual_leave_saldo(): void
    {
        $cutiService = app(CutiService::class);
        $pns = $this->createPnsPegawai([
            'cuti_n' => 10,
            'kompensasi_cuti_bersama' => 3,
        ]);

        $saldo = $cutiService->getSaldoCuti($pns);
        $this->assertEquals(13, $saldo);
    }
}
