<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\RuangRequest;
use App\Models\Gedung;
use App\Models\Ruang;
use Illuminate\Database\QueryException;
use RealRashid\SweetAlert\Facades\Alert;

class RuangController extends Controller
{
    public function index()
    {
        $paginate = 10;
        $keyword = trim((string) request()->input('search', ''));

        return view('ruang.index', [
            'title' => 'Ruang',
            'keyword' => $keyword,
            'ruangs' => Ruang::query()
                ->with(['gedung'])
                ->withCount('lemaris')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($innerQuery) use ($keyword) {
                        $innerQuery->where('kode_ruang', 'like', '%' . $keyword . '%')
                            ->orWhere('nama_ruang', 'like', '%' . $keyword . '%')
                            ->orWhere('keterangan', 'like', '%' . $keyword . '%')
                            ->orWhereHas('gedung', function ($gedungQuery) use ($keyword) {
                                $gedungQuery->where('nama_gedung', 'like', '%' . $keyword . '%');
                            });
                    });
                })
                ->orderBy('kode_ruang')
                ->paginate($paginate)
                ->withQueryString()
                ->onEachSide(0),
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create()
    {
        return view('ruang.create', [
            'title' => 'Tambah Ruang',
            'ruang' => new Ruang(),
            'gedungs' => Gedung::query()->orderBy('nama_gedung')->get(),
        ]);
    }

    public function store(RuangRequest $request)
    {
        try {
            $ruang = Ruang::create($request->validated());

            Alert::success('Success', 'Ruang berhasil ditambahkan.');

            return redirect()->route('ruang.show', $ruang);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Ruang gagal disimpan.');

            return redirect()->back()->withInput();
        }
    }

    public function show(Ruang $ruang)
    {
        return view('ruang.show', [
            'title' => 'Detail Ruang',
            'ruang' => $ruang->load(['gedung', 'lemaris'])->loadCount('lemaris'),
        ]);
    }

    public function edit(Ruang $ruang)
    {
        return view('ruang.edit', [
            'title' => 'Edit Ruang',
            'ruang' => $ruang->load('gedung'),
            'gedungs' => Gedung::query()->orderBy('nama_gedung')->get(),
        ]);
    }

    public function update(RuangRequest $request, Ruang $ruang)
    {
        try {
            $ruang->update($request->validated());

            Alert::success('Success', 'Ruang berhasil diperbarui.');

            return redirect()->route('ruang.show', $ruang);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Ruang gagal diperbarui.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Ruang $ruang)
    {
        try {
            $ruang->delete();

            Alert::success('Success', 'Ruang berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Ruang gagal dihapus. Pastikan tidak ada lemari yang masih terhubung.');
        }

        return redirect()->route('ruang.index');
    }
}
