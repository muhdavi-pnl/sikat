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

class KepegawaianDokumenFeatureTest extends TestCase
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
    public function kepegawaian_can_open_dokumen_review_list_and_manage_page()
    {
        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011601',
            'nama' => 'Pegawai Review',
            'status_pegawai' => 'PNS',
        ]);

        $this->actingAs($kepegawaian)
            ->get(route('kepegawaian.dokumen.index'))
            ->assertOk()
            ->assertSee('PEGAWAI REVIEW');

        $this->actingAs($kepegawaian)
            ->get(route('kepegawaian.dokumen.show', $pegawai))
            ->assertOk()
            ->assertSee('Kelola Dokumen Pegawai')
            ->assertSee('PEGAWAI REVIEW')
            ->assertSee('Skor Kelengkapan Data')
            ->assertSee('Progress Kelengkapan Total');
    }

    /** @test */
    public function kepegawaian_can_upload_dokumen_for_pegawai_and_mark_it_valid()
    {
        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011602',
            'nama' => 'Pegawai Upload Kepegawaian',
            'status_pegawai' => 'PNS',
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'SK01',
            'nama_dokumen' => 'SK Pengangkatan',
        ]);

        $this->actingAs($kepegawaian)
            ->post(route('kepegawaian.dokumen.store', $pegawai), [
                'dokumen_id' => $dokumenId,
                'nomor' => 'SK-001',
                'tanggal' => '2026-04-11',
                'status' => '1',
                'keterangan' => 'diunggah admin kepegawaian',
                'file' => UploadedFile::fake()->create('sk.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('kepegawaian.dokumen.show', $pegawai));

        $this->assertDatabaseHas('dokumen_pegawai', [
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $kepegawaian->id,
            'nomor' => 'SK-001',
            'status' => true,
        ]);
    }

    /** @test */
    public function kepegawaian_dokumen_list_prioritizes_pegawai_with_latest_upload()
    {
        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        $pegawaiLama = Pegawai::create([
            'nip' => '198501012010011610',
            'nama' => 'Pegawai Lama',
            'status_pegawai' => 'PNS',
        ]);

        $pegawaiTerbaru = Pegawai::create([
            'nip' => '198501012010011611',
            'nama' => 'Pegawai Terbaru',
            'status_pegawai' => 'PNS',
        ]);

        $pegawaiTanpaUpload = Pegawai::create([
            'nip' => '198501012010011612',
            'nama' => 'Pegawai Tanpa Upload',
            'status_pegawai' => 'PNS',
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'URUT',
            'nama_dokumen' => 'Dokumen Urut',
        ]);

        DB::table('dokumen_pegawai')->insert([
            [
                'dokumen_id' => $dokumenId,
                'pegawai_id' => $pegawaiLama->id,
                'user_id' => $kepegawaian->id,
                'file' => 'lama.pdf',
                'status' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'dokumen_id' => $dokumenId,
                'pegawai_id' => $pegawaiTerbaru->id,
                'user_id' => $kepegawaian->id,
                'file' => 'baru.pdf',
                'status' => true,
                'created_at' => now()->subHour(),
                'updated_at' => now()->subHour(),
            ],
        ]);

        $this->actingAs($kepegawaian)
            ->get(route('kepegawaian.dokumen.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'PEGAWAI TERBARU',
                'PEGAWAI LAMA',
                'PEGAWAI TANPA UPLOAD',
            ]);
    }

    /** @test */
    public function kepegawaian_can_review_and_update_existing_dokumen_status()
    {
        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011603',
            'nama' => 'Pegawai Existing Dokumen',
            'status_pegawai' => 'PNS',
            'user_id' => $pegawaiUser->id,
        ]);

        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'DOK1',
            'nama_dokumen' => 'Dokumen Existing',
        ]);

        DokumenPegawai::create([
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pegawaiUser->id,
            'file' => 'lama.pdf',
            'nomor' => 'OLD-001',
            'status' => false,
        ]);

        $this->actingAs($kepegawaian)
            ->put(route('kepegawaian.dokumen.update', [$pegawai, $dokumenId]), [
                'nomor' => 'REV-001',
                'tanggal' => '2026-04-11',
                'status' => '1',
                'keterangan' => 'sudah diverifikasi kepegawaian',
            ])
            ->assertRedirect(route('kepegawaian.dokumen.show', $pegawai));

        $this->assertDatabaseHas('dokumen_pegawai', [
            'dokumen_id' => $dokumenId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $kepegawaian->id,
            'nomor' => 'REV-001',
            'status' => true,
            'keterangan' => 'sudah diverifikasi kepegawaian',
        ]);
    }

    /** @test */
    public function pegawai_cannot_access_kepegawaian_dokumen_module()
    {
        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011604',
            'nama' => 'Pegawai Forbidden',
            'status_pegawai' => 'PNS',
            'user_id' => $pegawaiUser->id,
        ]);

        $this->actingAs($pegawaiUser)
            ->get(route('kepegawaian.dokumen.index'))
            ->assertForbidden();

        $this->actingAs($pegawaiUser)
            ->get(route('kepegawaian.dokumen.show', $pegawai))
            ->assertForbidden();
    }
}

