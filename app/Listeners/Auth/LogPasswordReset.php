<?php

namespace App\Listeners\Auth;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\PasswordReset;

class LogPasswordReset
{
    public function __construct(protected AuditService $auditService)
    {
    }

    public function handle(PasswordReset $event): void
    {
        /** @var User|null $user */
        $user = $event->user instanceof User ? $event->user : null;
        $userName = $user ? $user->name : 'User';

        $this->auditService->logAuth(
            eventType: 'auth.password_reset',
            action: "Password untuk pengguna {$userName} berhasil direset",
            user: $user,
            metadata: [
                'user_id' => $user?->id,
                'email' => $user?->email,
            ],
            status: 'success'
        );
    }
}
