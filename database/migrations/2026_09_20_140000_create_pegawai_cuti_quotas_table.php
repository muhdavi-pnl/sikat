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
        Schema::create('pegawai_cuti_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedSmallInteger('hari_tersedia')->default(12);
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->unique(['pegawai_id', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_cuti_quotas');
    }
};
