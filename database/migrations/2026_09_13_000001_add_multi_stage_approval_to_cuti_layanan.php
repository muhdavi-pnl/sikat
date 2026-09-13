<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultiStageApprovalToCutiLayanan extends Migration
{
    public function up()
    {
        Schema::create('pejabat_cuti_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->cascadeOnUpdate()->nullOnDelete();
            $table->string('jabatan_label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('cuti_layanan_pegawais', function (Blueprint $table) {
            $table->foreignId('atasan_pegawai_id')->nullable()->after('hari_tersedia_saat_usul')->constrained('pegawais')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('atasan_user_id')->nullable()->after('atasan_pegawai_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('atasan_status', 30)->nullable()->after('atasan_user_id');
            $table->text('catatan_atasan')->nullable()->after('atasan_status');
            $table->timestamp('atasan_approved_at')->nullable()->after('catatan_atasan');

            $table->foreignId('pybmc_pegawai_id')->nullable()->after('atasan_approved_at')->constrained('pegawais')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('pybmc_user_id')->nullable()->after('pybmc_pegawai_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('pybmc_status', 30)->nullable()->after('pybmc_user_id');
            $table->text('catatan_pybmc')->nullable()->after('pybmc_status');
            $table->timestamp('pybmc_approved_at')->nullable()->after('catatan_pybmc');

            $table->string('approval_stage', 30)->default('atasan')->after('pybmc_approved_at');
        });
    }

    public function down()
    {
        Schema::table('cuti_layanan_pegawais', function (Blueprint $table) {
            $table->dropForeign(['atasan_pegawai_id']);
            $table->dropForeign(['atasan_user_id']);
            $table->dropForeign(['pybmc_pegawai_id']);
            $table->dropForeign(['pybmc_user_id']);
            $table->dropColumn([
                'atasan_pegawai_id',
                'atasan_user_id',
                'atasan_status',
                'catatan_atasan',
                'atasan_approved_at',
                'pybmc_pegawai_id',
                'pybmc_user_id',
                'pybmc_status',
                'catatan_pybmc',
                'pybmc_approved_at',
                'approval_stage',
            ]);
        });

        Schema::dropIfExists('pejabat_cuti_settings');
    }
}
