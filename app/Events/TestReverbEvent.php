<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestReverbEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
{
    $this->message = $message;
    \Log::info("Broadcasting TestReverbEvent with message: $message");
}


    public function broadcastOn()
    {
        return new Channel('test-reverb-channel');
    }

    public function broadcastAs()
    {
        return 'TestReverbEvent';
    }
}
