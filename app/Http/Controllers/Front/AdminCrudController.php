<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\JabatanRequest;
use App\Models\CareerPath;
use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\Pangkat;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RealRashid\SweetAlert\Facades\Alert;

class AdminCrudController extends Controller
{
    public function generateKode(Request $request): JsonResponse
    {
        $unitKerjaId = $request->filled('unit_kerja_id') ? (int) $request->input('unit_kerja_id') : null;
        $jenisJabatanId = $request->filled('jenis_jabatan_id') ? (int) $request->input('jenis_jabatan_id') : null;
        $excludeId = $request->filled('exclude_id') ? (int) $request->input('exclude_id') : null;

        $kode = Jabatan::generateKode($unitKerjaId, $jenisJabatanId, $excludeId);

        return response()->json([
            'success' => true,
            'kode' => $kode,
        ]);
    }
    public function index(string $slug): View
    {
        if ($slug === 'jabatan' || $slug === 'peta-jabatan') {
            $search = trim((string) request('search', ''));
            $unitKerjaId = request('unit_kerja_id');
            $jenisJabatanId = request('jenis_jabatan_id');
            $statusJabatan = request('status_jabatan');

            $query = Jabatan::query()
                ->with(['peta_jabatan.unit_kerja', 'peta_jabatan.atasan_langsung', 'pangkat_minimal_rel', 'jenis_jabatan'])
                ->withCount('pegawais');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('jabatan', 'like', "%{$search}%")
                        ->orWhere('kode_jabatan', 'like', "%{$search}%");
                });
            }

            if (!empty($unitKerjaId)) {
                $query->whereHas('peta_jabatan', function ($q) use ($unitKerjaId) {
                    $q->where('unit_kerja_id', $unitKerjaId);
                });
            }

            if (!empty($jenisJabatanId)) {
                $query->where('jenis_jabatan_id', $jenisJabatanId);
            }

            if (!empty($statusJabatan)) {
                $query->where('status_jabatan', $statusJabatan);
            }

            $rows = $query
                ->orderByRaw('CASE WHEN kelas_jabatan IS NOT NULL THEN kelas_jabatan ELSE 0 END DESC')
                ->orderByRaw('CASE 
                    WHEN jenis_jabatan_id = 1 THEN 1 
                    WHEN jenis_jabatan_id = 3 THEN 2 
                    WHEN jenis_jabatan_id = 2 THEN 3 
                    WHEN jenis_jabatan_id = 4 THEN 4 
                    ELSE 5 END ASC')
                ->orderByRaw('CASE WHEN pangkat_minimal IS NOT NULL THEN pangkat_minimal ELSE 0 END DESC')
                ->orderBy('jabatan', 'asc')
                ->paginate(15)
                ->withQueryString();

            return view('peta-jabatan.manage.index', [
                'title' => 'Kelola Jabatan',
                'slug' => 'jabatan',
                'rows' => $rows,
                'unitKerjas' => UnitKerja::orderBy('order', 'asc')->orderBy('unit_kerja', 'asc')->get(),
                'jenisJabatans' => JenisJabatan::orderBy('jenis_jabatan')->get(),
                'filters' => [
                    'search' => $search,
                    'unit_kerja_id' => $unitKerjaId,
                    'jenis_jabatan_id' => $jenisJabatanId,
                    'status_jabatan' => $statusJabatan,
                ],
            ]);
        }

        return view('peta-jabatan.manage.index', [
            'title' => 'Kelola Career Path',
            'slug' => $slug,
            'rows' => CareerPath::query()
                ->with(['jabatan_asal', 'jabatan_tujuan'])
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(string $slug): View
    {
        if ($slug === 'jabatan' || $slug === 'peta-jabatan') {
            $unitKerjaId = request('unit_kerja_id');
            $atasanId = request('atasan_id') ?: request('atasan_langsung_id');

            $jabatan = new Jabatan([
                'kebutuhan_pegawai' => 1,
                'status_jabatan' => 'Aktif',
            ]);

            if ($unitKerjaId) {
                $jabatan->unit_kerja_id = (int) $unitKerjaId;
            }
            if ($atasanId) {
                $jabatan->atasan_langsung_id = (int) $atasanId;
            }

            return view('peta-jabatan.manage.jabatan-form', [
                'title' => 'Tambah Jabatan',
                'slug' => 'jabatan',
                'jabatan' => $jabatan,
                'jenisJabatans' => JenisJabatan::orderBy('jenis_jabatan')->get(),
                'unitKerjas' => UnitKerja::orderBy('order', 'asc')->orderBy('unit_kerja', 'asc')->get(),
                'pangkats' => Pangkat::orderBy('id')->get(),
                'atasanOptions' => $this->getStrukturalAtasanOptions(),
                'action' => route('peta-jabatan.manage.store', ['slug' => 'jabatan']),
                'method' => 'POST',
                'isEdit' => false,
            ]);
        }

        return view('peta-jabatan.manage.placeholder', [
            'title' => 'Tambah Career Path',
        ]);
    }

    public function store(string $slug, JabatanRequest $request): RedirectResponse
    {
        if ($slug === 'jabatan' || $slug === 'peta-jabatan') {
            $validated = $request->validated();
            $petaData = [
                'unit_kerja_id' => $validated['unit_kerja_id'] ?? null,
                'atasan_langsung_id' => $validated['atasan_langsung_id'] ?? null,
                'kebutuhan_pegawai' => $validated['kebutuhan_pegawai'] ?? 0,
            ];

            $jabatan = Jabatan::create(Arr::except($validated, ['unit_kerja_id', 'atasan_langsung_id', 'kebutuhan_pegawai']));

            $jabatan->peta_jabatan()->create($petaData);

            Alert::success('Berhasil', 'Data jabatan ' . $jabatan->jabatan . ' berhasil ditambahkan.');

            return redirect()->route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]);
        }

        abort(501, 'Fitur penyimpanan ' . $slug . ' belum tersedia.');
    }

    public function show(string $slug, int $id): View
    {
        if ($slug === 'jabatan' || $slug === 'peta-jabatan') {
            $jabatan = Jabatan::query()
                ->with([
                    'peta_jabatan.unit_kerja',
                    'peta_jabatan.atasan_langsung.unit_kerja',
                    'bawahan.unit_kerja',
                    'pangkat_minimal_rel',
                    'pegawais.pangkat',
                    'pegawais.user',
                    'careerPaths.jabatan_tujuan',
                ])
                ->withCount('pegawais')
                ->findOrFail($id);

            $allJabatans = Jabatan::query()
                ->with('unit_kerja')
                ->where('id', '!=', $id)
                ->orderByKelasJabatanDesc()
                ->get();

            return view('peta-jabatan.manage.jabatan-show', [
                'title' => 'Detail Jabatan: ' . $jabatan->jabatan,
                'slug' => 'jabatan',
                'jabatan' => $jabatan,
                'allJabatans' => $allJabatans,
            ]);
        }

        return view('peta-jabatan.manage.placeholder', [
            'title' => 'Detail Career Path',
            'recordId' => $id,
        ]);
    }

    public function searchPegawais(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('q', ''));
        $excludeJabatanId = $request->integer('exclude_jabatan_id');
        $page = max((int) $request->get('page', 1), 1);
        $perPage = 15;

        $query = Pegawai::query()
            ->with(['jabatan', 'unit_kerja'])
            ->when($excludeJabatanId > 0, function ($q) use ($excludeJabatanId) {
                $q->where(function ($sub) use ($excludeJabatanId) {
                    $sub->whereNull('jabatan_id')
                        ->orWhere('jabatan_id', '!=', $excludeJabatanId);
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('nip', 'like', "%{$search}%")
                          ->orWhere('nama', 'like', "%{$search}%")
                          ->orWhere('nidn', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama');

        $total = (clone $query)->count();

        $pegawais = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $pegawais->map(function (Pegawai $pegawai) {
                $nama = $pegawai->nama_lengkap ?: $pegawai->nama;
                $nip = $pegawai->nip ?: ($pegawai->nidn ?: '-');
                $currentJabatan = $pegawai->jabatan?->jabatan ? " (Jabatan Saat Ini: {$pegawai->jabatan->jabatan})" : " (Belum ada jabatan)";

                return [
                    'id' => $pegawai->id,
                    'text' => "{$nip} - {$nama}{$currentJabatan}",
                    'nama' => $nama,
                    'nip' => $pegawai->nip ?? '',
                    'current_jabatan' => $pegawai->jabatan?->jabatan ?? 'Belum ada jabatan',
                    'unit_kerja' => $pegawai->unit_kerja?->unit_kerja ?? '-',
                ];
            }),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    public function assignPegawai(int $id, Request $request): RedirectResponse
    {
        $jabatan = Jabatan::findOrFail($id);

        $validated = $request->validate([
            'pegawai_id' => ['required', 'exists:pegawais,id'],
        ], [
            'pegawai_id.required' => 'Pilih pegawai yang akan ditambahkan.',
            'pegawai_id.exists' => 'Pegawai yang dipilih tidak valid.',
        ]);

        $pegawai = Pegawai::findOrFail($validated['pegawai_id']);

        if ((int) $pegawai->jabatan_id === (int) $jabatan->id) {
            Alert::info('Info', "Pegawai {$pegawai->nama_lengkap} sudah menduduki jabatan ini.");
            return redirect()->back();
        }

        $previousJabatan = $pegawai->jabatan?->jabatan;

        $pegawai->jabatan_id = $jabatan->id;
        if ($jabatan->unit_kerja_id) {
            $pegawai->unit_kerja_id = $jabatan->unit_kerja_id;
        }
        $pegawai->save();

        $msg = "Pegawai {$pegawai->nama_lengkap} berhasil ditambahkan ke jabatan {$jabatan->jabatan}.";
        if ($previousJabatan) {
            $msg .= " (Sebelumnya di jabatan: {$previousJabatan})";
        }

        Alert::success('Berhasil', $msg);

        return redirect()->route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]);
    }

    public function pindahPegawai(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'target_jabatan_id' => ['required', 'exists:jabatans,id'],
            'current_jabatan_id' => ['nullable', 'exists:jabatans,id'],
        ], [
            'pegawai_id.required' => 'Pegawai tidak ditemukan.',
            'target_jabatan_id.required' => 'Pilih jabatan tujuan.',
            'target_jabatan_id.exists' => 'Jabatan tujuan tidak valid.',
        ]);

        $pegawai = Pegawai::findOrFail($validated['pegawai_id']);
        $targetJabatan = Jabatan::findOrFail($validated['target_jabatan_id']);

        if ((int) $pegawai->jabatan_id === (int) $targetJabatan->id) {
            Alert::warning('Perhatian', "Pegawai {$pegawai->nama_lengkap} sudah berada di jabatan {$targetJabatan->jabatan}.");
            return redirect()->back();
        }

        $oldJabatanName = $pegawai->jabatan?->jabatan ?? 'Belum ada jabatan';

        $pegawai->jabatan_id = $targetJabatan->id;
        if ($targetJabatan->unit_kerja_id) {
            $pegawai->unit_kerja_id = $targetJabatan->unit_kerja_id;
        }
        $pegawai->save();

        Alert::success('Berhasil', "Pegawai {$pegawai->nama_lengkap} berhasil dipindahkan dari jabatan '{$oldJabatanName}' ke '{$targetJabatan->jabatan}'.");

        $returnJabatanId = $validated['current_jabatan_id'] ?? $targetJabatan->id;
        return redirect()->route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $returnJabatanId]);
    }

    public function edit(string $slug, int $id): View
    {
        if ($slug === 'jabatan' || $slug === 'peta-jabatan') {
            $jabatan = Jabatan::with('peta_jabatan')->findOrFail($id);

            return view('peta-jabatan.manage.jabatan-form', [
                'title' => 'Edit Jabatan: ' . $jabatan->jabatan,
                'slug' => 'jabatan',
                'jabatan' => $jabatan,
                'jenisJabatans' => JenisJabatan::orderBy('jenis_jabatan')->get(),
                'unitKerjas' => UnitKerja::orderBy('order', 'asc')->orderBy('unit_kerja', 'asc')->get(),
                'pangkats' => Pangkat::orderBy('id')->get(),
                'atasanOptions' => $this->getStrukturalAtasanOptions($id),
                'action' => route('peta-jabatan.manage.update', ['slug' => 'jabatan', 'id' => $id]),
                'method' => 'PUT',
                'isEdit' => true,
            ]);
        }

        return view('peta-jabatan.manage.placeholder', [
            'title' => 'Edit Career Path',
            'recordId' => $id,
        ]);
    }

    public function update(string $slug, int $id, JabatanRequest $request): RedirectResponse
    {
        if ($slug === 'jabatan' || $slug === 'peta-jabatan') {
            $jabatan = Jabatan::findOrFail($id);
            $validated = $request->validated();

            $jabatan->update(Arr::except($validated, ['unit_kerja_id', 'atasan_langsung_id', 'kebutuhan_pegawai']));

            $jabatan->peta_jabatan()->updateOrCreate([], [
                'unit_kerja_id' => $validated['unit_kerja_id'] ?? null,
                'atasan_langsung_id' => $validated['atasan_langsung_id'] ?? null,
                'kebutuhan_pegawai' => $validated['kebutuhan_pegawai'] ?? 0,
            ]);

            Alert::success('Berhasil', 'Data jabatan ' . $jabatan->jabatan . ' berhasil diperbarui.');

            return redirect()->route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]);
        }

        abort(501, 'Fitur pembaruan ' . $slug . ' belum tersedia untuk ID ' . $id . '.');
    }

    public function destroy(string $slug, int $id): RedirectResponse
    {
        if ($slug === 'jabatan' || $slug === 'peta-jabatan') {
            $jabatan = Jabatan::query()
                ->withCount(['pegawais', 'bawahan'])
                ->findOrFail($id);

            if ($jabatan->pegawais_count > 0) {
                Alert::error('Gagal Hapus', "Jabatan '{$jabatan->jabatan}' tidak dapat dihapus karena masih diduduki oleh {$jabatan->pegawais_count} pegawai.");
                return redirect()->back();
            }

            if ($jabatan->bawahan_count > 0) {
                Alert::error('Gagal Hapus', "Jabatan '{$jabatan->jabatan}' tidak dapat dihapus karena merupakan atasan langsung dari {$jabatan->bawahan_count} jabatan lain.");
                return redirect()->back();
            }

            $jabatan->peta_jabatan()->delete();
            $namaJabatan = $jabatan->jabatan;
            $jabatan->delete();

            Alert::success('Berhasil', "Data jabatan '{$namaJabatan}' berhasil dihapus.");

            if (url()->previous() && str_contains(url()->previous(), 'peta-jabatan') && !str_contains(url()->previous(), 'peta-jabatan/manage')) {
                return redirect()->route('peta-jabatan.index');
            }

            return redirect()->route('peta-jabatan.manage.index', ['slug' => 'jabatan']);
        }

        abort(501, 'Fitur hapus ' . $slug . ' belum tersedia untuk ID ' . $id . '.');
    }

    private function getStrukturalAtasanOptions(?int $excludeId = null)
    {
        $query = Jabatan::query()
            ->with('unit_kerja')
            ->where(function ($q) {
                $q->where('jenis_jabatan_id', 1)
                  ->orWhere('jabatan', 'like', '%Direktur%')
                  ->orWhere('jabatan', 'like', '%Kepala%')
                  ->orWhere('jabatan', 'like', '%Ketua%')
                  ->orWhere('jabatan', 'like', '%Koordinator%')
                  ->orWhere('jabatan', 'like', '%Sekretaris%')
                  ->orWhere('jabatan', 'like', '%Dekan%')
                  ->orWhere('jabatan', 'like', '%Pimpinan%')
                  ->orWhere('jabatan', 'like', '%Manager%')
                  ->orWhere('jabatan', 'like', '%Wakil%');
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $results = $query->orderByKelasJabatanDesc()->get();

        if ($results->isEmpty()) {
            $fallbackQuery = Jabatan::query()->with('unit_kerja');
            if ($excludeId) {
                $fallbackQuery->where('id', '!=', $excludeId);
            }
            return $fallbackQuery->orderByKelasJabatanDesc()->get();
        }

        return $results;
    }
}
