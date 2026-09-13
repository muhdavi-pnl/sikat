<?php

namespace Tests\Unit;

use App\Models\CutiLayananPegawai;
use App\Models\LayananPegawai;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LayananPegawaiTest extends TestCase
{
    /** @test */
    public function dedicated_cuti_detail_takes_precedence_over_legacy_cuti_attributes()
    {
        $layananPegawai = new LayananPegawai([
            'status' => LayananPegawai::STATUS_USULAN,
        ]);

        $layananPegawai->forceFill([
            'cuti_hari_diminta' => 99,
            'cuti_hari_tersedia' => 77,
            'cuti_tanggal_mulai' => '2026-05-01',
            'cuti_tanggal_selesai' => '2026-05-30',
        ]);

        $layananPegawai->setRelation('cutiDetail', new CutiLayananPegawai([
            'hari_diminta' => 2,
            'hari_tersedia_saat_usul' => 24,
            'tanggal_mulai' => '2026-04-13',
            'tanggal_selesai' => '2026-04-14',
        ]));

        $this->assertTrue($layananPegawai->hasDedicatedCutiDetail());
        $this->assertSame(2, $layananPegawai->cuti_hari_diminta);
        $this->assertSame(24, $layananPegawai->cuti_hari_tersedia);
        $this->assertSame('2026-04-13', optional($layananPegawai->cuti_tanggal_mulai)->format('Y-m-d'));
        $this->assertSame('2026-04-14', optional($layananPegawai->cuti_tanggal_selesai)->format('Y-m-d'));
    }

    /** @test */
    public function legacy_cuti_attributes_still_work_as_fallback_when_no_dedicated_detail_exists()
    {
        $layananPegawai = new LayananPegawai([
            'status' => LayananPegawai::STATUS_USULAN,
        ]);

        $layananPegawai->forceFill([
            'cuti_hari_diminta' => 3,
            'cuti_hari_tersedia' => 21,
            'cuti_tanggal_mulai' => '2026-04-13',
            'cuti_tanggal_selesai' => '2026-04-15',
        ]);

        $this->assertFalse($layananPegawai->hasDedicatedCutiDetail());
        $this->assertNull($layananPegawai->resolvedCutiDetail());
        $this->assertSame(3, $layananPegawai->cuti_hari_diminta);
        $this->assertSame(21, $layananPegawai->cuti_hari_tersedia);
        $this->assertInstanceOf(Carbon::class, $layananPegawai->cuti_tanggal_mulai);
        $this->assertSame('2026-04-13', $layananPegawai->cuti_tanggal_mulai->format('Y-m-d'));
        $this->assertSame('2026-04-15', $layananPegawai->cuti_tanggal_selesai->format('Y-m-d'));
    }

    /** @test */
    public function legacy_cuti_attributes_are_no_longer_mass_assignable_for_new_runtime_writes()
    {
        $layananPegawai = new LayananPegawai([
            'layanan_id' => 10,
            'pegawai_id' => 20,
            'user_id' => 30,
            'status' => LayananPegawai::STATUS_USULAN,
            'catatan_pengusul' => 'Tes mass assignment',
            'cuti_hari_diminta' => 5,
            'cuti_hari_tersedia' => 24,
            'cuti_tanggal_mulai' => '2026-04-13',
            'cuti_tanggal_selesai' => '2026-04-17',
        ]);

        $this->assertSame(10, $layananPegawai->layanan_id);
        $this->assertSame(20, $layananPegawai->pegawai_id);
        $this->assertSame(30, $layananPegawai->user_id);
        $this->assertSame(LayananPegawai::STATUS_USULAN, $layananPegawai->status);
        $this->assertSame('Tes mass assignment', $layananPegawai->catatan_pengusul);

        $this->assertArrayNotHasKey('cuti_hari_diminta', $layananPegawai->getAttributes());
        $this->assertArrayNotHasKey('cuti_hari_tersedia', $layananPegawai->getAttributes());
        $this->assertArrayNotHasKey('cuti_tanggal_mulai', $layananPegawai->getAttributes());
        $this->assertArrayNotHasKey('cuti_tanggal_selesai', $layananPegawai->getAttributes());
    }
}

