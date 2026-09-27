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
            'kode' => 'JTIK',
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
        $responsePrint->assertSee('Salahuddin');
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
        $unitTIK = UnitKerja::create([
            'unit_kerja' => 'Jurusan Teknologi Informasi dan Komputer',
            'kode' => 'JTIK',
        ]);
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

    /** @test */
    public function pegawai_status_badges_are_differentiated_on_jabatan_detail_page()
    {
        $jabatan = $this->createJabatan([
            'jabatan' => 'Dosen Komputer',
            'kode_jabatan' => 'JTIK-JF01',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 4,
        ]);

        $pegawaiPns = Pegawai::create([
            'nama' => 'Budi PNS',
            'nip' => '198001012000011001',
            'jabatan_id' => $jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        $pegawaiCpns = Pegawai::create([
            'nama' => 'Siti CPNS',
            'nip' => '199501012023012001',
            'jabatan_id' => $jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'status_pegawai' => 'CPNS',
        ]);

        $pegawaiPppk = Pegawai::create([
            'nama' => 'Agus PPPK',
            'nip' => '198501012022011002',
            'jabatan_id' => $jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'status_pegawai' => 'PPPK',
        ]);

        $pegawaiPppkPw = Pegawai::create([
            'nama' => 'Rini PPPK PW',
            'nip' => '199001012024012003',
            'jabatan_id' => $jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'status_pegawai' => 'PPPK Paruh Waktu',
        ]);

        $this->assertEquals('badge-success', $pegawaiPns->status_pegawai_badge_class);
        $this->assertEquals('badge-warning', $pegawaiCpns->status_pegawai_badge_class);
        $this->assertEquals('badge-info', $pegawaiPppk->status_pegawai_badge_class);
        $this->assertEquals('badge-secondary', $pegawaiPppkPw->status_pegawai_badge_class);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]));

        $response->assertOk()
            ->assertSee('Budi PNS')
            ->assertSee('badge-success', false)
            ->assertSee('Siti CPNS')
            ->assertSee('badge-warning', false)
            ->assertSee('Agus PPPK')
            ->assertSee('badge-info', false)
            ->assertSee('Rini PPPK PW')
            ->assertSee('badge-secondary', false);
    }

    /** @test */
    public function unit_kerja_uses_database_kode_column_for_jabatan_code_generation()
    {
        $customUnit = UnitKerja::create([
            'unit_kerja' => 'Laboratorium Rekayasa Perangkat Lunak Terapan',
            'kode' => 'LAB-RPLT',
        ]);

        $this->assertEquals('LAB-RPLT', $customUnit->kode);

        $response = $this->actingAs($this->kepegawaian)
            ->getJson(route('peta-jabatan.manage.generate-kode', [
                'unit_kerja_id' => $customUnit->id,
                'jenis_jabatan_id' => $this->jenisJabatan->id,
            ]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'kode' => 'LAB-RPLT-JS01',
            ]);
    }

    /** @test */
    public function manager_can_search_pegawais_for_jabatan_assignment_modal()
    {
        $pegawai1 = Pegawai::create([
            'nama' => 'Ahmad Zaki',
            'nip' => '198801012015011005',
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $pegawai2 = Pegawai::create([
            'nama' => 'Zubaidah',
            'nip' => '199202022019022008',
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $response = $this->actingAs($this->kepegawaian)
            ->getJson(route('peta-jabatan.manage.options.pegawais', ['q' => '19880101']));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $pegawai1->id,
                'nama' => 'Ahmad Zaki',
                'nip' => '198801012015011005',
            ]);

        $responseNama = $this->actingAs($this->kepegawaian)
            ->getJson(route('peta-jabatan.manage.options.pegawais', ['q' => 'Zubaidah']));

        $responseNama->assertOk()
            ->assertJsonFragment([
                'id' => $pegawai2->id,
                'nama' => 'Zubaidah',
            ]);
    }

    /** @test */
    public function manager_can_assign_pegawai_to_jabatan_via_modal()
    {
        $jabatan = $this->createJabatan([
            'jabatan' => 'Kepala Lab Komputer',
            'unit_kerja_id' => $this->unitKerja->id,
            'kebutuhan_pegawai' => 2,
        ]);

        $pegawai = Pegawai::create([
            'nama' => 'Bambang Staf',
            'nip' => '199105052018011002',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id' => null,
        ]);

        $response = $this->actingAs($this->kepegawaian)
            ->post(route('peta-jabatan.manage.assign-pegawai', ['slug' => 'jabatan', 'id' => $jabatan->id]), [
                'pegawai_id' => $pegawai->id,
            ]);

        $response->assertRedirect(route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]));

        $pegawai->refresh();
        $this->assertEquals($jabatan->id, $pegawai->jabatan_id);

        $showPage = $this->actingAs($this->kepegawaian)
            ->get(route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]));

        $showPage->assertOk()
            ->assertSee('Bambang Staf')
            ->assertSee('199105052018011002')
            ->assertSee('Tambah Pegawai')
            ->assertSee('modalTambahPegawai', false)
            ->assertSee('modalPindahJabatan', false)
            ->assertSee('Pindah');
    }

    /** @test */
    public function manager_can_move_pegawai_to_another_jabatan()
    {
        $jabatanA = $this->createJabatan([
            'jabatan' => 'Dosen Asal',
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $jabatanB = $this->createJabatan([
            'jabatan' => 'Dosen Tujuan',
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $pegawai = Pegawai::create([
            'nama' => 'Dewi Lestari',
            'nip' => '198703032012012001',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id' => $jabatanA->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('peta-jabatan.manage.pindah-pegawai', ['slug' => 'jabatan']), [
                'pegawai_id' => $pegawai->id,
                'target_jabatan_id' => $jabatanB->id,
                'current_jabatan_id' => $jabatanA->id,
            ]);

        $response->assertRedirect(route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatanA->id]));

        $pegawai->refresh();
        $this->assertEquals($jabatanB->id, $pegawai->jabatan_id);
    }

    /** @test */
    public function jabatan_lists_and_options_are_ordered_by_kelas_jabatan_descending()
    {
        $jabatanLow = $this->createJabatan([
            'jabatan' => 'Jabatan Rendah',
            'kelas_jabatan' => 5,
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $jabatanHigh = $this->createJabatan([
            'jabatan' => 'Jabatan Tinggi',
            'kelas_jabatan' => 14,
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $jabatanMid = $this->createJabatan([
            'jabatan' => 'Jabatan Menengah',
            'kelas_jabatan' => 9,
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        // 1. Verify show page allJabatans ordering
        $responseShow = $this->actingAs($this->superAdmin)
            ->get(route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatanLow->id]));
        $responseShow->assertOk();
        $allJabatans = $responseShow->viewData('allJabatans');
        $this->assertEquals($jabatanHigh->id, $allJabatans->first()->id);

        // 2. Verify searchJabatans select2 options endpoint ordering
        $responseSearch = $this->actingAs($this->kepegawaian)
            ->getJson(route('kepegawaian.pegawai.options.jabatans', ['q' => 'Jabatan']));
        $responseSearch->assertOk();
        $results = $responseSearch->json('results');
        $this->assertEquals($jabatanHigh->id, $results[0]['id']);
        $this->assertEquals($jabatanMid->id, $results[1]['id']);
        $this->assertEquals($jabatanLow->id, $results[2]['id']);
    }
}




