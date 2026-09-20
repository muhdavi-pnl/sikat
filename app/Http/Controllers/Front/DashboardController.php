<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\DokumenPegawai;
use App\Models\Layanan;
use App\Models\Pegawai;
use App\Models\StudiLanjut;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Entry point dashboard yang menampilkan Dashboard Pimpinan untuk pimpinan/admin
     * dan Dashboard Pegawai untuk pegawai biasa.
     */
    public function index()
    {
        $user = auth()->user();

        // Pimpinan, super-admin, dan kepegawaian dapat melihat Dashboard Pimpinan
        if ($user && $user->hasAnyRole(['pimpinan', 'super-admin', 'kepegawaian'])) {
            return $this->dashboardPimpinan();
        }

        return $this->dashboardPegawai();
    }

    /**
     * Dashboard Pimpinan: Ringkasan tingkat institusi (Jabatan Fungsional Dosen,
     * Tingkat Pendidikan Pegawai, Sertifikasi Dosen, Status Studi Lanjut Dosen).
     */
    public function dashboardPimpinan()
    {
        $totalPegawai = Pegawai::count();
        $totalDosen = Pegawai::where(function ($q) {
            $q->where('kelompok_pegawai', 'dosen')->orWhereNull('kelompok_pegawai');
        })->count();
        $totalTendik = Pegawai::where('kelompok_pegawai', 'tendik')->count();

        $jurusanMap = [
            1 => 'Teknik Sipil',
            2 => 'Teknik Kimia',
            3 => 'Teknik Mesin',
            4 => 'Teknik Elektro',
            5 => 'Bisnis',
            6 => 'TIK',
        ];

        $unitMap = [
            1 => 'Teknik Sipil',
            2 => 'Teknik Kimia',
            3 => 'Teknik Mesin',
            4 => 'Teknik Elektro',
            5 => 'Bisnis',
            6 => 'TIK',
            7 => 'UPA & Pusat',
        ];

        // 1. Grafik Jabatan Fungsional Dosen per Jurusan
        $jabatanMap = [
            'tenaga pengajar' => 'Tenaga Pengajar',
            'asisten ahli' => 'Asisten Ahli',
            'lektor' => 'Lektor',
            'lektor kepala' => 'Lektor Kepala',
            'profesor' => 'Profesor',
        ];

        $jurusanJabatanTotals = Pegawai::query()
            ->join('program_studis', 'program_studis.id', '=', 'pegawais.program_studi_id')
            ->leftJoin('jabatans', 'jabatans.id', '=', 'pegawais.jabatan_id')
            ->selectRaw("program_studis.jurusan_id, COALESCE(NULLIF(LOWER(TRIM(jabatans.jabatan)), ''), 'tenaga pengajar') as jabatan_key, COUNT(*) as total")
            ->where(function ($q) {
                $q->where('pegawais.kelompok_pegawai', 'dosen')->orWhereNull('pegawais.kelompok_pegawai');
            })
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

        // 2. Grafik Jumlah Pegawai Berdasarkan Tingkat Pendidikan (Dosen & Tendik)
        $pendidikanMap = [
            's3' => 'S-3 / Doktor',
            's2' => 'S-2 / Magister',
            's1' => 'S-1 / D-IV',
            'd3' => 'D-III',
            'slta' => 'SLTA / SMK',
            'lainnya' => 'Lainnya',
        ];

        $pegawaiPendidikanRaw = Pegawai::query()
            ->leftJoin('program_studis', 'program_studis.id', '=', 'pegawais.program_studi_id')
            ->leftJoin('unit_kerjas', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id')
            ->leftJoin('pendidikans', 'pendidikans.id', '=', 'pegawais.pendidikan_id')
            ->leftJoin('tingkat_pendidikans', 'tingkat_pendidikans.id', '=', 'pendidikans.tingkat_pendidikan_id')
            ->selectRaw("
                COALESCE(pegawais.kelompok_pegawai, 'dosen') as kelompok_pegawai,
                CASE
                    WHEN program_studis.jurusan_id BETWEEN 1 AND 6 THEN program_studis.jurusan_id
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%sipil%' THEN 1
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%kimia%' THEN 2
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%mesin%' THEN 3
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%elektro%' THEN 4
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%bisnis%' THEN 5
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%informasi%' OR LOWER(unit_kerjas.unit_kerja) LIKE '%tik%' THEN 6
                    ELSE 7
                END as unit_id,
                CASE
                    WHEN tingkat_pendidikans.id = 50
                        OR LOWER(COALESCE(tingkat_pendidikans.group_tingkat_pendidikan, '')) = 's-3'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%s-3%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%doktor%'
                        THEN 's3'
                    WHEN tingkat_pendidikans.id = 45
                        OR LOWER(COALESCE(tingkat_pendidikans.group_tingkat_pendidikan, '')) = 's-2'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%s-2%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%magister%'
                        THEN 's2'
                    WHEN tingkat_pendidikans.id IN (35, 40)
                        OR LOWER(COALESCE(tingkat_pendidikans.group_tingkat_pendidikan, '')) = 's-1/d-iv'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%s-1%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%sarjana%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%d-iv%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%diploma iv%'
                        THEN 's1'
                    WHEN tingkat_pendidikans.id = 30
                        OR LOWER(COALESCE(tingkat_pendidikans.group_tingkat_pendidikan, '')) = 'd-iii'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%d-iii%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%diploma iii%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%diploma 3%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%d3%'
                        THEN 'd3'
                    WHEN tingkat_pendidikans.id IN (15, 17, 18, 20)
                        OR LOWER(COALESCE(tingkat_pendidikans.group_tingkat_pendidikan, '')) LIKE '%slta%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%slta%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%sma%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%smk%'
                        THEN 'slta'
                    ELSE 'lainnya'
                END as pendidikan_key,
                COUNT(*) as total
            ")
            ->groupBy('kelompok_pegawai', 'unit_id', 'pendidikan_key')
            ->get();

        $pegawaiPendidikanTotals = collect($pendidikanMap)
            ->map(function ($label, $key) use ($pegawaiPendidikanRaw) {
                $dosenCount = (int) $pegawaiPendidikanRaw
                    ->where('kelompok_pegawai', 'dosen')
                    ->where('pendidikan_key', $key)
                    ->sum('total');

                $tendikCount = (int) $pegawaiPendidikanRaw
                    ->where('kelompok_pegawai', 'tendik')
                    ->where('pendidikan_key', $key)
                    ->sum('total');

                return [
                    'key' => $key,
                    'label' => $label,
                    'dosen_count' => $dosenCount,
                    'tendik_count' => $tendikCount,
                    'count' => $dosenCount + $tendikCount,
                ];
            })
            ->values()
            ->all();

        $pegawaiPendidikanChart = collect($unitMap)
            ->map(function ($unitLabel, $unitId) use ($pendidikanMap, $pegawaiPendidikanRaw) {
                $unitRows = $pegawaiPendidikanRaw->where('unit_id', (int) $unitId);

                $dosenTotals = $unitRows->where('kelompok_pegawai', 'dosen')->pluck('total', 'pendidikan_key');
                $tendikTotals = $unitRows->where('kelompok_pegawai', 'tendik')->pluck('total', 'pendidikan_key');

                $dosenDistribution = collect($pendidikanMap)->map(function ($label, $key) use ($dosenTotals) {
                    return [
                        'key' => $key,
                        'label' => $label,
                        'count' => (int) ($dosenTotals[$key] ?? 0),
                    ];
                })->values();

                $tendikDistribution = collect($pendidikanMap)->map(function ($label, $key) use ($tendikTotals) {
                    return [
                        'key' => $key,
                        'label' => $label,
                        'count' => (int) ($tendikTotals[$key] ?? 0),
                    ];
                })->values();

                $combinedDistribution = collect($pendidikanMap)->map(function ($label, $key) use ($dosenDistribution, $tendikDistribution) {
                    $dosenCount = $dosenDistribution->firstWhere('key', $key)['count'] ?? 0;
                    $tendikCount = $tendikDistribution->firstWhere('key', $key)['count'] ?? 0;

                    return [
                        'key' => $key,
                        'label' => $label,
                        'dosen_count' => $dosenCount,
                        'tendik_count' => $tendikCount,
                        'count' => $dosenCount + $tendikCount,
                    ];
                })->values();

                $totalDosen = (int) $dosenDistribution->sum('count');
                $totalTendik = (int) $tendikDistribution->sum('count');
                $totalPegawai = $totalDosen + $totalTendik;

                return [
                    'unit_id' => (int) $unitId,
                    'unit_label' => $unitLabel,
                    'total_pegawai' => $totalPegawai,
                    'total_dosen' => $totalDosen,
                    'total_tendik' => $totalTendik,
                    'dosen_distribution' => $dosenDistribution->map(function (array $item) use ($totalDosen) {
                        $item['percentage'] = $totalDosen > 0
                            ? round(($item['count'] / $totalDosen) * 100, 2)
                            : 0.0;
                        return $item;
                    })->all(),
                    'tendik_distribution' => $tendikDistribution->map(function (array $item) use ($totalTendik) {
                        $item['percentage'] = $totalTendik > 0
                            ? round(($item['count'] / $totalTendik) * 100, 2)
                            : 0.0;
                        return $item;
                    })->all(),
                    'combined_distribution' => $combinedDistribution->map(function (array $item) use ($totalPegawai) {
                        $item['percentage'] = $totalPegawai > 0
                            ? round(($item['count'] / $totalPegawai) * 100, 2)
                            : 0.0;
                        return $item;
                    })->all(),
                ];
            })
            ->values()
            ->all();

        // 3. Grafik Jumlah Dosen Berdasarkan Sertifikasi Dosen per Jurusan (Berdasarkan no_serdos pada tabel pegawai_identitas)
        $sertifikasiMap = [
            'sudah_sertifikasi' => 'Sudah Sertifikasi',
            'belum_sertifikasi' => 'Belum Sertifikasi',
        ];

        $sertifikasiRaw = Pegawai::query()
            ->join('program_studis', 'program_studis.id', '=', 'pegawais.program_studi_id')
            ->leftJoin('pegawai_identitas', 'pegawai_identitas.pegawai_id', '=', 'pegawais.id')
            ->selectRaw("
                program_studis.jurusan_id,
                CASE
                    WHEN pegawai_identitas.no_serdos IS NOT NULL AND TRIM(pegawai_identitas.no_serdos) != '' THEN 'sudah_sertifikasi'
                    ELSE 'belum_sertifikasi'
                END as sertifikasi_key,
                COUNT(DISTINCT pegawais.id) as total
            ")
            ->where(function ($q) {
                $q->where('pegawais.kelompok_pegawai', 'dosen')->orWhereNull('pegawais.kelompok_pegawai');
            })
            ->whereIn('program_studis.jurusan_id', array_keys($jurusanMap))
            ->groupBy('program_studis.jurusan_id', 'sertifikasi_key')
            ->get();

        $sertifikasiDosenTotals = collect($sertifikasiMap)
            ->map(function ($label, $key) use ($sertifikasiRaw) {
                return [
                    'key' => $key,
                    'label' => $label,
                    'count' => (int) $sertifikasiRaw
                        ->where('sertifikasi_key', $key)
                        ->sum('total'),
                ];
            })
            ->values()
            ->all();

        $jurusanSertifikasiChart = collect($jurusanMap)
            ->map(function ($jurusanLabel, $jurusanId) use ($sertifikasiMap, $sertifikasiRaw) {
                $jurusanTotals = $sertifikasiRaw
                    ->where('jurusan_id', (int) $jurusanId)
                    ->pluck('total', 'sertifikasi_key');

                $distribution = collect($sertifikasiMap)
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

        // 4. Grafik Jumlah Dosen Berdasarkan Bidang Studi Lanjut (Donut Chart)
        $bidangIlmuMap = StudiLanjut::BIDANG_ILMU_OPTIONS;

        $studiLanjutColors = [
            'STEM' => '#6777ef',
            'EKONOMI' => '#3abaf4',
            'SOSIAL' => '#ffa426',
            'HUMANIORA' => '#47c363',
            'KEAGAMAAN' => '#6f42c1',
            'LAINNYA' => '#98a6ad',
        ];

        $studiLanjutRaw = StudiLanjut::query()
            ->join('pegawais', 'pegawais.id', '=', 'studi_lanjuts.pegawai_id')
            ->selectRaw("
                UPPER(COALESCE(studi_lanjuts.bidang_ilmu, 'STEM')) as bidang_key,
                COUNT(DISTINCT studi_lanjuts.pegawai_id) as total
            ")
            ->where(function ($q) {
                $q->where('pegawais.kelompok_pegawai', 'dosen')->orWhereNull('pegawais.kelompok_pegawai');
            })
            ->whereNull('studi_lanjuts.deleted_at')
            ->groupBy('bidang_key')
            ->get();

        $totalStudiLanjutDosen = (int) $studiLanjutRaw->sum('total');

        $studiLanjutTotals = collect($bidangIlmuMap)
            ->map(function ($label, $key) use ($studiLanjutRaw, $studiLanjutColors, $totalStudiLanjutDosen) {
                $count = (int) ($studiLanjutRaw->firstWhere('bidang_key', $key)->total ?? 0);
                $percentage = $totalStudiLanjutDosen > 0
                    ? round(($count / $totalStudiLanjutDosen) * 100, 1)
                    : 0.0;

                return [
                    'key' => $key,
                    'label' => $label,
                    'count' => $count,
                    'percentage' => $percentage,
                    'color' => $studiLanjutColors[$key] ?? '#6c757d',
                ];
            })
            ->values()
            ->all();

        $extraBidangRaw = $studiLanjutRaw->reject(function ($row) use ($bidangIlmuMap) {
            return array_key_exists($row->bidang_key, $bidangIlmuMap);
        });

        if ($extraBidangRaw->isNotEmpty()) {
            $extraTotal = (int) $extraBidangRaw->sum('total');
            $studiLanjutTotals[] = [
                'key' => 'LAINNYA',
                'label' => 'Lainnya',
                'count' => $extraTotal,
                'percentage' => $totalStudiLanjutDosen > 0
                    ? round(($extraTotal / $totalStudiLanjutDosen) * 100, 1)
                    : 0.0,
                'color' => $studiLanjutColors['LAINNYA'] ?? '#98a6ad',
            ];
        }

        $circumference = 2 * M_PI * 60; // radius = 60
        $currentOffset = 0.0;
        $studiLanjutDonutSegments = [];

        foreach ($studiLanjutTotals as $item) {
            $dashLength = $totalStudiLanjutDosen > 0
                ? ($item['count'] / $totalStudiLanjutDosen) * $circumference
                : 0.0;
            $dashOffset = -$currentOffset;

            $studiLanjutDonutSegments[] = array_merge($item, [
                'dash_length' => round($dashLength, 4),
                'dash_space' => round($circumference, 4),
                'dash_offset' => round($dashOffset, 4),
            ]);

            $currentOffset += $dashLength;
        }

        return view('dashboard.index', [
            'pegawais' => $totalPegawai,
            'dosens' => $totalDosen,
            'tendiks' => $totalTendik,
            'jabatanFungsionalTotals' => $jabatanFungsionalTotals,
            'jurusanJabatanChart' => $jurusanJabatanChart,
            'pegawaiPendidikanTotals' => $pegawaiPendidikanTotals,
            'pegawaiPendidikanChart' => $pegawaiPendidikanChart,
            'sertifikasiDosenTotals' => $sertifikasiDosenTotals,
            'jurusanSertifikasiChart' => $jurusanSertifikasiChart,
            'studiLanjutTotals' => $studiLanjutTotals,
            'studiLanjutDonutSegments' => $studiLanjutDonutSegments,
            'totalStudiLanjutDosen' => $totalStudiLanjutDosen,
            'title' => 'Dashboard Pimpinan',
        ]);
    }

    /**
     * Dashboard Pegawai: Khusus untuk pegawai dengan statistik dan grafik
     * Jumlah Pegawai Berdasarkan Jenis Kelamin per Unit Kerja / Jurusan.
     */
    public function dashboardPegawai()
    {
        $user = auth()->user();
        $pegawai = $user?->pegawai;

        $totalPegawai = Pegawai::count();
        $totalDosen = Pegawai::where(function ($q) {
            $q->where('kelompok_pegawai', 'dosen')->orWhereNull('kelompok_pegawai');
        })->count();
        $totalTendik = Pegawai::where('kelompok_pegawai', 'tendik')->count();

        $genderMap = [
            'laki_laki' => 'Laki-laki',
            'perempuan' => 'Perempuan',
        ];

        $unitMap = [
            1 => 'Teknik Sipil',
            2 => 'Teknik Kimia',
            3 => 'Teknik Mesin',
            4 => 'Teknik Elektro',
            5 => 'Bisnis',
            6 => 'TIK',
            7 => 'UPA & Pusat',
        ];

        $pegawaiGenderRaw = Pegawai::query()
            ->leftJoin('program_studis', 'program_studis.id', '=', 'pegawais.program_studi_id')
            ->leftJoin('unit_kerjas', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id')
            ->selectRaw("
                COALESCE(pegawais.kelompok_pegawai, 'dosen') as kelompok_pegawai,
                CASE
                    WHEN program_studis.jurusan_id BETWEEN 1 AND 6 THEN program_studis.jurusan_id
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%sipil%' THEN 1
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%kimia%' THEN 2
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%mesin%' THEN 3
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%elektro%' THEN 4
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%bisnis%' THEN 5
                    WHEN LOWER(unit_kerjas.unit_kerja) LIKE '%informasi%' OR LOWER(unit_kerjas.unit_kerja) LIKE '%tik%' THEN 6
                    ELSE 7
                END as unit_id,
                CASE
                    WHEN pegawais.jenis_kelamin = 1 OR pegawais.jenis_kelamin = true THEN 'laki_laki'
                    ELSE 'perempuan'
                END as gender_key,
                COUNT(*) as total
            ")
            ->groupBy('kelompok_pegawai', 'unit_id', 'gender_key')
            ->get();

        $pegawaiGenderTotals = collect($genderMap)
            ->map(function ($label, $key) use ($pegawaiGenderRaw) {
                $dosenCount = (int) $pegawaiGenderRaw
                    ->where('kelompok_pegawai', 'dosen')
                    ->where('gender_key', $key)
                    ->sum('total');

                $tendikCount = (int) $pegawaiGenderRaw
                    ->where('kelompok_pegawai', 'tendik')
                    ->where('gender_key', $key)
                    ->sum('total');

                return [
                    'key' => $key,
                    'label' => $label,
                    'dosen_count' => $dosenCount,
                    'tendik_count' => $tendikCount,
                    'count' => $dosenCount + $tendikCount,
                ];
            })
            ->values()
            ->all();

        $totalLakiLaki = collect($pegawaiGenderTotals)->firstWhere('key', 'laki_laki')['count'] ?? 0;
        $totalPerempuan = collect($pegawaiGenderTotals)->firstWhere('key', 'perempuan')['count'] ?? 0;

        $pegawaiGenderChart = collect($unitMap)
            ->map(function ($unitLabel, $unitId) use ($genderMap, $pegawaiGenderRaw) {
                $unitRows = $pegawaiGenderRaw->where('unit_id', (int) $unitId);

                $dosenTotals = $unitRows->where('kelompok_pegawai', 'dosen')->pluck('total', 'gender_key');
                $tendikTotals = $unitRows->where('kelompok_pegawai', 'tendik')->pluck('total', 'gender_key');

                $dosenDistribution = collect($genderMap)->map(function ($label, $key) use ($dosenTotals) {
                    return [
                        'key' => $key,
                        'label' => $label,
                        'count' => (int) ($dosenTotals[$key] ?? 0),
                    ];
                })->values();

                $tendikDistribution = collect($genderMap)->map(function ($label, $key) use ($tendikTotals) {
                    return [
                        'key' => $key,
                        'label' => $label,
                        'count' => (int) ($tendikTotals[$key] ?? 0),
                    ];
                })->values();

                $combinedDistribution = collect($genderMap)->map(function ($label, $key) use ($dosenDistribution, $tendikDistribution) {
                    $dosenCount = $dosenDistribution->firstWhere('key', $key)['count'] ?? 0;
                    $tendikCount = $tendikDistribution->firstWhere('key', $key)['count'] ?? 0;

                    return [
                        'key' => $key,
                        'label' => $label,
                        'dosen_count' => $dosenCount,
                        'tendik_count' => $tendikCount,
                        'count' => $dosenCount + $tendikCount,
                    ];
                })->values();

                $unitTotalDosen = (int) $dosenDistribution->sum('count');
                $unitTotalTendik = (int) $tendikDistribution->sum('count');
                $unitTotalPegawai = $unitTotalDosen + $unitTotalTendik;

                return [
                    'unit_id' => (int) $unitId,
                    'unit_label' => $unitLabel,
                    'total_pegawai' => $unitTotalPegawai,
                    'total_dosen' => $unitTotalDosen,
                    'total_tendik' => $unitTotalTendik,
                    'dosen_distribution' => $dosenDistribution->map(function (array $item) use ($unitTotalDosen) {
                        $item['percentage'] = $unitTotalDosen > 0
                            ? round(($item['count'] / $unitTotalDosen) * 100, 2)
                            : 0.0;
                        return $item;
                    })->all(),
                    'tendik_distribution' => $tendikDistribution->map(function (array $item) use ($unitTotalTendik) {
                        $item['percentage'] = $unitTotalTendik > 0
                            ? round(($item['count'] / $unitTotalTendik) * 100, 2)
                            : 0.0;
                        return $item;
                    })->all(),
                    'combined_distribution' => $combinedDistribution->map(function (array $item) use ($unitTotalPegawai) {
                        $item['percentage'] = $unitTotalPegawai > 0
                            ? round(($item['count'] / $unitTotalPegawai) * 100, 2)
                            : 0.0;
                        return $item;
                    })->all(),
                ];
            })
            ->values()
            ->all();

        return view('dashboard.pegawai', [
            'pegawai' => $pegawai,
            'pegawais' => $totalPegawai,
            'dosens' => $totalDosen,
            'tendiks' => $totalTendik,
            'totalLakiLaki' => $totalLakiLaki,
            'totalPerempuan' => $totalPerempuan,
            'pegawaiGenderTotals' => $pegawaiGenderTotals,
            'pegawaiGenderChart' => $pegawaiGenderChart,
            'title' => 'Dashboard Pegawai',
        ]);
    }
}
