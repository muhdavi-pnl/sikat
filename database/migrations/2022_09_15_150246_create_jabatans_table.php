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
            $table->foreignId('jenis_jabatan_id')->nullable()->constrained('jenis_jabatans')->nullOnDelete();
            $table->unsignedTinyInteger('kelas_jabatan')->nullable();
            $table->foreignId('pangkat_minimal')->nullable()->constrained('pangkats')->nullOnDelete();
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jabatans');
    }
}
