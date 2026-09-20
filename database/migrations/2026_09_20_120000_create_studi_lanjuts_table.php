<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('studi_lanjuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->enum('progres', ['defer', 'ongoing', 'selesai'])->default('ongoing');
            $table->enum('jenis_pembiayaan', ['mandiri', 'beasiswa'])->default('beasiswa');
            $table->string('nama_beasiswa')->nullable();
            $table->enum('jenis_tugas', ['Meninggalkan Tugas', 'Menjalankan Tugas'])->default('Meninggalkan Tugas');
            $table->enum('bidang_ilmu', ['STEM', 'EKONOMI', 'SOSIAL', 'HUMANIORA', 'KEAGAMAAN'])->default('STEM');
            $table->string('jenjang')->nullable(); // e.g. S2, S3, Spesialis, Postdoctoral
            $table->string('program_studi');
            $table->string('nama_institusi');
            $table->string('negara')->default('Indonesia');
            $table->date('tanggal_mulai')->nullable();
            $table->date('target_selesai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->string('dokumen_sk')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('studi_lanjuts');
    }
};
