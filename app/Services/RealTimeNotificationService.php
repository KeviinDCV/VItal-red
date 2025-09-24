<?php

namespace App\Services;

use App\Models\CriticalAlert;
use App\Models\Notificacion;
use App\Events\CriticalAlertCreated;
use App\Events\NotificationSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RealTimeNotificationService
{
    public function sendCriticalAlert(array $alertData)
    {
        try {
            $alert = CriticalAlert::create($alertData);
            
            // Broadcast via WebSocket
            broadcast(new CriticalAlertCreated($alert));
            
            // Send email if critical
            if ($alert->priority === 'CRITICAL') {
                $this->sendCriticalEmail($alert);
            }
            
            // Send SMS if required
            if ($alert->action_required) {
                $this->sendSMS($alert);
            }
            
            Log::info("Critical alert created: {$alert->id}");
            
            return $alert;
        } catch (\Exception $e) {
            Log::error("Failed to send critical alert: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendNotification($userId, $title, $message, $type = 'info', $metadata = [])
    {
        try {
            $notification = Notificacion::create([
                'user_id' => $userId,
                'titulo' => $title,
                'mensaje' => $message,
                'tipo' => $type,
                'leida' => false,
                'metadata' => $metadata
            ]);

            // Broadcast in real-time
            broadcast(new NotificationSent($notification));
            
            return $notification;
        } catch (\Exception $e) {
            Log::error("Failed to send notification: " . $e->getMessage());
            return false;
        }
    }

    public function broadcastAlertAcknowledged(CriticalAlert $alert)
    {
        broadcast(new \App\Events\AlertAcknowledged($alert));
    }

    public function escalateAlert(CriticalAlert $alert)
    {
        // Escalar a supervisor o administrador
        $supervisors = \App\Models\User::where('role', 'administrador')->get();
        
        foreach ($supervisors as $supervisor) {
            $this->sendNotification(
                $supervisor->id,
                "🚨 ESCALAMIENTO: {$alert->title}",
                "Alerta crítica escalada: {$alert->message}",
                'critical',
                ['original_alert_id' => $alert->id]
            );
        }
        
        $alert->update(['status' => 'escalated']);
    }

    private function sendCriticalEmail(CriticalAlert $alert)
    {
        if ($alert->assignedUser && $alert->assignedUser->email) {
            try {
                Mail::send('emails.critical-alert', [
                    'alert' => $alert
                ], function ($message) use ($alert) {
                    $message->to($alert->assignedUser->email)
                           ->subject("🚨 ALERTA CRÍTICA: {$alert->title}");
                });
            } catch (\Exception $e) {
                Log::error("Failed to send critical email: " . $e->getMessage());
            }
        }
    }

    private function sendSMS(CriticalAlert $alert)
    {
        // Implementar envío SMS
        Log::info("SMS would be sent for alert: {$alert->id}");
    }

    public function checkForEscalation()
    {
        $alertsToEscalate = CriticalAlert::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(15))
            ->where('priority', 'CRITICAL')
            ->get();

        foreach ($alertsToEscalate as $alert) {
            $this->escalateAlert($alert);
        }
    }

    public function sendBulkNotification($userIds, $title, $message, $type = 'info')
    {
        foreach ($userIds as $userId) {
            $this->sendNotification($userId, $title, $message, $type);
        }
    }

    public function getNotificationStats($userId)
    {
        return [
            'total' => Notificacion::where('user_id', $userId)->count(),
            'unread' => Notificacion::where('user_id', $userId)->where('leida', false)->count(),
            'critical' => Notificacion::where('user_id', $userId)->where('tipo', 'critical')->count()
        ];
    }
}