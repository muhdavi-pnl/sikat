<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudiLanjutRequest;
use App\Models\Jurusan;
use App\Models\Pegawai;
use App\Models\StudiLanjut;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudiLanjutController extends Controller
{
    public function index(Request $request): View
    {
        $paginate = 10;
        $search = trim((string) $request->input('search', ''));
        $progres = trim((string) $request->input('progres', ''));
        $jenisPembiayaan = trim((string) $request->input('jenis_pembiayaan', ''));
        $jenisTugas = trim((string) $request->input('jenis_tugas', ''));
        $bidangIlmu = trim((string) $request->input('bidang_ilmu', ''));
        $jenjang = trim((string) $request->input('jenjang', ''));
        $jurusanId = $request->input('jurusan_id');

        // Global Statistics for monitoring widgets
        $stats = [
            'total' => StudiLanjut::count(),
            'ongoing' => StudiLanjut::where('progres', StudiLanjut::PROGRES_ONGOING)->count(),
            'defer' => StudiLanjut::where('progres', StudiLanjut::PROGRES_DEFER)->count(),
            'selesai' => StudiLanjut::where('progres', StudiLanjut::PROGRES_SELESAI)->count(),
            'beasiswa' => StudiLanjut::where('jenis_pembiayaan', StudiLanjut::JENIS_PEMBIAYAAN_BEASISWA)->count(),
            'mandiri' => StudiLanjut::where('jenis_pembiayaan', StudiLanjut::JENIS_PEMBIAYAAN_MANDIRI)->count(),
            'meninggalkan_tugas' => StudiLanjut::where('jenis_tugas', StudiLanjut::JENIS_TUGAS_MENINGGALKAN)->count(),
            'menjalankan_tugas' => StudiLanjut::where('jenis_tugas', StudiLanjut::JENIS_TUGAS_MENJALANKAN)->count(),
        ];

        $query = StudiLanjut::query()
            ->with(['pegawai.program_studi.jurusan', 'pegawai.unit_kerja', 'pegawai.jabatan', 'creator'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('program_studi', 'like', '%' . $search . '%')
                    ->orWhere('nama_institusi', 'like', '%' . $search . '%')
                    ->orWhere('bidang_ilmu', 'like', '%' . $search . '%')
                    ->orWhere('nomor_sk', 'like', '%' . $search . '%')
                    ->orWhere('nama_beasiswa', 'like', '%' . $search . '%')
                    ->orWhereHas('pegawai', function ($pegawaiQuery) use ($search) {
                        $pegawaiQuery->where('nama', 'like', '%' . $search . '%')
                            ->orWhere('nip', 'like', '%' . $search . '%');
                    });
            });
        }

        if (in_array($progres, array_keys(StudiLanjut::PROGRES_OPTIONS), true)) {
            $query->where('progres', $progres);
        }

        if (in_array($jenisPembiayaan, array_keys(StudiLanjut::JENIS_PEMBIAYAAN_OPTIONS), true)) {
            $query->where('jenis_pembiayaan', $jenisPembiayaan);
        }

        if (in_array($jenisTugas, array_keys(StudiLanjut::JENIS_TUGAS_OPTIONS), true)) {
            $query->where('jenis_tugas', $jenisTugas);
        }

        if (in_array($bidangIlmu, array_keys(StudiLanjut::BIDANG_ILMU_OPTIONS), true)) {
            $query->where('bidang_ilmu', $bidangIlmu);
        }

        if ($jenjang !== '') {
            $query->where('jenjang', $jenjang);
        }

        if ($jurusanId !== null && $jurusanId !== '') {
            $query->whereHas('pegawai.program_studi', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', (int) $jurusanId);
            });
        }

        $studiLanjuts = $query->paginate($paginate)->withQueryString()->onEachSide(0);
        $jurusans = Jurusan::orderBy('jurusan')->get();

        return view('kepegawaian.studi-lanjut.index', [
            'title' => 'Monitoring Studi Lanjut Pegawai',
            'studiLanjuts' => $studiLanjuts,
            'stats' => $stats,
            'jurusans' => $jurusans,
            'filters' => [
                'search' => $search,
                'progres' => $progres,
                'jenis_pembiayaan' => $jenisPembiayaan,
                'jenis_tugas' => $jenisTugas,
                'bidang_ilmu' => $bidangIlmu,
                'jenjang' => $jenjang,
                'jurusan_id' => $jurusanId,
            ],
            'progresOptions' => StudiLanjut::PROGRES_OPTIONS,
            'jenisPembiayaanOptions' => StudiLanjut::JENIS_PEMBIAYAAN_OPTIONS,
            'jenisTugasOptions' => StudiLanjut::JENIS_TUGAS_OPTIONS,
            'bidangIlmuOptions' => StudiLanjut::BIDANG_ILMU_OPTIONS,
            'jenjangOptions' => StudiLanjut::JENJANG_OPTIONS,
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create(Request $request): View
    {
        $preselectedPegawaiId = $request->input('pegawai_id');
        $preselectedPegawai = null;

        if ($preselectedPegawaiId) {
            $preselectedPegawai = Pegawai::with(['program_studi.jurusan', 'unit_kerja'])->find($preselectedPegawaiId);
        }

        return view('kepegawaian.studi-lanjut.create', [
            'title' => 'Tambah Data Studi Lanjut Pegawai',
            'studiLanjut' => new StudiLanjut([
                'progres' => StudiLanjut::PROGRES_ONGOING,
                'jenis_pembiayaan' => StudiLanjut::JENIS_PEMBIAYAAN_BEASISWA,
                'jenis_tugas' => StudiLanjut::JENIS_TUGAS_MENINGGALKAN,
                'bidang_ilmu' => StudiLanjut::BIDANG_ILMU_STEM,
                'negara' => 'Indonesia',
                'pegawai_id' => $preselectedPegawaiId,
            ]),
            'preselectedPegawai' => $preselectedPegawai,
            'progresOptions' => StudiLanjut::PROGRES_OPTIONS,
            'jenisPembiayaanOptions' => StudiLanjut::JENIS_PEMBIAYAAN_OPTIONS,
            'jenisTugasOptions' => StudiLanjut::JENIS_TUGAS_OPTIONS,
            'bidangIlmuOptions' => StudiLanjut::BIDANG_ILMU_OPTIONS,
            'jenjangOptions' => StudiLanjut::JENJANG_OPTIONS,
        ]);
    }

    public function store(StudiLanjutRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();

            if ($request->hasFile('dokumen_sk')) {
                $file = $request->file('dokumen_sk');
                $filename = 'sk_studi_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                $destinationPath = public_path('uploads/studi_lanjut');

                if (! File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $data['dokumen_sk'] = 'uploads/studi_lanjut/' . $filename;
            }

            $studiLanjut = StudiLanjut::create($data);

            Alert::success('Berhasil', 'Data monitoring studi lanjut pegawai berhasil disimpan.');

            return redirect()->route('kepegawaian.studi-lanjut.show', $studiLanjut);
        } catch (\Throwable $exception) {
            report($exception);
            Alert::error('Gagal', 'Terjadi kesalahan saat menyimpan data studi lanjut.');

            return redirect()->back()->withInput();
        }
    }

    public function show(StudiLanjut $studiLanjut): View
    {
        $studiLanjut->load([
            'pegawai.program_studi.jurusan',
            'pegawai.unit_kerja',
            'pegawai.jabatan',
            'pegawai.pangkat',
            'creator',
        ]);

        return view('kepegawaian.studi-lanjut.show', [
            'title' => 'Detail Monitoring Studi Lanjut',
            'studiLanjut' => $studiLanjut,
        ]);
    }

    public function edit(StudiLanjut $studiLanjut): View
    {
        $studiLanjut->load(['pegawai.program_studi.jurusan', 'pegawai.unit_kerja']);

        return view('kepegawaian.studi-lanjut.edit', [
            'title' => 'Edit Data Studi Lanjut Pegawai',
            'studiLanjut' => $studiLanjut,
            'progresOptions' => StudiLanjut::PROGRES_OPTIONS,
            'jenisPembiayaanOptions' => StudiLanjut::JENIS_PEMBIAYAAN_OPTIONS,
            'jenisTugasOptions' => StudiLanjut::JENIS_TUGAS_OPTIONS,
            'bidangIlmuOptions' => StudiLanjut::BIDANG_ILMU_OPTIONS,
            'jenjangOptions' => StudiLanjut::JENJANG_OPTIONS,
        ]);
    }

    public function update(StudiLanjutRequest $request, StudiLanjut $studiLanjut): RedirectResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('dokumen_sk')) {
                $file = $request->file('dokumen_sk');
                $filename = 'sk_studi_' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                $destinationPath = public_path('uploads/studi_lanjut');

                if (! File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);

                // Remove existing file
                if ($studiLanjut->dokumen_sk && File::exists(public_path($studiLanjut->dokumen_sk))) {
                    File::delete(public_path($studiLanjut->dokumen_sk));
                }

                $data['dokumen_sk'] = 'uploads/studi_lanjut/' . $filename;
            }

            $studiLanjut->update($data);

            Alert::success('Berhasil', 'Data monitoring studi lanjut berhasil diperbarui.');

            return redirect()->route('kepegawaian.studi-lanjut.show', $studiLanjut);
        } catch (\Throwable $exception) {
            report($exception);
            Alert::error('Gagal', 'Terjadi kesalahan saat memperbarui data studi lanjut.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(StudiLanjut $studiLanjut): RedirectResponse
    {
        try {
            if ($studiLanjut->dokumen_sk && File::exists(public_path($studiLanjut->dokumen_sk))) {
                File::delete(public_path($studiLanjut->dokumen_sk));
            }

            $studiLanjut->delete();

            Alert::success('Berhasil', 'Data studi lanjut berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Gagal', 'Data studi lanjut gagal dihapus.');
        }

        return redirect()->route('kepegawaian.studi-lanjut.index');
    }

    public function searchPegawais(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('q', ''));
        $page = max((int) $request->get('page', 1), 1);
        $perPage = 15;

        $query = Pegawai::query()->with(['program_studi.jurusan', 'unit_kerja']);

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nip', 'like', '%' . $search . '%');
            });
        }

        $total = $query->count();
        $pegawais = $query->orderBy('nama')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $pegawais->map(function (Pegawai $pegawai) {
            $namaLengkap = $pegawai->nama_lengkap;
            $unit = $pegawai->program_studi?->program_studi ?? $pegawai->unit_kerja?->unit_kerja ?? '-';

            return [
                'id' => $pegawai->id,
                'text' => sprintf('%s - %s (%s)', $pegawai->nip ?? '-', $namaLengkap, $unit),
                'nama' => $namaLengkap,
                'nip' => $pegawai->nip,
                'unit' => $unit,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('search', ''));
        $progres = trim((string) $request->input('progres', ''));
        $jenisPembiayaan = trim((string) $request->input('jenis_pembiayaan', ''));
        $jenisTugas = trim((string) $request->input('jenis_tugas', ''));
        $bidangIlmu = trim((string) $request->input('bidang_ilmu', ''));

        $query = StudiLanjut::query()
            ->with(['pegawai.program_studi.jurusan', 'pegawai.unit_kerja'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('program_studi', 'like', '%' . $search . '%')
                    ->orWhere('nama_institusi', 'like', '%' . $search . '%')
                    ->orWhere('bidang_ilmu', 'like', '%' . $search . '%')
                    ->orWhere('nomor_sk', 'like', '%' . $search . '%')
                    ->orWhereHas('pegawai', function ($pegawaiQuery) use ($search) {
                        $pegawaiQuery->where('nama', 'like', '%' . $search . '%')
                            ->orWhere('nip', 'like', '%' . $search . '%');
                    });
            });
        }

        if (in_array($progres, array_keys(StudiLanjut::PROGRES_OPTIONS), true)) {
            $query->where('progres', $progres);
        }

        if (in_array($jenisPembiayaan, array_keys(StudiLanjut::JENIS_PEMBIAYAAN_OPTIONS), true)) {
            $query->where('jenis_pembiayaan', $jenisPembiayaan);
        }

        if (in_array($jenisTugas, array_keys(StudiLanjut::JENIS_TUGAS_OPTIONS), true)) {
            $query->where('jenis_tugas', $jenisTugas);
        }

        if (in_array($bidangIlmu, array_keys(StudiLanjut::BIDANG_ILMU_OPTIONS), true)) {
            $query->where('bidang_ilmu', $bidangIlmu);
        }

        $records = $query->get();
        $fileName = 'monitoring_pegawai_studi_lanjut_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            fputcsv($handle, [
                'No',
                'NIP',
                'Nama Pegawai',
                'Homebase / Unit Kerja',
                'Jenjang',
                'Program Studi Tujuan',
                'Perguruan Tinggi / Institusi',
                'Negara',
                'Bidang Ilmu',
                'Jenis Tugas',
                'Jenis Pembiayaan',
                'Nama Beasiswa / Sumber Dana',
                'Progres Studi',
                'Tanggal Mulai',
                'Target Selesai',
                'Tanggal Selesai Riil',
                'Nomor SK',
                'Tanggal SK',
                'Keterangan',
            ]);

            $no = 1;
            foreach ($records as $item) {
                fputcsv($handle, [
                    $no++,
                    $item->pegawai?->nip ?? '-',
                    $item->pegawai?->nama_lengkap ?? '-',
                    $item->pegawai?->program_studi?->program_studi ?? $item->pegawai?->unit_kerja?->unit_kerja ?? '-',
                    $item->jenjang ?? '-',
                    $item->program_studi,
                    $item->nama_institusi,
                    $item->negara ?? 'Indonesia',
                    $item->bidang_ilmu,
                    $item->jenis_tugas,
                    $item->jenis_pembiayaan_label,
                    $item->nama_beasiswa ?? '-',
                    $item->progres_label,
                    $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-',
                    $item->target_selesai ? $item->target_selesai->format('d/m/Y') : '-',
                    $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-',
                    $item->nomor_sk ?? '-',
                    $item->tanggal_sk ? $item->tanggal_sk->format('d/m/Y') : '-',
                    $item->keterangan ?? '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
