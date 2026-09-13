<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SmartTriageApiFeatureTest extends TestCase
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
    public function kepegawaian_can_get_sorted_smart_triage_json_from_api()
    {
        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        $pemohonLama = User::factory()->create();
        $pemohonLama->assignRole('pegawai');
        $pegawaiLama = Pegawai::create([
            'nip' => '198501012010011601',
            'nama' => 'Pegawai API Lama',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohonLama->id,
        ]);

        $pemohonBaru = User::factory()->create();
        $pemohonBaru->assignRole('pegawai');
        $pegawaiBaru = Pegawai::create([
            'nip' => '198501012010011602',
            'nama' => 'Pegawai API Baru',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohonBaru->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Tes endpoint triage',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('layanan_pegawais')->insert([
            [
                'layanan_id' => $layananId,
                'pegawai_id' => $pegawaiLama->id,
                'user_id' => $pemohonLama->id,
                'status' => 'usulan',
                'catatan_pengusul' => 'Mohon segera, mendesak.',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'layanan_id' => $layananId,
                'pegawai_id' => $pegawaiBaru->id,
                'user_id' => $pemohonBaru->id,
                'status' => 'usulan',
                'catatan_pengusul' => 'Mohon diproses biasa.',
                'created_at' => Carbon::now()->subHours(1),
                'updated_at' => Carbon::now()->subHours(1),
            ],
        ]);

        Sanctum::actingAs($kepegawaian);

        $response = $this->getJson('/api/intelligence/layanan-triage?refresh=1');

        $response
            ->assertOk()
            ->assertJsonPath('meta.refreshed_count', 2)
            ->assertJsonPath('data.0.pegawai.nama', 'Pegawai API Lama')
            ->assertJsonPath('data.1.pegawai.nama', 'Pegawai API Baru')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'status',
                        'priority_score',
                        'sla_due_at',
                        'sla_risk',
                        'pegawai' => ['id', 'nama', 'nip'],
                        'layanan' => ['id', 'nama'],
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'refreshed_count', 'filters'],
            ]);
    }
}

