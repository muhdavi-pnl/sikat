<?php

namespace App\Services\Intelligence;

use App\Models\Dokumen;
use App\Models\DokumenPegawai;
use App\Models\Pegawai;

class PegawaiInsightService
{
    public function analyze(Pegawai $pegawai): array
    {
        $profileFields = (array) config('intelligence.profile_fields', []);
        $weights = (array) config('intelligence.weights', ['profile' => 70, 'documents' => 30]);

        $missingProfileFields = [];

        foreach ($profileFields as $field => $label) {
            $value = $pegawai->{$field};

            if ($value === null || $value === '') {
                $missingProfileFields[] = (string) $label;
            }
        }

        $profileWeight = (int) ($weights['profile'] ?? 70);
        $documentsWeight = (int) ($weights['documents'] ?? 30);
        $weightTotal = max(1, $profileWeight + $documentsWeight);

        $profileCompletion = empty($profileFields)
            ? 1.0
            : (count($profileFields) - count($missingProfileFields)) / count($profileFields);

        $requiredCodes = array_values(array_filter((array) config('intelligence.required_document_codes', [])));
        $normalizedRequiredCodes = collect($requiredCodes)
            ->map(function ($code) {
                return mb_strtoupper(trim((string) $code));
            })
            ->filter()
            ->values();

        $masterDokumens = Dokumen::query()->get(['id', 'kode_dokumen', 'nama_dokumen']);

        $scoredDokumens = $normalizedRequiredCodes->isEmpty()
            ? $masterDokumens
            : $masterDokumens->filter(function ($dokumen) use ($normalizedRequiredCodes) {
                $normalizedCode = mb_strtoupper(trim((string) $dokumen->kode_dokumen));

                return $normalizedRequiredCodes->contains($normalizedCode);
            })->values();

        $uploadedDokumenIds = $scoredDokumens->isEmpty()
            ? collect()
            : DokumenPegawai::query()
                ->where('pegawai_id', $pegawai->id)
                ->where('status', true)
                ->whereIn('dokumen_id', $scoredDokumens->pluck('id'))
                ->pluck('dokumen_id')
                ->unique();

        $ownedDocuments = $scoredDokumens
            ->filter(function ($dokumen) use ($uploadedDokumenIds) {
                return $uploadedDokumenIds->contains($dokumen->id);
            })
            ->map(function ($dokumen) {
                return [
                    'kode' => (string) $dokumen->kode_dokumen,
                    'nama' => (string) $dokumen->nama_dokumen,
                ];
            })
            ->values()
            ->all();

        $missingDocuments = $scoredDokumens
            ->reject(function ($dokumen) use ($uploadedDokumenIds) {
                return $uploadedDokumenIds->contains($dokumen->id);
            })
            ->map(function ($dokumen) {
                return [
                    'kode' => (string) $dokumen->kode_dokumen,
                    'nama' => (string) $dokumen->nama_dokumen,
                ];
            })
            ->values()
            ->all();

        $documentCompletion = $scoredDokumens->isEmpty()
            ? 1.0
            : ($scoredDokumens->count() - count($missingDocuments)) / $scoredDokumens->count();

        $profileScore = (int) round($profileCompletion * 100);
        $documentScore = (int) round($documentCompletion * 100);

        $finalScore = (int) round((
            ($profileScore * $profileWeight) +
            ($documentScore * $documentsWeight)
        ) / $weightTotal);

        return [
            'score' => max(0, min(100, $finalScore)),
            'profile_score' => $profileScore,
            'document_score' => $documentScore,
            'missing_profile_fields' => $missingProfileFields,
            'owned_documents' => $ownedDocuments,
            'missing_documents' => $missingDocuments,
            'required_document_codes' => $requiredCodes,
            'total_scored_documents' => $scoredDokumens->count(),
            'total_owned_documents' => count($ownedDocuments),
        ];
    }
}

