<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HISIntegrationService;
use App\Services\LabIntegrationService;
use App\Services\PACSIntegrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationsController extends Controller
{
    public function __construct(
        private HISIntegrationService $hisService,
        private LabIntegrationService $labService,
        private PACSIntegrationService $pacsService
    ) {}

    public function index()
    {
        $integrations = [
            'his' => [
                'name' => 'Hospital Information System',
                'status' => $this->hisService->isConnected() ? 'connected' : 'disconnected',
                'last_sync' => now()->subMinutes(5),
                'total_syncs' => 1247,
                'errors_today' => 2
            ],
            'lab' => [
                'name' => 'Sistema de Laboratorio',
                'status' => $this->labService->isConnected() ? 'connected' : 'disconnected',
                'last_sync' => now()->subMinutes(2),
                'total_syncs' => 856,
                'errors_today' => 0
            ],
            'pacs' => [
                'name' => 'Picture Archiving System',
                'status' => $this->pacsService->isConnected() ? 'connected' : 'disconnected',
                'last_sync' => now()->subMinutes(8),
                'total_syncs' => 423,
                'errors_today' => 1
            ]
        ];

        return Inertia::render('admin/Integrations', compact('integrations'));
    }

    public function syncPatient(Request $request)
    {
        $patientId = $request->get('patient_id');
        
        $results = [
            'his_data' => $this->hisService->syncPatientData($patientId),
            'lab_results' => $this->labService->getLabResults($patientId),
            'medical_images' => $this->pacsService->getMedicalImages($patientId)
        ];

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => 'Sincronización completada'
        ]);
    }

    public function testConnection(Request $request)
    {
        $service = $request->get('service');
        
        $status = match($service) {
            'his' => $this->hisService->isConnected(),
            'lab' => $this->labService->isConnected(),
            'pacs' => $this->pacsService->isConnected(),
            default => false
        };

        return response()->json([
            'service' => $service,
            'connected' => $status,
            'timestamp' => now()
        ]);
    }
}