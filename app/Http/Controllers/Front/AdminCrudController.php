<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\JabatanRequest;
use App\Models\CareerPath;
use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\UnitKerja;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class AdminCrudController extends Controller
{
    public function index(string $slug): View
    {
        if ($slug === 'jabatan') {
            $search = trim((string) request('search', ''));
            $unitKerjaId = request('unit_kerja_id');
            $jenisJabatanId = request('jenis_jabatan_id');
            $statusJabatan = request('status_jabatan');

            $query = Jabatan::query()
                ->with(['jenis_jabatan', 'unit_kerja', 'atasan_langsung'])
                ->withCount('pegawais');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('jabatan', 'like', "%{$search}%")
                        ->orWhere('kode_jabatan', 'like', "%{$search}%")
                        ->orWhere('jenjang_jabatan', 'like', "%{$search}%");
                });
            }

            if (!empty($unitKerjaId)) {
                $query->where('unit_kerja_id', $unitKerjaId);
            }

            if (!empty($jenisJabatanId)) {
                $query->where('jenis_jabatan_id', $jenisJabatanId);
            }

            if (!empty($statusJabatan)) {
                $query->where('status_jabatan', $statusJabatan);
            }

            $rows = $query->orderBy('jabatan')->paginate(15)->withQueryString();

            return view('peta-jabatan.manage.index', [
                'title' => 'Kelola Jabatan',
                'slug' => $slug,
                'rows' => $rows,
                'unitKerjas' => UnitKerja::orderBy('unit_kerja')->get(),
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
        if ($slug === 'jabatan') {
            return view('peta-jabatan.manage.jabatan-form', [
                'title' => 'Tambah Jabatan',
                'slug' => $slug,
                'jabatan' => new Jabatan([
                    'kebutuhan_pegawai' => 1,
                    'status_jabatan' => 'Aktif',
                ]),
                'jenisJabatans' => JenisJabatan::orderBy('jenis_jabatan')->get(),
                'unitKerjas' => UnitKerja::orderBy('unit_kerja')->get(),
                'atasanOptions' => Jabatan::orderBy('jabatan')->get(),
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
        if ($slug === 'jabatan') {
            $validated = $request->validated();
            $validated['kebutuhan_pegawai'] = $validated['kebutuhan_pegawai'] ?? 0;

            $jabatan = Jabatan::create($validated);

            Alert::success('Berhasil', 'Data jabatan ' . $jabatan->jabatan . ' berhasil ditambahkan.');

            return redirect()->route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]);
        }

        abort(501, 'Fitur penyimpanan ' . $slug . ' belum tersedia.');
    }

    public function show(string $slug, int $id): View
    {
        if ($slug === 'jabatan') {
            $jabatan = Jabatan::query()
                ->with([
                    'jenis_jabatan',
                    'unit_kerja',
                    'atasan_langsung.unit_kerja',
                    'bawahan.jenis_jabatan',
                    'bawahan.unit_kerja',
                    'pegawais.pangkat',
                    'pegawais.user',
                    'careerPaths.jabatan_tujuan',
                ])
                ->withCount('pegawais')
                ->findOrFail($id);

            return view('peta-jabatan.manage.jabatan-show', [
                'title' => 'Detail Jabatan: ' . $jabatan->jabatan,
                'slug' => $slug,
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
        if ($slug === 'jabatan') {
            $jabatan = Jabatan::findOrFail($id);

            return view('peta-jabatan.manage.jabatan-form', [
                'title' => 'Edit Jabatan: ' . $jabatan->jabatan,
                'slug' => $slug,
                'jabatan' => $jabatan,
                'jenisJabatans' => JenisJabatan::orderBy('jenis_jabatan')->get(),
                'unitKerjas' => UnitKerja::orderBy('unit_kerja')->get(),
                'atasanOptions' => Jabatan::where('id', '!=', $id)->orderBy('jabatan')->get(),
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
        if ($slug === 'jabatan') {
            $jabatan = Jabatan::findOrFail($id);
            $validated = $request->validated();
            $validated['kebutuhan_pegawai'] = $validated['kebutuhan_pegawai'] ?? 0;

            $jabatan->update($validated);

            Alert::success('Berhasil', 'Data jabatan ' . $jabatan->jabatan . ' berhasil diperbarui.');

            return redirect()->route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]);
        }

        abort(501, 'Fitur pembaruan ' . $slug . ' belum tersedia untuk ID ' . $id . '.');
    }

    public function destroy(string $slug, int $id): RedirectResponse
    {
        if ($slug === 'jabatan') {
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

            $namaJabatan = $jabatan->jabatan;
            $jabatan->delete();

            Alert::success('Berhasil', "Data jabatan '{$namaJabatan}' berhasil dihapus.");

            return redirect()->route('peta-jabatan.manage.index', ['slug' => 'jabatan']);
        }

        abort(501, 'Fitur hapus ' . $slug . ' belum tersedia untuk ID ' . $id . '.');
    }
}
