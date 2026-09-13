<?php

namespace App\Services\Intelligence;

use App\Models\LayananPegawai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SmartTriageService
{
    public function analyze(LayananPegawai $layananPegawai, ?Carbon $now = null): array
    {
        $now = $now ?: now();

        $status = (string) $layananPegawai->status;
        $baseScore = (int) config('intelligence.triage.status_base_scores.' . $status, 40);

        $createdAt = $layananPegawai->created_at ?: $now;
        $ageHours = max(0, (int) $createdAt->diffInHours($now));
        $hoursPerPoint = max(1, (int) config('intelligence.triage.age.hours_per_point', 6));
        $ageMaxBonus = max(0, (int) config('intelligence.triage.age.max_bonus', 20));
        $ageBonus = min($ageMaxBonus, intdiv($ageHours, $hoursPerPoint));

        $keywordBonus = $this->keywordBonus((string) ($layananPegawai->catatan_pengusul ?? ''));

        $priorityScore = (int) max(0, min(100, $baseScore + $ageBonus + $keywordBonus));

        $terminalStatuses = (array) config('intelligence.triage.terminal_statuses', [
            LayananPegawai::STATUS_SELESAI,
            LayananPegawai::STATUS_DITOLAK,
        ]);

        if (in_array($status, $terminalStatuses, true)) {
            return [
                'priority_score' => $priorityScore,
                'sla_due_at' => null,
                'sla_risk' => 'none',
            ];
        }

        $slaHours = (int) config('intelligence.triage.sla_hours.' . $status, (int) config('intelligence.triage.sla_hours.default', 48));
        $slaDueAt = $createdAt->copy()->addHours(max(1, $slaHours));

        $minutesLeft = (int) $now->diffInMinutes($slaDueAt, false);
        $highRiskMinutes = (int) config('intelligence.triage.risk_thresholds.high_minutes', 360);
        $mediumRiskMinutes = (int) config('intelligence.triage.risk_thresholds.medium_minutes', 1440);

        if ($minutesLeft <= 0 || $minutesLeft <= $highRiskMinutes) {
            $slaRisk = 'high';
        } elseif ($minutesLeft <= $mediumRiskMinutes) {
            $slaRisk = 'medium';
        } else {
            $slaRisk = 'low';
        }

        return [
            'priority_score' => $priorityScore,
            'sla_due_at' => $slaDueAt,
            'sla_risk' => $slaRisk,
        ];
    }

    public function applyAndPersist(LayananPegawai $layananPegawai, ?Carbon $now = null): void
    {
        $payload = $this->analyze($layananPegawai, $now);

        $layananPegawai->forceFill($payload);
        $layananPegawai->save();
    }

    public function refreshForBuilder(Builder $builder, ?Carbon $now = null): int
    {
        $now = $now ?: now();
        $refreshedCount = 0;

        $builder
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($now, &$refreshedCount) {
                $ids = collect($items)->pluck('id')->map(function ($id) {
                    return (int) $id;
                })->all();

                if (empty($ids)) {
                    return;
                }

                LayananPegawai::query()
                    ->whereIn('id', $ids)
                    ->get()
                    ->each(function (LayananPegawai $layananPegawai) use ($now) {
                        $this->applyAndPersist($layananPegawai, $now);
                    });
                $refreshedCount += count($ids);
            }, 'id');

        return $refreshedCount;
    }

    protected function keywordBonus(string $catatanPengusul): int
    {
        $keywords = collect((array) config('intelligence.triage.urgent_keywords', []))
            ->map(function ($keyword) {
                return mb_strtolower(trim((string) $keyword));
            })
            ->filter()
            ->all();

        if (empty($keywords)) {
            return 0;
        }

        $normalizedNote = mb_strtolower($catatanPengusul);

        foreach ($keywords as $keyword) {
            if (str_contains($normalizedNote, $keyword)) {
                return (int) config('intelligence.triage.keyword_bonus', 10);
            }
        }

        return 0;
    }
}

