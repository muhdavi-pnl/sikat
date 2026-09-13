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
        DB::table('layanans')->insertOrIgnore([
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'cuti', 'layanan' => 'Cuti Tahunan', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'cuti', 'layanan' => 'Cuti Besar', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'cuti', 'layanan' => 'Cuti Sakit', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'cuti', 'layanan' => 'Cuti Melahirkan', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'cuti', 'layanan' => 'Cuti Karena Alasan Penting (CAP)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'cuti', 'layanan' => 'Cuti di Luar Tanggungan Negara (CLTN)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Asisten Ahli', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Lektor', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Lektor Kepala', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'fungsional', 'layanan' => 'Usul Jabatan Fungsional Profesor', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Usul Tanda Kehormatan Satyalancana Karya Satya X', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Usul Tanda Kehormatan Satyalancana Karya Satya XX', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Usul Tanda Kehormatan Satyalancana Karya Satya XXX', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Kenaikan Pangkat Reguler', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Kenaikan Pangkat Jabatan Fungsional', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Penetapan SK Kenaikan Jabatan Fungsional', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Pensiun', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Surat Keterangan Tunjangan Keluarga (KP4)', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Pencantuman Gelar', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Tugas Belajar', 'deskripsi' => null],
            ['created_at' => date('Y-m-d H:i:s'), 'jenis' => 'kepegawaian', 'layanan' => 'Perpanjangan Tugas Belajar', 'deskripsi' => null],
        ]);
    }
}
