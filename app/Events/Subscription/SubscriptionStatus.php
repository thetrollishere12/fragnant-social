<?php

namespace App\Events\Subscription;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionStatus implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $status;
    public string $title;
    public string $message;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $userId,
        string $status,
        string $title = "Surpassed Subscription Limit!",
        string $message = null // Set as null to handle runtime initialization
    ) {
        $this->userId = $userId;
        $this->status = $status;
        $this->title = $title;

        // Handle dynamic URL construction
        $this->message = $message ?? 'Woops, you surpassed your limit. Please <a href="' . url('subscription-pricing') . '" style="color: #007bff; text-decoration: underline;">upgrade your subscription</a> to continue.';
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }

    public function broadcastWith(): array
    {
        return [
            'status' => $this->status,
            'title' => $this->title,
            'message' => $this->message,
        ];
    }

    public function broadcastAs(): string
    {
        return 'subscription-status';
    }
}
