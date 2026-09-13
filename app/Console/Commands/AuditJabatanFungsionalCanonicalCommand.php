<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditJabatanFungsionalCanonicalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:jabatan-fungsional-canonical';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit raw pegawai jabatan_fungsional values and fail when non-canonical data remains.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!Schema::hasTable('pegawais') || !Schema::hasTable('pegawai_identitas') || !Schema::hasColumn('pegawai_identitas', 'jabatan_fungsional')) {
            $this->warn('Table pegawais/pegawai_identitas atau kolom jabatan_fungsional tidak ditemukan. Audit dilewati.');

            return self::SUCCESS;
        }

        $allowedValues = Pegawai::jabatanFungsionalValidationValues();

        $offenders = DB::table('pegawai_identitas')
            ->join('pegawais', 'pegawais.id', '=', 'pegawai_identitas.pegawai_id')
            ->select([
                'pegawais.id',
                'pegawais.nip',
                'pegawais.nama',
                'pegawai_identitas.jabatan_fungsional',
            ])
            ->orderBy('pegawais.id')
            ->get()
            ->filter(function ($pegawai) use ($allowedValues) {
                $rawValue = $pegawai->jabatan_fungsional;

                if ($rawValue === null) {
                    return false;
                }

                $normalized = mb_strtolower(trim((string) $rawValue));

                if ($normalized === '') {
                    return false;
                }

                return !in_array($normalized, $allowedValues, true) || $normalized !== $rawValue;
            })
            ->values();

        if ($offenders->isEmpty()) {
            $this->info('Semua data jabatan_fungsional pegawai sudah canonical.');

            return self::SUCCESS;
        }

        $this->error('Ditemukan data jabatan_fungsional non-canonical: ' . $offenders->count());
        $this->table(
            ['ID', 'NIP', 'Nama', 'Raw Jabatan Fungsional'],
            $offenders->map(function ($pegawai) {
                return [
                    $pegawai->id,
                    $pegawai->nip,
                    $pegawai->nama,
                    $pegawai->jabatan_fungsional,
                ];
            })->all()
        );

        return self::FAILURE;
    }
}
