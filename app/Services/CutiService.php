<?php

namespace App\Services;

use App\Models\CutiLayananPegawai;
use App\Models\LayananPegawai;
use App\Models\PejabatCutiSetting;
use App\Models\Pegawai;
use App\Models\PegawaiCutiQuota;
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

    public const JENIS_TAHUNAN = 'tahunan';
    public const JENIS_BESAR = 'besar';
    public const JENIS_SAKIT = 'sakit';
    public const JENIS_MELAHIRKAN = 'melahirkan';
    public const JENIS_ALASAN_PENTING = 'alasan_penting';
    public const JENIS_CLTN = 'di_luar_tanggungan_negara';

    public const JENIS_CUTI_OPTIONS = [
        self::JENIS_TAHUNAN => 'Cuti Tahunan',
        self::JENIS_BESAR => 'Cuti Besar',
        self::JENIS_SAKIT => 'Cuti Sakit',
        self::JENIS_MELAHIRKAN => 'Cuti Melahirkan',
        self::JENIS_ALASAN_PENTING => 'Cuti Karena Alasan Penting',
        self::JENIS_CLTN => 'Cuti di Luar Tanggungan Negara',
    ];

    public const JENIS_CUTI_PPPK_OPTIONS = [
        self::JENIS_TAHUNAN => 'Cuti Tahunan',
        self::JENIS_SAKIT => 'Cuti Sakit',
        self::JENIS_MELAHIRKAN => 'Cuti Melahirkan',
    ];

    public static function getAvailableJenisCutiFor(?Pegawai $pegawai): array
    {
        return array_keys(static::jenisCutiOptions($pegawai));
    }

    /**
     * Get the total jatah cuti available for a pegawai.
     *
     * Saldo dihitung dinamis dari bucket tahunan dengan urutan pemakaian:
     *  - saldo tahun paling lama yang masih aktif dipakai terlebih dahulu,
     *  - lalu tahun sebelumnya,
     *  - terakhir saldo tahun berjalan (ditambah kompensasi cuti bersama jika ada).
     */
    public function getSaldoCuti(Pegawai $pegawai, ?int $currentYear = null, int $additionalRequestedDays = 0, ?string $alasanPppkBypass = null): int
    {
        return $this->getCutiBreakdown($pegawai, $currentYear, $additionalRequestedDays, $alasanPppkBypass)['total_saldo'];
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

    public function calculateDurasiHari($tanggalMulai, $tanggalSelesai, ?string $jenisCuti = 'tahunan'): int
    {
        $mulai = $this->normalizeDate($tanggalMulai);
        $selesai = $this->normalizeDate($tanggalSelesai);

        if (!$mulai || !$selesai || $mulai->gt($selesai)) {
            return 0;
        }

        $jenis = self::normalizeJenisCuti($jenisCuti);

        if ($jenis === 'tahunan') {
            return $this->calculateHariKerja($mulai, $selesai);
        }

        return (int) $mulai->diffInDays($selesai) + 1;
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

    public static function jenisCutiOptions(?Pegawai $pegawai = null): array
    {
        if ($pegawai && $pegawai->isPppk()) {
            return self::JENIS_CUTI_PPPK_OPTIONS;
        }

        return self::JENIS_CUTI_OPTIONS;
    }

    public static function isJenisCutiAllowedForPegawai(?Pegawai $pegawai, ?string $jenisCuti): bool
    {
        $normalized = self::normalizeJenisCuti($jenisCuti);
        if (!$normalized) {
            return false;
        }

        if ($pegawai && $pegawai->isPppk()) {
            return array_key_exists($normalized, self::JENIS_CUTI_PPPK_OPTIONS);
        }

        return array_key_exists($normalized, self::JENIS_CUTI_OPTIONS);
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

    public static function resolveJenisCutiFromLayanan($layanan): string
    {
        if (!$layanan) {
            return 'tahunan';
        }

        $nama = mb_strtolower(is_string($layanan) ? $layanan : (string) ($layanan->layanan ?? ''));

        if (str_contains($nama, 'besar')) {
            return 'besar';
        }
        if (str_contains($nama, 'sakit')) {
            return 'sakit';
        }
        if (str_contains($nama, 'melahirkan')) {
            return 'melahirkan';
        }
        if (str_contains($nama, 'alasan penting') || str_contains($nama, 'alasan_penting') || str_contains($nama, 'cap')) {
            return 'alasan_penting';
        }
        if (str_contains($nama, 'tanggungan') || str_contains($nama, 'cltn')) {
            return 'di_luar_tanggungan_negara';
        }

        return 'tahunan';
    }

    public function getCutiBreakdown(Pegawai $pegawai, ?int $currentYear = null, int $additionalRequestedDays = 0, ?string $alasanPppkBypass = null): array
    {
        $currentYear = $currentYear ?: now()->year;

        $simulation = $this->simulateSaldoBuckets($pegawai, $currentYear, $alasanPppkBypass);
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

    /**
     * Check regulatory eligibility and duration constraints for ASN leave proposals.
     */
    public function checkCutiEligibility(Pegawai $pegawai, string $jenisCuti, array $context = []): array
    {
        $jenis = self::normalizeJenisCuti($jenisCuti);
        if (!$jenis) {
            return [
                'eligible' => false,
                'reason' => 'Jenis cuti tidak valid.',
            ];
        }

        // 1. Role / Status Segmentation Check
        if ($pegawai->isPppk() && !in_array($jenis, ['tahunan', 'sakit', 'melahirkan'], true)) {
            return [
                'eligible' => false,
                'reason' => 'Jenis cuti ' . self::jenisCutiLabel($jenis) . ' hanya diperuntukkan bagi Pegawai Negeri Sipil (PNS) sesuai PP No. 49/2018 dan Peraturan BKN No. 7/2022.',
            ];
        }

        $tanggalMulai = $this->normalizeDate($context['tanggal_mulai'] ?? $context['cuti_tanggal_mulai'] ?? null);
        $tanggalSelesai = $this->normalizeDate($context['tanggal_selesai'] ?? $context['cuti_tanggal_selesai'] ?? null);
        $referenceDate = $tanggalMulai ?: now();
        $masaKerjaBulan = $pegawai->getMasaKerjaBulan($referenceDate);
        $hasTmt = (bool) $pegawai->getTanggalMulaiKerja();

        $durasiHari = 0;
        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai->lte($tanggalSelesai)) {
            $durasiHari = $this->calculateDurasiHari($tanggalMulai, $tanggalSelesai, $jenis);
        }

        switch ($jenis) {
            case 'tahunan':
                if ($pegawai->isPppk()) {
                    if ($hasTmt && $masaKerjaBulan < 12) {
                        $bypass = trim((string) ($context['alasan_pppk_bypass'] ?? ''));
                        $validBypass = in_array($bypass, [
                            CutiLayananPegawai::ALASAN_PPPK_PERKAWINAN_PERTAMA,
                            CutiLayananPegawai::ALASAN_PPPK_KELUARGA_INTI_SAKIT_KERAS_MENINGGAL,
                        ], true);

                        if (!$validBypass) {
                            return [
                                'eligible' => false,
                                'reason' => 'PPPK dengan masa kerja kurang dari 1 tahun belum berhak atas Cuti Tahunan, kecuali untuk alasan perkawinan pertama atau keluarga inti sakit keras/meninggal (maksimal 6 hari kerja).',
                            ];
                        }

                        if ($durasiHari > 6) {
                            return [
                                'eligible' => false,
                                'reason' => 'Pengajuan Cuti Tahunan PPPK masa kerja < 1 tahun dengan alasan khusus dibatasi maksimal 6 hari kerja.',
                            ];
                        }
                    }
                } elseif ($pegawai->isPns()) {
                    if ($hasTmt && $masaKerjaBulan < 12) {
                        return [
                            'eligible' => false,
                            'reason' => 'PNS dengan masa kerja kurang dari 1 tahun terus-menerus belum berhak atas Cuti Tahunan sesuai Peraturan BKN No. 24/2017.',
                        ];
                    }
                }

                $saldo = $this->getSaldoCuti($pegawai, (int) $referenceDate->format('Y'), 0, $context['alasan_pppk_bypass'] ?? null);
                if ($durasiHari > $saldo && $saldo > 0) {
                    return [
                        'eligible' => false,
                        'reason' => 'Jumlah hari cuti tahunan yang diajukan (' . $durasiHari . ' hari kerja) melebihi sisa cuti tersedia (' . $saldo . ' hari kerja).',
                    ];
                }
                break;

            case 'besar':
                if (!$pegawai->isPns()) {
                    return [
                        'eligible' => false,
                        'reason' => 'Cuti Besar hanya berlaku untuk Pegawai Negeri Sipil (PNS).',
                    ];
                }

                if ($hasTmt && $masaKerjaBulan < 60) {
                    $tahunKerja = (int) floor($masaKerjaBulan / 12);
                    $bulanKerja = $masaKerjaBulan % 12;

                    return [
                        'eligible' => false,
                        'reason' => 'Cuti Besar mensyaratkan masa kerja terus-menerus minimal 5 tahun (masa kerja saat ini: ' . $tahunKerja . ' tahun ' . $bulanKerja . ' bulan).',
                    ];
                }

                // Cuti Besar max 3 months (90 days)
                if ($durasiHari > 90) {
                    return [
                        'eligible' => false,
                        'reason' => 'Durasi Cuti Besar maksimal adalah 3 bulan (90 hari kalender).',
                    ];
                }
                break;

            case 'sakit':
                $kategori = trim((string) ($context['kategori_cuti'] ?? CutiLayananPegawai::KATEGORI_SAKIT_REGULER));

                if ($kategori === CutiLayananPegawai::KATEGORI_SAKIT_GUGUR_KANDUNGAN) {
                    if ($durasiHari > 45) {
                        return [
                            'eligible' => false,
                            'reason' => 'Cuti Sakit karena gugur kandungan maksimal adalah 45 hari kalender (1,5 bulan) dengan surat keterangan dokter spesialis kandungan/bidan.',
                        ];
                    }
                } elseif ($kategori === CutiLayananPegawai::KATEGORI_SAKIT_KECELAKAAN_KERJA) {
                    if ($pegawai->isPppk() && $pegawai->tanggal_akhir_kontrak && $tanggalSelesai) {
                        $akhirKontrak = $this->normalizeDate($pegawai->tanggal_akhir_kontrak);
                        if ($akhirKontrak && $tanggalSelesai->gt($akhirKontrak)) {
                            return [
                                'eligible' => false,
                                'reason' => 'Tanggal selesai cuti sakit kecelakaan kerja PPPK tidak boleh melampaui tanggal berakhirnya masa perjanjian kerja (' . $akhirKontrak->format('d-m-Y') . ').',
                            ];
                        }
                    }
                } else {
                    if ($pegawai->isPppk()) {
                        if ($durasiHari > 30) {
                            return [
                                'eligible' => false,
                                'reason' => 'Cuti Sakit untuk PPPK dibatasi maksimal 30 hari kerja kumulatif per tahun masa perjanjian kerja sesuai Peraturan BKN No. 7/2022.',
                            ];
                        }
                    } elseif ($pegawai->isPns()) {
                        $rekomendasiTimKesehatan = !empty($context['rekomendasi_tim_penguji_kesehatan']);
                        if ($durasiHari > 365 && !$rekomendasiTimKesehatan) {
                            return [
                                'eligible' => false,
                                'reason' => 'Cuti Sakit PNS lebih dari 365 hari (1 tahun) memerlukan persetujuan dan rekomendasi resmi dari Tim Penguji Kesehatan.',
                            ];
                        }
                        if ($durasiHari > 545) {
                            return [
                                'eligible' => false,
                                'reason' => 'Durasi maksimal Cuti Sakit PNS beserta perpanjangannya adalah 1 tahun 6 bulan (545 hari kalender).',
                            ];
                        }
                    }
                }
                break;

            case 'melahirkan':
                if ($durasiHari > 90) {
                    return [
                        'eligible' => false,
                        'reason' => 'Durasi Cuti Melahirkan maksimal adalah 3 bulan (90 hari kalender).',
                    ];
                }

                $anakKe = isset($context['kelahiran_anak_ke']) && $context['kelahiran_anak_ke'] !== ''
                    ? (int) $context['kelahiran_anak_ke']
                    : (int) ($pegawai->jumlah_anak ? ($pegawai->jumlah_anak + 1) : 1);

                if ($pegawai->isPppk()) {
                    if ($anakKe > 3) {
                        return [
                            'eligible' => false,
                            'reason' => 'Cuti Melahirkan bagi PPPK hanya diberikan untuk kelahiran anak pertama sampai dengan anak ketiga selama masa perjanjian kerja PPPK.',
                        ];
                    }
                } elseif ($pegawai->isPns()) {
                    if ($anakKe > 3) {
                        return [
                            'eligible' => false,
                            'reason' => 'Untuk persalinan anak keempat dan seterusnya bagi PNS, silakan ajukan melalui mekanisme Cuti Besar Melahirkan sesuai PP No. 11/2017 jo. Peraturan BKN No. 24/2017.',
                        ];
                    }
                }
                break;

            case 'alasan_penting':
                if (!$pegawai->isPns()) {
                    return [
                        'eligible' => false,
                        'reason' => 'Cuti Karena Alasan Penting (CAP) hanya berlaku bagi Pegawai Negeri Sipil (PNS).',
                    ];
                }

                $alasanCap = trim((string) ($context['alasan_cap'] ?? ''));
                if ($alasanCap === '' || !array_key_exists($alasanCap, CutiLayananPegawai::alasanCapOptions())) {
                    return [
                        'eligible' => false,
                        'reason' => 'Pengajuan Cuti Karena Alasan Penting wajib memilih salah satu alasan yang diakui regulasi (Keluarga Sakit Keras/Meninggal, Perkawinan Pertama, Bencana Alam, atau Istri Melahirkan/Operasi).',
                    ];
                }

                if ($durasiHari > 30) {
                    return [
                        'eligible' => false,
                        'reason' => 'Durasi Cuti Karena Alasan Penting (CAP) maksimal adalah 1 bulan (30 hari kalender).',
                    ];
                }
                break;

            case 'di_luar_tanggungan_negara':
                if (!$pegawai->isPns()) {
                    return [
                        'eligible' => false,
                        'reason' => 'Cuti di Luar Tanggungan Negara (CLTN) hanya berlaku bagi Pegawai Negeri Sipil (PNS).',
                    ];
                }

                if ($hasTmt && $masaKerjaBulan < 60) {
                    return [
                        'eligible' => false,
                        'reason' => 'Cuti di Luar Tanggungan Negara (CLTN) hanya dapat diberikan kepada PNS yang telah bekerja paling kurang 5 tahun secara terus-menerus.',
                    ];
                }

                if ($durasiHari > 1095) { // 3 years = 3 * 365
                    return [
                        'eligible' => false,
                        'reason' => 'Durasi Cuti di Luar Tanggungan Negara (CLTN) paling lama adalah 3 tahun.',
                    ];
                }
                break;
        }

        return [
            'eligible' => true,
            'reason' => null,
            'durasi_hari' => $durasiHari,
        ];
    }

    /**
     * Handle state updates and side effects on pegawai profile when a leave is completed.
     */
    public function handleCutiCompletedSideEffects($target, ?CutiLayananPegawai $cutiDetail = null): void
    {
        if ($target instanceof LayananPegawai) {
            $pegawai = $target->pegawai;
            $cutiDetail = $target->resolvedCutiDetail();
        } elseif ($target instanceof Pegawai) {
            $pegawai = $target;
        } else {
            return;
        }

        if (!$pegawai || !$cutiDetail) {
            return;
        }

        $jenis = self::normalizeJenisCuti($cutiDetail->jenis_cuti);
        $referenceYear = (int) optional($cutiDetail->tanggal_mulai ?? ($target instanceof LayananPegawai ? $target->created_at : now()))->format('Y');

        // 1. Cuti Besar: Reset annual leave quota for current year to 0
        if ($jenis === 'besar') {
            PegawaiCutiQuota::updateOrCreate(
                [
                    'pegawai_id' => $pegawai->id,
                    'tahun' => $referenceYear,
                ],
                [
                    'hari_tersedia' => 0,
                    'keterangan' => 'Direset ke 0 karena pengambilan Cuti Besar pada tahun ' . $referenceYear,
                ]
            );
        }

        // 2. CLTN: Freeze payroll and set kedudukan pegawai to CLTN (02)
        if ($jenis === 'di_luar_tanggungan_negara') {
            $pegawai->update([
                'status_payroll' => 'non_aktif',
                'kedudukan_pegawai_id' => '02', // 02 = CLTN
            ]);
        }
    }

    /**
     * Handle state updates when a completed leave is cancelled.
     */
    public function handleCutiCancelledSideEffects($target, ?CutiLayananPegawai $cutiDetail = null): void
    {
        if ($target instanceof LayananPegawai) {
            $pegawai = $target->pegawai;
            $cutiDetail = $target->resolvedCutiDetail();
        } elseif ($target instanceof Pegawai) {
            $pegawai = $target;
        } else {
            return;
        }

        if (!$pegawai || !$cutiDetail) {
            return;
        }

        $jenis = self::normalizeJenisCuti($cutiDetail->jenis_cuti);
        $referenceYear = (int) optional($cutiDetail->tanggal_mulai ?? ($target instanceof LayananPegawai ? $target->created_at : now()))->format('Y');

        if ($jenis === 'besar') {
            PegawaiCutiQuota::updateOrCreate(
                [
                    'pegawai_id' => $pegawai->id,
                    'tahun' => $referenceYear,
                ],
                [
                    'hari_tersedia' => self::HARI_PER_TAHUN,
                    'keterangan' => 'Dipulihkan kembali setelah pembatalan Cuti Besar tahun ' . $referenceYear,
                ]
            );
        }

        if ($jenis === 'di_luar_tanggungan_negara') {
            $pegawai->update([
                'status_payroll' => 'aktif',
                'kedudukan_pegawai_id' => $pegawai->isPppk() ? '71' : '01', // 01 = Aktif PNS, 71 = PPPK Aktif
            ]);
        }
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
            ? $this->getCutiBreakdown($pegawai, $referenceYear, $projectedRequestDays, $cutiDetail?->alasan_pppk_bypass)
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
    protected function simulateSaldoBuckets(Pegawai $pegawai, int $currentYear, ?string $alasanPppkBypass = null): array
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

        $hasTmt = (bool) $pegawai->getTanggalMulaiKerja();
        $isPppkLessThanOneYear = $pegawai->isPppk() && $hasTmt && $pegawai->getMasaKerjaBulan() < 12;
        $isPppkBypass = $isPppkLessThanOneYear && in_array($alasanPppkBypass, [
            CutiLayananPegawai::ALASAN_PPPK_PERKAWINAN_PERTAMA,
            CutiLayananPegawai::ALASAN_PPPK_KELUARGA_INTI_SAKIT_KERAS_MENINGGAL,
        ], true);

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

            if (!isset($buckets[$year])) {
                $baseQuota = $pegawai->getCutiQuotaForYear($year);

                if ($year === $currentYear) {
                    if ($pegawai->kompensasi_cuti_bersama > 0) {
                        $baseQuota += (int) $pegawai->kompensasi_cuti_bersama;
                    }

                    if ($isPppkLessThanOneYear) {
                        $baseQuota = $isPppkBypass ? 6 : 0;
                    }
                }

                $buckets[$year] = $baseQuota;
            }

            if ($year === $currentYear) {
                for ($i = 0; $i < self::TAHUN_DIPERHITUNGKAN; $i++) {
                    $originYear = $currentYear - $i;
                    $availableAtStartOfCurrentYear[$originYear] = (int) ($buckets[$originYear] ?? 0);
                    $consumedInCurrentYear[$originYear] = 0;
                }
            }

            foreach ($requestsByYear->get($year, collect()) as $request) {
                $detail = $request->resolvedCutiDetail();
                // Only annual leaves deduct from annual leave saldo quota
                $jenisCuti = $detail && $detail->jenis_cuti ? self::normalizeJenisCuti($detail->jenis_cuti) : 'tahunan';
                if ($jenisCuti !== 'tahunan') {
                    continue;
                }

                $remainingToConsume = max(0, (int) optional($detail)->hari_diminta);

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
        $jabatans = array_filter([$pegawai->jabatan_rangkap, $pegawai->jabatan]);

        $atasanLangsungId = null;
        foreach ($jabatans as $j) {
            $atasanLangsungId = $j->peta_jabatan?->atasan_langsung_id ?? $j->atasan_langsung_id;
            if ($atasanLangsungId) {
                break;
            }
        }

        if (!$atasanLangsungId) {
            return null;
        }

        $query = Pegawai::query()
            ->where('id', '!=', $pegawai->id)
            ->where(function ($q) use ($atasanLangsungId) {
                $q->where('jabatan_id', $atasanLangsungId)
                    ->orWhere('jabatan_rangkap_id', $atasanLangsungId);
            });

        if ($pegawai->unit_kerja_id) {
            $supervisorInUnit = (clone $query)
                ->where('unit_kerja_id', $pegawai->unit_kerja_id)
                ->orderBy('nama')
                ->first();

            if ($supervisorInUnit) {
                return $supervisorInUnit;
            }
        }

        return $query->orderBy('nama')->first();
    }

    public function getSubordinatePegawaiIds(Pegawai $supervisor): array
    {
        $supervisorJabatanIds = array_values(array_filter([
            $supervisor->jabatan_id,
            $supervisor->jabatan_rangkap_id,
        ]));

        if (empty($supervisorJabatanIds)) {
            return [];
        }

        return Pegawai::query()
            ->where('id', '!=', $supervisor->id)
            ->where(function ($q) use ($supervisorJabatanIds) {
                $q->whereHas('jabatan.peta_jabatan', function ($pjq) use ($supervisorJabatanIds) {
                    $pjq->whereIn('atasan_langsung_id', $supervisorJabatanIds);
                })
                ->orWhereHas('jabatan_rangkap.peta_jabatan', function ($pjq) use ($supervisorJabatanIds) {
                    $pjq->whereIn('atasan_langsung_id', $supervisorJabatanIds);
                });
            })
            ->pluck('id')
            ->all();
    }

    public function isSupervisorOf(Pegawai $supervisor, Pegawai $subordinate): bool
    {
        if ((int) $supervisor->id === (int) $subordinate->id) {
            return false;
        }

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
        $tanggalMulai = $pegawai->getTanggalMulaiKerja();

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
