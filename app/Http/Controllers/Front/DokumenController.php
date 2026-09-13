<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\DokumenRequest;
use App\Models\Dokumen;
use Illuminate\Database\QueryException;
use RealRashid\SweetAlert\Facades\Alert;

class DokumenController extends Controller
{
    public function index()
    {
        $paginate = 10;
        $keyword = trim((string) request()->input('search', ''));

        return view('dokumen.index', [
            'dokumens' => Dokumen::query()
                ->withCount('dokumenPegawais')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($innerQuery) use ($keyword) {
                        $innerQuery->where('kode_dokumen', 'like', '%' . $keyword . '%')
                            ->orWhere('nama_dokumen', 'like', '%' . $keyword . '%');
                    });
                })
                ->orderBy('nama_dokumen')
                ->paginate($paginate)
                ->withQueryString()
                ->onEachSide(0),
            'title' => 'Dokumen',
            'keyword' => $keyword,
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create()
    {
        return view('dokumen.create', [
            'title' => 'Tambah Dokumen',
            'dokumen' => new Dokumen(),
        ]);
    }

    public function store(DokumenRequest $request)
    {
        try {
            $dokumen = Dokumen::create($request->validated());

            Alert::success('Success', 'Nama dokumen berhasil ditambahkan.');

            return redirect()->route('dokumen.show', ['dokumen' => $dokumen]);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Nama dokumen gagal disimpan.');

            return redirect()->back()->withInput();
        }
    }

    public function show(Dokumen $dokumen)
    {
        return view('dokumen.show', [
            'title' => 'Detail Dokumen',
            'dokumen' => $dokumen->loadCount('dokumenPegawais'),
        ]);
    }

    public function edit(Dokumen $dokumen)
    {
        return view('dokumen.edit', [
            'title' => 'Edit Dokumen',
            'dokumen' => $dokumen,
        ]);
    }

    public function update(DokumenRequest $request, Dokumen $dokumen)
    {
        try {
            $dokumen->update($request->validated());

            Alert::success('Success', 'Nama dokumen berhasil diperbarui.');

            return redirect()->route('dokumen.show', ['dokumen' => $dokumen]);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Nama dokumen gagal diperbarui.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Dokumen $dokumen)
    {
        try {
            $dokumen->delete();

            Alert::success('Success', 'Nama dokumen berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Nama dokumen gagal dihapus. Pastikan tidak ada dokumen pegawai atau syarat layanan yang masih terhubung.');
        }

        return redirect()->route('dokumen.index');
    }
}
