<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;

class JabatanController extends Controller
{
    public function index()
    {
        return redirect()->route('peta-jabatan.manage.index', ['slug' => 'jabatan']);
    }

    public function create()
    {
        return redirect()->route('peta-jabatan.manage.create', ['slug' => 'jabatan']);
    }

    public function show(Jabatan $jabatan)
    {
        return redirect()->route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->id]);
    }

    public function edit(Jabatan $jabatan)
    {
        return redirect()->route('peta-jabatan.manage.edit', ['slug' => 'jabatan', 'id' => $jabatan->id]);
    }
}
