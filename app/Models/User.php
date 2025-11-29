<?php

namespace App\Models;

use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id'; // As per ERD
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password_hash',
        'password', // Laravel standard field
        'full_name',
        'role',
        'email',
        'profile_picture',
        'last_login',
        'force_password_change',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'password', // Laravel standard field
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password_hash' => 'hashed',
            'password' => 'hashed', // Laravel standard field
            'force_password_change' => 'boolean',
        ];
    }

    /**
     * Get the password attribute name for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash ?? $this->password;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->user_id;
    }

    /**
     * Get the name attribute for display.
     */
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Relationships
     */

    /**
     * Get all interviews conducted by this user (as interviewer).
     */
    public function interviews()
    {
        return $this->hasMany(Interview::class, 'interviewer_id', 'user_id');
    }

    /**
     * Get completed interviews for this user
     */
    public function completedInterviews()
    {
        return $this->hasMany(Interview::class, 'interviewer_id', 'user_id')->where('status', 'completed');
    }

    /**
     * Scope queries by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is department head
     */
    public function isDepartmentHead()
    {
        return $this->role === 'department-head';
    }

    /**
     * Check if user is administrator
     */
    public function isAdministrator()
    {
        return $this->role === 'administrator';
    }

    /**
     * Check if user is instructor
     */
    public function isInstructor()
    {
        return $this->role === 'instructor';
    }

    /**
     * Get the profile picture URL or return null
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return null;
    }

    /**
     * Get initials for avatar display
     */
    public function getInitialsAttribute()
    {
        $name = $this->full_name;
        $parts = explode(' ', $name);
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // Use custom admin password reset notification
        $this->notify(new AdminResetPasswordNotification($token));
    }

    /**
     * Get the delegations where this user is the delegatee
     */
    public function delegatedPermissions()
    {
        return $this->hasMany(RoleDelegation::class, 'delegatee_id', 'user_id');
    }

    /**
     * Check if user has a specific permission via role or delegation
     * Supports granular capabilities with dependency checking
     * Also handles backward compatibility with old capability names
     */
    public function hasPermission($permission)
    {
        // Admin and Dept Head have all permissions
        if (in_array($this->role, ['administrator', 'department-head'])) {
            return true;
        }

        // Map old capability names to new granular ones for backward compatibility
        $capabilityMapping = [
            'assign_applicants' => 'applicants.assign',
            'view_reports' => 'reports.view',
            'manage_questions' => 'questions.view',
            'manage_exam_settings' => 'questions.manage_exam_settings',
        ];

        // Reverse mapping: new granular -> old names (for checking delegations)
        $reverseMapping = [
            'applicants.assign' => 'assign_applicants',
            'applicants.view' => 'assign_applicants', // Old assign_applicants implied view
            'reports.view' => 'view_reports',
            'reports.generate' => 'view_reports', // Old view_reports implied generate
            'questions.view' => 'manage_questions',
            'questions.create' => 'manage_questions', // Old manage_questions implied all
            'questions.edit' => 'manage_questions',
            'questions.delete' => 'manage_questions',
            'questions.manage_exam_settings' => 'manage_exam_settings',
        ];

        // If checking old capability name, also check new equivalent
        // If checking new capability name, also check old equivalent
        $permissionsToCheck = [$permission];
        if (isset($capabilityMapping[$permission])) {
            $permissionsToCheck[] = $capabilityMapping[$permission];
        }
        if (isset($reverseMapping[$permission])) {
            $permissionsToCheck[] = $reverseMapping[$permission];
        }

        // Check for active delegation (exact match)
        foreach ($permissionsToCheck as $perm) {
            $delegation = $this->delegatedPermissions()
                        ->where('permission', $perm)
                        ->where('status', 'active')
                        ->where(function($q) {
                            $q->whereNull('starts_at')
                              ->orWhere('starts_at', '<=', now());
                        })
                        ->first();

            if ($delegation && !$delegation->isExpired()) {
                return true;
            }
        }

        // Check if permission has dependencies and user has parent permission
        // e.g., if checking questions.edit but user has questions.view, that's not enough
        // But if checking questions.view and user has questions.edit, that works
        $allDelegations = $this->delegatedPermissions()
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->get()
            ->filter(function($d) {
                return !$d->isExpired();
            })
            ->pluck('permission')
            ->toArray();

        // Check if user has a more powerful capability that includes this one
        // e.g., questions.edit includes questions.view
        foreach ($allDelegations as $delegatedPermission) {
            foreach ($permissionsToCheck as $perm) {
                if ($this->capabilityIncludes($delegatedPermission, $perm)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a capability includes another capability
     * e.g., questions.edit includes questions.view
     */
    protected function capabilityIncludes(string $hasCapability, string $neededCapability): bool
    {
        // Exact match
        if ($hasCapability === $neededCapability) {
            return true;
        }

        // Check if hasCapability is a parent of neededCapability
        // e.g., questions.edit includes questions.view
        $hasParts = explode('.', $hasCapability);
        $neededParts = explode('.', $neededCapability);

        // Must be same category (e.g., both "questions")
        if ($hasParts[0] !== $neededParts[0]) {
            return false;
        }

        // If has "manage" or "edit", it includes "view"
        if (in_array($hasParts[1] ?? '', ['edit', 'delete', 'manage_exam_settings']) && 
            ($neededParts[1] ?? '') === 'view') {
            return true;
        }

        // If has "create", it includes "view"
        if (($hasParts[1] ?? '') === 'create' && ($neededParts[1] ?? '') === 'view') {
            return true;
        }

        return false;
    }

    /**
     * Activate delegations on first login
     * This should be called when instructor logs in
     */
    public function activateDelegations()
    {
        // Get all active delegations that haven't been activated yet
        $delegations = $this->delegatedPermissions()
            ->where('status', 'active')
            ->whereNull('activated_at')
            ->where(function($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->with('delegator')
            ->get();

        foreach ($delegations as $delegation) {
            $delegation->update(['activated_at' => now()]);
            
            // Notify the delegator (admin) that the instructor has logged in
            if ($delegation->delegator) {
                $delegation->delegator->notify(
                    new \App\Notifications\DelegationActivatedNotification($this, $delegation)
                );
            }
        }
    }

    /**
     * Check if current user can manage a target user
     * 
     * @param User $targetUser The user being managed
     * @return bool
     */
    public function canManageUser(User $targetUser)
    {
        // Superadmin can manage anyone
        if ($this->isAdministrator()) {
            return true;
        }

        // Department-head can only manage instructors
        if ($this->isDepartmentHead()) {
            return $targetUser->isInstructor();
        }

        // Instructors cannot manage other users
        return false;
    }

    /**
     * Check if current user can change a target user's role
     * 
     * @param User|null $targetUser The user whose role is being changed (null for new users)
     * @param string $newRole The new role being assigned
     * @return bool
     */
    public function canChangeRole(?User $targetUser, string $newRole)
    {
        // Superadmin can change any role
        if ($this->isAdministrator()) {
            return true;
        }

        // Department-head can only create/edit instructors (cannot change roles)
        if ($this->isDepartmentHead()) {
            // Can only work with instructor role
            if ($newRole !== 'instructor') {
                return false;
            }
            
            // If editing existing user, can only edit if they're already an instructor
            if ($targetUser && !$targetUser->isInstructor()) {
                return false;
            }
            
            return true;
        }

        return false;
    }

    /**
     * Check if current user can delegate to a target user
     * 
     * @param User $targetUser The user to delegate to
     * @return bool
     */
    public function canDelegateTo(User $targetUser)
    {
        // Superadmin can delegate to both department-heads and instructors
        if ($this->isAdministrator()) {
            return in_array($targetUser->role, ['department-head', 'instructor']);
        }

        // Department-head can only delegate to instructors
        if ($this->isDepartmentHead()) {
            return $targetUser->isInstructor();
        }

        return false;
    }
}