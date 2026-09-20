<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paginate = 10;
        return view('unit-kerja.index', [
            'unit_kerjas' => UnitKerja::orderBy('order', 'asc')->orderBy('id', 'asc')->paginate($paginate),
            'title' => 'Unit Kerja',
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UnitKerja  $unitKerja
     * @return \Illuminate\Http\Response
     */
    public function show(UnitKerja $unitKerja)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UnitKerja  $unitKerja
     * @return \Illuminate\Http\Response
     */
    public function edit(UnitKerja $unitKerja)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UnitKerja  $unitKerja
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UnitKerja $unitKerja)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UnitKerja  $unitKerja
     * @return \Illuminate\Http\Response
     */
    public function destroy(UnitKerja $unitKerja)
    {
        //
    }
}
