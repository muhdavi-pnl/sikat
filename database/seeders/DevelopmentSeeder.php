<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds for development / local / testing environment.
     * Seeds all master data, plus demo users, demo pegawai profiles, cuti workflow samples, and demo documents.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            // 1. Core Master Data & Roles (from DeploymentSeeder)
            DeploymentSeeder::class,

            // 2. Development / Demo Users & Accounts
            UserSeeder::class,

            // 3. Development / Demo Pegawai Profiles
            PegawaiSeeder::class,

            // 4. Development / Demo Cuti Workflow & Transactions (All stages: Usulan, Proses, Selesai, Ditolak)
            CutiWorkflowSeeder::class,

            // 5. Development / Demo Uploaded Documents
            DokumenPegawaiSeeder::class,
        ]);
    }
}
