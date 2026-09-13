<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramStudisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_studis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_prodi', 5)->unique();
            $table->string('nama_prodi', 125);
            $table->date('tanggal_berdiri')->nullable();
            $table->enum('status', ['Aktif', 'Tutup', 'Pembinaan', 'Alih Bentuk']);
            $table->enum('jenjang', ['S2 Terapan', 'D4', 'D3', 'D2', 'D1']);
            $table->enum('akreditasi', ['A', 'Unggul', 'Baik Sekali', 'B', 'Baik', 'C', '-']);
            $table->foreignId('jurusan_id')->constrained('jurusans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_studis');
    }
}
