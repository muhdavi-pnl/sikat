<?php

namespace App\Services\Intelligence;

use App\Models\Dokumen;
use App\Models\DokumenPegawai;
use App\Models\Layanan;
use App\Models\Pegawai;
use App\Models\Syarat;

class LayananEligibilityService
{
    public function analyze(Pegawai $pegawai, Layanan $layanan, array $syaratEvidenceIds = [], array $context = []): array
    {
        $layanan->loadMissing('syarat.dokumen');
        $optionalRequirementCodes = $this->optionalRequirementCodesFor($layanan);
        $syaratEvidenceIds = collect($syaratEvidenceIds)->map(function ($id) {
            return (int) $id;
        })->all();
        $cutiContext = $this->normalizeCutiContext($context);

        $profileRequirementMap = collect((array) config('intelligence.layanan_profile_requirement_codes', []))
            ->mapWithKeys(function ($field, $code) {
                return [$this->normalizeRequirementCode($code) => (string) $field];
            });

        $profileLabels = (array) config('intelligence.profile_fields', []);

        $masterDokumens = Dokumen::query()->get(['id', 'kode_dokumen', 'nama_dokumen']);
        $masterDokumenByCode = $masterDokumens
            ->keyBy(function ($dokumen) {
                return $this->normalizeRequirementCode((string) $dokumen->kode_dokumen);
            });
        $masterDokumenById = $masterDokumens->keyBy('id');

        $validDokumenIds = DokumenPegawai::query()
            ->where('pegawai_id', $pegawai->id)
            ->where('status', true)
            ->pluck('dokumen_id')
            ->unique();

        $requirements = $layanan->syarat
            ->map(function (Syarat $syarat) use ($pegawai, $layanan, $validDokumenIds, $profileRequirementMap, $profileLabels, $masterDokumenByCode, $masterDokumenById, $syaratEvidenceIds, $optionalRequirementCodes, $cutiContext) {
                $normalizedCode = $this->normalizeRequirementCode($syarat->kode_syarat);
                $isOptional = in_array($normalizedCode, $optionalRequirementCodes, true);

                if ($this->isGeneratedCutiFormRequirement($layanan, $normalizedCode)) {
                    $isMet = $this->hasPrintableCutiFormData($cutiContext);

                    return [
                        'id' => $syarat->id,
                        'syarat' => (string) $syarat->syarat,
                        'kode_syarat' => (string) $syarat->kode_syarat,
                        'status' => $isMet ? 'met' : 'unmet',
                        'source' => 'generated',
                        'label' => 'Formulir Permintaan dan Pemberian Cuti',
                        'is_optional' => false,
                        'blocks_submission' => !$isMet,
                        'message' => $isMet
                            ? 'Formulir cuti akan dihasilkan otomatis dari data usulan dan siap dicetak.'
                            : 'Lengkapi data cuti wajib agar formulir Lampiran 1.B dapat dibuat.',
                    ];
                }

                if ($normalizedCode !== '' && $profileRequirementMap->has($normalizedCode)) {
                    $field = $profileRequirementMap->get($normalizedCode);
                    $label = (string) ($profileLabels[$field] ?? $syarat->syarat ?? $normalizedCode);
                    $value = $pegawai->{$field};
                    $isMet = !($value === null || $value === '');

                    return [
                        'id' => $syarat->id,
                        'syarat' => (string) $syarat->syarat,
                        'kode_syarat' => (string) $syarat->kode_syarat,
                        'status' => $isMet ? 'met' : ($isOptional ? 'optional' : 'unmet'),
                        'source' => 'profile',
                        'label' => $label,
                        'is_optional' => $isOptional,
                        'blocks_submission' => !$isMet && !$isOptional,
                        'message' => $isMet
                            ? 'Data profil tersedia.'
                            : ($isOptional
                                ? 'Data profil ' . $label . ' bersifat opsional untuk layanan ini.'
                                : 'Data profil ' . $label . ' belum lengkap.'),
                    ];
                }

                $dokumen = null;
                if ($syarat->dokumen_id && $masterDokumenById->has($syarat->dokumen_id)) {
                    $dokumen = $masterDokumenById->get($syarat->dokumen_id);
                } elseif ($normalizedCode !== '' && $masterDokumenByCode->has($normalizedCode)) {
                    $dokumen = $masterDokumenByCode->get($normalizedCode);
                }

                if ($dokumen) {
                    $dokumenId = (int) $dokumen->id;
                    $isMet = $validDokumenIds->contains($dokumenId);

                    return [
                        'id' => $syarat->id,
                        'syarat' => (string) $syarat->syarat,
                        'kode_syarat' => (string) $syarat->kode_syarat,
                        'status' => $isMet ? 'met' : ($isOptional ? 'optional' : 'unmet'),
                        'source' => 'document',
                        'label' => (string) $dokumen->nama_dokumen,
                        'is_optional' => $isOptional,
                        'blocks_submission' => !$isMet && !$isOptional,
                        'message' => $isMet
                            ? 'Dokumen valid tersedia.'
                            : ($isOptional
                                ? 'Dokumen ' . ((string) $dokumen->nama_dokumen ?: 'opsional') . ' bersifat opsional dan dapat dilampirkan bila tersedia.'
                                : 'Dokumen ' . ((string) $dokumen->nama_dokumen ?: 'wajib') . ' belum diunggah atau belum divalidasi.'),
                    ];
                }

                $isMet = in_array((int) $syarat->id, $syaratEvidenceIds, true);

                return [
                    'id' => $syarat->id,
                    'syarat' => (string) $syarat->syarat,
                    'kode_syarat' => (string) $syarat->kode_syarat,
                    'status' => $isMet ? 'met' : ($isOptional ? 'optional' : 'unmet'),
                    'source' => 'upload',
                    'label' => null,
                    'is_optional' => $isOptional,
                    'blocks_submission' => !$isMet && !$isOptional,
                    'message' => $isMet
                        ? 'Bukti dokumen syarat tambahan sudah dilampirkan.'
                        : ($isOptional
                            ? 'Syarat ini bersifat opsional. Unggah bukti bila tersedia atau bila nantinya diminta petugas kepegawaian.'
                            : 'Syarat ini belum tersinkron dengan master dokumen atau kode profil. Silakan unggah dokumen bukti pada form usulan.'),
                ];
            })
            ->values();

        $metCount = $requirements->where('status', 'met')->count();
        $unmetCount = $requirements->where('status', 'unmet')->count();
        $manualCount = $requirements->where('status', 'manual')->count();
        $optionalCount = $requirements->where('is_optional', true)->count();
        $optionalMissingCount = $requirements->where('status', 'optional')->count();
        $totalCount = $requirements->count();

        return [
            'eligible' => $unmetCount === 0,
            'summary' => $this->buildSummary($unmetCount, $manualCount, $totalCount, $optionalMissingCount),
            'requirements' => $requirements->all(),
            'met_count' => $metCount,
            'unmet_count' => $unmetCount,
            'manual_count' => $manualCount,
            'optional_count' => $optionalCount,
            'optional_missing_count' => $optionalMissingCount,
            'total_count' => $totalCount,
        ];
    }

    protected function buildSummary(int $unmetCount, int $manualCount, int $totalCount, int $optionalMissingCount = 0): string
    {
        if ($unmetCount > 0) {
            return 'Belum memenuhi ' . $unmetCount . ' syarat';
        }

        if ($manualCount > 0) {
            return 'Siap diajukan dengan ' . $manualCount . ' syarat perlu verifikasi manual';
        }

        if ($optionalMissingCount > 0) {
            return 'Siap diajukan dengan ' . $optionalMissingCount . ' syarat opsional belum dilampirkan';
        }

        if ($totalCount === 0) {
            return 'Siap diajukan';
        }

        return 'Siap diajukan';
    }

    protected function optionalRequirementCodesFor(Layanan $layanan): array
    {
        if (!$this->isCutiLayanan($layanan)) {
            return [];
        }

        return collect((array) config('intelligence.cuti_optional_requirement_codes', []))
            ->map(function ($code) {
                return $this->normalizeRequirementCode((string) $code);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function isCutiLayanan(Layanan $layanan): bool
    {
        return str_contains(mb_strtolower((string) $layanan->layanan), 'cuti');
    }

    protected function isGeneratedCutiFormRequirement(Layanan $layanan, string $normalizedCode): bool
    {
        return $this->isCutiLayanan($layanan) && $normalizedCode === 'CUTIFORM';
    }

    protected function normalizeCutiContext(array $context): array
    {
        return [
            'jenis_cuti' => trim((string) ($context['cuti_jenis'] ?? $context['jenis_cuti'] ?? '')),
            'alasan_cuti' => trim((string) ($context['cuti_alasan'] ?? $context['alasan_cuti'] ?? '')),
            'alamat_menjalankan_cuti' => trim((string) ($context['cuti_alamat'] ?? $context['alamat_menjalankan_cuti'] ?? '')),
            'nomor_telepon_cuti' => trim((string) ($context['cuti_no_telp'] ?? $context['nomor_telepon_cuti'] ?? '')),
            'tanggal_mulai' => trim((string) ($context['cuti_tanggal_mulai'] ?? $context['tanggal_mulai'] ?? '')),
            'tanggal_selesai' => trim((string) ($context['cuti_tanggal_selesai'] ?? $context['tanggal_selesai'] ?? '')),
            'hari_diminta' => (int) ($context['cuti_hari_diminta'] ?? $context['hari_diminta'] ?? 0),
        ];
    }

    protected function hasPrintableCutiFormData(array $cutiContext): bool
    {
        return $cutiContext['tanggal_mulai'] !== ''
            && $cutiContext['tanggal_selesai'] !== ''
            && $cutiContext['hari_diminta'] > 0;
    }

    protected function normalizeRequirementCode(?string $code): string
    {
        $normalized = mb_strtoupper(trim((string) $code));

        if ($normalized === '') {
            return '';
        }

        return (string) preg_replace('/[^A-Z0-9]+/', '', $normalized);
    }
}
