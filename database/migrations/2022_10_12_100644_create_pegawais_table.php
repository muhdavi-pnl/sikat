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
            $table->foreignId('pangkat_id')->nullable()->constrained('pangkats');
            $table->date('tmt_pangkat')->nullable();
            $table->date('tmt_cpns')->nullable();
            $table->date('tmt_pns')->nullable();
            $table->date('tmt_jabatan')->nullable();
            $table->date('tmt_pmk')->nullable();
            $table->tinyInteger('pmk_tahun')->nullable();
            $table->tinyInteger('pmk_bulan')->nullable();
            $table->char('kedudukan_pegawai_id', 2)->nullable();
            $table->foreign('kedudukan_pegawai_id')->references('id')->on('kedudukan_pegawais')->onUpdate('cascade');
            $table->enum('status_pegawai', ['CPNS', 'PNS', 'PPPK', 'PPPK Paruh Waktu'])->default('PNS');
            $table->enum('kelompok_pegawai', ['dosen', 'tendik'])->default('dosen');
            $table->foreignId('jenis_jabatan_id')->nullable()->constrained('jenis_jabatans')->onUpdate('cascade');
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans');
            $table->foreignId('jabatan_struktural_id')->nullable()->constrained('jabatan_strukturals');
            $table->char('eselon_id', 2)->nullable();
            $table->foreign('eselon_id')->references('id')->on('eselons')->onUpdate('cascade');
            $table->foreignId('pendidikan_id')->nullable()->constrained('pendidikans');
            $table->date('tanggal_lulus')->nullable();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas');
            $table->unsignedSmallInteger('cuti_hari_tersedia')->default(12);
            $table->string('npwp', 25)->nullable();
            $table->string('bpjs', 20)->nullable();
            $table->string('no_karpeg', 25)->nullable();
            $table->string('no_karis_karsu', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('no_hp', 15)->nullable();
            $table->string('no_telp', 15)->nullable();
            $table->string('alamat_asal', 255)->nullable();
            $table->foreignId('kelurahan_asal_id')->nullable()->constrained('kelurahans')->nullOnDelete();
            $table->string('alamat', 255)->nullable();
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahans');
            $table->foreignId('agama_id')->nullable()->constrained('agamas');
            $table->foreignId('status_perkawinan_id')->nullable()->constrained('status_perkawinans');
            $table->tinyInteger('jumlah_anak')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
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
