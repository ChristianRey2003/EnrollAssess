<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForcePasswordChangeController extends Controller
{
    /**
     * Show the forced password change form.
     */
    public function show(): View
    {
        // Ensure user is authenticated and must change password
        if (!Auth::check() || !Auth::user()->force_password_change) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.force-password-change');
    }

    /**
     * Handle the forced password change.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Ensure user must change password
        if (!$user->force_password_change) {
            return redirect()->route('admin.dashboard');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Update password and clear force password change flag
        $user->update([
            'password_hash' => Hash::make($validated['password']),
            'force_password_change' => false,
        ]);

        // Determine redirect based on role
        $redirectRoute = match($user->role) {
            'department-head' => 'admin.dashboard',
            'instructor' => 'instructor.dashboard',
            default => 'admin.dashboard'
        };

        return redirect()->route($redirectRoute)
            ->with('success', 'Password changed successfully. You can now access all features.');
    }
}
