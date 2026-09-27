<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'tmt_pppk')) {
                $table->date('tmt_pppk')->nullable()->after('tmt_pns');
            }
            if (!Schema::hasColumn('pegawais', 'tanggal_akhir_kontrak')) {
                $table->date('tanggal_akhir_kontrak')->nullable()->after('tmt_pppk');
            }
            if (!Schema::hasColumn('pegawais', 'status_payroll')) {
                $table->string('status_payroll', 20)->default('aktif')->after('status_pegawai');
            }
            if (!Schema::hasColumn('pegawais', 'kompensasi_cuti_bersama')) {
                $table->unsignedSmallInteger('kompensasi_cuti_bersama')->default(0)->after('cuti_hari_tersedia');
            }
        });

        Schema::table('cuti_layanan_pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('cuti_layanan_pegawais', 'kategori_cuti')) {
                $table->string('kategori_cuti', 50)->nullable()->after('jenis_cuti');
            }
            if (!Schema::hasColumn('cuti_layanan_pegawais', 'alasan_cap')) {
                $table->string('alasan_cap', 100)->nullable()->after('alasan_cuti');
            }
            if (!Schema::hasColumn('cuti_layanan_pegawais', 'alasan_pppk_bypass')) {
                $table->string('alasan_pppk_bypass', 100)->nullable()->after('alasan_cap');
            }
            if (!Schema::hasColumn('cuti_layanan_pegawais', 'kelahiran_anak_ke')) {
                $table->unsignedTinyInteger('kelahiran_anak_ke')->nullable()->after('alasan_pppk_bypass');
            }
            if (!Schema::hasColumn('cuti_layanan_pegawais', 'rekomendasi_tim_penguji_kesehatan')) {
                $table->boolean('rekomendasi_tim_penguji_kesehatan')->default(false)->after('kelahiran_anak_ke');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuti_layanan_pegawais', function (Blueprint $table) {
            $table->dropColumn([
                'kategori_cuti',
                'alasan_cap',
                'alasan_pppk_bypass',
                'kelahiran_anak_ke',
                'rekomendasi_tim_penguji_kesehatan',
            ]);
        });

        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn([
                'tmt_pppk',
                'tanggal_akhir_kontrak',
                'status_payroll',
                'kompensasi_cuti_bersama',
            ]);
        });
    }
};
