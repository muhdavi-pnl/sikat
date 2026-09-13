<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Syarat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LayananManagementFeatureTest extends TestCase
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
    public function non_privileged_user_cannot_manage_layanan_admin_pages()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $this->actingAs($user)
            ->get(route('layanan.index'))
            ->assertForbidden();
    }

    /** @test */
    public function kepegawaian_cannot_manage_layanan_admin_pages()
    {
        $manager = User::factory()->create();
        $manager->assignRole('kepegawaian');

        $this->actingAs($manager)
            ->get(route('layanan.index'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_create_layanan_with_attached_syarat()
    {
        $manager = $this->createSuperAdminUser();
        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'SKP',
            'nama_dokumen' => 'SK Pangkat',
        ]);

        $syaratDokumen = Syarat::create([
            'syarat' => 'Unggah SK Pangkat',
            'dokumen_id' => $dokumenId,
        ]);

        $syaratProfil = Syarat::create([
            'syarat' => 'Isi ID Google Scholar',
            'kode_syarat' => 'ID_GSCHOLAR',
        ]);

        $response = $this->actingAs($manager)
            ->post(route('layanan.store'), [
                'layanan' => 'Kenaikan Jabatan Akademik',
                'deskripsi' => 'Pengajuan kenaikan jabatan dosen.',
                'jenis' => 'fungsional',
                'syarat_ids' => [$syaratDokumen->id, $syaratProfil->id],
            ]);

        $layanan = Layanan::query()->first();

        $response->assertRedirect(route('layanan.show', $layanan));
        $this->assertNotNull($layanan);
        $this->assertSame('Kenaikan Jabatan Akademik', $layanan->layanan);
        $this->assertSame('fungsional', $layanan->jenis);
        $this->assertEqualsCanonicalizing(
            [$syaratDokumen->id, $syaratProfil->id],
            $layanan->syarat()->pluck('syarats.id')->all()
        );
    }

    /** @test */
    public function manager_can_update_layanan_and_sync_attached_syarat()
    {
        $manager = $this->createSuperAdminUser();

        $syaratManual = Syarat::create([
            'syarat' => 'Verifikasi manual oleh admin',
        ]);

        $syaratProfil = Syarat::create([
            'syarat' => 'Lengkapi email pegawai',
            'kode_syarat' => 'EMAIL',
        ]);

        $layanan = Layanan::create([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Deskripsi lama',
            'jenis' => 'kepegawaian',
        ]);
        $layanan->syarat()->sync([$syaratManual->id]);

        $this->actingAs($manager)
            ->put(route('layanan.update', $layanan), [
                'layanan' => 'Mutasi Internal Terbaru',
                'deskripsi' => 'Deskripsi baru',
                'jenis' => 'kepegawaian',
                'syarat_ids' => [$syaratProfil->id],
            ])
            ->assertRedirect(route('layanan.show', $layanan));

        $layanan->refresh();
        $this->assertSame('Mutasi Internal Terbaru', $layanan->layanan);
        $this->assertSame('Deskripsi baru', $layanan->deskripsi);
        $this->assertEquals([$syaratProfil->id], $layanan->syarat()->pluck('syarats.id')->all());
    }

    /** @test */
    public function layanan_detail_page_shows_attached_syarat_and_mapping_type()
    {
        $manager = $this->createSuperAdminUser();
        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'SKCPNS',
            'nama_dokumen' => 'SK CPNS',
        ]);

        $syaratDokumen = Syarat::create([
            'syarat' => 'Unggah SK CPNS',
            'dokumen_id' => $dokumenId,
        ]);

        $syaratManual = Syarat::create([
            'syarat' => 'Bawa berkas asli saat verifikasi',
        ]);

        $layanan = Layanan::create([
            'layanan' => 'Penyesuaian Data Pegawai',
            'deskripsi' => 'Tes detail layanan',
            'jenis' => 'kepegawaian',
        ]);
        $layanan->syarat()->sync([$syaratDokumen->id, $syaratManual->id]);

        $this->actingAs($manager)
            ->get(route('layanan.show', $layanan))
            ->assertOk()
            ->assertSee('Penyesuaian Data Pegawai')
            ->assertSee('Unggah SK CPNS')
            ->assertSee('SK CPNS')
            ->assertSee('Dokumen')
            ->assertSee('Manual');
    }

    /** @test */
    public function layanan_detail_page_shows_optional_badge_for_cuti_specific_optional_requirements()
    {
        $manager = $this->createSuperAdminUser();

        $syaratOptionalCuti = Syarat::create([
            'syarat' => 'Persetujuan atasan langsung pada formulir cuti (opsional).',
            'kode_syarat' => 'CUTI_ATSN',
        ]);

        $layanan = Layanan::create([
            'layanan' => 'Layanan Cuti Pegawai',
            'deskripsi' => 'Tes badge opsional cuti di detail layanan',
            'jenis' => 'kepegawaian',
        ]);
        $layanan->syarat()->sync([$syaratOptionalCuti->id]);

        $this->actingAs($manager)
            ->get(route('layanan.show', $layanan))
            ->assertOk()
            ->assertSee('Layanan Cuti Pegawai')
            ->assertSee('Persetujuan atasan langsung pada formulir cuti (opsional).')
            ->assertSee('Opsional untuk layanan cuti');
    }

    protected function createSuperAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }
}

