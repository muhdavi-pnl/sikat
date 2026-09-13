<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserManagementFeatureTest extends TestCase
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
    public function manager_can_create_user_and_link_to_pegawai()
    {
        $manager = $this->createManagerUser();
        $unitKerjaId = DB::table('unit_kerjas')->insertGetId([
            'unit_kerja' => 'BAUK',
        ]);

        $pegawai = Pegawai::create([
            'nip' => '198501012010011011',
            'nama' => 'Pegawai Link',
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->actingAs($manager)->post(route('kepegawaian.pengguna.store'), [
            'email' => 'user.baru@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pegawai',
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $unitKerjaId,
        ]);

        $response->assertRedirect(route('kepegawaian.pengguna'));

        $user = User::where('email', 'user.baru@example.test')->first();
        $this->assertNotNull($user);
        $this->assertSame($pegawai->nama, $user->name);

        if (Schema::hasTable((string) config('permission.table_names.roles', 'roles'))) {
            $this->assertTrue($user->hasRole('pegawai'));
        }
        $this->assertSame($unitKerjaId, $user->unit_kerja_id);

        $pegawai->refresh();
        $this->assertSame($user->id, $pegawai->user_id);
    }

    /** @test */
    public function super_admin_can_open_hak_akses_pages_with_localized_titles()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)
            ->get(route('role.index'))
            ->assertOk()
            ->assertSee('Peran')
            ->assertSee('Cari nama peran atau guard');

        $this->actingAs($superAdmin)
            ->get(route('permission.index'))
            ->assertOk()
            ->assertSee('Izin Akses')
            ->assertSee('Cari nama izin akses atau guard');
    }

    /** @test */
    public function kepegawaian_cannot_open_hak_akses_pages()
    {
        $manager = $this->createManagerUser();

        $this->actingAs($manager)
            ->get(route('role.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('permission.index'))
            ->assertForbidden();
    }

    /** @test */
    public function create_form_does_not_show_super_admin_role_and_store_rejects_it()
    {
        $manager = $this->createManagerUser();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011013',
            'nama' => 'Pegawai Role Tersembunyi',
            'status_pegawai' => 'PNS',
        ]);

        $this->actingAs($manager)
            ->get(route('kepegawaian.pengguna.create'))
            ->assertOk()
            ->assertDontSee('super-admin')
            ->assertSee('kepegawaian')
            ->assertSee('pegawai');

        $this->actingAs($manager)
            ->from(route('kepegawaian.pengguna.create'))
            ->post(route('kepegawaian.pengguna.store'), [
                'email' => 'superadmin.hidden@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'super-admin',
                'pegawai_id' => $pegawai->id,
            ])
            ->assertRedirect(route('kepegawaian.pengguna.create'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'superadmin.hidden@example.test',
        ]);
    }

    /** @test */
    public function manager_can_update_user_role_and_unit_kerja()
    {
        $manager = $this->createManagerUser();
        $unitKerjaOne = DB::table('unit_kerjas')->insertGetId([
            'unit_kerja' => 'BAUK',
        ]);
        $unitKerjaTwo = DB::table('unit_kerjas')->insertGetId([
            'unit_kerja' => 'BAAK',
        ]);

        $target = User::factory()->create([
            'unit_kerja_id' => $unitKerjaOne,
        ]);
        $target->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011099',
            'nama' => 'Nama Dari Pegawai',
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->actingAs($manager)->put(route('kepegawaian.pengguna.update', $target), [
            'email' => $target->email,
            'role' => 'kepegawaian',
            'unit_kerja_id' => $unitKerjaTwo,
            'pegawai_id' => $pegawai->id,
        ]);

        $response->assertRedirect(route('kepegawaian.pengguna'));

        $target->refresh();
        $this->assertSame('Nama Dari Pegawai', $target->name);
        $this->assertSame($unitKerjaTwo, $target->unit_kerja_id);

        $pegawai->refresh();
        $this->assertSame($target->id, $pegawai->user_id);

        if (Schema::hasTable((string) config('permission.table_names.roles', 'roles'))) {
            $this->assertTrue($target->hasRole('kepegawaian'));
        }
    }

    /** @test */
    public function super_admin_user_is_hidden_from_pengguna_list_and_direct_edit_page()
    {
        $manager = $this->createManagerUser();

        $hiddenUser = User::factory()->create([
            'name' => 'Super Admin Tersembunyi',
            'email' => 'superadmin@example.test',
        ]);
        $hiddenUser->assignRole('super-admin');

        $visibleUser = User::factory()->create([
            'name' => 'Pegawai Biasa',
            'email' => 'pegawai.biasa@example.test',
        ]);
        $visibleUser->assignRole('pegawai');

        $this->actingAs($manager)
            ->get(route('kepegawaian.pengguna.edit', $hiddenUser))
            ->assertNotFound();

        $response = $this->actingAs($manager)
            ->get(route('kepegawaian.pengguna'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk()
            ->assertSee('Pegawai Biasa')
            ->assertDontSee('Super Admin Tersembunyi')
            ->assertDontSee('superadmin@example.test');
    }

    /** @test */
    public function manager_can_fetch_paginated_pegawai_options()
    {
        $manager = $this->createManagerUser();

        for ($i = 1; $i <= 16; $i++) {
            Pegawai::create([
                'nip' => sprintf('1985010120100199%02d', $i),
                'nama' => 'Pegawai Opsi ' . $i,
                'status_pegawai' => 'PNS',
            ]);
        }

        $firstPage = $this->actingAs($manager)
            ->getJson(route('kepegawaian.pengguna.options.pegawais', ['q' => 'Pegawai Opsi', 'page' => 1]));

        $firstPage->assertOk()
            ->assertJsonPath('pagination.more', true);
        $this->assertCount(15, $firstPage->json('results'));

        $secondPage = $this->actingAs($manager)
            ->getJson(route('kepegawaian.pengguna.options.pegawais', ['q' => 'Pegawai Opsi', 'page' => 2]));

        $secondPage->assertOk()
            ->assertJsonPath('pagination.more', false);
        $this->assertCount(1, $secondPage->json('results'));

        $this->actingAs($manager)
            ->getJson(route('kepegawaian.pengguna.options.pegawais', ['q' => 'P', 'page' => 1]))
            ->assertOk()
            ->assertJson([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
    }

    /** @test */
    public function manager_can_reset_password_to_default()
    {
        $manager = $this->createManagerUser();

        $target = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);
        $target->assignRole('pegawai');

        $response = $this->actingAs($manager)->post(route('kepegawaian.pengguna.reset-password', $target->id));

        $response->assertRedirect();
        $target->refresh();
        $this->assertTrue(Hash::check('Sikat2019', $target->password));
        $this->assertTrue((bool) $target->must_change_password);
    }

    /** @test */
    public function manager_can_deactivate_and_restore_user()
    {
        $manager = $this->createManagerUser();

        $target = User::factory()->create();
        $target->assignRole('pegawai');

        $this->actingAs($manager)
            ->delete(route('kepegawaian.pengguna.deactivate', $target->id))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $target->id]);

        $this->actingAs($manager)
            ->post(route('kepegawaian.pengguna.restore', $target->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function manager_can_force_delete_user_and_unlink_pegawai()
    {
        $manager = $this->createManagerUser();

        $target = User::factory()->create();
        $target->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '198501012010011012',
            'nama' => 'Pegawai Unlink',
            'status_pegawai' => 'PNS',
            'user_id' => $target->id,
        ]);

        $this->actingAs($manager)->delete(route('kepegawaian.pengguna.deactivate', $target->id));

        $this->actingAs($manager)
            ->delete(route('kepegawaian.pengguna.force-delete', $target->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $target->id]);

        $pegawai->refresh();
        $this->assertNull($pegawai->user_id);
    }

    protected function createManagerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('kepegawaian');

        return $user;
    }
}

