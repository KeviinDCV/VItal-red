<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SolicitudReferencia;
use App\Events\NuevaNotificacion;
use Exception;

class LabIntegrationService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.lab.base_url', 'https://lab-api.hospital.com');
        $this->apiKey = config('services.lab.api_key');
    }

    public function getLabResults($patientId)
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get("{$this->baseUrl}/api/results/{$patientId}");

            if ($response->successful()) {
                $results = $response->json();
                $this->processLabResults($results, $patientId);
                return $results;
            }

            return [];

        } catch (Exception $e) {
            Log::error('Lab Integration Error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function attachResultsToReference($solicitudId, $labResults)
    {
        try {
            $solicitud = SolicitudReferencia::find($solicitudId);
            if (!$solicitud) return false;

            // Store lab results as JSON in the database
            $solicitud->update([
                'resultados_laboratorio' => json_encode($labResults),
                'lab_sync_at' => now()
            ]);

            // Check for critical values
            $this->checkCriticalValues($labResults, $solicitud);

            return true;

        } catch (Exception $e) {
            Log::error('Lab Results Attachment Error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function processLabResults($results, $patientId)
    {
        foreach ($results as $result) {
            if ($this->isCriticalValue($result)) {
                $this->sendCriticalAlert($result, $patientId);
            }
        }
    }

    private function isCriticalValue($result)
    {
        $criticalRanges = [
            'glucose' => ['min' => 70, 'max' => 200],
            'creatinine' => ['min' => 0.6, 'max' => 1.3],
            'hemoglobin' => ['min' => 12, 'max' => 18],
            'white_blood_cells' => ['min' => 4000, 'max' => 11000]
        ];

        $testName = strtolower($result['test_name'] ?? '');
        $value = floatval($result['value'] ?? 0);

        foreach ($criticalRanges as $test => $range) {
            if (strpos($testName, $test) !== false) {
                return $value < $range['min'] || $value > $range['max'];
            }
        }

        return false;
    }

    private function sendCriticalAlert($result, $patientId)
    {
        $message = "Valor crítico de laboratorio detectado: {$result['test_name']} = {$result['value']} {$result['unit']} para paciente {$patientId}";

        event(new NuevaNotificacion([
            'tipo' => 'lab_critical',
            'titulo' => 'Valor Crítico de Laboratorio',
            'mensaje' => $message,
            'prioridad' => 'ALTA',
            'usuario_id' => null, // Send to all medical staff
            'metadata' => [
                'patient_id' => $patientId,
                'test_name' => $result['test_name'],
                'value' => $result['value'],
                'unit' => $result['unit']
            ]
        ]));
    }

    private function checkCriticalValues($labResults, $solicitud)
    {
        foreach ($labResults as $result) {
            if ($this->isCriticalValue($result)) {
                // Upgrade priority if critical lab values found
                if ($solicitud->prioridad === 'VERDE') {
                    $solicitud->update(['prioridad' => 'ROJO']);
                    
                    event(new NuevaNotificacion([
                        'tipo' => 'priority_upgrade',
                        'titulo' => 'Prioridad Actualizada por Laboratorio',
                        'mensaje' => "Solicitud {$solicitud->codigo_solicitud} actualizada a ROJO por valores críticos de laboratorio",
                        'prioridad' => 'ALTA',
                        'usuario_id' => null
                    ]));
                }
            }
        }
    }

    public function isConnected()
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get("{$this->baseUrl}/api/health");

            return $response->successful();

        } catch (Exception $e) {
            return false;
        }
    }
}