<?php

namespace Tests\Feature;

use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PengumumanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'kepegawaian']);
        Role::firstOrCreate(['name' => 'pegawai']);
    }

    /** @test */
    public function non_privileged_user_cannot_access_pengumuman_management()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $this->actingAs($user)
            ->get(route('pengumuman.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('pengumuman.create'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_create_text_pengumuman()
    {
        $manager = $this->createManagerUser();

        $this->actingAs($manager)
            ->get(route('pengumuman.create'))
            ->assertOk()
            ->assertSee('Tambah Pengumuman');

        $response = $this->actingAs($manager)
            ->post(route('pengumuman.store'), [
                'judul' => 'Pengumuman Libur Nasional',
                'tipe' => 'teks',
                'isi' => 'Diberitahukan bahwa kampus libur pada hari Senin.',
                'target_role' => 'semua',
                'is_aktif' => '1',
            ]);

        $pengumuman = Pengumuman::first();

        $this->assertNotNull($pengumuman);
        $this->assertSame('Pengumuman Libur Nasional', $pengumuman->judul);
        $this->assertSame('teks', $pengumuman->tipe);
        $this->assertSame('Diberitahukan bahwa kampus libur pada hari Senin.', $pengumuman->isi);
        $this->assertTrue($pengumuman->is_aktif);
        $this->assertSame($manager->id, $pengumuman->created_by);

        $response->assertRedirect(route('pengumuman.show', ['pengumuman' => $pengumuman]));
    }

    /** @test */
    public function manager_can_create_image_pengumuman()
    {
        $manager = $this->createManagerUser();
        $file = UploadedFile::fake()->image('banner.jpg', 800, 600);

        $response = $this->actingAs($manager)
            ->post(route('pengumuman.store'), [
                'judul' => 'Banner Sosialisasi SIKAT',
                'tipe' => 'gambar',
                'gambar' => $file,
                'target_role' => 'semua',
                'is_aktif' => '1',
            ]);

        $pengumuman = Pengumuman::first();

        $this->assertNotNull($pengumuman);
        $this->assertSame('Banner Sosialisasi SIKAT', $pengumuman->judul);
        $this->assertSame('gambar', $pengumuman->tipe);
        $this->assertNotNull($pengumuman->gambar);
        $this->assertFileExists(public_path($pengumuman->gambar));

        $response->assertRedirect(route('pengumuman.show', ['pengumuman' => $pengumuman]));

        // Cleanup
        if (File::exists(public_path($pengumuman->gambar))) {
            File::delete(public_path($pengumuman->gambar));
        }
    }

    /** @test */
    public function manager_can_update_and_delete_pengumuman()
    {
        $manager = $this->createManagerUser();

        $pengumuman = Pengumuman::create([
            'judul' => 'Judul Awal',
            'tipe' => 'teks',
            'isi' => 'Konten awal',
            'is_aktif' => true,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->put(route('pengumuman.update', ['pengumuman' => $pengumuman]), [
                'judul' => 'Judul Diperbarui',
                'tipe' => 'teks',
                'isi' => 'Konten setelah diedit',
                'is_aktif' => '1',
            ])
            ->assertRedirect(route('pengumuman.show', ['pengumuman' => $pengumuman]));

        $pengumuman->refresh();
        $this->assertSame('Judul Diperbarui', $pengumuman->judul);
        $this->assertSame('Konten setelah diedit', $pengumuman->isi);

        // Delete
        $this->actingAs($manager)
            ->delete(route('pengumuman.destroy', ['pengumuman' => $pengumuman]))
            ->assertRedirect(route('pengumuman.index'));

        $this->assertDatabaseMissing('pengumumans', ['id' => $pengumuman->id]);
    }

    /** @test */
    public function manager_can_toggle_pengumuman_status()
    {
        $manager = $this->createManagerUser();

        $pengumuman = Pengumuman::create([
            'judul' => 'Pengumuman Uji Coba',
            'tipe' => 'teks',
            'isi' => 'Testing toggle',
            'is_aktif' => true,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->post(route('pengumuman.toggle-status', ['pengumuman' => $pengumuman]))
            ->assertRedirect();

        $pengumuman->refresh();
        $this->assertFalse($pengumuman->is_aktif);

        $this->actingAs($manager)
            ->post(route('pengumuman.toggle-status', ['pengumuman' => $pengumuman]))
            ->assertRedirect();

        $pengumuman->refresh();
        $this->assertTrue($pengumuman->is_aktif);
    }

    /** @test */
    public function popup_modal_is_rendered_for_active_announcements_on_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $pengumuman = Pengumuman::create([
            'judul' => 'Pemberitahuan Sistem Baru',
            'tipe' => 'teks',
            'isi' => 'Selamat datang di pembaruan sistem SIKAT.',
            'is_aktif' => true,
            'target_role' => 'semua',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="pengumumanPopupModal"', false)
            ->assertSee('Pemberitahuan Sistem Baru')
            ->assertSee('Selamat datang di pembaruan sistem SIKAT.');
    }

    /** @test */
    public function user_can_dismiss_popup_for_session()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $response = $this->actingAs($user)
            ->postJson(route('pengumuman.dismiss-popup'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertTrue(session('pengumuman_popup_dismissed'));
    }

    /** @test */
    public function manager_can_create_combination_text_and_image_pengumuman()
    {
        $manager = $this->createManagerUser();
        $file = UploadedFile::fake()->image('combo.png', 600, 400);

        $response = $this->actingAs($manager)
            ->post(route('pengumuman.store'), [
                'judul' => 'Pengumuman Penting dengan Poster',
                'tipe' => 'keduanya',
                'isi' => 'Rincian instruksi pengumuman penting.',
                'gambar' => $file,
                'target_role' => 'pegawai',
                'is_aktif' => '1',
            ]);

        $pengumuman = Pengumuman::first();

        $this->assertNotNull($pengumuman);
        $this->assertSame('keduanya', $pengumuman->tipe);
        $this->assertSame('Rincian instruksi pengumuman penting.', $pengumuman->isi);
        $this->assertNotNull($pengumuman->gambar);
        $this->assertFileExists(public_path($pengumuman->gambar));

        // Cleanup
        if (File::exists(public_path($pengumuman->gambar))) {
            File::delete(public_path($pengumuman->gambar));
        }
    }

    /** @test */
    public function expired_announcement_is_not_displayed_in_popup()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        Pengumuman::create([
            'judul' => 'Pengumuman Kadaluarsa',
            'tipe' => 'teks',
            'isi' => 'Ini sudah lewat.',
            'is_aktif' => true,
            'tanggal_mulai' => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(2)->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pengumuman Kadaluarsa');
    }

    /** @test */
    public function role_targeted_announcement_only_shows_for_matching_role()
    {
        $pegawai = User::factory()->create();
        $pegawai->assignRole('pegawai');

        $manager = $this->createManagerUser(); // has role kepegawaian

        Pengumuman::create([
            'judul' => 'Pengumuman Khusus Pegawai',
            'tipe' => 'teks',
            'isi' => 'Hanya untuk pegawai.',
            'is_aktif' => true,
            'target_role' => 'pegawai',
        ]);

        $this->actingAs($pegawai)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengumuman Khusus Pegawai');

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pengumuman Khusus Pegawai');
    }

    protected function createManagerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('kepegawaian');

        return $user;
    }
}
