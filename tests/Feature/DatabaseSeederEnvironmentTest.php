<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Pegawai;
use App\Models\User;
use Database\Seeders\DeploymentSeeder;
use Database\Seeders\DevelopmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseSeederEnvironmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function deployment_seeder_only_seeds_master_data_and_admin()
    {
        $this->seed(DeploymentSeeder::class);

        // Roles exist
        $this->assertTrue(Role::where('name', 'super-admin')->exists());
        $this->assertTrue(Role::where('name', 'kepegawaian')->exists());
        $this->assertTrue(Role::where('name', 'pegawai')->exists());

        // Master data exists
        $this->assertTrue(Layanan::exists());

        // Only initial super-admin exists, no demo dummy users/pegawais
        $adminEmail = env('ADMIN_EMAIL', 'sikat@muhdavi.com');
        $this->assertTrue(User::where('email', $adminEmail)->exists());
        $this->assertSame(1, User::count());
        $this->assertSame(0, Pegawai::count());
    }

    /** @test */
    public function development_seeder_seeds_master_data_and_demo_records()
    {
        $this->seed(DevelopmentSeeder::class);

        // Roles & Master data exist
        $this->assertTrue(Role::where('name', 'super-admin')->exists());
        $this->assertTrue(Layanan::exists());

        // Demo users & pegawais exist
        $this->assertGreaterThan(1, User::count());
        $this->assertGreaterThan(0, Pegawai::count());

        // Specific demo users exist
        $this->assertTrue(User::where('email', 'fakhruddin@pnl.ac.id')->exists());
        $this->assertTrue(User::where('email', 'salahuddintik@pnl.ac.id')->exists());
    }

    /** @test */
    public function database_seeder_executes_deployment_seeder_in_production_environment()
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--force' => true])
            ->assertSuccessful();

        $this->assertSame(1, User::count());
        $this->assertSame(0, Pegawai::count());
        $this->assertTrue(Layanan::exists());
    }
}
