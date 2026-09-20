<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\ProgramStudi;
use App\Models\StudiLanjut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudiLanjutFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'kepegawaian']);
        Role::firstOrCreate(['name' => 'pegawai']);
    }

    private function createManagerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('kepegawaian');

        return $user;
    }

    private function createPegawai(string $nama = 'Budi Santoso', string $nip = '198501012010121001'): Pegawai
    {
        return Pegawai::create([
            'nip' => $nip,
            'nama' => $nama,
            'status_pegawai' => 'PNS',
            'kelompok_pegawai' => 'dosen',
        ]);
    }

    /** @test */
    public function non_privileged_user_cannot_access_studi_lanjut_management()
    {
        $user = User::factory()->create();
        $user->assignRole('pegawai');

        $this->actingAs($user)
            ->get(route('kepegawaian.studi-lanjut.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('kepegawaian.studi-lanjut.create'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_view_studi_lanjut_monitoring_index_with_statistics()
    {
        $manager = $this->createManagerUser();
        $pegawai1 = $this->createPegawai('Dosen Satu', '198001012005011001');
        $pegawai2 = $this->createPegawai('Dosen Dua', '198202022006022002');

        StudiLanjut::create([
            'pegawai_id' => $pegawai1->id,
            'progres' => StudiLanjut::PROGRES_ONGOING,
            'jenis_pembiayaan' => StudiLanjut::JENIS_PEMBIAYAAN_BEASISWA,
            'nama_beasiswa' => 'LPDP',
            'jenis_tugas' => StudiLanjut::JENIS_TUGAS_MENINGGALKAN,
            'bidang_ilmu' => StudiLanjut::BIDANG_ILMU_STEM,
            'jenjang' => 'S3',
            'program_studi' => 'Doctor of Computer Science',
            'nama_institusi' => 'University of Melbourne',
            'negara' => 'Australia',
        ]);

        StudiLanjut::create([
            'pegawai_id' => $pegawai2->id,
            'progres' => StudiLanjut::PROGRES_DEFER,
            'jenis_pembiayaan' => StudiLanjut::JENIS_PEMBIAYAAN_MANDIRI,
            'jenis_tugas' => StudiLanjut::JENIS_TUGAS_MENJALANKAN,
            'bidang_ilmu' => StudiLanjut::BIDANG_ILMU_EKONOMI,
            'jenjang' => 'S2',
            'program_studi' => 'Magister Manajemen',
            'nama_institusi' => 'Universitas Indonesia',
            'negara' => 'Indonesia',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('kepegawaian.studi-lanjut.index'));

        $response->assertOk()
            ->assertSee('Monitoring Studi Lanjut Pegawai')
            ->assertSee('Dosen Satu')
            ->assertSee('Doctor of Computer Science')
            ->assertSee('University of Melbourne')
            ->assertSee('STEM')
            ->assertSee('LPDP')
            ->assertSee('Dosen Dua')
            ->assertSee('Magister Manajemen')
            ->assertSee('EKONOMI');
    }

    /** @test */
    public function manager_can_create_studi_lanjut_record_with_document_upload()
    {
        $manager = $this->createManagerUser();
        $pegawai = $this->createPegawai('Prof. Ahmad Subarjo', '197505052000031001');

        $file = UploadedFile::fake()->create('sk_tugas_belajar.pdf', 500, 'application/pdf');

        $payload = [
            'pegawai_id' => $pegawai->id,
            'progres' => 'ongoing',
            'jenis_pembiayaan' => 'beasiswa',
            'nama_beasiswa' => 'Beasiswa Pendidikan Indonesia (BPI)',
            'jenis_tugas' => 'Meninggalkan Tugas',
            'bidang_ilmu' => 'STEM',
            'jenjang' => 'S3',
            'program_studi' => 'Teknik Elektro dan Informatika',
            'nama_institusi' => 'Institut Teknologi Bandung',
            'negara' => 'Indonesia',
            'tanggal_mulai' => '2026-09-01',
            'target_selesai' => '2029-08-31',
            'nomor_sk' => 'SK-TUBEL/001/IX/2026',
            'tanggal_sk' => '2026-08-15',
            'dokumen_sk' => $file,
            'keterangan' => 'Riset tentang Artificial Intelligence dan Renewable Energy.',
        ];

        $response = $this->actingAs($manager)
            ->post(route('kepegawaian.studi-lanjut.store'), $payload);

        $studiLanjut = StudiLanjut::first();

        $this->assertNotNull($studiLanjut);
        $this->assertSame($pegawai->id, $studiLanjut->pegawai_id);
        $this->assertSame('ongoing', $studiLanjut->progres);
        $this->assertSame('beasiswa', $studiLanjut->jenis_pembiayaan);
        $this->assertSame('Beasiswa Pendidikan Indonesia (BPI)', $studiLanjut->nama_beasiswa);
        $this->assertSame('Meninggalkan Tugas', $studiLanjut->jenis_tugas);
        $this->assertSame('STEM', $studiLanjut->bidang_ilmu);
        $this->assertSame('Teknik Elektro dan Informatika', $studiLanjut->program_studi);
        $this->assertSame('Institut Teknologi Bandung', $studiLanjut->nama_institusi);
        $this->assertSame('SK-TUBEL/001/IX/2026', $studiLanjut->nomor_sk);
        $this->assertNotNull($studiLanjut->dokumen_sk);
        $this->assertFileExists(public_path($studiLanjut->dokumen_sk));
        $this->assertSame($manager->id, $studiLanjut->created_by);

        $response->assertRedirect(route('kepegawaian.studi-lanjut.show', $studiLanjut));

        // Clean up uploaded file
        if ($studiLanjut->dokumen_sk && File::exists(public_path($studiLanjut->dokumen_sk))) {
            File::delete(public_path($studiLanjut->dokumen_sk));
        }
    }

    /** @test */
    public function validation_fails_if_required_or_invalid_fields_are_provided()
    {
        $manager = $this->createManagerUser();

        $response = $this->actingAs($manager)
            ->post(route('kepegawaian.studi-lanjut.store'), [
                'pegawai_id' => 99999, // non-existent
                'progres' => 'invalid_progres',
                'jenis_pembiayaan' => 'invalid_biaya',
                'jenis_tugas' => 'invalid_tugas',
                'bidang_ilmu' => 'INVALID_BIDANG',
                'program_studi' => '',
                'nama_institusi' => '',
            ]);

        $response->assertSessionHasErrors([
            'pegawai_id',
            'progres',
            'jenis_pembiayaan',
            'jenis_tugas',
            'bidang_ilmu',
            'program_studi',
            'nama_institusi',
        ]);
    }

    /** @test */
    public function manager_can_update_studi_lanjut_record_and_change_progress()
    {
        $manager = $this->createManagerUser();
        $pegawai = $this->createPegawai('Dr. Ratna Kartika', '198304042008122001');

        $studiLanjut = StudiLanjut::create([
            'pegawai_id' => $pegawai->id,
            'progres' => 'ongoing',
            'jenis_pembiayaan' => 'beasiswa',
            'nama_beasiswa' => 'BU',
            'jenis_tugas' => 'Meninggalkan Tugas',
            'bidang_ilmu' => 'HUMANIORA',
            'jenjang' => 'S3',
            'program_studi' => 'Linguistik Terapan',
            'nama_institusi' => 'Universitas Gadjah Mada',
            'negara' => 'Indonesia',
            'tanggal_mulai' => '2023-01-01',
            'target_selesai' => '2026-08-31',
        ]);

        $updatePayload = [
            'pegawai_id' => $pegawai->id,
            'progres' => 'selesai',
            'jenis_pembiayaan' => 'beasiswa',
            'nama_beasiswa' => 'BU Kemendikbudristek',
            'jenis_tugas' => 'Meninggalkan Tugas',
            'bidang_ilmu' => 'HUMANIORA',
            'jenjang' => 'S3',
            'program_studi' => 'Linguistik Terapan',
            'nama_institusi' => 'Universitas Gadjah Mada',
            'negara' => 'Indonesia',
            'tanggal_mulai' => '2023-01-01',
            'target_selesai' => '2026-08-31',
            'tanggal_selesai' => '2026-08-20',
            'nomor_sk' => 'SK-LULUS-UGM/2026/08',
            'keterangan' => 'Telah lulus yudisium dengan predikat Cum Laude.',
        ];

        $response = $this->actingAs($manager)
            ->put(route('kepegawaian.studi-lanjut.update', $studiLanjut), $updatePayload);

        $studiLanjut->refresh();

        $this->assertSame('selesai', $studiLanjut->progres);
        $this->assertSame('BU Kemendikbudristek', $studiLanjut->nama_beasiswa);
        $this->assertSame('2026-08-20', $studiLanjut->tanggal_selesai->format('Y-m-d'));
        $this->assertSame('SK-LULUS-UGM/2026/08', $studiLanjut->nomor_sk);
        $this->assertSame('Telah lulus yudisium dengan predikat Cum Laude.', $studiLanjut->keterangan);

        $response->assertRedirect(route('kepegawaian.studi-lanjut.show', $studiLanjut));
    }

    /** @test */
    public function manager_can_filter_studi_lanjut_by_progres_pembiayaan_tugas_and_bidang_ilmu()
    {
        $manager = $this->createManagerUser();
        $pegawaiA = $this->createPegawai('Pegawai STEM', '111111');
        $pegawaiB = $this->createPegawai('Pegawai Sosial', '222222');

        StudiLanjut::create([
            'pegawai_id' => $pegawaiA->id,
            'progres' => 'ongoing',
            'jenis_pembiayaan' => 'beasiswa',
            'jenis_tugas' => 'Meninggalkan Tugas',
            'bidang_ilmu' => 'STEM',
            'program_studi' => 'Teknik Kimia',
            'nama_institusi' => 'ITS',
        ]);

        StudiLanjut::create([
            'pegawai_id' => $pegawaiB->id,
            'progres' => 'defer',
            'jenis_pembiayaan' => 'mandiri',
            'jenis_tugas' => 'Menjalankan Tugas',
            'bidang_ilmu' => 'SOSIAL',
            'program_studi' => 'Ilmu Komunikasi',
            'nama_institusi' => 'UNPAD',
        ]);

        // Filter by STEM
        $this->actingAs($manager)
            ->get(route('kepegawaian.studi-lanjut.index', ['bidang_ilmu' => 'STEM']))
            ->assertOk()
            ->assertSee('Pegawai STEM')
            ->assertDontSee('Pegawai Sosial');

        // Filter by SOSIAL
        $this->actingAs($manager)
            ->get(route('kepegawaian.studi-lanjut.index', ['bidang_ilmu' => 'SOSIAL']))
            ->assertOk()
            ->assertSee('Pegawai Sosial')
            ->assertDontSee('Pegawai STEM');

        // Filter by Mandiri
        $this->actingAs($manager)
            ->get(route('kepegawaian.studi-lanjut.index', ['jenis_pembiayaan' => 'mandiri']))
            ->assertOk()
            ->assertSee('Pegawai Sosial')
            ->assertDontSee('Pegawai STEM');

        // Filter by Defer
        $this->actingAs($manager)
            ->get(route('kepegawaian.studi-lanjut.index', ['progres' => 'defer']))
            ->assertOk()
            ->assertSee('Pegawai Sosial')
            ->assertDontSee('Pegawai STEM');
    }

    /** @test */
    public function manager_can_view_detail_show_page()
    {
        $manager = $this->createManagerUser();
        $pegawai = $this->createPegawai('Dr. Ir. Hendra Gunawan', '197707072002121002');

        $studiLanjut = StudiLanjut::create([
            'pegawai_id' => $pegawai->id,
            'progres' => 'ongoing',
            'jenis_pembiayaan' => 'beasiswa',
            'nama_beasiswa' => 'Fulbright Scholarship',
            'jenis_tugas' => 'Meninggalkan Tugas',
            'bidang_ilmu' => 'STEM',
            'jenjang' => 'Postdoctoral',
            'program_studi' => 'Advanced Robotics Research',
            'nama_institusi' => 'MIT',
            'negara' => 'United States',
            'nomor_sk' => 'SK-POSTDOC/MIT/2026',
            'keterangan' => 'Penelitian kolaboratif robotika dan automasi.',
        ]);

        $this->actingAs($manager)
            ->get(route('kepegawaian.studi-lanjut.show', $studiLanjut))
            ->assertOk()
            ->assertSee('Detail Monitoring Studi Lanjut')
            ->assertSee('Dr. Ir. Hendra Gunawan')
            ->assertSee('Advanced Robotics Research')
            ->assertSee('MIT')
            ->assertSee('United States')
            ->assertSee('Fulbright Scholarship')
            ->assertSee('SK-POSTDOC/MIT/2026');
    }

    /** @test */
    public function manager_can_delete_studi_lanjut_record()
    {
        $manager = $this->createManagerUser();
        $pegawai = $this->createPegawai('Pegawai Dihapus', '19999999');

        $studiLanjut = StudiLanjut::create([
            'pegawai_id' => $pegawai->id,
            'progres' => 'ongoing',
            'jenis_pembiayaan' => 'mandiri',
            'jenis_tugas' => 'Menjalankan Tugas',
            'bidang_ilmu' => 'KEAGAMAAN',
            'program_studi' => 'Studi Islam',
            'nama_institusi' => 'UIN',
        ]);

        $response = $this->actingAs($manager)
            ->delete(route('kepegawaian.studi-lanjut.destroy', $studiLanjut));

        $this->assertSoftDeleted('studi_lanjuts', ['id' => $studiLanjut->id]);
        $response->assertRedirect(route('kepegawaian.studi-lanjut.index'));
    }

    /** @test */
    public function manager_can_export_studi_lanjut_data_to_csv()
    {
        $manager = $this->createManagerUser();
        $pegawai = $this->createPegawai('Dosen Ekspor', '198909092015041001');

        StudiLanjut::create([
            'pegawai_id' => $pegawai->id,
            'progres' => 'ongoing',
            'jenis_pembiayaan' => 'beasiswa',
            'nama_beasiswa' => 'LPDP Target',
            'jenis_tugas' => 'Meninggalkan Tugas',
            'bidang_ilmu' => 'STEM',
            'jenjang' => 'S3',
            'program_studi' => 'Teknik Fisika',
            'nama_institusi' => 'Nanyang Technological University',
            'negara' => 'Singapore',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('kepegawaian.studi-lanjut.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('monitoring_pegawai_studi_lanjut_', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function search_pegawais_options_endpoint_returns_json_results()
    {
        $manager = $this->createManagerUser();
        $this->createPegawai('Zulham Effendi', '199101012019031005');

        $response = $this->actingAs($manager)
            ->getJson(route('kepegawaian.studi-lanjut.options.pegawais', ['q' => 'Zulham']));

        $response->assertOk()
            ->assertJsonStructure(['results', 'pagination'])
            ->assertJsonFragment(['nama' => 'Zulham Effendi', 'nip' => '199101012019031005']);
    }
}
