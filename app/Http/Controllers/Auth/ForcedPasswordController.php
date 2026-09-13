<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcedPasswordController extends Controller
{
    /**
     * Show forced password change form.
     */
    public function edit(Request $request)
    {
        return view('auth.force-change-password');
    }

    /**
     * Update password for user forced to change credentials.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        app(\App\Services\AuditService::class)->logAuth(
            eventType: 'auth.password_changed',
            action: "Pengguna {$user->name} berhasil mengubah password awal (force change)",
            user: $user,
            status: 'success'
        );

        return redirect()->route('dashboard')->with('status', 'Password berhasil diperbarui.');
    }
}

