<?php

namespace App\Services;

use App\Models\SolicitudReferencia;
use App\Models\ResponseTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AutomaticResponseGenerator
{
    public function generateResponse(SolicitudReferencia $solicitud): array
    {
        try {
            $template = $this->selectTemplate($solicitud);
            $personalizedContent = $this->personalizeContent($template, $solicitud);
            
            // Enviar email automático
            $emailSent = $this->sendAutomaticEmail($solicitud, $personalizedContent);
            
            // Registrar respuesta enviada
            $this->logResponse($solicitud, $personalizedContent);
            
            return [
                'status' => 'sent',
                'content' => $personalizedContent,
                'email_sent' => $emailSent,
                'timestamp' => now(),
                'type' => 'automatic_response'
            ];
        } catch (\Exception $e) {
            Log::error('Error generating automatic response: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => now()
            ];
        }
    }

    private function selectTemplate(SolicitudReferencia $solicitud): array
    {
        $specialty = $solicitud->registroMedico->especialidad_solicitada;
        $reason = $this->determineRejectionReason($solicitud);
        
        // Templates por especialidad y razón
        $templates = [
            'no_capacity' => [
                'cardiologia' => [
                    'subject' => 'Referencia {codigo_solicitud} - No disponibilidad de cupos',
                    'content' => 'Estimado Dr. {doctor_name},

Hemos recibido su solicitud de referencia para el paciente {patient_name} a la especialidad de Cardiología.

Actualmente no contamos con disponibilidad de cupos para atención electiva en esta especialidad. Le sugerimos:

1. Continuar manejo en su nivel de atención
2. Contactar IPS de segundo nivel más cercana  
3. En caso de urgencia, dirigirse directamente a urgencias

Atentamente,
Centro de Referencia y Contrarreferencia
Hospital de Alta Complejidad'
                ],
                'general' => [
                    'subject' => 'Referencia {codigo_solicitud} - No disponibilidad de cupos',
                    'content' => 'Estimado Dr. {doctor_name},

Hemos recibido su solicitud de referencia para el paciente {patient_name} a la especialidad de {specialty}.

Actualmente no contamos con disponibilidad de cupos para esta especialidad. Le recomendamos:

{alternative_recommendations}

Atentamente,
Centro de Referencia y Contrarreferencia'
                ]
            ],
            'insufficient_complexity' => [
                'general' => [
                    'subject' => 'Referencia {codigo_solicitud} - Manejo en menor nivel de complejidad',
                    'content' => 'Estimado Dr. {doctor_name},

Después de revisar la solicitud para el paciente {patient_name}, consideramos que el caso puede ser manejado en un nivel de menor complejidad.

Recomendaciones:
{alternative_recommendations}

Quedamos atentos a cualquier consulta.

Atentamente,
Centro de Referencia y Contrarreferencia'
                ]
            ]
        ];

        $specialtyKey = strtolower(str_replace(' ', '', $specialty));
        
        if (isset($templates[$reason][$specialtyKey])) {
            return $templates[$reason][$specialtyKey];
        }
        
        return $templates[$reason]['general'] ?? $templates['no_capacity']['general'];
    }

    private function determineRejectionReason(SolicitudReferencia $solicitud): string
    {
        // Lógica para determinar la razón del rechazo automático
        $specialty = $solicitud->registroMedico->especialidad_solicitada;
        $priority = $solicitud->prioridad;
        
        // Si es VERDE, probablemente es por falta de cupos o complejidad insuficiente
        if ($priority === 'VERDE') {
            // Especialidades con alta demanda = no_capacity
            $highDemandSpecialties = ['cardiologia', 'neurologia', 'oncologia'];
            
            if (in_array(strtolower($specialty), $highDemandSpecialties)) {
                return 'no_capacity';
            }
            
            return 'insufficient_complexity';
        }
        
        return 'no_capacity';
    }

    private function personalizeContent(array $template, SolicitudReferencia $solicitud): string
    {
        $replacements = [
            '{patient_name}' => $solicitud->registroMedico->nombre . ' ' . $solicitud->registroMedico->apellidos,
            '{doctor_name}' => $solicitud->medico_remitente ?? 'Doctor',
            '{ips_name}' => $solicitud->ips->nombre ?? 'IPS Remitente',
            '{specialty}' => $solicitud->registroMedico->especialidad_solicitada,
            '{codigo_solicitud}' => $solicitud->codigo_solicitud,
            '{date}' => now()->format('d/m/Y'),
            '{alternative_recommendations}' => $this->getAlternativeRecommendations($solicitud)
        ];

        $content = $template['content'];
        foreach ($replacements as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    private function getAlternativeRecommendations(SolicitudReferencia $solicitud): string
    {
        $specialty = $solicitud->registroMedico->especialidad_solicitada;
        
        $recommendations = [
            'cardiologia' => '• Manejo con medicina interna en su IPS
• Control con cardiólogo de segundo nivel
• Medicamentos disponibles en su nivel de atención',
            
            'neurologia' => '• Evaluación por medicina interna
• Manejo sintomático según guías
• Interconsulta con neurología de segundo nivel',
            
            'general' => '• Continuar manejo en su nivel de atención
• Interconsulta con especialista de segundo nivel
• Seguimiento según evolución del paciente'
        ];

        $specialtyKey = strtolower(str_replace(' ', '', $specialty));
        return $recommendations[$specialtyKey] ?? $recommendations['general'];
    }

    private function sendAutomaticEmail(SolicitudReferencia $solicitud, string $content): bool
    {
        try {
            // Aquí iría la lógica de envío de email
            // Por ahora simulamos el envío
            
            $emailData = [
                'to' => $solicitud->ips->email ?? 'noreply@hospital.com',
                'subject' => "Respuesta automática - Referencia {$solicitud->codigo_solicitud}",
                'content' => $content,
                'solicitud_id' => $solicitud->id
            ];

            // Mail::send('emails.automatic-response', $emailData, function($message) use ($emailData) {
            //     $message->to($emailData['to'])
            //             ->subject($emailData['subject']);
            // });

            Log::info('Automatic email sent', $emailData);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send automatic email: ' . $e->getMessage());
            return false;
        }
    }

    private function logResponse(SolicitudReferencia $solicitud, string $content): void
    {
        Log::info('Automatic response generated', [
            'solicitud_id' => $solicitud->id,
            'codigo_solicitud' => $solicitud->codigo_solicitud,
            'prioridad' => $solicitud->prioridad,
            'especialidad' => $solicitud->registroMedico->especialidad_solicitada,
            'content_length' => strlen($content),
            'timestamp' => now()
        ]);
    }

    public function processGreenCases(): int
    {
        $greenCases = SolicitudReferencia::where('prioridad', 'VERDE')
            ->where('estado', 'PENDIENTE')
            ->whereNull('respuesta_automatica_enviada')
            ->limit(100) // Procesar en lotes
            ->get();

        $processed = 0;
        
        foreach ($greenCases as $solicitud) {
            $response = $this->generateResponse($solicitud);
            
            if ($response['status'] === 'sent') {
                $solicitud->update([
                    'estado' => 'NO_ADMITIDO',
                    'respuesta_automatica_enviada' => now(),
                    'observaciones_ia' => 'Respuesta automática generada por el sistema'
                ]);
                $processed++;
            }
        }

        return $processed;
    }
}