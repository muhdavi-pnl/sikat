<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_asal_id')->constrained('jabatans')->cascadeOnDelete();
            $table->foreignId('jabatan_tujuan_id')->constrained('jabatans')->cascadeOnDelete();
            $table->json('persyaratan')->nullable();
            $table->json('kompetensi_belum_terpenuhi')->nullable();
            $table->json('pendidikan_belum_terpenuhi')->nullable();
            $table->unsignedSmallInteger('pengalaman_minimal_tahun')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_paths');
    }
};
