<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepegawaianPegawaiDokumenUpdateRequest;
use App\Http\Requests\KepegawaianPegawaiDokumenUploadRequest;
use App\Models\Dokumen;
use App\Models\DokumenPegawai;
use App\Models\Pegawai;
use App\Services\Intelligence\PegawaiInsightService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RealRashid\SweetAlert\Facades\Alert;
use Throwable;

class KepegawaianDokumenPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));

        $pegawais = Pegawai::query()
            ->with(['unit_kerja'])
            ->addSelect([
                'latest_document_uploaded_at' => DokumenPegawai::query()
                    ->selectRaw('MAX(updated_at)')
                    ->whereColumn('pegawai_id', 'pegawais.id'),
            ])
            ->withCount([
                'dokumenPegawais',
                'dokumenPegawais as dokumen_valid_count' => function ($query) {
                    $query->where('status', true);
                },
            ])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($innerQuery) use ($keyword) {
                    $innerQuery->where('nip', 'like', '%' . $keyword . '%')
                        ->orWhere('nama', 'like', '%' . $keyword . '%')
                        ->orWhereHas('unit_kerja', function ($unitKerjaQuery) use ($keyword) {
                            $unitKerjaQuery->where('unit_kerja', 'like', '%' . $keyword . '%');
                        });
                });
            })
            ->orderByRaw('CASE WHEN latest_document_uploaded_at IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('latest_document_uploaded_at')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('kepegawaian.dokumen.index', [
            'title' => 'Review Dokumen Pegawai',
            'pegawais' => $pegawais,
            'keyword' => $keyword,
        ]);
    }

    public function show(Pegawai $pegawai)
    {
        return view('kepegawaian.dokumen.show', [
            'title' => 'Kelola Dokumen Pegawai',
            'pegawai' => $pegawai,
            'insight' => app(PegawaiInsightService::class)->analyze($pegawai),
            'dokumenOptions' => Dokumen::orderBy('nama_dokumen')->get(),
            'dokumenUploads' => DokumenPegawai::query()
                ->with(['dokumen', 'uploader.roles'])
                ->where('pegawai_id', $pegawai->id)
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->get(),
            'pegawaiNipCipher' => Crypt::encryptString($pegawai->nip),
        ]);
    }

    public function store(KepegawaianPegawaiDokumenUploadRequest $request, Pegawai $pegawai)
    {
        $storedPath = null;

        try {
            $validated = $request->validated();
            $dokumen = Dokumen::findOrFail($validated['dokumen_id']);
            $uploadedFile = $request->file('file');

            if (!$uploadedFile || !$uploadedFile->isValid()) {
                Alert::error('Error', 'File dokumen tidak valid atau gagal diunggah.');

                return redirect()->back()->withInput();
            }

            $targetDirectory = public_path('file/' . $pegawai->nip);
            File::ensureDirectoryExists($targetDirectory);

            $existingUpload = $this->dokumenUpload($pegawai, $dokumen);
            if ($existingUpload && $existingUpload->file && File::exists($targetDirectory . '/' . $existingUpload->file)) {
                File::delete($targetDirectory . '/' . $existingUpload->file);
            }

            $fileName = $this->generateFileName($dokumen, $uploadedFile->getClientOriginalExtension());
            $uploadedFile->move($targetDirectory, $fileName);
            $storedPath = $targetDirectory . '/' . $fileName;

            DB::table('dokumen_pegawai')->updateOrInsert(
                [
                    'dokumen_id' => $dokumen->id,
                    'pegawai_id' => $pegawai->id,
                ],
                [
                    'user_id' => auth()->id(),
                    'file' => $fileName,
                    'nomor' => $validated['nomor'] ?? null,
                    'tanggal' => $validated['tanggal'] ?? null,
                    'status' => (bool) $validated['status'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'updated_at' => now(),
                    'created_at' => optional($existingUpload)->created_at ?? now(),
                ]
            );

            Alert::success('Success', 'Dokumen pegawai berhasil disimpan.');
        } catch (Throwable $exception) {
            if ($storedPath && File::exists($storedPath)) {
                File::delete($storedPath);
            }

            report($exception);
            Alert::error('Error', 'Dokumen pegawai gagal disimpan.');

            return redirect()->back()->withInput();
        }

        return redirect()->route('kepegawaian.dokumen.show', $pegawai);
    }

    public function edit(Pegawai $pegawai, Dokumen $dokumen)
    {
        $dokumenUpload = $this->dokumenUpload($pegawai, $dokumen);
        abort_if(!$dokumenUpload, 404);

        return view('kepegawaian.dokumen.edit', [
            'title' => 'Review Dokumen Pegawai',
            'pegawai' => $pegawai,
            'dokumen' => $dokumen,
            'dokumenUpload' => $dokumenUpload->load(['uploader.roles']),
            'pegawaiNipCipher' => Crypt::encryptString($pegawai->nip),
        ]);
    }

    public function update(KepegawaianPegawaiDokumenUpdateRequest $request, Pegawai $pegawai, Dokumen $dokumen)
    {
        $dokumenUpload = $this->dokumenUpload($pegawai, $dokumen);
        abort_if(!$dokumenUpload, 404);

        $storedPath = null;

        try {
            $validated = $request->validated();
            $fileName = $dokumenUpload->file;
            $targetDirectory = public_path('file/' . $pegawai->nip);
            File::ensureDirectoryExists($targetDirectory);

            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');

                if (!$uploadedFile || !$uploadedFile->isValid()) {
                    Alert::error('Error', 'File dokumen tidak valid atau gagal diunggah.');

                    return redirect()->back()->withInput();
                }

                if ($fileName && File::exists($targetDirectory . '/' . $fileName)) {
                    File::delete($targetDirectory . '/' . $fileName);
                }

                $fileName = $this->generateFileName($dokumen, $uploadedFile->getClientOriginalExtension());
                $uploadedFile->move($targetDirectory, $fileName);
                $storedPath = $targetDirectory . '/' . $fileName;
            }

            DB::table('dokumen_pegawai')
                ->where('dokumen_id', $dokumen->id)
                ->where('pegawai_id', $pegawai->id)
                ->update([
                    'user_id' => auth()->id(),
                    'file' => $fileName,
                    'nomor' => $validated['nomor'] ?? null,
                    'tanggal' => $validated['tanggal'] ?? null,
                    'status' => (bool) $validated['status'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'updated_at' => now(),
                ]);

            Alert::success('Success', 'Dokumen pegawai berhasil diperbarui.');
        } catch (Throwable $exception) {
            if ($storedPath && File::exists($storedPath)) {
                File::delete($storedPath);
            }

            report($exception);
            Alert::error('Error', 'Dokumen pegawai gagal diperbarui.');

            return redirect()->back()->withInput();
        }

        return redirect()->route('kepegawaian.dokumen.show', $pegawai);
    }

    protected function dokumenUpload(Pegawai $pegawai, Dokumen $dokumen): ?DokumenPegawai
    {
        /** @var DokumenPegawai|null $dokumenUpload */
        $dokumenUpload = DokumenPegawai::query()
            ->where('pegawai_id', $pegawai->id)
            ->where('dokumen_id', $dokumen->id)
            ->first();

        return $dokumenUpload;
    }

    protected function generateFileName(Dokumen $dokumen, string $extension): string
    {
        return $dokumen->kode_dokumen . '_' . uniqid('', true) . '.' . strtolower($extension);
    }
}

