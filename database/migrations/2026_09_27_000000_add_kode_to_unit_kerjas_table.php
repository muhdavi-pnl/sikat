<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('unit_kerjas', function (Blueprint $table) {
            $table->string('kode', 50)->nullable()->after('unit_kerja');
        });

        $map = [
            'Politeknik Negeri Lhokseumawe' => 'PNL',
            'Jurusan Teknologi Informasi dan Komputer' => 'JTIK',
            'Jurusan Teknik Sipil' => 'JTS',
            'Jurusan Teknik Kimia' => 'JTK',
            'Jurusan Teknik Mesin' => 'JTM',
            'Jurusan Teknik Elektro' => 'JTE',
            'Jurusan Bisnis' => 'JBN',
            'Bagian Akademik, Kemahasiswaan, dan Alumni' => 'BAKA',
            'Bagian Perencanaan, Keuangan, dan Umum' => 'BPKU',
            'Subbagian Akademik' => 'SBA',
            'Subbagian Umum' => 'SBU',
            'Pusat Penelitian dan Pengabdian Kepada Masyarakat' => 'P3M',
            'Pusat Penjaminan Mutu dan Pengembangan Pembelajaran' => 'P4M',
            'UPA Perpustakaan' => 'UPAPERPUS',
            'UPA Teknologi Informasi dan Komunikasi' => 'UPATIK',
            'UPA Bahasa' => 'UPABAHASA',
            'UPA Perawatan dan Perbaikan' => 'UPAPP',
            'UPA Layanan Uji Kompetensi' => 'UPALUK',
            'UPA Pengembangan Karir dan Kemahasiswaan' => 'UPAPKK',
            'UPA Pengembangan Teknologi dan Produk Unggulan' => 'UPAPTPU',
            'Bidang Akademik dan Sistem Informasi' => 'BAKSI',
            'Bidang Keuangan dan Umum' => 'BKU',
            'Bidang Kemahasiswaan dan Alumni' => 'BKA',
            'Bidang Perencanaan dan Kerja Sama' => 'BPKS',
            'Senat' => 'SENAT',
            'Satuan Pengawas Internal' => 'SPI',
        ];

        foreach ($map as $name => $code) {
            DB::table('unit_kerjas')->where('unit_kerja', $name)->update(['kode' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_kerjas', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }
};
