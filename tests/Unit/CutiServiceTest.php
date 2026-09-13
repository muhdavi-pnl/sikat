<?php

namespace Tests\Unit;

use App\Services\CutiService;
use Tests\TestCase;

class CutiServiceTest extends TestCase
{
    /** @test */
    public function calculate_hari_kerja_excludes_configured_holidays()
    {
        config()->set('cuti.excluded_dates', ['2026-04-14']);

        $service = app(CutiService::class);

        $this->assertSame(2, $service->calculateHariKerja('2026-04-13', '2026-04-15'));
    }

    /** @test */
    public function calculate_hari_kerja_excludes_recurring_month_day_holidays()
    {
        config()->set('cuti.excluded_dates', ['04-14']);

        $service = app(CutiService::class);

        $this->assertSame(2, $service->calculateHariKerja('2026-04-13', '2026-04-15'));
        $this->assertSame(2, $service->calculateHariKerja('2027-04-13', '2027-04-15'));
    }
}

