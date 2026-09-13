<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\GedungRequest;
use App\Models\Gedung;
use Illuminate\Database\QueryException;
use RealRashid\SweetAlert\Facades\Alert;

class GedungController extends Controller
{
    public function index()
    {
        $paginate = 10;
        $keyword = trim((string) request()->input('search', ''));

        return view('gedung.index', [
            'title' => 'Gedung',
            'keyword' => $keyword,
            'gedungs' => Gedung::query()
                ->withCount('ruangs')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($innerQuery) use ($keyword) {
                        $innerQuery->where('nama_gedung', 'like', '%' . $keyword . '%')
                            ->orWhere('alamat_gedung', 'like', '%' . $keyword . '%')
                            ->orWhere('keterangan', 'like', '%' . $keyword . '%');
                    });
                })
                ->orderBy('nama_gedung')
                ->paginate($paginate)
                ->withQueryString()
                ->onEachSide(0),
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create()
    {
        return view('gedung.create', [
            'title' => 'Tambah Gedung',
            'gedung' => new Gedung(),
        ]);
    }

    public function store(GedungRequest $request)
    {
        try {
            $gedung = Gedung::create($request->validated());

            Alert::success('Success', 'Gedung berhasil ditambahkan.');

            return redirect()->route('gedung.show', $gedung);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Gedung gagal disimpan.');

            return redirect()->back()->withInput();
        }
    }

    public function show(Gedung $gedung)
    {
        return view('gedung.show', [
            'title' => 'Detail Gedung',
            'gedung' => $gedung->load(['ruangs'])->loadCount('ruangs'),
        ]);
    }

    public function edit(Gedung $gedung)
    {
        return view('gedung.edit', [
            'title' => 'Edit Gedung',
            'gedung' => $gedung,
        ]);
    }

    public function update(GedungRequest $request, Gedung $gedung)
    {
        try {
            $gedung->update($request->validated());

            Alert::success('Success', 'Gedung berhasil diperbarui.');

            return redirect()->route('gedung.show', $gedung);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Gedung gagal diperbarui.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Gedung $gedung)
    {
        try {
            $gedung->delete();

            Alert::success('Success', 'Gedung berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Gedung gagal dihapus. Pastikan tidak ada ruang yang masih terhubung.');
        }

        return redirect()->route('gedung.index');
    }
}
