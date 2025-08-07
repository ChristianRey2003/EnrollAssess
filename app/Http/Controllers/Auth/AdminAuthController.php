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
            // Check if user has admin role
            if (in_array($user->role, ['department-head', 'administrator', 'instructor'])) {
                Auth::login($user);
                
                $request->session()->regenerate();
                
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, ' . $user->full_name . '!');
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

        $accessCode = \App\Models\AccessCode::where('code', $request->access_code)
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