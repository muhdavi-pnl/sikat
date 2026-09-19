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
        if (!Schema::hasColumn('pegawais', 'eselon_id')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->char('eselon_id', 2)->nullable()->after('kedudukan_pegawai_id');
                $table->foreign('eselon_id')->references('id')->on('eselons')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pegawais', 'eselon_id')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropForeign(['eselon_id']);
                $table->dropColumn('eselon_id');
            });
        }
    }
};
