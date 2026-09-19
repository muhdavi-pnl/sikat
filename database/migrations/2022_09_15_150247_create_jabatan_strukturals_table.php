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
        Schema::create('jabatan_strukturals', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jabatan', 50)->nullable();
            $table->string('jabatan', 150);
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
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatan_strukturals');
    }
};
