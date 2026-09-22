<?php

namespace Tests\Feature;

use App\Services\CutiService;
use App\Models\LayananPegawai;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LayananFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'kepegawaian']);
        Role::create(['name' => 'pegawai']);
    }

    /** @test */
    public function pegawai_hanya_melihat_riwayat_layanan_milik_sendiri()
    {
        $userA = User::factory()->create();
        $userA->assignRole('pegawai');
        $pegawaiA = Pegawai::create([
            'nip' => '198501012010011301',
            'nama' => 'Pegawai A',
            'status_pegawai' => 'PNS',
            'user_id' => $userA->id,
        ]);

        $userB = User::factory()->create();
        $userB->assignRole('pegawai');
        $pegawaiB = Pegawai::create([
            'nip' => '198501012010011302',
            'nama' => 'Pegawai B',
            'status_pegawai' => 'PNS',
            'user_id' => $userB->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Kenaikan Pangkat',
            'deskripsi' => 'Tes layanan',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_pegawais')->insert([
            [
                'layanan_id' => $layananId,
                'pegawai_id' => $pegawaiA->id,
                'user_id' => $userA->id,
                'status' => 'usulan',
                'catatan_pengusul' => 'Catatan Pegawai A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'layanan_id' => $layananId,
                'pegawai_id' => $pegawaiB->id,
                'user_id' => $userB->id,
                'status' => 'usulan',
                'catatan_pengusul' => 'Catatan Pegawai B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($userA)
            ->get(route('pegawai.layanan'))
            ->assertOk()
            ->assertSee('Catatan Pegawai A')
            ->assertDontSee('Catatan Pegawai B');
    }

    /** @test */
    public function pegawai_bisa_mengusulkan_layanan_dari_form_usulan()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011303',
            'nama' => 'Pegawai Usul',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi',
            'deskripsi' => 'Tes usulan',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('pegawai.layanan.preview', $layananId), [
                'catatan_pengusul' => 'Mohon diproses segera',
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $response = $this->post(route('pegawai.layanan.store', $layananId), [
                'konfirmasi_kirim' => '1',
            ]);

        $usulan = LayananPegawai::query()
            ->where('layanan_id', $layananId)
            ->where('pegawai_id', $pegawai->id)
            ->firstOrFail();

        $response->assertRedirect(route('pegawai.layanan.done', $usulan));

        $this->get(route('pegawai.layanan.done', $usulan))
            ->assertOk()
            ->assertSee('Usulan Berhasil Dikirim')
            ->assertSee('Mutasi');

        $this->assertDatabaseHas('layanan_pegawais', [
            'id' => $usulan->id,
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $user->id,
            'status' => 'usulan',
            'catatan_pengusul' => 'Mohon diproses segera',
        ]);
    }

    /** @test */
    public function review_step_menampilkan_ringkasan_layanan_dan_data_pegawai_sebelum_kirim()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011399',
            'nama' => 'Pegawai Review Layanan',
            'status_pegawai' => 'PNS',
            'email' => 'review@example.test',
            'no_hp' => '08123456789',
            'user_id' => $user->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Perubahan Data',
            'deskripsi' => 'Review akhir usulan',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('pegawai.layanan.preview', $layananId), [
                'catatan_pengusul' => 'Mohon diproses segera',
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $this->get(route('pegawai.layanan.review', $layananId))
            ->assertOk()
            ->assertSee('Informasi Layanan')
            ->assertSee('Perubahan Data')
            ->assertSee('PEGAWAI REVIEW LAYANAN')
            ->assertSee('Mohon diproses segera')
            ->assertSee('Konfirmasi Pengiriman');
    }

    /** @test */
    public function pegawai_harus_mengonfirmasi_sebelum_usulan_layanan_dikirim()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011398',
            'nama' => 'Pegawai Konfirmasi',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Surat Aktif',
            'deskripsi' => 'Tes konfirmasi submit',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('pegawai.layanan.preview', $layananId), [
            'catatan_pengusul' => 'Draft siap kirim',
        ])->assertRedirect(route('pegawai.layanan.review', $layananId));

        $this->from(route('pegawai.layanan.review', $layananId))
            ->post(route('pegawai.layanan.store', $layananId), [])
            ->assertRedirect(route('pegawai.layanan.review', $layananId))
            ->assertSessionHasErrors('konfirmasi_kirim');

        $this->assertDatabaseMissing('layanan_pegawais', [
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function layanan_usul_page_shows_ready_status_when_profile_and_document_requirements_are_met()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011308',
            'nama' => 'Pegawai Eligible',
            'id_gscholar' => 'SCHOLAR123',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'SKP',
            'nama_dokumen' => 'SK Pangkat',
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Kenaikan Jabatan',
            'deskripsi' => 'Tes precheck layanan',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratDokumenId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'SK_PANGKAT',
            'dokumen_id' => $dokumenId,
            'syarat' => 'Unggah SK Pangkat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratProfilId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'ID_GSCHOLAR',
            'syarat' => 'Isi ID Google Scholar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            ['layanan_id' => $layananId, 'syarat_id' => $syaratDokumenId],
            ['layanan_id' => $layananId, 'syarat_id' => $syaratProfilId],
        ]);

        DB::table('dokumen_pegawai')->insert([
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $user->id,
            'file' => 'sk-pangkat.pdf',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('layanan.usul', $layananId))
            ->assertOk()
            ->assertSee('Siap diajukan')
            ->assertSee('Unggah SK Pangkat')
            ->assertSee('Dokumen valid tersedia.')
            ->assertSee('Isi ID Google Scholar')
            ->assertSee('Data profil tersedia.');
    }

    /** @test */
    public function layanan_list_hanya_menampilkan_layanan_yang_memiliki_syarat()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        Pegawai::create([
            'nip' => '198501012010011390',
            'nama' => 'Pegawai List Layanan',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $layananDenganSyaratId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Dengan Syarat',
            'deskripsi' => 'Layanan valid untuk daftar',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanans')->insert([
            'layanan' => 'Layanan Tanpa Syarat',
            'deskripsi' => 'Harus disembunyikan dari daftar',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'syarat' => 'Persyaratan wajib',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananDenganSyaratId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($user)
            ->get(route('layanan.kepegawaian'))
            ->assertOk()
            ->assertSee('Layanan Dengan Syarat')
            ->assertDontSee('Layanan Tanpa Syarat');
    }

    /** @test */
    public function pegawai_tidak_bisa_mengirim_usulan_layanan_jika_dokumen_wajib_belum_valid()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011309',
            'nama' => 'Pegawai Belum Lengkap',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'SKCPNS',
            'nama_dokumen' => 'SK CPNS',
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Penyesuaian Data',
            'deskripsi' => 'Tes blokir usulan',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'SK_CPNS',
            'dokumen_id' => $dokumenId,
            'syarat' => 'Unggah SK CPNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($user)
            ->get(route('layanan.usul', $layananId))
            ->assertOk()
            ->assertSee('Belum memenuhi 1 syarat')
            ->assertSee('Dokumen SK CPNS belum diunggah atau belum divalidasi.');

        $this->from(route('layanan.usul', $layananId))
            ->actingAs($user)
            ->post(route('pegawai.layanan.preview', $layananId), [
                'catatan_pengusul' => 'Mohon tetap diajukan',
            ])
            ->assertRedirect(route('layanan.usul', $layananId));

        $this->assertDatabaseMissing('layanan_pegawais', [
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function syarat_tanpa_mapping_master_document_atau_kode_profil_tidak_boleh_diajukan()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011310',
            'nama' => 'Pegawai Manual',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Surat Tugas',
            'deskripsi' => 'Tes syarat manual',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => null,
            'syarat' => 'Lampirkan surat pengantar manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($user)
            ->get(route('layanan.usul', $layananId))
            ->assertOk()
            ->assertSee('Belum memenuhi 1 syarat')
            ->assertSee('Syarat ini belum tersinkron dengan master dokumen atau kode profil.');

        $this->actingAs($user)
            ->post(route('pegawai.layanan.preview', $layananId), [
                'catatan_pengusul' => 'Persyaratan manual akan saya bawa.',
            ])
            ->assertRedirect(route('layanan.usul', $layananId));

        $this->assertDatabaseMissing('layanan_pegawais', [
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function syarat_dengan_kode_dokumen_master_tetap_terbaca_walau_dokumen_id_belum_diisi()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011311',
            'nama' => 'Pegawai Kode Dokumen',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'SKCPNS',
            'nama_dokumen' => 'SK CPNS',
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Pembaruan Data Kepegawaian',
            'deskripsi' => 'Tes sinkron kode dokumen',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'SK_CPNS',
            'dokumen_id' => null,
            'syarat' => 'Unggah SK CPNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($user)
            ->get(route('layanan.usul', $layananId))
            ->assertOk()
            ->assertSee('Belum memenuhi 1 syarat')
            ->assertSee('Dokumen SK CPNS belum diunggah atau belum divalidasi.');

        DB::table('dokumen_pegawai')->insert([
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $user->id,
            'file' => 'sk-cpns.pdf',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('pegawai.layanan.preview', $layananId), [
                'catatan_pengusul' => 'Dokumen sudah saya lengkapi.',
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $response = $this->post(route('pegawai.layanan.store', $layananId), [
            'konfirmasi_kirim' => '1',
        ]);

        $usulan = LayananPegawai::query()->where('layanan_id', $layananId)->firstOrFail();

        $response->assertRedirect(route('pegawai.layanan.done', $usulan));

        $this->assertDatabaseHas('layanan_pegawais', [
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $user->id,
            'status' => 'usulan',
            'catatan_pengusul' => 'Dokumen sudah saya lengkapi.',
        ]);
    }

    /** @test */
    public function syarat_non_master_bisa_dipenuhi_dengan_upload_bukti_dan_terlihat_di_proses_kepegawaian()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011312',
            'nama' => 'Pegawai Upload Syarat',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $processor = User::factory()->create();
        $processor->assignRole('kepegawaian');

        $layanan = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Bukti Tambahan',
            'deskripsi' => 'Tes upload syarat non master',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratNonMasterId = DB::table('syarats')->insertGetId([
            'kode_syarat' => null,
            'dokumen_id' => null,
            'syarat' => 'Lampiran surat rekomendasi eksternal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layanan,
            'syarat_id' => $syaratNonMasterId,
        ]);

        $this->actingAs($pemohon);

        $this->post(route('pegawai.layanan.preview', $layanan), [
                'catatan_pengusul' => 'Melampirkan bukti syarat tambahan.',
                'syarat_files' => [
                    $syaratNonMasterId => UploadedFile::fake()->create('bukti-syarat.pdf', 120, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layanan));

        $this->get(route('pegawai.layanan.review', $layanan))
            ->assertOk()
            ->assertSee('Lampiran surat rekomendasi eksternal')
            ->assertSee('bukti-syarat.pdf')
            ->assertSee('Data Pegawai');

        $this->post(route('pegawai.layanan.store', $layanan), [
            'konfirmasi_kirim' => '1',
        ])->assertRedirect();

        $usulan = LayananPegawai::query()->where('layanan_id', $layanan)->firstOrFail();

        $this->assertIsArray($usulan->syarat_uploads);
        $this->assertNotEmpty($usulan->syarat_uploads);
        $this->assertSame($syaratNonMasterId, (int) $usulan->syarat_uploads[0]['syarat_id']);
        $this->assertSame('pending', $usulan->syarat_uploads[0]['review_status']);

        $this->actingAs($processor)
            ->get(route('kepegawaian.layanan.edit', $usulan->id))
            ->assertOk()
            ->assertSee('Lampiran surat rekomendasi eksternal')
            ->assertSee('Unggah Bukti')
            ->assertSee('Menunggu Verifikasi')
            ->assertSee('Lihat Bukti');
    }

    /** @test */
    public function pegawai_bisa_kembali_ke_ceklist_tanpa_kehilangan_bukti_upload_draft()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011397',
            'nama' => 'Pegawai Draft Upload',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Draft Checklist',
            'deskripsi' => 'Tes kembali ke ceklist tanpa kehilangan draft upload',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => null,
            'dokumen_id' => null,
            'syarat' => 'Lampiran surat pendukung draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($pemohon);

        $this->post(route('pegawai.layanan.preview', $layananId), [
                'catatan_pengusul' => 'Draft pertama dengan bukti upload.',
                'syarat_files' => [
                    $syaratId => UploadedFile::fake()->create('draft-awal.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $this->get(route('layanan.usul', $layananId))
            ->assertOk()
            ->assertSee('Bukti draft tersimpan:')
            ->assertSee('draft-awal.pdf')
            ->assertSee('Siap diajukan');

        $this->post(route('pegawai.layanan.preview', $layananId), [
                'catatan_pengusul' => 'Draft diperbarui tanpa upload ulang.',
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $this->get(route('pegawai.layanan.review', $layananId))
            ->assertOk()
            ->assertSee('draft-awal.pdf')
            ->assertSee('Draft diperbarui tanpa upload ulang.');

        $response = $this->post(route('pegawai.layanan.store', $layananId), [
            'konfirmasi_kirim' => '1',
        ]);

        $usulan = LayananPegawai::query()->where('layanan_id', $layananId)->firstOrFail();

        $response->assertRedirect(route('pegawai.layanan.done', $usulan));
        $this->assertSame('Draft diperbarui tanpa upload ulang.', $usulan->catatan_pengusul);
        $this->assertSame($syaratId, (int) $usulan->syarat_uploads[0]['syarat_id']);
    }

    /** @test */
    public function layanan_cuti_tetap_bisa_diajukan_meski_persetujuan_atasan_dan_dokumen_pendukung_belum_dilampirkan()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011394',
            'nama' => 'Pegawai Cuti Opsional',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Tes syarat opsional cuti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cutiFormSyaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'CUTI_FORM',
            'dokumen_id' => null,
            'syarat' => 'Formulir Permintaan dan Pemberian Cuti (Lampiran 1.B) yang sudah diisi.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cutiAtsnSyaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'CUTI_ATSN',
            'dokumen_id' => null,
            'syarat' => 'Persetujuan/pertimbangan atasan langsung pada formulir cuti.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cutiBuktiSyaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'CUTI_BUKTI',
            'dokumen_id' => null,
            'syarat' => 'Dokumen pendukung alasan cuti (jika diperlukan sesuai jenis cuti).',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            ['layanan_id' => $layananId, 'syarat_id' => $cutiFormSyaratId],
            ['layanan_id' => $layananId, 'syarat_id' => $cutiAtsnSyaratId],
            ['layanan_id' => $layananId, 'syarat_id' => $cutiBuktiSyaratId],
        ]);

        $this->actingAs($pemohon)
            ->get(route('layanan.usul', $layananId))
            ->assertOk()
            ->assertDontSee('name="cuti_jenis"', false)
            ->assertSee('Belum memenuhi 1 syarat')
            ->assertSee('Formulir Permintaan dan Pemberian Cuti')
            ->assertSee('Opsional');

        $this->post(route('pegawai.layanan.preview', $layananId), [
            'cuti_alamat' => 'Jl. Medan - Banda Aceh No. 10',
            'cuti_alasan' => 'Keperluan keluarga.',
            'cuti_no_telp' => '081234567890',
            'cuti_tanggal_mulai' => '2026-04-13',
            'cuti_tanggal_selesai' => '2026-04-14',
            'catatan_pengusul' => 'Mohon dibuatkan formulir Lampiran 1.B.',
        ])->assertRedirect(route('pegawai.layanan.review', $layananId));

        $this->get(route('pegawai.layanan.review', $layananId))
            ->assertOk()
            ->assertSee('Siap diajukan dengan 2 syarat opsional belum dilampirkan')
            ->assertSee('Persetujuan/pertimbangan atasan langsung pada formulir cuti.')
            ->assertSee('Dokumen pendukung alasan cuti')
            ->assertSee('Opsional');

        $response = $this->post(route('pegawai.layanan.store', $layananId), [
            'konfirmasi_kirim' => '1',
        ]);

        $usulan = LayananPegawai::query()->where('layanan_id', $layananId)->firstOrFail();

        $response->assertRedirect(route('pegawai.layanan.done', $usulan));
        $this->assertSame($pegawai->id, (int) $usulan->pegawai_id);
        $this->assertSame(2, (int) $usulan->cuti_hari_diminta);
        $this->assertSame('2026-04-13', optional($usulan->cuti_tanggal_mulai)->format('Y-m-d'));
        $this->assertSame('2026-04-14', optional($usulan->cuti_tanggal_selesai)->format('Y-m-d'));
        $this->assertSame([], $usulan->syarat_uploads ?? []);
        $this->assertDatabaseHas('layanan_pegawais', [
            'id' => $usulan->id,
            'cuti_hari_diminta' => null,
            'cuti_hari_tersedia' => null,
            'cuti_tanggal_mulai' => null,
            'cuti_tanggal_selesai' => null,
        ]);
        $cutiDetail = $usulan->fresh('cutiDetail')->cutiDetail;
        $this->assertNotNull($cutiDetail);
        $this->assertSame($usulan->id, (int) $cutiDetail->layanan_pegawai_id);
        $this->assertSame('2026-04-13', optional($cutiDetail->tanggal_mulai)->format('Y-m-d'));
        $this->assertSame('2026-04-14', optional($cutiDetail->tanggal_selesai)->format('Y-m-d'));
        $this->assertSame(2, (int) $cutiDetail->hari_diminta);
        $this->assertSame((int) $usulan->cuti_hari_tersedia, (int) $cutiDetail->hari_tersedia_saat_usul);
        $this->assertSame('tahunan', $cutiDetail->jenis_cuti);
        $this->assertSame('Keperluan keluarga.', $cutiDetail->alasan_cuti);
        $this->assertSame('Jl. Medan - Banda Aceh No. 10', $cutiDetail->alamat_menjalankan_cuti);
        $this->assertSame('081234567890', $cutiDetail->nomor_telepon_cuti);
    }

    /** @test */
    public function layanan_usul_cuti_menampilkan_sisa_cuti_dari_perhitungan_dinamis_bukan_dari_kolom_legacy_pegawai()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011392',
            'nama' => 'Pegawai Saldo Dinamis',
            'status_pegawai' => 'PNS',
            'cuti_hari_tersedia' => 12,
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Tes tampilan jatah cuti dinamis',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'CUTI_FORM',
            'dokumen_id' => null,
            'syarat' => 'Formulir cuti',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $saldoDinamis = app(CutiService::class)->getSaldoCuti($pegawai);

        $this->assertSame(24, $saldoDinamis);

        $this->actingAs($pemohon)
            ->get(route('layanan.usul', $layananId))
            ->assertOk()
            ->assertSee('Sisa Cuti Tersedia')
            ->assertSee($saldoDinamis . ' hari')
            ->assertDontSee('value="12 hari"', false);
    }

    /** @test */
    public function pemakaian_cuti_mengurangi_saldo_tahun_terlama_terlebih_dahulu()
    {
        Carbon::setTestNow('2026-04-12 09:00:00');

        try {
            $pemohon = User::factory()->create();
            $pemohon->assignRole('pegawai');

            $pegawai = Pegawai::create([
                'nip' => '198501012010011391',
                'nama' => 'Pegawai Prioritas Carry Over',
                'status_pegawai' => 'PNS',
                'cuti_hari_tersedia' => 12,
                'user_id' => $pemohon->id,
            ]);

            $layananId = DB::table('layanans')->insertGetId([
                'layanan' => 'Layanan Cuti Pegawai',
                'deskripsi' => 'Tes pengurangan saldo dari tahun paling lama',
                'jenis' => 'kepegawaian',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $layananPegawaiId = DB::table('layanan_pegawais')->insertGetId([
                'layanan_id' => $layananId,
                'pegawai_id' => $pegawai->id,
                'user_id' => $pemohon->id,
                'status' => LayananPegawai::STATUS_SELESAI,
                'cuti_hari_diminta' => 99,
                'cuti_hari_tersedia' => 88,
                'processed_at' => Carbon::parse('2026-04-12 09:00:00'),
                'created_at' => Carbon::parse('2026-04-12 08:00:00'),
                'updated_at' => Carbon::parse('2026-04-12 09:00:00'),
            ]);

            DB::table('cuti_layanan_pegawais')->insert([
                'layanan_pegawai_id' => $layananPegawaiId,
                'tanggal_mulai' => '2026-04-13',
                'tanggal_selesai' => '2026-04-15',
                'hari_diminta' => 3,
                'hari_tersedia_saat_usul' => 24,
                'created_at' => Carbon::parse('2026-04-12 08:00:00'),
                'updated_at' => Carbon::parse('2026-04-12 09:00:00'),
            ]);

            $breakdown = app(CutiService::class)->getCutiBreakdown($pegawai);
            $years = collect($breakdown['years'])->keyBy('tahun');

            $this->assertSame(21, $breakdown['total_saldo']);
            $this->assertSame(6, $years[2025]['hari_tersedia']);
            $this->assertSame(0, $years[2025]['hari_diambil']);
            $this->assertSame(6, $years[2025]['sisa']);
            $this->assertSame(6, $years[2025]['kontribusi_saldo']);
            $this->assertSame(6, $years[2024]['hari_tersedia']);
            $this->assertSame(3, $years[2024]['hari_diambil']);
            $this->assertSame(3, $years[2024]['sisa']);
            $this->assertSame(3, $years[2024]['kontribusi_saldo']);
            $this->assertSame(12, $years[2026]['hari_tersedia']);
            $this->assertSame(0, $years[2026]['hari_diambil']);
            $this->assertSame(12, $years[2026]['sisa']);
        } finally {
            Carbon::setTestNow();
        }
    }

    /** @test */
    public function layanan_cuti_menolak_rentang_tanggal_yang_hanya_berisi_akhir_pekan()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        Pegawai::create([
            'nip' => '198501012010011390',
            'nama' => 'Pegawai Akhir Pekan',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Tes penolakan tanggal cuti akhir pekan',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'CUTI_FORM',
            'dokumen_id' => null,
            'syarat' => 'Formulir cuti',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($pemohon)
            ->post(route('pegawai.layanan.preview', $layananId), [
                'cuti_tanggal_mulai' => '2026-04-18',
                'cuti_tanggal_selesai' => '2026-04-19',
                'catatan_pengusul' => 'Mengajukan cuti akhir pekan.',
                'syarat_files' => [
                    $syaratId => UploadedFile::fake()->create('lampiran-1b.pdf', 120, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('layanan.usul', $layananId));

        $this->assertDatabaseMissing('layanan_pegawais', [
            'layanan_id' => $layananId,
        ]);
    }

    /** @test */
    public function layanan_cuti_mengurangi_hari_libur_yang_dikonfigurasi_dari_perhitungan_hari_diminta()
    {
        config()->set('cuti.excluded_dates', ['04-14']);

        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        Pegawai::create([
            'nip' => '198501012010011389',
            'nama' => 'Pegawai Hari Libur',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Tes pengurangan hari libur dari hitungan cuti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'CUTI_FORM',
            'dokumen_id' => null,
            'syarat' => 'Formulir cuti',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($pemohon)
            ->post(route('pegawai.layanan.preview', $layananId), [
                'cuti_tanggal_mulai' => '2026-04-13',
                'cuti_tanggal_selesai' => '2026-04-15',
                'catatan_pengusul' => 'Mengajukan cuti dengan satu hari libur nasional.',
                'syarat_files' => [
                    $syaratId => UploadedFile::fake()->create('lampiran-1b.pdf', 120, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $response = $this->post(route('pegawai.layanan.store', $layananId), [
            'konfirmasi_kirim' => '1',
        ]);

        $usulan = LayananPegawai::query()->where('layanan_id', $layananId)->firstOrFail();

        $response->assertRedirect(route('pegawai.layanan.done', $usulan));
        $this->assertSame(2, (int) $usulan->cuti_hari_diminta);
    }

    /** @test */
    public function layanan_cuti_menolak_rentang_tanggal_yang_hanya_berisi_hari_libur_dikonfigurasi()
    {
        config()->set('cuti.excluded_dates', ['04-14']);

        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        Pegawai::create([
            'nip' => '198501012010011388',
            'nama' => 'Pegawai Libur Konfigurasi',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Tes penolakan tanggal libur konfigurasi',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'CUTI_FORM',
            'dokumen_id' => null,
            'syarat' => 'Formulir cuti',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($pemohon)
            ->post(route('pegawai.layanan.preview', $layananId), [
                'cuti_tanggal_mulai' => '2026-04-14',
                'cuti_tanggal_selesai' => '2026-04-14',
                'catatan_pengusul' => 'Mengajukan cuti pada hari libur konfigurasi.',
                'syarat_files' => [
                    $syaratId => UploadedFile::fake()->create('lampiran-1b.pdf', 120, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('layanan.usul', $layananId));

        $this->assertDatabaseMissing('layanan_pegawais', [
            'layanan_id' => $layananId,
        ]);
    }

    /** @test */
    public function kode_syarat_opsional_cuti_tetap_menjadi_wajib_bila_dipakai_pada_layanan_non_cuti()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011393',
            'nama' => 'Pegawai Non Cuti',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Tes opsional hanya berlaku pada cuti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'kode_syarat' => 'CUTI_BUKTI',
            'dokumen_id' => null,
            'syarat' => 'Lampiran bukti pendukung',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($pemohon)
            ->get(route('layanan.usul', $layananId))
            ->assertOk()
            ->assertSee('Belum memenuhi 1 syarat')
            ->assertDontSee('Opsional');

        $this->from(route('layanan.usul', $layananId))
            ->post(route('pegawai.layanan.preview', $layananId), [
                'catatan_pengusul' => 'Saya belum melampirkan bukti.',
            ])
            ->assertRedirect(route('layanan.usul', $layananId));

        $this->assertDatabaseMissing('layanan_pegawais', [
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
        ]);
    }

    /** @test */
    public function layanan_cuti_mencatat_jumlah_hari_dan_mengurangi_sisa_cuti_saat_selesai()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011395',
            'nama' => 'Pegawai Layanan Cuti',
            'status_pegawai' => 'PNS',
            'cuti_hari_tersedia' => 12,
            'user_id' => $pemohon->id,
        ]);

        $processor = User::factory()->create();
        $processor->assignRole('kepegawaian');

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Pengajuan cuti tahunan',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $saldoAwal = app(CutiService::class)->getSaldoCuti($pegawai);

        $this->actingAs($pemohon)
            ->post(route('pegawai.layanan.preview', $layananId), [
                'cuti_tanggal_mulai' => '2026-04-13',
                'cuti_tanggal_selesai' => '2026-04-15',
                'catatan_pengusul' => 'Mengajukan cuti tahunan 3 hari.',
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $this->post(route('pegawai.layanan.store', $layananId), [
            'konfirmasi_kirim' => '1',
        ])->assertRedirect();

        $usulan = LayananPegawai::query()->where('layanan_id', $layananId)->firstOrFail();
        $this->assertSame(3, (int) $usulan->cuti_hari_diminta);
        $this->assertSame($saldoAwal, (int) $usulan->cuti_hari_tersedia);
        $this->assertSame('2026-04-13', optional($usulan->cuti_tanggal_mulai)->format('Y-m-d'));
        $this->assertSame('2026-04-15', optional($usulan->cuti_tanggal_selesai)->format('Y-m-d'));
        $this->assertSame('usulan', $usulan->status);

        $this->actingAs($processor)
            ->post(route('kepegawaian.cuti.update', $usulan->id), [
                '_method' => 'PUT',
                'layanan_id' => $layananId,
                'status' => 'selesai',
                'catatan_proses' => 'Disetujui sesuai hak cuti.',
                'output_file' => UploadedFile::fake()->create('hasil-cuti.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('kepegawaian.cuti.proses'));

        $usulan->refresh();
        $pegawai->refresh();

        $this->assertSame('selesai', $usulan->status);
        $this->assertSame($saldoAwal - 3, app(CutiService::class)->getSaldoCuti($pegawai));
    }

    /** @test */
    public function halaman_riwayat_layanan_pegawai_menampilkan_rekap_dari_modul_cuti_tersendiri()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011387',
            'nama' => 'Pegawai Rekap Cuti',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Tes rekap modul cuti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nonCutiLayananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Tes riwayat layanan umum tanpa cuti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $layananPegawaiId = DB::table('layanan_pegawais')->insertGetId([
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Mengajukan cuti untuk rekap.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_pegawais')->insert([
            'layanan_id' => $nonCutiLayananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => LayananPegawai::STATUS_PROSES,
            'catatan_pengusul' => 'Mengajukan mutasi biasa.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cuti_layanan_pegawais')->insert([
            'layanan_pegawai_id' => $layananPegawaiId,
            'tanggal_mulai' => '2026-04-13',
            'tanggal_selesai' => '2026-04-14',
            'hari_diminta' => 2,
            'hari_tersedia_saat_usul' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($pemohon)
            ->get(route('pegawai.layanan'))
            ->assertOk()
            ->assertSee('Riwayat Usulan')
            ->assertSee('Mutasi Internal')
            ->assertDontSee('Hari Cuti')
            ->assertSee('Rekap Cuti')
            ->assertSee('Sisa Jatah Cuti Saat Pengajuan')
            ->assertSee('13-04-2026 s.d. 14-04-2026')
            ->assertSee('2 hari')
            ->assertSee('24 hari');
    }

    /** @test */
    public function pegawai_bisa_mencetak_formulir_cuti_dari_usulan_yang_disimpan()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011395',
            'nama' => 'Pegawai Cetak Cuti',
            'status_pegawai' => 'PNS',
            'alamat' => 'Jl. Merdeka No. 1',
            'no_hp' => '081234567890',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Tes cetak formulir cuti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $layananPegawaiId = DB::table('layanan_pegawais')->insertGetId([
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Mohon proses cuti tahunan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cuti_layanan_pegawais')->insert([
            'layanan_pegawai_id' => $layananPegawaiId,
            'jenis_cuti' => 'tahunan',
            'alasan_cuti' => 'Keperluan keluarga.',
            'alamat_menjalankan_cuti' => 'Dusun Pangkalim, Kota Lhokseumawe',
            'nomor_telepon_cuti' => '081234567890',
            'tanggal_mulai' => '2026-07-22',
            'tanggal_selesai' => '2026-07-22',
            'hari_diminta' => 1,
            'hari_tersedia_saat_usul' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($pemohon)
            ->get(route('pegawai.layanan.cuti.print', $layananPegawaiId))
            ->assertOk()
            ->assertSee('FORMULIR PERMINTAAN DAN PEMBERIAN CUTI')
            ->assertSee('PEGAWAI CETAK CUTI')
            ->assertSee('Cuti Tahunan')
            ->assertSee('Keperluan keluarga.')
            ->assertSee('Dusun Pangkalim, Kota Lhokseumawe')
            ->assertSee('081234567890');
    }

    /** @test */
    public function kepegawaian_memiliki_halaman_proses_cuti_tersendiri_dan_hanya_menampilkan_usulan_cuti()
    {
        $processor = User::factory()->create();
        $processor->assignRole('kepegawaian');

        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011386',
            'nama' => 'Pegawai Proses Cuti',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $cutiLayananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Halaman proses cuti tersendiri',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nonCutiLayananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Bukan cuti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cutiUsulanId = DB::table('layanan_pegawais')->insertGetId([
            'layanan_id' => $cutiLayananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Mengajukan cuti tahunan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cuti_layanan_pegawais')->insert([
            'layanan_pegawai_id' => $cutiUsulanId,
            'tanggal_mulai' => '2026-04-13',
            'tanggal_selesai' => '2026-04-14',
            'hari_diminta' => 2,
            'hari_tersedia_saat_usul' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nonCutiUsulanId = DB::table('layanan_pegawais')->insertGetId([
            'layanan_id' => $nonCutiLayananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Mengajukan mutasi.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($processor)
            ->get(route('kepegawaian.cuti.proses'))
            ->assertOk()
            ->assertSee('Proses Cuti Pegawai')
            ->assertSee('Layanan Cuti Pegawai')
            ->assertSee('Pegawai Proses Cuti')
            ->assertSee('Sisa Jatah Cuti Saat Pengajuan')
            ->assertDontSee('Mutasi Internal');

        $this->actingAs($processor)
            ->get(route('kepegawaian.layanan.proses'))
            ->assertOk()
            ->assertSee('Mutasi Internal')
            ->assertDontSee('Layanan Cuti Pegawai');

        $this->actingAs($processor)
            ->get(route('kepegawaian.cuti.edit', $cutiUsulanId))
            ->assertOk()
            ->assertSee('Informasi Cuti')
            ->assertSee('Sisa Cuti Saat Pengajuan')
            ->assertSee('13-04-2026 s.d. 14-04-2026')
            ->assertSee('2 hari')
            ->assertSee('24 hari')
            ->assertDontSee('01-05-2026')
            ->assertDontSee('99 hari')
            ->assertDontSee('77 hari');

        $this->actingAs($processor)
            ->get(route('kepegawaian.layanan.edit', $cutiUsulanId))
            ->assertNotFound();

        $this->actingAs($processor)
            ->get(route('kepegawaian.cuti.edit', $nonCutiUsulanId))
            ->assertNotFound();
    }

    /** @test */
    public function kepegawaian_tidak_bisa_menyetujui_usulan_jika_bukti_syarat_tambahan_belum_direview_approved()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011313',
            'nama' => 'Pegawai Review Pending',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $processor = User::factory()->create();
        $processor->assignRole('kepegawaian');

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Review Bukti',
            'deskripsi' => 'Tes blok approval pending review',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'syarat' => 'Lampirkan surat bukti tambahan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($pemohon);

        $this->post(route('pegawai.layanan.preview', $layananId), [
                'syarat_files' => [
                    $syaratId => UploadedFile::fake()->create('review-pending.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $this->post(route('pegawai.layanan.store', $layananId), [
            'konfirmasi_kirim' => '1',
        ])->assertRedirect();

        $usulan = LayananPegawai::query()->where('layanan_id', $layananId)->firstOrFail();

        $this->from(route('kepegawaian.layanan.edit', $usulan->id))
            ->actingAs($processor)
            ->post(route('kepegawaian.layanan.update', $usulan->id), [
                '_method' => 'PUT',
                'layanan_id' => $layananId,
                'status' => 'selesai',
                'catatan_proses' => 'Mencoba approve.',
                'output_file' => UploadedFile::fake()->create('hasil-review-pending.pdf', 100, 'application/pdf'),
                'syarat_reviews' => [
                    $syaratId => [
                        'status' => 'pending',
                        'catatan' => 'Belum dicek.',
                    ],
                ],
            ])
            ->assertRedirect(route('kepegawaian.layanan.edit', $usulan->id));

        $usulan->refresh();
        $this->assertSame('usulan', $usulan->status);
        $this->assertSame('pending', $usulan->syarat_uploads[0]['review_status']);
    }

    /** @test */
    public function kepegawaian_bisa_menyetujui_usulan_setelah_bukti_syarat_tambahan_disetujui()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011314',
            'nama' => 'Pegawai Review Approve',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $processor = User::factory()->create();
        $processor->assignRole('kepegawaian');

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Layanan Final Review',
            'deskripsi' => 'Tes approval review bukti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syaratId = DB::table('syarats')->insertGetId([
            'syarat' => 'Lampirkan bukti pendukung final',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_syarat')->insert([
            'layanan_id' => $layananId,
            'syarat_id' => $syaratId,
        ]);

        $this->actingAs($pemohon);

        $this->post(route('pegawai.layanan.preview', $layananId), [
                'syarat_files' => [
                    $syaratId => UploadedFile::fake()->create('review-approved.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('pegawai.layanan.review', $layananId));

        $this->post(route('pegawai.layanan.store', $layananId), [
            'konfirmasi_kirim' => '1',
        ])->assertRedirect();

        $usulan = LayananPegawai::query()->where('layanan_id', $layananId)->firstOrFail();

        $this->actingAs($processor)
            ->post(route('kepegawaian.layanan.update', $usulan->id), [
                '_method' => 'PUT',
                'layanan_id' => $layananId,
                'status' => 'selesai',
                'catatan_proses' => 'Bukti tambahan sudah valid.',
                'output_file' => UploadedFile::fake()->create('hasil-review-approved.pdf', 100, 'application/pdf'),
                'syarat_reviews' => [
                    $syaratId => [
                        'status' => 'approved',
                        'catatan' => 'Dokumen bukti sudah sesuai.',
                    ],
                ],
            ])
            ->assertRedirect(route('kepegawaian.layanan.proses'));

        $usulan->refresh();
        $this->assertSame('selesai', $usulan->status);
        $this->assertSame('approved', $usulan->syarat_uploads[0]['review_status']);
        $this->assertSame('Dokumen bukti sudah sesuai.', $usulan->syarat_uploads[0]['review_catatan']);
        $this->assertSame($processor->id, $usulan->syarat_uploads[0]['reviewed_by']);
    }

    /** @test */
    public function kepegawaian_bisa_menyelesaikan_layanan_tanpa_output_dan_jenis_layanan_tetap_readonly()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011333',
            'nama' => 'Pegawai Wajib Output',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $processor = User::factory()->create();
        $processor->assignRole('kepegawaian');

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Surat Tugas',
            'deskripsi' => 'Output opsional saat selesai',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $layananLainId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Dipakai untuk memastikan jenis layanan tidak bisa diubah saat proses',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usulanId = DB::table('layanan_pegawais')->insertGetId([
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => LayananPegawai::STATUS_PROSES,
            'catatan_pengusul' => 'Mohon diterbitkan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($processor)
            ->get(route('kepegawaian.layanan.edit', $usulanId))
            ->assertOk()
            ->assertSee('Surat Tugas')
            ->assertSee('type="hidden" name="layanan_id" value="' . $layananId . '"', false)
            ->assertDontSee('<select name="layanan_id"', false);

        $this->from(route('kepegawaian.layanan.edit', $usulanId))
            ->actingAs($processor)
            ->put(route('kepegawaian.layanan.update', $usulanId), [
                'layanan_id' => $layananLainId,
                'status' => LayananPegawai::STATUS_SELESAI,
                'catatan_proses' => 'Siap diselesaikan.',
            ])
            ->assertRedirect(route('kepegawaian.layanan.proses'));

        $this->assertDatabaseHas('layanan_pegawais', [
            'id' => $usulanId,
            'layanan_id' => $layananId,
            'status' => LayananPegawai::STATUS_SELESAI,
            'catatan_proses' => 'Siap diselesaikan.',
            'output_path' => null,
        ]);
    }

    /** @test */
    public function kepegawaian_bisa_mengunggah_output_dan_pegawai_bisa_melihatnya_di_riwayat_layanan()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011334',
            'nama' => 'Pegawai Output Final',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $processor = User::factory()->create();
        $processor->assignRole('kepegawaian');

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Tes upload output final layanan',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usulan = LayananPegawai::create([
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => LayananPegawai::STATUS_PROSES,
            'catatan_pengusul' => 'Mohon proses hingga selesai.',
        ]);

        $outputFile = UploadedFile::fake()->create('surat-hasil-mutasi.pdf', 120, 'application/pdf');

        $this->actingAs($processor)
            ->post(route('kepegawaian.layanan.update', $usulan->id), [
                '_method' => 'PUT',
                'layanan_id' => $layananId,
                'status' => LayananPegawai::STATUS_SELESAI,
                'catatan_proses' => 'Output sudah diterbitkan.',
                'output_file' => $outputFile,
            ])
            ->assertRedirect(route('kepegawaian.layanan.proses'));

        $usulan->refresh();

        $this->assertSame(LayananPegawai::STATUS_SELESAI, $usulan->status);
        $this->assertSame('surat-hasil-mutasi.pdf', $usulan->output_original_name);
        $this->assertSame($processor->id, $usulan->output_uploaded_by);
        $this->assertNotNull($usulan->output_uploaded_at);
        $this->assertNotNull($usulan->output_path);
        $this->assertTrue(File::exists(public_path('file/' . ltrim((string) $usulan->output_path, '/'))));

        $this->actingAs($pemohon)
            ->get(route('pegawai.layanan'))
            ->assertOk()
            ->assertSee('Unduh Output')
            ->assertSee('surat-hasil-mutasi.pdf');

        File::deleteDirectory(public_path('file/' . $pegawai->nip . '/layanan-output'));
    }

    /** @test */
    public function kepegawaian_bisa_memproses_usulan_layanan()
    {
        $pemohon = User::factory()->create();
        $pemohon->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011304',
            'nama' => 'Pegawai Proses',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $processor = User::factory()->create();
        $processor->assignRole('kepegawaian');

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Tes proses',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usulanId = DB::table('layanan_pegawais')->insertGetId([
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => 'usulan',
            'catatan_pengusul' => 'Mohon disetujui',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($processor)
            ->put(route('kepegawaian.layanan.update', $usulanId), [
                'layanan_id' => $layananId,
                'status' => 'proses',
                'catatan_proses' => 'Sudah diverifikasi.',
            ])
            ->assertRedirect(route('kepegawaian.layanan.proses'));

        $this->assertDatabaseHas('layanan_pegawais', [
            'id' => $usulanId,
            'status' => 'proses',
            'catatan_proses' => 'Sudah diverifikasi.',
            'processed_by' => $processor->id,
        ]);
    }

    /** @test */
    public function pegawai_tidak_boleh_mengakses_halaman_proses_kepegawaian()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        Pegawai::create([
            'nip' => '198501012010011305',
            'nama' => 'Pegawai Biasa',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('kepegawaian.layanan.proses'))
            ->assertForbidden();
    }

    /** @test */
    public function kepegawaian_layanan_proses_menampilkan_kolom_triage_dan_mengurutkan_berdasarkan_risiko_sla_dan_prioritas()
    {
        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        $pemohonLama = User::factory()->create();
        $pemohonLama->assignRole('pegawai');
        $pegawaiLama = Pegawai::create([
            'nip' => '198501012010011388',
            'nama' => 'Pegawai Prioritas Lama',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohonLama->id,
        ]);

        $pemohonBaru = User::factory()->create();
        $pemohonBaru->assignRole('pegawai');
        $pegawaiBaru = Pegawai::create([
            'nip' => '198501012010011389',
            'nama' => 'Pegawai Prioritas Baru',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohonBaru->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Tes urutan smart triage',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_pegawais')->insert([
            [
                'layanan_id' => $layananId,
                'pegawai_id' => $pegawaiLama->id,
                'user_id' => $pemohonLama->id,
                'status' => LayananPegawai::STATUS_USULAN,
                'catatan_pengusul' => 'Mohon segera diproses karena mendesak.',
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            [
                'layanan_id' => $layananId,
                'pegawai_id' => $pegawaiBaru->id,
                'user_id' => $pemohonBaru->id,
                'status' => LayananPegawai::STATUS_USULAN,
                'catatan_pengusul' => 'Mohon diproses sesuai antrean.',
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ],
        ]);

        $this->actingAs($kepegawaian)
            ->get(route('kepegawaian.layanan.proses'))
            ->assertOk()
            ->assertSee('Prioritas')
            ->assertSee('Risiko SLA')
            ->assertSee('Jatuh Tempo SLA')
            ->assertSeeInOrder(['Pegawai Prioritas Lama', 'Pegawai Prioritas Baru']);
    }

    /** @test */
    public function kepegawaian_can_search_layanan_process_by_pegawai_name_or_layanan_name()
    {
        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        $pemohonA = User::factory()->create();
        $pemohonA->assignRole('pegawai');
        $pegawaiA = Pegawai::create([
            'nip' => '198501012010011306',
            'nama' => 'Budi Santoso',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohonA->id,
        ]);

        $pemohonB = User::factory()->create();
        $pemohonB->assignRole('pegawai');
        $pegawaiB = Pegawai::create([
            'nip' => '198501012010011307',
            'nama' => 'Sari Wulan',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohonB->id,
        ]);

        $layananAId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Tes layanan mutasi',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $layananBId = DB::table('layanans')->insertGetId([
            'layanan' => 'Cuti Tahunan',
            'deskripsi' => 'Tes layanan cuti',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_pegawais')->insertGetId([
            'layanan_id' => $layananAId,
            'pegawai_id' => $pegawaiA->id,
            'user_id' => $pemohonA->id,
            'status' => 'usulan',
            'catatan_pengusul' => 'Mutasi untuk Budi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cutiLayananPegawaiId = DB::table('layanan_pegawais')->insertGetId([
            'layanan_id' => $layananBId,
            'pegawai_id' => $pegawaiB->id,
            'user_id' => $pemohonB->id,
            'status' => 'usulan',
            'catatan_pengusul' => 'Cuti untuk Sari',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cuti_layanan_pegawais')->insert([
            'layanan_pegawai_id' => $cutiLayananPegawaiId,
            'tanggal_mulai' => '2026-04-13',
            'tanggal_selesai' => '2026-04-14',
            'hari_diminta' => 2,
            'hari_tersedia_saat_usul' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($kepegawaian)
            ->get(route('kepegawaian.layanan.proses', ['q' => 'Budi']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Sari Wulan');

        $this->actingAs($kepegawaian)
            ->get(route('kepegawaian.layanan.proses', ['q' => 'Cuti']))
            ->assertOk()
            ->assertDontSee('Cuti Tahunan')
            ->assertDontSee('Mutasi Internal');
    }
}
