<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleDelegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegator_id',
        'delegatee_id',
        'permission',
        'starts_at',
        'activated_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function delegator()
    {
        return $this->belongsTo(User::class, 'delegator_id', 'user_id');
    }

    public function delegatee()
    {
        return $this->belongsTo(User::class, 'delegatee_id', 'user_id');
    }

    /**
     * Scope to check if a delegation is currently valid
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(function($q) {
                         $q->whereNull('starts_at')
                           ->orWhere('starts_at', '<=', now());
                     })
                     ->where(function($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    /**
     * Get the effective expiration time based on activation
     * If activated_at is set, expiration is based on activation time + duration
     * Otherwise, expiration is based on expires_at
     */
    public function getEffectiveExpiresAt()
    {
        if ($this->activated_at) {
            // Calculate duration from original delegation
            $duration = $this->starts_at && $this->expires_at 
                ? $this->starts_at->diffInHours($this->expires_at)
                : 0;
            
            // Return activation time + duration
            return $this->activated_at->copy()->addHours($duration);
        }
        
        // If not activated yet, return original expiration
        return $this->expires_at;
    }

    /**
     * Check if delegation is expired based on activation
     */
    public function isExpired()
    {
        if ($this->status !== 'active') {
            return true;
        }

        $effectiveExpiresAt = $this->getEffectiveExpiresAt();
        
        if (!$effectiveExpiresAt) {
            return false; // No expiration set
        }

        return $effectiveExpiresAt->isPast();
    }
}
