<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLayananSyaratTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('layanan_syarat', function (Blueprint $table) {
            $table->foreignId('layanan_id')
                ->constrained('layanans')
                ->onUpdate('cascade');
            $table->foreignId('syarat_id')
                ->constrained('syarats')
                ->onUpdate('cascade');
            $table->primary(['layanan_id', 'syarat_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('layanan_syarat');
    }
}
