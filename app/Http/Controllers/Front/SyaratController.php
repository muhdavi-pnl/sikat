<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyaratRequest;
use App\Models\Dokumen;
use App\Models\Syarat;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use RealRashid\SweetAlert\Facades\Alert;

class SyaratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paginate = 10;
        $keyword = trim((string) request()->input('search', ''));

        return view('syarat.index', [
            'syarats' => Syarat::query()
                ->with(['dokumen'])
                ->withCount('layanan')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($innerQuery) use ($keyword) {
                        $innerQuery->where('kode_syarat', 'like', '%' . $keyword . '%')
                            ->orWhere('syarat', 'like', '%' . $keyword . '%')
                            ->orWhereHas('dokumen', function ($dokumenQuery) use ($keyword) {
                                $dokumenQuery->where('kode_dokumen', 'like', '%' . $keyword . '%')
                                    ->orWhere('nama_dokumen', 'like', '%' . $keyword . '%');
                            });
                    });
                })
                ->orderBy('syarat')
                ->paginate($paginate)
                ->withQueryString()
                ->onEachSide(0),
            'cutiOptionalRequirementCodes' => $this->cutiOptionalRequirementCodes(),
            'title' => 'Syarat',
            'keyword' => $keyword,
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('syarat.create', $this->formData([
            'title' => 'Tambah Syarat',
            'syarat' => new Syarat(),
            'mappingSource' => 'manual',
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SyaratRequest $request)
    {
        try {
            $syarat = Syarat::create($this->normalizedPayload($request));

            Alert::success('Success', 'Syarat berhasil ditambahkan.');

            return redirect()->route('syarat.show', $syarat);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Syarat gagal disimpan.');

            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Syarat  $syarat
     * @return \Illuminate\Http\Response
     */
    public function show(Syarat $syarat)
    {
        return view('syarat.show', [
            'title' => 'Detail Syarat',
            'syarat' => $syarat->load(['dokumen', 'layanan']),
            'cutiOptionalRequirementCodes' => $this->cutiOptionalRequirementCodes(),
            'profileRequirementOptions' => $this->profileRequirementOptions(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Syarat  $syarat
     * @return \Illuminate\Http\Response
     */
    public function edit(Syarat $syarat)
    {
        return view('syarat.edit', $this->formData([
            'title' => 'Edit Syarat',
            'syarat' => $syarat->load('dokumen'),
            'mappingSource' => $this->resolveMappingSource($syarat),
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Syarat  $syarat
     * @return \Illuminate\Http\Response
     */
    public function update(SyaratRequest $request, Syarat $syarat)
    {
        try {
            $syarat->update($this->normalizedPayload($request));

            Alert::success('Success', 'Syarat berhasil diperbarui.');

            return redirect()->route('syarat.show', $syarat);
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Syarat gagal diperbarui.');

            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Syarat  $syarat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Syarat $syarat)
    {
        try {
            $syarat->delete();

            Alert::success('Success', 'Syarat berhasil dihapus.');
        } catch (QueryException $exception) {
            report($exception);
            Alert::error('Error', 'Syarat gagal dihapus.');
        }

        return redirect()->route('syarat.index');
    }

    protected function formData(array $data = [])
    {
        return array_merge([
            'cutiOptionalRequirementCodes' => $this->cutiOptionalRequirementCodes(),
            'dokumens' => Dokumen::query()->orderBy('nama_dokumen')->get(),
            'profileRequirementOptions' => $this->profileRequirementOptions(),
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

    protected function profileRequirementOptions(): array
    {
        $labels = (array) config('intelligence.profile_fields', []);

        return collect((array) config('intelligence.layanan_profile_requirement_codes', []))
            ->map(function ($field, $code) use ($labels) {
                return [
                    'code' => (string) $code,
                    'field' => (string) $field,
                    'label' => (string) ($labels[$field] ?? $field),
                ];
            })
            ->unique('code')
            ->sortBy('label')
            ->values()
            ->all();
    }

    protected function normalizedPayload(SyaratRequest $request): array
    {
        $validated = $request->validated();
        $mappingSource = (string) $validated['mapping_source'];

        if ($mappingSource === 'document') {
            $validated['kode_syarat'] = null;
        } elseif ($mappingSource === 'profile') {
            $validated['dokumen_id'] = null;
        } else {
            $validated['dokumen_id'] = null;
            $validated['kode_syarat'] = null;
        }

        return Arr::only($validated, ['kode_syarat', 'dokumen_id', 'syarat']);
    }

    protected function resolveMappingSource(Syarat $syarat): string
    {
        if ($syarat->dokumen_id) {
            return 'document';
        }

        if ($syarat->kode_syarat) {
            return 'profile';
        }

        return 'manual';
    }
}
