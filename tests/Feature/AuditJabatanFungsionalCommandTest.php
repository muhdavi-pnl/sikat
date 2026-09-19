<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditJabatanFungsionalCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function command_skips_gracefully_when_column_does_not_exist()
    {
        $this->artisan('audit:jabatan-fungsional-canonical')
            ->expectsOutput('Table pegawais/pegawai_identitas atau kolom jabatan_fungsional tidak ditemukan. Audit dilewati.')
            ->assertSuccessful();
    }
}
