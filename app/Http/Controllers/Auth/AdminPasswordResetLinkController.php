<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AdminPasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.admin-forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check if user exists and has admin role
        $user = User::where('email', $request->email)->first();
        
        if ($user && !in_array($user->role, ['department-head', 'instructor'])) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This email is not associated with an admin account.']);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', 'We have emailed your password reset link!');
        }

        // Handle throttle case - Laravel returns 'passwords.throttled' status
        // Check if the status message contains throttle-related text
        $statusMessage = __($status);
        if (stripos($statusMessage, 'throttle') !== false || 
            stripos($statusMessage, 'wait') !== false || 
            stripos($statusMessage, 'retry') !== false ||
            $status === 'passwords.throttled') {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Please wait before retrying. You can request a password reset once per minute.']);
        }

        // Handle other errors
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => $statusMessage]);
    }
}

