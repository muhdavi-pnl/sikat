<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePegawaisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 18)->unique();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('nama', 150);
            $table->string('gelar_depan', 25)->nullable();
            $table->string('gelar_belakang', 30)->nullable();
            $table->string('tempat_lahir', 50)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->boolean('jenis_kelamin')->nullable();
            $table->tinyInteger('jumlah_anak')->nullable();
            $table->date('tanggal_lulus')->nullable();
            $table->string('npwp', 25)->nullable();
            $table->string('bpjs', 20)->nullable();
            $table->string('no_karpeg', 25)->nullable();
            $table->string('no_karis_karsu', 25)->nullable();
            $table->date('tmt_cpns')->nullable();
            $table->date('tmt_pns')->nullable();
            $table->date('tmt_jabatan')->nullable();
            $table->date('tmt_pmk')->nullable();
            $table->tinyInteger('pmk_tahun')->nullable();
            $table->tinyInteger('pmk_bulan')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('no_hp', 15)->nullable();
            $table->string('no_telp', 15)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahans');
            $table->char('eselon_id', 2)->nullable();
            $table->foreign('eselon_id')->references('id')->on('eselons')->onUpdate('cascade');
            $table->char('kedudukan_pegawai_id', 2)->nullable();
            $table->foreign('kedudukan_pegawai_id')->references('id')->on('kedudukan_pegawais')->onUpdate('cascade');
            $table->foreignId('agama_id')->nullable()->constrained('agamas');
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans');
            $table->foreignId('pangkat_id')->nullable()->constrained('pangkats');
            $table->foreignId('status_perkawinan_id')->nullable()->constrained('status_perkawinans');
            $table->foreignId('pendidikan_id')->nullable()->constrained('pendidikans');
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->enum('status_pegawai', ['CPNS', 'PNS', 'PPPK'])->default('PNS');
            $table->unsignedSmallInteger('cuti_hari_tersedia')->default(12);
            $table->enum('kelompok_pegawai', ['dosen', 'tenaga kependidikan'])->default('dosen');
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
        Schema::dropIfExists('pegawais');
    }
}
