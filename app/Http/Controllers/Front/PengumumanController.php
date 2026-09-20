<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengumumanRequest;
use App\Models\Pengumuman;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class PengumumanController extends Controller
{
    public function index(Request $request): View
    {
        $paginate = 10;
        $keyword = trim((string) $request->input('search', ''));
        $tipe = trim((string) $request->input('tipe', ''));
        $status = $request->input('status');

        $query = Pengumuman::query()->with('creator')->latest();

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', '%' . $keyword . '%')
                    ->orWhere('isi', 'like', '%' . $keyword . '%');
            });
        }

        if (in_array($tipe, ['teks', 'gambar', 'keduanya'], true)) {
            $query->where('tipe', $tipe);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_aktif', (bool) $status);
        }

        $pengumumans = $query->paginate($paginate)
            ->withQueryString()
            ->onEachSide(0);

        return view('pengumuman.index', [
            'pengumumans' => $pengumumans,
            'title' => 'Pengumuman',
            'keyword' => $keyword,
            'tipe' => $tipe,
            'status' => $status,
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create(): View
    {
        return view('pengumuman.create', [
            'title' => 'Tambah Pengumuman',
            'pengumuman' => new Pengumuman([
                'tipe' => 'teks',
                'is_aktif' => true,
                'target_role' => 'semua',
            ]),
        ]);
    }

    public function store(PengumumanRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();
            $data['is_aktif'] = $request->has('is_aktif') ? (bool) $request->input('is_aktif') : true;

            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = 'pengumuman_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                $destinationPath = public_path('uploads/pengumuman');

                if (! File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $data['gambar'] = 'uploads/pengumuman/' . $filename;
            }

            $pengumuman = Pengumuman::create($data);

            Alert::success('Berhasil', 'Pengumuman baru berhasil ditambahkan.');

            return redirect()->route('pengumuman.show', ['pengumuman' => $pengumuman]);
        } catch (\Throwable $exception) {
            report($exception);
            Alert::error('Gagal', 'Terjadi kesalahan saat menyimpan pengumuman.');

            return redirect()->back()->withInput();
        }
    }

    public function show(Pengumuman $pengumuman): View
    {
        $pengumuman->load('creator');

        return view('pengumuman.show', [
            'title' => 'Detail Pengumuman',
            'pengumuman' => $pengumuman,
        ]);
    }

    public function edit(Pengumuman $pengumuman): View
    {
        return view('pengumuman.edit', [
            'title' => 'Edit Pengumuman',
            'pengumuman' => $pengumuman,
        ]);
    }

    public function update(PengumumanRequest $request, Pengumuman $pengumuman): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['is_aktif'] = $request->has('is_aktif') ? (bool) $request->input('is_aktif') : false;

            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = 'pengumuman_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                $destinationPath = public_path('uploads/pengumuman');

                if (! File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);

                // Remove previous file if exists
                if ($pengumuman->gambar && File::exists(public_path($pengumuman->gambar))) {
                    File::delete(public_path($pengumuman->gambar));
                }

                $data['gambar'] = 'uploads/pengumuman/' . $filename;
            }

            $pengumuman->update($data);

            Alert::success('Berhasil', 'Pengumuman berhasil diperbarui.');

            return redirect()->route('pengumuman.show', ['pengumuman' => $pengumuman]);
        } catch (\Throwable $exception) {
            report($exception);
            Alert::error('Gagal', 'Terjadi kesalahan saat memperbarui pengumuman.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        try {
            if ($pengumuman->gambar && File::exists(public_path($pengumuman->gambar))) {
                File::delete(public_path($pengumuman->gambar));
            }

            $pengumuman->delete();

            Alert::success('Berhasil', 'Pengumuman berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Gagal', 'Pengumuman gagal dihapus.');
        }

        return redirect()->route('pengumuman.index');
    }

    public function toggleStatus(Pengumuman $pengumuman, Request $request)
    {
        $pengumuman->is_aktif = ! $pengumuman->is_aktif;
        $pengumuman->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_aktif' => $pengumuman->is_aktif,
                'message' => 'Status pengumuman berhasil diubah.',
            ]);
        }

        Alert::success('Berhasil', 'Status pengumuman berhasil diubah.');

        return redirect()->back();
    }

    public function dismissPopup(Request $request): JsonResponse
    {
        $request->session()->put('pengumuman_popup_dismissed', true);

        return response()->json([
            'success' => true,
            'message' => 'Pop-up pengumuman berhasil ditutup untuk sesi ini.',
        ]);
    }
}
