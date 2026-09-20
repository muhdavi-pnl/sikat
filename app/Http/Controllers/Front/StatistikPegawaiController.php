<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Eselon;
use App\Models\JenisJabatan;
use App\Models\Pangkat;
use App\Models\Pegawai;
use App\Models\TingkatPendidikan;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\DB;

class StatistikPegawaiController extends Controller
{
    /**
     * Menampilkan halaman statistik dan rekapitulasi data pegawai berdasarkan unit kerja.
     */
    public function index()
    {
        $totalPegawai = Pegawai::count();
        $totalDosen = Pegawai::where(function ($q) {
            $q->where('kelompok_pegawai', 'dosen')->orWhereNull('kelompok_pegawai');
        })->count();
        $totalTendik = Pegawai::where('kelompok_pegawai', 'tendik')->count();

        $unitKerjaList = UnitKerja::orderBy('order', 'asc')->orderBy('id', 'asc')->get();

        // -------------------------------------------------------------
        // 1 & 6. STATISTIK & REKAPITULASI PER JENIS KELAMIN & UNIT KERJA
        // -------------------------------------------------------------
        $genderRaw = Pegawai::query()
            ->selectRaw("
                CASE
                    WHEN jenis_kelamin = 1 OR jenis_kelamin = true THEN 'laki_laki'
                    ELSE 'perempuan'
                END as gender_key,
                COALESCE(kelompok_pegawai, 'dosen') as kelompok_pegawai,
                COALESCE(status_pegawai, 'PNS') as status_pegawai,
                COUNT(*) as total
            ")
            ->groupBy('gender_key', 'kelompok_pegawai', 'status_pegawai')
            ->get();

        $genderDefinitions = [
            'laki_laki' => [
                'label' => 'Laki-laki',
                'color' => '#3abaf4',
                'icon' => 'fas fa-mars',
            ],
            'perempuan' => [
                'label' => 'Perempuan',
                'color' => '#fc544b',
                'icon' => 'fas fa-venus',
            ],
        ];

        $rekapGender = [];
        $totalLakiLaki = 0;
        $totalPerempuan = 0;

        foreach ($genderDefinitions as $key => $meta) {
            $rows = $genderRaw->where('gender_key', $key);
            $dosenCount = (int) $rows->where('kelompok_pegawai', 'dosen')->sum('total');
            $tendikCount = (int) $rows->where('kelompok_pegawai', 'tendik')->sum('total');
            $pnsCount = (int) $rows->where('status_pegawai', 'PNS')->sum('total');
            $pppkCount = (int) $rows->where('status_pegawai', 'PPPK')->sum('total');
            $otherCount = (int) $rows->reject(fn($r) => in_array($r->status_pegawai, ['PNS', 'PPPK']))->sum('total');
            $subtotal = $dosenCount + $tendikCount;
            $percentage = $totalPegawai > 0 ? round(($subtotal / $totalPegawai) * 100, 2) : 0.0;

            if ($key === 'laki_laki') {
                $totalLakiLaki = $subtotal;
            } else {
                $totalPerempuan = $subtotal;
            }

            $rekapGender[$key] = [
                'key' => $key,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'icon' => $meta['icon'],
                'dosen' => $dosenCount,
                'tendik' => $tendikCount,
                'pns' => $pnsCount,
                'pppk' => $pppkCount,
                'lainnya' => $otherCount,
                'total' => $subtotal,
                'percentage' => $percentage,
            ];
        }

        // Matriks Rekapitulasi Jenis Kelamin per Unit Kerja berdasarkan Status Pegawai
        $statusPegawaiOptions = ['PNS', 'CPNS', 'PPPK', 'PPPK Paruh Waktu'];

        $pegawaiUnitGenderRaw = Pegawai::query()
            ->selectRaw("
                unit_kerja_id,
                CASE
                    WHEN jenis_kelamin = 1 OR jenis_kelamin = true THEN 'laki_laki'
                    ELSE 'perempuan'
                END as gender_key,
                COALESCE(status_pegawai, 'PNS') as status_pegawai,
                COUNT(*) as total
            ")
            ->groupBy('unit_kerja_id', 'gender_key', 'status_pegawai')
            ->get();

        $rekapUnitKerjaGender = [];
        $statusGrandTotals = [];
        foreach ($statusPegawaiOptions as $st) {
            $statusGrandTotals[$st] = ['laki_laki' => 0, 'perempuan' => 0, 'subtotal' => 0];
        }

        foreach ($unitKerjaList as $uk) {
            $rows = $pegawaiUnitGenderRaw->where('unit_kerja_id', $uk->id);
            $unitRow = [
                'id' => $uk->id,
                'unit_kerja' => $uk->unit_kerja,
                'status' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach ($statusPegawaiOptions as $st) {
                $stRows = $rows->where('status_pegawai', $st);
                $lCount = (int) $stRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $stRows->where('gender_key', 'perempuan')->sum('total');
                $subtotal = $lCount + $pCount;

                $unitRow['status'][$st] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unitRow['total_laki_laki'] += $lCount;
                $unitRow['total_perempuan'] += $pCount;
                $unitRow['total'] += $subtotal;

                $statusGrandTotals[$st]['laki_laki'] += $lCount;
                $statusGrandTotals[$st]['perempuan'] += $pCount;
                $statusGrandTotals[$st]['subtotal'] += $subtotal;
            }

            $unitRow['percentage'] = $totalPegawai > 0 ? round(($unitRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            $rekapUnitKerjaGender[] = $unitRow;
        }

        // Unassigned Unit Kerja
        $unassignedUnitRows = $pegawaiUnitGenderRaw->whereNull('unit_kerja_id');
        $unmatchedUnitRows = $pegawaiUnitGenderRaw->whereNotNull('unit_kerja_id')
            ->reject(fn($r) => $unitKerjaList->contains('id', $r->unit_kerja_id));
        $allUnassignedUnit = $unassignedUnitRows->concat($unmatchedUnitRows);

        if ($allUnassignedUnit->isNotEmpty()) {
            $unassignedRow = [
                'id' => null,
                'unit_kerja' => 'Lainnya / Belum Diatur',
                'status' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach ($statusPegawaiOptions as $st) {
                $stRows = $allUnassignedUnit->where('status_pegawai', $st);
                $lCount = (int) $stRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $stRows->where('gender_key', 'perempuan')->sum('total');
                $subtotal = $lCount + $pCount;

                $unassignedRow['status'][$st] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unassignedRow['total_laki_laki'] += $lCount;
                $unassignedRow['total_perempuan'] += $pCount;
                $unassignedRow['total'] += $subtotal;

                $statusGrandTotals[$st]['laki_laki'] += $lCount;
                $statusGrandTotals[$st]['perempuan'] += $pCount;
                $statusGrandTotals[$st]['subtotal'] += $subtotal;
            }

            $unassignedRow['percentage'] = $totalPegawai > 0 ? round(($unassignedRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            if ($unassignedRow['total'] > 0) {
                $rekapUnitKerjaGender[] = $unassignedRow;
            }
        }

        // -------------------------------------------------------------
        // 2 & 7. STATISTIK & REKAPITULASI PER GOLONGAN & UNIT KERJA
        // -------------------------------------------------------------
        $pangkatList = Pangkat::orderBy('golongan_ruang', 'asc')->get();

        $golonganGroupKeys = [
            'Golongan I' => ['label' => 'Gol. I', 'color' => '#6777ef', 'items' => ['I/a', 'I/b', 'I/c', 'I/d']],
            'Golongan II' => ['label' => 'Gol. II', 'color' => '#3abaf4', 'items' => ['II/a', 'II/b', 'II/c', 'II/d']],
            'Golongan III' => ['label' => 'Gol. III', 'color' => '#47c363', 'items' => ['III/a', 'III/b', 'III/c', 'III/d']],
            'Golongan IV' => ['label' => 'Gol. IV', 'color' => '#ffa426', 'items' => ['IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e']],
            'PPPK' => ['label' => 'PPPK', 'color' => '#fc544b', 'items' => ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII']],
            'Lainnya / Belum Diatur' => ['label' => 'Lainnya', 'color' => '#98a6ad', 'items' => []],
        ];

        // Map each pangkat ID to its golongan group name
        $pangkatGroupMapping = [];
        foreach ($pangkatList as $pkt) {
            $golRuang = trim((string) $pkt->golongan_ruang);
            $pangkatNama = trim((string) $pkt->pangkat);

            $matchedGroup = 'Lainnya / Belum Diatur';
            if (str_starts_with($golRuang, 'I/') || in_array($golRuang, ['I/a', 'I/b', 'I/c', 'I/d'], true)) {
                $matchedGroup = 'Golongan I';
            } elseif (str_starts_with($golRuang, 'II/') || in_array($golRuang, ['II/a', 'II/b', 'II/c', 'II/d'], true)) {
                $matchedGroup = 'Golongan II';
            } elseif (str_starts_with($golRuang, 'III/') || in_array($golRuang, ['III/a', 'III/b', 'III/c', 'III/d'], true)) {
                $matchedGroup = 'Golongan III';
            } elseif (str_starts_with($golRuang, 'IV/') || in_array($golRuang, ['IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e'], true)) {
                $matchedGroup = 'Golongan IV';
            } elseif (stripos($pangkatNama, 'PPPK') !== false || in_array($golRuang, $golonganGroupKeys['PPPK']['items'], true)) {
                $matchedGroup = 'PPPK';
            }
            $pangkatGroupMapping[$pkt->id] = $matchedGroup;
        }

        $pangkatPegawaiUnitRaw = Pegawai::query()
            ->selectRaw("
                unit_kerja_id,
                pangkat_id,
                CASE
                    WHEN jenis_kelamin = 1 OR jenis_kelamin = true THEN 'laki_laki'
                    ELSE 'perempuan'
                END as gender_key,
                COALESCE(kelompok_pegawai, 'dosen') as kelompok_pegawai,
                COUNT(*) as total
            ")
            ->groupBy('unit_kerja_id', 'pangkat_id', 'gender_key', 'kelompok_pegawai')
            ->get();

        // Top summary & chart group totals
        $groupGolonganMap = [];
        foreach ($golonganGroupKeys as $gName => $gMeta) {
            $groupGolonganMap[$gName] = [
                'name' => $gName,
                'label' => $gMeta['label'],
                'color' => $gMeta['color'],
                'dosen' => 0,
                'tendik' => 0,
                'laki_laki' => 0,
                'perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];
        }

        // Matriks Rekapitulasi Golongan per Unit Kerja
        $rekapUnitKerjaGolongan = [];
        $golonganGrandTotals = [];
        foreach (array_keys($golonganGroupKeys) as $gName) {
            $golonganGrandTotals[$gName] = ['laki_laki' => 0, 'perempuan' => 0, 'subtotal' => 0];
        }

        foreach ($unitKerjaList as $uk) {
            $uRows = $pangkatPegawaiUnitRaw->where('unit_kerja_id', $uk->id);
            $unitRow = [
                'id' => $uk->id,
                'unit_kerja' => $uk->unit_kerja,
                'golongan' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach (array_keys($golonganGroupKeys) as $gName) {
                // Find all pangkat_ids for this group
                $grpPangkatIds = collect($pangkatGroupMapping)
                    ->filter(fn($grp) => $grp === $gName)
                    ->keys()
                    ->map(fn($id) => (string) $id)
                    ->all();

                $grpRows = $uRows->filter(function ($r) use ($grpPangkatIds, $gName, $pangkatGroupMapping) {
                    $pid = $r->pangkat_id !== null ? (string) $r->pangkat_id : null;
                    if ($gName === 'Lainnya / Belum Diatur') {
                        return $pid === null || in_array($pid, $grpPangkatIds, true) || !isset($pangkatGroupMapping[$r->pangkat_id]);
                    }
                    return $pid !== null && in_array($pid, $grpPangkatIds, true);
                });

                $lCount = (int) $grpRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $grpRows->where('gender_key', 'perempuan')->sum('total');
                $dosenCount = (int) $grpRows->where('kelompok_pegawai', 'dosen')->sum('total');
                $tendikCount = (int) $grpRows->where('kelompok_pegawai', 'tendik')->sum('total');
                $subtotal = $lCount + $pCount;

                $unitRow['golongan'][$gName] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unitRow['total_laki_laki'] += $lCount;
                $unitRow['total_perempuan'] += $pCount;
                $unitRow['total'] += $subtotal;

                $golonganGrandTotals[$gName]['laki_laki'] += $lCount;
                $golonganGrandTotals[$gName]['perempuan'] += $pCount;
                $golonganGrandTotals[$gName]['subtotal'] += $subtotal;

                $groupGolonganMap[$gName]['laki_laki'] += $lCount;
                $groupGolonganMap[$gName]['perempuan'] += $pCount;
                $groupGolonganMap[$gName]['dosen'] += $dosenCount;
                $groupGolonganMap[$gName]['tendik'] += $tendikCount;
                $groupGolonganMap[$gName]['total'] += $subtotal;
            }

            $unitRow['percentage'] = $totalPegawai > 0 ? round(($unitRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            $rekapUnitKerjaGolongan[] = $unitRow;
        }

        // Unassigned Unit Kerja Golongan
        $unassignedGolRows = $pangkatPegawaiUnitRaw->whereNull('unit_kerja_id')
            ->concat($pangkatPegawaiUnitRaw->whereNotNull('unit_kerja_id')->reject(fn($r) => $unitKerjaList->contains('id', $r->unit_kerja_id)));

        if ($unassignedGolRows->isNotEmpty()) {
            $unassignedRow = [
                'id' => null,
                'unit_kerja' => 'Lainnya / Belum Diatur',
                'golongan' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach (array_keys($golonganGroupKeys) as $gName) {
                $grpPangkatIds = collect($pangkatGroupMapping)
                    ->filter(fn($grp) => $grp === $gName)
                    ->keys()
                    ->map(fn($id) => (string) $id)
                    ->all();
                $grpRows = $unassignedGolRows->filter(function ($r) use ($grpPangkatIds, $gName, $pangkatGroupMapping) {
                    $pid = $r->pangkat_id !== null ? (string) $r->pangkat_id : null;
                    if ($gName === 'Lainnya / Belum Diatur') {
                        return $pid === null || in_array($pid, $grpPangkatIds, true) || !isset($pangkatGroupMapping[$r->pangkat_id]);
                    }
                    return $pid !== null && in_array($pid, $grpPangkatIds, true);
                });

                $lCount = (int) $grpRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $grpRows->where('gender_key', 'perempuan')->sum('total');
                $dosenCount = (int) $grpRows->where('kelompok_pegawai', 'dosen')->sum('total');
                $tendikCount = (int) $grpRows->where('kelompok_pegawai', 'tendik')->sum('total');
                $subtotal = $lCount + $pCount;

                $unassignedRow['golongan'][$gName] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unassignedRow['total_laki_laki'] += $lCount;
                $unassignedRow['total_perempuan'] += $pCount;
                $unassignedRow['total'] += $subtotal;

                $golonganGrandTotals[$gName]['laki_laki'] += $lCount;
                $golonganGrandTotals[$gName]['perempuan'] += $pCount;
                $golonganGrandTotals[$gName]['subtotal'] += $subtotal;

                $groupGolonganMap[$gName]['laki_laki'] += $lCount;
                $groupGolonganMap[$gName]['perempuan'] += $pCount;
                $groupGolonganMap[$gName]['dosen'] += $dosenCount;
                $groupGolonganMap[$gName]['tendik'] += $tendikCount;
                $groupGolonganMap[$gName]['total'] += $subtotal;
            }

            $unassignedRow['percentage'] = $totalPegawai > 0 ? round(($unassignedRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            if ($unassignedRow['total'] > 0) {
                $rekapUnitKerjaGolongan[] = $unassignedRow;
            }
        }

        $rekapGolonganGroup = collect($groupGolonganMap)
            ->map(function ($data) use ($totalPegawai) {
                $data['percentage'] = $totalPegawai > 0 ? round(($data['total'] / $totalPegawai) * 100, 2) : 0.0;
                return $data;
            })
            ->values()
            ->all();

        // -------------------------------------------------------------
        // 3 & 8. STATISTIK & REKAPITULASI PER TINGKAT PENDIDIKAN & UNIT KERJA
        // -------------------------------------------------------------
        $pendidikanMap = [
            's3' => ['label' => 'S-3 / Doktor', 'short' => 'S-3', 'color' => '#6f42c1'],
            's2' => ['label' => 'S-2 / Magister', 'short' => 'S-2', 'color' => '#6777ef'],
            's1' => ['label' => 'S-1 / D-IV / Sarjana', 'short' => 'S-1/D-IV', 'color' => '#3abaf4'],
            'd3' => ['label' => 'Diploma III (D-3)', 'short' => 'D-3', 'color' => '#47c363'],
            'd2_d1' => ['label' => 'Diploma I / II', 'short' => 'D-I/D-II', 'color' => '#20c997'],
            'slta' => ['label' => 'SLTA / Sederajat', 'short' => 'SLTA', 'color' => '#ffa426'],
            'sltp_sd' => ['label' => 'SLTP / SD / Lainnya', 'short' => 'SLTP/SD', 'color' => '#98a6ad'],
        ];

        $pendidikanUnitRaw = Pegawai::query()
            ->leftJoin('pendidikans', 'pendidikans.id', '=', 'pegawais.pendidikan_id')
            ->leftJoin('tingkat_pendidikans', 'tingkat_pendidikans.id', '=', 'pendidikans.tingkat_pendidikan_id')
            ->selectRaw("
                pegawais.unit_kerja_id,
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
                    WHEN tingkat_pendidikans.id IN (20, 25)
                        OR LOWER(COALESCE(tingkat_pendidikans.group_tingkat_pendidikan, '')) IN ('d-ii', 'd-i')
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%diploma i%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%diploma ii%'
                        THEN 'd2_d1'
                    WHEN tingkat_pendidikans.id IN (15, 17, 18)
                        OR LOWER(COALESCE(tingkat_pendidikans.group_tingkat_pendidikan, '')) LIKE '%slta%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%slta%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%sma%'
                        OR LOWER(COALESCE(tingkat_pendidikans.tingkat_pendidikan, '')) LIKE '%smk%'
                        THEN 'slta'
                    ELSE 'sltp_sd'
                END as pendidikan_key,
                CASE
                    WHEN pegawais.jenis_kelamin = 1 OR pegawais.jenis_kelamin = true THEN 'laki_laki'
                    ELSE 'perempuan'
                END as gender_key,
                COALESCE(pegawais.kelompok_pegawai, 'dosen') as kelompok_pegawai,
                COUNT(*) as total
            ")
            ->groupBy('pegawais.unit_kerja_id', 'pendidikan_key', 'gender_key', 'kelompok_pegawai')
            ->get();

        // Top chart data
        $rekapPendidikan = collect($pendidikanMap)
            ->map(function ($meta, $key) use ($pendidikanUnitRaw, $totalPegawai) {
                $rows = $pendidikanUnitRaw->where('pendidikan_key', $key);
                $lCount = (int) $rows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $rows->where('gender_key', 'perempuan')->sum('total');
                $dosenCount = (int) $rows->where('kelompok_pegawai', 'dosen')->sum('total');
                $tendikCount = (int) $rows->where('kelompok_pegawai', 'tendik')->sum('total');
                $subtotal = $lCount + $pCount;
                $percentage = $totalPegawai > 0 ? round(($subtotal / $totalPegawai) * 100, 2) : 0.0;

                return [
                    'key' => $key,
                    'label' => $meta['label'],
                    'short' => $meta['short'],
                    'color' => $meta['color'],
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'dosen' => $dosenCount,
                    'tendik' => $tendikCount,
                    'total' => $subtotal,
                    'percentage' => $percentage,
                ];
            })
            ->values()
            ->all();

        // Matriks Rekapitulasi Pendidikan per Unit Kerja
        $rekapUnitKerjaPendidikan = [];
        $pendidikanGrandTotals = [];
        foreach (array_keys($pendidikanMap) as $pKey) {
            $pendidikanGrandTotals[$pKey] = ['laki_laki' => 0, 'perempuan' => 0, 'subtotal' => 0];
        }

        foreach ($unitKerjaList as $uk) {
            $uRows = $pendidikanUnitRaw->where('unit_kerja_id', $uk->id);
            $unitRow = [
                'id' => $uk->id,
                'unit_kerja' => $uk->unit_kerja,
                'pendidikan' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach (array_keys($pendidikanMap) as $pKey) {
                $pRows = $uRows->where('pendidikan_key', $pKey);
                $lCount = (int) $pRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $pRows->where('gender_key', 'perempuan')->sum('total');
                $subtotal = $lCount + $pCount;

                $unitRow['pendidikan'][$pKey] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unitRow['total_laki_laki'] += $lCount;
                $unitRow['total_perempuan'] += $pCount;
                $unitRow['total'] += $subtotal;

                $pendidikanGrandTotals[$pKey]['laki_laki'] += $lCount;
                $pendidikanGrandTotals[$pKey]['perempuan'] += $pCount;
                $pendidikanGrandTotals[$pKey]['subtotal'] += $subtotal;
            }

            $unitRow['percentage'] = $totalPegawai > 0 ? round(($unitRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            $rekapUnitKerjaPendidikan[] = $unitRow;
        }

        // Unassigned Unit Kerja Pendidikan
        $unassignedPendRows = $pendidikanUnitRaw->whereNull('unit_kerja_id')
            ->concat($pendidikanUnitRaw->whereNotNull('unit_kerja_id')->reject(fn($r) => $unitKerjaList->contains('id', $r->unit_kerja_id)));

        if ($unassignedPendRows->isNotEmpty()) {
            $unassignedRow = [
                'id' => null,
                'unit_kerja' => 'Lainnya / Belum Diatur',
                'pendidikan' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach (array_keys($pendidikanMap) as $pKey) {
                $pRows = $unassignedPendRows->where('pendidikan_key', $pKey);
                $lCount = (int) $pRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $pRows->where('gender_key', 'perempuan')->sum('total');
                $subtotal = $lCount + $pCount;

                $unassignedRow['pendidikan'][$pKey] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unassignedRow['total_laki_laki'] += $lCount;
                $unassignedRow['total_perempuan'] += $pCount;
                $unassignedRow['total'] += $subtotal;

                $pendidikanGrandTotals[$pKey]['laki_laki'] += $lCount;
                $pendidikanGrandTotals[$pKey]['perempuan'] += $pCount;
                $pendidikanGrandTotals[$pKey]['subtotal'] += $subtotal;
            }

            $unassignedRow['percentage'] = $totalPegawai > 0 ? round(($unassignedRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            if ($unassignedRow['total'] > 0) {
                $rekapUnitKerjaPendidikan[] = $unassignedRow;
            }
        }

        // -------------------------------------------------------------
        // 4 & 9. STATISTIK & REKAPITULASI PER ESELON JABATAN & UNIT KERJA
        // -------------------------------------------------------------
        $eselonList = Eselon::orderBy('id', 'asc')->get();

        $eselonGroupDefinitions = [
            'Eselon I' => ['label' => 'Eselon I', 'color' => '#6777ef', 'ids' => ['10', '11', '12']],
            'Eselon II' => ['label' => 'Eselon II', 'color' => '#3abaf4', 'ids' => ['21', '22']],
            'Eselon III' => ['label' => 'Eselon III', 'color' => '#47c363', 'ids' => ['31', '32']],
            'Eselon IV' => ['label' => 'Eselon IV', 'color' => '#ffa426', 'ids' => ['41', '42']],
            'Eselon V' => ['label' => 'Eselon V', 'color' => '#e83e8c', 'ids' => ['51', '52']],
            'Non Eselon' => ['label' => 'Non Eselon', 'color' => '#98a6ad', 'ids' => ['99', '00']],
        ];

        // Mapping from eselon_id to group
        $eselonIdToGroup = [];
        foreach ($eselonList as $esl) {
            $eslId = (string) $esl->id;
            $matchedGroup = 'Non Eselon';
            foreach ($eselonGroupDefinitions as $tierName => $def) {
                if (in_array($eslId, $def['ids'], true)) {
                    $matchedGroup = $tierName;
                    break;
                }
            }
            $eselonIdToGroup[$eslId] = $matchedGroup;
        }

        $eselonPegawaiUnitRaw = Pegawai::query()
            ->selectRaw("
                unit_kerja_id,
                eselon_id,
                CASE
                    WHEN jenis_kelamin = 1 OR jenis_kelamin = true THEN 'laki_laki'
                    ELSE 'perempuan'
                END as gender_key,
                COALESCE(kelompok_pegawai, 'dosen') as kelompok_pegawai,
                COUNT(*) as total
            ")
            ->groupBy('unit_kerja_id', 'eselon_id', 'gender_key', 'kelompok_pegawai')
            ->get();

        // Top summary eselon
        $rekapEselonGroupData = [];
        foreach ($eselonGroupDefinitions as $eName => $eMeta) {
            $rekapEselonGroupData[$eName] = [
                'name' => $eName,
                'label' => $eMeta['label'],
                'color' => $eMeta['color'],
                'dosen' => 0,
                'tendik' => 0,
                'laki_laki' => 0,
                'perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];
        }

        // Matriks Rekapitulasi Eselon per Unit Kerja
        $rekapUnitKerjaEselon = [];
        $eselonGrandTotals = [];
        foreach (array_keys($eselonGroupDefinitions) as $eName) {
            $eselonGrandTotals[$eName] = ['laki_laki' => 0, 'perempuan' => 0, 'subtotal' => 0];
        }

        foreach ($unitKerjaList as $uk) {
            $uRows = $eselonPegawaiUnitRaw->where('unit_kerja_id', $uk->id);
            $unitRow = [
                'id' => $uk->id,
                'unit_kerja' => $uk->unit_kerja,
                'eselon' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach (array_keys($eselonGroupDefinitions) as $eName) {
                $eDefIds = $eselonGroupDefinitions[$eName]['ids'];
                $grpRows = $uRows->filter(function ($r) use ($eDefIds, $eName) {
                    $eid = (string) $r->eselon_id;
                    if ($eName === 'Non Eselon') {
                        return $r->eselon_id === null || in_array($eid, $eDefIds, true) || $eid === '' || $eid === '00' || $eid === '99';
                    }
                    return in_array($eid, $eDefIds, true);
                });

                $lCount = (int) $grpRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $grpRows->where('gender_key', 'perempuan')->sum('total');
                $dosenCount = (int) $grpRows->where('kelompok_pegawai', 'dosen')->sum('total');
                $tendikCount = (int) $grpRows->where('kelompok_pegawai', 'tendik')->sum('total');
                $subtotal = $lCount + $pCount;

                $unitRow['eselon'][$eName] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unitRow['total_laki_laki'] += $lCount;
                $unitRow['total_perempuan'] += $pCount;
                $unitRow['total'] += $subtotal;

                $eselonGrandTotals[$eName]['laki_laki'] += $lCount;
                $eselonGrandTotals[$eName]['perempuan'] += $pCount;
                $eselonGrandTotals[$eName]['subtotal'] += $subtotal;

                $rekapEselonGroupData[$eName]['laki_laki'] += $lCount;
                $rekapEselonGroupData[$eName]['perempuan'] += $pCount;
                $rekapEselonGroupData[$eName]['dosen'] += $dosenCount;
                $rekapEselonGroupData[$eName]['tendik'] += $tendikCount;
                $rekapEselonGroupData[$eName]['total'] += $subtotal;
            }

            $unitRow['percentage'] = $totalPegawai > 0 ? round(($unitRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            $rekapUnitKerjaEselon[] = $unitRow;
        }

        // Unassigned Unit Kerja Eselon
        $unassignedEslRows = $eselonPegawaiUnitRaw->whereNull('unit_kerja_id')
            ->concat($eselonPegawaiUnitRaw->whereNotNull('unit_kerja_id')->reject(fn($r) => $unitKerjaList->contains('id', $r->unit_kerja_id)));

        if ($unassignedEslRows->isNotEmpty()) {
            $unassignedRow = [
                'id' => null,
                'unit_kerja' => 'Lainnya / Belum Diatur',
                'eselon' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach (array_keys($eselonGroupDefinitions) as $eName) {
                $eDefIds = $eselonGroupDefinitions[$eName]['ids'];
                $grpRows = $unassignedEslRows->filter(function ($r) use ($eDefIds, $eName) {
                    $eid = (string) $r->eselon_id;
                    if ($eName === 'Non Eselon') {
                        return $r->eselon_id === null || in_array($eid, $eDefIds, true) || $eid === '' || $eid === '00' || $eid === '99';
                    }
                    return in_array($eid, $eDefIds, true);
                });

                $lCount = (int) $grpRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $grpRows->where('gender_key', 'perempuan')->sum('total');
                $dosenCount = (int) $grpRows->where('kelompok_pegawai', 'dosen')->sum('total');
                $tendikCount = (int) $grpRows->where('kelompok_pegawai', 'tendik')->sum('total');
                $subtotal = $lCount + $pCount;

                $unassignedRow['eselon'][$eName] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unassignedRow['total_laki_laki'] += $lCount;
                $unassignedRow['total_perempuan'] += $pCount;
                $unassignedRow['total'] += $subtotal;

                $eselonGrandTotals[$eName]['laki_laki'] += $lCount;
                $eselonGrandTotals[$eName]['perempuan'] += $pCount;
                $eselonGrandTotals[$eName]['subtotal'] += $subtotal;

                $rekapEselonGroupData[$eName]['laki_laki'] += $lCount;
                $rekapEselonGroupData[$eName]['perempuan'] += $pCount;
                $rekapEselonGroupData[$eName]['dosen'] += $dosenCount;
                $rekapEselonGroupData[$eName]['tendik'] += $tendikCount;
                $rekapEselonGroupData[$eName]['total'] += $subtotal;
            }

            $unassignedRow['percentage'] = $totalPegawai > 0 ? round(($unassignedRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            if ($unassignedRow['total'] > 0) {
                $rekapUnitKerjaEselon[] = $unassignedRow;
            }
        }

        $rekapEselonGroup = collect($rekapEselonGroupData)
            ->map(function ($data) use ($totalPegawai) {
                $data['percentage'] = $totalPegawai > 0 ? round(($data['total'] / $totalPegawai) * 100, 2) : 0.0;
                return $data;
            })
            ->values()
            ->all();

        // -------------------------------------------------------------
        // 5 & 10. STATISTIK & REKAPITULASI PER JENIS JABATAN & UNIT KERJA
        // -------------------------------------------------------------
        $jenisJabatanList = JenisJabatan::orderBy('id', 'asc')->get();

        $jenisJabatanColors = [
            1 => '#6777ef',
            2 => '#3abaf4',
            3 => '#ffa426',
            4 => '#47c363',
            5 => '#e83e8c',
            6 => '#20c997',
        ];

        $jenisJabatanUnitRaw = Pegawai::query()
            ->selectRaw("
                unit_kerja_id,
                jenis_jabatan_id,
                CASE
                    WHEN jenis_kelamin = 1 OR jenis_kelamin = true THEN 'laki_laki'
                    ELSE 'perempuan'
                END as gender_key,
                COALESCE(kelompok_pegawai, 'dosen') as kelompok_pegawai,
                COUNT(*) as total
            ")
            ->groupBy('unit_kerja_id', 'jenis_jabatan_id', 'gender_key', 'kelompok_pegawai')
            ->get();

        // Top summary jenis jabatan
        $rekapJenisJabatan = [];
        foreach ($jenisJabatanList as $jj) {
            $rows = $jenisJabatanUnitRaw->where('jenis_jabatan_id', $jj->id);
            $lCount = (int) $rows->where('gender_key', 'laki_laki')->sum('total');
            $pCount = (int) $rows->where('gender_key', 'perempuan')->sum('total');
            $dosenCount = (int) $rows->where('kelompok_pegawai', 'dosen')->sum('total');
            $tendikCount = (int) $rows->where('kelompok_pegawai', 'tendik')->sum('total');
            $subtotal = $lCount + $pCount;
            $percentage = $totalPegawai > 0 ? round(($subtotal / $totalPegawai) * 100, 2) : 0.0;

            $rekapJenisJabatan[] = [
                'id' => $jj->id,
                'label' => $jj->jenis_jabatan,
                'color' => $jenisJabatanColors[$jj->id] ?? '#6c757d',
                'laki_laki' => $lCount,
                'perempuan' => $pCount,
                'dosen' => $dosenCount,
                'tendik' => $tendikCount,
                'total' => $subtotal,
                'percentage' => $percentage,
            ];
        }

        // Check if there are unassigned jenis jabatan
        $nullJjRows = $jenisJabatanUnitRaw->whereNull('jenis_jabatan_id');
        $unmatchedJjRows = $jenisJabatanUnitRaw->whereNotNull('jenis_jabatan_id')
            ->reject(fn($r) => $jenisJabatanList->contains('id', $r->jenis_jabatan_id));
        $allNullJj = $nullJjRows->concat($unmatchedJjRows);

        if ($allNullJj->isNotEmpty()) {
            $lCount = (int) $allNullJj->where('gender_key', 'laki_laki')->sum('total');
            $pCount = (int) $allNullJj->where('gender_key', 'perempuan')->sum('total');
            $dosenCount = (int) $allNullJj->where('kelompok_pegawai', 'dosen')->sum('total');
            $tendikCount = (int) $allNullJj->where('kelompok_pegawai', 'tendik')->sum('total');
            $subtotal = $lCount + $pCount;

            if ($subtotal > 0) {
                $percentage = $totalPegawai > 0 ? round(($subtotal / $totalPegawai) * 100, 2) : 0.0;
                $rekapJenisJabatan[] = [
                    'id' => null,
                    'label' => 'Belum Ditentukan / Lainnya',
                    'color' => '#98a6ad',
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'dosen' => $dosenCount,
                    'tendik' => $tendikCount,
                    'total' => $subtotal,
                    'percentage' => $percentage,
                ];
            }
        }

        // Matriks Rekapitulasi Jenis Jabatan per Unit Kerja
        $rekapUnitKerjaJenisJabatan = [];
        $jenisJabatanGrandTotals = [];
        foreach ($rekapJenisJabatan as $jjItem) {
            $jenisJabatanGrandTotals[$jjItem['label']] = ['laki_laki' => 0, 'perempuan' => 0, 'subtotal' => 0];
        }

        foreach ($unitKerjaList as $uk) {
            $uRows = $jenisJabatanUnitRaw->where('unit_kerja_id', $uk->id);
            $unitRow = [
                'id' => $uk->id,
                'unit_kerja' => $uk->unit_kerja,
                'jenis_jabatan' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach ($rekapJenisJabatan as $jjItem) {
                $jKey = $jjItem['label'];
                $jId = $jjItem['id'];

                if ($jId !== null) {
                    $jRows = $uRows->where('jenis_jabatan_id', $jId);
                } else {
                    $jRows = $uRows->filter(fn($r) => $r->jenis_jabatan_id === null || !$jenisJabatanList->contains('id', $r->jenis_jabatan_id));
                }

                $lCount = (int) $jRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $jRows->where('gender_key', 'perempuan')->sum('total');
                $subtotal = $lCount + $pCount;

                $unitRow['jenis_jabatan'][$jKey] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unitRow['total_laki_laki'] += $lCount;
                $unitRow['total_perempuan'] += $pCount;
                $unitRow['total'] += $subtotal;

                $jenisJabatanGrandTotals[$jKey]['laki_laki'] += $lCount;
                $jenisJabatanGrandTotals[$jKey]['perempuan'] += $pCount;
                $jenisJabatanGrandTotals[$jKey]['subtotal'] += $subtotal;
            }

            $unitRow['percentage'] = $totalPegawai > 0 ? round(($unitRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            $rekapUnitKerjaJenisJabatan[] = $unitRow;
        }

        // Unassigned Unit Kerja Jenis Jabatan
        $unassignedJjRows = $jenisJabatanUnitRaw->whereNull('unit_kerja_id')
            ->concat($jenisJabatanUnitRaw->whereNotNull('unit_kerja_id')->reject(fn($r) => $unitKerjaList->contains('id', $r->unit_kerja_id)));

        if ($unassignedJjRows->isNotEmpty()) {
            $unassignedRow = [
                'id' => null,
                'unit_kerja' => 'Lainnya / Belum Diatur',
                'jenis_jabatan' => [],
                'total_laki_laki' => 0,
                'total_perempuan' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];

            foreach ($rekapJenisJabatan as $jjItem) {
                $jKey = $jjItem['label'];
                $jId = $jjItem['id'];

                if ($jId !== null) {
                    $jRows = $unassignedJjRows->where('jenis_jabatan_id', $jId);
                } else {
                    $jRows = $unassignedJjRows->filter(fn($r) => $r->jenis_jabatan_id === null || !$jenisJabatanList->contains('id', $r->jenis_jabatan_id));
                }

                $lCount = (int) $jRows->where('gender_key', 'laki_laki')->sum('total');
                $pCount = (int) $jRows->where('gender_key', 'perempuan')->sum('total');
                $subtotal = $lCount + $pCount;

                $unassignedRow['jenis_jabatan'][$jKey] = [
                    'laki_laki' => $lCount,
                    'perempuan' => $pCount,
                    'subtotal' => $subtotal,
                ];

                $unassignedRow['total_laki_laki'] += $lCount;
                $unassignedRow['total_perempuan'] += $pCount;
                $unassignedRow['total'] += $subtotal;

                $jenisJabatanGrandTotals[$jKey]['laki_laki'] += $lCount;
                $jenisJabatanGrandTotals[$jKey]['perempuan'] += $pCount;
                $jenisJabatanGrandTotals[$jKey]['subtotal'] += $subtotal;
            }

            $unassignedRow['percentage'] = $totalPegawai > 0 ? round(($unassignedRow['total'] / $totalPegawai) * 100, 2) : 0.0;
            if ($unassignedRow['total'] > 0) {
                $rekapUnitKerjaJenisJabatan[] = $unassignedRow;
            }
        }

        // -------------------------------------------------------------
        // 11. REKAPITULASI CROSS-TABULATION ESELON JABATAN X JENIS KELAMIN
        // -------------------------------------------------------------
        $rekapEselonGenderMatrix = [];

        foreach ($rekapEselonGroup as $group) {
            $tierName = $group['name'];
            $lCount = $group['laki_laki'];
            $pCount = $group['perempuan'];
            $dosenCount = $group['dosen'];
            $tendikCount = $group['tendik'];
            $subtotal = $group['total'];

            $lPct = $totalLakiLaki > 0 ? round(($lCount / $totalLakiLaki) * 100, 2) : 0.0;
            $pPct = $totalPerempuan > 0 ? round(($pCount / $totalPerempuan) * 100, 2) : 0.0;
            $totalPct = $totalPegawai > 0 ? round(($subtotal / $totalPegawai) * 100, 2) : 0.0;
            $rowRatioL = $subtotal > 0 ? round(($lCount / $subtotal) * 100, 1) : 0.0;
            $rowRatioP = $subtotal > 0 ? round(($pCount / $subtotal) * 100, 1) : 0.0;

            $rekapEselonGenderMatrix[] = [
                'eselon_group' => $tierName,
                'color' => $group['color'],
                'laki_laki' => $lCount,
                'laki_laki_pct' => $lPct,
                'perempuan' => $pCount,
                'perempuan_pct' => $pPct,
                'row_ratio_l' => $rowRatioL,
                'row_ratio_p' => $rowRatioP,
                'dosen' => $dosenCount,
                'tendik' => $tendikCount,
                'total' => $subtotal,
                'total_pct' => $totalPct,
            ];
        }

        return view('dashboard.statistik', [
            'title' => 'Statistik Pegawai',
            'totalPegawai' => $totalPegawai,
            'totalDosen' => $totalDosen,
            'totalTendik' => $totalTendik,
            'totalLakiLaki' => $totalLakiLaki,
            'totalPerempuan' => $totalPerempuan,
            // 1 & 6. Jenis Kelamin
            'rekapGender' => $rekapGender,
            'statusPegawaiOptions' => $statusPegawaiOptions,
            'rekapUnitKerjaGender' => $rekapUnitKerjaGender,
            'statusGrandTotals' => $statusGrandTotals,
            // 2 & 7. Golongan
            'rekapGolonganGroup' => $rekapGolonganGroup,
            'golonganGroupKeys' => $golonganGroupKeys,
            'rekapUnitKerjaGolongan' => $rekapUnitKerjaGolongan,
            'golonganGrandTotals' => $golonganGrandTotals,
            // 3 & 8. Tingkat Pendidikan
            'rekapPendidikan' => $rekapPendidikan,
            'pendidikanMap' => $pendidikanMap,
            'rekapUnitKerjaPendidikan' => $rekapUnitKerjaPendidikan,
            'pendidikanGrandTotals' => $pendidikanGrandTotals,
            // 4 & 9. Eselon Jabatan
            'rekapEselonGroup' => $rekapEselonGroup,
            'eselonGroupDefinitions' => $eselonGroupDefinitions,
            'rekapUnitKerjaEselon' => $rekapUnitKerjaEselon,
            'eselonGrandTotals' => $eselonGrandTotals,
            // 5 & 10. Jenis Jabatan
            'rekapJenisJabatan' => $rekapJenisJabatan,
            'rekapUnitKerjaJenisJabatan' => $rekapUnitKerjaJenisJabatan,
            'jenisJabatanGrandTotals' => $jenisJabatanGrandTotals,
            // 11. Eselon x Jenis Kelamin Matrix
            'rekapEselonGenderMatrix' => $rekapEselonGenderMatrix,
        ]);
    }
}
