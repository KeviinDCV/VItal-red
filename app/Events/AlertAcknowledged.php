<?php

namespace App\Events;

use App\Models\CriticalAlert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertAcknowledged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $alert;

    public function __construct(CriticalAlert $alert)
    {
        $this->alert = $alert;
    }

    public function broadcastOn()
    {
        return new Channel('critical-alerts');
    }

    public function broadcastWith()
    {
        return [
            'alert_id' => $this->alert->id,
            'status' => $this->alert->status,
            'acknowledged_by' => $this->alert->acknowledged_by,
            'acknowledged_at' => $this->alert->acknowledged_at?->toISOString()
        ];
    }
}