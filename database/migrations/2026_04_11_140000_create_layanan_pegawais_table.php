<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLayananPegawaisTable extends Migration
{
    public function up()
    {
        Schema::create('layanan_pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->enum('status', ['usulan', 'pending', 'proses', 'selesai', 'ditolak'])->default('usulan');
            $table->text('catatan_pengusul')->nullable();
            $table->unsignedSmallInteger('cuti_hari_diminta')->nullable();
            $table->unsignedSmallInteger('cuti_hari_tersedia')->nullable();
            $table->date('cuti_tanggal_mulai')->nullable();
            $table->date('cuti_tanggal_selesai')->nullable();
            $table->json('syarat_uploads')->nullable();
            $table->string('output_file')->nullable();
            $table->string('output_original_name')->nullable();
            $table->string('output_path')->nullable();
            $table->text('catatan_proses')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('output_uploaded_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('output_uploaded_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['pegawai_id', 'created_at']);
            $table->index(['status', 'created_at']);

            $table->unsignedTinyInteger('priority_score')->default(0);
            $table->timestamp('sla_due_at')->nullable();
            $table->string('sla_risk', 16)->default('none');

            $table->index(['sla_risk', 'priority_score']);
            $table->index('sla_due_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('layanan_pegawais');
    }
}

