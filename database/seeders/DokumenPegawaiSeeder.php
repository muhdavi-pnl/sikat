<?php

namespace Database\Seeders;

use App\Models\Dokumen;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DokumenPegawaiSeeder extends Seeder
{
    public function run()
    {
        $pegawaiUser = User::firstOrCreate(
            ['email' => 'muhammad.davi@pnl.ac.id'],
            [
                'name' => 'Muhammad Davi',
                'password' => Hash::make('Sikat2019'),
            ]
        );

        if (method_exists($pegawaiUser, 'assignRole') && !$pegawaiUser->hasRole('pegawai')) {
            $pegawaiUser->assignRole('pegawai');
        }

        $pegawai = Pegawai::where('nama', 'Muhammad Davi')->first();
        if (!$pegawai) {
            $pegawai = Pegawai::create([
                'nip' => '198905102022031006',
                'nama' => 'Muhammad Davi',
                'status_pegawai' => 'PNS',
            ]);
        }

        if ((int) $pegawai->user_id !== (int) $pegawaiUser->id) {
            $pegawai->update(['user_id' => $pegawaiUser->id]);
        }

        $kepegawaian = User::firstOrCreate(
            ['email' => 'fakhruddin@pnl.ac.id'],
            [
                'name' => 'Fakhruddin',
                'password' => Hash::make('Sikat2019'),
            ]
        );

        if (method_exists($kepegawaian, 'assignRole') && !$kepegawaian->hasRole('kepegawaian')) {
            $kepegawaian->assignRole('kepegawaian');
        }

        $dokumenA = Dokumen::firstOrCreate(
            ['kode_dokumen' => 'DEMO-SKP'],
            ['nama_dokumen' => 'Dokumen SKP Demo']
        );

        $dokumenB = Dokumen::firstOrCreate(
            ['kode_dokumen' => 'DEMO-SERDIK'],
            ['nama_dokumen' => 'Dokumen Sertifikat Demo']
        );

        $targetDir = public_path('file/' . $pegawai->nip);
        File::ensureDirectoryExists($targetDir);

        $fileA = 'DEMO-SKP-seeder.pdf';
        $fileB = 'DEMO-SERDIK-seeder.pdf';

        if (!File::exists($targetDir . '/' . $fileA)) {
            File::put($targetDir . '/' . $fileA, 'Demo dokumen pegawai (SKP).');
        }

        if (!File::exists($targetDir . '/' . $fileB)) {
            File::put($targetDir . '/' . $fileB, 'Demo dokumen kepegawaian (Sertifikat).');
        }

        DB::table('dokumen_pegawai')->updateOrInsert(
            [
                'dokumen_id' => $dokumenA->id,
                'pegawai_id' => $pegawai->id,
            ],
            [
                'user_id' => $pegawaiUser->id,
                'file' => $fileA,
                'nomor' => 'SKP-DEMO-001',
                'tanggal' => now()->subDays(10)->toDateString(),
                'status' => false,
                'keterangan' => 'Unggah demo oleh pegawai',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('dokumen_pegawai')->updateOrInsert(
            [
                'dokumen_id' => $dokumenB->id,
                'pegawai_id' => $pegawai->id,
            ],
            [
                'user_id' => $kepegawaian->id,
                'file' => $fileB,
                'nomor' => 'KPG-DEMO-002',
                'tanggal' => now()->subDays(5)->toDateString(),
                'status' => true,
                'keterangan' => 'Unggah demo oleh kepegawaian',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

