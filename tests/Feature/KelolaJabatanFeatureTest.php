<?php

namespace Tests\Feature;

use App\Models\CutiLayananPegawai;
use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\Layanan;
use App\Models\LayananPegawai;
use App\Models\Pangkat;
use App\Models\PejabatCutiSetting;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use Database\Seeders\CutiWorkflowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class KelolaJabatanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $kepegawaian;
    protected User $pegawaiUser;
    protected UnitKerja $unitKerja;
    protected JenisJabatan $jenisJabatan;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'kepegawaian']);
        Role::create(['name' => 'pimpinan']);
        Role::create(['name' => 'atasan']);
        Role::create(['name' => 'pegawai']);

        $this->unitKerja = UnitKerja::create([
            'unit_kerja' => 'Jurusan Teknologi Informasi dan Komputer',
        ]);

        $this->jenisJabatan = JenisJabatan::create([
            'jenis_jabatan' => 'Jabatan Struktural',
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        $this->kepegawaian = User::factory()->create();
        $this->kepegawaian->assignRole('kepegawaian');

        $this->pegawaiUser = User::factory()->create();
        $this->pegawaiUser->assignRole('pegawai');
    }

    private function createJabatan(array $attributes = []): Jabatan
    {
        $jabatan = Jabatan::create([
            'jabatan' => $attributes['jabatan'] ?? 'Nama Jabatan',
            'kode_jabatan' => $attributes['kode_jabatan'] ?? null,
            'kelas_jabatan' => $attributes['kelas_jabatan'] ?? null,
            'pangkat_minimal' => $attributes['pangkat_minimal'] ?? null,
            'pendidikan_minimal' => $attributes['pendidikan_minimal'] ?? null,
            'kompetensi' => $attributes['kompetensi'] ?? null,
            'ikhtisar_jabatan' => $attributes['ikhtisar_jabatan'] ?? null,
            'uraian_tugas' => $attributes['uraian_tugas'] ?? null,
            'tanggung_jawab' => $attributes['tanggung_jawab'] ?? null,
            'wewenang' => $attributes['wewenang'] ?? null,
            'persyaratan_jabatan' => $attributes['persyaratan_jabatan'] ?? null,
            'beban_kerja' => $attributes['beban_kerja'] ?? null,
        ]);

        $jabatan->peta_jabatan()->create([
            'unit_kerja_id' => $attributes['unit_kerja_id'] ?? null,
            'atasan_langsung_id' => $attributes['atasan_langsung_id'] ?? null,
            'kebutuhan_pegawai' => $attributes['kebutuhan_pegawai'] ?? 1,
        ]);

        return $jabatan;
    }

    /** @test */
    public function super_admin_and_kepegawaian_can_view_kelola_jabatan_list()
    {
        $jabatan = $this->createJabatan([
            'jabatan' => 'Ketua Jurusan TIK',
            'kode_jabatan' => 'KAJUR-TIK',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 1,
        ]);

        foreach ([$this->superAdmin, $this->kepegawaian] as $user) {
            $this->actingAs($user)
                ->get(route('peta-jabatan.manage.index', ['slug' => 'jabatan']))
                ->assertOk()
                ->assertSee('Kelola Jabatan')
                ->assertSee('Ketua Jurusan TIK')
                ->assertSee('KAJUR-TIK');
        }
    }

    /** @test */
    public function manager_can_create_new_jabatan_with_all_attributes()
    {
        $pangkat = Pangkat::create([
            'pangkat' => 'Penata',
            'golongan_ruang' => 'III/c',
        ]);

        $atasan = $this->createJabatan([
            'jabatan' => 'Direktur',
            'kode_jabatan' => 'DIR-01',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 1,
        ]);

        $this->actingAs($this->kepegawaian)
            ->get(route('peta-jabatan.manage.create', ['slug' => 'jabatan']))
            ->assertOk()
            ->assertSee('Tambah Jabatan');

        $response = $this->actingAs($this->kepegawaian)
            ->post(route('peta-jabatan.manage.store', ['slug' => 'jabatan']), [
                'jabatan' => 'Sekretaris Jurusan TIK',
                'kode_jabatan' => 'SEKJUR-TIK',
                'jenis_jabatan_id' => $this->jenisJabatan->id,
                'unit_kerja_id' => $this->unitKerja->id,
                'atasan_langsung_id' => $atasan->id,
                'kebutuhan_pegawai' => 1,
                'status_jabatan' => 'Aktif',
                'jenjang_jabatan' => 'Administrator',
                'kelas_jabatan' => 10,
                'pangkat_minimal' => $pangkat->id,
                'pendidikan_minimal' => 'S-2 Ilmu Komputer',
                'kompetensi' => 'Manajemen operasional jurusan',
                'ikhtisar_jabatan' => 'Membantu ketua jurusan dalam urusan administrasi dan akademik',
                'uraian_tugas' => 'Menyusun jadwal kuliah dan mengkoordinasikan kegiatan laboratorium',
                'tanggung_jawab' => 'Kelancaran proses perkuliahan',
                'wewenang' => 'Menandatangani surat pengantar administrasi',
                'persyaratan_jabatan' => 'Dosen tetap aktif minimal 2 tahun',
                'beban_kerja' => '1200 Jam/Tahun',
            ]);

        $newJabatan = Jabatan::where('kode_jabatan', 'SEKJUR-TIK')->first();
        $this->assertNotNull($newJabatan);
        $this->assertEquals('Sekretaris Jurusan TIK', $newJabatan->jabatan);
        $this->assertEquals($this->jenisJabatan->id, $newJabatan->jenis_jabatan_id);
        $this->assertEquals('Administrator', $newJabatan->jenjang_jabatan);
        $this->assertEquals('Aktif', $newJabatan->status_jabatan);
        $this->assertEquals($atasan->id, $newJabatan->atasan_langsung_id);
        $this->assertEquals(10, $newJabatan->kelas_jabatan);
        $this->assertEquals($pangkat->id, $newJabatan->pangkat_minimal);

        $response->assertRedirect(route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $newJabatan->id]));
    }

    /** @test */
    public function manager_can_view_detail_of_jabatan()
    {
        $atasan = $this->createJabatan([
            'jabatan' => 'Direktur',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 1,
        ]);

        $jabatan = $this->createJabatan([
            'jabatan' => 'Ketua Jurusan TIK',
            'kode_jabatan' => 'KAJUR-TIK',
            'unit_kerja_id' => $this->unitKerja->id,
            'atasan_langsung_id' => $atasan->id,
            'kebutuhan_pegawai' => 1,
            'ikhtisar_jabatan' => 'Memimpin jurusan TIK secara menyeluruh',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]))
            ->assertOk()
            ->assertSee('Ketua Jurusan TIK')
            ->assertSee('Direktur')
            ->assertSee('Memimpin jurusan TIK secara menyeluruh');
    }

    /** @test */
    public function manager_can_update_existing_jabatan()
    {
        $jabatan = $this->createJabatan([
            'jabatan' => 'Dosen Pemula',
            'kode_jabatan' => 'DOSEN-01',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 5,
        ]);

        $this->actingAs($this->kepegawaian)
            ->get(route('peta-jabatan.manage.edit', ['slug' => 'jabatan', 'id' => $jabatan->id]))
            ->assertOk()
            ->assertSee('Dosen Pemula');

        $response = $this->actingAs($this->kepegawaian)
            ->put(route('peta-jabatan.manage.update', ['slug' => 'jabatan', 'id' => $jabatan->id]), [
                'jabatan' => 'Dosen Ahli Pertama',
                'kode_jabatan' => 'DOSEN-AHLI-1',
                'jenis_jabatan_id' => $this->jenisJabatan->id,
                'unit_kerja_id' => $this->unitKerja->id,
                'kebutuhan_pegawai' => 10,
                'jenjang_jabatan' => 'Ahli Pertama',
                'status_jabatan' => 'Aktif',
            ]);

        $response->assertRedirect(route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]));

        $jabatan->refresh();
        $this->assertEquals('Dosen Ahli Pertama', $jabatan->jabatan);
        $this->assertEquals('DOSEN-AHLI-1', $jabatan->kode_jabatan);
        $this->assertEquals($this->jenisJabatan->id, $jabatan->jenis_jabatan_id);
        $this->assertEquals('Ahli Pertama', $jabatan->jenjang_jabatan);
        $this->assertEquals('Aktif', $jabatan->status_jabatan);
        $this->assertEquals(10, $jabatan->kebutuhan_pegawai);
    }

    /** @test */
    public function kode_jabatan_and_unit_kerja_are_required_when_creating_or_updating_jabatan()
    {
        $responseCreate = $this->actingAs($this->kepegawaian)
            ->post(route('peta-jabatan.manage.store', ['slug' => 'jabatan']), [
                'jabatan' => 'Jabatan Tanpa Kode',
                'kode_jabatan' => '',
                'unit_kerja_id' => '',
            ]);

        $responseCreate->assertSessionHasErrors(['kode_jabatan', 'unit_kerja_id']);

        $jabatan = $this->createJabatan([
            'jabatan' => 'Jabatan Uji',
            'kode_jabatan' => 'JAB-UJI',
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $responseUpdate = $this->actingAs($this->kepegawaian)
            ->put(route('peta-jabatan.manage.update', ['slug' => 'jabatan', 'id' => $jabatan->id]), [
                'jabatan' => 'Jabatan Uji Edit',
                'kode_jabatan' => '',
                'unit_kerja_id' => '',
            ]);

        $responseUpdate->assertSessionHasErrors(['kode_jabatan', 'unit_kerja_id']);
    }

    /** @test */
    public function jabatan_cannot_be_its_own_supervisor()
    {
        $jabatan = $this->createJabatan([
            'jabatan' => 'Ketua Jurusan',
            'kode_jabatan' => 'KAJUR-01',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 1,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->from(route('peta-jabatan.manage.edit', ['slug' => 'jabatan', 'id' => $jabatan->id]))
            ->put(route('peta-jabatan.manage.update', ['slug' => 'jabatan', 'id' => $jabatan->id]), [
                'jabatan' => 'Ketua Jurusan',
                'kode_jabatan' => 'KAJUR-01',
                'unit_kerja_id' => $this->unitKerja->id,
                'jenis_jabatan_id' => $this->jenisJabatan->id,
                'atasan_langsung_id' => $jabatan->id, // self assignment
            ]);

        $response->assertSessionHasErrors('atasan_langsung_id');
    }

    /** @test */
    public function manager_can_delete_unassigned_jabatan()
    {
        $jabatan = $this->createJabatan([
            'jabatan' => 'Jabatan Sementara',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 0,
        ]);

        $response = $this->actingAs($this->kepegawaian)
            ->delete(route('peta-jabatan.manage.destroy', ['slug' => 'jabatan', 'id' => $jabatan->id]));

        $response->assertRedirect(route('peta-jabatan.manage.index', ['slug' => 'jabatan']));
        $this->assertSoftDeleted('jabatans', ['id' => $jabatan->id]);
    }

    /** @test */
    public function manager_cannot_delete_jabatan_with_assigned_pegawais()
    {
        $jabatan = $this->createJabatan([
            'jabatan' => 'Ketua Jurusan',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 1,
        ]);

        Pegawai::create([
            'nama' => 'Dosen A',
            'nip' => '198001012010011001',
            'jabatan_id' => $jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('peta-jabatan.manage.destroy', ['slug' => 'jabatan', 'id' => $jabatan->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('jabatans', ['id' => $jabatan->id]);
    }

    /** @test */
    public function cuti_workflow_seeder_provisions_all_leave_stages_and_pybmc_setting()
    {
        $this->seed(CutiWorkflowSeeder::class);

        // Verify PYBMC setting active
        $activePybmc = PejabatCutiSetting::getActivePybmc();
        $this->assertNotNull($activePybmc);
        $this->assertNotNull($activePybmc->pegawai);

        // Verify seeded leave requests
        $usulanRequests = LayananPegawai::where('status', LayananPegawai::STATUS_USULAN)->get();
        $prosesRequests = LayananPegawai::where('status', LayananPegawai::STATUS_PROSES)->get();
        $selesaiRequests = LayananPegawai::where('status', LayananPegawai::STATUS_SELESAI)->get();
        $ditolakRequests = LayananPegawai::where('status', LayananPegawai::STATUS_DITOLAK)->get();

        $this->assertNotEmpty($usulanRequests);
        $this->assertNotEmpty($prosesRequests);
        $this->assertNotEmpty($selesaiRequests);
        $this->assertNotEmpty($ditolakRequests);

        // Verify supervisor (Salahuddin) can see subordinate leave in Atasan approval queue
        $userSalahuddin = User::where('email', 'salahuddintik@pnl.ac.id')->first();
        $this->assertNotNull($userSalahuddin);

        $response = $this->actingAs($userSalahuddin)
            ->get(route('cuti.approval.atasan.index'));

        $response->assertOk();
        $response->assertSee('Muhammad Davi');

        // Verify PYBMC (Direktur) can see stage 2 leave in PYBMC approval queue
        $userDirektur = User::where('email', 'direktur@pnl.ac.id')->first();
        $this->assertNotNull($userDirektur);

        $responsePybmc = $this->actingAs($userDirektur)
            ->get(route('cuti.approval.pybmc.index'));

        $responsePybmc->assertOk();
        $responsePybmc->assertSee('Jamilah');

        // Verify print form on finished leave request
        $finishedRequest = $selesaiRequests->first();
        $this->assertNotNull($finishedRequest);

        $responsePrint = $this->actingAs($finishedRequest->pengusul)
            ->get(route('pegawai.layanan.cuti.print', $finishedRequest->id));

        $responsePrint->assertOk();
        $responsePrint->assertSee('SALAHUDDIN');
    }

    /** @test */
    public function kelola_jabatan_list_is_ordered_by_highest_level()
    {
        $pangkatHigh = Pangkat::create(['pangkat' => 'Pembina Utama', 'golongan_ruang' => 'IV/e']);
        $pangkatLow = Pangkat::create(['pangkat' => 'Pengatur Muda', 'golongan_ruang' => 'II/a']);

        $jabatanPelaksana = $this->createJabatan([
            'jabatan' => 'Pengadministrasi Umum',
            'kelas_jabatan' => 5,
            'pangkat_minimal' => $pangkatLow->id,
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $jabatanDirektur = $this->createJabatan([
            'jabatan' => 'Direktur Utama',
            'kelas_jabatan' => 15,
            'pangkat_minimal' => $pangkatHigh->id,
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $jabatanWadir = $this->createJabatan([
            'jabatan' => 'Wakil Direktur',
            'kelas_jabatan' => 14,
            'pangkat_minimal' => $pangkatHigh->id,
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $jabatanKajur = $this->createJabatan([
            'jabatan' => 'Ketua Jurusan',
            'kelas_jabatan' => 12,
            'pangkat_minimal' => $pangkatHigh->id,
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('peta-jabatan.manage.index', ['slug' => 'jabatan']));

        $response->assertOk();

        $rows = $response->viewData('rows');
        $this->assertNotEmpty($rows);

        // First item should be the highest level (Direktur Utama with kelas 15)
        $this->assertSame('Direktur Utama', $rows->first()->jabatan);
        $this->assertEquals(15, $rows->first()->kelas_jabatan);

        // Verify order of items in rows collection
        $jabatansInOrder = $rows->pluck('jabatan')->all();
        $idxDirektur = array_search('Direktur Utama', $jabatansInOrder);
        $idxWadir = array_search('Wakil Direktur', $jabatansInOrder);
        $idxKajur = array_search('Ketua Jurusan', $jabatansInOrder);
        $idxPelaksana = array_search('Pengadministrasi Umum', $jabatansInOrder);

        $this->assertTrue($idxDirektur < $idxWadir);
        $this->assertTrue($idxWadir < $idxKajur);
        $this->assertTrue($idxKajur < $idxPelaksana);
    }

    /** @test */
    public function generate_kode_endpoint_returns_expected_code_for_unit_and_jenis()
    {
        $unitTIK = UnitKerja::create(['unit_kerja' => 'Jurusan Teknologi Informasi dan Komputer']);
        $jenisStruktural = JenisJabatan::create(['jenis_jabatan' => 'Jabatan Struktural']);
        $jenisFungsional = JenisJabatan::create(['jenis_jabatan' => 'Jabatan Fungsional Tertentu']);
        $jenisPelaksana = JenisJabatan::create(['jenis_jabatan' => 'Jabatan Pelaksana']);

        // First code for JTIK - JS should be JTIK-JS01
        $response1 = $this->actingAs($this->kepegawaian)
            ->getJson(route('peta-jabatan.manage.generate-kode', [
                'unit_kerja_id' => $unitTIK->id,
                'jenis_jabatan_id' => $jenisStruktural->id,
            ]));

        $response1->assertOk()
            ->assertJson(['success' => true, 'kode' => 'JTIK-JS01']);

        // Create a jabatan with JTIK-JS01
        $this->createJabatan([
            'jabatan' => 'Ketua Jurusan TIK',
            'kode_jabatan' => 'JTIK-JS01',
            'unit_kerja_id' => $unitTIK->id,
            'jenis_jabatan_id' => $jenisStruktural->id,
        ]);

        // Next code should be JTIK-JS02
        $response2 = $this->actingAs($this->kepegawaian)
            ->getJson(route('peta-jabatan.manage.generate-kode', [
                'unit_kerja_id' => $unitTIK->id,
                'jenis_jabatan_id' => $jenisStruktural->id,
            ]));

        $response2->assertOk()
            ->assertJson(['success' => true, 'kode' => 'JTIK-JS02']);

        // Test Fungsional (JF) and Pelaksana (JP)
        $responseJF = $this->actingAs($this->kepegawaian)
            ->getJson(route('peta-jabatan.manage.generate-kode', [
                'unit_kerja_id' => $unitTIK->id,
                'jenis_jabatan_id' => $jenisFungsional->id,
            ]));

        $responseJF->assertOk()
            ->assertJson(['success' => true, 'kode' => 'JTIK-JF01']);

        $responseJP = $this->actingAs($this->kepegawaian)
            ->getJson(route('peta-jabatan.manage.generate-kode', [
                'unit_kerja_id' => $unitTIK->id,
                'jenis_jabatan_id' => $jenisPelaksana->id,
            ]));

        $responseJP->assertOk()
            ->assertJson(['success' => true, 'kode' => 'JTIK-JP01']);
    }
}
