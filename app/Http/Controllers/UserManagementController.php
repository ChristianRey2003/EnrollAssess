<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status (active users have recent login activity)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('updated_at', '>=', now()->subDays(30));
            } else {
                $query->where('updated_at', '<', now()->subDays(30));
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'department_heads' => User::where('role', 'department-head')->count(),
            'administrators' => User::where('role', 'administrator')->count(),
            'instructors' => User::where('role', 'instructor')->count(),
            'recent_logins' => User::where('updated_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|in:department-head,administrator,instructor',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'role' => $request->role,
                'password_hash' => Hash::make($request->password),
            ]);

            return redirect()->route('admin.users.index')
                           ->with('success', 'User account created successfully for ' . $user->full_name . '!');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to create user account. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // Get user activity stats
        $userStats = [
            'created_date' => $user->created_at,
            'last_login' => $user->updated_at,
            'days_since_login' => $user->updated_at->diffInDays(now()),
        ];

        // Get user's related data based on role
        $relatedData = [];
        
        if ($user->role === 'instructor') {
            $relatedData['assigned_interviews'] = \App\Models\Interview::where('interviewer_id', $user->user_id)->count();
            $relatedData['completed_interviews'] = \App\Models\Interview::where('interviewer_id', $user->user_id)
                                                                       ->whereNotNull('interview_date')->count();
        }

        return view('admin.users.show', compact('user', 'userStats', 'relatedData'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent editing of your own account through this interface
        if ($user->user_id === Auth::id()) {
            return redirect()->route('admin.users.show', $id)
                           ->with('warning', 'Use the profile section to edit your own account.');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent editing of your own account
        if ($user->user_id === Auth::id()) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'You cannot edit your own account through user management.');
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->user_id . ',user_id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'role' => 'required|in:department-head,administrator,instructor',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $updateData = [
                'username' => $request->username,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'role' => $request->role,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password_hash'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('admin.users.show', $user->user_id)
                           ->with('success', 'User account updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to update user account. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deletion of your own account
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ]);
            }

            // Check if user has related data that would be affected
            if ($user->role === 'instructor') {
                $hasInterviews = \App\Models\Interview::where('interviewer_id', $user->user_id)->exists();
                if ($hasInterviews) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete instructor with existing interview assignments. Please reassign interviews first.'
                    ]);
                }
            }

            $userName = $user->full_name;
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => "User account for {$userName} has been deleted successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user account. Please try again.'
            ]);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent resetting your own password through this interface
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Use the profile section to change your own password.'
                ]);
            }

            // Generate a temporary password
            $tempPassword = 'temp' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            $user->update([
                'password_hash' => Hash::make($tempPassword)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Password reset for {$user->full_name}. Temporary password: {$tempPassword}",
                'temp_password' => $tempPassword
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password. Please try again.'
            ]);
        }
    }

    /**
     * Toggle user account status (for future implementation)
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            // This is a placeholder for account status functionality
            // You could add an 'is_active' field to users table in the future
            
            return response()->json([
                'success' => true,
                'message' => 'Account status feature will be implemented in future version.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account status.'
            ]);
        }
    }

    /**
     * Export users list as CSV
     */
    public function export(Request $request)
    {
        $query = User::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $csv = "Username,Full Name,Email,Role,Created Date,Last Login\n";

        foreach ($users as $user) {
            $csv .= sprintf('"%s","%s","%s","%s","%s","%s"' . "\n",
                $user->username,
                $user->full_name,
                $user->email,
                ucfirst(str_replace('-', ' ', $user->role)),
                $user->created_at->format('Y-m-d H:i:s'),
                $user->updated_at->format('Y-m-d H:i:s')
            );
        }

        $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}