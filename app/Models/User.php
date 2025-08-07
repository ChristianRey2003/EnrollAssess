<?php

namespace App\Models;

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
        'full_name',
        'role',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
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
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Get the password attribute name for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
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
     * Get the interviews conducted by this user (as interviewer).
     */
    public function conductedInterviews()
    {
        return $this->hasMany(Interview::class, 'interviewer_id', 'user_id');
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
}