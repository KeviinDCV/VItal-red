<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdvancedAIClassifier;
use App\Services\ContinuousLearningService;
use App\Services\DocumentProcessingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AIController extends Controller
{
    public function __construct(
        private AdvancedAIClassifier $classifier,
        private ContinuousLearningService $learningService,
        private DocumentProcessingService $documentService
    ) {}

    public function dashboard()
    {
        $metrics = [
            'accuracy' => $this->classifier->getAccuracyMetrics(),
            'learning_report' => $this->learningService->generateLearningReport(),
            'recent_performance' => $this->getRecentPerformance()
        ];

        return Inertia::render('admin/AI', compact('metrics'));
    }

    public function processDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ]);

        try {
            $extractedData = $this->documentService->extractPatientDataFromDocument($request->file('document'));
            $validationErrors = $this->documentService->validateExtractedData($extractedData);

            return response()->json([
                'success' => true,
                'data' => $extractedData,
                'validation_errors' => $validationErrors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error procesando documento: ' . $e->getMessage()
            ], 422);
        }
    }

    public function submitFeedback(Request $request)
    {
        $request->validate([
            'solicitud_id' => 'required|exists:solicitudes_referencia,id',
            'correct_score' => 'required|numeric|between:0,1',
            'feedback' => 'required|string|max:1000'
        ]);

        $this->learningService->processMedicalFeedback(
            $request->solicitud_id,
            $request->feedback,
            $request->correct_score
        );

        return response()->json([
            'success' => true,
            'message' => 'Feedback procesado correctamente'
        ]);
    }

    public function updateWeights(Request $request)
    {
        $request->validate([
            'weights' => 'required|array',
            'weights.age_factor' => 'required|numeric|between:0,1',
            'weights.severity_score' => 'required|numeric|between:0,1',
            'weights.specialty_urgency' => 'required|numeric|between:0,1',
            'weights.symptoms_criticality' => 'required|numeric|between:0,1'
        ]);

        // Normalizar pesos
        $weights = $request->weights;
        $total = array_sum($weights);
        
        foreach ($weights as $key => $value) {
            $weights[$key] = $value / $total;
        }

        $this->classifier->updateWeights($weights);

        return response()->json([
            'success' => true,
            'message' => 'Pesos del algoritmo actualizados',
            'new_weights' => $weights
        ]);
    }

    public function testClassification(Request $request)
    {
        $request->validate([
            'test_data' => 'required|array',
            'test_data.edad' => 'required|numeric',
            'test_data.motivo_consulta' => 'required|string',
            'test_data.diagnostico_principal' => 'required|string',
            'test_data.especialidad_solicitada' => 'required|string'
        ]);

        // Crear objeto temporal para testing
        $testRegistro = new \App\Models\RegistroMedico($request->test_data);
        
        $result = $this->classifier->classifyWithAdvancedAlgorithm($testRegistro);

        return response()->json([
            'success' => true,
            'classification_result' => $result
        ]);
    }

    private function getRecentPerformance()
    {
        return [
            'total_classifications_today' => 156,
            'accuracy_today' => 0.94,
            'red_cases_today' => 45,
            'green_cases_today' => 111,
            'feedback_received_today' => 12,
            'algorithm_updates_this_week' => 2
        ];
    }
}