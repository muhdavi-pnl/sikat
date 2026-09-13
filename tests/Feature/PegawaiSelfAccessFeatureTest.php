<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PegawaiSelfAccessFeatureTest extends TestCase
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
    public function all_main_roles_can_access_their_own_pegawai_pages_when_linked()
    {
        $roles = ['super-admin', 'kepegawaian', 'pegawai'];

        foreach ($roles as $index => $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            Pegawai::create([
                'nip' => '19850101201001150' . $index,
                'nama' => 'Pegawai ' . $role,
                'status_pegawai' => 'PNS',
                'user_id' => $user->id,
            ]);

            $this->actingAs($user)->get(route('pegawai.profile'))->assertOk();
            $this->actingAs($user)->get(route('pegawai.dokumen'))->assertOk();
            $this->actingAs($user)->get(route('pegawai.layanan'))->assertOk();
        }
    }

    /** @test */
    public function user_without_linked_pegawai_can_still_open_layanan_page()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get(route('pegawai.layanan'))
            ->assertOk()
            ->assertSee('Data pegawai Anda belum tersedia');
    }

    /** @test */
    public function sidebar_navigation_matches_user_role_visibility()
    {
        $pegawaiUser = User::factory()->create([
            'must_change_password' => false,
        ]);
        $pegawaiUser->assignRole('pegawai');

        Pegawai::create([
            'nip' => '198501012010011600',
            'nama' => 'Pegawai Sidebar',
            'status_pegawai' => 'PNS',
            'user_id' => $pegawaiUser->id,
        ]);

        $this->actingAs($pegawaiUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Profil')
            ->assertSee('Dokumen')
            ->assertSee('Layanan')
            ->assertDontSee('Pengguna')
            ->assertDontSee('Proses Layanan')
            ->assertDontSee('Proses Cuti')
            ->assertDontSee('Lokasi Arsip')
            ->assertDontSee('Master Data')
            ->assertDontSee('Hak Akses');

        $kepegawaianUser = User::factory()->create([
            'must_change_password' => false,
        ]);
        $kepegawaianUser->assignRole('kepegawaian');

        Pegawai::create([
            'nip' => '198501012010011601',
            'nama' => 'Kepegawaian Sidebar',
            'status_pegawai' => 'PNS',
            'user_id' => $kepegawaianUser->id,
        ]);

        $this->actingAs($kepegawaianUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kepegawaian')
            ->assertSee('Pegawai')
            ->assertSee('Pengguna')
            ->assertSee('Arsip')
            ->assertSee('Dokumen')
            ->assertDontSee('Master Data')
            ->assertDontSee('Hak Akses')
            ->assertDontSee('Peran')
            ->assertDontSee('Izin Akses');
    }
}

