<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Agama;
use App\Models\Eselon;
use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\Kabupaten;
use App\Models\KedudukanPegawai;
use App\Models\Kecamatan;
use App\Models\KelompokKeahlian;
use App\Models\Kelurahan;
use App\Models\Pangkat;
use App\Models\Pegawai;
use App\Models\PegawaiCutiQuota;
use App\Models\Dokumen;
use App\Models\DokumenPegawai;
use App\Models\Layanan;
use App\Models\LayananPegawai;
use App\Models\Pendidikan;
use App\Models\Provinsi;
use App\Models\ProgramStudi;
use App\Models\StatusPerkawinan;
use App\Models\UnitKerja;
use App\Models\User;
use App\Http\Requests\PegawaiRequest;
use App\Http\Requests\PegawaiProfileRequest;
use App\Http\Requests\PegawaiPasswordUpdateRequest;
use App\Http\Requests\PegawaiDokumenUploadRequest;
use App\Http\Requests\PegawaiLayananUsulanRequest;
use App\Http\Requests\KepegawaianLayananProcessRequest;
use App\Services\CutiService;
use App\Services\Intelligence\LayananEligibilityService;
use App\Services\Intelligence\PegawaiInsightService;
use App\Services\Intelligence\SmartTriageService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Throwable;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $pegawais = $this->pegawaiQuery($this->resolveSearchTerm(request()))->get();

            return DataTables::of($pegawais)
                ->addIndexColumn()
                ->editColumn('nama', function ($pegawais) {
                    return strtoupper($pegawais->nama);
                })
                ->editColumn('jabatan_fungsional', function ($pegawais) {
                    if ($pegawais->jabatan_fungsional == null) {
                        return '<span class="badge badge-secondary">Tenaga Pengajar</span>';
                    } else if ($pegawais->jabatan_fungsional == 'asisten ahli') {
                        return '<span class="badge badge-danger">Asisten Ahli</span>';
                    } else if ($pegawais->jabatan_fungsional == 'lektor') {
                        return '<span class="badge badge-primary">Lektor</span>';
                    } else if ($pegawais->jabatan_fungsional == 'lektor kepala') {
                        return '<span class="badge badge-info">Lektor Kepala</span>';
                    } else {
                        return '<span class="badge badge-warning">Profesor</span>';
                    }
                })
                ->addColumn('jurusan', function ($pegawais) {
                    return optional(optional($pegawais->program_studi)->jurusan)->jurusan ?: '<span class="text-muted">-</span>';
                })
                ->addColumn('action', function ($pegawais) {
                    return '<a href="' . route("kepegawaian.pegawai.show", $pegawais->id) . '" class="btn btn-icon btn-info" title="Detail Pegawai"><i class="fas fa-info-circle"></i></a>
                            <a href="' . route("kepegawaian.pegawai.edit", $pegawais->id) . '" class="btn btn-icon btn-primary" title="Edit Pegawai"><i class="fas fa-edit"></i></a>
                            <form action="' . route("kepegawaian.pegawai.destroy", $pegawais->id) . '" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Data pegawai ini akan dihapus dari daftar pegawai." data-confirm-item-label="Nama Pegawai" data-confirm-item-name="' . e($pegawais->nama) . '" data-confirm-button="Ya, hapus">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn btn-icon btn-danger" title="Hapus Pegawai"><i class="fas fa-trash-alt"></i></button>
                            </form>';
                })
                ->rawColumns(['jabatan_fungsional', 'jurusan', 'action'])
                ->make();
        }

        return view('kepegawaian.pegawai.index', [
            'title' => 'Pegawai',
        ]);
    }

    public function export()
    {
        Gate::authorize('manage-pegawai');

        $fileName = 'pegawai-' . now()->format('Ymd-His') . '.csv';
        $pegawais = $this->pegawaiQuery($this->resolveSearchTerm(request()))->get();

        // Audit Trail untuk ekspor data pegawai
        app(\App\Services\AuditService::class)->logSecurity(
            'security.data_export',
            'Mengekspor data master pegawai ke format CSV (' . $pegawais->count() . ' baris)',
            [
                'total_rows' => $pegawais->count(),
                'file_name' => $fileName,
                'classification' => 'CONFIDENTIAL_EXPORT',
            ],
            'success'
        );

        return response()->streamDownload(function () use ($pegawais) {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['NIK', 'NIP', 'NUPTK', 'NIDN', 'Nama', 'ID Google Scholar', 'Jabatan Fungsional', 'Program Studi', 'Unit Kerja', 'Status Pegawai']);

            foreach ($pegawais as $pegawai) {
                fputcsv($output, [
                    $pegawai->nik,
                    $pegawai->nip,
                    $pegawai->nuptk,
                    $pegawai->nidn,
                    $pegawai->nama,
                    $pegawai->id_gscholar,
                    $pegawai->jabatan_fungsional,
                    optional($pegawai->program_studi)->nama_prodi,
                    optional($pegawai->unit_kerja)->unit_kerja,
                    $pegawai->status_pegawai,
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function print()
    {
        Gate::authorize('manage-pegawai');

        $pegawais = $this->pegawaiQuery($this->resolveSearchTerm(request()))->get();

        // Audit Trail untuk pencetakan data pegawai
        app(\App\Services\AuditService::class)->logSecurity(
            'security.data_print',
            'Membuka antarmuka cetak data pegawai (' . $pegawais->count() . ' pegawai)',
            [
                'total_records' => $pegawais->count(),
                'classification' => 'CONFIDENTIAL_PRINT',
            ],
            'success'
        );

        return view('kepegawaian.pegawai.print', [
            'pegawais' => $pegawais,
            'title' => 'Cetak Data Pegawai',
        ]);
    }

    public function create()
    {
        return view('kepegawaian.pegawai.create', $this->formData([
            'pegawai' => new Pegawai(),
            'title' => 'Tambah Pegawai',
        ]));
    }

    public function store(PegawaiRequest $request)
    {
        try {
            $payload = $this->validatedPayload($request);
            $pegawai = Pegawai::create($payload);
            $this->saveExternalIdentifiers($pegawai, $request->validated());
            $this->saveCutiQuotas($pegawai, $request->input('cuti_quotas', []));

            Alert::success('Success', 'Data pegawai berhasil disimpan!');

            return redirect()->route('kepegawaian.pegawai.show', $pegawai);
        } catch (QueryException $exception) {
            Alert::error('Error', 'Data pegawai gagal disimpan!');

            return redirect()->back()->withInput();
        }
    }

    public function show(Pegawai $pegawai)
    {
        $pegawai->loadMissing([
            'kelurahan_asal.kecamatan.kabupaten.provinsi',
            'kelurahan.kecamatan.kabupaten.provinsi',
            'studiLanjuts',
            'jenis_jabatan',
            'jabatan_rangkap',
            'identitas.kelompok_keahlian',
            'pendidikan.perguruan_tinggi',
            'pendidikan.tingkat_pendidikan',
            'kedudukan_pegawai',
            'eselon',
            'agama',
            'status_perkawinan',
            'pangkat',
            'unit_kerja',
            'program_studi',
            'cutiQuotas',
        ]);

        // Audit trail akses data spesifik pegawai
        app(\App\Services\Security\PegawaiDataProtectionService::class)->logSensitiveDataAccess(
            $pegawai,
            'Melihat detail data & profil pegawai'
        );

        return view('kepegawaian.pegawai.show', [
            'pegawai'       => $pegawai,
            'title'         => 'Detail Pegawai',
            'cutiBreakdown' => app(CutiService::class)->getCutiBreakdown($pegawai),
        ]);
    }

    /**
     * Endpoint API aman untuk membuka data sensitif ter-masking dengan verifikasi & audit log.
     */
    public function revealSensitiveData(Request $request, Pegawai $pegawai)
    {
        if (! $pegawai->canViewSensitiveData(auth()->user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk membuka data sensitif ini.',
            ], 403);
        }

        // Catat pembukaan data sensitif ke audit log
        app(\App\Services\Security\PegawaiDataProtectionService::class)->logSensitiveDataAccess(
            $pegawai,
            'Membuka (Unmask) Data Sensitif PII Pegawai di Layar'
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'nik' => $pegawai->nik ?: '-',
                'npwp' => $pegawai->npwp ?: '-',
                'bpjs' => $pegawai->bpjs ?: '-',
                'no_hp' => $pegawai->no_hp ?: '-',
                'no_telp' => $pegawai->no_telp ?: '-',
                'alamat' => $pegawai->alamat ?: '-',
                'alamat_asal' => $pegawai->alamat_asal ?: '-',
                'tanggal_lahir' => optional($pegawai->tanggal_lahir)->isoFormat('D MMMM Y') ?: '-',
            ],
        ]);
    }

    public function edit(Pegawai $pegawai)
    {
        $pegawai->loadMissing([
            'jabatan',
            'jabatan_rangkap',
            'program_studi.jurusan',
            'user',
        ]);

        return view('kepegawaian.pegawai.edit', $this->formData([
            'pegawai' => $pegawai,
            'title' => 'Edit Pegawai',
        ]));
    }

    public function update(PegawaiRequest $request, Pegawai $pegawai)
    {
        try {
            $payload = $this->validatedPayload($request);

            $pegawai->update($payload);
            $this->saveExternalIdentifiers($pegawai, $request->validated());
            $this->saveCutiQuotas($pegawai, $request->input('cuti_quotas', []));

            Alert::success('Success', 'Data pegawai berhasil diperbarui!');

            return redirect()->route('kepegawaian.pegawai.show', $pegawai);
        } catch (QueryException $exception) {
            Alert::error('Error', 'Data pegawai gagal diperbarui!');

            return redirect()->back()->withInput();
        }
    }

    public function updateCutiQuota(Request $request, Pegawai $pegawai)
    {
        Gate::authorize('manage-pegawai');

        $currentYear = (int) now()->year;

        $validated = $request->validate([
            'cuti_quotas' => ['required', 'array'],
            'cuti_quotas.' . $currentYear => ['required', 'integer', 'min:0', 'max:12'],
            'cuti_quotas.' . ($currentYear - 1) => ['required', 'integer', 'min:0', 'max:6'],
            'cuti_quotas.' . ($currentYear - 2) => ['required', 'integer', 'min:0', 'max:6'],
        ], [], [
            'cuti_quotas.' . $currentYear => 'jatah cuti tahun ' . $currentYear . ' (N)',
            'cuti_quotas.' . ($currentYear - 1) => 'jatah cuti tahun ' . ($currentYear - 1) . ' (N-1)',
            'cuti_quotas.' . ($currentYear - 2) => 'jatah cuti tahun ' . ($currentYear - 2) . ' (N-2)',
        ]);

        $this->saveCutiQuotas($pegawai, $validated['cuti_quotas']);

        Alert::success('Success', 'Jatah cuti pegawai berhasil diperbarui!');

        return back()->with('success', 'Jatah cuti pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        try {
            $pegawai->delete();

            Alert::success('Success', 'Data pegawai berhasil dihapus!');
        } catch (QueryException $exception) {
            Alert::error('Error', 'Data pegawai gagal dihapus!');
        }

        return redirect()->route('kepegawaian.pegawai');
    }

    public function profile()
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        return view('pegawai.profile', [
            'pegawai' => $pegawai,
            'title' => 'Profil Pegawai',
            'unitKerjas' => UnitKerja::orderBy('order', 'asc')->orderBy('unit_kerja', 'asc')->get(),
            'programStudis' => ProgramStudi::orderBy('nama_prodi')->limit(250)->get(),
            'jabatanFungsionalOptions' => Pegawai::jabatanFungsionalOptions(),
            'insight' => $pegawai ? app(PegawaiInsightService::class)->analyze($pegawai) : null,
            'cutiBreakdown' => $pegawai ? app(CutiService::class)->getCutiBreakdown($pegawai) : null,
        ] + $this->alamatReferenceData($pegawai));
    }

    public function updateProfile(PegawaiProfileRequest $request)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            Alert::info('Info', 'Data pegawai Anda belum tersedia.');

            return redirect()->route('pegawai.profile');
        }

        try {
            $pegawai->update($this->validatedProfilePayload($request));
            $this->saveExternalIdentifiers($pegawai, $request->validated());

            Alert::success('Success', 'Profil pegawai berhasil diperbarui.');
        } catch (QueryException $exception) {
            Alert::error('Error', 'Profil pegawai gagal diperbarui.');

            return redirect()->back()->withInput();
        }

        return redirect()->route('pegawai.profile');
    }

    public function updatePassword(PegawaiPasswordUpdateRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()
                ->back()
                ->withErrors([
                    'current_password' => 'Password saat ini tidak sesuai.',
                ], 'updatePassword');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        app(\App\Services\AuditService::class)->logAuth(
            eventType: 'auth.password_changed',
            action: "Pengguna {$user->name} berhasil mengubah password akun",
            user: $user,
            status: 'success'
        );

        Alert::success('Success', 'Password berhasil diperbarui.');

        return redirect()->to(route('pegawai.profile') . '#password-security');
    }

    public function dokumen()
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        return view('pegawai.dokumen', [
            'title' => 'Dokumen Pegawai',
            'pegawai' => $pegawai,
            'insight' => $pegawai ? app(PegawaiInsightService::class)->analyze($pegawai) : null,
            'dokumenOptions' => Dokumen::orderBy('nama_dokumen')->get(),
            'dokumenUploads' => $pegawai
                ? DokumenPegawai::query()
                    ->with(['dokumen', 'uploader.roles'])
                    ->where('pegawai_id', $pegawai->id)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('created_at')
                    ->get()
                : collect(),
            'pegawaiNipCipher' => $pegawai ? Crypt::encryptString($pegawai->nip) : null,
        ]);
    }

    public function storeDokumen(PegawaiDokumenUploadRequest $request)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            Alert::info('Info', 'Data pegawai Anda belum tersedia.');

            return redirect()->route('pegawai.dokumen');
        }

        $storedPath = null;

        try {
            $validated = $request->validated();

            $dokumen = Dokumen::findOrFail($validated['dokumen_id']);
            $uploadedFile = $request->file('file');

            if (!$uploadedFile || !$uploadedFile->isValid()) {
                Alert::error('Error', 'File dokumen tidak valid atau gagal diunggah.');

                return redirect()->back()->withInput();
            }

            $fileName = $dokumen->kode_dokumen . '_' . uniqid('', true) . '.' . strtolower($uploadedFile->getClientOriginalExtension());
            $targetDirectory = public_path('file/' . $pegawai->nip);
            File::ensureDirectoryExists($targetDirectory);

            $existingUpload = DokumenPegawai::query()
                ->where('dokumen_id', $dokumen->id)
                ->where('pegawai_id', $pegawai->id)
                ->first();

            if ($existingUpload && $existingUpload->file && File::exists($targetDirectory . '/' . $existingUpload->file)) {
                File::delete($targetDirectory . '/' . $existingUpload->file);
            }

            $uploadedFile->move($targetDirectory, $fileName);
            $storedPath = $targetDirectory . '/' . $fileName;

            DB::table('dokumen_pegawai')->updateOrInsert(
                [
                    'dokumen_id' => $dokumen->id,
                    'pegawai_id' => $pegawai->id,
                ],
                [
                    'user_id' => $user->id,
                    'file' => $fileName,
                    'nomor' => $validated['nomor'] ?? null,
                    'tanggal' => $validated['tanggal'] ?? null,
                    'status' => DokumenPegawai::STATUS_PENDING,
                    'keterangan' => $validated['keterangan'] ?? null,
                    'alasan_penolakan' => null,
                    'updated_at' => now(),
                    'created_at' => optional($existingUpload)->created_at ?? now(),
                ]
            );

            Alert::success('Success', 'Dokumen berhasil diunggah.');
        } catch (Throwable $exception) {
            if ($storedPath && File::exists($storedPath)) {
                File::delete($storedPath);
            }

            report($exception);
            Alert::error('Error', 'Dokumen gagal diunggah.');

            return redirect()->back()->withInput();
        }

        return redirect()->route('pegawai.dokumen');
    }

    public function layanan()
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        return view('pegawai.layanan', [
            'pegawai' => $pegawai,
            'title' => 'Layanan Pegawai',
            'riwayatCutis' => $pegawai
                ? $pegawai->cutiLayananPegawais()
                    ->with(['layananPegawai.layanan', 'layananPegawai.processor', 'layananPegawai.outputUploader'])
                    ->orderByDesc('created_at')
                    ->get()
                : collect(),
            'riwayatLayanans' => $pegawai
                ? LayananPegawai::query()
                    ->with(['layanan', 'processor.roles', 'outputUploader'])
                    ->where('pegawai_id', $pegawai->id)
                    ->whereDoesntHave('cutiDetail')
                    ->orderByDesc('created_at')
                    ->get()
                : collect(),
        ]);
    }

    public function reviewLayananUsulan(Layanan $layanan)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            Alert::info('Info', 'Data pegawai Anda belum tersedia.');

            return redirect()->route('pegawai.layanan');
        }

        $draft = session()->get($this->layananUsulanDraftSessionKey($pegawai, $layanan), []);

        if (!is_array($draft) || empty($draft)) {
            Alert::info('Info', 'Silakan lengkapi ceklist persyaratan terlebih dahulu.');

            return redirect()->route('layanan.usul', $layanan->id);
        }

        $pegawai->loadMissing(['unit_kerja', 'program_studi', 'jabatan', 'pangkat']);
        $layanan->loadMissing('syarat.dokumen');

        $draftUploads = collect($this->normalizeDraftSyaratUploads((array) ($draft['syarat_uploads'] ?? [])));

        $precheck = app(LayananEligibilityService::class)->analyze(
            $pegawai,
            $layanan,
            $draftUploads->pluck('syarat_id')->map(function ($id) {
                return (int) $id;
            })->all(),
            $draft
        );

        $isCutiLayanan = $this->isCutiLayanan($layanan);
        $cutiTanggalMulai = data_get($draft, 'cuti_tanggal_mulai');
        $cutiTanggalSelesai = data_get($draft, 'cuti_tanggal_selesai');
        $cutiHariDiminta = isset($draft['cuti_hari_diminta']) ? (int) $draft['cuti_hari_diminta'] : null;
        $cutiHariTersedia = isset($draft['cuti_hari_tersedia']) ? (int) $draft['cuti_hari_tersedia'] : $this->resolveCutiHariTersedia($pegawai);
        $cutiJenis = trim((string) data_get($draft, 'cuti_jenis', 'tahunan')) ?: 'tahunan';
        $cutiAlasan = trim((string) data_get($draft, 'cuti_alasan', data_get($draft, 'catatan_pengusul', 'Permohonan Cuti'))) ?: 'Permohonan Cuti';
        $cutiAlamat = trim((string) data_get($draft, 'cuti_alamat', optional($pegawai)->alamat ?: '-')) ?: (optional($pegawai)->alamat ?: '-');
        $cutiNoTelp = trim((string) data_get($draft, 'cuti_no_telp', optional($pegawai)->no_hp ?: optional($pegawai)->no_telp ?: '-')) ?: (optional($pegawai)->no_hp ?: optional($pegawai)->no_telp ?: '-');

        if (
            $isCutiLayanan
            && (
                !$cutiTanggalMulai
                || !$cutiTanggalSelesai
                || $cutiHariDiminta === null
                || $cutiHariDiminta <= 0
            )
        ) {
            Alert::error('Error', 'Mohon lengkapi data rentang tanggal cuti terlebih dahulu agar formulir dapat dibuat.');

            return redirect()->route('layanan.usul', $layanan->id)->withInput();
        }

        if (!$precheck['eligible']) {
            $this->clearLayananUsulanDraft(session(), $pegawai, $layanan, $draft);
            Alert::error('Error', 'Persyaratan layanan berubah. Silakan cek kembali sebelum mengirim usulan.');

            return redirect()->route('layanan.usul', $layanan->id);
        }

        return view('layanan.create', [
            'draft' => $draft,
            'draftUploadsBySyaratId' => $draftUploads->keyBy(function (array $upload) {
                return (int) ($upload['syarat_id'] ?? 0);
            }),
            'layanan' => $layanan,
            'pegawai' => $pegawai,
            'precheck' => $precheck,
            'isCutiLayanan' => $isCutiLayanan,
            'cutiTanggalMulai' => $cutiTanggalMulai,
            'cutiTanggalSelesai' => $cutiTanggalSelesai,
            'cutiHariDiminta' => $cutiHariDiminta,
            'cutiHariTersedia' => $cutiHariTersedia,
            'cutiJenis' => $cutiJenis,
            'cutiAlasan' => $cutiAlasan,
            'cutiAlamat' => $cutiAlamat,
            'cutiNoTelp' => $cutiNoTelp,
            'step' => 'review',
            'title' => 'Review Usulan Layanan',
        ]);
    }

    public function previewLayananUsulan(Request $request, Layanan $layanan)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            Alert::info('Info', 'Data pegawai Anda belum tersedia.');

            return redirect()->route('pegawai.layanan');
        }

        $isCutiLayanan = $this->isCutiLayanan($layanan);

        $validated = $request->validate(array_merge([
            'catatan_pengusul' => ['nullable', 'string', 'max:1000'],
            'syarat_files' => ['nullable', 'array'],
            'syarat_files.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ], $isCutiLayanan ? [
            'cuti_jenis' => ['nullable', 'string', 'in:' . implode(',', array_keys(CutiService::jenisCutiOptions()))],
            'cuti_alasan' => ['nullable', 'string', 'max:2000'],
            'cuti_alamat' => ['nullable', 'string', 'max:1000'],
            'cuti_no_telp' => ['nullable', 'string', 'max:50'],
            'cuti_tanggal_mulai' => ['required', 'date'],
            'cuti_tanggal_selesai' => ['required', 'date', 'after_or_equal:cuti_tanggal_mulai'],
        ] : []));

        $layanan->loadMissing('syarat.dokumen');
        $existingDraft = session()->get($this->layananUsulanDraftSessionKey($pegawai, $layanan), []);
        $existingDraftUploads = $this->normalizeDraftSyaratUploads((array) ($existingDraft['syarat_uploads'] ?? []));

        $cutiHariTersedia = $this->resolveCutiHariTersedia($pegawai);
        $cutiTanggalMulai = $isCutiLayanan ? (string) $validated['cuti_tanggal_mulai'] : null;
        $cutiTanggalSelesai = $isCutiLayanan ? (string) $validated['cuti_tanggal_selesai'] : null;
        $cutiJenis = $isCutiLayanan ? (trim((string) ($validated['cuti_jenis'] ?? 'tahunan')) ?: 'tahunan') : null;
        $cutiAlasan = $isCutiLayanan ? (trim((string) ($validated['cuti_alasan'] ?? ($validated['catatan_pengusul'] ?? 'Permohonan Cuti'))) ?: 'Permohonan Cuti') : null;
        $cutiAlamat = $isCutiLayanan ? (trim((string) ($validated['cuti_alamat'] ?? ($pegawai->alamat ?: '-'))) ?: ($pegawai->alamat ?: '-')) : null;
        $cutiNoTelp = $isCutiLayanan ? (trim((string) ($validated['cuti_no_telp'] ?? ($pegawai->no_hp ?: ($pegawai->no_telp ?: '-')))) ?: ($pegawai->no_hp ?: ($pegawai->no_telp ?: '-'))) : null;
        $cutiHariDiminta = $isCutiLayanan
            ? $this->calculateCutiHariDiminta($cutiTanggalMulai, $cutiTanggalSelesai)
            : null;

        if ($isCutiLayanan && $cutiHariDiminta <= 0) {
            Alert::error('Error', 'Rentang tanggal cuti tidak menghasilkan hari kerja. Hanya hari kerja yang dihitung sebagai cuti, dan tanggal libur yang dikonfigurasi akan dikecualikan.');

            return redirect()->route('layanan.usul', $layanan->id)->withInput();
        }

        if ($isCutiLayanan && $cutiHariDiminta > $cutiHariTersedia) {
            Alert::error('Error', 'Jumlah hari cuti yang diajukan melebihi sisa cuti tersedia.');

            return redirect()->route('layanan.usul', $layanan->id)->withInput();
        }

        $syaratFiles = (array) $request->file('syarat_files', []);
        $syaratEvidenceIds = collect(array_keys($syaratFiles))
            ->map(function ($id) {
                return (int) $id;
            })
            ->merge(collect($existingDraftUploads)->pluck('syarat_id'))
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        $precheck = app(LayananEligibilityService::class)->analyze($pegawai, $layanan, $syaratEvidenceIds, [
            'cuti_jenis' => $cutiJenis,
            'cuti_alamat' => $cutiAlamat,
            'cuti_alasan' => $cutiAlasan,
            'cuti_hari_diminta' => $cutiHariDiminta,
            'cuti_no_telp' => $cutiNoTelp,
            'cuti_tanggal_mulai' => $cutiTanggalMulai,
            'cuti_tanggal_selesai' => $cutiTanggalSelesai,
        ]);

        if (!$precheck['eligible']) {
            Alert::error('Error', $precheck['summary'] . '. Lengkapi syarat yang belum terpenuhi terlebih dahulu.');

            return redirect()->route('layanan.usul', $layanan->id)->withInput();
        }

        $allowedUploadSyaratIds = collect($precheck['requirements'])
            ->filter(function (array $requirement) {
                return $requirement['source'] === 'upload';
            })
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $newDraftUploads = $this->storeTemporaryLayananSyaratUploads($syaratFiles, $allowedUploadSyaratIds, $pegawai, $layanan);
        $newDraftUploadsBySyaratId = collect($newDraftUploads)->keyBy(function (array $upload) {
            return (int) ($upload['syarat_id'] ?? 0);
        });

        $mergedDraftUploads = collect($existingDraftUploads)
            ->filter(function (array $upload) use ($allowedUploadSyaratIds) {
                return in_array((int) ($upload['syarat_id'] ?? 0), $allowedUploadSyaratIds, true);
            })
            ->keyBy(function (array $upload) {
                return (int) ($upload['syarat_id'] ?? 0);
            });

        $replacedDraftUploads = $mergedDraftUploads
            ->only($newDraftUploadsBySyaratId->keys()->all())
            ->values()
            ->all();

        $removedDraftUploads = collect($existingDraftUploads)
            ->filter(function (array $upload) use ($allowedUploadSyaratIds) {
                return !in_array((int) ($upload['syarat_id'] ?? 0), $allowedUploadSyaratIds, true);
            })
            ->values()
            ->all();

        foreach ($newDraftUploadsBySyaratId as $syaratId => $upload) {
            $mergedDraftUploads->put($syaratId, $upload);
        }

        $this->deleteTemporaryDraftUploads(array_merge($replacedDraftUploads, $removedDraftUploads));

        $draft = [
            'catatan_pengusul' => $validated['catatan_pengusul'] ?? null,
            'created_at' => now()->toDateTimeString(),
            'cuti_hari_diminta' => $cutiHariDiminta,
            'cuti_hari_tersedia' => $isCutiLayanan ? $cutiHariTersedia : null,
            'cuti_jenis' => $cutiJenis,
            'cuti_alamat' => $cutiAlamat,
            'cuti_alasan' => $cutiAlasan,
            'cuti_no_telp' => $cutiNoTelp,
            'cuti_tanggal_mulai' => $cutiTanggalMulai,
            'cuti_tanggal_selesai' => $cutiTanggalSelesai,
            'layanan_id' => $layanan->id,
            'pegawai_id' => $pegawai->id,
            'precheck' => $precheck,
            'syarat_uploads' => $mergedDraftUploads->values()->all(),
            'user_id' => $user->id,
        ];

        session()->put($this->layananUsulanDraftSessionKey($pegawai, $layanan), $draft);

        return redirect()->route('pegawai.layanan.review', $layanan->id);
    }

    public function storeLayananUsulan(PegawaiLayananUsulanRequest $request, Layanan $layanan)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;
        $validated = $request->validated();

        if (!$pegawai) {
            Alert::info('Info', 'Data pegawai Anda belum tersedia.');

            return redirect()->route('pegawai.layanan');
        }

        $draft = session()->get($this->layananUsulanDraftSessionKey($pegawai, $layanan), []);

        if (!is_array($draft) || empty($draft)) {
            Alert::info('Info', 'Silakan lengkapi ceklist persyaratan terlebih dahulu.');

            return redirect()->route('layanan.usul', $layanan->id);
        }

        if ((int) ($draft['pegawai_id'] ?? 0) !== (int) $pegawai->id || (int) ($draft['layanan_id'] ?? 0) !== (int) $layanan->id) {
            $this->clearLayananUsulanDraft(session(), $pegawai, $layanan, $draft);
            Alert::error('Error', 'Draft usulan tidak valid. Silakan ulangi proses usulan layanan.');

            return redirect()->route('layanan.usul', $layanan->id);
        }

        $layanan->loadMissing('syarat.dokumen');

        $draftUploads = collect($this->normalizeDraftSyaratUploads((array) ($draft['syarat_uploads'] ?? [])));

        $precheck = app(LayananEligibilityService::class)->analyze(
            $pegawai,
            $layanan,
            $draftUploads->pluck('syarat_id')->map(function ($id) {
                return (int) $id;
            })->all(),
            $draft
        );

        $isCutiLayanan = $this->isCutiLayanan($layanan);
        $cutiTanggalMulai = $draft['cuti_tanggal_mulai'] ?? null;
        $cutiTanggalSelesai = $draft['cuti_tanggal_selesai'] ?? null;
        $cutiHariDiminta = isset($draft['cuti_hari_diminta']) ? (int) $draft['cuti_hari_diminta'] : null;
        $cutiHariTersedia = isset($draft['cuti_hari_tersedia']) ? (int) $draft['cuti_hari_tersedia'] : $this->resolveCutiHariTersedia($pegawai);
        $cutiJenis = trim((string) ($draft['cuti_jenis'] ?? 'tahunan')) ?: 'tahunan';
        $cutiAlasan = trim((string) ($draft['cuti_alasan'] ?? ($draft['catatan_pengusul'] ?? 'Permohonan Cuti'))) ?: 'Permohonan Cuti';
        $cutiAlamat = trim((string) ($draft['cuti_alamat'] ?? ($pegawai->alamat ?: '-'))) ?: ($pegawai->alamat ?: '-');
        $cutiNoTelp = trim((string) ($draft['cuti_no_telp'] ?? ($pegawai->no_hp ?: ($pegawai->no_telp ?: '-')))) ?: ($pegawai->no_hp ?: ($pegawai->no_telp ?: '-'));

        if ($isCutiLayanan) {
            if (
                !$cutiTanggalMulai
                || !$cutiTanggalSelesai
                || $cutiHariDiminta === null
                || $cutiHariDiminta <= 0
            ) {
                Alert::error('Error', 'Data rentang tanggal cuti pada draft usulan belum lengkap.');

                return redirect()->route('layanan.usul', $layanan->id)->withInput();
            }

            if ($cutiHariDiminta > $cutiHariTersedia) {
                $this->clearLayananUsulanDraft(session(), $pegawai, $layanan, $draft);
                Alert::error('Error', 'Jumlah hari cuti yang diajukan melebihi sisa cuti tersedia. Silakan perbarui usulan.');

                return redirect()->route('layanan.usul', $layanan->id)->withInput();
            }
        }

        if (!$precheck['eligible']) {
            $this->clearLayananUsulanDraft(session(), $pegawai, $layanan, $draft);
            Alert::error('Error', $precheck['summary'] . '. Lengkapi syarat yang belum terpenuhi terlebih dahulu.');

            return redirect()->route('layanan.usul', $layanan->id)->withInput();
        }

        try {
            $usulan = DB::transaction(function () use ($draft, $draftUploads, $isCutiLayanan, $cutiHariDiminta, $cutiHariTersedia, $cutiJenis, $cutiAlasan, $cutiAlamat, $cutiNoTelp, $cutiTanggalMulai, $cutiTanggalSelesai, $layanan, $pegawai, $user, $validated) {
                $usulan = LayananPegawai::create([
                    'layanan_id' => $layanan->id,
                    'pegawai_id' => $pegawai->id,
                    'user_id' => $user->id,
                    'status' => LayananPegawai::STATUS_USULAN,
                    'catatan_pengusul' => $draft['catatan_pengusul'] ?? ($validated['catatan_pengusul'] ?? null),
                    'syarat_uploads' => $this->moveDraftUploadsToFinalLocation($draftUploads->all(), $pegawai),
                ]);

                if ($isCutiLayanan) {
                    $usulan->cutiDetail()->updateOrCreate(
                        ['layanan_pegawai_id' => $usulan->id],
                        [
                            'jenis_cuti' => $cutiJenis,
                            'alasan_cuti' => $cutiAlasan,
                            'alamat_menjalankan_cuti' => $cutiAlamat,
                            'nomor_telepon_cuti' => $cutiNoTelp,
                            'tanggal_mulai' => $cutiTanggalMulai,
                            'tanggal_selesai' => $cutiTanggalSelesai,
                            'hari_diminta' => $cutiHariDiminta,
                            'hari_tersedia_saat_usul' => $cutiHariTersedia,
                        ]
                    );
                }

                return $usulan;
            });

            app(SmartTriageService::class)->applyAndPersist($usulan);
        } catch (Throwable $exception) {
            report($exception);
            Alert::error('Error', 'Usulan layanan gagal dikirim. Silakan periksa kembali data usulan Anda.');

            return redirect()->route('pegawai.layanan.review', $layanan->id)->withInput();
        }

        $this->clearLayananUsulanDraft(session(), $pegawai, $layanan, $draft);

        Alert::success('Success', 'Usulan layanan berhasil dikirim.');

        return redirect()->route('pegawai.layanan.done', $usulan->id);
    }

    public function doneLayananUsulan(LayananPegawai $layananPegawai)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        abort_if(!$pegawai || (int) $layananPegawai->pegawai_id !== (int) $pegawai->id, 403);

        return view('layanan.selesai', [
            'title' => 'Usulan Layanan Terkirim',
            'usulan' => $layananPegawai->load(['cutiDetail', 'layanan.syarat', 'pegawai.unit_kerja', 'pegawai.program_studi', 'pegawai.jabatan', 'pegawai.pangkat']),
        ]);
    }

    public function printLayananCuti(LayananPegawai $layananPegawai)
    {
        $pegawai = optional(auth()->user())->pegawai;

        abort_if(!$pegawai || (int) $layananPegawai->pegawai_id !== (int) $pegawai->id, 403);

        $usulan = $layananPegawai->load([
            'cutiDetail',
            'layanan',
            'pegawai.unit_kerja',
            'pegawai.program_studi',
            'pegawai.jabatan.atasan_langsung',
            'pegawai.pangkat',
            'processor.pegawai.jabatan',
            'outputUploader.pegawai.jabatan',
        ]);

        abort_unless($usulan->cutiDetail && $usulan->layanan && $this->isCutiLayanan($usulan->layanan), 404);

        return view('pegawai.cuti-print', [
            'formData' => app(CutiService::class)->buildPrintableFormData($usulan),
            'title' => 'Formulir Permintaan dan Pemberian Cuti',
            'usulan' => $usulan,
        ]);
    }

    public function indexLayananProses(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));

        $query = LayananPegawai::query()
            ->with(['layanan', 'pegawai', 'pengusul', 'processor'])
            ->whereDoesntHave('cutiDetail')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($innerQuery) use ($keyword) {
                    $innerQuery->whereHas('pegawai', function ($pegawaiQuery) use ($keyword) {
                        $pegawaiQuery->where('nama', 'like', '%' . $keyword . '%');
                    })->orWhereHas('layanan', function ($layananQuery) use ($keyword) {
                        $layananQuery->where('layanan', 'like', '%' . $keyword . '%');
                    });
                });
            });

        app(SmartTriageService::class)->refreshForBuilder(clone $query);

        $usulans = $query
            ->orderByRaw("CASE
                WHEN sla_risk = 'high' THEN 0
                WHEN sla_risk = 'medium' THEN 1
                WHEN sla_risk = 'low' THEN 2
                ELSE 3
            END")
            ->orderByDesc('priority_score')
            ->orderByRaw('CASE WHEN sla_due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sla_due_at')
            ->orderByRaw("CASE
                WHEN status = 'usulan' THEN 0
                WHEN status = 'pending' THEN 1
                WHEN status = 'proses' THEN 2
                WHEN status = 'selesai' THEN 3
                WHEN status = 'ditolak' THEN 4
                ELSE 5
            END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('kepegawaian.layanan.index', [
            'title' => 'Proses Layanan Pegawai',
            'usulans' => $usulans,
            'keyword' => $keyword,
        ]);
    }

    public function indexCutiProses(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));

        $usulans = $this->cutiProcessingQuery($keyword)
            ->orderByRaw("CASE
                WHEN status = 'usulan' THEN 0
                WHEN status = 'pending' THEN 1
                WHEN status = 'proses' THEN 2
                WHEN status = 'selesai' THEN 3
                WHEN status = 'ditolak' THEN 4
                ELSE 5
            END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('kepegawaian.cuti.index', [
            'title' => 'Proses Cuti Pegawai',
            'usulans' => $usulans,
            'keyword' => $keyword,
        ]);
    }

    protected function layananUsulanDraftSessionKey(Pegawai $pegawai, Layanan $layanan): string
    {
        return 'pegawai.layanan.usulan_draft.' . $pegawai->id . '.' . $layanan->id;
    }

    protected function storeTemporaryLayananSyaratUploads(array $syaratFiles, array $allowedUploadSyaratIds, Pegawai $pegawai, Layanan $layanan): array
    {
        $draftDirectory = storage_path('app/tmp/layanan-usulan/' . $pegawai->id . '/' . $layanan->id);
        File::ensureDirectoryExists($draftDirectory);

        $draftUploads = [];

        foreach ($syaratFiles as $syaratId => $file) {
            $syaratId = (int) $syaratId;

            if (!in_array($syaratId, $allowedUploadSyaratIds, true) || !$file || !$file->isValid()) {
                continue;
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());
            $originalName = (string) $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileName = 'draft_syarat_' . $syaratId . '_' . uniqid('', true) . ($extension !== '' ? '.' . $extension : '');
            $file->move($draftDirectory, $fileName);
            $tempPath = $draftDirectory . '/' . $fileName;

            if (!is_int($fileSize) && File::exists($tempPath)) {
                $fileSize = File::size($tempPath);
            }

            $draftUploads[] = [
                'original_name' => $originalName,
                'size' => is_int($fileSize) ? $fileSize : null,
                'syarat_id' => $syaratId,
                'temp_path' => $tempPath,
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        return $draftUploads;
    }

    protected function moveDraftUploadsToFinalLocation(array $draftUploads, Pegawai $pegawai): array
    {
        $targetDirectory = public_path('file/' . $pegawai->nip . '/layanan-syarat');
        File::ensureDirectoryExists($targetDirectory);

        $storedPaths = [];
        $syaratUploads = [];

        try {
            foreach ($draftUploads as $upload) {
                $syaratId = (int) ($upload['syarat_id'] ?? 0);
                $tempPath = (string) ($upload['temp_path'] ?? '');

                if ($syaratId <= 0 || $tempPath === '' || !File::exists($tempPath)) {
                    throw new \RuntimeException('Draft file syarat tidak ditemukan.');
                }

                $extension = strtolower((string) pathinfo($tempPath, PATHINFO_EXTENSION));
                $fileName = 'syarat_' . $syaratId . '_' . uniqid('', true) . ($extension !== '' ? '.' . $extension : '');
                $finalPath = $targetDirectory . '/' . $fileName;

                File::move($tempPath, $finalPath);
                $storedPaths[] = $finalPath;

                $syaratUploads[] = [
                    'file' => $fileName,
                    'original_name' => $upload['original_name'] ?? $fileName,
                    'path' => $pegawai->nip . '/layanan-syarat/' . $fileName,
                    'review_catatan' => null,
                    'review_status' => 'pending',
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                    'syarat_id' => $syaratId,
                    'uploaded_at' => $upload['uploaded_at'] ?? now()->toDateTimeString(),
                ];
            }
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                if (File::exists($storedPath)) {
                    File::delete($storedPath);
                }
            }

            throw $exception;
        }

        return $syaratUploads;
    }

    protected function clearLayananUsulanDraft($session, Pegawai $pegawai, Layanan $layanan, array $draft = []): void
    {
        $this->deleteTemporaryDraftUploads($this->normalizeDraftSyaratUploads((array) ($draft['syarat_uploads'] ?? [])));

        $session->forget($this->layananUsulanDraftSessionKey($pegawai, $layanan));
    }

    protected function deleteTemporaryDraftUploads(array $draftUploads): void
    {
        $draftUploads = collect($draftUploads);

        $draftUploads->pluck('temp_path')
            ->filter(function ($path) {
                return is_string($path) && $path !== '';
            })
            ->each(function ($path) {
                if (File::exists($path)) {
                    File::delete($path);
                }
            });

        $draftUploads->pluck('temp_path')
            ->map(function ($path) {
                return is_string($path) && $path !== '' ? dirname($path) : null;
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->each(function ($directory) {
                if (File::isDirectory($directory) && empty(File::files($directory))) {
                    File::deleteDirectory($directory);
                }
            });
    }

    protected function normalizeDraftSyaratUploads(?array $uploads): array
    {
        return collect($uploads ?? [])
            ->map(function ($upload) {
                $upload = (array) $upload;

                return [
                    'original_name' => $upload['original_name'] ?? null,
                    'size' => isset($upload['size']) ? (int) $upload['size'] : null,
                    'syarat_id' => (int) ($upload['syarat_id'] ?? 0),
                    'temp_path' => $upload['temp_path'] ?? null,
                    'uploaded_at' => $upload['uploaded_at'] ?? null,
                ];
            })
            ->filter(function (array $upload) {
                return $upload['syarat_id'] > 0 && is_string($upload['temp_path']) && $upload['temp_path'] !== '';
            })
            ->values()
            ->all();
    }

    public function editLayananProses(LayananPegawai $layananPegawai)
    {
        abort_if($layananPegawai->hasDedicatedCutiDetail(), 404);

        return view('kepegawaian.layanan.proses', [
            'title' => 'Proses Usulan Layanan',
            'usulan' => tap($layananPegawai->load(['cutiDetail', 'layanan.syarat.dokumen', 'pegawai', 'pengusul', 'outputUploader']), function ($usulan) {
                $usulan->syarat_uploads = $this->normalizeSyaratUploads($usulan->syarat_uploads);
            }),
        ]);
    }

    public function editCutiProses(LayananPegawai $layananPegawai)
    {
        $usulan = tap($layananPegawai->load(['cutiDetail', 'layanan.syarat.dokumen', 'pegawai', 'pengusul', 'outputUploader']), function ($item) {
            $item->syarat_uploads = $this->normalizeSyaratUploads($item->syarat_uploads);
        });

        abort_unless($usulan->cutiDetail && $this->isCutiLayanan($usulan->layanan), 404);

        return view('kepegawaian.cuti.proses', [
            'title' => 'Proses Usulan Cuti',
            'usulan' => $usulan,
        ]);
    }

    public function updateLayananProses(KepegawaianLayananProcessRequest $request, LayananPegawai $layananPegawai)
    {
        $hasCutiDetail = $layananPegawai->hasDedicatedCutiDetail();

        if (request()->routeIs('kepegawaian.layanan.*') && $hasCutiDetail) {
            abort(404);
        }

        if (request()->routeIs('kepegawaian.cuti.*') && !$hasCutiDetail) {
            abort(404);
        }

        $validated = $request->validated();
        $status = (string) $validated['status'];
        $currentStatus = (string) $layananPegawai->status;
        $uploadedOutputFile = $request->file('output_file');
        $existingUploads = $this->normalizeSyaratUploads($layananPegawai->syarat_uploads);
        $reviewInputs = collect((array) ($validated['syarat_reviews'] ?? []));
        $selectedLayanan = $layananPegawai->layanan ?: $layananPegawai->layanan()->first();
        $isCutiLayanan = $selectedLayanan instanceof Layanan
            ? $this->isCutiLayanan($selectedLayanan)
            : false;

        $updatedUploads = collect($existingUploads)
            ->map(function (array $upload) use ($reviewInputs) {
                $syaratId = (int) ($upload['syarat_id'] ?? 0);
                $input = (array) $reviewInputs->get((string) $syaratId, $reviewInputs->get($syaratId, []));
                $reviewStatus = (string) ($input['status'] ?? $upload['review_status'] ?? 'pending');
                $reviewCatatan = isset($input['catatan']) ? trim((string) $input['catatan']) : ($upload['review_catatan'] ?? null);

                if ($reviewStatus === 'pending') {
                    $upload['review_status'] = 'pending';
                    $upload['review_catatan'] = $reviewCatatan !== '' ? $reviewCatatan : null;
                    $upload['reviewed_by'] = null;
                    $upload['reviewed_at'] = null;

                    return $upload;
                }

                $upload['review_status'] = $reviewStatus;
                $upload['review_catatan'] = $reviewCatatan !== '' ? $reviewCatatan : null;
                $upload['reviewed_by'] = auth()->id();
                $upload['reviewed_at'] = now()->toDateTimeString();

                return $upload;
            })
            ->values()
            ->all();

        if ($status === LayananPegawai::STATUS_SELESAI) {
            $hasUnapprovedUpload = collect($updatedUploads)->contains(function (array $upload) {
                return ($upload['review_status'] ?? 'pending') !== 'approved';
            });

            if ($hasUnapprovedUpload) {
                Alert::error('Error', 'Usulan belum dapat disetujui karena masih ada bukti syarat tambahan yang belum disetujui.');

                return redirect()->back()->withInput();
            }
        }

        if ($isCutiLayanan && $currentStatus === LayananPegawai::STATUS_SELESAI && $status !== LayananPegawai::STATUS_SELESAI) {
            Alert::error('Error', 'Status layanan cuti yang sudah selesai tidak dapat diubah untuk menjaga konsistensi jatah cuti.');

            return redirect()->back()->withInput();
        }

        $cutiDeduction = null;
        if ($isCutiLayanan && $status === LayananPegawai::STATUS_SELESAI && $currentStatus !== LayananPegawai::STATUS_SELESAI) {
            $cutiHariDiminta = (int) optional($layananPegawai->resolvedCutiDetail())->hari_diminta;
            if ($cutiHariDiminta <= 0) {
                Alert::error('Error', 'Usulan cuti belum memiliki jumlah hari cuti yang valid.');

                return redirect()->back()->withInput();
            }

            $cutiDeduction = $cutiHariDiminta;
        }

        $storedOutput = null;
        $oldOutputPath = $layananPegawai->output_path;

        if ($uploadedOutputFile) {
            try {
                $storedOutput = $this->storeLayananOutputFile($layananPegawai, $uploadedOutputFile);
            } catch (Throwable $exception) {
                report($exception);

                return redirect()
                    ->back()
                    ->withErrors([
                        'output_file' => 'File output layanan gagal diunggah. Silakan coba lagi.',
                    ])
                    ->withInput();
            }
        }

        try {
            DB::transaction(function () use ($layananPegawai, $validated, $status, $updatedUploads, $cutiDeduction, $storedOutput) {
                if ($cutiDeduction !== null) {
                    /** @var Pegawai|null $pegawai */
                    $pegawai = $layananPegawai->pegawai()->lockForUpdate()->first();

                    if (!$pegawai) {
                        throw new \RuntimeException('Data pegawai untuk usulan layanan tidak ditemukan.');
                    }

                    $saldoSaatIni = app(CutiService::class)->getSaldoCuti($pegawai);

                    if ($cutiDeduction > $saldoSaatIni) {
                        throw new \RuntimeException('Sisa cuti pegawai tidak mencukupi untuk diselesaikan.');
                    }

                    // Saldo cuti now computed dynamically from layanan_pegawais records;
                    // no manual deduction on pegawais.cuti_hari_tersedia is needed.
                }

                $layananPegawai->update([
                    'layanan_id' => $layananPegawai->layanan_id,
                    'status' => $status,
                    'catatan_proses' => $validated['catatan_proses'] ?? null,
                    'syarat_uploads' => $updatedUploads,
                    'output_file' => $storedOutput['output_file'] ?? $layananPegawai->output_file,
                    'output_original_name' => $storedOutput['output_original_name'] ?? $layananPegawai->output_original_name,
                    'output_path' => $storedOutput['output_path'] ?? $layananPegawai->output_path,
                    'processed_by' => $status === LayananPegawai::STATUS_USULAN ? null : auth()->id(),
                    'processed_at' => $status === LayananPegawai::STATUS_USULAN ? null : now(),
                    'output_uploaded_by' => $storedOutput['output_uploaded_by'] ?? $layananPegawai->output_uploaded_by,
                    'output_uploaded_at' => $storedOutput['output_uploaded_at'] ?? $layananPegawai->output_uploaded_at,
                ]);
            });

            app(SmartTriageService::class)->applyAndPersist($layananPegawai->fresh());
        } catch (Throwable $exception) {
            if ($storedOutput) {
                $this->deleteStoredLayananOutput($storedOutput['output_path'] ?? null);
            }

            report($exception);
            Alert::error('Error', $exception->getMessage() !== '' ? $exception->getMessage() : 'Usulan layanan gagal diperbarui.');

            return redirect()->back()->withInput();
        }

        if ($storedOutput && $oldOutputPath && $oldOutputPath !== ($storedOutput['output_path'] ?? null)) {
            $this->deleteStoredLayananOutput($oldOutputPath);
        }

        Alert::success('Success', 'Usulan layanan berhasil diperbarui.');

        return redirect()->route(
            request()->routeIs('kepegawaian.cuti.*') ? 'kepegawaian.cuti.proses' : 'kepegawaian.layanan.proses'
        );
    }

    protected function cutiProcessingQuery(?string $keyword = '')
    {
        return LayananPegawai::query()
            ->with(['cutiDetail', 'layanan', 'pegawai', 'pengusul', 'processor'])
            ->whereHas('cutiDetail')
            ->when(trim((string) $keyword) !== '', function ($query) use ($keyword) {
                $query->where(function ($innerQuery) use ($keyword) {
                    $innerQuery->whereHas('pegawai', function ($pegawaiQuery) use ($keyword) {
                        $pegawaiQuery->where('nama', 'like', '%' . $keyword . '%');
                    })->orWhereHas('layanan', function ($layananQuery) use ($keyword) {
                        $layananQuery->where('layanan', 'like', '%' . $keyword . '%');
                    });
                });
            });
    }

    protected function normalizeSyaratUploads(?array $uploads): array
    {
        return collect($uploads ?? [])
            ->map(function ($upload) {
                $upload = (array) $upload;

                return [
                    'syarat_id' => (int) ($upload['syarat_id'] ?? 0),
                    'file' => $upload['file'] ?? null,
                    'path' => $upload['path'] ?? null,
                    'uploaded_at' => $upload['uploaded_at'] ?? null,
                    'review_status' => $upload['review_status'] ?? 'pending',
                    'review_catatan' => $upload['review_catatan'] ?? null,
                    'reviewed_by' => $upload['reviewed_by'] ?? null,
                    'reviewed_at' => $upload['reviewed_at'] ?? null,
                ];
            })
            ->filter(function (array $upload) {
                return $upload['syarat_id'] > 0;
            })
            ->values()
            ->all();
    }


    protected function storeLayananOutputFile(LayananPegawai $layananPegawai, $uploadedFile): array
    {
        $pegawai = $layananPegawai->pegawai ?: $layananPegawai->pegawai()->first();

        if (!$pegawai) {
            throw new \RuntimeException('Data pegawai untuk output layanan tidak ditemukan.');
        }

        $directorySegment = trim((string) ($pegawai->nip ?: ('pegawai-' . $pegawai->id)));
        $directorySegment = preg_replace('/[^A-Za-z0-9_-]/', '-', $directorySegment) ?: ('pegawai-' . $pegawai->id);
        $targetDirectory = public_path('file/' . $directorySegment . '/layanan-output');
        File::ensureDirectoryExists($targetDirectory);

        $extension = strtolower((string) $uploadedFile->getClientOriginalExtension());
        $originalName = (string) $uploadedFile->getClientOriginalName();
        $fileName = 'output_layanan_' . $layananPegawai->id . '_' . uniqid('', true) . ($extension !== '' ? '.' . $extension : '');
        $uploadedFile->move($targetDirectory, $fileName);

        return [
            'output_file' => $fileName,
            'output_original_name' => $originalName,
            'output_path' => $directorySegment . '/layanan-output/' . $fileName,
            'output_uploaded_by' => auth()->id(),
            'output_uploaded_at' => now(),
        ];
    }

    protected function deleteStoredLayananOutput(?string $outputPath): void
    {
        $outputPath = trim((string) $outputPath);

        if ($outputPath === '') {
            return;
        }

        $absolutePath = public_path('file/' . ltrim($outputPath, '/'));

        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }

    protected function isCutiLayanan(Layanan $layanan): bool
    {
        return str_contains(mb_strtolower((string) $layanan->layanan), 'cuti');
    }

    protected function resolveCutiHariTersedia($pegawai): int
    {
        if ($pegawai instanceof Pegawai) {
            return app(CutiService::class)->getSaldoCuti($pegawai);
        }

        return \App\Services\CutiService::HARI_PER_TAHUN;
    }

    protected function calculateCutiHariDiminta(?string $tanggalMulai, ?string $tanggalSelesai): int
    {
        if (!$tanggalMulai || !$tanggalSelesai) {
            return 0;
        }

        return app(CutiService::class)->calculateHariKerja($tanggalMulai, $tanggalSelesai);
    }

    public function pribadi(PegawaiRequest $request, Pegawai $pegawai)
    {
        return $this->update($request, $pegawai);
    }

    public function searchUsers(Request $request)
    {
        Gate::authorize('manage-pegawai');

        $search = trim((string) $request->input('q'));
        $currentId = $request->input('current');

        if (mb_strlen($search) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        $users = User::query()
            ->where(function ($query) use ($currentId) {
                $query->whereDoesntHave('pegawai');

                if ($currentId) {
                    $query->orWhere('id', $currentId);
                }
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return $this->select2Response($users, function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->name . ' - ' . $user->email,
            ];
        });
    }

    public function searchJabatans(Request $request)
    {
        Gate::authorize('manage-pegawai');

        $search = trim((string) $request->input('q'));

        if (mb_strlen($search) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        $jabatans = Jabatan::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('jabatan', 'like', '%' . $search . '%')
                    ->orWhere('kode_jabatan', 'like', '%' . $search . '%');
            })
            ->orderBy('jabatan')
            ->paginate(15);

        return $this->select2Response($jabatans, function ($jabatan) {
            return [
                'id' => $jabatan->id,
                'text' => trim($jabatan->jabatan . ($jabatan->kode_jabatan ? ' (' . $jabatan->kode_jabatan . ')' : '')),
            ];
        });
    }

    public function searchProgramStudis(Request $request)
    {
        Gate::authorize('manage-pegawai');

        $search = trim((string) $request->input('q'));

        if (mb_strlen($search) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        $programStudis = ProgramStudi::with('jurusan')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('nama_prodi', 'like', '%' . $search . '%')
                        ->orWhere('kode_prodi', 'like', '%' . $search . '%')
                        ->orWhere('jenjang', 'like', '%' . $search . '%')
                        ->orWhereHas('jurusan', function ($jurusanQuery) use ($search) {
                            $jurusanQuery->where('jurusan', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderBy('nama_prodi')
            ->paginate(15);

        return $this->select2Response($programStudis, function ($programStudi) {
            return [
                'id' => $programStudi->id,
                'text' => trim($programStudi->jenjang . ' - ' . $programStudi->nama_prodi . ($programStudi->jurusan ? ' - ' . $programStudi->jurusan->jurusan : '')),
            ];
        });
    }

    public function searchKelurahans(Request $request)
    {
        Gate::authorize('manage-pegawai');

        $search = trim((string) $request->input('q'));

        if (mb_strlen($search) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        $kelurahans = Kelurahan::with('kecamatan.kabupaten')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('desa', 'like', '%' . $search . '%')
                        ->orWhereHas('kecamatan', function ($kecamatanQuery) use ($search) {
                            $kecamatanQuery->where('kecamatan', 'like', '%' . $search . '%')
                                ->orWhereHas('kabupaten', function ($kabupatenQuery) use ($search) {
                                    $kabupatenQuery->where('kabupaten', 'like', '%' . $search . '%');
                                });
                        });
                });
            })
            ->orderBy('desa')
            ->paginate(15);

        return $this->select2Response($kelurahans, function ($kelurahan) {
            $kecamatan = optional($kelurahan->kecamatan)->kecamatan;
            $kabupaten = optional(optional($kelurahan->kecamatan)->kabupaten)->kabupaten;

            return [
                'id' => $kelurahan->id,
                'text' => trim($kelurahan->desa . ($kecamatan ? ' - ' . $kecamatan : '') . ($kabupaten ? ' - ' . $kabupaten : '')),
            ];
        });
    }

    public function domisiliProvinsis()
    {
        return response()->json([
            'results' => Provinsi::orderBy('provinsi')->get()->map(function ($provinsi) {
                return [
                    'id' => $provinsi->id,
                    'text' => $provinsi->provinsi,
                ];
            })->values(),
        ]);
    }

    public function domisiliKabupatens(Request $request)
    {
        $provinsiId = $request->input('provinsi_id');

        if (!$provinsiId) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => Kabupaten::where('provinsi_id', $provinsiId)
                ->orderBy('kabupaten')
                ->get()
                ->map(function ($kabupaten) {
                    return [
                        'id' => $kabupaten->id,
                        'text' => $kabupaten->kabupaten,
                    ];
                })->values(),
        ]);
    }

    public function domisiliKecamatans(Request $request)
    {
        $kabupatenId = $request->input('kabupaten_id');

        if (!$kabupatenId) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => Kecamatan::where('kabupaten_id', $kabupatenId)
                ->orderBy('kecamatan')
                ->get()
                ->map(function ($kecamatan) {
                    return [
                        'id' => $kecamatan->id,
                        'text' => $kecamatan->kecamatan,
                    ];
                })->values(),
        ]);
    }

    public function domisiliKelurahans(Request $request)
    {
        $kecamatanId = $request->input('kecamatan_id');

        if (!$kecamatanId) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => Kelurahan::where('kecamatan_id', $kecamatanId)
                ->orderBy('desa')
                ->get()
                ->map(function ($kelurahan) {
                    return [
                        'id' => $kelurahan->id,
                        'text' => $kelurahan->desa,
                    ];
                })->values(),
        ]);
    }

    protected function formData(array $data = [])
    {
        $pegawai = $data['pegawai'] ?? null;

        return array_merge([
            'agamas' => Agama::orderBy('agama')->get(),
            'eselons' => Eselon::orderBy('id')->get(),
            'kedudukanPegawais' => KedudukanPegawai::orderBy('kedudukan_pegawai')->get(),
            'pangkats' => Pangkat::orderBy('pangkat')->get(),
            'pendidikans' => Pendidikan::with(['perguruan_tinggi', 'tingkat_pendidikan'])->orderBy('pendidikan')->get(),
            'statusPerkawinans' => StatusPerkawinan::orderBy('status_perkawinan')->get(),
            'unitKerjas' => UnitKerja::orderBy('order', 'asc')->orderBy('unit_kerja', 'asc')->get(),
            'jenisJabatans' => JenisJabatan::orderBy('jenis_jabatan')->get(),
            'jabatans' => Jabatan::orderBy('jabatan')->get(),
            'jabatanRangkaps' => Jabatan::where('jenis_jabatan_id', 1)
                ->orWhere('jabatan', 'like', '%Direktur%')
                ->orWhere('jabatan', 'like', '%Kepala%')
                ->orWhere('jabatan', 'like', '%Ketua%')
                ->orWhere('jabatan', 'like', '%Wakil%')
                ->orWhere('jabatan', 'like', '%Koordinator%')
                ->orderBy('jabatan')
                ->get(),
            'jabatanStrukturals' => Jabatan::where('jenis_jabatan_id', 1)
                ->orWhere('jabatan', 'like', '%Direktur%')
                ->orWhere('jabatan', 'like', '%Kepala%')
                ->orWhere('jabatan', 'like', '%Ketua%')
                ->orWhere('jabatan', 'like', '%Wakil%')
                ->orWhere('jabatan', 'like', '%Koordinator%')
                ->orderBy('jabatan')
                ->get(),
            'kelompokKeahlians' => KelompokKeahlian::orderBy('nama_kelompok')->get(),
            'jabatanFungsionalOptions' => Pegawai::jabatanFungsionalOptions(),
            'statusPegawaiOptions' => ['CPNS', 'PNS', 'PPPK', 'PPPK Paruh Waktu'],
            'kelompokPegawaiOptions' => ['dosen', 'tendik'],
        ], $this->alamatReferenceData($pegawai), $data);
    }

    protected function validatedPayload(PegawaiRequest $request)
    {
        $validated = $request->validated();

        if ($request->boolean('alamat_sama')) {
            $validated['alamat'] = $validated['alamat_asal'] ?? $request->input('alamat_asal');
            $validated['kelurahan_id'] = $validated['kelurahan_asal_id'] ?? $request->input('kelurahan_asal_id');
        }

        foreach ([
            'nik', 'gelar_depan', 'gelar_belakang', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'jumlah_anak', 'tanggal_lulus', 'npwp', 'bpjs',
            'no_karpeg', 'no_karis_karsu', 'tmt_pangkat', 'tmt_cpns', 'tmt_pns',
            'tmt_jabatan', 'tmt_pmk', 'pmk_tahun', 'pmk_bulan', 'email',
            'no_hp', 'no_telp', 'alamat_asal', 'kelurahan_asal_id', 'alamat',
            'kelurahan_id', 'eselon_id', 'kedudukan_pegawai_id', 'agama_id',
            'jenis_jabatan_id', 'jabatan_id', 'jabatan_rangkap_id', 'jabatan_struktural_id', 'pangkat_id',
            'status_perkawinan_id', 'pendidikan_id', 'program_studi_id', 'unit_kerja_id',
            'user_id', 'jabatan_fungsional', 'cuti_hari_tersedia', 'bidang_penelitian',
            'kelompok_keahlian_id', 'no_serdos'
        ] as $nullableField) {
            if ($request->input($nullableField) === '' || $request->input($nullableField) === null) {
                $validated[$nullableField] = null;
            }
        }

        if (isset($validated['jabatan_struktural_id']) && !isset($validated['jabatan_rangkap_id'])) {
            $validated['jabatan_rangkap_id'] = $validated['jabatan_struktural_id'];
        }

        // Jabatan rangkap hanya terisi jika jenis jabatan adalah jabatan rangkap (ID 3)
        if ((int) ($validated['jenis_jabatan_id'] ?? null) !== 3) {
            $validated['jabatan_rangkap_id'] = null;
        }

        if (empty($validated['status_pegawai'])) {
            $validated['status_pegawai'] = 'PNS';
        }

        if (empty($validated['kelompok_pegawai'])) {
            $validated['kelompok_pegawai'] = 'dosen';
        } elseif ($validated['kelompok_pegawai'] === 'tenaga kependidikan') {
            $validated['kelompok_pegawai'] = 'tendik';
        }

        if (empty($validated['cuti_hari_tersedia']) && !is_numeric($validated['cuti_hari_tersedia'] ?? null)) {
            $validated['cuti_hari_tersedia'] = 12;
        }

        $validated = Arr::except($validated, Pegawai::IDENTITY_FIELDS);

        return Arr::only($validated, (new Pegawai())->getFillable());
    }

    protected function validatedProfilePayload(PegawaiProfileRequest $request)
    {
        $validated = $request->validated();

        if ($request->boolean('alamat_sama')) {
            $validated['alamat'] = $validated['alamat_asal'] ?? $request->input('alamat_asal');
            $validated['kelurahan_id'] = $validated['kelurahan_asal_id'] ?? $request->input('kelurahan_asal_id');
        }

        foreach (['id_gscholar', 'id_sinta', 'id_scopus', 'id_garuda', 'id_wos', 'id_orc', 'nidn', 'nuptk', 'no_serdos', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'email', 'no_hp', 'no_telp', 'alamat_asal', 'kelurahan_asal_id', 'alamat', 'kelurahan_id', 'unit_kerja_id', 'program_studi_id', 'jabatan_fungsional', 'bidang_penelitian', 'kelompok_keahlian_id'] as $nullableField) {
            if ($request->input($nullableField) === '' || $request->input($nullableField) === null) {
                $validated[$nullableField] = null;
            }
        }

        $validated = Arr::except($validated, Pegawai::IDENTITY_FIELDS);

        return Arr::only($validated, (new Pegawai())->getFillable());
    }

    protected function saveExternalIdentifiers(Pegawai $pegawai, array $validated): void
    {
        $externalIdentifiers = Arr::only($validated, Pegawai::IDENTITY_FIELDS);

        if ($externalIdentifiers === []) {
            return;
        }

        $payload = [];

        foreach ($externalIdentifiers as $field => $value) {
            $payload[$field] = $value === '' || $value === null ? null : trim((string) $value);
        }

        $pegawai->identitas()->updateOrCreate(['pegawai_id' => $pegawai->id], $payload);
    }

    protected function saveCutiQuotas(Pegawai $pegawai, array $quotas): void
    {
        foreach ($quotas as $tahun => $hari) {
            if ($hari !== null && $hari !== '') {
                PegawaiCutiQuota::updateOrCreate(
                    [
                        'pegawai_id' => $pegawai->id,
                        'tahun' => (int) $tahun,
                    ],
                    [
                        'hari_tersedia' => max(0, min(100, (int) $hari)),
                    ]
                );
            }
        }
    }

    protected function domisiliReferenceData(?Pegawai $pegawai = null): array
    {
        return $this->alamatReferenceData($pegawai);
    }

    protected function alamatReferenceData(?Pegawai $pegawai = null): array
    {
        $provinsis = Provinsi::orderBy('provinsi')->get();

        // Alamat Asal references
        $selectedKelurahanAsalId = session()->getOldInput('kelurahan_asal_id', optional($pegawai)->kelurahan_asal_id);
        $selectedKelurahanAsal = $selectedKelurahanAsalId
            ? Kelurahan::with('kecamatan.kabupaten.provinsi')->find($selectedKelurahanAsalId)
            : null;

        $selectedKecamatanAsalId = session()->getOldInput('kecamatan_asal_id', optional(optional($selectedKelurahanAsal)->kecamatan)->id);
        $selectedKabupatenAsalId = session()->getOldInput('kabupaten_asal_id', optional(optional(optional($selectedKelurahanAsal)->kecamatan)->kabupaten)->id);
        $selectedProvinsiAsalId = session()->getOldInput('provinsi_asal_id', optional(optional(optional(optional($selectedKelurahanAsal)->kecamatan)->kabupaten)->provinsi)->id);

        // Alamat Domisili references
        $selectedKelurahanId = session()->getOldInput('kelurahan_id', optional($pegawai)->kelurahan_id);
        $selectedKelurahan = $selectedKelurahanId
            ? Kelurahan::with('kecamatan.kabupaten.provinsi')->find($selectedKelurahanId)
            : null;

        $selectedKecamatanId = session()->getOldInput('kecamatan_id', optional(optional($selectedKelurahan)->kecamatan)->id);
        $selectedKabupatenId = session()->getOldInput('kabupaten_id', optional(optional(optional($selectedKelurahan)->kecamatan)->kabupaten)->id);
        $selectedProvinsiId = session()->getOldInput('provinsi_id', optional(optional(optional(optional($selectedKelurahan)->kecamatan)->kabupaten)->provinsi)->id);

        // Check if alamat domisili is equal to alamat asal
        $isAlamatSamaInitial = false;
        if (session()->hasOldInput('alamat_sama')) {
            $isAlamatSamaInitial = (bool) session()->getOldInput('alamat_sama');
        } elseif ($pegawai && $pegawai->alamat_asal && $pegawai->alamat && $pegawai->alamat_asal === $pegawai->alamat && $pegawai->kelurahan_asal_id && $pegawai->kelurahan_id && (string) $pegawai->kelurahan_asal_id === (string) $pegawai->kelurahan_id) {
            $isAlamatSamaInitial = true;
        }

        return [
            'provinsis' => $provinsis,

            // Asal
            'kabupatensAsal' => $selectedProvinsiAsalId
                ? Kabupaten::where('provinsi_id', $selectedProvinsiAsalId)->orderBy('kabupaten')->get()
                : collect(),
            'kecamatansAsal' => $selectedKabupatenAsalId
                ? Kecamatan::where('kabupaten_id', $selectedKabupatenAsalId)->orderBy('kecamatan')->get()
                : collect(),
            'kelurahansAsal' => $selectedKecamatanAsalId
                ? Kelurahan::where('kecamatan_id', $selectedKecamatanAsalId)->orderBy('desa')->get()
                : collect(),
            'selectedProvinsiAsalId' => (string) ($selectedProvinsiAsalId ?? ''),
            'selectedKabupatenAsalId' => (string) ($selectedKabupatenAsalId ?? ''),
            'selectedKecamatanAsalId' => (string) ($selectedKecamatanAsalId ?? ''),
            'selectedKelurahanAsalId' => (string) ($selectedKelurahanAsalId ?? ''),

            // Domisili
            'kabupatens' => $selectedProvinsiId
                ? Kabupaten::where('provinsi_id', $selectedProvinsiId)->orderBy('kabupaten')->get()
                : collect(),
            'kecamatans' => $selectedKabupatenId
                ? Kecamatan::where('kabupaten_id', $selectedKabupatenId)->orderBy('kecamatan')->get()
                : collect(),
            'kelurahans' => $selectedKecamatanId
                ? Kelurahan::where('kecamatan_id', $selectedKecamatanId)->orderBy('desa')->get()
                : collect(),
            'selectedProvinsiId' => (string) ($selectedProvinsiId ?? ''),
            'selectedKabupatenId' => (string) ($selectedKabupatenId ?? ''),
            'selectedKecamatanId' => (string) ($selectedKecamatanId ?? ''),
            'selectedKelurahanId' => (string) ($selectedKelurahanId ?? ''),

            // Same address flag
            'isAlamatSama' => $isAlamatSamaInitial,
        ];
    }

    protected function pegawaiQuery(?string $keyword = null)
    {
        return Pegawai::with([
            'program_studi.jurusan',
            'jabatan',
            'jenis_jabatan',
            'unit_kerja',
            'user',
            'identitas',
        ])
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($innerQuery) use ($keyword) {
                    $innerQuery->where('nik', 'like', '%' . $keyword . '%')
                        ->orWhere('nip', 'like', '%' . $keyword . '%')
                        ->orWhere('nama', 'like', '%' . $keyword . '%')
                        ->orWhereHas('identitas', function ($identifierQuery) use ($keyword) {
                            $identifierQuery->where('nuptk', 'like', '%' . $keyword . '%')
                                ->orWhere('id_gscholar', 'like', '%' . $keyword . '%')
                                ->orWhere('nidn', 'like', '%' . $keyword . '%');
                        })
                        ->orWhereHas('jabatan', function ($jabatanQuery) use ($keyword) {
                            $jabatanQuery->where('jabatan', 'like', '%' . $keyword . '%');
                        })
                        ->orWhereHas('program_studi', function ($programStudiQuery) use ($keyword) {
                            $programStudiQuery->where('nama_prodi', 'like', '%' . $keyword . '%')
                                ->orWhereHas('jurusan', function ($jurusanQuery) use ($keyword) {
                                    $jurusanQuery->where('jurusan', 'like', '%' . $keyword . '%');
                                });
                        })
                        ->orWhereHas('unit_kerja', function ($unitKerjaQuery) use ($keyword) {
                            $unitKerjaQuery->where('unit_kerja', 'like', '%' . $keyword . '%');
                        });
                });
            })
            ->orderBy('tmt_cpns')
            ->orderBy('nip');
    }

    protected function resolveSearchTerm(Request $request): ?string
    {
        $keyword = trim((string) $request->input('q', ''));

        if ($keyword === '') {
            $keyword = trim((string) $request->input('search.value', ''));
        }

        return $keyword === '' ? null : $keyword;
    }

    protected function select2Response(LengthAwarePaginator $paginator, callable $formatter)
    {
        return response()->json([
            'results' => $paginator->getCollection()->map($formatter)->values(),
            'pagination' => [
                'more' => $paginator->hasMorePages(),
            ],
        ]);
    }
}
