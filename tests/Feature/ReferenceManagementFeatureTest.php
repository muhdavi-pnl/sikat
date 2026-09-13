<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReferenceManagementFeatureTest extends TestCase
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
    public function kepegawaian_cannot_access_reference_master_modules()
    {
        $manager = User::factory()->create();
        $manager->assignRole('kepegawaian');

        $this->actingAs($manager)
            ->get(route('unit-kerja.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('program-studi.index'))
            ->assertForbidden();
    }

    /** @test */
    public function super_admin_can_access_reference_master_modules_and_see_navigation()
    {
        $superAdmin = User::factory()->create([
            'must_change_password' => false,
        ]);
        $superAdmin->assignRole('super-admin');

        // Verify Master Data menu is visible with consolidated menu items
        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Master Data')
            // All models are now under Master Data dropdown
            ->assertSee('Layanan')
            ->assertSee('Unit Kerja')
            ->assertSee('Program Studi');

        $this->actingAs($superAdmin)
            ->get(route('unit-kerja.index'))
            ->assertOk()
            ->assertSee('Daftar Unit Kerja');

        $this->actingAs($superAdmin)
            ->get(route('program-studi.index'))
            ->assertOk()
            ->assertSee('Daftar Program Studi');
    }
}

