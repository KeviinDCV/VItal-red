<?php

namespace App\Events;

use App\Models\Notificacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevaNotificacion implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notificacion;

    public function __construct(Notificacion $notificacion)
    {
        $this->notificacion = $notificacion;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->notificacion->user_id);
    }

    public function broadcastAs()
    {
        return 'nueva-notificacion';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->notificacion->id,
            'tipo' => $this->notificacion->tipo,
            'titulo' => $this->notificacion->titulo,
            'mensaje' => $this->notificacion->mensaje,
            'prioridad' => $this->notificacion->prioridad,
            'created_at' => $this->notificacion->created_at->toISOString(),
        ];
    }
}