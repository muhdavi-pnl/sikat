<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCutiLayananPegawaisTable extends Migration
{
    public function up()
    {
        Schema::create('cuti_layanan_pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_pegawai_id')->unique()->constrained('layanan_pegawais')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('jenis_cuti', 50)->nullable();
            $table->text('alasan_cuti')->nullable();
            $table->text('alamat_menjalankan_cuti')->nullable();
            $table->string('nomor_telepon_cuti', 50)->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedSmallInteger('hari_diminta')->nullable();
            $table->unsignedSmallInteger('hari_tersedia_saat_usul')->nullable();
            $table->timestamps();

            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cuti_layanan_pegawais');
    }
}
