<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\RegistroMedico;
use Exception;

class HISIntegrationService
{
    private $baseUrl;
    private $apiKey;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.his.base_url', 'https://his-api.hospital.com');
        $this->apiKey = config('services.his.api_key');
        $this->timeout = config('services.his.timeout', 30);
    }

    public function syncPatientData($patientId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ])
                ->get("{$this->baseUrl}/api/patients/{$patientId}");

            if ($response->successful()) {
                $patientData = $response->json();
                return $this->processPatientData($patientData);
            }

            Log::error('HIS API Error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (Exception $e) {
            Log::error('HIS Integration Error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getMedicalHistory($patientId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get("{$this->baseUrl}/api/patients/{$patientId}/history");

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (Exception $e) {
            Log::error('Medical History Error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function syncDemographicData($patientId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get("{$this->baseUrl}/api/patients/{$patientId}/demographics");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (Exception $e) {
            Log::error('Demographics Sync Error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function processPatientData($data)
    {
        return [
            'nombre' => $data['first_name'] ?? '',
            'apellidos' => $data['last_name'] ?? '',
            'numero_identificacion' => $data['patient_id'] ?? '',
            'fecha_nacimiento' => $data['birth_date'] ?? '',
            'sexo' => $data['gender'] ?? '',
            'telefono' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'direccion' => $data['address'] ?? '',
            'asegurador' => $data['insurance'] ?? '',
            'his_sync_at' => now()
        ];
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