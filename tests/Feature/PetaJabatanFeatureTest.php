<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PetaJabatanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'kepegawaian']);
        Role::create(['name' => 'pimpinan']);
        Role::create(['name' => 'pegawai']);
    }

    private function makeJabatan(array $overrides = []): Jabatan
    {
        $jabatan = new Jabatan();
        $jabatan->timestamps = false;
        $jabatan->forceFill([
            'jabatan' => $overrides['jabatan'] ?? 'Dosen',
            'kode_jabatan' => $overrides['kode_jabatan'] ?? 'DOSEN',
        ])->save();

        $jabatan->peta_jabatan()->create([
            'kebutuhan_pegawai' => $overrides['kebutuhan_pegawai'] ?? 5,
            'unit_kerja_id' => $overrides['unit_kerja_id'] ?? null,
            'atasan_langsung_id' => $overrides['atasan_langsung_id'] ?? null,
        ]);

        return $jabatan;
    }

    /** @test */
    public function super_admin_can_view_peta_jabatan_and_manage_jabatan()
    {
        $this->makeJabatan();

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get(route('peta-jabatan.index'))
            ->assertOk()
            ->assertSee('Peta Jabatan')
            ->assertSee('Kelola Jabatan');

        $this->actingAs($user)
            ->get(route('peta-jabatan.manage.index', ['slug' => 'jabatan']))
            ->assertOk();
    }

    /** @test */
    public function kepegawaian_can_view_peta_jabatan_and_manage_jabatan()
    {
        $this->makeJabatan();

        $user = User::factory()->create();
        $user->assignRole('kepegawaian');

        $this->actingAs($user)
            ->get(route('peta-jabatan.index'))
            ->assertOk()
            ->assertSee('Kelola Jabatan');

        $this->actingAs($user)
            ->get(route('peta-jabatan.manage.index', ['slug' => 'jabatan']))
            ->assertOk();
    }

    /** @test */
    public function peta_jabatan_displays_full_employee_name_with_academic_titles()
    {
        $jabatan = $this->makeJabatan([
            'jabatan' => 'Ketua Jurusan TIK',
            'kode_jabatan' => 'KAJUR-TIK',
        ]);

        \App\Models\Pegawai::create([
            'nama' => 'Budi Santoso',
            'gelar_depan' => 'Dr. Ir.',
            'gelar_belakang' => 'M.Kom., IPM.',
            'nip' => '198501012010121001',
            'jabatan_id' => $jabatan->id,
        ]);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)
            ->get(route('peta-jabatan.index'));

        $response->assertOk();
        $response->assertSee('Dr. Ir. Budi Santoso, M.Kom., IPM.');
    }

    /** @test */
    public function pimpinan_can_view_peta_jabatan_read_only()
    {
        $this->makeJabatan();

        $user = User::factory()->create();
        $user->assignRole('pimpinan');

        $this->actingAs($user)
            ->get(route('peta-jabatan.index'))
            ->assertOk()
            ->assertSee('Peta Jabatan')
            ->assertDontSee('Kelola Jabatan');

        // Pimpinan cannot manage the underlying jabatan master data.
        $this->actingAs($user)
            ->get(route('peta-jabatan.manage.index', ['slug' => 'jabatan']))
            ->assertForbidden();
    }

    /** @test */
    public function pegawai_cannot_access_peta_jabatan_module()
    {
        $this->makeJabatan();

        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $this->actingAs($user)
            ->get(route('peta-jabatan.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('peta-jabatan.dashboard'))
            ->assertForbidden();
    }

    /** @test */
    public function guest_is_redirected_to_login()
    {
        $this->get(route('peta-jabatan.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function web_dashboard_endpoint_returns_expected_payload_for_authorized_roles()
    {
        $jabatan = $this->makeJabatan(['kebutuhan_pegawai' => 3]);

        foreach (['super-admin', 'kepegawaian', 'pimpinan'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)
                ->getJson(route('peta-jabatan.dashboard'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        'total_jabatan',
                        'jabatan_terisi',
                        'jabatan_kosong',
                        'kekurangan_pegawai',
                        'kelebihan_pegawai',
                        'distribusi_pegawai_per_jabatan',
                        'distribusi_dosen_per_jabatan_akademik',
                        'distribusi_per_unit_kerja',
                        'distribusi_per_pendidikan',
                        'jabatan_membutuhkan_pengisian',
                    ],
                ])
                ->assertJsonPath('data.total_jabatan', 1)
                ->assertJsonPath('data.jabatan_membutuhkan_pengisian.0.jabatan', $jabatan->jabatan);
        }
    }

    /** @test */
    public function api_dashboard_endpoint_works_for_authorized_roles_and_blocks_pegawai()
    {
        $this->makeJabatan();

        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        Sanctum::actingAs($kepegawaian);

        $this->getJson('/api/peta-jabatan/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => ['total_jabatan']]);

        $pegawai = User::factory()->create();
        $pegawai->assignRole('pegawai');

        Sanctum::actingAs($pegawai);

        $this->getJson('/api/peta-jabatan/dashboard')->assertForbidden();
    }
}
