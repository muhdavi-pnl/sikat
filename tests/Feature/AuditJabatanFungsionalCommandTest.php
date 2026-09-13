<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditJabatanFungsionalCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function command_passes_when_all_jabatan_fungsional_values_are_canonical()
    {
        $pegawaiId = DB::table('pegawais')->insertGetId([
            'nip' => '198501012010011501',
            'nama' => 'Pegawai Profesor',
            'status_pegawai' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pegawai_identitas')->insert([
            'pegawai_id' => $pegawaiId,
            'jabatan_fungsional' => 'profesor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('audit:jabatan-fungsional-canonical')
            ->expectsOutput('Semua data jabatan_fungsional pegawai sudah canonical.')
            ->assertSuccessful();
    }

    /** @test */
    public function command_fails_when_non_canonical_jabatan_fungsional_values_exist()
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        try {
            $pegawaiId = DB::table('pegawais')->insertGetId([
                'nip' => '198501012010011502',
                'nama' => 'Pegawai Legacy',
                'status_pegawai' => 'PNS',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('pegawai_identitas')->insert([
                'pegawai_id' => $pegawaiId,
                'jabatan_fungsional' => ' guru besar ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } finally {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA ignore_check_constraints = OFF');
            }
        }

        $this->artisan('audit:jabatan-fungsional-canonical')
            ->expectsOutput('Ditemukan data jabatan_fungsional non-canonical: 1')
            ->expectsTable(
                ['ID', 'NIP', 'Nama', 'Raw Jabatan Fungsional'],
                [
                    [1, '198501012010011502', 'Pegawai Legacy', ' guru besar '],
                ]
            )
            ->assertFailed();
    }
}
