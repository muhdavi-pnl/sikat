<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\RakRequest;
use App\Models\Lemari;
use App\Models\Rak;
use Illuminate\Database\QueryException;
use RealRashid\SweetAlert\Facades\Alert;

class RakController extends Controller
{
    public function index()
    {
        $paginate = 10;
        $keyword = trim((string) request()->input('search', ''));

        return view('rak.index', [
            'title' => 'Rak',
            'keyword' => $keyword,
            'raks' => Rak::query()
                ->with(['lemari.ruang.gedung'])
                ->withCount('lokasiArsips')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($innerQuery) use ($keyword) {
                        $innerQuery->where('rak', 'like', '%' . $keyword . '%')
                            ->orWhere('keterangan', 'like', '%' . $keyword . '%')
                            ->orWhereHas('lemari', function ($lemariQuery) use ($keyword) {
                                $lemariQuery->where('lemari', 'like', '%' . $keyword . '%')
                                    ->orWhereHas('ruang', function ($ruangQuery) use ($keyword) {
                                        $ruangQuery->where('nama_ruang', 'like', '%' . $keyword . '%')
                                            ->orWhere('kode_ruang', 'like', '%' . $keyword . '%')
                                            ->orWhereHas('gedung', function ($gedungQuery) use ($keyword) {
                                                $gedungQuery->where('nama_gedung', 'like', '%' . $keyword . '%');
                                            });
                                    });
                            });
                    });
                })
                ->orderBy('rak')
                ->paginate($paginate)
                ->withQueryString()
                ->onEachSide(0),
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create()
    {
        return view('rak.create', [
            'title' => 'Tambah Rak',
            'rak' => new Rak(),
            'lemaris' => Lemari::query()->with('ruang.gedung')->orderBy('lemari')->get(),
        ]);
    }

    public function store(RakRequest $request)
    {
        try {
            $rak = Rak::create($request->validated());

            Alert::success('Success', 'Rak berhasil ditambahkan.');

            return redirect()->route('rak.show', $rak);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Rak gagal disimpan.');

            return redirect()->back()->withInput();
        }
    }

    public function show(Rak $rak)
    {
        return view('rak.show', [
            'title' => 'Detail Rak',
            'rak' => $rak->load(['lemari.ruang.gedung', 'lokasiArsips.pegawai'])->loadCount('lokasiArsips'),
        ]);
    }

    public function edit(Rak $rak)
    {
        return view('rak.edit', [
            'title' => 'Edit Rak',
            'rak' => $rak->load('lemari.ruang.gedung'),
            'lemaris' => Lemari::query()->with('ruang.gedung')->orderBy('lemari')->get(),
        ]);
    }

    public function update(RakRequest $request, Rak $rak)
    {
        try {
            $rak->update($request->validated());

            Alert::success('Success', 'Rak berhasil diperbarui.');

            return redirect()->route('rak.show', $rak);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Rak gagal diperbarui.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Rak $rak)
    {
        try {
            $rak->delete();

            Alert::success('Success', 'Rak berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Rak gagal dihapus. Pastikan tidak ada lokasi arsip yang masih terhubung.');
        }

        return redirect()->route('rak.index');
    }
}
