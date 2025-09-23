<?php

namespace App\Events;

use App\Models\CriticalAlert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CriticalAlertCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $alert;

    public function __construct(CriticalAlert $alert)
    {
        $this->alert = $alert;
    }

    public function broadcastOn()
    {
        return [
            new Channel('critical-alerts'),
            new PrivateChannel('user.' . $this->alert->assigned_to)
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->alert->id,
            'title' => $this->alert->title,
            'message' => $this->alert->message,
            'priority' => $this->alert->priority,
            'created_at' => $this->alert->created_at->toISOString()
        ];
    }
}