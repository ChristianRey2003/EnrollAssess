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
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|in:department-head,instructor',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

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
            'role' => 'required|in:department-head,instructor',
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
            'permission' => 'required|string',
            'duration' => 'required|integer|min:1', // Duration in hours
        ]);

        try {
            $delegatee = User::findOrFail($id);
            
            // Only allow delegation to instructors
            if ($delegatee->role !== 'instructor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Permissions can only be delegated to instructors.'
                ]);
            }

            // Create delegation
            \App\Models\RoleDelegation::create([
                'delegator_id' => Auth::id(),
                'delegatee_id' => $delegatee->user_id,
                'permission' => $request->permission,
                'starts_at' => now(),
                'expires_at' => now()->addHours((int)$request->duration),
                'status' => 'active'
            ]);

            return redirect()->back()->with('success', "Successfully delegated '{$request->permission}' to {$delegatee->full_name} for {$request->duration} hours.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delegate permission: ' . $e->getMessage());
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
            $user = User::findOrFail($id);

            // Only allow sending credentials to instructors
            if ($user->role !== 'instructor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Credentials can only be sent to instructors.'
                ], 400);
            }

            // Check if user has email
            if (empty($user->email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have an email address configured.'
                ], 400);
            }

            // Generate a temporary password
            $tempPassword = 'Temp' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . '!';

            // Update user password and set force password change flag
            $user->update([
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
            Mail::to($user->email)->send(new InstructorCredentialsMail($user, $tempPassword));

            return response()->json([
                'success' => true,
                'message' => "Credentials email sent successfully to {$user->email}. The user will be required to change their password on first login."
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