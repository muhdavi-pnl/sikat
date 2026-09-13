<?php

namespace App\Listeners\Auth;

use App\Services\AuditService;
use Illuminate\Auth\Events\Lockout;

class LogLockout
{
    public function __construct(protected AuditService $auditService)
    {
    }

    public function handle(Lockout $event): void
    {
        $email = $event->request->input('email');

        $this->auditService->logSecurity(
            eventType: 'auth.lockout',
            action: "Akun/IP terkunci akibat terlalu banyak percobaan login yang gagal (Throttle Lockout) untuk: {$email}",
            metadata: [
                'attempted_email' => $email,
                'ip' => $event->request->ip(),
            ],
            status: 'warning'
        );
    }
}
