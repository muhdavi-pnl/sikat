<?php

namespace Tests\Feature;

use App\Models\LayananPegawai;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefreshSmartTriageCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function command_recalculates_smart_triage_fields_for_non_cuti_layanan()
    {
        $pemohon = User::factory()->create();

        $pegawai = Pegawai::create([
            'nip' => '198501012010011701',
            'nama' => 'Pegawai Command Triage',
            'status_pegawai' => 'PNS',
            'user_id' => $pemohon->id,
        ]);

        $layananId = DB::table('layanans')->insertGetId([
            'layanan' => 'Mutasi Internal',
            'deskripsi' => 'Tes command triage',
            'jenis' => 'kepegawaian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usulan = LayananPegawai::create([
            'layanan_id' => $layananId,
            'pegawai_id' => $pegawai->id,
            'user_id' => $pemohon->id,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Mohon diproses.',
        ]);

        $this->assertSame(0, (int) $usulan->priority_score);
        $this->assertNull($usulan->sla_due_at);
        $this->assertTrue(in_array((string) $usulan->sla_risk, ['', 'none'], true));

        $this->artisan('intelligence:refresh-smart-triage')
            ->expectsOutput('Smart Triage refresh selesai untuk layanan non-cuti: 1/1 data.')
            ->assertSuccessful();

        $usulan->refresh();

        $this->assertSame(70, (int) $usulan->priority_score);
        $this->assertSame('medium', (string) $usulan->sla_risk);
        $this->assertNotNull($usulan->sla_due_at);
    }
}

