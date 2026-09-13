<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\LayananRequest;
use App\Models\Layanan;
use App\Models\Pegawai;
use App\Models\Syarat;
use App\Services\CutiService;
use App\Services\Intelligence\LayananEligibilityService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class LayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paginate = 10;
        $keyword = trim((string) request()->input('search', ''));

        return view('layanan.index', [
            'layanans' => Layanan::query()
                ->withCount('syarat')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($innerQuery) use ($keyword) {
                        $innerQuery->where('layanan', 'like', '%' . $keyword . '%')
                            ->orWhere('jenis', 'like', '%' . $keyword . '%')
                            ->orWhere('deskripsi', 'like', '%' . $keyword . '%');
                    });
                })
                ->orderBy('layanan')
                ->paginate($paginate)
                ->withQueryString()
                ->onEachSide(0),
            'title' => 'Layanan',
            'keyword' => $keyword,
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('layanan.manage-create', $this->formData([
            'title' => 'Tambah Layanan',
            'layanan' => new Layanan(),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LayananRequest $request)
    {
        try {
            $validated = $request->validated();

            $layanan = DB::transaction(function () use ($validated) {
                $layanan = Layanan::create(Arr::only($validated, ['layanan', 'deskripsi', 'jenis']));
                $layanan->syarat()->sync($validated['syarat_ids'] ?? []);

                return $layanan;
            });

            Alert::success('Success', 'Layanan berhasil ditambahkan.');

            return redirect()->route('layanan.show', $layanan);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Layanan gagal disimpan.');

            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Layanan  $layanan
     * @return \Illuminate\Http\Response
     */
    public function show(Layanan $layanan)
    {
        return view('layanan.show', [
            'cutiOptionalRequirementCodes' => $this->cutiOptionalRequirementCodes(),
            'title' => 'Detail Layanan',
            'layanan' => $layanan->load(['syarat.dokumen']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Layanan  $layanan
     * @return \Illuminate\Http\Response
     */
    public function edit(Layanan $layanan)
    {
        return view('layanan.manage-edit', $this->formData([
            'title' => 'Edit Layanan',
            'layanan' => $layanan->load('syarat'),
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Layanan  $layanan
     * @return \Illuminate\Http\Response
     */
    public function update(LayananRequest $request, Layanan $layanan)
    {
        try {
            $validated = $request->validated();

            DB::transaction(function () use ($validated, $layanan) {
                $layanan->update(Arr::only($validated, ['layanan', 'deskripsi', 'jenis']));
                $layanan->syarat()->sync($validated['syarat_ids'] ?? []);
            });

            Alert::success('Success', 'Layanan berhasil diperbarui.');

            return redirect()->route('layanan.show', $layanan);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Layanan gagal diperbarui.');

            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Layanan  $layanan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Layanan $layanan)
    {
        try {
            DB::transaction(function () use ($layanan) {
                $layanan->syarat()->detach();
                $layanan->delete();
            });

            Alert::success('Success', 'Layanan berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Layanan gagal dihapus.');
        }

        return redirect()->route('layanan.index');
    }

    public function fungsional()
    {
        $layanans = Layanan::query()
            ->with('syarat')
            ->where('jenis', 'fungsional')
            ->has('syarat')
            ->get();

        return view('layanan.layanans', ['layanans' => $layanans, 'layanan' => 'Fungsional']);
    }

    public function kepegawaian()
    {
        $layanans = Layanan::query()
            ->with('syarat')
            ->where('jenis', 'kepegawaian')
            ->where('layanan', 'not like', '%cuti%')
            ->has('syarat')
            ->get();

        return view('layanan.layanans', ['layanans' => $layanans, 'layanan' => 'Kepegawaian']);
    }

    public function cuti()
    {
        $layanans = Layanan::query()
            ->with('syarat')
            ->where('jenis', 'kepegawaian')
            ->where('layanan', 'like', '%cuti%')
            ->has('syarat')
            ->get();

        return view('layanan.layanans', ['layanans' => $layanans, 'layanan' => 'Cuti']);
    }

    public function usul($id)
    {
        $pegawai = optional(auth()->user())->pegawai;

        if (!$pegawai) {
            alert()->info('Info', 'Maaf, data pegawai Anda belum tersedia.')->toHtml();

            return redirect()->route('dashboard');
        }

        $layanan = Layanan::query()->with('syarat.dokumen')->findOrFail($id);

        if (!$layanan instanceof Layanan) {
            abort(404);
        }

        $pegawai->loadMissing(['unit_kerja', 'program_studi', 'jabatan', 'pangkat']);
        $draft = request()->session()->get($this->layananUsulanDraftSessionKey($pegawai, (int) $layanan->getKey()), []);
        $draftUploadsBySyaratId = collect((array) data_get($draft, 'syarat_uploads', []))
            ->filter(function ($upload) {
                return (int) data_get($upload, 'syarat_id', 0) > 0;
            })
            ->keyBy(function ($upload) {
                return (int) data_get($upload, 'syarat_id', 0);
            });
        $isCutiLayanan = str_contains(mb_strtolower((string) $layanan->layanan), 'cuti');


        return view('layanan.create', [
            'layanan' => $layanan,
            'pegawai' => $pegawai,
            'isCutiLayanan' => $isCutiLayanan,
            'cutiHariTersedia' => $isCutiLayanan ? app(CutiService::class)->getSaldoCuti($pegawai) : null,
            'precheck' => app(LayananEligibilityService::class)->analyze(
                $pegawai,
                $layanan,
                $draftUploadsBySyaratId->keys()->map(function ($id) {
                    return (int) $id;
                })->all(),
                is_array($draft) ? $draft : []
            ),
            'draft' => is_array($draft) ? $draft : [],
            'draftUploadsBySyaratId' => $draftUploadsBySyaratId,
            'step' => 'checklist',
            'title' => 'Buat Usulan Layanan',
        ]);
    }

    public function usulan()
    {
        return view('layanan.selesai', [
            'title' => 'Layanan Selesai',
            'usulan' => null,
        ]);
    }

    public function rekap()
    {
        return view('kepegawaian.layanan.index', [
            'title' => 'Layanan',
        ]);
    }

    protected function formData(array $data = [])
    {
        return array_merge([
            'cutiOptionalRequirementCodes' => $this->cutiOptionalRequirementCodes(),
            'syarats' => Syarat::query()->with('dokumen')->orderBy('syarat')->get(),
            'jenisOptions' => [
                'kepegawaian' => 'Kepegawaian',
                'fungsional' => 'Fungsional',
            ],
        ], $data);
    }

    protected function cutiOptionalRequirementCodes(): array
    {
        return collect((array) config('intelligence.cuti_optional_requirement_codes', []))
            ->map(function ($code) {
                return mb_strtoupper(trim((string) $code));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function layananUsulanDraftSessionKey(Pegawai $pegawai, int $layananId): string
    {
        return 'pegawai.layanan.usulan_draft.' . $pegawai->id . '.' . $layananId;
    }
}
