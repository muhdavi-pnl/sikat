<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SeedsDomisiliReferenceData;
use Tests\TestCase;

class PegawaiProfileFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsDomisiliReferenceData;

    /** @test */
    public function user_without_pegawai_can_open_profile_page_and_see_empty_state()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pegawai.profile'))
            ->assertOk()
            ->assertSee('Data pegawai Anda belum tersedia');
    }

    /** @test */
    public function user_with_pegawai_can_update_profile_from_profile_page()
    {
        $user = User::factory()->create();

        $this->seedDomisiliReferenceData();

        DB::table('unit_kerjas')->insert([
            'id' => 1,
            'unit_kerja' => 'UPT TIK',
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

        $pegawai = Pegawai::create([
            'nip' => '198501012010011099',
            'nama' => 'Nama Lama',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('pegawai.profile.update'), [
                'nip' => '198501012010011099',
                'nuptk' => '5566778899001122',
                'nidn' => '4455667788',
                'nama' => 'Nama Baru Profil',
                'id_gscholar' => 'AbCdEfGh12345',
                'id_sinta' => '6000010',
                'id_scopus' => '57219111111',
                'id_garuda' => '445566',
                'id_wos' => 'AAE-1111-2024',
                'id_orc' => '0000-0003-1234-5678',
                'email' => 'profil.pegawai@example.test',
                'no_hp' => '08123456789',
                'alamat' => 'Jalan Merdeka No. 10',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'kecamatan_id' => 1,
                'kelurahan_id' => 1,
                'unit_kerja_id' => 1,
                'program_studi_id' => 1,
                'jabatan_fungsional' => 'lektor',
            ]);

        $response->assertRedirect(route('pegawai.profile'));

        $pegawai->refresh();
        $this->assertSame('Nama Baru Profil', $pegawai->nama);
        $this->assertSame('5566778899001122', $pegawai->nuptk);
        $this->assertSame('4455667788', $pegawai->nidn);
        $this->assertSame('AbCdEfGh12345', $pegawai->id_gscholar);
        $this->assertSame('6000010', $pegawai->id_sinta);
        $this->assertSame('57219111111', $pegawai->id_scopus);
        $this->assertSame('445566', $pegawai->id_garuda);
        $this->assertSame('AAE-1111-2024', $pegawai->id_wos);
        $this->assertSame('0000-0003-1234-5678', $pegawai->id_orc);
        $this->assertSame('profil.pegawai@example.test', $pegawai->email);
        $this->assertSame('08123456789', $pegawai->no_hp);
        $this->assertSame('Jalan Merdeka No. 10', $pegawai->alamat);
        $this->assertSame(1, $pegawai->kelurahan_id);
        $this->assertSame(1, $pegawai->unit_kerja_id);
        $this->assertSame(1, $pegawai->program_studi_id);
        $this->assertSame('lektor', $pegawai->jabatan_fungsional);
        $this->assertDatabaseHas('pegawai_identitas', [
            'pegawai_id' => $pegawai->id,
            'nuptk' => '5566778899001122',
            'nidn' => '4455667788',
            'id_gscholar' => 'AbCdEfGh12345',
        ]);
    }

    /** @test */
    public function profile_form_shows_all_academic_identity_fields_for_pegawai()
    {
        $user = User::factory()->create();

        $this->seedDomisiliReferenceData();

        Pegawai::create([
            'nip' => '198501012010011188',
            'nama' => 'Pegawai Form Profil',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('pegawai.profile'))
            ->assertOk()
            ->assertSee('NUPTK')
            ->assertSee('NIDN')
            ->assertSee('Alamat Domisili')
            ->assertSee('Provinsi')
            ->assertSee('Kabupaten / Kota')
            ->assertSee('Kecamatan')
            ->assertSee('Desa / Kelurahan')
            ->assertSee('Identitas Akademik')
            ->assertSeeInOrder(['Google Scholar', 'SINTA', 'Scopus', 'Garuda', 'WOS Researcher', 'ORCID'])
            ->assertSee('Nomor HP')
            ->assertSee('Nomor Telepon')
            ->assertSee('name="nuptk"', false)
            ->assertSee('name="nidn"', false)
            ->assertSee('name="provinsi_id"', false)
            ->assertSee('name="kabupaten_id"', false)
            ->assertSee('name="kecamatan_id"', false)
            ->assertSee('name="kelurahan_id"', false)
            ->assertSee('form-select2-domisili', false)
            ->assertSee('name="id_gscholar"', false)
            ->assertSee('name="id_sinta"', false)
            ->assertSee('name="id_scopus"', false)
            ->assertSee('name="id_garuda"', false)
            ->assertSee('name="id_wos"', false)
            ->assertSee('name="id_orc"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'name="id_scopus"'));
        $this->assertStringContainsString('name="kabupaten_id" class="form-control form-select2-domisili" data-domisili="kabupaten" data-placeholder="-- Pilih Kabupaten/Kota --" disabled', $response->getContent());
        $this->assertStringContainsString('name="kecamatan_id" class="form-control form-select2-domisili" data-domisili="kecamatan" data-placeholder="-- Pilih Kecamatan --" disabled', $response->getContent());
        $this->assertStringContainsString('name="kelurahan_id" class="form-control form-select2-domisili" data-domisili="kelurahan" data-placeholder="-- Pilih Desa / Kelurahan --" disabled', $response->getContent());
        $this->assertStringContainsString('select2.min.js', $response->getContent());
        $this->assertStringContainsString('change.select2Domisili', $response->getContent());
    }

    /** @test */
    public function authenticated_user_can_fetch_domisili_option_results_for_profile_form()
    {
        $user = User::factory()->create();
        $this->seedDomisiliReferenceData();

        $this->actingAs($user)
            ->getJson(route('pegawai.wilayah.provinsis'))
            ->assertOk()
            ->assertJsonFragment(['id' => 1, 'text' => 'Aceh']);

        $this->actingAs($user)
            ->getJson(route('pegawai.wilayah.kabupatens', ['provinsi_id' => 1]))
            ->assertOk()
            ->assertJsonFragment(['id' => 1, 'text' => 'Lhokseumawe']);

        $this->actingAs($user)
            ->getJson(route('pegawai.wilayah.kecamatans', ['kabupaten_id' => 1]))
            ->assertOk()
            ->assertJsonFragment(['id' => 1, 'text' => 'Banda Sakti']);

        $this->actingAs($user)
            ->getJson(route('pegawai.wilayah.kelurahans', ['kecamatan_id' => 1]))
            ->assertOk()
            ->assertJsonFragment(['id' => 1, 'text' => 'Sukamaju']);
    }

    /** @test */
    public function profile_update_requires_complete_domisili_hierarchy_when_alamat_is_filled()
    {
        $user = User::factory()->create();
        $this->seedDomisiliReferenceData();

        Pegawai::create([
            'nip' => '198501012010011177',
            'nama' => 'Pegawai Domisili Profil',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->from(route('pegawai.profile'))
            ->actingAs($user)
            ->put(route('pegawai.profile.update'), [
                'nip' => '198501012010011177',
                'nama' => 'Pegawai Domisili Profil',
                'alamat' => 'Jalan Baru',
                'provinsi_id' => 1,
            ]);

        $response->assertRedirect(route('pegawai.profile'));
        $response->assertSessionHasErrors(['kabupaten_id', 'kecamatan_id', 'kelurahan_id']);
    }

    /** @test */
    public function profile_form_preserves_old_domisili_input_after_validation_error()
    {
        $user = User::factory()->create();
        $this->seedDomisiliReferenceData();

        Pegawai::create([
            'nip' => '198501012010011176',
            'nama' => 'Pegawai Old Input Profil',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->from(route('pegawai.profile'))
            ->actingAs($user)
            ->put(route('pegawai.profile.update'), [
                'nip' => '198501012010011176',
                'nama' => 'Pegawai Old Input Profil',
                'alamat' => 'Jalan Lama No. 1',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'kecamatan_id' => 1,
                'kelurahan_id' => 1,
                'email' => 'bukan-email-valid',
            ]);

        $response->assertRedirect(route('pegawai.profile'));
        $response->assertSessionHasErrors(['email']);

        $profileResponse = $this->actingAs($user)
            ->get(route('pegawai.profile'))
            ->assertOk();

        $this->assertStringContainsString('Jalan Lama No. 1', $profileResponse->getContent());
        $this->assertStringContainsString('<option value="1" selected>Aceh</option>', $profileResponse->getContent());
        $this->assertStringContainsString('<option value="1" selected>Lhokseumawe</option>', $profileResponse->getContent());
        $this->assertStringContainsString('<option value="1" selected>Banda Sakti</option>', $profileResponse->getContent());
        $this->assertStringContainsString('<option value="1" selected>Sukamaju</option>', $profileResponse->getContent());
    }

    /** @test */
    public function profile_update_rejects_mismatched_domisili_hierarchy()
    {
        $user = User::factory()->create();
        $this->seedDomisiliReferenceData();

        Pegawai::create([
            'nip' => '198501012010011175',
            'nama' => 'Pegawai Hierarki Salah',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->from(route('pegawai.profile'))
            ->actingAs($user)
            ->put(route('pegawai.profile.update'), [
                'nip' => '198501012010011175',
                'nama' => 'Pegawai Hierarki Salah',
                'alamat' => 'Jalan Hierarki Salah',
                'provinsi_id' => 2,
                'kabupaten_id' => 1,
                'kecamatan_id' => 2,
                'kelurahan_id' => 2,
            ]);

        $response->assertRedirect(route('pegawai.profile'));
        $response->assertSessionHasErrors(['kabupaten_id', 'kecamatan_id']);
        $this->assertArrayNotHasKey('kelurahan_id', session('errors')->getBag('default')->messages());
    }

    /** @test */
    public function profile_page_shows_nik_summary_and_researcher_ids_in_icon_grid()
    {
        $user = User::factory()->create();

        Pegawai::create([
            'nip' => '198501012010011155',
            'nik' => '3174012001900001',
            'nama' => 'Pegawai Riset',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
            'id_gscholar' => 'AbCdEfGh12345',
            'id_sinta' => '6000001',
            'id_scopus' => '57219123456',
            'id_garuda' => '998877',
            'id_wos' => 'AAB-1234-2024',
            'id_orc' => '0000-0002-1825-0097',
        ]);

        $this->actingAs($user)
            ->get(route('pegawai.profile'))
            ->assertOk()
            ->assertSee('NIK')
            ->assertSee('3174012001900001')
            ->assertSee('Identitas Akademik')
            ->assertSee('Jatah Awal')
            ->assertSee('Sisa Jatah')
            ->assertSee('Kontribusi Jatah')
            ->assertSee('profile-researcher-ids', false)
            ->assertSee('Google Scholar')
            ->assertSee('SINTA')
            ->assertSee('Scopus')
            ->assertSee('Garuda')
            ->assertSee('WOS Researcher')
            ->assertSee('ORCID')
            ->assertSee('AbCdEfGh12345')
            ->assertSee('6000001')
            ->assertSee('57219123456')
            ->assertSee('998877')
            ->assertSee('AAB-1234-2024')
            ->assertSee('0000-0002-1825-0097')
            ->assertSee('fas fa-graduation-cap', false)
            ->assertSee('fas fa-award', false)
            ->assertSee('fas fa-database', false)
            ->assertSee('fas fa-feather-alt', false)
            ->assertSee('fas fa-globe', false)
            ->assertSee('fas fa-id-badge', false);
    }

    /** @test */
    public function legacy_guru_besar_profile_input_is_rejected()
    {
        $user = User::factory()->create();

        DB::table('unit_kerjas')->insert([
            'id' => 1,
            'unit_kerja' => 'UPT TIK',
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

        $pegawai = Pegawai::create([
            'nip' => '198501012010011199',
            'nama' => 'Nama Profesor Profil',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->from(route('pegawai.profile'))
            ->actingAs($user)
            ->put(route('pegawai.profile.update'), [
                'nip' => '198501012010011199',
                'nama' => 'Nama Profesor Profil',
                'unit_kerja_id' => 1,
                'program_studi_id' => 1,
                'jabatan_fungsional' => 'guru besar',
            ]);

        $response->assertRedirect(route('pegawai.profile'));
        $response->assertSessionHasErrors('jabatan_fungsional');

        $pegawai->refresh();
        $this->assertNull($pegawai->jabatan_fungsional);

        $this->actingAs($user)
            ->get(route('pegawai.profile'))
            ->assertOk()
            ->assertDontSee('value="guru besar"', false)
            ->assertSee('Profesor');
    }

    /** @test */
    public function user_can_change_password_from_profile_page()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Sikat2019'),
            'must_change_password' => false,
        ]);

        Pegawai::create([
            'nip' => '198501012010011299',
            'nama' => 'Pegawai Password',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('pegawai.profile'))
            ->assertOk()
            ->assertSee('Ubah Password')
            ->assertSee('password-security', false);

        $response = $this->actingAs($user)
            ->from(route('pegawai.profile'))
            ->put(route('pegawai.profile.password.update'), [
                'current_password' => 'Sikat2019',
                'password' => 'NewSecurePass123',
                'password_confirmation' => 'NewSecurePass123',
            ]);

        $response->assertRedirect(route('pegawai.profile') . '#password-security');

        $user->refresh();

        $this->assertTrue(Hash::check('NewSecurePass123', $user->password));
        $this->assertFalse((bool) $user->must_change_password);
    }

    /** @test */
    public function password_change_from_profile_page_requires_valid_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Sikat2019'),
        ]);

        Pegawai::create([
            'nip' => '198501012010011399',
            'nama' => 'Pegawai Salah Password',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('pegawai.profile'))
            ->put(route('pegawai.profile.password.update'), [
                'current_password' => 'SalahPassword',
                'password' => 'NewSecurePass123',
                'password_confirmation' => 'NewSecurePass123',
            ]);

        $response->assertRedirect(route('pegawai.profile'));
        $response->assertSessionHasErrors(['current_password'], null, 'updatePassword');

        $user->refresh();

        $this->assertTrue(Hash::check('Sikat2019', $user->password));
    }

    /** @test */
    public function user_can_update_profile_with_distinct_alamat_asal_and_domisili()
    {
        $user = User::factory()->create();
        $domisili = $this->seedDomisiliReferenceData();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011499',
            'nama' => 'Pegawai Profil Dua Alamat',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('pegawai.profile'))
            ->put(route('pegawai.profile.update'), [
                'nip' => '198501012010011499',
                'nama' => 'Pegawai Profil Dua Alamat',
                'alamat_asal' => 'Jl. Asal Pegawai No. 1',
                'provinsi_asal_id' => $domisili['primary']['provinsi_id'],
                'kabupaten_asal_id' => $domisili['primary']['kabupaten_id'],
                'kecamatan_asal_id' => $domisili['primary']['kecamatan_id'],
                'kelurahan_asal_id' => $domisili['primary']['kelurahan_id'],
                'alamat' => 'Jl. Domisili Pegawai No. 2',
                'provinsi_id' => $domisili['secondary']['provinsi_id'],
                'kabupaten_id' => $domisili['secondary']['kabupaten_id'],
                'kecamatan_id' => $domisili['secondary']['kecamatan_id'],
                'kelurahan_id' => $domisili['secondary']['kelurahan_id'],
            ]);

        $response->assertRedirect(route('pegawai.profile'));
        $pegawai->refresh();

        $this->assertSame('Jl. Asal Pegawai No. 1', $pegawai->alamat_asal);
        $this->assertSame($domisili['primary']['kelurahan_id'], $pegawai->kelurahan_asal_id);
        $this->assertSame('Jl. Domisili Pegawai No. 2', $pegawai->alamat);
        $this->assertSame($domisili['secondary']['kelurahan_id'], $pegawai->kelurahan_id);
    }

    /** @test */
    public function user_can_update_profile_with_alamat_sama_checkbox()
    {
        $user = User::factory()->create();
        $domisili = $this->seedDomisiliReferenceData();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011599',
            'nama' => 'Pegawai Profil Alamat Sama',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('pegawai.profile'))
            ->put(route('pegawai.profile.update'), [
                'nip' => '198501012010011599',
                'nama' => 'Pegawai Profil Alamat Sama',
                'alamat_asal' => 'Jl. Bersama No. 10',
                'provinsi_asal_id' => $domisili['primary']['provinsi_id'],
                'kabupaten_asal_id' => $domisili['primary']['kabupaten_id'],
                'kecamatan_asal_id' => $domisili['primary']['kecamatan_id'],
                'kelurahan_asal_id' => $domisili['primary']['kelurahan_id'],
                'alamat_sama' => '1',
            ]);

        $response->assertRedirect(route('pegawai.profile'));
        $pegawai->refresh();

        $this->assertSame('Jl. Bersama No. 10', $pegawai->alamat_asal);
        $this->assertSame($domisili['primary']['kelurahan_id'], $pegawai->kelurahan_asal_id);
        $this->assertSame('Jl. Bersama No. 10', $pegawai->alamat);
        $this->assertSame($domisili['primary']['kelurahan_id'], $pegawai->kelurahan_id);
    }
}
