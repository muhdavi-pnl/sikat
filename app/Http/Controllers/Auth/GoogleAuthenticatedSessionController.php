<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthenticatedSessionController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return $this->googleDriver()->redirect();
    }

    public function callback(): RedirectResponse
    {
        $auditService = app(\App\Services\AuditService::class);

        try {
            $googleUser = $this->googleDriver()->user();
        } catch (Throwable $exception) {
            report($exception);

            $auditService->logAuth(
                eventType: 'auth.login_failed',
                action: 'Login dengan Google gagal: ' . $exception->getMessage(),
                metadata: [
                    'provider' => 'google',
                    'error' => $exception->getMessage(),
                ],
                status: 'failed'
            );

            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Login dengan Google gagal. Silakan coba lagi.',
                ]);
        }

        $email = trim((string) $googleUser->getEmail());
        $googleId = trim((string) $googleUser->getId());
        $isVerified = $this->hasVerifiedGoogleEmail($googleUser);

        if ($email === '' || $googleId === '' || ! $isVerified) {
            $auditService->logAuth(
                eventType: 'auth.login_failed',
                action: "Login dengan Google gagal: Email ({$email}) tidak valid atau belum terverifikasi di Google",
                metadata: [
                    'provider' => 'google',
                    'email' => $email,
                    'google_id' => $googleId,
                    'is_verified' => $isVerified,
                ],
                status: 'failed'
            );

            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Akun Google harus memiliki email yang terverifikasi.',
                ]);
        }

        $user = User::query()->where('google_id', $googleId)->first();
        $isNewUser = false;

        if (! $user) {
            $user = User::query()->where('email', $email)->first();
        }

        if ($user) {
            $user->forceFill([
                'google_id' => $googleId,
                'email_verified_at' => $user->email_verified_at ?: now(),
                'name' => $user->name ?: $this->resolveDisplayName($googleUser, $email),
            ])->save();
        } else {
            $user = User::create([
                'name' => $this->resolveDisplayName($googleUser, $email),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'must_change_password' => false,
            ]);

            $user->forceFill([
                'google_id' => $googleId,
                'email_verified_at' => now(),
            ])->save();

            $isNewUser = true;
        }

        if ($isNewUser) {
            event(new Registered($user));
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    protected function hasVerifiedGoogleEmail($googleUser): bool
    {
        $raw = (array) data_get($googleUser, 'user', []);

        return (bool) ($raw['email_verified'] ?? $raw['verified_email'] ?? false);
    }

    protected function resolveDisplayName($googleUser, string $email): string
    {
        $name = trim((string) $googleUser->getName());

        if ($name !== '') {
            return Str::limit($name, 150, '');
        }

        $emailName = Str::before($email, '@');

        return Str::limit($emailName !== '' ? $emailName : 'Google User', 150, '');
    }

    protected function googleDriver()
    {
        return Socialite::driver('google')->redirectUrl($this->googleRedirectUrl());
    }

    protected function googleRedirectUrl(): string
    {
        $configuredRedirect = trim((string) config('services.google.redirect'));

        return $configuredRedirect !== ''
            ? $configuredRedirect
            : route('auth.google.callback');
    }
}
