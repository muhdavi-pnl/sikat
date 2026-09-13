<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\LemariRequest;
use App\Models\Lemari;
use App\Models\Ruang;
use Illuminate\Database\QueryException;
use RealRashid\SweetAlert\Facades\Alert;

class LemariController extends Controller
{
    public function index()
    {
        $paginate = 10;
        $keyword = trim((string) request()->input('search', ''));

        return view('lemari.index', [
            'title' => 'Lemari',
            'keyword' => $keyword,
            'lemaris' => Lemari::query()
                ->with(['ruang.gedung'])
                ->withCount('raks')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($innerQuery) use ($keyword) {
                        $innerQuery->where('lemari', 'like', '%' . $keyword . '%')
                            ->orWhere('keterangan', 'like', '%' . $keyword . '%')
                            ->orWhereHas('ruang', function ($ruangQuery) use ($keyword) {
                                $ruangQuery->where('nama_ruang', 'like', '%' . $keyword . '%')
                                    ->orWhere('kode_ruang', 'like', '%' . $keyword . '%')
                                    ->orWhereHas('gedung', function ($gedungQuery) use ($keyword) {
                                        $gedungQuery->where('nama_gedung', 'like', '%' . $keyword . '%');
                                    });
                            });
                    });
                })
                ->orderBy('lemari')
                ->paginate($paginate)
                ->withQueryString()
                ->onEachSide(0),
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create()
    {
        return view('lemari.create', [
            'title' => 'Tambah Lemari',
            'lemari' => new Lemari(),
            'ruangs' => Ruang::query()->with('gedung')->orderBy('kode_ruang')->get(),
        ]);
    }

    public function store(LemariRequest $request)
    {
        try {
            $lemari = Lemari::create($request->validated());

            Alert::success('Success', 'Lemari berhasil ditambahkan.');

            return redirect()->route('lemari.show', $lemari);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Lemari gagal disimpan.');

            return redirect()->back()->withInput();
        }
    }

    public function show(Lemari $lemari)
    {
        return view('lemari.show', [
            'title' => 'Detail Lemari',
            'lemari' => $lemari->load(['ruang.gedung', 'raks'])->loadCount('raks'),
        ]);
    }

    public function edit(Lemari $lemari)
    {
        return view('lemari.edit', [
            'title' => 'Edit Lemari',
            'lemari' => $lemari->load('ruang.gedung'),
            'ruangs' => Ruang::query()->with('gedung')->orderBy('kode_ruang')->get(),
        ]);
    }

    public function update(LemariRequest $request, Lemari $lemari)
    {
        try {
            $lemari->update($request->validated());

            Alert::success('Success', 'Lemari berhasil diperbarui.');

            return redirect()->route('lemari.show', $lemari);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Lemari gagal diperbarui.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Lemari $lemari)
    {
        try {
            $lemari->delete();

            Alert::success('Success', 'Lemari berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Lemari gagal dihapus. Pastikan tidak ada rak yang masih terhubung.');
        }

        return redirect()->route('lemari.index');
    }
}
