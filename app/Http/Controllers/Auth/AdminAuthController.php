<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminAuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        // Redirect if already logged in
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    /**
     * Handle admin login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username
        $user = User::where('username', $request->username)->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            // Check if user has valid role
            if (in_array($user->role, ['department-head', 'instructor'])) {
                // Update last login timestamp
                $user->update(['last_login' => now()]);
                
                // Login with remember me functionality
                Auth::login($user, $request->filled('remember'));
                
                $request->session()->regenerate();
                
                // Check if user must change password
                if ($user->force_password_change) {
                    return redirect()->route('admin.password.force-change');
                }
                
                // Role-based redirect
                $redirectRoute = match($user->role) {
                    'department-head' => 'admin.dashboard',
                    'instructor' => 'instructor.dashboard',
                    default => 'admin.dashboard'
                };
                
                return redirect()->intended(route($redirectRoute));
            } else {
                return back()->withErrors([
                    'username' => 'Access denied. Admin privileges required.',
                ]);
            }
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show the applicant login form.
     */
    public function showApplicantLogin()
    {
        return view('auth.applicant-login');
    }

    /**
     * Handle applicant access code verification.
     */
    public function verifyAccessCode(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string',
        ]);

        // Normalize access code: ensure BSIT- prefix
        $rawCode = $request->access_code;
        $normalizedCode = preg_match('/^BSIT-/i', $rawCode) ? $rawCode : ('BSIT-' . $rawCode);

        // Check for special easter egg access codes
        $specialCodes = ['BSIT-404', 'BSIT-CREDITS'];
        if (in_array(strtoupper($normalizedCode), array_map('strtoupper', $specialCodes))) {
            return redirect()->route('credits');
        }

        $accessCode = \App\Models\AccessCode::where('code', $normalizedCode)
            ->with('applicant')
            ->first();

        if (!$accessCode) {
            return back()->withErrors([
                'access_code' => 'Invalid access code. Please check and try again.',
            ]);
        }

        if ($accessCode->is_used) {
            return back()->withErrors([
                'access_code' => 'This access code has already been used.',
            ]);
        }

        if ($accessCode->expires_at && $accessCode->expires_at->isPast()) {
            return back()->withErrors([
                'access_code' => 'This access code has expired.',
            ]);
        }

        // Store access code and applicant info in session
        $request->session()->put('access_code', $accessCode->code);
        $request->session()->put('applicant_id', $accessCode->applicant_id);

        // Redirect to privacy consent first
        return redirect()->route('privacy.consent');
    }
}