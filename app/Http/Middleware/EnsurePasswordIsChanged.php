<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('password.force.edit', 'password.force.update', 'logout')) {
            return $next($request);
        }

        return redirect()->route('password.force.edit')
            ->with('status', 'Silakan ubah password Anda terlebih dahulu sebelum melanjutkan.');
    }
}

