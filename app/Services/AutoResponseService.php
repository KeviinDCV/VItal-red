<?php

namespace App\Services;

use App\Models\SolicitudReferencia;
use App\Models\AutomaticResponse;
use App\Models\ResponseTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoResponseService
{
    public function processGreenCase(SolicitudReferencia $solicitud)
    {
        try {
            // Buscar plantilla apropiada
            $template = $this->findBestTemplate($solicitud);
            
            if (!$template) {
                Log::warning("No se encontró plantilla para solicitud {$solicitud->id}");
                return false;
            }

            // Generar respuesta personalizada
            $response = $this->generateResponse($solicitud, $template);
            
            // Enviar email
            $sent = $this->sendEmail($response);
            
            if ($sent) {
                $response->markAsSent();
                $template->incrementUsage();
                
                // Crear notificación para el solicitante
                $this->createNotification($solicitud, $response);
                
                Log::info("Respuesta automática enviada para solicitud {$solicitud->id}");
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error("Error procesando respuesta automática: " . $e->getMessage());
            return false;
        }
    }

    private function findBestTemplate(SolicitudReferencia $solicitud)
    {
        return ResponseTemplate::active()
            ->forSpecialty($solicitud->especialidad_solicitada)
            ->forPriority('VERDE')
            ->orderBy('usage_count', 'desc')
            ->first();
    }

    private function generateResponse(SolicitudReferencia $solicitud, ResponseTemplate $template)
    {
        $variables = $this->extractVariables($solicitud);
        
        $response = AutomaticResponse::create([
            'solicitud_referencia_id' => $solicitud->id,
            'response_template_id' => $template->id,
            'recipient_email' => $solicitud->solicitante->email,
            'recipient_name' => $solicitud->solicitante->name,
            'subject' => $template->renderSubject($variables),
            'content' => $template->renderContent($variables),
            'status' => 'pending',
            'variables_used' => $variables
        ]);

        return $response;
    }

    private function extractVariables(SolicitudReferencia $solicitud)
    {
        return [
            'patient_name' => $solicitud->registroMedico->nombre . ' ' . $solicitud->registroMedico->apellidos,
            'patient_id' => $solicitud->registroMedico->numero_identificacion,
            'specialty' => $solicitud->especialidad_solicitada,
            'ips_name' => $solicitud->solicitante->name,
            'reference_code' => $solicitud->codigo_solicitud,
            'date' => now()->format('d/m/Y'),
            'estimated_time' => $this->calculateEstimatedTime($solicitud),
            'contact_phone' => config('app.contact_phone', '555-0123'),
            'contact_email' => config('app.contact_email', 'referencias@hospital.com')
        ];
    }

    private function calculateEstimatedTime(SolicitudReferencia $solicitud)
    {
        // Lógica para calcular tiempo estimado basado en especialidad y carga
        $baseTime = [
            'Cardiología' => '3-5 días hábiles',
            'Neurología' => '5-7 días hábiles',
            'Oncología' => '2-3 días hábiles',
            'Pediatría' => '2-4 días hábiles',
            'Ginecología' => '3-5 días hábiles',
            'Ortopedia' => '5-10 días hábiles',
            'Dermatología' => '7-14 días hábiles',
            'Psiquiatría' => '10-15 días hábiles'
        ];

        return $baseTime[$solicitud->especialidad_solicitada] ?? '5-7 días hábiles';
    }

    private function sendEmail(AutomaticResponse $response)
    {
        try {
            Mail::send('emails.automatic-response', [
                'content' => $response->content,
                'patient_name' => $response->variables_used['patient_name'] ?? '',
                'reference_code' => $response->variables_used['reference_code'] ?? ''
            ], function ($message) use ($response) {
                $message->to($response->recipient_email, $response->recipient_name)
                       ->subject($response->subject)
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Error enviando email automático: " . $e->getMessage());
            return false;
        }
    }

    private function createNotification(SolicitudReferencia $solicitud, AutomaticResponse $response)
    {
        $solicitud->solicitante->notificaciones()->create([
            'titulo' => 'Respuesta Automática Enviada',
            'mensaje' => "Se ha enviado una respuesta automática para la solicitud {$solicitud->codigo_solicitud}",
            'tipo' => 'info',
            'leida' => false,
            'metadata' => [
                'response_id' => $response->id,
                'solicitud_id' => $solicitud->id
            ]
        ]);
    }

    public function getSuccessRate()
    {
        $total = AutomaticResponse::count();
        if ($total === 0) return 0;

        $successful = AutomaticResponse::successful()->count();
        return round(($successful / $total) * 100, 2);
    }

    public function getAverageResponseTime()
    {
        return AutomaticResponse::whereNotNull('response_time_seconds')
            ->avg('response_time_seconds') ?? 0;
    }

    public function getMetrics($period = '7d')
    {
        $startDate = match($period) {
            '1d' => Carbon::now()->subDay(),
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            default => Carbon::now()->subDays(7)
        };

        return [
            'total_sent' => AutomaticResponse::where('created_at', '>=', $startDate)->count(),
            'success_rate' => $this->getSuccessRateForPeriod($startDate),
            'avg_response_time' => $this->getAverageResponseTimeForPeriod($startDate),
            'by_specialty' => $this->getMetricsBySpecialty($startDate),
            'daily_breakdown' => $this->getDailyBreakdown($startDate)
        ];
    }

    private function getSuccessRateForPeriod($startDate)
    {
        $total = AutomaticResponse::where('created_at', '>=', $startDate)->count();
        if ($total === 0) return 0;

        $successful = AutomaticResponse::successful()
            ->where('created_at', '>=', $startDate)
            ->count();
            
        return round(($successful / $total) * 100, 2);
    }

    private function getAverageResponseTimeForPeriod($startDate)
    {
        return AutomaticResponse::where('created_at', '>=', $startDate)
            ->whereNotNull('response_time_seconds')
            ->avg('response_time_seconds') ?? 0;
    }

    private function getMetricsBySpecialty($startDate)
    {
        return AutomaticResponse::join('solicitudes_referencia', 'automatic_responses.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
            ->where('automatic_responses.created_at', '>=', $startDate)
            ->selectRaw('solicitudes_referencia.especialidad_solicitada, COUNT(*) as count, AVG(automatic_responses.response_time_seconds) as avg_time')
            ->groupBy('solicitudes_referencia.especialidad_solicitada')
            ->get();
    }

    private function getDailyBreakdown($startDate)
    {
        return AutomaticResponse::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function previewResponse(ResponseTemplate $template, array $testData)
    {
        return [
            'subject' => $template->renderSubject($testData),
            'content' => $template->renderContent($testData),
            'variables' => $template->getAvailableVariables()
        ];
    }
}