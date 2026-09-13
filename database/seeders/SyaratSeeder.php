<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyaratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        DB::table('syarats')->upsert([
            ['id' => 2, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK CPNS', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK PNS', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Pas Foto Berwarna', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Buku Nikah', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SKP 1 Tahun Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SKP 2 Tahun Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Keterangan Sehat Jasmani dan Rohani', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Pangkat Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Jabatan Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Tugas Belajar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'AK Integrasi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 18, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Penetapan Angka Kredit', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 19, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Sertifikat Uji Kompetensi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Peta Jabatan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Rekomendasi Instansi Pembina', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 22, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Kartu Keluarga', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 23, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Kartu Tanda Penduduk', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 24, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Ijazah Pendidikan Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 25, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Transkrip Nilai Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 26, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Pernyataan Tanggung Jawab Mutlak Kebutuhan Organisasi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 27, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Rekomendasi dari Pimpinan Unit Kerja', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 28, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Perjanjian Tugas Belajar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 29, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Jaminan Pembiayaan Tugas Belajar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 30, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Penerimaan dari Lembaga Pendidikan Tempat Pelaksanaan Tugas Belajar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 31, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Pernyataan dari Pimpinan Unit Kerja (9 Poin)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 32, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Pernyataan dari Calon Pegawai Tugas Belajar (4 Poin)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 33, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Rekomendasi dari Lembaga Pendidikan Tempat Pegawai Pelajar Melaksanakan Tugas Belajar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 34, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Jaminan Perpanjangan Pembiayaan Tugas Belajar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 35, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Akte Kelahiran', 'created_at' => $now, 'updated_at' => $now],
            
        ], ['id'], ['dokumen_id', 'kode_syarat', 'syarat', 'updated_at']);
    }
}
