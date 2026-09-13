<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KabupatenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kabupatens')->insertOrIgnore([
//            calcode=CONCATENATE("[pro, ", "'id' => '",E3, "', 'kabupaten' => '",D3, "'],")
//            Aceh
            ['provinsi_id' => '11', 'id' => '1101', 'kabupaten' => 'Kab. Aceh Selatan'],
            ['provinsi_id' => '11', 'id' => '1102', 'kabupaten' => 'Kab. Aceh Tenggara'],
            ['provinsi_id' => '11', 'id' => '1103', 'kabupaten' => 'Kab. Aceh Timur'],
            ['provinsi_id' => '11', 'id' => '1104', 'kabupaten' => 'Kab. Aceh Tengah'],
            ['provinsi_id' => '11', 'id' => '1105', 'kabupaten' => 'Kab. Aceh Barat'],
            ['provinsi_id' => '11', 'id' => '1106', 'kabupaten' => 'Kab. Aceh Besar'],
            ['provinsi_id' => '11', 'id' => '1107', 'kabupaten' => 'Kab. Pidie'],
            ['provinsi_id' => '11', 'id' => '1108', 'kabupaten' => 'Kab. Aceh Utara'],
            ['provinsi_id' => '11', 'id' => '1109', 'kabupaten' => 'Kab. Simeulue'],
            ['provinsi_id' => '11', 'id' => '1110', 'kabupaten' => 'Kab. Aceh Singkil'],
            ['provinsi_id' => '11', 'id' => '1111', 'kabupaten' => 'Kab. Bireuen'],
            ['provinsi_id' => '11', 'id' => '1112', 'kabupaten' => 'Kab. Aceh Barat Daya'],
            ['provinsi_id' => '11', 'id' => '1113', 'kabupaten' => 'Kab. Gayo Lues'],
            ['provinsi_id' => '11', 'id' => '1114', 'kabupaten' => 'Kab. Aceh Jaya'],
            ['provinsi_id' => '11', 'id' => '1115', 'kabupaten' => 'Kab. Nagan Raya'],
            ['provinsi_id' => '11', 'id' => '1116', 'kabupaten' => 'Kab. Aceh Tamiang'],
            ['provinsi_id' => '11', 'id' => '1117', 'kabupaten' => 'Kab. Bener Meriah'],
            ['provinsi_id' => '11', 'id' => '1118', 'kabupaten' => 'Kab. Pidie Jaya'],
            ['provinsi_id' => '11', 'id' => '1171', 'kabupaten' => 'Kota Banda Aceh'],
            ['provinsi_id' => '11', 'id' => '1172', 'kabupaten' => 'Kota Sabang'],
            ['provinsi_id' => '11', 'id' => '1174', 'kabupaten' => 'Kota Langsa'],
            ['provinsi_id' => '11', 'id' => '1173', 'kabupaten' => 'Kota Lhokseumawe'],
            ['provinsi_id' => '11', 'id' => '1175', 'kabupaten' => 'Kota Subulussalam'],
        ]);
    }
}
