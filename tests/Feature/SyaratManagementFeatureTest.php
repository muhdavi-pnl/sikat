<?php

namespace Tests\Feature;

use App\Models\Syarat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SyaratManagementFeatureTest extends TestCase
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
    public function non_privileged_user_cannot_manage_syarat()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $this->actingAs($user)
            ->get(route('syarat.index'))
            ->assertForbidden();
    }

    /** @test */
    public function kepegawaian_cannot_manage_syarat()
    {
        $manager = User::factory()->create();
        $manager->assignRole('kepegawaian');

        $this->actingAs($manager)
            ->get(route('syarat.index'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_create_document_mapped_syarat()
    {
        $manager = $this->createSuperAdminUser();
        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'SKP',
            'nama_dokumen' => 'SK Pangkat',
        ]);

        $response = $this->actingAs($manager)
            ->post(route('syarat.store'), [
                'syarat' => 'Unggah SK Pangkat terakhir',
                'mapping_source' => 'document',
                'dokumen_id' => $dokumenId,
            ]);

        $syarat = Syarat::query()->first();

        $response->assertRedirect(route('syarat.show', $syarat));
        $this->assertNotNull($syarat);
        $this->assertSame('Unggah SK Pangkat terakhir', $syarat->syarat);
        $this->assertSame($dokumenId, $syarat->dokumen_id);
        $this->assertNull($syarat->kode_syarat);
    }

    /** @test */
    public function manager_can_create_profile_mapped_syarat_and_view_its_detail()
    {
        $manager = $this->createSuperAdminUser();

        $response = $this->actingAs($manager)
            ->post(route('syarat.store'), [
                'syarat' => 'Pastikan ID Google Scholar sudah diisi',
                'mapping_source' => 'profile',
                'kode_syarat' => 'ID_GSCHOLAR',
            ]);

        $syarat = Syarat::query()->first();

        $response->assertRedirect(route('syarat.show', $syarat));
        $this->assertSame('ID_GSCHOLAR', $syarat->kode_syarat);
        $this->assertNull($syarat->dokumen_id);

        $this->actingAs($manager)
            ->get(route('syarat.show', $syarat))
            ->assertOk()
            ->assertSee('Data Profil Pegawai')
            ->assertSee('ID Google Scholar')
            ->assertSee('ID_GSCHOLAR');
    }

    /** @test */
    public function manager_can_update_syarat_mapping_to_manual_and_soft_delete_it()
    {
        $manager = $this->createSuperAdminUser();
        $dokumenId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'SKCPNS',
            'nama_dokumen' => 'SK CPNS',
        ]);

        $syarat = Syarat::create([
            'syarat' => 'Unggah SK CPNS',
            'dokumen_id' => $dokumenId,
        ]);

        $this->actingAs($manager)
            ->put(route('syarat.update', $syarat), [
                'syarat' => 'Verifikasi surat pengantar manual',
                'mapping_source' => 'manual',
            ])
            ->assertRedirect(route('syarat.show', $syarat));

        $syarat->refresh();
        $this->assertSame('Verifikasi surat pengantar manual', $syarat->syarat);
        $this->assertNull($syarat->dokumen_id);
        $this->assertNull($syarat->kode_syarat);

        $this->actingAs($manager)
            ->delete(route('syarat.destroy', $syarat))
            ->assertRedirect(route('syarat.index'));

        $this->assertSoftDeleted('syarats', ['id' => $syarat->id]);
    }

    protected function createSuperAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }
}

