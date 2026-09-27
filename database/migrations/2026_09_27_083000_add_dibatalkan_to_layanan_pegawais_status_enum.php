<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDibatalkanToLayananPegawaisStatusEnum extends Migration
{
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE layanan_pegawais MODIFY COLUMN status ENUM('usulan', 'pending', 'proses', 'selesai', 'ditolak', 'dibatalkan') NOT NULL DEFAULT 'usulan'");
        }
    }

    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE layanan_pegawais MODIFY COLUMN status ENUM('usulan', 'pending', 'proses', 'selesai', 'ditolak') NOT NULL DEFAULT 'usulan'");
        }
    }
}
