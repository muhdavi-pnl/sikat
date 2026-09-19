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
        Schema::create('kelompok_keahlians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelompok', 100);
            $table->text('deskripsi')->nullable();
            $table->string('ketua_kelompok', 75)->nullable();
            $table->string('bidang_penelitian')->nullable();
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusans')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_keahlians');
    }
};
