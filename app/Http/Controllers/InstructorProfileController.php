<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InstructorProfileController extends Controller
{
    /**
     * Display the instructor profile page.
     */
    public function edit(): View
    {
        $user = Auth::user();
        return view('instructor.profile.edit', compact('user'));
    }

    /**
     * Update the instructor profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->user_id . ',user_id',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Update basic information
        $user->full_name = $request->full_name;
        $user->username = $request->username;
        $user->email = $request->email;

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $path;
        }

        // Update password if provided
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password_hash)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            $user->password_hash = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('instructor.profile.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the profile picture.
     */
    public function deleteProfilePicture(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->profile_picture = null;
            $user->save();
        }

        return redirect()->route('instructor.profile.edit')->with('success', 'Profile picture deleted successfully!');
    }
}
