<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SolicitudReferencia;
use Exception;

class PACSIntegrationService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.pacs.base_url', 'https://pacs-api.hospital.com');
        $this->apiKey = config('services.pacs.api_key');
    }

    public function getMedicalImages($patientId)
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get("{$this->baseUrl}/api/images/{$patientId}");

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (Exception $e) {
            Log::error('PACS Integration Error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getImageViewerUrl($imageId)
    {
        return "{$this->baseUrl}/viewer/{$imageId}?token={$this->apiKey}";
    }

    public function getRadiologyReports($patientId)
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get("{$this->baseUrl}/api/reports/{$patientId}");

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (Exception $e) {
            Log::error('Radiology Reports Error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function attachImagesToReference($solicitudId, $imageIds)
    {
        try {
            $solicitud = SolicitudReferencia::find($solicitudId);
            if (!$solicitud) return false;

            $imageUrls = [];
            foreach ($imageIds as $imageId) {
                $imageUrls[] = $this->getImageViewerUrl($imageId);
            }

            $solicitud->update([
                'imagenes_medicas' => json_encode($imageUrls),
                'pacs_sync_at' => now()
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Images Attachment Error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function analyzeImageWithAI($imageId)
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->post("{$this->baseUrl}/api/ai-analysis/{$imageId}");

            if ($response->successful()) {
                $analysis = $response->json();
                return [
                    'findings' => $analysis['findings'] ?? [],
                    'confidence' => $analysis['confidence'] ?? 0,
                    'recommendations' => $analysis['recommendations'] ?? [],
                    'critical_findings' => $analysis['critical_findings'] ?? false
                ];
            }

            return null;

        } catch (Exception $e) {
            Log::error('AI Image Analysis Error', ['error' => $e->getMessage()]);
            return null;
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