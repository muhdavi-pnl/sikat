<?php

namespace App\Services\PetaJabatan;

use App\Models\Jabatan;
use App\Models\Pegawai;

class PetaJabatanDashboardService
{
    /**
     * Build the Peta Jabatan dashboard payload described in
     * docs/peta-jabatan.md section 9 (Dashboard Peta Jabatan).
     */
    public function summary(): array
    {
        $jabatans = Jabatan::query()
            ->with('unit_kerja')
            ->withCount('pegawais')
            ->get();

        $totalJabatan = $jabatans->count();
        $jabatanTerisi = $jabatans->filter(fn (Jabatan $jabatan) => $jabatan->pegawais_count > 0)->count();
        $jabatanKosong = $totalJabatan - $jabatanTerisi;

        $kekurangan = 0;
        $kelebihan = 0;
        $jabatanMembutuhkanPengisian = [];

        foreach ($jabatans as $jabatan) {
            $kebutuhan = (int) ($jabatan->kebutuhan_pegawai ?? 0);
            $terisi = (int) $jabatan->pegawais_count;
            $selisih = $kebutuhan - $terisi;

            if ($selisih > 0) {
                $kekurangan += $selisih;
                $jabatanMembutuhkanPengisian[] = [
                    'jabatan' => $jabatan->jabatan,
                    'unit_kerja' => $jabatan->unit_kerja?->unit_kerja ?? '-',
                    'kebutuhan' => $kebutuhan,
                    'terisi' => $terisi,
                    'kekurangan' => $selisih,
                ];
            } elseif ($selisih < 0) {
                $kelebihan += abs($selisih);
            }
        }

        return [
            'total_jabatan' => $totalJabatan,
            'jabatan_terisi' => $jabatanTerisi,
            'jabatan_kosong' => $jabatanKosong,
            'kekurangan_pegawai' => $kekurangan,
            'kelebihan_pegawai' => $kelebihan,
            'distribusi_pegawai_per_jabatan' => $this->distribusiPegawaiPerJabatan($jabatans),
            'distribusi_dosen_per_jabatan_akademik' => $this->distribusiDosenPerJabatanAkademik(),
            'distribusi_per_unit_kerja' => $this->distribusiPerUnitKerja(),
            'distribusi_per_pendidikan' => $this->distribusiPerPendidikan(),
            'jabatan_membutuhkan_pengisian' => collect($jabatanMembutuhkanPengisian)
                ->sortByDesc('kekurangan')
                ->values()
                ->all(),
        ];
    }

    private function distribusiPegawaiPerJabatan($jabatans): array
    {
        return $jabatans
            ->sortByDesc('pegawais_count')
            ->map(fn (Jabatan $jabatan) => [
                'jabatan' => $jabatan->jabatan,
                'total' => (int) $jabatan->pegawais_count,
            ])
            ->values()
            ->all();
    }

    private function distribusiDosenPerJabatanAkademik(): array
    {
        // jabatan_fungsional now lives on pegawai_identitas (see the
        // 2026_09_05_150000_separate_pegawai_identitas migration), not on
        // pegawais itself, so it must be reached through a left join.
        $totals = Pegawai::query()
            ->leftJoin('pegawai_identitas', 'pegawai_identitas.pegawai_id', '=', 'pegawais.id')
            ->selectRaw("COALESCE(NULLIF(LOWER(TRIM(pegawai_identitas.jabatan_fungsional)), ''), 'tenaga pengajar') as jabatan_key, COUNT(*) as total")
            ->groupBy('jabatan_key')
            ->pluck('total', 'jabatan_key');

        $labels = ['tenaga pengajar' => 'Tenaga Pengajar'] + Pegawai::JABATAN_FUNGSIONAL_OPTIONS;

        return collect($labels)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'total' => (int) ($totals[$key] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function distribusiPerUnitKerja(): array
    {
        return Pegawai::query()
            ->join('unit_kerjas', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id')
            ->selectRaw('unit_kerjas.unit_kerja as unit_kerja, COUNT(*) as total')
            ->groupBy('unit_kerjas.id', 'unit_kerjas.unit_kerja')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'unit_kerja' => $row->unit_kerja,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function distribusiPerPendidikan(): array
    {
        return Pegawai::query()
            ->join('pendidikans', 'pendidikans.id', '=', 'pegawais.pendidikan_id')
            ->selectRaw('pendidikans.pendidikan as pendidikan, COUNT(*) as total')
            ->groupBy('pendidikans.id', 'pendidikans.pendidikan')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'pendidikan' => $row->pendidikan,
                'total' => (int) $row->total,
            ])
            ->all();
    }
}
