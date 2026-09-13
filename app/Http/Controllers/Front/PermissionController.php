<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $paginate = 10;
        return view('user.permission', [
            'permissions' => Permission::paginate($paginate),
            'title' => 'Izin Akses',
        ])->with('i', (request()->input('page', 1) - 1) * 10);
    }
}
