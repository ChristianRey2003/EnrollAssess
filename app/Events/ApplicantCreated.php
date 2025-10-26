<?php

namespace App\Events;

use App\Models\Applicant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicantCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $applicant;

    /**
     * Create a new event instance.
     */
    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('dashboard');
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->applicant->id,
            'name' => $this->applicant->first_name . ' ' . $this->applicant->last_name,
            'email' => $this->applicant->email,
            'status' => $this->applicant->status,
            'created_at' => $this->applicant->created_at->format('M d, Y g:i A'),
            'message' => 'New applicant registered: ' . $this->applicant->first_name . ' ' . $this->applicant->last_name,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'applicant.created';
    }
}

