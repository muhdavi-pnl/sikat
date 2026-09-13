<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLokasiArsipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lokasi_arsips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rak_id')
                ->constrained('raks')
                ->onUpdate('cascade');
            $table->foreignId('pegawai_id')
                ->constrained('pegawais')
                ->onUpdate('cascade');
            $table->string('keterangan', 100)->nullable();
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
        Schema::dropIfExists('lokasi_arsips');
    }
}
