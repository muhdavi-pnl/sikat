<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Services\PetaJabatan\PetaJabatanDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetaJabatanController extends Controller
{
    public function __construct(private readonly PetaJabatanDashboardService $dashboardService)
    {
    }

    public function index(Request $request)
    {
        $unitKerjaId = $request->integer('unit_kerja_id');
        $jenisJabatanId = $request->integer('jenis_jabatan_id');
        $statusJabatan = $request->string('status_jabatan')->toString();
        $search = trim((string) $request->input('search', ''));

        $query = Jabatan::query()
            ->with(['unit_kerja', 'atasan_langsung', 'bawahan', 'pegawais', 'peta_jabatan', 'pangkat_minimal_rel'])
            ->withCount(['pegawais', 'bawahan']);

        if ($unitKerjaId) {
            $query->whereHas('peta_jabatan', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('jabatan', 'like', '%' . $search . '%')
                    ->orWhere('kode_jabatan', 'like', '%' . $search . '%');
            });
        }

        $jabatans = $query->orderBy('kode_jabatan')->orderBy('jabatan')->get();

        $occupiedCounts = $jabatans->mapWithKeys(function (Jabatan $jabatan) {
            return [$jabatan->id => $jabatan->pegawais_count];
        });

        $jabatanIds = $jabatans->pluck('id')->all();
        $tree = $jabatans
            ->filter(fn (Jabatan $jabatan) => is_null($jabatan->atasan_langsung_id) || !in_array($jabatan->atasan_langsung_id, $jabatanIds))
            ->map(function (Jabatan $jabatan) use ($jabatans, $occupiedCounts) {
                return $this->buildNode($jabatan, $jabatans, $occupiedCounts);
            })
            ->values()
            ->all();

        $summary = [
            'total_jabatan' => $jabatans->count(),
            'jabatan_terisi' => $jabatans->filter(fn (Jabatan $jabatan) => $jabatan->pegawais_count > 0)->count(),
            'jabatan_kosong' => $jabatans->filter(fn (Jabatan $jabatan) => $jabatan->pegawais_count === 0)->count(),
            'kekurangan' => $jabatans->sum(fn (Jabatan $jabatan) => max(($jabatan->kebutuhan_pegawai ?? 0) - $jabatan->pegawais_count, 0)),
        ];

        $gapRows = $jabatans->map(function (Jabatan $jabatan) {
            $kebutuhan = (int) ($jabatan->kebutuhan_pegawai ?? 0);
            $terisi = (int) $jabatan->pegawais_count;

            return [
                'id' => $jabatan->id,
                'kode_jabatan' => $jabatan->kode_jabatan,
                'jabatan' => $jabatan->jabatan,
                'unit_kerja' => $jabatan->unit_kerja?->unit_kerja ?? '-',
                'unit_kerja_id' => $jabatan->unit_kerja_id,
                'atasan_langsung' => $jabatan->atasan_langsung?->jabatan ?? '-',
                'kebutuhan' => $kebutuhan,
                'terisi' => $terisi,
                'kekurangan' => max($kebutuhan - $terisi, 0),
                'kelebihan' => max($terisi - $kebutuhan, 0),
                'pegawais_count' => $jabatan->pegawais_count,
                'bawahan_count' => $jabatan->bawahan_count,
            ];
        })->values();

        $careerPaths = DB::table('career_paths')
            ->join('jabatans as asal', 'asal.id', '=', 'career_paths.jabatan_asal_id')
            ->join('jabatans as tujuan', 'tujuan.id', '=', 'career_paths.jabatan_tujuan_id')
            ->select('asal.jabatan as asal', 'tujuan.jabatan as tujuan', 'career_paths.persyaratan')
            ->get();

        return view('peta-jabatan.index', [
            'title' => 'Peta Jabatan',
            'tree' => $tree,
            'summary' => $summary,
            'gapRows' => $gapRows,
            'careerPaths' => $careerPaths,
            'filters' => [
                'search' => $search,
                'unit_kerja_id' => $unitKerjaId,
                'jenis_jabatan_id' => $jenisJabatanId,
                'status_jabatan' => $statusJabatan,
            ],
            'unitKerjas' => DB::table('unit_kerjas')->select('id', 'unit_kerja')->orderBy('order', 'asc')->orderBy('unit_kerja', 'asc')->get(),
            'jenisJabatans' => DB::table('jenis_jabatans')->select('id', 'jenis_jabatan')->orderBy('jenis_jabatan')->get(),
            'canManage' => $request->user()?->hasAnyRole(['super-admin', 'kepegawaian']) ?? false,
        ]);
    }

    /**
     * JSON payload for the Peta Jabatan dashboard (docs/peta-jabatan.md
     * section 9). Consumed by the dashboard widgets on this page and mirrors
     * the token-based /api/peta-jabatan/dashboard endpoint.
     */
    public function dashboard()
    {
        return response()->json([
            'data' => $this->dashboardService->summary(),
        ]);
    }

    private function buildNode(Jabatan $jabatan, $jabatans, $occupiedCounts): array
    {
        $needed = (int) ($jabatan->kebutuhan_pegawai ?? 0);
        $occupied = (int) ($occupiedCounts[$jabatan->id] ?? 0);

        if ($occupied === 0) {
            $status = 'Kosong';
        } elseif ($occupied > $needed && $needed > 0) {
            $status = 'Kelebihan';
        } elseif ($occupied < $needed) {
            $status = 'Kekurangan';
        } else {
            $status = 'Terisi';
        }

        $pegawais = $jabatan->pegawais->map(function ($pegawai) {
            return [
                'id' => $pegawai->id,
                'nama' => $pegawai->nama_lengkap ?: $pegawai->nama,
                'nip' => $pegawai->nip ?? null,
            ];
        })->values()->all();

        return [
            'id' => $jabatan->id,
            'kode_jabatan' => $jabatan->kode_jabatan,
            'nama' => $jabatan->jabatan,
            'kelas_jabatan' => $jabatan->kelas_jabatan,
            'pangkat_minimal' => $jabatan->pangkat_minimal_rel ? ($jabatan->pangkat_minimal_rel->pangkat . ' (' . $jabatan->pangkat_minimal_rel->golongan_ruang . ')') : null,
            'unit_kerja' => $jabatan->unit_kerja?->unit_kerja ?? '-',
            'unit_kerja_id' => $jabatan->unit_kerja_id,
            'pemangku' => $jabatan->pegawais->map(fn ($p) => $p->nama_lengkap ?: $p->nama)->filter()->values()->all(),
            'pegawais' => $pegawais,
            'status' => $status,
            'kebutuhan_pegawai' => $needed,
            'jumlah_pemangku' => $occupied,
            'bawahan_count' => (int) $jabatan->bawahan_count,
            'children' => $jabatans
                ->where('atasan_langsung_id', $jabatan->id)
                ->map(fn (Jabatan $child) => $this->buildNode($child, $jabatans, $occupiedCounts))
                ->values()
                ->all(),
        ];
    }
}
