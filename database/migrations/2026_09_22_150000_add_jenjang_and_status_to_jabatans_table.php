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
        Schema::table('jabatans', function (Blueprint $table) {
            $table->string('jenjang_jabatan', 100)->nullable()->after('jenis_jabatan_id');
            $table->string('status_jabatan', 50)->nullable()->default('Aktif')->after('jenjang_jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropColumn(['jenjang_jabatan', 'status_jabatan']);
        });
    }
};
