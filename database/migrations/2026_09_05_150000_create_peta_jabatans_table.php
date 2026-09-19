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
        Schema::create('peta_jabatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_id')->constrained('jabatans')->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->foreignId('atasan_langsung_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->unsignedInteger('kebutuhan_pegawai')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peta_jabatans');
    }
};
