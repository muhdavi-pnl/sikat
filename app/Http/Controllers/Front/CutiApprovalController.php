<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CutiLayananPegawai;
use App\Models\LayananPegawai;
use App\Models\PejabatCutiSetting;
use App\Models\Pegawai;
use App\Services\CutiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RealRashid\SweetAlert\Facades\Alert;
use Throwable;

class CutiApprovalController extends Controller
{
    protected $cutiService;

    public function __construct(CutiService $cutiService)
    {
        $this->cutiService = $cutiService;
    }

    /**
     * Display list of leave requests for the immediate supervisor.
     */
    public function indexAtasan(Request $request)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;
        $isSuperAdmin = $user->hasRole('super-admin');
        $isKepegawaian = $user->hasRole('kepegawaian');

        if (!$pegawai && !$isSuperAdmin && !$isKepegawaian) {
            abort(403, 'Anda tidak terhubung dengan data pegawai.');
        }

        $subordinateIds = ($isSuperAdmin || $isKepegawaian)
            ? []
            : $this->cutiService->getSubordinatePegawaiIds($pegawai);

        if (!$isSuperAdmin && !$isKepegawaian && empty($subordinateIds)) {
            // User is not a supervisor to anyone
            return view('layanan.cuti-approval.atasan-index', [
                'title' => 'Persetujuan Cuti (Atasan Langsung)',
                'usulans' => collect([]),
                'isSupervisor' => false,
                'keyword' => '',
                'stageFilter' => 'all',
            ]);
        }

        $keyword = trim((string) $request->input('q', ''));
        $stageFilter = (string) $request->input('stage', 'pending');

        $query = LayananPegawai::query()
            ->with(['cutiDetail', 'pegawai.jabatan', 'pegawai.unit_kerja', 'layanan'])
            ->whereHas('cutiDetail')
            ->when(!$isSuperAdmin && !$isKepegawaian, function ($q) use ($subordinateIds) {
                $q->whereIn('pegawai_id', $subordinateIds);
            })
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->whereHas('pegawai', function ($pegawaiQuery) use ($keyword) {
                    $pegawaiQuery->where('nama', 'like', '%' . $keyword . '%')
                        ->orWhere('nip', 'like', '%' . $keyword . '%');
                });
            });

        if ($stageFilter === 'pending') {
            $query->whereHas('cutiDetail', function ($q) {
                $q->whereNull('atasan_status');
            })->where('status', LayananPegawai::STATUS_USULAN);
        } elseif ($stageFilter === 'approved') {
            $query->whereHas('cutiDetail', function ($q) {
                $q->whereNotNull('atasan_status');
            });
        }

        $usulans = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('layanan.cuti-approval.atasan-index', [
            'title' => 'Persetujuan Cuti (Atasan Langsung)',
            'usulans' => $usulans,
            'isSupervisor' => true,
            'keyword' => $keyword,
            'stageFilter' => $stageFilter,
        ]);
    }

    /**
     * Show detail and approval form for immediate supervisor.
     */
    public function showAtasan(LayananPegawai $layananPegawai)
    {
        $this->authorizeSupervisor($layananPegawai);

        $usulan = $layananPegawai->load([
            'cutiDetail',
            'layanan.syarat.dokumen',
            'pegawai.jabatan',
            'pegawai.unit_kerja',
            'pegawai.pangkat',
            'pengusul',
        ]);

        abort_unless($usulan->cutiDetail, 404);

        $formData = $this->cutiService->buildPrintableFormData($usulan);

        return view('layanan.cuti-approval.atasan-show', [
            'title' => 'Pertimbangan Atasan Langsung - Usulan Cuti',
            'usulan' => $usulan,
            'formData' => $formData,
        ]);
    }

    /**
     * Submit immediate supervisor review / pertimbangan.
     */
    public function approveAtasan(Request $request, LayananPegawai $layananPegawai)
    {
        $this->authorizeSupervisor($layananPegawai);

        $validated = $request->validate([
            'atasan_status' => 'required|string|in:disetujui,perubahan,ditangguhkan,tidak_disetujui',
            'catatan_atasan' => 'nullable|string|max:1000',
        ]);

        $status = $validated['atasan_status'];
        $catatan = trim((string) ($validated['catatan_atasan'] ?? ''));
        $user = auth()->user();
        $supervisorPegawai = $user->pegawai ?: $this->cutiService->resolveAtasanLangsung($layananPegawai->pegawai);

        $cutiDetail = $layananPegawai->resolvedCutiDetail();
        if (!$cutiDetail) {
            abort(404);
        }

        DB::transaction(function () use ($layananPegawai, $cutiDetail, $status, $catatan, $supervisorPegawai, $user) {
            $isRejected = ($status === CutiLayananPegawai::STATUS_TIDAK_DISETUJUI);

            $cutiDetail->update([
                'atasan_pegawai_id' => $supervisorPegawai?->id,
                'atasan_user_id' => $user->id,
                'atasan_status' => $status,
                'catatan_atasan' => $catatan !== '' ? $catatan : null,
                'atasan_approved_at' => now(),
                'approval_stage' => $isRejected ? CutiLayananPegawai::STAGE_DITOLAK : CutiLayananPegawai::STAGE_PYBMC,
            ]);

            if ($isRejected) {
                $layananPegawai->update([
                    'status' => LayananPegawai::STATUS_DITOLAK,
                    'catatan_proses' => $catatan !== '' ? $catatan : 'Usulan cuti tidak disetujui oleh Atasan Langsung.',
                    'processed_by' => $user->id,
                    'processed_at' => now(),
                ]);
            } else {
                $layananPegawai->update([
                    'status' => LayananPegawai::STATUS_PROSES,
                ]);
            }
        });

        Alert::success('Berhasil', 'Pertimbangan atasan langsung berhasil disimpan dan diteruskan.');

        return redirect()->route('cuti.approval.atasan.index');
    }

    /**
     * Display list of leave requests for PYBMC.
     */
    public function indexPybmc(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $isKepegawaian = $user->hasRole('kepegawaian');
        $isPybmc = $this->cutiService->isUserDesignatedPybmc($user);

        if (!$isSuperAdmin && !$isKepegawaian && !$isPybmc) {
            abort(403, 'Anda tidak memiliki hak akses sebagai Pejabat Yang Berwenang Memberikan Cuti (PYBMC).');
        }

        $keyword = trim((string) $request->input('q', ''));
        $stageFilter = (string) $request->input('stage', 'pending');

        $query = LayananPegawai::query()
            ->with(['cutiDetail.atasanPegawai', 'pegawai.jabatan', 'pegawai.unit_kerja', 'layanan'])
            ->whereHas('cutiDetail')
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->whereHas('pegawai', function ($pegawaiQuery) use ($keyword) {
                    $pegawaiQuery->where('nama', 'like', '%' . $keyword . '%')
                        ->orWhere('nip', 'like', '%' . $keyword . '%');
                });
            });

        if ($stageFilter === 'pending') {
            $query->whereHas('cutiDetail', function ($q) {
                $q->where('approval_stage', CutiLayananPegawai::STAGE_PYBMC)
                    ->whereNull('pybmc_status');
            });
        } elseif ($stageFilter === 'approved') {
            $query->whereHas('cutiDetail', function ($q) {
                $q->whereNotNull('pybmc_status');
            });
        }

        $usulans = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('layanan.cuti-approval.pybmc-index', [
            'title' => 'Persetujuan Cuti (PYBMC)',
            'usulans' => $usulans,
            'keyword' => $keyword,
            'stageFilter' => $stageFilter,
        ]);
    }

    /**
     * Show detail and final decision form for PYBMC.
     */
    public function showPybmc(LayananPegawai $layananPegawai)
    {
        $this->authorizePybmc();

        $usulan = $layananPegawai->load([
            'cutiDetail.atasanPegawai',
            'layanan.syarat.dokumen',
            'pegawai.jabatan',
            'pegawai.unit_kerja',
            'pegawai.pangkat',
            'pengusul',
        ]);

        abort_unless($usulan->cutiDetail, 404);

        $formData = $this->cutiService->buildPrintableFormData($usulan);

        return view('layanan.cuti-approval.pybmc-show', [
            'title' => 'Keputusan PYBMC - Usulan Cuti',
            'usulan' => $usulan,
            'formData' => $formData,
        ]);
    }

    /**
     * Submit PYBMC decision.
     */
    public function approvePybmc(Request $request, LayananPegawai $layananPegawai)
    {
        $this->authorizePybmc();

        $validated = $request->validate([
            'pybmc_status' => 'required|string|in:disetujui,perubahan,ditangguhkan,tidak_disetujui',
            'catatan_pybmc' => 'nullable|string|max:1000',
            'output_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $status = $validated['pybmc_status'];
        $catatan = trim((string) ($validated['catatan_pybmc'] ?? ''));
        $uploadedOutputFile = $request->file('output_file');
        $user = auth()->user();
        $pybmcSetting = PejabatCutiSetting::getActivePybmc();
        $pybmcPegawai = $user->pegawai ?: $pybmcSetting?->pegawai;

        $cutiDetail = $layananPegawai->resolvedCutiDetail();
        if (!$cutiDetail) {
            abort(404);
        }

        $storedOutput = null;
        if ($uploadedOutputFile) {
            $pegawai = $layananPegawai->pegawai ?: $layananPegawai->pegawai()->first();
            $directorySegment = trim((string) ($pegawai->nip ?: ('pegawai-' . $pegawai->id)));
            $directorySegment = preg_replace('/[^A-Za-z0-9_-]/', '-', $directorySegment) ?: ('pegawai-' . $pegawai->id);
            $targetDirectory = public_path('file/' . $directorySegment . '/layanan-output');
            File::ensureDirectoryExists($targetDirectory);

            $extension = strtolower((string) $uploadedOutputFile->getClientOriginalExtension());
            $originalName = (string) $uploadedOutputFile->getClientOriginalName();
            $fileName = 'output_layanan_' . $layananPegawai->id . '_' . uniqid('', true) . ($extension !== '' ? '.' . $extension : '');
            $uploadedOutputFile->move($targetDirectory, $fileName);

            $storedOutput = [
                'output_file' => $fileName,
                'output_original_name' => $originalName,
                'output_path' => $directorySegment . '/layanan-output/' . $fileName,
                'output_uploaded_by' => auth()->id(),
                'output_uploaded_at' => now(),
            ];
        }

        DB::transaction(function () use ($layananPegawai, $cutiDetail, $status, $catatan, $pybmcPegawai, $user, $storedOutput) {
            $isApproved = ($status === CutiLayananPegawai::STATUS_DISETUJUI);
            $isRejected = ($status === CutiLayananPegawai::STATUS_TIDAK_DISETUJUI);

            $cutiDetail->update([
                'pybmc_pegawai_id' => $pybmcPegawai?->id,
                'pybmc_user_id' => $user->id,
                'pybmc_status' => $status,
                'catatan_pybmc' => $catatan !== '' ? $catatan : null,
                'pybmc_approved_at' => now(),
                'approval_stage' => $isApproved ? CutiLayananPegawai::STAGE_SELESAI : ($isRejected ? CutiLayananPegawai::STAGE_DITOLAK : CutiLayananPegawai::STAGE_PYBMC),
            ]);

            $layananUpdate = [
                'processed_by' => $user->id,
                'processed_at' => now(),
            ];

            if ($storedOutput) {
                $layananUpdate = array_merge($layananUpdate, $storedOutput);
            }

            if ($isApproved) {
                $layananUpdate['status'] = LayananPegawai::STATUS_SELESAI;
                if ($catatan !== '') {
                    $layananUpdate['catatan_proses'] = $catatan;
                }
            } elseif ($isRejected) {
                $layananUpdate['status'] = LayananPegawai::STATUS_DITOLAK;
                $layananUpdate['catatan_proses'] = $catatan !== '' ? $catatan : 'Usulan cuti tidak disetujui oleh Pejabat Yang Berwenang Memberikan Cuti (PYBMC).';
            } else {
                if ($catatan !== '') {
                    $layananUpdate['catatan_proses'] = $catatan;
                }
            }

            $layananPegawai->update($layananUpdate);
        });

        Alert::success('Berhasil', 'Keputusan PYBMC berhasil disimpan.');

        return redirect()->route('cuti.approval.pybmc.index');
    }

    /**
     * Show PYBMC designation setting page for Kepegawaian.
     */
    public function pybmcSetting(Request $request)
    {
        $currentSetting = PejabatCutiSetting::getActivePybmc();
        $pegawais = Pegawai::with(['jabatan', 'unit_kerja'])->orderBy('nama')->get();

        return view('kepegawaian.cuti.pybmc-setting', [
            'title' => 'Pengaturan PYBMC',
            'currentSetting' => $currentSetting,
            'pegawais' => $pegawais,
        ]);
    }

    /**
     * Update PYBMC designation setting.
     */
    public function updatePybmcSetting(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'jabatan_label' => 'nullable|string|max:255',
        ]);

        $pegawai = Pegawai::findOrFail($validated['pegawai_id']);

        DB::transaction(function () use ($pegawai, $validated) {
            PejabatCutiSetting::query()->update(['is_active' => false]);

            PejabatCutiSetting::create([
                'pegawai_id' => $pegawai->id,
                'jabatan_label' => trim((string) ($validated['jabatan_label'] ?? '')) ?: null,
                'is_active' => true,
                'updated_by' => auth()->id(),
            ]);
        });

        Alert::success('Berhasil', 'Pejabat Yang Berwenang Memberikan Cuti (PYBMC) berhasil ditetapkan.');

        return redirect()->route('kepegawaian.cuti.pybmc-setting');
    }

    protected function authorizeSupervisor(LayananPegawai $layananPegawai): void
    {
        $user = auth()->user();
        if ($user->hasRole('super-admin') || $user->hasRole('kepegawaian')) {
            return;
        }

        $pegawai = $user->pegawai;
        if (!$pegawai) {
            abort(403, 'Akses ditolak.');
        }

        if (!$this->cutiService->isSupervisorOf($pegawai, $layananPegawai->pegawai)) {
            abort(403, 'Anda bukan atasan langsung dari pegawai pengusul cuti ini.');
        }
    }

    protected function authorizePybmc(): void
    {
        $user = auth()->user();
        if ($user->hasRole('super-admin') || $user->hasRole('kepegawaian')) {
            return;
        }

        if (!$this->cutiService->isUserDesignatedPybmc($user)) {
            abort(403, 'Anda tidak memiliki wewenang sebagai Pejabat Yang Berwenang Memberikan Cuti (PYBMC).');
        }
    }
}
