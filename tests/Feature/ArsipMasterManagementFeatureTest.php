<?php

namespace Tests\Feature;

use App\Models\Gedung;
use App\Models\Lemari;
use App\Models\LokasiArsip;
use App\Models\Pegawai;
use App\Models\Rak;
use App\Models\Ruang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ArsipMasterManagementFeatureTest extends TestCase
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
    public function pegawai_cannot_manage_arsip_master_modules()
    {
        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $this->actingAs($pegawaiUser)
            ->get(route('gedung.index'))
            ->assertForbidden();

        $this->actingAs($pegawaiUser)
            ->get(route('lokasi-arsip.index'))
            ->assertForbidden();
    }

    /** @test */
    public function kepegawaian_can_access_arsip_master_modules_and_see_navigation()
    {
        $manager = User::factory()->create();
        $manager->assignRole('kepegawaian');

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Layanan Kepegawaian', 'Kepegawaian', 'Pegawai'])
            ->assertSeeInOrder(['Proses Cuti', 'Arsip', 'Buku Panduan'])
            ->assertSee('Arsip')
            ->assertSee('Gedung')
            ->assertSee('Lokasi Arsip')
            ->assertSee('Dokumen')
            ->assertDontSee('Data Pegawai')
            ->assertDontSee('Data Pengguna')
            ->assertDontSee('Data Arsip')
            ->assertDontSee('Data Referensi')
            ->assertDontSee('Master Data')
            ->assertDontSee('Hak Akses')
            ->assertDontSee('Nama Dokumen');

        $this->actingAs($manager)
            ->get(route('gedung.index'))
            ->assertOk()
            ->assertSee('Daftar Gedung');

        $this->actingAs($manager)
            ->get(route('gedung.create'))
            ->assertOk()
            ->assertSee('Tambah Gedung');

        $this->actingAs($manager)
            ->get(route('lokasi-arsip.index'))
            ->assertOk()
            ->assertSee('Daftar Lokasi Arsip');
    }

    /** @test */
    public function super_admin_can_crud_arsip_master_hierarchy()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)
            ->get(route('gedung.create'))
            ->assertOk()
            ->assertSee('Tambah Gedung');

        $this->actingAs($superAdmin)
            ->post(route('gedung.store'), [
                'nama_gedung' => 'Gedung Pusat Arsip',
                'alamat_gedung' => 'Jl. Kampus PNL',
                'keterangan' => 'Gedung utama arsip',
            ])
            ->assertRedirect();

        $gedung = Gedung::query()->firstOrFail();
        $this->assertSame('Gedung Pusat Arsip', $gedung->nama_gedung);

        $this->actingAs($superAdmin)
            ->post(route('ruang.store'), [
                'gedung_id' => $gedung->id,
                'kode_ruang' => 'ARS.101',
                'nama_ruang' => 'Ruang Arsip Utama',
                'keterangan' => 'Lantai 1',
            ])
            ->assertRedirect();

        $ruang = Ruang::query()->firstOrFail();
        $this->assertSame($gedung->id, $ruang->gedung_id);

        $this->actingAs($superAdmin)
            ->post(route('lemari.store'), [
                'ruang_id' => $ruang->id,
                'lemari' => 'Lemari A',
                'keterangan' => 'Baris depan',
            ])
            ->assertRedirect();

        $lemari = Lemari::query()->firstOrFail();
        $this->assertSame($ruang->id, $lemari->ruang_id);

        $this->actingAs($superAdmin)
            ->post(route('rak.store'), [
                'lemari_id' => $lemari->id,
                'rak' => 'Rak 01',
                'keterangan' => 'Tingkat atas',
            ])
            ->assertRedirect();

        $rak = Rak::query()->firstOrFail();
        $this->assertSame($lemari->id, $rak->lemari_id);

        $pegawai = Pegawai::create([
            'nip' => '198501012010011111',
            'nama' => 'Pegawai Arsip',
            'status_pegawai' => 'PNS',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('lokasi-arsip.store'), [
                'pegawai_id' => $pegawai->id,
                'rak_id' => $rak->id,
                'keterangan' => 'Map biru',
            ])
            ->assertRedirect();

        $lokasiArsip = LokasiArsip::query()->firstOrFail();
        $this->assertSame($pegawai->id, $lokasiArsip->pegawai_id);
        $this->assertSame($rak->id, $lokasiArsip->rak_id);

        $this->actingAs($superAdmin)
            ->get(route('lokasi-arsip.show', $lokasiArsip))
            ->assertOk()
            ->assertSee('Pegawai Arsip')
            ->assertSee('Rak 01')
            ->assertSee('Lemari A')
            ->assertSee('ARS.101')
            ->assertSee('Gedung Pusat Arsip');

        $this->actingAs($superAdmin)
            ->put(route('gedung.update', $gedung), [
                'nama_gedung' => 'Gedung Pusat Arsip Baru',
                'alamat_gedung' => 'Jl. Kampus PNL Barat',
                'keterangan' => 'Gedung utama baru',
            ])
            ->assertRedirect(route('gedung.show', $gedung));

        $this->actingAs($superAdmin)
            ->put(route('ruang.update', $ruang), [
                'gedung_id' => $gedung->id,
                'kode_ruang' => 'ARS.102',
                'nama_ruang' => 'Ruang Arsip Aktif',
                'keterangan' => 'Lantai 2',
            ])
            ->assertRedirect(route('ruang.show', $ruang));

        $this->actingAs($superAdmin)
            ->put(route('lemari.update', $lemari), [
                'ruang_id' => $ruang->id,
                'lemari' => 'Lemari B',
                'keterangan' => 'Baris tengah',
            ])
            ->assertRedirect(route('lemari.show', $lemari));

        $this->actingAs($superAdmin)
            ->put(route('rak.update', $rak), [
                'lemari_id' => $lemari->id,
                'rak' => 'Rak 02',
                'keterangan' => 'Tingkat tengah',
            ])
            ->assertRedirect(route('rak.show', $rak));

        $this->actingAs($superAdmin)
            ->put(route('lokasi-arsip.update', $lokasiArsip), [
                'pegawai_id' => $pegawai->id,
                'rak_id' => $rak->id,
                'keterangan' => 'Map merah',
            ])
            ->assertRedirect(route('lokasi-arsip.show', $lokasiArsip));

        $gedung->refresh();
        $ruang->refresh();
        $lemari->refresh();
        $rak->refresh();
        $lokasiArsip->refresh();

        $this->assertSame('Gedung Pusat Arsip Baru', $gedung->nama_gedung);
        $this->assertSame('ARS.102', $ruang->kode_ruang);
        $this->assertSame('Lemari B', $lemari->lemari);
        $this->assertSame('Rak 02', $rak->rak);
        $this->assertSame('Map merah', $lokasiArsip->keterangan);

        $this->actingAs($superAdmin)
            ->delete(route('lokasi-arsip.destroy', $lokasiArsip))
            ->assertRedirect(route('lokasi-arsip.index'));

        $this->actingAs($superAdmin)
            ->delete(route('rak.destroy', $rak))
            ->assertRedirect(route('rak.index'));

        $this->actingAs($superAdmin)
            ->delete(route('lemari.destroy', $lemari))
            ->assertRedirect(route('lemari.index'));

        $this->actingAs($superAdmin)
            ->delete(route('ruang.destroy', $ruang))
            ->assertRedirect(route('ruang.index'));

        $this->actingAs($superAdmin)
            ->delete(route('gedung.destroy', $gedung))
            ->assertRedirect(route('gedung.index'));

        $this->assertDatabaseMissing('lokasi_arsips', ['id' => $lokasiArsip->id]);
        $this->assertDatabaseMissing('raks', ['id' => $rak->id]);
        $this->assertDatabaseMissing('lemaris', ['id' => $lemari->id]);
        $this->assertDatabaseMissing('ruangs', ['id' => $ruang->id]);
        $this->assertDatabaseMissing('gedungs', ['id' => $gedung->id]);
    }
}

