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
                'kategori_pegawai' => 'pns',
            ]);

        $dokumen = Dokumen::query()->firstOrFail();

        $response->assertRedirect(route('dokumen.show', ['dokumen' => $dokumen]));
        $this->assertSame('ARSIP01', $dokumen->kode_dokumen);
        $this->assertSame('Dokumen Arsip Utama', $dokumen->nama_dokumen);
        $this->assertSame('pns', $dokumen->kategori_pegawai);

        $this->actingAs($manager)
            ->get(route('dokumen.index'))
            ->assertOk()
            ->assertSee('Dokumen Arsip Utama')
            ->assertSee('Khusus PNS / CPNS')
            ->assertSee('title="Detail Dokumen"', false)
            ->assertSee('title="Edit Dokumen"', false)
            ->assertSee('title="Hapus Dokumen"', false);

        $this->actingAs($manager)
            ->put(route('dokumen.update', ['dokumen' => $dokumen]), [
                'kode_dokumen' => 'ARSIP02',
                'nama_dokumen' => 'Dokumen Arsip Revisi',
                'kategori_pegawai' => 'pppk',
            ])
            ->assertRedirect(route('dokumen.show', ['dokumen' => $dokumen]));

        $dokumen->refresh();
        $this->assertSame('ARSIP02', $dokumen->kode_dokumen);
        $this->assertSame('Dokumen Arsip Revisi', $dokumen->nama_dokumen);
        $this->assertSame('pppk', $dokumen->kategori_pegawai);

        $this->actingAs($manager)
            ->delete(route('dokumen.destroy', ['dokumen' => $dokumen]))
            ->assertRedirect(route('dokumen.index'));

        $this->assertDatabaseMissing('dokumens', ['id' => $dokumen->id]);
    }

    /** @test */
    public function manager_can_filter_dokumen_index_by_kategori_pegawai()
    {
        $manager = $this->createManagerUser();

        Dokumen::create([
            'kode_dokumen' => 'DOC_SEMUA',
            'nama_dokumen' => 'Dokumen Semua Pegawai',
            'kategori_pegawai' => 'semua',
        ]);
        Dokumen::create([
            'kode_dokumen' => 'DOC_PNS',
            'nama_dokumen' => 'Dokumen Khusus PNS',
            'kategori_pegawai' => 'pns',
        ]);
        Dokumen::create([
            'kode_dokumen' => 'DOC_PPPK',
            'nama_dokumen' => 'Dokumen Khusus PPPK',
            'kategori_pegawai' => 'pppk',
        ]);

        $this->actingAs($manager)
            ->get(route('dokumen.index', ['kategori' => 'pns']))
            ->assertOk()
            ->assertSee('Dokumen Khusus PNS')
            ->assertDontSee('Dokumen Khusus PPPK');

        $this->actingAs($manager)
            ->get(route('dokumen.index', ['kategori' => 'pppk']))
            ->assertOk()
            ->assertSee('Dokumen Khusus PPPK')
            ->assertDontSee('Dokumen Khusus PNS');
    }

    protected function createManagerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('kepegawaian');

        return $user;
    }
}

