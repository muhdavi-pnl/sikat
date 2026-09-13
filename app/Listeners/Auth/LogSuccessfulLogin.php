<?php

namespace App\Listeners\Auth;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(protected AuditService $auditService)
    {
    }

    public function handle(Login $event): void
    {
        /** @var User|null $user */
        $user = $event->user instanceof User ? $event->user : null;
        $userName = $user ? $user->name : 'User';

        $this->auditService->logAuth(
            eventType: 'auth.login',
            action: "Pengguna {$userName} berhasil login ke sistem",
            user: $user,
            metadata: [
                'guard' => $event->guard,
                'remember' => $event->remember,
            ],
            status: 'success'
        );
    }
}
