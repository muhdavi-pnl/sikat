<?php

namespace Tests\Feature;

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
use App\Services\CutiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CutiMultiStageApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdminRole;
    protected $kepegawaianRole;
    protected $atasanRole;
    protected $pimpinanRole;
    protected $pegawaiRole;

    protected $unitKerja;
    protected $jenisJabatan;
    protected $jabatanAtasan;
    protected $jabatanBawahan;
    protected $userAtasan;
    protected $pegawaiAtasan;
    protected $userBawahan;
    protected $pegawaiBawahan;
    protected $userPybmc;
    protected $pegawaiPybmc;
    protected $layananCuti;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $this->kepegawaianRole = Role::firstOrCreate(['name' => 'kepegawaian']);
        $this->atasanRole = Role::firstOrCreate(['name' => 'atasan']);
        $this->pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan']);
        $this->pegawaiRole = Role::firstOrCreate(['name' => 'pegawai']);

        $this->unitKerja = UnitKerja::create([
            'unit_kerja' => 'Fakultas Teknik',
            'is_aktif' => true,
        ]);

        $this->jenisJabatan = JenisJabatan::create([
            'jenis_jabatan' => 'Struktural',
        ]);

        // Jabatan Atasan (e.g. Dekan / Ketua Jurusan)
        $this->jabatanAtasan = Jabatan::create([
            'jabatan' => 'Ketua Jurusan Informatika',
            'kode_jabatan' => 'KAPRODI-IF',
        ]);

        PetaJabatan::create([
            'jabatan_id' => $this->jabatanAtasan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 1,
        ]);

        // Jabatan Bawahan (reports to Jabatan Atasan)
        $this->jabatanBawahan = Jabatan::create([
            'jabatan' => 'Dosen Informatika',
            'kode_jabatan' => 'DOSEN-IF',
        ]);

        PetaJabatan::create([
            'jabatan_id' => $this->jabatanBawahan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'atasan_langsung_id' => $this->jabatanAtasan->id,
            'kebutuhan_pegawai' => 5,
        ]);

        // Jabatan PYBMC (e.g. Rektor / Wakil Rektor)
        $jabatanPybmc = Jabatan::create([
            'jabatan' => 'Rektor',
            'kode_jabatan' => 'REKTOR',
        ]);

        PetaJabatan::create([
            'jabatan_id' => $jabatanPybmc->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 1,
        ]);

        // Pegawai & User Atasan
        $this->userAtasan = User::factory()->create([
            'name' => 'Atasan Langsung',
            'email' => 'atasan@test.com',
        ]);
        $this->userAtasan->assignRole($this->pegawaiRole);
        $this->pegawaiAtasan = Pegawai::create([
            'nama' => 'Dr. Budi Atasan, M.T.',
            'nip' => '198001012005011001',
            'email' => 'atasan@test.com',
            'jabatan_id' => $this->jabatanAtasan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'user_id' => $this->userAtasan->id,
        ]);

        // Pegawai & User Bawahan (Pemohon Cuti)
        $this->userBawahan = User::factory()->create([
            'name' => 'Staf Bawahan',
            'email' => 'bawahan@test.com',
        ]);
        $this->userBawahan->assignRole($this->pegawaiRole);
        $this->pegawaiBawahan = Pegawai::create([
            'nama' => 'Ahmad Subordinate, S.Kom.',
            'nip' => '199501012020011002',
            'email' => 'bawahan@test.com',
            'jabatan_id' => $this->jabatanBawahan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'user_id' => $this->userBawahan->id,
        ]);

        // Pegawai & User PYBMC
        $this->userPybmc = User::factory()->create([
            'name' => 'Prof. Pejabat PYBMC',
            'email' => 'pybmc@test.com',
        ]);
        $this->userPybmc->assignRole($this->pegawaiRole);
        $this->pegawaiPybmc = Pegawai::create([
            'nama' => 'Prof. Dr. Hendra PYBMC, M.Sc.',
            'nip' => '197001011995011001',
            'email' => 'pybmc@test.com',
            'jabatan_id' => $jabatanPybmc->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'user_id' => $this->userPybmc->id,
        ]);
        $this->userPybmc->update(['pegawai_id' => $this->pegawaiPybmc->id]);

        // Layanan Cuti
        $this->layananCuti = Layanan::create([
            'layanan' => 'Layanan Cuti Tahunan Pegawai',
            'deskripsi' => 'Pengajuan cuti tahunan pegawai',
            'jenis' => 'kepegawaian',
        ]);
    }

    public function test_subordinate_leave_request_appears_in_immediate_supervisor_queue()
    {
        $usulan = LayananPegawai::create([
            'layanan_id' => $this->layananCuti->id,
            'pegawai_id' => $this->pegawaiBawahan->id,
            'user_id' => $this->userBawahan->id,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Mohon izin cuti keperluan keluarga.',
        ]);

        CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan->id,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Keperluan keluarga penting',
            'alamat_menjalankan_cuti' => 'Jakarta Barat',
            'nomor_telepon_cuti' => '081234567890',
            'tanggal_mulai' => Carbon::parse('2026-10-05'),
            'tanggal_selesai' => Carbon::parse('2026-10-07'),
            'hari_diminta' => 3,
            'hari_tersedia_saat_usul' => 12,
            'approval_stage' => 'atasan',
        ]);

        $response = $this->actingAs($this->userAtasan)
            ->get(route('cuti.approval.atasan.index'));

        $response->assertOk();
        $response->assertSee($this->pegawaiBawahan->nama);
        $response->assertSee('Ahmad Subordinate');
    }

    public function test_immediate_supervisor_can_approve_and_forward_to_pybmc()
    {
        $usulan = LayananPegawai::create([
            'layanan_id' => $this->layananCuti->id,
            'pegawai_id' => $this->pegawaiBawahan->id,
            'user_id' => $this->userBawahan->id,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Izin cuti 3 hari.',
        ]);

        $cutiDetail = CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan->id,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Acara keluarga',
            'alamat_menjalankan_cuti' => 'Bandung',
            'nomor_telepon_cuti' => '08123456789',
            'tanggal_mulai' => Carbon::parse('2026-10-05'),
            'tanggal_selesai' => Carbon::parse('2026-10-07'),
            'hari_diminta' => 3,
            'hari_tersedia_saat_usul' => 12,
            'approval_stage' => 'atasan',
        ]);

        $response = $this->actingAs($this->userAtasan)
            ->post(route('cuti.approval.atasan.approve', $usulan->id), [
                'atasan_status' => 'disetujui',
                'catatan_atasan' => 'Disetujui untuk diteruskan ke PYBMC.',
            ]);

        $response->assertRedirect(route('cuti.approval.atasan.index'));

        $cutiDetail->refresh();
        $usulan->refresh();

        $this->assertEquals('disetujui', $cutiDetail->atasan_status);
        $this->assertEquals($this->pegawaiAtasan->id, $cutiDetail->atasan_pegawai_id);
        $this->assertEquals($this->userAtasan->id, $cutiDetail->atasan_user_id);
        $this->assertEquals('pybmc', $cutiDetail->approval_stage);
        $this->assertNotNull($cutiDetail->atasan_approved_at);
        $this->assertEquals(LayananPegawai::STATUS_PROSES, $usulan->status);
    }

    public function test_non_supervisor_cannot_approve_other_employees_leave_request()
    {
        $unrelatedUser = User::factory()->create();
        $unrelatedUser->assignRole($this->pegawaiRole);
        $unrelatedPegawai = Pegawai::create([
            'nama' => 'Pegawai Lain',
            'nip' => '199001012015011003',
            'email' => 'lain@test.com',
            'unit_kerja_id' => $this->unitKerja->id,
            'user_id' => $unrelatedUser->id,
        ]);

        $usulan = LayananPegawai::create([
            'layanan_id' => $this->layananCuti->id,
            'pegawai_id' => $this->pegawaiBawahan->id,
            'user_id' => $this->userBawahan->id,
            'status' => LayananPegawai::STATUS_USULAN,
        ]);

        CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan->id,
            'jenis_cuti' => 'tahunan',
            'hari_diminta' => 2,
            'approval_stage' => 'atasan',
        ]);

        $response = $this->actingAs($unrelatedUser)
            ->post(route('cuti.approval.atasan.approve', $usulan->id), [
                'atasan_status' => 'disetujui',
            ]);

        $response->assertStatus(403);
    }

    public function test_immediate_supervisor_rejection_marks_status_as_ditolak()
    {
        $usulan = LayananPegawai::create([
            'layanan_id' => $this->layananCuti->id,
            'pegawai_id' => $this->pegawaiBawahan->id,
            'user_id' => $this->userBawahan->id,
            'status' => LayananPegawai::STATUS_USULAN,
        ]);

        $cutiDetail = CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan->id,
            'jenis_cuti' => 'tahunan',
            'hari_diminta' => 5,
            'approval_stage' => 'atasan',
        ]);

        $response = $this->actingAs($this->userAtasan)
            ->post(route('cuti.approval.atasan.approve', $usulan->id), [
                'atasan_status' => 'tidak_disetujui',
                'catatan_atasan' => 'Sedang ada akreditasi prodi.',
            ]);

        $response->assertRedirect(route('cuti.approval.atasan.index'));

        $cutiDetail->refresh();
        $usulan->refresh();

        $this->assertEquals('tidak_disetujui', $cutiDetail->atasan_status);
        $this->assertEquals('ditolak', $cutiDetail->approval_stage);
        $this->assertEquals(LayananPegawai::STATUS_DITOLAK, $usulan->status);
        $this->assertEquals('Sedang ada akreditasi prodi.', $usulan->catatan_proses);
    }

    public function test_kepegawaian_can_designate_active_pybmc_manually()
    {
        $userKepegawaian = User::factory()->create([
            'name' => 'Admin Kepegawaian',
            'email' => 'kepegawaian@test.com',
        ]);
        $userKepegawaian->assignRole($this->kepegawaianRole);

        $response = $this->actingAs($userKepegawaian)
            ->post(route('kepegawaian.cuti.pybmc-setting.update'), [
                'pegawai_id' => $this->pegawaiPybmc->id,
                'jabatan_label' => 'Rektor Universitas',
            ]);

        $response->assertRedirect(route('kepegawaian.cuti.pybmc-setting'));

        $this->assertDatabaseHas('pejabat_cuti_settings', [
            'pegawai_id' => $this->pegawaiPybmc->id,
            'jabatan_label' => 'Rektor Universitas',
            'is_active' => true,
        ]);

        $activePybmc = PejabatCutiSetting::getActivePybmc();
        $this->assertNotNull($activePybmc);
        $this->assertEquals($this->pegawaiPybmc->id, $activePybmc->pegawai_id);
    }

    public function test_designated_pybmc_can_give_final_approval_and_complete_leave()
    {
        // Designate PYBMC
        PejabatCutiSetting::create([
            'pegawai_id' => $this->pegawaiPybmc->id,
            'jabatan_label' => 'Rektor',
            'is_active' => true,
        ]);

        $usulan = LayananPegawai::create([
            'layanan_id' => $this->layananCuti->id,
            'pegawai_id' => $this->pegawaiBawahan->id,
            'user_id' => $this->userBawahan->id,
            'status' => LayananPegawai::STATUS_PROSES,
        ]);

        $cutiDetail = CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan->id,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Cuti tahunan resmi',
            'tanggal_mulai' => Carbon::parse('2026-11-02'),
            'tanggal_selesai' => Carbon::parse('2026-11-04'),
            'hari_diminta' => 3,
            'hari_tersedia_saat_usul' => 12,
            'atasan_pegawai_id' => $this->pegawaiAtasan->id,
            'atasan_user_id' => $this->userAtasan->id,
            'atasan_status' => 'disetujui',
            'atasan_approved_at' => now(),
            'approval_stage' => 'pybmc',
        ]);

        $response = $this->actingAs($this->userPybmc)
            ->post(route('cuti.approval.pybmc.approve', $usulan->id), [
                'pybmc_status' => 'disetujui',
                'catatan_pybmc' => 'Disetujui penuh oleh Rektor.',
            ]);

        $response->assertRedirect(route('cuti.approval.pybmc.index'));

        $cutiDetail->refresh();
        $usulan->refresh();

        $this->assertEquals('disetujui', $cutiDetail->pybmc_status);
        $this->assertEquals($this->pegawaiPybmc->id, $cutiDetail->pybmc_pegawai_id);
        $this->assertEquals('selesai', $cutiDetail->approval_stage);
        $this->assertEquals(LayananPegawai::STATUS_SELESAI, $usulan->status);

        // Verify balance updated dynamically
        $cutiService = app(CutiService::class);
        $saldoSisa = $cutiService->getSaldoCuti($this->pegawaiBawahan, 2026);
        $this->assertEquals(21, $saldoSisa); // Initial 24 - 3 = 21
    }

    public function test_print_formulir_cuti_displays_both_atasan_and_pybmc_decisions()
    {
        PejabatCutiSetting::create([
            'pegawai_id' => $this->pegawaiPybmc->id,
            'jabatan_label' => 'Rektor Universitas',
            'is_active' => true,
        ]);

        $usulan = LayananPegawai::create([
            'layanan_id' => $this->layananCuti->id,
            'pegawai_id' => $this->pegawaiBawahan->id,
            'user_id' => $this->userBawahan->id,
            'status' => LayananPegawai::STATUS_SELESAI,
        ]);

        CutiLayananPegawai::create([
            'layanan_pegawai_id' => $usulan->id,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Cuti liburan',
            'tanggal_mulai' => Carbon::parse('2026-10-12'),
            'tanggal_selesai' => Carbon::parse('2026-10-14'),
            'hari_diminta' => 3,
            'hari_tersedia_saat_usul' => 12,
            'atasan_pegawai_id' => $this->pegawaiAtasan->id,
            'atasan_user_id' => $this->userAtasan->id,
            'atasan_status' => 'disetujui',
            'catatan_atasan' => 'Telah diverifikasi atasan',
            'atasan_approved_at' => now(),
            'pybmc_pegawai_id' => $this->pegawaiPybmc->id,
            'pybmc_user_id' => $this->userPybmc->id,
            'pybmc_status' => 'disetujui',
            'catatan_pybmc' => 'Disetujui Rektor',
            'pybmc_approved_at' => now(),
            'approval_stage' => 'selesai',
        ]);

        $response = $this->actingAs($this->userBawahan)
            ->get(route('pegawai.layanan.cuti.print', $usulan->id));

        $response->assertOk();
        $response->assertSee(strtoupper($this->pegawaiAtasan->nama));
        $response->assertSee($this->pegawaiAtasan->nip);
        $response->assertSee('Telah diverifikasi atasan');
        $response->assertSee(strtoupper($this->pegawaiPybmc->nama));
        $response->assertSee($this->pegawaiPybmc->nip);
        $response->assertSee('Disetujui Rektor');
    }
}
