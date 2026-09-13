<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'kepegawaian']);
        Role::firstOrCreate(['name' => 'pegawai']);
    }

    public function test_successful_login_is_audited(): void
    {
        $user = User::factory()->create([
            'email' => 'audittest@example.com',
            'password' => Hash::make('Secret123!'),
        ]);
        $user->assignRole('pegawai');

        event(new Login('web', $user, false));

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.login',
            'user_id' => $user->id,
            'user_email' => 'audittest@example.com',
            'role' => 'pegawai',
            'status' => 'success',
        ]);
    }

    public function test_failed_login_is_audited(): void
    {
        $response = $this->withSession([
            'login_captcha' => [
                'question' => '2 + 3',
                'answer' => '5',
            ],
        ])->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
            'captcha' => '5',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.login_failed',
            'status' => 'failed',
        ]);
    }

    public function test_logout_is_audited(): void
    {
        $user = User::factory()->create([
            'email' => 'logoutuser@example.com',
        ]);
        $user->assignRole('pegawai');

        $this->actingAs($user);

        event(new Logout('web', $user));

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.logout',
            'user_id' => $user->id,
            'user_email' => 'logoutuser@example.com',
            'status' => 'success',
        ]);
    }

    public function test_user_registration_is_audited(): void
    {
        $user = User::factory()->create([
            'name' => 'John Newbie',
            'email' => 'newbie@example.com',
        ]);

        event(new Registered($user));

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.registered',
            'user_id' => $user->id,
            'user_email' => 'newbie@example.com',
            'status' => 'success',
        ]);
    }

    public function test_lockout_is_audited(): void
    {
        $request = Request::create('/login', 'POST', [
            'email' => 'locked@example.com',
        ]);

        event(new Lockout($request));

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.lockout',
            'status' => 'warning',
        ]);
    }

    public function test_model_creation_update_and_deletion_are_audited(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        // 1. Creation audit
        $unitKerja = UnitKerja::create([
            'unit_kerja' => 'Biro Sistem Informasi',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'model.created',
            'auditable_type' => UnitKerja::class,
            'auditable_id' => (string) $unitKerja->id,
            'user_id' => $admin->id,
        ]);

        // 2. Update audit with diff
        $unitKerja->update([
            'unit_kerja' => 'Pusat Data dan Informasi',
        ]);

        $latestLog = AuditLog::where('auditable_type', UnitKerja::class)
            ->where('event_type', 'model.updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($latestLog);
        $this->assertEquals('Biro Sistem Informasi', $latestLog->old_values['unit_kerja'] ?? null);
        $this->assertEquals('Pusat Data dan Informasi', $latestLog->new_values['unit_kerja'] ?? null);

        // 3. Deletion audit
        $unitKerja->delete();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'model.deleted',
            'auditable_type' => UnitKerja::class,
            'auditable_id' => (string) $unitKerja->id,
        ]);
    }

    public function test_sensitive_attributes_are_redacted_in_audit_logs(): void
    {
        $service = app(AuditService::class);
        $sanitized = $service->sanitizeData([
            'name' => 'Safe Name',
            'password' => 'supersecret',
            'nested' => [
                'token' => 'jwt.token.here',
                'description' => 'safe text',
            ],
        ]);

        $this->assertEquals('Safe Name', $sanitized['name']);
        $this->assertEquals('********', $sanitized['password']);
        $this->assertEquals('********', $sanitized['nested']['token']);
        $this->assertEquals('safe text', $sanitized['nested']['description']);
    }

    public function test_super_admin_and_kepegawaian_can_access_audit_log_dashboard(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->get(route('admin.audit-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('Log Audit');

        $kepegawaian = User::factory()->create();
        $kepegawaian->assignRole('kepegawaian');

        $response = $this->actingAs($kepegawaian)->get(route('admin.audit-logs.index'));
        $response->assertStatus(200);
    }

    public function test_regular_pegawai_cannot_access_audit_log_dashboard(): void
    {
        $pegawaiUser = User::factory()->create();
        $pegawaiUser->assignRole('pegawai');

        $response = $this->actingAs($pegawaiUser)->get(route('admin.audit-logs.index'));
        $response->assertStatus(403);
    }

    public function test_audit_log_detail_endpoint_returns_json(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $log = AuditLog::create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'user_email' => $admin->email,
            'role' => 'super-admin',
            'event_type' => 'auth.login',
            'action' => 'Pengguna login',
            'ip_address' => '127.0.0.1',
            'status' => 'success',
            'properties' => ['guard' => 'web'],
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.audit-logs.show', $log->id));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $log->id,
            'event_type' => 'auth.login',
            'user_name' => $admin->name,
            'ip_address' => '127.0.0.1',
            'status' => 'success',
        ]);
    }

    public function test_datatables_ajax_filtering_works(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        AuditLog::create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'user_email' => $admin->email,
            'role' => 'super-admin',
            'event_type' => 'auth.login',
            'action' => 'Admin logged in',
            'ip_address' => '192.168.1.100',
            'status' => 'success',
        ]);

        AuditLog::create([
            'user_name' => 'Unknown',
            'user_email' => 'hacker@attacker.com',
            'role' => 'guest',
            'event_type' => 'auth.login_failed',
            'action' => 'Failed login attempt',
            'ip_address' => '10.0.0.99',
            'status' => 'failed',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.audit-logs.index', [
                'status' => 'failed',
            ]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('hacker@attacker.com', $response->getContent());
        $this->assertStringNotContainsString('Admin logged in', $response->getContent());
    }

    public function test_audit_log_scopes_and_categories(): void
    {
        $log1 = AuditLog::create([
            'event_type' => 'auth.login',
            'action' => 'User logged in',
            'ip_address' => '127.0.0.1',
            'status' => 'success',
        ]);

        $log2 = AuditLog::create([
            'event_type' => 'model.updated',
            'action' => 'Pegawai data changed',
            'ip_address' => '127.0.0.1',
            'status' => 'success',
        ]);

        $this->assertEquals('Autentikasi', $log1->category);
        $this->assertEquals('Database', $log2->category);

        $authLogs = AuditLog::byCategory('auth')->get();
        $this->assertTrue($authLogs->contains('id', $log1->id));
        $this->assertFalse($authLogs->contains('id', $log2->id));

        $modelLogs = AuditLog::byCategory('model')->get();
        $this->assertTrue($modelLogs->contains('id', $log2->id));
        $this->assertFalse($modelLogs->contains('id', $log1->id));
    }
}
