<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\RoleDelegation;
use Illuminate\Notifications\Notification;

class DelegationActivatedNotification extends Notification
{
    protected $instructor;
    protected $delegation;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $instructor, RoleDelegation $delegation)
    {
        $this->instructor = $instructor;
        $this->delegation = $delegation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        $effectiveExpiresAt = $this->delegation->getEffectiveExpiresAt();
        $expiresAtText = $effectiveExpiresAt ? $effectiveExpiresAt->format('M d, Y g:i A') : 'No expiration';
        
        return [
            'type' => 'delegation_activated',
            'title' => 'Delegation Activated',
            'message' => $this->instructor->full_name . ' has logged in and activated the delegated permission: ' . ucwords(str_replace('_', ' ', $this->delegation->permission)),
            'instructor_id' => $this->instructor->user_id,
            'instructor_name' => $this->instructor->full_name,
            'delegation_id' => $this->delegation->id,
            'permission' => $this->delegation->permission,
            'expires_at' => $expiresAtText,
            'url' => route('admin.dashboard'),
            'icon' => 'user-check',
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast($notifiable)
    {
        return [
            'type' => 'delegation_activated',
            'title' => 'Delegation Activated',
            'message' => $this->instructor->full_name . ' has logged in and activated the delegated permission',
            'instructor_id' => $this->instructor->user_id,
            'instructor_name' => $this->instructor->full_name,
            'permission' => $this->delegation->permission,
            'url' => route('admin.dashboard'),
        ];
    }
}
