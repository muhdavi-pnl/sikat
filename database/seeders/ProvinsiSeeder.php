<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('provinsis')->insert([
            ['id' => '11', 'provinsi' => 'Aceh'],
            ['id' => '12', 'provinsi' => 'Sumatera Utara'],
            ['id' => '13', 'provinsi' => 'Sumatera Barat'],
            ['id' => '14', 'provinsi' => 'Riau'],
            ['id' => '15', 'provinsi' => 'Jambi'],
            ['id' => '16', 'provinsi' => 'Sumatera Selatan'],
            ['id' => '17', 'provinsi' => 'Bengkulu'],
            ['id' => '18', 'provinsi' => 'Lampung'],
            ['id' => '19', 'provinsi' => 'Kep. Bangka Belitung'],
            ['id' => '21', 'provinsi' => 'Kep. Riau'],
            ['id' => '31', 'provinsi' => 'Dki Jakarta'],
            ['id' => '32', 'provinsi' => 'Jawa Barat'],
            ['id' => '33', 'provinsi' => 'Jawa Tengah'],
            ['id' => '34', 'provinsi' => 'Di Yogyakarta'],
            ['id' => '35', 'provinsi' => 'Jawa Timur'],
            ['id' => '36', 'provinsi' => 'Banten'],
            ['id' => '51', 'provinsi' => 'Bali'],
            ['id' => '52', 'provinsi' => 'Nusa Tenggara Barat'],
            ['id' => '53', 'provinsi' => 'Nusa Tenggara Timur'],
            ['id' => '61', 'provinsi' => 'Kalimantan Barat'],
            ['id' => '62', 'provinsi' => 'Kalimantan Tengah'],
            ['id' => '63', 'provinsi' => 'Kalimantan Selatan'],
            ['id' => '64', 'provinsi' => 'Kalimantan Timur'],
            ['id' => '65', 'provinsi' => 'Kalimantan Utara'],
            ['id' => '71', 'provinsi' => 'Sulawesi Utara'],
            ['id' => '72', 'provinsi' => 'Sulawesi Tengah'],
            ['id' => '73', 'provinsi' => 'Sulawesi Selatan'],
            ['id' => '74', 'provinsi' => 'Sulawesi Tenggara'],
            ['id' => '75', 'provinsi' => 'Gorontalo'],
            ['id' => '76', 'provinsi' => 'Sulawesi Barat'],
            ['id' => '81', 'provinsi' => 'Maluku'],
            ['id' => '82', 'provinsi' => 'Maluku Utara'],
            ['id' => '92', 'provinsi' => 'Papua'],
            ['id' => '91', 'provinsi' => 'Papua Barat'],
        ]);
    }
}
