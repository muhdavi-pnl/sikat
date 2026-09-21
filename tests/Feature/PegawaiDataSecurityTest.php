<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Dokumen;
use App\Models\DokumenPegawai;
use App\Models\Pegawai;
use App\Models\User;
use App\Services\Security\PegawaiDataProtectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PegawaiDataSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'kepegawaian']);
        Role::create(['name' => 'pegawai']);
    }

    /** @test */
    public function pii_masking_service_correctly_masks_sensitive_fields()
    {
        $service = app(PegawaiDataProtectionService::class);

        // NIK masking (16 digits)
        $this->assertSame('1234********3456', $service->maskNik('1234567890123456'));

        // NPWP masking
        $this->assertSame('12.***.***.*-***.789', $service->maskNpwp('12.345.678.9-012.789'));

        // BPJS masking
        $this->assertSame('0001*****789', $service->maskBpjs('000123456789'));

        // Phone masking
        $this->assertSame('0812****7890', $service->maskPhone('081234567890'));

        // Email masking
        $this->assertSame('j*****e@domain.com', $service->maskEmail('johndoe@domain.com'));

        // Alamat masking
        $maskedAlamat = $service->maskAlamat('Jl. Merdeka No. 123 Kompleks Asri');
        $this->assertStringContainsString('Jl. Merdeka', $maskedAlamat);
        $this->assertStringContainsString('Terproteksi UU PDP', $maskedAlamat);
    }

    /** @test */
    public function pegawai_model_provides_masked_pii_accessors()
    {
        $pegawai = Pegawai::create([
            'nip' => '199001012020121001',
            'nik' => '1234567890123456',
            'nama' => 'Budi Santoso',
            'npwp' => '12.345.678.9-012.789',
            'bpjs' => '000123456789',
            'no_hp' => '081234567890',
            'email' => 'budi.santoso@example.com',
            'alamat' => 'Jl. Pendidikan No. 45 Banda Aceh',
        ]);

        $this->assertSame('1234********3456', $pegawai->masked_nik);
        $this->assertSame('12.***.***.*-***.789', $pegawai->masked_npwp);
        $this->assertSame('0001*****789', $pegawai->masked_bpjs);
        $this->assertSame('0812****7890', $pegawai->masked_no_hp);
        $this->assertStringContainsString('Terproteksi UU PDP', $pegawai->masked_alamat);
    }

    /** @test */
    public function non_privileged_user_cannot_download_other_pegawai_dokumen_arsip()
    {
        $ownerUser = User::factory()->create();
        $ownerUser->assignRole('pegawai');

        $ownerPegawai = Pegawai::create([
            'nip' => '199101012020121001',
            'nama' => 'Owner Pegawai',
            'user_id' => $ownerUser->id,
        ]);

        $otherUser = User::factory()->create();
        $otherUser->assignRole('pegawai');

        $cipherNip = Crypt::encryptString($ownerPegawai->nip);

        $response = $this->actingAs($otherUser)->get(route('arsip.download', [
            'file_name' => 'test_file.pdf',
            'pegawai_nip' => $cipherNip,
        ]));

        $response->assertForbidden();
    }

    /** @test */
    public function privileged_user_can_download_dokumen_arsip_with_audit_log()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $pegawai = Pegawai::create([
            'nip' => '199201012020121002',
            'nama' => 'Pegawai Target',
        ]);

        $dir = public_path('file/' . $pegawai->nip);
        File::ensureDirectoryExists($dir);
        $fileName = 'test_sk_cpns.pdf';
        file_put_contents($dir . '/' . $fileName, 'Dummy PDF content for security test');

        $cipherNip = Crypt::encryptString($pegawai->nip);

        $response = $this->actingAs($admin)->get(route('arsip.download', [
            'file_name' => $fileName,
            'pegawai_nip' => $cipherNip,
        ]));

        $response->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'security.document_download',
            'user_id' => $admin->id,
        ]);

        // Clean up
        if (File::exists($dir . '/' . $fileName)) {
            File::delete($dir . '/' . $fileName);
        }
    }

    /** @test */
    public function export_pegawai_generates_security_audit_log()
    {
        $manager = User::factory()->create();
        $manager->assignRole('kepegawaian');

        Pegawai::create([
            'nip' => '199301012020121003',
            'nama' => 'Pegawai Uji Ekspor',
        ]);

        $response = $this->actingAs($manager)->get(route('kepegawaian.pegawai.export'));
        $response->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'security.data_export',
            'user_id' => $manager->id,
        ]);
    }

    /** @test */
    public function print_pegawai_generates_security_audit_log_and_renders_security_headers()
    {
        $manager = User::factory()->create();
        $manager->assignRole('kepegawaian');

        Pegawai::create([
            'nip' => '199401012020121004',
            'nik' => '1234567890123456',
            'nama' => 'Pegawai Uji Cetak',
        ]);

        $response = $this->actingAs($manager)->get(route('kepegawaian.pegawai.print'));
        $response->assertOk();
        $response->assertSee('RAHASIA / CONFIDENTIAL');
        $response->assertSee('UU PDP');
        $response->assertSee('1234********3456');

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'security.data_print',
            'user_id' => $manager->id,
        ]);
    }

    /** @test */
    public function reveal_sensitive_data_endpoint_requires_authorization_and_logs_audit()
    {
        $manager = User::factory()->create();
        $manager->assignRole('kepegawaian');

        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('pegawai');

        $pegawai = Pegawai::create([
            'nip' => '199501012020121005',
            'nik' => '1122334455667788',
            'nama' => 'Pegawai Sensitif',
            'npwp' => '11.222.333.4-555.666',
        ]);

        // Unauthorized request
        $this->actingAs($unauthorizedUser)
            ->postJson(route('kepegawaian.pegawai.reveal-sensitive', $pegawai))
            ->assertForbidden();

        // Authorized request
        $response = $this->actingAs($manager)
            ->postJson(route('kepegawaian.pegawai.reveal-sensitive', $pegawai));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'nik' => '1122334455667788',
                'npwp' => '11.222.333.4-555.666',
            ],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'security.pii_access',
            'user_id' => $manager->id,
        ]);
    }
}
