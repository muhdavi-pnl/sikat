<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $paginate = 10;
        return view('user.role', [
            'roles' => Role::paginate($paginate),
            'title' => 'Peran',
        ])->with('i', (request()->input('page', 1) - 1) * $paginate);
    }
}
