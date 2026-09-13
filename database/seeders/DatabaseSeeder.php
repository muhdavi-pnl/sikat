<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DataArsipSeeder::class,
            ProvinsiSeeder::class,
            KabupatenSeeder::class,
            KecamatanSeeder::class,
            KelurahanSeeder::class,
            DataReferensiSeeder::class,
            SyaratSeeder::class,
            LayananSeeder::class,
            LayananSyaratSeeder::class,
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            PegawaiSeeder::class,
            CutiWorkflowSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                DokumenPegawaiDemoSeeder::class,
            ]);
        }
    }
}
