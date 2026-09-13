<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LayananPegawai;
use App\Services\Intelligence\SmartTriageService;
use Illuminate\Http\Request;

class SmartTriageController extends Controller
{
    public function index(Request $request, SmartTriageService $triageService)
    {
        $keyword = trim((string) $request->query('q', ''));
        $risk = trim((string) $request->query('risk', ''));
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $shouldRefresh = filter_var($request->query('refresh', false), FILTER_VALIDATE_BOOLEAN);

        $query = LayananPegawai::query()
            ->with(['layanan:id,layanan', 'pegawai:id,nama,nip'])
            ->whereDoesntHave('cutiDetail')
            ->when($keyword !== '', function ($builder) use ($keyword) {
                $builder->where(function ($innerQuery) use ($keyword) {
                    $innerQuery->whereHas('pegawai', function ($pegawaiQuery) use ($keyword) {
                        $pegawaiQuery->where('nama', 'like', '%' . $keyword . '%')
                            ->orWhere('nip', 'like', '%' . $keyword . '%');
                    })->orWhereHas('layanan', function ($layananQuery) use ($keyword) {
                        $layananQuery->where('layanan', 'like', '%' . $keyword . '%');
                    });
                });
            })
            ->when(in_array($risk, ['high', 'medium', 'low', 'none'], true), function ($builder) use ($risk) {
                $builder->where('sla_risk', $risk);
            });

        $refreshedCount = 0;
        if ($shouldRefresh) {
            $refreshedCount = $triageService->refreshForBuilder(clone $query);
        }

        $paginator = $query
            ->orderByRaw("CASE
                WHEN sla_risk = 'high' THEN 0
                WHEN sla_risk = 'medium' THEN 1
                WHEN sla_risk = 'low' THEN 2
                ELSE 3
            END")
            ->orderByDesc('priority_score')
            ->orderByRaw('CASE WHEN sla_due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sla_due_at')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => collect($paginator->items())->map(function (LayananPegawai $item) {
                return [
                    'id' => (int) $item->id,
                    'status' => (string) $item->status,
                    'priority_score' => (int) ($item->priority_score ?? 0),
                    'sla_due_at' => optional($item->sla_due_at)->toIso8601String(),
                    'sla_risk' => (string) ($item->sla_risk ?? 'none'),
                    'pegawai' => [
                        'id' => (int) optional($item->pegawai)->id,
                        'nama' => (string) (optional($item->pegawai)->nama ?? ''),
                        'nip' => (string) (optional($item->pegawai)->nip ?? ''),
                    ],
                    'layanan' => [
                        'id' => (int) optional($item->layanan)->id,
                        'nama' => (string) (optional($item->layanan)->layanan ?? ''),
                    ],
                    'created_at' => optional($item->created_at)->toIso8601String(),
                    'updated_at' => optional($item->updated_at)->toIso8601String(),
                ];
            })->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'refreshed_count' => $refreshedCount,
                'filters' => [
                    'q' => $keyword,
                    'risk' => $risk,
                    'refresh' => $shouldRefresh,
                ],
            ],
        ]);
    }
}

