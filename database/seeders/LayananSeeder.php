<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('layanans')->insert([
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Asisten Ahli (150)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Lektor (200)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Lektor (300)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Lektor Kepala (400)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Lektor Kepala (550)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Lektor Kepala (700)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Profesor (850)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Profesor (1050)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Usul Surat Keputusan PNS', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Layanan Cuti Pegawai', 'deskripsi' => 'Layanan pengajuan cuti berdasarkan Formulir Permintaan dan Pemberian Cuti PNS (Lampiran 1.B).'],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Usul Tanda Kehormatan Satyalancana Karya Satya X', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Usul Tanda Kehormatan Satyalancana Karya Satya XX', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Usul Tanda Kehormatan Satyalancana Karya Satya XXX', 'deskripsi' => null],
        ]);
    }
}
