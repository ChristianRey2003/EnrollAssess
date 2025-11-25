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
        'expires_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
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
}
