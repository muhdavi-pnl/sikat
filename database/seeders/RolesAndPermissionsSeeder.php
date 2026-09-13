<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $routeCollection = Route::getRoutes()->get();
        foreach ($routeCollection as $value) {
            $name = $value->action;
            if(!empty($name['as'])) {
                $permission = $name['as'];
                $str = trim(strtolower($permission));
                $newStr = preg_replace('/[\s.,_-]+/', ' ', $str);
//                $permissions[] = $newStr;
                // firstOrCreate avoids unique-constraint failures for route
                // names that are intentionally registered more than once
                // (e.g. admin.crud.* is shared by a role-restricted group
                // for jabatan/career-path and the super-admin-only fallback).
                Permission::firstOrCreate([
                    'name' => $newStr
                ]);
            }
        }

        // this can be done as separate statements
        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());

        // Pimpinan: read-only access to organizational structure, peta
        // jabatan, kebutuhan pegawai analysis, and the dashboard (see
        // docs/peta-jabatan.md section 10).
        $pimpinan = Role::create(['name' => 'pimpinan']);
        $pimpinan->givePermissionTo([
            'dashboard',
            'landing',
            'peta jabatan index',
            'peta jabatan dashboard',
        ]);

        Role::create(['name' => 'kepegawaian']);
        Role::create(['name' => 'atasan']);

        $role = Role::create(['name' => 'pegawai']);
        $role->givePermissionTo([
            'pegawai edit',
            'pegawai update',
            'pegawai show',
        ]);
    }
}
