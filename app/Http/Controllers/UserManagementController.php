<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use App\Mail\InstructorCredentialsMail;
use App\Services\MailConfigurationService;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * Mail Configuration Service
     *
     * @var MailConfigurationService
     */
    protected $mailConfigService;

    /**
     * Constructor
     */
    public function __construct(MailConfigurationService $mailConfigService)
    {
        $this->mailConfigService = $mailConfigService;
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('delegatedPermissions');

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
                $query->where('last_login', '>=', now()->subDays(30))
                      ->whereNotNull('last_login');
            } else {
                $query->where(function($q) {
                    $q->where('last_login', '<', now()->subDays(30))
                      ->orWhereNull('last_login');
                });
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'department_heads' => User::where('role', 'department-head')->count(),
            'administrators' => User::where('role', 'administrator')->count(),
            'instructors' => User::where('role', 'instructor')->count(),
            'recent_logins' => User::where('last_login', '>=', now()->subDays(7))
                                   ->whereNotNull('last_login')
                                   ->count(),
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
        $user = Auth::user();
        
        // Determine allowed roles based on current user's role
        $allowedRoles = ['instructor'];
        if ($user->isAdministrator()) {
            $allowedRoles = ['administrator', 'department-head', 'instructor'];
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Additional authorization check: department-head cannot create department-head or administrator
        if (!$user->isAdministrator() && in_array($request->role, ['department-head', 'administrator'])) {
            return redirect()->back()
                           ->with('error', 'You do not have permission to create users with this role.')
                           ->withInput();
        }

        try {
            $formatNamePart = function (?string $value) {
                if ($value === null) {
                    return null;
                }

                $trimmed = trim($value);
                if ($trimmed === '') {
                    return null;
                }

                return ucfirst(strtolower($trimmed));
            };

            $firstName = $formatNamePart($request->first_name);
            $middleName = $formatNamePart($request->middle_name);
            $lastName = $formatNamePart($request->last_name);

            // Combine first, middle, and last name into full_name
            $fullName = trim(
                collect([$firstName, $middleName, $lastName])
                    ->filter(fn ($part) => !empty($part))
                    ->implode(' ')
            );

            $user = User::create([
                'username' => $request->username,
                'full_name' => $fullName,
                'email' => $request->email,
                'role' => $request->role,
                'password_hash' => Hash::make($request->password),
            ]);

            ActivityLogger::log('create_user', "Created user account for {$user->full_name}", ['user_id' => $user->user_id, 'role' => $user->role]);

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
            'last_login' => $user->last_login,
            'days_since_login' => $user->last_login ? $user->last_login->diffInDays(now()) : null,
        ];

        // Get user's related data based on role
        $relatedData = [];
        
        if ($user->role === 'instructor') {
            $relatedData['assigned_interviews'] = \App\Models\Interview::where('interviewer_id', $user->user_id)->count();
            $relatedData['completed_interviews'] = \App\Models\Interview::where('interviewer_id', $user->user_id)
                                                                       ->whereNotNull('schedule_date')->count();
        }

        return view('admin.users.show', compact('user', 'userStats', 'relatedData'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $targetUser = User::findOrFail($id);
        $currentUser = Auth::user();
        
        // Prevent editing of your own account through this interface
        if ($targetUser->user_id === Auth::id()) {
            return redirect()->route('admin.users.show', $id)
                           ->with('warning', 'Use the profile section to edit your own account.');
        }

        // Check if current user can manage this user
        if (!$currentUser->canManageUser($targetUser)) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'You do not have permission to edit this user.');
        }

        return view('admin.users.edit', ['user' => $targetUser]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        $currentUser = Auth::user();

        // Prevent editing of your own account
        if ($targetUser->user_id === Auth::id()) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'You cannot edit your own account through user management.');
        }

        // Check if current user can manage this user
        if (!$currentUser->canManageUser($targetUser)) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'You do not have permission to manage this user.');
        }

        // Determine allowed roles based on current user's role
        $allowedRoles = ['instructor'];
        if ($currentUser->isAdministrator()) {
            $allowedRoles = ['administrator', 'department-head', 'instructor'];
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $targetUser->user_id . ',user_id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $targetUser->user_id . ',user_id',
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check if role change is allowed
        if (!$currentUser->canChangeRole($targetUser, $request->role)) {
            return redirect()->back()
                           ->with('error', 'You do not have permission to change this user\'s role.')
                           ->withInput();
        }

        // Additional check: prevent department-head from changing roles of department-head or administrator
        if ($currentUser->isDepartmentHead()) {
            if (in_array($targetUser->role, ['department-head', 'administrator'])) {
                return redirect()->back()
                               ->with('error', 'You do not have permission to edit users with this role.')
                               ->withInput();
            }
            // Department-head cannot change roles, only edit instructor details
            if ($request->role !== $targetUser->role) {
                return redirect()->back()
                               ->with('error', 'You do not have permission to change user roles.')
                               ->withInput();
            }
        }

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

            $oldRole = $targetUser->role;
            $targetUser->update($updateData);

            // Log role change if it occurred
            if ($oldRole !== $request->role) {
                ActivityLogger::log('change_role', "Changed role for {$targetUser->full_name} from {$oldRole} to {$request->role}", [
                    'user_id' => $targetUser->user_id,
                    'old_role' => $oldRole,
                    'new_role' => $request->role
                ]);
            }

            ActivityLogger::log('update_user', "Updated user account for {$targetUser->full_name}", ['user_id' => $targetUser->user_id, 'changes' => array_keys($updateData)]);

            return redirect()->route('admin.users.show', $targetUser->user_id)
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
    public function destroy(Request $request, $id)
    {
        try {
            $targetUser = User::findOrFail($id);
            $currentUser = Auth::user();

            // Prevent deletion of your own account
            if ($targetUser->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ]);
            }

            // Check if current user can manage this user
            if (!$currentUser->canManageUser($targetUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this user.'
                ]);
            }

            // Only administrator can delete department-head or administrator users
            if (in_array($targetUser->role, ['department-head', 'administrator']) && !$currentUser->isAdministrator()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete users with this role.'
                ]);
            }

            // Check if user has related data that would be affected
            if ($targetUser->role === 'instructor') {
                $hasInterviews = \App\Models\Interview::where('interviewer_id', $targetUser->user_id)->exists();
                if ($hasInterviews) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete instructor with existing interview assignments. Please reassign interviews first.'
                    ]);
                }
            }

            $userName = $targetUser->full_name;
            $userRole = $targetUser->role;
            $targetUser->delete();

            ActivityLogger::log('delete_user', "Deleted user account for {$userName} (Role: {$userRole})", ['user_id' => $id, 'role' => $userRole]);

            // Return JSON for API/AJAX calls, redirect with flash for normal form submits
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "User account for {$userName} has been deleted successfully."
                ]);
            }

            return redirect()->route('admin.users.index')
                             ->with('success', "User account for {$userName} has been deleted successfully.");

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
            $currentUser = Auth::user();

            // Only allow privileged users to reset passwords
            if (!$currentUser || !$currentUser->canManageUser($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to reset this user\'s password.'
                ], 403);
            }

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

            ActivityLogger::log('reset_password', "Reset password for {$user->full_name}", ['user_id' => $user->user_id]);

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
                $user->last_login ? $user->last_login->format('Y-m-d H:i:s') : 'Never'
            );
        }

        $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
    /**
     * Delegate a permission to a user
     */
    public function delegate(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'required|string',
            'duration' => 'required|integer|min:1', // Duration in hours
        ]);

        try {
            $delegatee = User::findOrFail($id);
            $currentUser = Auth::user();
            
            // Check if current user can delegate to this user
            if (!$currentUser->canDelegateTo($delegatee)) {
                $allowedRoles = $currentUser->isAdministrator() 
                    ? 'department-heads or instructors' 
                    : 'instructors';
                return redirect()->back()->with('error', "Permissions can only be delegated to {$allowedRoles}.");
            }

            $delegatedCount = 0;
            $delegatedNames = [];
            $permissionsToGrant = [];

            DB::transaction(function () use ($request, $delegatee, &$delegatedCount, &$delegatedNames, &$permissionsToGrant) {
                foreach ($request->permissions as $permission) {
                    // Add the permission itself
                    $permissionsToGrant[] = $permission;
                    
                    // Automatically add dependencies
                    $dependencies = \App\Constants\Capabilities::getDependencies($permission);
                    foreach ($dependencies as $dep) {
                        if (!in_array($dep, $permissionsToGrant)) {
                            $permissionsToGrant[] = $dep;
                        }
                    }
                }
                
                // Grant all permissions (including dependencies)
                foreach ($permissionsToGrant as $perm) {
                    // Check if already exists to avoid duplicates
                    $exists = \App\Models\RoleDelegation::where('delegatee_id', $delegatee->user_id)
                        ->where('permission', $perm)
                        ->where('status', 'active')
                        ->where(function($q) use ($request) {
                            $q->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now()->addHours((int)$request->duration));
                        })
                        ->exists();
                    
                    if (!$exists) {
                        \App\Models\RoleDelegation::create([
                            'delegator_id' => Auth::id(),
                            'delegatee_id' => $delegatee->user_id,
                            'permission' => $perm,
                            'starts_at' => now(),
                            'expires_at' => now()->addHours((int)$request->duration),
                            'status' => 'active'
                        ]);
                    }
                    
                    $delegatedCount++;
                    $delegatedNames[] = \App\Constants\Capabilities::getLabel($perm);
                }
            });

            $permissionList = implode(', ', $delegatedNames);
            
            ActivityLogger::log('delegate_permission', "Delegated permissions: {$permissionList} to {$delegatee->full_name}", [
                'delegatee_id' => $delegatee->user_id, 
                'permissions' => $request->permissions
            ]);

            return redirect()->back()->with('success', "Successfully delegated: {$permissionList} to {$delegatee->full_name} for {$request->duration} hours.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delegate permissions: ' . $e->getMessage());
        }
    }

    /**
     * Revoke a delegation
     */
    public function revokeDelegation($id)
    {
        try {
            // Find active delegations for this user
            $updated = \App\Models\RoleDelegation::where('delegatee_id', $id)
                ->where('status', 'active')
                ->update(['status' => 'revoked']);

            if ($updated > 0) {
                ActivityLogger::log('revoke_delegation', "Revoked delegations for user ID {$id}", ['delegatee_id' => $id]);
                return redirect()->back()->with('success', 'Successfully revoked all delegations for this user.');
            } else {
                 return redirect()->back()->with('warning', 'No active delegations found for this user.');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to revoke delegations.');
        }
    }

    /**
     * Send credentials email to instructor
     */
    public function sendCredentials($id)
    {
        try {
            $targetUser = User::findOrFail($id);
            $currentUser = Auth::user();

            // Check if current user can manage this user
            if (!$currentUser->canManageUser($targetUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to send credentials to this user.'
                ], 403);
            }

            // Only allow sending credentials to instructors (for now)
            // Administrator can send to anyone they can manage, but credentials email is instructor-specific
            if ($targetUser->role !== 'instructor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Credentials email can only be sent to instructors.'
                ], 400);
            }

            // Check if user has email
            if (empty($targetUser->email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have an email address configured.'
                ], 400);
            }

            // Generate a temporary password
            $tempPassword = 'Temp' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . '!';

            // Update user password and set force password change flag
            $targetUser->update([
                'password_hash' => Hash::make($tempPassword),
                'force_password_change' => true,
            ]);

            // Reload mail configuration to ensure we're using latest settings
            $this->mailConfigService->loadFromDatabase();

            // Verify mail configuration before sending
            $mailerType = Settings::getSetting('mail_mailer', 'resend');
            $fromAddress = Settings::getSetting('mail_from_address', '');
            
            if (empty($fromAddress)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email configuration error: Please set a "From Address" in email settings before sending credentials.'
                ], 400);
            }
            
            if ($mailerType === 'resend') {
                $resendApiKey = Settings::getSetting('resend_api_key', '');
                if (empty($resendApiKey)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email configuration error: Please set your Resend API key in email settings before sending credentials.'
                    ], 400);
                }
            }

            // Send email with credentials
            Mail::to($targetUser->email)->send(new InstructorCredentialsMail($targetUser, $tempPassword));

            ActivityLogger::log('send_credentials', "Sent credentials to {$targetUser->email}", ['user_id' => $targetUser->user_id]);

            return response()->json([
                'success' => true,
                'message' => "Credentials email sent successfully to {$targetUser->email}. The user will be required to change their password on first login."
            ]);

        } catch (\Illuminate\Mail\SendException $e) {
            Log::error('Failed to send credentials email (SendException): ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            
            // Check if this is a Resend API error
            $errorMessage = 'Failed to send credentials email. ';
            if (str_contains($e->getMessage(), 'Resend')) {
                $errorMessage .= 'Resend API error: ' . $e->getMessage();
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        } catch (\Exception $e) {
            Log::error('Failed to send credentials email: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send credentials email: ' . $e->getMessage()
            ], 500);
        }
    }
}