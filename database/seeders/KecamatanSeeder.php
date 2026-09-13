<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kecamatans')->insert([
//            calcode=CONCATENATE("[kab, ", "'id' => '",E3, "', 'kecamatan' => '",D3, "'],")
//            Aceh Timur
            ['kabupaten_id' => '1103', 'id' => '110301', 'kecamatan' => 'Darul Aman'],
            ['kabupaten_id' => '1103', 'id' => '110302', 'kecamatan' => 'Julok'],
            ['kabupaten_id' => '1103', 'id' => '110303', 'kecamatan' => 'Idi Rayeuk'],
            ['kabupaten_id' => '1103', 'id' => '110304', 'kecamatan' => 'Birem Bayeun'],
            ['kabupaten_id' => '1103', 'id' => '110305', 'kecamatan' => 'Serbajadi'],
            ['kabupaten_id' => '1103', 'id' => '110306', 'kecamatan' => 'Nurussalam'],
            ['kabupaten_id' => '1103', 'id' => '110307', 'kecamatan' => 'Peureulak'],
            ['kabupaten_id' => '1103', 'id' => '110308', 'kecamatan' => 'Rantau Selamat'],
            ['kabupaten_id' => '1103', 'id' => '110309', 'kecamatan' => 'Simpang Ulim'],
            ['kabupaten_id' => '1103', 'id' => '110310', 'kecamatan' => 'Rantau Peureulak'],
            ['kabupaten_id' => '1103', 'id' => '110311', 'kecamatan' => 'Pante Bidari'],
            ['kabupaten_id' => '1103', 'id' => '110312', 'kecamatan' => 'Madat'],
            ['kabupaten_id' => '1103', 'id' => '110313', 'kecamatan' => 'Indra Makmu'],
            ['kabupaten_id' => '1103', 'id' => '110314', 'kecamatan' => 'Idi Tunong'],
            ['kabupaten_id' => '1103', 'id' => '110315', 'kecamatan' => 'Banda Alam'],
            ['kabupaten_id' => '1103', 'id' => '110316', 'kecamatan' => 'Peudawa'],
            ['kabupaten_id' => '1103', 'id' => '110317', 'kecamatan' => 'Peureulak Timur'],
            ['kabupaten_id' => '1103', 'id' => '110318', 'kecamatan' => 'Peureulak Barat'],
            ['kabupaten_id' => '1103', 'id' => '110319', 'kecamatan' => 'Sungai Raya'],
            ['kabupaten_id' => '1103', 'id' => '110320', 'kecamatan' => 'Simpang Jernih'],
            ['kabupaten_id' => '1103', 'id' => '110321', 'kecamatan' => 'Darul Ihsan'],
            ['kabupaten_id' => '1103', 'id' => '110322', 'kecamatan' => 'Darul Falah'],
            ['kabupaten_id' => '1103', 'id' => '110323', 'kecamatan' => 'Idi Timur'],
            ['kabupaten_id' => '1103', 'id' => '110324', 'kecamatan' => 'Peunaron'],
//            Aceh Utara
            ['kabupaten_id' => '1108', 'id' => '110801', 'kecamatan' => 'Baktiya'],
            ['kabupaten_id' => '1108', 'id' => '110802', 'kecamatan' => 'Dewantara'],
            ['kabupaten_id' => '1108', 'id' => '110803', 'kecamatan' => 'Kuta Makmur'],
            ['kabupaten_id' => '1108', 'id' => '110804', 'kecamatan' => 'Lhoksukon'],
            ['kabupaten_id' => '1108', 'id' => '110805', 'kecamatan' => 'Matangkuli'],
            ['kabupaten_id' => '1108', 'id' => '110806', 'kecamatan' => 'Muara Batu'],
            ['kabupaten_id' => '1108', 'id' => '110807', 'kecamatan' => 'Meurah Mulia'],
            ['kabupaten_id' => '1108', 'id' => '110808', 'kecamatan' => 'Samudera'],
            ['kabupaten_id' => '1108', 'id' => '110809', 'kecamatan' => 'Seunuddon'],
            ['kabupaten_id' => '1108', 'id' => '110810', 'kecamatan' => 'Syamtalira Aron'],
            ['kabupaten_id' => '1108', 'id' => '110811', 'kecamatan' => 'Syamtalira Bayu'],
            ['kabupaten_id' => '1108', 'id' => '110812', 'kecamatan' => 'Tanah Luas'],
            ['kabupaten_id' => '1108', 'id' => '110813', 'kecamatan' => 'Tanah Pasir'],
            ['kabupaten_id' => '1108', 'id' => '110814', 'kecamatan' => 'T. Jambo Aye'],
            ['kabupaten_id' => '1108', 'id' => '110815', 'kecamatan' => 'Sawang'],
            ['kabupaten_id' => '1108', 'id' => '110816', 'kecamatan' => 'Nisam'],
            ['kabupaten_id' => '1108', 'id' => '110817', 'kecamatan' => 'Cot Girek'],
            ['kabupaten_id' => '1108', 'id' => '110818', 'kecamatan' => 'Langkahan'],
            ['kabupaten_id' => '1108', 'id' => '110819', 'kecamatan' => 'Baktiya Barat'],
            ['kabupaten_id' => '1108', 'id' => '110820', 'kecamatan' => 'Paya Bakong'],
            ['kabupaten_id' => '1108', 'id' => '110821', 'kecamatan' => 'Nibong'],
            ['kabupaten_id' => '1108', 'id' => '110822', 'kecamatan' => 'Simpang Kramat'],
            ['kabupaten_id' => '1108', 'id' => '110823', 'kecamatan' => 'Lapang'],
            ['kabupaten_id' => '1108', 'id' => '110824', 'kecamatan' => 'Pirak Timur'],
            ['kabupaten_id' => '1108', 'id' => '110825', 'kecamatan' => 'Geuredong Pase'],
            ['kabupaten_id' => '1108', 'id' => '110826', 'kecamatan' => 'Banda Baro'],
            ['kabupaten_id' => '1108', 'id' => '110827', 'kecamatan' => 'Nisam Antara'],
//            Lhokseumawe
            ['kabupaten_id' => '1173', 'id' => '117301', 'kecamatan' => 'Muara Dua'],
            ['kabupaten_id' => '1173', 'id' => '117302', 'kecamatan' => 'Banda Sakti'],
            ['kabupaten_id' => '1173', 'id' => '117303', 'kecamatan' => 'Blang Mangat'],
            ['kabupaten_id' => '1173', 'id' => '117304', 'kecamatan' => 'Muara Satu'],
//            Langsa
            ['kabupaten_id' => '1174', 'id' => '117401', 'kecamatan' => 'Langsa Timur'],
            ['kabupaten_id' => '1174', 'id' => '117402', 'kecamatan' => 'Langsa Barat'],
            ['kabupaten_id' => '1174', 'id' => '117403', 'kecamatan' => 'Langsa Kota'],
            ['kabupaten_id' => '1174', 'id' => '117404', 'kecamatan' => 'Langsa Lama'],
            ['kabupaten_id' => '1174', 'id' => '117405', 'kecamatan' => 'Langsa Baro'],
        ]);
    }
}
