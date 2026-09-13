<?php

namespace App\Listeners\Auth;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Registered;

class LogUserRegistration
{
    public function __construct(protected AuditService $auditService)
    {
    }

    public function handle(Registered $event): void
    {
        /** @var User|null $user */
        $user = $event->user instanceof User ? $event->user : null;
        $userName = $user ? $user->name : 'User';
        $userEmail = $user ? $user->email : '';

        $this->auditService->logAuth(
            eventType: 'auth.registered',
            action: "Pendaftaran akun pengguna baru: {$userName} ({$userEmail})",
            user: $user,
            metadata: [
                'registered_user_id' => $user?->id,
                'email' => $userEmail,
                'google_id' => $user?->google_id,
            ],
            status: 'success'
        );
    }
}
