<?php

namespace App\Services;

use App\Models\CriticalAlert;
use App\Models\SolicitudReferencia;
use App\Services\RealTimeNotificationService;
use Carbon\Carbon;

class CriticalAlertService
{
    protected $notificationService;

    public function __construct(RealTimeNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function checkCriticalCases()
    {
        // Casos ROJOS sin respuesta > 30 minutos
        $criticalCases = SolicitudReferencia::where('prioridad_ia', 'ROJO')
            ->whereNull('decision_id')
            ->where('created_at', '<', Carbon::now()->subMinutes(30))
            ->get();

        foreach ($criticalCases as $case) {
            $this->createTimeoutAlert($case);
        }

        // Casos ROJOS sin respuesta > 2 horas (escalamiento)
        $escalationCases = SolicitudReferencia::where('prioridad_ia', 'ROJO')
            ->whereNull('decision_id')
            ->where('created_at', '<', Carbon::now()->subHours(2))
            ->get();

        foreach ($escalationCases as $case) {
            $this->createEscalationAlert($case);
        }
    }

    public function createTimeoutAlert(SolicitudReferencia $solicitud)
    {
        $existingAlert = CriticalAlert::where('source_type', SolicitudReferencia::class)
            ->where('source_id', $solicitud->id)
            ->where('type', 'timeout')
            ->where('status', 'pending')
            ->first();

        if ($existingAlert) {
            return $existingAlert;
        }

        return $this->notificationService->sendCriticalAlert([
            'title' => "⏰ TIMEOUT: Caso ROJO sin respuesta",
            'message' => "Solicitud {$solicitud->codigo_solicitud} lleva más de 30 minutos sin respuesta",
            'priority' => 'HIGH',
            'type' => 'timeout',
            'source_type' => SolicitudReferencia::class,
            'source_id' => $solicitud->id,
            'target_role' => 'medico',
            'action_required' => true,
            'action_url' => "/medico/referencias/{$solicitud->id}",
            'expires_at' => Carbon::now()->addHours(2),
            'metadata' => [
                'patient_name' => $solicitud->registroMedico->nombre,
                'specialty' => $solicitud->especialidad_solicitada,
                'elapsed_minutes' => $solicitud->created_at->diffInMinutes(now())
            ]
        ]);
    }

    public function createEscalationAlert(SolicitudReferencia $solicitud)
    {
        return $this->notificationService->sendCriticalAlert([
            'title' => "🚨 ESCALAMIENTO: Caso ROJO crítico",
            'message' => "Solicitud {$solicitud->codigo_solicitud} requiere intervención inmediata - 2+ horas sin respuesta",
            'priority' => 'CRITICAL',
            'type' => 'escalation',
            'source_type' => SolicitudReferencia::class,
            'source_id' => $solicitud->id,
            'target_role' => 'administrador',
            'action_required' => true,
            'action_url' => "/admin/referencias/{$solicitud->id}",
            'metadata' => [
                'patient_name' => $solicitud->registroMedico->nombre,
                'specialty' => $solicitud->especialidad_solicitada,
                'elapsed_hours' => $solicitud->created_at->diffInHours(now()),
                'ips_name' => $solicitud->solicitante->name
            ]
        ]);
    }

    public function createSystemAlert($title, $message, $priority = 'MEDIUM', $metadata = [])
    {
        return $this->notificationService->sendCriticalAlert([
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'type' => 'system',
            'target_role' => 'administrador',
            'action_required' => $priority === 'CRITICAL',
            'metadata' => $metadata
        ]);
    }

    public function checkSystemHealth()
    {
        // Verificar métricas del sistema
        $metrics = [
            'response_time' => $this->getAverageResponseTime(),
            'ai_accuracy' => $this->getAIAccuracy(),
            'pending_cases' => $this->getPendingCasesCount(),
            'system_load' => $this->getSystemLoad()
        ];

        // Alertas basadas en métricas
        if ($metrics['response_time'] > 3600) { // > 1 hora
            $this->createSystemAlert(
                "⚠️ Tiempo de respuesta elevado",
                "El tiempo promedio de respuesta es de {$metrics['response_time']} segundos",
                'HIGH',
                ['metric' => 'response_time', 'value' => $metrics['response_time']]
            );
        }

        if ($metrics['ai_accuracy'] < 85) { // < 85%
            $this->createSystemAlert(
                "🤖 Precisión IA baja",
                "La precisión del algoritmo IA ha bajado a {$metrics['ai_accuracy']}%",
                'MEDIUM',
                ['metric' => 'ai_accuracy', 'value' => $metrics['ai_accuracy']]
            );
        }

        if ($metrics['pending_cases'] > 50) {
            $this->createSystemAlert(
                "📋 Acumulación de casos",
                "Hay {$metrics['pending_cases']} casos pendientes de revisión",
                'HIGH',
                ['metric' => 'pending_cases', 'value' => $metrics['pending_cases']]
            );
        }
    }

    private function getAverageResponseTime()
    {
        return SolicitudReferencia::whereHas('decision')
            ->whereDate('created_at', today())
            ->get()
            ->avg(function($solicitud) {
                return $solicitud->created_at->diffInSeconds($solicitud->decision->created_at);
            }) ?? 0;
    }

    private function getAIAccuracy()
    {
        // Calcular precisión basada en decisiones recientes
        return 92; // Simulado
    }

    private function getPendingCasesCount()
    {
        return SolicitudReferencia::whereNull('decision_id')->count();
    }

    private function getSystemLoad()
    {
        // Obtener carga del sistema
        return 0.75; // Simulado
    }

    public function resolveAlert(CriticalAlert $alert, $userId = null)
    {
        $alert->resolve($userId);
        
        // Notificar resolución si es necesario
        if ($alert->priority === 'CRITICAL') {
            $this->notificationService->sendNotification(
                $alert->created_by ?? 1,
                "✅ Alerta resuelta",
                "La alerta crítica '{$alert->title}' ha sido resuelta",
                'success'
            );
        }
    }
}