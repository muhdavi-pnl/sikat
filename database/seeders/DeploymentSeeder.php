<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DeploymentSeeder extends Seeder
{
    /**
     * Run the database seeds for deployment / production environment.
     * Only seeds core master data, reference tables, roles, and initial super admin.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ProvinsiSeeder::class,
            KabupatenSeeder::class,
            KecamatanSeeder::class,
            KelurahanSeeder::class,
            DataReferensiSeeder::class,
            SyaratSeeder::class,
            LayananSeeder::class,
            PegawaiSeeder::class,
            UserSeeder::class,
        ]);
    }
}
