<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PegawaiIntelligenceFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function profile_page_shows_intelligence_score_and_missing_profile_recommendations()
    {
        $user = User::factory()->create();

        Pegawai::create([
            'nip' => '198501012010011401',
            'nama' => 'Pegawai Insight',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        DB::table('dokumens')->insert([
            ['kode_dokumen' => 'KTP', 'nama_dokumen' => 'KTP'],
            ['kode_dokumen' => 'NPWP', 'nama_dokumen' => 'NPWP'],
            ['kode_dokumen' => 'KK', 'nama_dokumen' => 'Kartu Keluarga'],
        ]);

        $this->actingAs($user)
            ->get(route('pegawai.profile'))
            ->assertOk()
            ->assertSee('Skor Kelengkapan Data')
            ->assertSee('ID Google Scholar')
            ->assertSee('Dokumen belum ada');
    }

    /** @test */
    public function dokumen_page_shows_full_score_when_profile_and_required_documents_are_complete()
    {
        $user = User::factory()->create();

        DB::table('unit_kerjas')->insert([
            'id' => 1,
            'unit_kerja' => 'UPT TIK',
        ]);

        DB::table('perguruan_tinggis')->insert([
            'id' => 1,
            'perguruan_tinggi' => 'Politeknik Negeri Lhokseumawe',
        ]);

        DB::table('jurusans')->insert([
            'id' => 1,
            'jurusan' => 'Teknik Elektro',
            'perguruan_tinggi_id' => 1,
        ]);

        DB::table('program_studis')->insert([
            'id' => 1,
            'kode_prodi' => 'TI01',
            'nama_prodi' => 'Teknik Informatika',
            'tanggal_berdiri' => '2000-01-01',
            'status' => 'Aktif',
            'jenjang' => 'D4',
            'akreditasi' => 'A',
            'jurusan_id' => 1,
        ]);

        $pegawai = Pegawai::create([
            'nip' => '198501012010011402',
            'nama' => 'Pegawai Lengkap',
            'nidn' => '0123456789',
            'nik' => '1234567890123456',
            'email' => 'pegawai.lengkap@example.test',
            'no_hp' => '08123456789',
            'alamat' => 'Jalan Kenanga',
            'unit_kerja_id' => 1,
            'program_studi_id' => 1,
            'jabatan_fungsional' => 'lektor',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $ktpId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'KTP',
            'nama_dokumen' => 'KTP',
        ]);
        $npwpId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'NPWP',
            'nama_dokumen' => 'NPWP',
        ]);
        $kkId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'KK',
            'nama_dokumen' => 'Kartu Keluarga',
        ]);

        DB::table('dokumen_pegawai')->insert([
            [
                'dokumen_id' => $ktpId,
                'pegawai_id' => $pegawai->id,
                'user_id' => $user->id,
                'file' => 'ktp.pdf',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dokumen_id' => $npwpId,
                'pegawai_id' => $pegawai->id,
                'user_id' => $user->id,
                'file' => 'npwp.pdf',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dokumen_id' => $kkId,
                'pegawai_id' => $pegawai->id,
                'user_id' => $user->id,
                'file' => 'kk.pdf',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get(route('pegawai.dokumen'))
            ->assertOk()
            ->assertSee('Skor Kelengkapan Data')
            ->assertSee('100%')
            ->assertSee('Progress Kelengkapan Total')
            ->assertSee('aria-valuenow="100"', false)
            ->assertSee('Semua dokumen pada master sudah valid');
    }

    /** @test */
    public function dokumen_score_matches_partial_owned_documents_in_database()
    {
        $user = User::factory()->create();

        Pegawai::create([
            'nip' => '198501012010011403',
            'nama' => 'Pegawai Dokumen Sebagian',
            'status_pegawai' => 'PNS',
            'user_id' => $user->id,
        ]);

        $dokumenAId = DB::table('dokumens')->insertGetId([
            'kode_dokumen' => 'DOCA',
            'nama_dokumen' => 'Dokumen A',
        ]);
        DB::table('dokumens')->insert([
            ['kode_dokumen' => 'DOCB', 'nama_dokumen' => 'Dokumen B'],
            ['kode_dokumen' => 'DOCC', 'nama_dokumen' => 'Dokumen C'],
        ]);

        $pegawaiId = (int) DB::table('pegawais')->where('user_id', $user->id)->value('id');

        DB::table('dokumen_pegawai')->insert([
            'dokumen_id' => $dokumenAId,
            'pegawai_id' => $pegawaiId,
            'user_id' => $user->id,
            'file' => 'doc-a.pdf',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Draft/invalid upload should not be counted in score.
        $dokumenBId = (int) DB::table('dokumens')->where('kode_dokumen', 'DOCB')->value('id');
        DB::table('dokumen_pegawai')->insert([
            'dokumen_id' => $dokumenBId,
            'pegawai_id' => $pegawaiId,
            'user_id' => $user->id,
            'file' => 'doc-b-draft.pdf',
            'status' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('pegawai.dokumen'))
            ->assertOk()
            ->assertSee('Dokumen 33% (valid 1/3)')
            ->assertSee('DOCB')
            ->assertSee('DOCC');
    }
}

