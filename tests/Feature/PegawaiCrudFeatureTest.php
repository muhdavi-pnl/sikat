<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\SeedsDomisiliReferenceData;
use Tests\TestCase;

class PegawaiCrudFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsDomisiliReferenceData;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'kepegawaian']);
        Role::create(['name' => 'pegawai']);
    }

    /** @test */
    public function non_privileged_user_cannot_access_pegawai_management_page()
    {
        $user = $this->createPegawaiRoleUser();

        $this->actingAs($user)
            ->get(route('kepegawaian.pegawai.create'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_create_pegawai_with_minimal_payload()
    {
        $user = $this->createManagerUser();

        $response = $this->actingAs($user)->post(route('kepegawaian.pegawai.store'), [
            'nip' => '198501012010011001',
            'nama' => 'Pegawai Baru',
            'agama_id' => '',
            'user_id' => '',
        ]);

        $pegawai = Pegawai::first();

        $response->assertRedirect(route('kepegawaian.pegawai.show', $pegawai));
        $this->assertNotNull($pegawai);
        $this->assertSame('Pegawai Baru', $pegawai->nama);
        $this->assertSame('PNS', $pegawai->status_pegawai);
        $this->assertNull($pegawai->agama_id);
        $this->assertNull($pegawai->user_id);
        $this->assertDatabaseHas('pegawais', [
            'nip' => '198501012010011001',
            'nama' => 'Pegawai Baru',
            'status_pegawai' => 'PNS',
        ]);
    }

    /** @test */
    public function manager_can_create_pegawai_with_identitas_saved_in_identitas_table()
    {
        $user = $this->createManagerUser();

        $response = $this->actingAs($user)->post(route('kepegawaian.pegawai.store'), [
            'nip' => '198501012010011010',
            'nama' => 'Pegawai Dosen',
            'bidang_penelitian' => 'Kecerdasan Buatan',
            'nidn' => '0123456789',
            'agama_id' => '',
            'user_id' => '',
        ]);

        $pegawai = Pegawai::firstWhere('nip', '198501012010011010');

        $response->assertRedirect(route('kepegawaian.pegawai.show', $pegawai));
        $this->assertSame('Kecerdasan Buatan', $pegawai?->fresh()->bidang_penelitian);
        $this->assertDatabaseHas('pegawai_identitas', [
            'pegawai_id' => $pegawai->id,
            'bidang_penelitian' => 'Kecerdasan Buatan',
            'nidn' => '0123456789',
        ]);
    }

    /** @test */
    public function manager_can_update_pegawai()
    {
        $user = $this->createManagerUser();
        $this->seedRemoteReferenceData();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011002',
            'nama' => 'Nama Lama',
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->actingAs($user)->put(route('kepegawaian.pegawai.update', $pegawai), [
            'nip' => '198501012010011002',
            'nuptk' => '9988776655443322',
            'nidn' => '0011223344',
            'nama' => 'Nama Baru',
            'status_pegawai' => 'PPPK',
            'bidang_penelitian' => 'Jaringan Komputer',
            'alamat' => 'Jalan Baru No. 15',
            'provinsi_id' => 1,
            'kabupaten_id' => 1,
            'kecamatan_id' => 1,
            'kelurahan_id' => 1,
            'agama_id' => '',
            'user_id' => '',
        ]);

        $response->assertRedirect(route('kepegawaian.pegawai.show', $pegawai));

        $pegawai->refresh();

        $this->assertSame('Nama Baru', $pegawai->nama);
        $this->assertSame('9988776655443322', $pegawai->nuptk);
        $this->assertSame('0011223344', $pegawai->nidn);
        $this->assertSame('PPPK', $pegawai->status_pegawai);
        $this->assertSame('Jaringan Komputer', $pegawai->bidang_penelitian);
        $this->assertSame('Jalan Baru No. 15', $pegawai->alamat);
        $this->assertSame(1, $pegawai->kelurahan_id);
        $this->assertDatabaseHas('pegawai_identitas', [
            'pegawai_id' => $pegawai->id,
            'nuptk' => '9988776655443322',
            'nidn' => '0011223344',
            'bidang_penelitian' => 'Jaringan Komputer',
        ]);
    }

    /** @test */
    public function manager_can_view_pegawai_detail_with_nik_and_researcher_id_grid()
    {
        $manager = $this->createManagerUser();
        $this->seedRemoteReferenceData();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011006',
            'nik' => '3174012001900002',
            'nuptk' => '2233445566778899',
            'nidn' => '1122334455',
            'nama' => 'Pegawai Detail',
            'alamat' => 'Komplek Kampus Blok A',
            'kelurahan_id' => 1,
            'status_pegawai' => 'PNS',
            'id_gscholar' => 'GSCHOLAR999',
            'id_sinta' => '6000002',
            'id_scopus' => '57219199999',
            'id_garuda' => '887766',
            'id_wos' => 'AAC-5678-2024',
            'id_orc' => '0000-0001-2345-6789',
        ]);

        $this->actingAs($manager)
            ->get(route('kepegawaian.pegawai.show', $pegawai))
            ->assertOk()
            ->assertSee('NIK')
            ->assertSee('3174012001900002')
            ->assertSee('NUPTK')
            ->assertSee('2233445566778899')
            ->assertSee('NIDN')
            ->assertSee('1122334455')
            ->assertSee('Alamat Domisili')
            ->assertSee('Komplek Kampus Blok A')
            ->assertSee('Sukamaju')
            ->assertSee('Banda Sakti')
            ->assertSee('Lhokseumawe')
            ->assertSee('Aceh')
            ->assertSee('Identitas Akademik')
            ->assertSee('Jatah Awal')
            ->assertSee('Sisa Jatah')
            ->assertSee('Kontribusi Jatah')
            ->assertSee('Total Jatah')
            ->assertSee('Nomor HP')
            ->assertSee('Nomor Telepon')
            ->assertSee('profile-researcher-ids', false)
            ->assertSee('Google Scholar')
            ->assertSee('SINTA')
            ->assertSee('Scopus')
            ->assertSee('Garuda')
            ->assertSee('WOS Researcher')
            ->assertSee('ORCID')
            ->assertSee('GSCHOLAR999')
            ->assertSee('6000002')
            ->assertSee('57219199999')
            ->assertSee('887766')
            ->assertSee('AAC-5678-2024')
            ->assertSee('0000-0001-2345-6789')
            ->assertSee('fas fa-graduation-cap', false)
            ->assertSee('fas fa-award', false)
            ->assertSee('fas fa-database', false)
            ->assertSee('fas fa-feather-alt', false)
            ->assertSee('fas fa-globe', false)
            ->assertSee('fas fa-id-badge', false);
    }

    /** @test */
    public function manager_can_open_pegawai_edit_form_with_academic_identifier_section()
    {
        $manager = $this->createManagerUser();
        $this->seedRemoteReferenceData();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011007',
            'nik' => '3174012001900003',
            'nuptk' => '3344556677889900',
            'nidn' => '2233445566',
            'nama' => 'Pegawai Edit',
            'status_pegawai' => 'PNS',
            'id_gscholar' => 'GSCHOLAR777',
            'id_sinta' => '6000003',
            'id_scopus' => '57219177777',
            'id_garuda' => '554433',
            'id_wos' => 'AAD-8888-2024',
            'id_orc' => '0000-0003-1111-2222',
        ]);

        $this->actingAs($manager)
            ->get(route('kepegawaian.pegawai.edit', $pegawai))
            ->assertOk()
            ->assertSee('Identitas Akademik')
            ->assertSee('Google Scholar')
            ->assertSee('SINTA')
            ->assertSee('Scopus')
            ->assertSee('Garuda')
            ->assertSee('WOS Researcher')
            ->assertSee('ORCID')
            ->assertSee('NUPTK')
            ->assertSee('NIDN')
            ->assertSee('Alamat Domisili')
            ->assertSee('name="provinsi_id"', false)
            ->assertSee('name="kabupaten_id"', false)
            ->assertSee('name="kecamatan_id"', false)
            ->assertSee('name="kelurahan_id"', false)
            ->assertSee('form-select2-domisili', false)
            ->assertSee('name="nuptk"', false)
            ->assertSee('name="nidn"', false)
            ->assertSee('fas fa-graduation-cap', false)
            ->assertSee('fas fa-award', false)
            ->assertSee('fas fa-database', false)
            ->assertSee('fas fa-feather-alt', false)
            ->assertSee('fas fa-globe', false)
            ->assertSee('fas fa-id-badge', false)
            ->assertSee('GSCHOLAR777')
            ->assertSee('0000-0003-1111-2222');

        $response = $this->actingAs($manager)->get(route('kepegawaian.pegawai.edit', $pegawai));

        $this->assertStringContainsString('name="kabupaten_id" class="form-control form-select2-domisili" data-domisili="kabupaten" data-placeholder="-- Pilih Kabupaten/Kota --" disabled', $response->getContent());
        $this->assertStringContainsString('name="kecamatan_id" class="form-control form-select2-domisili" data-domisili="kecamatan" data-placeholder="-- Pilih Kecamatan --" disabled', $response->getContent());
        $this->assertStringContainsString('name="kelurahan_id" class="form-control form-select2-domisili" data-domisili="kelurahan" data-placeholder="-- Pilih Desa / Kelurahan --" disabled', $response->getContent());
    }

    /** @test */
    public function manager_must_choose_complete_domisili_hierarchy_when_filling_alamat()
    {
        $user = $this->createManagerUser();
        $this->seedRemoteReferenceData();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011008',
            'nama' => 'Pegawai Domisili',
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->from(route('kepegawaian.pegawai.edit', $pegawai))
            ->actingAs($user)
            ->put(route('kepegawaian.pegawai.update', $pegawai), [
                'nip' => '198501012010011008',
                'nama' => 'Pegawai Domisili',
                'status_pegawai' => 'PNS',
                'alamat' => 'Jalan Baru',
                'provinsi_id' => 1,
                'agama_id' => '',
                'user_id' => '',
            ]);

        $response->assertRedirect(route('kepegawaian.pegawai.edit', $pegawai));
        $response->assertSessionHasErrors(['kabupaten_id', 'kecamatan_id', 'kelurahan_id']);
    }

    /** @test */
    public function edit_form_preserves_old_domisili_input_after_validation_error()
    {
        $manager = $this->createManagerUser();
        $this->seedRemoteReferenceData();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011009',
            'nama' => 'Pegawai Old Input Admin',
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->from(route('kepegawaian.pegawai.edit', $pegawai))
            ->actingAs($manager)
            ->put(route('kepegawaian.pegawai.update', $pegawai), [
                'nip' => '198501012010011009',
                'nama' => 'Pegawai Old Input Admin',
                'status_pegawai' => 'PNS',
                'alamat' => 'Jalan Admin Lama No. 2',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'kecamatan_id' => 1,
                'kelurahan_id' => 1,
                'email' => 'bukan-email-valid',
                'agama_id' => '',
                'user_id' => '',
            ]);

        $response->assertRedirect(route('kepegawaian.pegawai.edit', $pegawai));
        $response->assertSessionHasErrors(['email']);

        $editResponse = $this->actingAs($manager)
            ->get(route('kepegawaian.pegawai.edit', $pegawai))
            ->assertOk();

        $this->assertStringContainsString('Jalan Admin Lama No. 2', $editResponse->getContent());
        $this->assertStringContainsString('<option value="1" selected>Aceh</option>', $editResponse->getContent());
        $this->assertStringContainsString('<option value="1" selected>Lhokseumawe</option>', $editResponse->getContent());
        $this->assertStringContainsString('<option value="1" selected>Banda Sakti</option>', $editResponse->getContent());
        $this->assertStringContainsString('<option value="1" selected>Sukamaju</option>', $editResponse->getContent());
    }

    /** @test */
    public function manager_cannot_update_pegawai_with_mismatched_domisili_hierarchy()
    {
        $manager = $this->createManagerUser();
        $this->seedRemoteReferenceData();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011010',
            'nama' => 'Pegawai Hierarki Admin',
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->from(route('kepegawaian.pegawai.edit', $pegawai))
            ->actingAs($manager)
            ->put(route('kepegawaian.pegawai.update', $pegawai), [
                'nip' => '198501012010011010',
                'nama' => 'Pegawai Hierarki Admin',
                'status_pegawai' => 'PNS',
                'alamat' => 'Jalan Hierarki Admin',
                'provinsi_id' => 2,
                'kabupaten_id' => 1,
                'kecamatan_id' => 2,
                'kelurahan_id' => 2,
                'agama_id' => '',
                'user_id' => '',
            ]);

        $response->assertRedirect(route('kepegawaian.pegawai.edit', $pegawai));
        $response->assertSessionHasErrors(['kabupaten_id', 'kecamatan_id']);
        $this->assertArrayNotHasKey('kelurahan_id', session('errors')->getBag('default')->messages());
    }

    /** @test */
    public function manager_cannot_submit_legacy_guru_besar_value_anymore()
    {
        $user = $this->createManagerUser();
        $pegawai = Pegawai::create([
            'nip' => '198501012010011212',
            'nama' => 'Nama Profesor Lama',
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->from(route('kepegawaian.pegawai.edit', $pegawai))
            ->actingAs($user)
            ->put(route('kepegawaian.pegawai.update', $pegawai), [
            'nip' => '198501012010011212',
            'nama' => 'Nama Profesor Baru',
            'status_pegawai' => 'PNS',
            'jabatan_fungsional' => 'guru besar',
            'agama_id' => '',
            'user_id' => '',
        ]);

        $response->assertRedirect(route('kepegawaian.pegawai.edit', $pegawai));
        $response->assertSessionHasErrors('jabatan_fungsional');

        $pegawai->refresh();

        $this->assertNull($pegawai->jabatan_fungsional);

        $this->actingAs($user)
            ->get(route('kepegawaian.pegawai.edit', $pegawai))
            ->assertOk()
            ->assertSee('value="profesor"', false)
            ->assertDontSee('value="profesor" selected', false)
            ->assertDontSee('value="guru besar"', false)
            ->assertSee('Profesor');
    }

    /** @test */
    public function manager_can_soft_delete_pegawai()
    {
        $user = $this->createManagerUser();
        $pegawai = Pegawai::create([
            'nip' => '198501012010011003',
            'nama' => 'Pegawai Hapus',
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->actingAs($user)->delete(route('kepegawaian.pegawai.destroy', $pegawai));

        $response->assertRedirect(route('kepegawaian.pegawai'));
        $this->assertSoftDeleted('pegawais', ['id' => $pegawai->id]);
    }

    /** @test */
    public function create_pegawai_requires_nip_and_nama()
    {
        $user = $this->createManagerUser();

        $response = $this->from(route('kepegawaian.pegawai.create'))
            ->actingAs($user)
            ->post(route('kepegawaian.pegawai.store'), []);

        $response->assertRedirect(route('kepegawaian.pegawai.create'));
        $response->assertSessionHasErrors(['nip', 'nama']);
    }

    /** @test */
    public function manager_can_fetch_ajax_option_results()
    {
        $manager = $this->createManagerUser();
        $candidateUser = User::factory()->create([
            'name' => 'Remote User',
            'email' => 'remote-user@example.test',
        ]);

        $references = $this->seedRemoteReferenceData();

        $this->actingAs($manager)
            ->getJson(route('kepegawaian.pegawai.options.users', ['q' => 'Remote']))
            ->assertOk()
            ->assertJsonFragment(['id' => $candidateUser->id, 'text' => 'Remote User - remote-user@example.test']);

        $this->actingAs($manager)
            ->getJson(route('kepegawaian.pegawai.options.jabatans', ['q' => 'Kepala']))
            ->assertOk()
            ->assertJsonFragment(['id' => $references['jabatan_id'], 'text' => 'Kepala Unit']);

        $this->actingAs($manager)
            ->getJson(route('kepegawaian.pegawai.options.program-studis', ['q' => 'Teknik']))
            ->assertOk()
            ->assertJsonFragment(['id' => $references['program_studi_id'], 'text' => 'D4 - Teknik Informatika - Teknik Elektro']);

        $this->actingAs($manager)
            ->getJson(route('kepegawaian.pegawai.options.kelurahans', ['q' => 'Suka']))
            ->assertOk()
            ->assertJsonFragment(['id' => $references['kelurahan_id'], 'text' => 'Sukamaju - Banda Sakti - Lhokseumawe']);

        $this->actingAs($manager)
            ->getJson(route('pegawai.wilayah.provinsis'))
            ->assertOk()
            ->assertJsonFragment(['id' => 1, 'text' => 'Aceh']);

        $this->actingAs($manager)
            ->getJson(route('pegawai.wilayah.kabupatens', ['provinsi_id' => 1]))
            ->assertOk()
            ->assertJsonFragment(['id' => 1, 'text' => 'Lhokseumawe']);

        $this->actingAs($manager)
            ->getJson(route('pegawai.wilayah.kecamatans', ['kabupaten_id' => 1]))
            ->assertOk()
            ->assertJsonFragment(['id' => 1, 'text' => 'Banda Sakti']);

        $this->actingAs($manager)
            ->getJson(route('pegawai.wilayah.kelurahans', ['kecamatan_id' => 1]))
            ->assertOk()
            ->assertJsonFragment(['id' => 1, 'text' => 'Sukamaju']);
    }

    /** @test */
    public function ajax_option_results_require_minimum_search_length()
    {
        $manager = $this->createManagerUser();
        $this->seedRemoteReferenceData();

        $this->actingAs($manager)
            ->getJson(route('kepegawaian.pegawai.options.users', ['q' => 'R']))
            ->assertOk()
            ->assertJson([
                'results' => [],
                'pagination' => ['more' => false],
            ]);

        $this->actingAs($manager)
            ->getJson(route('kepegawaian.pegawai.options.jabatans', ['q' => 'K']))
            ->assertOk()
            ->assertJson([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
    }

    /** @test */
    public function manager_can_export_and_print_pegawai_listing()
    {
        $manager = $this->createManagerUser();
        Pegawai::create([
            'nik' => '3174012001900099',
            'nip' => '198501012010011004',
            'nuptk' => '4455667788990011',
            'nidn' => '3344556677',
            'nama' => 'Pegawai Export',
            'id_gscholar' => 'GSCHOLAR123',
            'status_pegawai' => 'PNS',
        ]);
        Pegawai::create([
            'nip' => '198501012010011005',
            'nama' => 'Pegawai Tidak Tampil',
            'status_pegawai' => 'PNS',
        ]);

        $exportResponse = $this->actingAs($manager)->get(route('kepegawaian.pegawai.export', ['q' => 'Export']));
        $exportResponse->assertOk();
        $exportContent = $exportResponse->streamedContent();
        $this->assertStringContainsString('Pegawai Export', $exportContent);
        $this->assertStringContainsString('NIK,NIP,NUPTK,NIDN,Nama,"ID Google Scholar"', $exportContent);
        $this->assertStringContainsString('3174012001900099,198501012010011004,4455667788990011,3344556677,"Pegawai Export",GSCHOLAR123', $exportContent);
        $this->assertStringContainsString('Unit Kerja', $exportContent);
        $this->assertStringNotContainsString('Jurusan', $exportContent);
        $this->assertStringNotContainsString('Pegawai Tidak Tampil', $exportContent);

        $nikExportResponse = $this->actingAs($manager)->get(route('kepegawaian.pegawai.export', ['q' => '3174012001900099']));
        $nikExportResponse->assertOk();
        $nikExportContent = $nikExportResponse->streamedContent();
        $this->assertStringContainsString('Pegawai Export', $nikExportContent);
        $this->assertStringNotContainsString('Pegawai Tidak Tampil', $nikExportContent);

        $this->actingAs($manager)
            ->get(route('kepegawaian.pegawai.print', ['q' => 'Export']))
            ->assertOk()
            ->assertSee('Cetak Data Pegawai')
            ->assertSeeInOrder(['NIK', 'NIP', 'NUPTK', 'NIDN', 'Nama'])
            ->assertSee('ID Google Scholar')
            ->assertSee('Unit Kerja')
            ->assertDontSee('Jurusan')
            ->assertSee('3174012001900099')
            ->assertSee('4455667788990011')
            ->assertSee('3344556677')
            ->assertSee('PEGAWAI EXPORT')
            ->assertSee('GSCHOLAR123')
            ->assertDontSee('PEGAWAI TIDAK TAMPIL');

        $this->actingAs($manager)
            ->get(route('kepegawaian.pegawai.print', ['q' => '3174012001900099']))
            ->assertOk()
            ->assertSee('PEGAWAI EXPORT')
            ->assertDontSee('PEGAWAI TIDAK TAMPIL');

        $this->actingAs($manager)
            ->get(route('kepegawaian.pegawai.print', ['q' => '4455667788990011']))
            ->assertOk()
            ->assertSee('PEGAWAI EXPORT')
            ->assertDontSee('PEGAWAI TIDAK TAMPIL');
    }

    /** @test */
    public function manager_can_create_pegawai_with_distinct_alamat_asal_and_alamat_domisili()
    {
        $user = $this->createManagerUser();
        $domisili = $this->seedDomisiliReferenceData();

        $response = $this->actingAs($user)->post(route('kepegawaian.pegawai.store'), [
            'nip' => '198501012010017777',
            'nama' => 'Pegawai Dua Alamat',
            'alamat_asal' => 'Jl. Asal No. 123',
            'provinsi_asal_id' => $domisili['primary']['provinsi_id'],
            'kabupaten_asal_id' => $domisili['primary']['kabupaten_id'],
            'kecamatan_asal_id' => $domisili['primary']['kecamatan_id'],
            'kelurahan_asal_id' => $domisili['primary']['kelurahan_id'],
            'alamat' => 'Jl. Domisili No. 456',
            'provinsi_id' => $domisili['secondary']['provinsi_id'],
            'kabupaten_id' => $domisili['secondary']['kabupaten_id'],
            'kecamatan_id' => $domisili['secondary']['kecamatan_id'],
            'kelurahan_id' => $domisili['secondary']['kelurahan_id'],
        ]);

        $pegawai = Pegawai::firstWhere('nip', '198501012010017777');
        $response->assertRedirect(route('kepegawaian.pegawai.show', $pegawai));

        $this->assertSame('Jl. Asal No. 123', $pegawai->alamat_asal);
        $this->assertSame($domisili['primary']['kelurahan_id'], $pegawai->kelurahan_asal_id);
        $this->assertSame('Jl. Domisili No. 456', $pegawai->alamat);
        $this->assertSame($domisili['secondary']['kelurahan_id'], $pegawai->kelurahan_id);

        // Check show page displays both addresses
        $this->actingAs($user)->get(route('kepegawaian.pegawai.show', $pegawai))
            ->assertOk()
            ->assertSee('Jl. Asal No. 123')
            ->assertSee('Jl. Domisili No. 456')
            ->assertSee('Data Alamat Asal (KTP)')
            ->assertSee('Data Alamat Domisili');
    }

    /** @test */
    public function manager_can_create_pegawai_with_alamat_sama_checkbox()
    {
        $user = $this->createManagerUser();
        $domisili = $this->seedDomisiliReferenceData();

        $response = $this->actingAs($user)->post(route('kepegawaian.pegawai.store'), [
            'nip' => '198501012010018888',
            'nama' => 'Pegawai Alamat Sama',
            'alamat_asal' => 'Jl. Tunggal No. 99',
            'provinsi_asal_id' => $domisili['primary']['provinsi_id'],
            'kabupaten_asal_id' => $domisili['primary']['kabupaten_id'],
            'kecamatan_asal_id' => $domisili['primary']['kecamatan_id'],
            'kelurahan_asal_id' => $domisili['primary']['kelurahan_id'],
            'alamat_sama' => '1',
        ]);

        $pegawai = Pegawai::firstWhere('nip', '198501012010018888');
        $response->assertRedirect(route('kepegawaian.pegawai.show', $pegawai));

        $this->assertSame('Jl. Tunggal No. 99', $pegawai->alamat_asal);
        $this->assertSame($domisili['primary']['kelurahan_id'], $pegawai->kelurahan_asal_id);
        $this->assertSame('Jl. Tunggal No. 99', $pegawai->alamat);
        $this->assertSame($domisili['primary']['kelurahan_id'], $pegawai->kelurahan_id);
    }

    protected function createManagerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('kepegawaian');

        return $user;
    }

    protected function createPegawaiRoleUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        return $user;
    }

    protected function seedRemoteReferenceData(): array
    {
        $domisili = $this->seedDomisiliReferenceData();

        DB::table('jenis_jabatans')->insert([
            'id' => 1,
            'jenis_jabatan' => 'Struktural',
        ]);

        DB::table('jabatans')->insert([
            'id' => 1,
            'jabatan' => 'Kepala Unit',
            'kelas_jabatan' => 10,
        ]);

        DB::table('perguruan_tinggis')->insert([
            'id' => 1,
            'perguruan_tinggi' => 'Politeknik Negeri Lhokseumawe',
        ]);

        DB::table('jurusans')->insert([
            'id' => 1,
            'jurusan' => 'Teknik Elektro',
        ]);

        DB::table('program_studis')->insert([
            'id' => 1,
            'kode_prodi' => 'TI01',
            'nama_prodi' => 'Teknik Informatika',
            'tanggal_berdiri' => '2000-01-01',
            'status' => 'Aktif',
            'jenjang' => 'D4',
            'akreditasi' => 'A',
            'jurusan_id' => 1,
        ]);

        return [
            'jabatan_id' => 1,
            'program_studi_id' => 1,
            'kelurahan_id' => $domisili['primary']['kelurahan_id'],
        ];
    }
}
