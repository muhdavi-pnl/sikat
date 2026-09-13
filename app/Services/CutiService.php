<?php

namespace App\Services;

use App\Models\CutiLayananPegawai;
use App\Models\LayananPegawai;
use App\Models\PejabatCutiSetting;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;

class CutiService
{
    /** Default cuti days allocated per year */
    public const HARI_PER_TAHUN = 12;

    /** Maximum carry-over days allowed from a previous year */
    public const MAX_CARRY_OVER = 6;

    /** Number of years to consider (current + previous N-1) */
    public const TAHUN_DIPERHITUNGKAN = 3;

    public const JENIS_CUTI_OPTIONS = [
        'tahunan' => 'Cuti Tahunan',
        'besar' => 'Cuti Besar',
        'sakit' => 'Cuti Sakit',
        'melahirkan' => 'Cuti Melahirkan',
        'alasan_penting' => 'Cuti Karena Alasan Penting',
        'di_luar_tanggungan_negara' => 'Cuti di Luar Tanggungan Negara',
    ];

    /**
     * Get the total jatah cuti available for a pegawai.
     *
     * Saldo dihitung dinamis dari bucket tahunan dengan urutan pemakaian:
     *  - saldo tahun paling lama yang masih aktif dipakai terlebih dahulu,
     *  - lalu tahun sebelumnya,
     *  - terakhir saldo tahun berjalan.
     */
    public function getSaldoCuti(Pegawai $pegawai, ?int $currentYear = null, int $additionalRequestedDays = 0): int
    {
        return $this->getCutiBreakdown($pegawai, $currentYear, $additionalRequestedDays)['total_saldo'];
    }

    public function calculateHariKerja($tanggalMulai, $tanggalSelesai): int
    {
        $mulai = $this->normalizeDate($tanggalMulai);
        $selesai = $this->normalizeDate($tanggalSelesai);

        if (!$mulai || !$selesai || $mulai->gt($selesai)) {
            return 0;
        }

        $hariKerja = 0;
        $cursor = $mulai->copy();

        while ($cursor->lte($selesai)) {
            if ($cursor->isWeekday() && !$this->isExcludedDate($cursor)) {
                $hariKerja++;
            }

            $cursor->addDay();
        }

        return $hariKerja;
    }

    public function getExcludedDateRules(): array
    {
        $exactDates = [];
        $recurringDates = [];

        foreach ((array) config('cuti.excluded_dates', []) as $date) {
            $date = trim((string) $date);

            if ($date === '') {
                continue;
            }

            if (preg_match('/^\d{2}-\d{2}$/', $date)) {
                $recurringDates[$date] = true;
                continue;
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $exactDates[$date] = true;
                continue;
            }

            $normalized = $this->normalizeDate($date);
            if ($normalized) {
                $exactDates[$normalized->format('Y-m-d')] = true;
            }
        }

        return [
            'exact_dates' => array_keys($exactDates),
            'recurring_dates' => array_keys($recurringDates),
        ];
    }

    /**
     * Get detailed cuti breakdown per year for display purposes.
     *
     * Returns:
     * [
     *   'years' => [
     *     [
     *       'tahun'             => 2026,
     *       'hari_tersedia'     => 12,
     *       'hari_diambil'      => 0,
     *       'sisa'              => 12,
     *       'kontribusi_saldo'  => 12,
     *       'is_current_year'   => true,
     *     ],
     *     ...
     *   ],
     *   'total_saldo' => 15,
     * ]
     */
    public static function jenisCutiOptions(): array
    {
        return self::JENIS_CUTI_OPTIONS;
    }

    public static function normalizeJenisCuti(?string $value): ?string
    {
        $normalized = mb_strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        return array_key_exists($normalized, self::JENIS_CUTI_OPTIONS) ? $normalized : null;
    }

    public static function jenisCutiLabel(?string $value, string $default = '-'): string
    {
        $normalized = self::normalizeJenisCuti($value);

        return $normalized ? self::JENIS_CUTI_OPTIONS[$normalized] : $default;
    }

    public function getCutiBreakdown(Pegawai $pegawai, ?int $currentYear = null, int $additionalRequestedDays = 0): array
    {
        $currentYear = $currentYear ?: now()->year;

        $simulation = $this->simulateSaldoBuckets($pegawai, $currentYear);
        $remaining = $simulation['remaining'];
        $consumedInCurrentYear = $simulation['consumed_in_current_year'];

        if ($additionalRequestedDays > 0) {
            $distribution = $this->consumeFromBuckets($remaining, $additionalRequestedDays);

            foreach ($distribution as $originYear => $deducted) {
                $consumedInCurrentYear[$originYear] = (int) ($consumedInCurrentYear[$originYear] ?? 0) + $deducted;
            }
        }

        $yearsData = [];
        $totalSaldo = 0;

        for ($i = 0; $i < self::TAHUN_DIPERHITUNGKAN; $i++) {
            $tahun = $currentYear - $i;
            $hariTersedia = (int) ($simulation['available_at_start_of_year'][$tahun] ?? 0);
            $hariDiambil = (int) ($consumedInCurrentYear[$tahun] ?? 0);
            $sisa = (int) ($remaining[$tahun] ?? 0);

            $yearsData[] = [
                'tahun' => $tahun,
                'hari_tersedia' => $hariTersedia,
                'hari_diambil' => $hariDiambil,
                'sisa' => $sisa,
                'kontribusi_saldo' => $sisa,
                'is_current_year' => ($i === 0),
            ];

            $totalSaldo += $sisa;
        }

        return [
            'years'        => $yearsData,
            'total_saldo'  => $totalSaldo,
        ];
    }

    public function buildPrintableFormData(LayananPegawai $layananPegawai): array
    {
        $pegawai = $layananPegawai->pegawai;
        $cutiDetail = $layananPegawai->resolvedCutiDetail();
        $referenceDate = $cutiDetail?->tanggal_mulai ?? $layananPegawai->created_at ?? now();
        $referenceYear = (int) $referenceDate->format('Y');
        $projectedRequestDays = $layananPegawai->status === LayananPegawai::STATUS_SELESAI
            ? 0
            : max(0, (int) optional($cutiDetail)->hari_diminta);
        $breakdown = $pegawai instanceof Pegawai
            ? $this->getCutiBreakdown($pegawai, $referenceYear, $projectedRequestDays)
            : ['years' => [], 'total_saldo' => 0];

        $atasanLangsung = $cutiDetail?->atasanPegawai ?: ($pegawai instanceof Pegawai ? $this->resolveAtasanLangsung($pegawai) : null);
        $pybmcSetting = PejabatCutiSetting::getActivePybmc();
        $pybmcPegawai = $cutiDetail?->pybmcPegawai ?: $pybmcSetting?->pegawai;
        $pybmcCustomJabatan = $pybmcSetting?->jabatan_label;

        $processor = $layananPegawai->outputUploader ?: $layananPegawai->processor;
        $processorPegawai = optional($processor)->pegawai;
        $approval = $this->resolveApprovalSelectionsFromDetail($layananPegawai, $cutiDetail);

        return [
            'approval' => $approval,
            'atasan_approved_at' => $cutiDetail?->atasan_approved_at,
            'atasan_langsung' => $atasanLangsung,
            'catatan_atasan' => $cutiDetail?->catatan_atasan,
            'catatan_cuti' => $breakdown['years'],
            'catatan_pybmc' => $cutiDetail?->catatan_pybmc,
            'cuti' => $cutiDetail,
            'jenis_cuti_label' => self::jenisCutiLabel(optional($cutiDetail)->jenis_cuti),
            'masa_kerja' => $pegawai instanceof Pegawai ? $this->formatMasaKerja($pegawai, $referenceDate) : '-',
            'pegawai' => $pegawai,
            'processor' => $processor,
            'processor_pegawai' => $processorPegawai,
            'pybmc_approved_at' => $cutiDetail?->pybmc_approved_at,
            'pybmc_custom_jabatan' => $pybmcCustomJabatan,
            'pybmc_pegawai' => $pybmcPegawai,
            'sisa_cuti_total' => $breakdown['total_saldo'],
        ];
    }


    /**
     * Simulate saldo buckets year by year so leave usage always consumes
     * the oldest active bucket first.
     */
    protected function simulateSaldoBuckets(Pegawai $pegawai, int $currentYear): array
    {
        $requests = LayananPegawai::query()
            ->with('cutiDetail')
            ->whereHas('cutiDetail')
            ->where('pegawai_id', $pegawai->id)
            ->where('status', LayananPegawai::STATUS_SELESAI)
            ->orderByRaw('COALESCE(processed_at, created_at) asc')
            ->orderBy('id')
            ->get(['id', 'created_at', 'processed_at']);

        $earliestRequestYear = $requests
            ->map(function (LayananPegawai $request) {
                return $this->resolveRequestYear($request);
            })
            ->filter(function ($year) {
                return is_int($year) && $year > 0;
            })
            ->min();

        $startYear = min(
            $currentYear - (self::TAHUN_DIPERHITUNGKAN + 1),
            $earliestRequestYear ? ($earliestRequestYear - 1) : ($currentYear - (self::TAHUN_DIPERHITUNGKAN + 1))
        );

        $requestsByYear = $requests->groupBy(function (LayananPegawai $request) {
            return $this->resolveRequestYear($request);
        });

        $buckets = [];
        $availableAtStartOfCurrentYear = [];
        $consumedInCurrentYear = [];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            foreach (array_keys($buckets) as $originYear) {
                if ($originYear < ($year - (self::TAHUN_DIPERHITUNGKAN - 1))) {
                    unset($buckets[$originYear]);
                    continue;
                }

                if ($originYear < $year) {
                    $buckets[$originYear] = min($buckets[$originYear], self::MAX_CARRY_OVER);
                }
            }

            $buckets[$year] = $buckets[$year] ?? self::HARI_PER_TAHUN;

            if ($year === $currentYear) {
                for ($i = 0; $i < self::TAHUN_DIPERHITUNGKAN; $i++) {
                    $originYear = $currentYear - $i;
                    $availableAtStartOfCurrentYear[$originYear] = (int) ($buckets[$originYear] ?? 0);
                    $consumedInCurrentYear[$originYear] = 0;
                }
            }

            foreach ($requestsByYear->get($year, collect()) as $request) {
                $remainingToConsume = max(0, (int) optional($request->resolvedCutiDetail())->hari_diminta);

                if ($remainingToConsume === 0) {
                    continue;
                }

                ksort($buckets);

                foreach (array_keys($buckets) as $originYear) {
                    if ($remainingToConsume === 0) {
                        break;
                    }

                    $available = (int) ($buckets[$originYear] ?? 0);
                    if ($available <= 0) {
                        continue;
                    }

                    $deducted = min($available, $remainingToConsume);
                    $buckets[$originYear] -= $deducted;
                    $remainingToConsume -= $deducted;

                    if ($year === $currentYear && array_key_exists($originYear, $consumedInCurrentYear)) {
                        $consumedInCurrentYear[$originYear] += $deducted;
                    }
                }
            }
        }

        $remaining = [];
        for ($i = 0; $i < self::TAHUN_DIPERHITUNGKAN; $i++) {
            $originYear = $currentYear - $i;
            $remaining[$originYear] = (int) ($buckets[$originYear] ?? 0);
            $availableAtStartOfCurrentYear[$originYear] = (int) ($availableAtStartOfCurrentYear[$originYear] ?? 0);
            $consumedInCurrentYear[$originYear] = (int) ($consumedInCurrentYear[$originYear] ?? 0);
        }

        return [
            'available_at_start_of_year' => $availableAtStartOfCurrentYear,
            'consumed_in_current_year' => $consumedInCurrentYear,
            'remaining' => $remaining,
        ];
    }

    protected function consumeFromBuckets(array &$buckets, int $days): array
    {
        $distribution = [];
        $remainingToConsume = max(0, $days);

        ksort($buckets);

        foreach (array_keys($buckets) as $originYear) {
            if ($remainingToConsume === 0) {
                break;
            }

            $available = (int) ($buckets[$originYear] ?? 0);
            if ($available <= 0) {
                continue;
            }

            $deducted = min($available, $remainingToConsume);
            $buckets[$originYear] = $available - $deducted;
            $distribution[$originYear] = $deducted;
            $remainingToConsume -= $deducted;
        }

        return $distribution;
    }

    protected function resolveRequestYear(LayananPegawai $request): int
    {
        return (int) optional($request->processed_at ?? $request->created_at)->format('Y');
    }

    public function resolveAtasanLangsung(Pegawai $pegawai): ?Pegawai
    {
        $jabatan = $pegawai->jabatan;
        $atasanLangsungId = $jabatan?->atasan_langsung_id;

        if (!$atasanLangsungId) {
            return null;
        }

        return Pegawai::query()
            ->where('jabatan_id', $atasanLangsungId)
            ->when($pegawai->unit_kerja_id, function ($query) use ($pegawai) {
                $query->where('unit_kerja_id', $pegawai->unit_kerja_id);
            })
            ->orderBy('nama')
            ->first();
    }

    public function getSubordinatePegawaiIds(Pegawai $supervisor): array
    {
        if (!$supervisor->jabatan_id) {
            return [];
        }

        return Pegawai::query()
            ->whereHas('jabatan', function ($query) use ($supervisor) {
                $query->where('atasan_langsung_id', $supervisor->jabatan_id);
            })
            ->when($supervisor->unit_kerja_id, function ($query) use ($supervisor) {
                $query->where('unit_kerja_id', $supervisor->unit_kerja_id);
            })
            ->pluck('id')
            ->all();
    }

    public function isSupervisorOf(Pegawai $supervisor, Pegawai $subordinate): bool
    {
        $subordinateIds = $this->getSubordinatePegawaiIds($supervisor);

        return in_array((int) $subordinate->id, array_map('intval', $subordinateIds), true);
    }

    public function resolveDesignatedPybmc(): ?Pegawai
    {
        $setting = PejabatCutiSetting::getActivePybmc();

        return $setting?->pegawai;
    }

    public function isUserDesignatedPybmc(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $pybmcPegawai = $this->resolveDesignatedPybmc();
        if ($pybmcPegawai) {
            $userPegawai = $user->pegawai;
            if ($userPegawai && (int) $userPegawai->id === (int) $pybmcPegawai->id) {
                return true;
            }

            if ($pybmcPegawai->user_id && (int) $pybmcPegawai->user_id === (int) $user->id) {
                return true;
            }
        }

        return false;
    }


    public function resolveApprovalSelectionsFromDetail(LayananPegawai $layananPegawai, ?CutiLayananPegawai $cutiDetail): array
    {
        $atasanSelection = $cutiDetail?->atasan_status;
        $pejabatSelection = $cutiDetail?->pybmc_status;

        if (!$atasanSelection) {
            if ($layananPegawai->status === LayananPegawai::STATUS_SELESAI) {
                $atasanSelection = 'disetujui';
            } elseif ($layananPegawai->status === LayananPegawai::STATUS_DITOLAK) {
                $atasanSelection = 'tidak_disetujui';
            }
        }

        if (!$pejabatSelection) {
            if ($layananPegawai->status === LayananPegawai::STATUS_SELESAI) {
                $pejabatSelection = 'disetujui';
            } elseif ($layananPegawai->status === LayananPegawai::STATUS_DITOLAK) {
                $pejabatSelection = 'tidak_disetujui';
            }
        }

        return [
            'atasan' => $atasanSelection,
            'pejabat' => $pejabatSelection,
        ];
    }

    protected function resolveApprovalSelections(?string $status): array
    {
        $selection = null;

        if ($status === LayananPegawai::STATUS_SELESAI) {
            $selection = 'disetujui';
        } elseif ($status === LayananPegawai::STATUS_DITOLAK) {
            $selection = 'tidak_disetujui';
        }

        return [
            'atasan' => $selection,
            'pejabat' => $selection,
        ];
    }

    protected function formatMasaKerja(Pegawai $pegawai, Carbon $referenceDate): string
    {
        $tanggalMulai = $pegawai->tmt_cpns ?? $pegawai->tmt_pns ?? $pegawai->tmt_jabatan;

        if (!$tanggalMulai) {
            return '-';
        }

        $mulai = $this->normalizeDate($tanggalMulai);

        if (!$mulai || $mulai->gt($referenceDate)) {
            return '-';
        }

        $selisih = $mulai->diff($referenceDate);

        return sprintf('%d tahun %d bulan', $selisih->y, $selisih->m);
    }



    protected function normalizeDate($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->startOfDay();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->startOfDay();
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    protected function isExcludedDate(Carbon $date): bool
    {
        $rules = $this->getExcludedDateRules();

        return in_array($date->format('Y-m-d'), $rules['exact_dates'], true)
            || in_array($date->format('m-d'), $rules['recurring_dates'], true);
    }
}
