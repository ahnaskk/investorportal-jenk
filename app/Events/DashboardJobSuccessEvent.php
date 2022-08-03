<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardJobSuccessEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $userId;
    public $host;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $userId, $host = '')
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->host = $host;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            $this->host.'-dashboard-job-success-'.$this->userId,
        ];

        return new PrivateChannel('dashboard-job-success-'.$this->userId);
    }

    /*public function broadcastAs()
    {
        return 'success';
    }*/
}
