<?php

namespace Tests\Unit;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiNamaFormattingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_formats_name_without_titles_when_no_titles_present()
    {
        $pegawai = new Pegawai([
            'nama' => 'Budi Santoso',
            'gelar_depan' => null,
            'gelar_belakang' => null,
        ]);

        $this->assertEquals('Budi Santoso', $pegawai->nama_tanpa_gelar);
        $this->assertEquals('Budi Santoso', $pegawai->nama_lengkap);
        $this->assertEquals('Budi Santoso', $pegawai->nama_dengan_gelar);
        $this->assertEquals('Budi Santoso', $pegawai->formatNama(true));
        $this->assertEquals('Budi Santoso', $pegawai->formatNama(false));
    }

    /** @test */
    public function it_formats_name_with_gelar_depan_only()
    {
        $pegawai = new Pegawai([
            'nama' => 'Budi Santoso',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => null,
        ]);

        $this->assertEquals('Budi Santoso', $pegawai->nama_tanpa_gelar);
        $this->assertEquals('Dr. Budi Santoso', $pegawai->nama_lengkap);
        $this->assertEquals('Dr. Budi Santoso', $pegawai->nama_dengan_gelar);
        $this->assertEquals('Dr. Budi Santoso', $pegawai->formatNama(true));
        $this->assertEquals('Budi Santoso', $pegawai->formatNama(false));
    }

    /** @test */
    public function it_formats_name_with_gelar_belakang_only()
    {
        $pegawai = new Pegawai([
            'nama' => 'Budi Santoso',
            'gelar_depan' => null,
            'gelar_belakang' => 'S.Kom., M.Cs.',
        ]);

        $this->assertEquals('Budi Santoso', $pegawai->nama_tanpa_gelar);
        $this->assertEquals('Budi Santoso, S.Kom., M.Cs.', $pegawai->nama_lengkap);
        $this->assertEquals('Budi Santoso, S.Kom., M.Cs.', $pegawai->nama_dengan_gelar);
        $this->assertEquals('Budi Santoso, S.Kom., M.Cs.', $pegawai->formatNama(true));
        $this->assertEquals('Budi Santoso', $pegawai->formatNama(false));
    }

    /** @test */
    public function it_formats_name_with_both_gelar_depan_and_belakang()
    {
        $pegawai = new Pegawai([
            'nama' => 'Hendra Syahputra',
            'gelar_depan' => 'Prof. Dr.',
            'gelar_belakang' => 'M.Sc.',
        ]);

        $this->assertEquals('Hendra Syahputra', $pegawai->nama_tanpa_gelar);
        $this->assertEquals('Prof. Dr. Hendra Syahputra, M.Sc.', $pegawai->nama_lengkap);
        $this->assertEquals('Prof. Dr. Hendra Syahputra, M.Sc.', $pegawai->nama_dengan_gelar);
        $this->assertEquals('Prof. Dr. Hendra Syahputra, M.Sc.', $pegawai->formatNama(true));
        $this->assertEquals('Hendra Syahputra', $pegawai->formatNama(false));
    }

    /** @test */
    public function static_helper_formats_names_cleanly_handling_spaces_and_leading_commas()
    {
        $formatted = Pegawai::formatNamaPegawai(' Ahmad Fauzi ', ' Ir. ', ' , M.T. ');
        $this->assertEquals('Ir. Ahmad Fauzi, M.T.', $formatted);

        $empty = Pegawai::formatNamaPegawai('', 'Dr.', 'M.Pd.');
        $this->assertEquals('', $empty);
    }

    /** @test */
    public function user_model_can_access_pegawai_name_with_or_without_titles()
    {
        $user = User::factory()->create([
            'name' => 'Budi Account',
            'email' => 'budi@test.com',
        ]);

        // When no linked pegawai
        $this->assertEquals('Budi Account', $user->nama_lengkap);
        $this->assertEquals('Budi Account', $user->nama_tanpa_gelar);
        $this->assertEquals('Budi Account', $user->formatNama(true));
        $this->assertEquals('Budi Account', $user->formatNama(false));

        // When linked to pegawai with titles
        $pegawai = Pegawai::create([
            'nama' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'S.Kom., M.Kom.',
            'user_id' => $user->id,
            'status_pegawai' => 'PNS',
        ]);

        $user->setRelation('pegawai', $pegawai);

        $this->assertEquals('Dr. Budi Santoso, S.Kom., M.Kom.', $user->nama_lengkap);
        $this->assertEquals('Dr. Budi Santoso, S.Kom., M.Kom.', $user->nama_dengan_gelar);
        $this->assertEquals('Budi Santoso', $user->nama_tanpa_gelar);
        $this->assertEquals('Dr. Budi Santoso, S.Kom., M.Kom.', $user->formatNama(true));
        $this->assertEquals('Budi Santoso', $user->formatNama(false));
    }
}
