<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

trait SeedsDomisiliReferenceData
{
    protected function seedDomisiliReferenceData(): array
    {
        DB::table('provinsis')->insert([
            [
                'id' => 1,
                'provinsi' => 'Aceh',
            ],
            [
                'id' => 2,
                'provinsi' => 'Sumatera Utara',
            ],
        ]);

        DB::table('kabupatens')->insert([
            [
                'id' => 1,
                'kabupaten' => 'Lhokseumawe',
                'provinsi_id' => 1,
            ],
            [
                'id' => 2,
                'kabupaten' => 'Medan',
                'provinsi_id' => 2,
            ],
        ]);

        DB::table('kecamatans')->insert([
            [
                'id' => 1,
                'kecamatan' => 'Banda Sakti',
                'kabupaten_id' => 1,
            ],
            [
                'id' => 2,
                'kecamatan' => 'Medan Kota',
                'kabupaten_id' => 2,
            ],
        ]);

        DB::table('kelurahans')->insert([
            [
                'id' => 1,
                'desa' => 'Sukamaju',
                'kode_pos' => '24352',
                'kecamatan_id' => 1,
            ],
            [
                'id' => 2,
                'desa' => 'Sei Rengas I',
                'kode_pos' => '20212',
                'kecamatan_id' => 2,
            ],
        ]);

        return [
            'primary' => [
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'kecamatan_id' => 1,
                'kelurahan_id' => 1,
            ],
            'secondary' => [
                'provinsi_id' => 2,
                'kabupaten_id' => 2,
                'kecamatan_id' => 2,
                'kelurahan_id' => 2,
            ],
        ];
    }
}

