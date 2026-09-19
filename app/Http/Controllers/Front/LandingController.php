<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LandingController extends Controller
{
    public function index()
    {
        if (! Schema::hasTable('pegawais')) {
            $genderChart = ['labels' => ['Laki-laki', 'Perempuan'], 'data' => [0, 0], 'total' => 0];
            $golonganChart = ['labels' => [], 'sublabels' => [], 'data' => [], 'total' => 0, 'terbanyak' => '-', 'terbanyak_total' => 0, 'highest' => '-'];
            $pendidikanChart = ['labels' => [], 'data' => [], 'total' => 0];
            $eselonChart = ['labels' => [], 'data' => [], 'total' => 0];

            return view('landing', compact('genderChart', 'golonganChart', 'pendidikanChart', 'eselonChart'));
        }
        // 1. Grafik Jumlah Pegawai per Jenis Kelamin
        $genderRaw = Pegawai::query()
            ->selectRaw("jenis_kelamin, COUNT(*) as total")
            ->groupBy('jenis_kelamin')
            ->get();

        $totalLaki = (int) ($genderRaw->firstWhere('jenis_kelamin', true)?->total ?? $genderRaw->firstWhere('jenis_kelamin', 1)?->total ?? 0);
        $totalPerempuan = (int) ($genderRaw->firstWhere('jenis_kelamin', false)?->total ?? $genderRaw->firstWhere('jenis_kelamin', 0)?->total ?? 0);
        $totalPegawai = (int) Pegawai::count();
        $genderChart = [
            'labels' => ['Laki-laki', 'Perempuan'],
            'data' => [$totalLaki, $totalPerempuan],
            'total' => $totalPegawai,
        ];

        // 2. Grafik Jumlah Pegawai per Golongan
        $golonganRaw = Pegawai::query()
            ->leftJoin('pangkats', 'pangkats.id', '=', 'pegawais.pangkat_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(pangkats.golongan_ruang), ''), 'Non Golongan') as golongan_ruang, MIN(COALESCE(pangkats.id, 9999)) as pangkat_order, COUNT(*) as total")
            ->groupBy('golongan_ruang')
            ->orderBy('pangkat_order')
            ->get();

        $topGolongan = $golonganRaw->sortByDesc('total')->first();
        $validGolongan = $golonganRaw->filter(fn ($item) => $item->golongan_ruang !== 'Non Golongan');

        $golonganChart = [
            'labels' => $golonganRaw->pluck('golongan_ruang')->all(),
            'data' => $golonganRaw->pluck('total')->map(fn ($v) => (int) $v)->all(),
            'total' => (int) $golonganRaw->sum('total'),
            'terbanyak' => $topGolongan?->golongan_ruang ?? '-',
            'terbanyak_total' => (int) ($topGolongan?->total ?? 0),
            'highest' => $validGolongan->last()?->golongan_ruang ?? '-',
        ];

        // 3. Grafik Jumlah Pegawai per Tingkat Pendidikan
        $pendidikanRaw = Pegawai::query()
            ->leftJoin('pendidikans', 'pendidikans.id', '=', 'pegawais.pendidikan_id')
            ->leftJoin('tingkat_pendidikans', 'tingkat_pendidikans.id', '=', 'pendidikans.tingkat_pendidikan_id')
            ->selectRaw("COALESCE(NULLIF(tingkat_pendidikans.group_tingkat_pendidikan, ''), tingkat_pendidikans.tingkat_pendidikan, 'Lainnya') as label, MIN(COALESCE(tingkat_pendidikans.id, 9999)) as pendidikan_order, COUNT(*) as total")
            ->groupBy('label')
            ->orderBy('pendidikan_order')
            ->get();

        $pendidikanChart = [
            'labels' => $pendidikanRaw->pluck('label')->all(),
            'data' => $pendidikanRaw->pluck('total')->map(fn ($v) => (int) $v)->all(),
            'total' => (int) $pendidikanRaw->sum('total'),
        ];

        // 4. Grafik Jumlah Pegawai per Eselon Jabatan
        $eselonRaw = Pegawai::query()
            ->leftJoin('eselons', 'eselons.id', '=', 'pegawais.eselon_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(eselons.eselon), ''), 'Non Eselon') as eselon_label, COUNT(*) as total")
            ->groupBy('eselon_label')
            ->orderByRaw("CASE WHEN eselon_label = 'Non Eselon' THEN 99 ELSE 1 END, eselon_label ASC")
            ->get();

        $eselonChart = [
            'labels' => $eselonRaw->pluck('eselon_label')->all(),
            'data' => $eselonRaw->pluck('total')->map(fn ($v) => (int) $v)->all(),
            'total' => (int) $eselonRaw->sum('total'),
        ];

        return view('landing', compact('genderChart', 'golonganChart', 'pendidikanChart', 'eselonChart'));
    }
}

