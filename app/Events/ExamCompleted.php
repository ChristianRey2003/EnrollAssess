<?php

namespace App\Events;

use App\Models\Applicant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $applicant;
    public $score;

    /**
     * Create a new event instance.
     */
    public function __construct(Applicant $applicant, float $score)
    {
        $this->applicant = $applicant;
        $this->score = $score;
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
            'score' => $this->score,
            'status' => $this->applicant->status,
            'completed_at' => now()->format('M d, Y g:i A'),
            'message' => $this->applicant->first_name . ' ' . $this->applicant->last_name . ' completed exam with ' . number_format($this->score, 1) . '%',
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'exam.completed';
    }
}

