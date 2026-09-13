<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds for initial Super Admin in deployment/production.
     *
     * @return void
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        $email = env('ADMIN_EMAIL', 'sikat@muhdavi.com');
        $name = env('ADMIN_NAME', 'Muhammad D. Kahfi');
        $password = env('ADMIN_PASSWORD', 'Apt2019!');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        $admin->syncRoles([$superAdminRole]);
        $admin->syncPermissions(Permission::all());
    }
}
