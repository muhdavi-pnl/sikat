<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\JabatanRequest;
use App\Models\CareerPath;
use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\Pangkat;
use App\Models\UnitKerja;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RealRashid\SweetAlert\Facades\Alert;

class AdminCrudController extends Controller
{
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

            return view('peta-jabatan.manage.jabatan-show', [
                'title' => 'Detail Jabatan: ' . $jabatan->jabatan,
                'slug' => 'jabatan',
                'jabatan' => $jabatan,
            ]);
        }

        return view('peta-jabatan.manage.placeholder', [
            'title' => 'Detail Career Path',
            'recordId' => $id,
        ]);
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

        $results = $query->orderBy('jabatan')->get();

        if ($results->isEmpty()) {
            $fallbackQuery = Jabatan::query()->with('unit_kerja');
            if ($excludeId) {
                $fallbackQuery->where('id', '!=', $excludeId);
            }
            return $fallbackQuery->orderBy('jabatan')->get();
        }

        return $results;
    }
}
