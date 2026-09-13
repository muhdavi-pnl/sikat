<?php

namespace App\Listeners\Auth;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function __construct(protected AuditService $auditService)
    {
    }

    public function handle(Logout $event): void
    {
        /** @var User|null $user */
        $user = $event->user instanceof User ? $event->user : null;
        $userName = $user ? $user->name : 'User';

        $this->auditService->logAuth(
            eventType: 'auth.logout',
            action: "Pengguna {$userName} telah logout dari sistem",
            user: $user,
            metadata: [
                'guard' => $event->guard,
            ],
            status: 'success'
        );
    }
}
