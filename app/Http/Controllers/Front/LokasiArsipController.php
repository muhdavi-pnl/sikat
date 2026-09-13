<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\LokasiArsipRequest;
use App\Models\LokasiArsip;
use App\Models\Pegawai;
use App\Models\Rak;
use Illuminate\Database\QueryException;
use RealRashid\SweetAlert\Facades\Alert;

class LokasiArsipController extends Controller
{
    public function index()
    {
        $paginate = 10;
        $keyword = trim((string) request()->input('search', ''));

        return view('lokasi-arsip.index', [
            'title' => 'Lokasi Arsip',
            'keyword' => $keyword,
            'lokasiArsips' => LokasiArsip::query()
                ->with(['pegawai', 'rak.lemari.ruang.gedung'])
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($innerQuery) use ($keyword) {
                        $innerQuery->where('keterangan', 'like', '%' . $keyword . '%')
                            ->orWhereHas('pegawai', function ($pegawaiQuery) use ($keyword) {
                                $pegawaiQuery->where('nama', 'like', '%' . $keyword . '%')
                                    ->orWhere('nip', 'like', '%' . $keyword . '%');
                            })
                            ->orWhereHas('rak', function ($rakQuery) use ($keyword) {
                                $rakQuery->where('rak', 'like', '%' . $keyword . '%')
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
                    });
                })
                ->orderByDesc('created_at')
                ->paginate($paginate)
                ->withQueryString()
                ->onEachSide(0),
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create()
    {
        return view('lokasi-arsip.create', [
            'title' => 'Tambah Lokasi Arsip',
            'lokasiArsip' => new LokasiArsip(),
            'pegawais' => Pegawai::query()->orderBy('nama')->get(),
            'raks' => Rak::query()->with('lemari.ruang.gedung')->orderBy('rak')->get(),
        ]);
    }

    public function store(LokasiArsipRequest $request)
    {
        try {
            $lokasiArsip = LokasiArsip::create($request->validated());

            Alert::success('Success', 'Lokasi arsip berhasil ditambahkan.');

            return redirect()->route('lokasi-arsip.show', $lokasiArsip);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Lokasi arsip gagal disimpan.');

            return redirect()->back()->withInput();
        }
    }

    public function show(LokasiArsip $lokasiArsip)
    {
        return view('lokasi-arsip.show', [
            'title' => 'Detail Lokasi Arsip',
            'lokasiArsip' => $lokasiArsip->load(['pegawai', 'rak.lemari.ruang.gedung']),
        ]);
    }

    public function edit(LokasiArsip $lokasiArsip)
    {
        return view('lokasi-arsip.edit', [
            'title' => 'Edit Lokasi Arsip',
            'lokasiArsip' => $lokasiArsip->load(['pegawai', 'rak.lemari.ruang.gedung']),
            'pegawais' => Pegawai::query()->orderBy('nama')->get(),
            'raks' => Rak::query()->with('lemari.ruang.gedung')->orderBy('rak')->get(),
        ]);
    }

    public function update(LokasiArsipRequest $request, LokasiArsip $lokasiArsip)
    {
        try {
            $lokasiArsip->update($request->validated());

            Alert::success('Success', 'Lokasi arsip berhasil diperbarui.');

            return redirect()->route('lokasi-arsip.show', $lokasiArsip);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Lokasi arsip gagal diperbarui.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(LokasiArsip $lokasiArsip)
    {
        try {
            $lokasiArsip->delete();

            Alert::success('Success', 'Lokasi arsip berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Lokasi arsip gagal dihapus.');
        }

        return redirect()->route('lokasi-arsip.index');
    }
}
