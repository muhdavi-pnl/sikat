<?php

namespace Tests\Feature;

use App\Models\DokumenPegawai;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PegawaiDokumenFeatureTest extends TestCase
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
    public function pegawai_can_see_own_document_history_uploaded_by_pegawai_and_kepegawaian()
    {
        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $kepegawaianUser = User::factory()->create();
        $kepegawaianUser->assignRole('kepegawaian');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011201',
            'nama' => 'Pegawai Dokumen',
            'status_pegawai' => 'PNS',
            'user_id' => $pegawaiUser->id,
        ]);

        $dokumenAId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'DOKA',
            'nama_dokumen' => 'Dokumen A',
        ]);

        $dokumenBId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'DOKB',
            'nama_dokumen' => 'Dokumen B',
        ]);

        DokumenPegawai::create([
            'dokumen_id' => $dokumenAId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pegawaiUser->id,
            'file' => 'doka.pdf',
        ]);

        DokumenPegawai::create([
            'dokumen_id' => $dokumenBId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $kepegawaianUser->id,
            'file' => 'dokb.pdf',
        ]);

        $this->actingAs($pegawaiUser)
            ->get(route('pegawai.dokumen'))
            ->assertOk()
            ->assertSee('Dokumen A')
            ->assertSee('Dokumen B')
            ->assertSee('Pegawai')
            ->assertSee('Kepegawaian');
    }

    /** @test */
    public function pegawai_can_upload_document_from_dokumen_page()
    {
        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011202',
            'nama' => 'Pegawai Upload',
            'status_pegawai' => 'PNS',
            'user_id' => $pegawaiUser->id,
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'UPLD',
            'nama_dokumen' => 'Dokumen Upload',
        ]);

        $response = $this->actingAs($pegawaiUser)
            ->post(route('pegawai.dokumen.store'), [
                'dokumen_id' => $dokumenId,
                'nomor' => 'DOC-001',
                'tanggal' => '2026-04-11',
                'keterangan' => 'unggahan test',
                'file' => UploadedFile::fake()->create('dokumen.pdf', 200, 'application/pdf'),
            ]);

        $response->assertRedirect(route('pegawai.dokumen'));

        $this->assertDatabaseHas('dokumen_pegawai', [
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pegawaiUser->id,
            'nomor' => 'DOC-001',
        ]);
    }

    /** @test */
    public function uploading_the_same_dokumen_type_replaces_the_existing_upload_for_that_pegawai()
    {
        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011203',
            'nama' => 'Pegawai Replace Upload',
            'status_pegawai' => 'PNS',
            'user_id' => $pegawaiUser->id,
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'UPR1',
            'nama_dokumen' => 'Dokumen Replace',
        ]);

        DokumenPegawai::create([
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pegawaiUser->id,
            'file' => 'lama.pdf',
            'nomor' => 'OLD-001',
        ]);

        $response = $this->actingAs($pegawaiUser)
            ->post(route('pegawai.dokumen.store'), [
                'dokumen_id' => $dokumenId,
                'nomor' => 'NEW-001',
                'tanggal' => '2026-04-11',
                'keterangan' => 'unggahan pengganti',
                'file' => UploadedFile::fake()->create('dokumen-baru.pdf', 200, 'application/pdf'),
            ]);

        $response->assertRedirect(route('pegawai.dokumen'));

        $this->assertDatabaseCount('dokumen_pegawai', 1);
        $this->assertDatabaseHas('dokumen_pegawai', [
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pegawaiUser->id,
            'nomor' => 'NEW-001',
        ]);
        $this->assertDatabaseMissing('dokumen_pegawai', [
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'nomor' => 'OLD-001',
        ]);
    }

    /** @test */
    public function pegawai_can_see_rejected_document_status_and_rejection_reason()
    {
        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $kepegawaianUser = User::factory()->create();
        $kepegawaianUser->assignRole('kepegawaian');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011204',
            'nama' => 'Pegawai Lihat Tolak',
            'status_pegawai' => 'PNS',
            'user_id' => $pegawaiUser->id,
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'REJ1',
            'nama_dokumen' => 'Dokumen Ditolak Pegawai',
        ]);

        DokumenPegawai::create([
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $kepegawaianUser->id,
            'file' => 'dokumen_tolak.pdf',
            'status' => DokumenPegawai::STATUS_REJECTED,
            'alasan_penolakan' => 'Kualitas scan tidak jelas, mohon scan ulang asli berwarna',
        ]);

        $this->actingAs($pegawaiUser)
            ->get(route('pegawai.dokumen'))
            ->assertOk()
            ->assertSee('Ditolak')
            ->assertSee('Kualitas scan tidak jelas, mohon scan ulang asli berwarna');
    }

    /** @test */
    public function pegawai_reuploading_rejected_document_resets_status_to_pending_and_clears_rejection_reason()
    {
        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $kepegawaianUser = User::factory()->create();
        $kepegawaianUser->assignRole('kepegawaian');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011205',
            'nama' => 'Pegawai Reupload Tolak',
            'status_pegawai' => 'PNS',
            'user_id' => $pegawaiUser->id,
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'REJ2',
            'nama_dokumen' => 'Dokumen Reupload',
        ]);

        DokumenPegawai::create([
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $kepegawaianUser->id,
            'file' => 'dokumen_lama_rusak.pdf',
            'status' => DokumenPegawai::STATUS_REJECTED,
            'alasan_penolakan' => 'Halaman 2 terpotong',
        ]);

        $response = $this->actingAs($pegawaiUser)
            ->post(route('pegawai.dokumen.store'), [
                'dokumen_id' => $dokumenId,
                'nomor' => 'DOC-FIXED-001',
                'tanggal' => '2026-04-11',
                'keterangan' => 'unggah ulang dokumen lengkap',
                'file' => UploadedFile::fake()->create('dokumen-lengkap.pdf', 200, 'application/pdf'),
            ]);

        $response->assertRedirect(route('pegawai.dokumen'));

        $this->assertDatabaseHas('dokumen_pegawai', [
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'status' => DokumenPegawai::STATUS_PENDING,
            'alasan_penolakan' => null,
            'nomor' => 'DOC-FIXED-001',
        ]);
    }
}

