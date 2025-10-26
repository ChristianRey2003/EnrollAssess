<?php

namespace App\Events;

use App\Models\Interview;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InterviewCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $interview;

    /**
     * Create a new event instance.
     */
    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
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
            'id' => $this->interview->id,
            'applicant_name' => $this->interview->applicant->first_name . ' ' . $this->interview->applicant->last_name,
            'instructor_name' => $this->interview->instructor->name ?? 'Not assigned',
            'overall_score' => $this->interview->overall_score,
            'status' => $this->interview->status,
            'message' => 'Interview completed for ' . $this->interview->applicant->first_name . ' ' . $this->interview->applicant->last_name,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'interview.completed';
    }
}

