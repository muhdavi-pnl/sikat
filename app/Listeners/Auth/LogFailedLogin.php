<?php

namespace App\Listeners\Auth;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function __construct(protected AuditService $auditService)
    {
    }

    public function handle(Failed $event): void
    {
        /** @var User|null $user */
        $user = $event->user instanceof User ? $event->user : null;
        $attemptedEmail = $event->credentials['email'] ?? ($event->credentials['username'] ?? null);

        $this->auditService->logAuth(
            eventType: 'auth.login_failed',
            action: "Percobaan login gagal dengan identitas: {$attemptedEmail}",
            user: $user,
            metadata: [
                'guard' => $event->guard,
                'attempted_email' => $attemptedEmail,
                'has_account' => $user !== null,
            ],
            status: 'failed'
        );
    }
}
