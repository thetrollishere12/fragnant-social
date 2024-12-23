<?php

namespace App\Events\Publish;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublishProcessing implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $stage;
    public $message;
    public $progress; // Percentage completion

    /**
     * Create a new event instance.
     *
     * @param int $userId
     * @param string $stage
     * @param string $message
     * @param int $progress
     */
    public function __construct($userId, $stage, $message, $progress)
    {
        $this->userId = $userId;
        $this->stage = $stage;
        $this->message = $message;
        $this->progress = $progress;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }

    /**
     * Data sent with the event.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'stage' => $this->stage,
            'message' => $this->message,
            'progress' => $this->progress,
        ];
    }

    /**
     * Broadcast event name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'publish.processing';
    }
}