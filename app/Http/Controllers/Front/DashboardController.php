<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\DokumenPegawai;
use App\Models\Layanan;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPegawai = Pegawai::count();
        $totalDosen = Pegawai::where('kelompok_pegawai', 'dosen')->count();
        $totalTendik = Pegawai::where('kelompok_pegawai', 'tenaga kependidikan')->count();

        $jurusanMap = [
            1 => 'Teknik Sipil',
            2 => 'Teknik Mesin',
            3 => 'Teknik Kimia',
            4 => 'Teknik Elektro',
            5 => 'Bisnis',
            6 => 'TIK',
        ];

        $jabatanMap = [
            'tenaga pengajar' => 'Tenaga Pengajar',
            'asisten ahli' => 'Asisten Ahli',
            'lektor' => 'Lektor',
            'lektor kepala' => 'Lektor Kepala',
            'profesor' => 'Profesor',
        ];

        $jurusanJabatanTotals = Pegawai::query()
            ->join('program_studis', 'program_studis.id', '=', 'pegawais.program_studi_id')
            ->leftJoin('pegawai_identitas', 'pegawai_identitas.pegawai_id', '=', 'pegawais.id')
            ->selectRaw("program_studis.jurusan_id, COALESCE(NULLIF(LOWER(TRIM(pegawai_identitas.jabatan_fungsional)), ''), 'tenaga pengajar') as jabatan_key, COUNT(*) as total")
            ->whereIn('program_studis.jurusan_id', array_keys($jurusanMap))
            ->groupBy('program_studis.jurusan_id', 'jabatan_key')
            ->get();

        $jabatanFungsionalTotals = collect($jabatanMap)
            ->map(function ($label, $key) use ($jurusanJabatanTotals) {
                return [
                    'key' => $key,
                    'label' => $label,
                    'count' => (int) $jurusanJabatanTotals
                        ->where('jabatan_key', $key)
                        ->sum('total'),
                ];
            })
            ->values()
            ->all();

        $jurusanJabatanChart = collect($jurusanMap)
            ->map(function ($jurusanLabel, $jurusanId) use ($jabatanMap, $jurusanJabatanTotals) {
                $jurusanTotals = $jurusanJabatanTotals
                    ->where('jurusan_id', (int) $jurusanId)
                    ->pluck('total', 'jabatan_key');

                $distribution = collect($jabatanMap)
                    ->map(function ($label, $key) use ($jurusanTotals) {
                        return [
                            'key' => $key,
                            'label' => $label,
                            'count' => (int) ($jurusanTotals[$key] ?? 0),
                        ];
                    })
                    ->values();

                $jurusanTotal = (int) $distribution->sum('count');

                return [
                    'jurusan_id' => (int) $jurusanId,
                    'jurusan_label' => $jurusanLabel,
                    'total' => $jurusanTotal,
                    'distribution' => $distribution->map(function (array $item) use ($jurusanTotal) {
                        $item['percentage'] = $jurusanTotal > 0
                            ? round(($item['count'] / $jurusanTotal) * 100, 2)
                            : 0.0;

                        return $item;
                    })->all(),
                ];
            })
            ->values()
            ->all();

        return view('dashboard', [
            'pegawais' => $totalPegawai,
            'dosens' => $totalDosen,
            'tendiks' => $totalTendik,
            'jabatanFungsionalTotals' => $jabatanFungsionalTotals,
            'jurusanJabatanChart' => $jurusanJabatanChart,
            'title' => 'Dashboard',
        ]);
    }
}
