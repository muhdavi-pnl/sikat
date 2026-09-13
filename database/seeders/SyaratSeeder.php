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
            ['id' => 1, 'dokumen_id' => 1, 'kode_syarat' => null, 'syarat' => 'Daftar Riwayat Hidup', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'dokumen_id' => 3, 'kode_syarat' => null, 'syarat' => 'SK CPNS', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'dokumen_id' => 4, 'kode_syarat' => null, 'syarat' => 'Surat Pernyataan Melaksanakan Tugas CPNS', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'dokumen_id' => 6, 'kode_syarat' => null, 'syarat' => 'SK PNS', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Pas Foto', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'dokumen_id' => 5, 'kode_syarat' => null, 'syarat' => 'Surat Tanda Tamat Pendidikan dan Pelatihan (STTPL) CPNS', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Pengantar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Daftar Keluarga', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Laporan Perkawinan Pertama', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Buku Nikah', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SKP 1 Tahun Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SKP 2 Tahun Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Sehat Jasmani dan Rohani', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Kenaikan Pangkat Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Ijazah dan Transkrip', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Tugas Belajar/Izin Belajar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Sertifikat Akreditasi Program Studi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 18, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Konversi NIP', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 19, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Penambahan Gelar', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Jabatan Penetapan Angka Kredit', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Asli Penetapan Angka Kredit', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 22, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Pembebasan Sementara', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 23, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Pengaktifan Kembali', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 24, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Asli Uraian Tugas pada Jabatan Baru', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 25, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Pernyataan Keaslian Dokumen', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 26, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Surat Pernyataan Pertanggungjawaban Mutlak', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 27, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'SK Jabatan Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 28, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'PAK Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 29, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Gaji Pokok Terakhir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 30, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'KARPEG', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 31, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'NIP Baru', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 32, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'NIDN (Katu Dosen)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 33, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Daftar Mata Kuliah Pokok', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 34, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Daftar Kehadiran PBM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 35, 'dokumen_id' => null, 'kode_syarat' => null, 'syarat' => 'Hasil Tes Kesehatan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 36, 'dokumen_id' => null, 'kode_syarat' => 'CUTI_FORM', 'syarat' => 'Formulir Permintaan dan Pemberian Cuti (Lampiran 1.B) yang sudah diisi.', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 37, 'dokumen_id' => null, 'kode_syarat' => 'CUTI_ATSN', 'syarat' => 'Persetujuan/pertimbangan atasan langsung pada formulir cuti (opsional).', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 38, 'dokumen_id' => null, 'kode_syarat' => 'CUTI_BUKTI', 'syarat' => 'Dokumen pendukung alasan cuti (opsional, jika diperlukan sesuai jenis cuti).', 'created_at' => $now, 'updated_at' => $now],
        ], ['id'], ['dokumen_id', 'kode_syarat', 'syarat', 'updated_at']);
    }
}
