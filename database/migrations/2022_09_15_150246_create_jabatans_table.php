<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJabatansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jabatan', 50)->nullable();
            $table->string('jabatan', 150);
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->unsignedTinyInteger('kelas_jabatan')->nullable();
            $table->string('pangkat_golongan', 50)->nullable();
            $table->string('pendidikan_minimal', 100)->nullable();
            $table->text('kompetensi')->nullable();
            $table->text('ikhtisar_jabatan')->nullable();
            $table->text('uraian_tugas')->nullable();
            $table->text('tanggung_jawab')->nullable();
            $table->text('wewenang')->nullable();
            $table->text('persyaratan_jabatan')->nullable();
            $table->string('beban_kerja', 100)->nullable();
            $table->foreignId('atasan_langsung_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->unsignedInteger('kebutuhan_pegawai')->default(0);
            $table->foreignId('jenis_jabatan_id')
                ->constrained('jenis_jabatans')
                ->onUpdate('cascade');
            $table->string('status_jabatan', 30)->nullable();
            $table->string('jenjang_jabatan', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jabatans');
    }
}
