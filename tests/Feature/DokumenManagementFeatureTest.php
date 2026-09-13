<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DokumenManagementFeatureTest extends TestCase
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
    public function non_privileged_user_cannot_manage_dokumen_master()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $this->actingAs($user)
            ->get(route('dokumen.index'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_create_update_view_and_delete_dokumen_master()
    {
        $manager = $this->createManagerUser();

        $this->actingAs($manager)
            ->get(route('dokumen.create'))
            ->assertOk()
            ->assertSee('Tambah Dokumen');

        $response = $this->actingAs($manager)
            ->post(route('dokumen.store'), [
                'kode_dokumen' => 'ARSIP01',
                'nama_dokumen' => 'Dokumen Arsip Utama',
            ]);

        $dokumen = Dokumen::query()->firstOrFail();

        $response->assertRedirect(route('dokumen.show', ['dokumen' => $dokumen]));
        $this->assertSame('ARSIP01', $dokumen->kode_dokumen);
        $this->assertSame('Dokumen Arsip Utama', $dokumen->nama_dokumen);

        $this->actingAs($manager)
            ->get(route('dokumen.index'))
            ->assertOk()
            ->assertSee('Dokumen Arsip Utama')
            ->assertSee('title="Detail Dokumen"', false)
            ->assertSee('title="Edit Dokumen"', false)
            ->assertSee('title="Hapus Dokumen"', false);

        $this->actingAs($manager)
            ->put(route('dokumen.update', ['dokumen' => $dokumen]), [
                'kode_dokumen' => 'ARSIP02',
                'nama_dokumen' => 'Dokumen Arsip Revisi',
            ])
            ->assertRedirect(route('dokumen.show', ['dokumen' => $dokumen]));

        $dokumen->refresh();
        $this->assertSame('ARSIP02', $dokumen->kode_dokumen);
        $this->assertSame('Dokumen Arsip Revisi', $dokumen->nama_dokumen);

        $this->actingAs($manager)
            ->delete(route('dokumen.destroy', ['dokumen' => $dokumen]))
            ->assertRedirect(route('dokumen.index'));

        $this->assertDatabaseMissing('dokumens', ['id' => $dokumen->id]);
    }

    protected function createManagerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('kepegawaian');

        return $user;
    }
}

