<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePegawaiIdentitasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pegawai_identitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->unique()->constrained('pegawais')->cascadeOnDelete();
            $table->string('id_wos', 15)->nullable()->unique();
            $table->string('id_orc', 20)->nullable()->unique();
            $table->string('id_sinta', 10)->nullable()->unique();
            $table->string('id_scopus', 15)->nullable()->unique();
            $table->string('id_garuda', 10)->nullable()->unique();
            $table->string('id_gscholar', 15)->nullable()->unique();
            $table->string('nidn', 10)->nullable()->unique();
            $table->string('nuptk', 16)->nullable()->unique();
            $table->enum('jabatan_fungsional', ['asisten ahli', 'lektor', 'lektor kepala', 'profesor'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pegawai_identitas');
    }
}
