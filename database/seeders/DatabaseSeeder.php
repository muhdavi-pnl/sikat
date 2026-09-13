<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Cara penggunaan:
     * - Default (otomatis berdasarkan APP_ENV):
     *   php artisan db:seed
     * - Khusus Deployment (Production):
     *   php artisan db:seed --class=DeploymentSeeder
     * - Khusus Development (Local):
     *   php artisan db:seed --class=DevelopmentSeeder
     *
     * @return void
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->info('Menjalankan Seeder untuk Deployment (Production)...');
            $this->call(DeploymentSeeder::class);
        } else {
            $this->command?->info('Menjalankan Seeder untuk Development (Local)...');
            $this->call(DevelopmentSeeder::class);
        }
    }
}
