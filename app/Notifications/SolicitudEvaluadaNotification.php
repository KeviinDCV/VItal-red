<?php

namespace App\Notifications;

use App\Models\SolicitudReferencia;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SolicitudEvaluadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SolicitudReferencia $solicitud,
        public string $decision
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Solicitud Evaluada: ' . $this->solicitud->codigo_solicitud)
            ->line('Su solicitud de referencia ha sido evaluada.')
            ->line('Código: ' . $this->solicitud->codigo_solicitud)
            ->line('Decisión: ' . strtoupper($this->decision))
            ->action('Ver Detalles', url('/ips/mis-solicitudes'))
            ->line('Gracias por usar VItal-red.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'solicitud_id' => $this->solicitud->id,
            'codigo_solicitud' => $this->solicitud->codigo_solicitud,
            'decision' => $this->decision,
            'mensaje' => "Solicitud {$this->solicitud->codigo_solicitud} ha sido {$this->decision}"
        ];
    }
}