<?php

namespace App\Services;

use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\FeedbackMedico;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContinuousLearningService
{
    private AdvancedAIClassifier $classifier;

    public function __construct(AdvancedAIClassifier $classifier)
    {
        $this->classifier = $classifier;
    }

    public function processMedicalFeedback(int $solicitudId, string $feedback, float $correctScore): void
    {
        $solicitud = SolicitudReferencia::find($solicitudId);
        
        if (!$solicitud) return;

        // Guardar feedback
        FeedbackMedico::create([
            'solicitud_id' => $solicitudId,
            'puntuacion_original' => $solicitud->puntuacion_ia,
            'puntuacion_correcta' => $correctScore,
            'feedback' => $feedback,
            'diferencia' => abs($solicitud->puntuacion_ia - $correctScore),
            'created_at' => now()
        ]);

        // Analizar patrones de error
        $this->analyzeErrorPatterns();
        
        // Ajustar pesos si es necesario
        if ($this->shouldUpdateWeights()) {
            $this->updateAlgorithmWeights();
        }

        Log::info('Medical Feedback Processed', [
            'solicitud_id' => $solicitudId,
            'original_score' => $solicitud->puntuacion_ia,
            'correct_score' => $correctScore,
            'feedback' => $feedback
        ]);
    }

    private function analyzeErrorPatterns(): void
    {
        $recentFeedback = FeedbackMedico::where('created_at', '>=', now()->subDays(7))
            ->with('solicitud.registroMedico')
            ->get();

        $patterns = [
            'age_errors' => 0,
            'specialty_errors' => 0,
            'symptoms_errors' => 0,
            'severity_errors' => 0
        ];

        foreach ($recentFeedback as $feedback) {
            if ($feedback->diferencia > 0.3) {
                $registro = $feedback->solicitud->registroMedico;
                
                // Analizar qué componente falló más
                if ($registro->edad < 18 || $registro->edad > 65) {
                    $patterns['age_errors']++;
                }
                
                if (in_array($registro->especialidad_solicitada, ['cardiologia', 'neurologia'])) {
                    $patterns['specialty_errors']++;
                }
                
                // Más análisis de patrones...
            }
        }

        // Guardar patrones para análisis
        DB::table('ai_learning_patterns')->insert([
            'date' => now()->toDateString(),
            'patterns' => json_encode($patterns),
            'total_feedback' => $recentFeedback->count(),
            'created_at' => now()
        ]);
    }

    private function shouldUpdateWeights(): bool
    {
        $recentAccuracy = $this->calculateRecentAccuracy();
        
        // Actualizar pesos si la precisión baja del 90%
        return $recentAccuracy < 0.90;
    }

    private function updateAlgorithmWeights(): void
    {
        $errorAnalysis = $this->getErrorAnalysis();
        $currentWeights = $this->classifier->getWeights();
        $newWeights = $currentWeights;

        // Ajustar pesos basado en errores
        if ($errorAnalysis['age_error_rate'] > 0.15) {
            $newWeights['age_factor'] += 0.05;
        }

        if ($errorAnalysis['severity_error_rate'] > 0.20) {
            $newWeights['severity_score'] += 0.05;
        }

        if ($errorAnalysis['specialty_error_rate'] > 0.10) {
            $newWeights['specialty_urgency'] += 0.03;
        }

        // Normalizar pesos para que sumen 1.0
        $total = array_sum($newWeights);
        foreach ($newWeights as $key => $weight) {
            $newWeights[$key] = $weight / $total;
        }

        $this->classifier->updateWeights($newWeights);

        Log::info('Algorithm Weights Updated', [
            'old_weights' => $currentWeights,
            'new_weights' => $newWeights,
            'error_analysis' => $errorAnalysis
        ]);
    }

    private function calculateRecentAccuracy(): float
    {
        $feedback = FeedbackMedico::where('created_at', '>=', now()->subDays(30))->get();
        
        if ($feedback->isEmpty()) return 0.95; // Default high accuracy

        $correctPredictions = $feedback->filter(function ($item) {
            return $item->diferencia <= 0.2; // Tolerancia del 20%
        })->count();

        return $correctPredictions / $feedback->count();
    }

    private function getErrorAnalysis(): array
    {
        $feedback = FeedbackMedico::where('created_at', '>=', now()->subDays(30))
            ->with('solicitud.registroMedico')
            ->get();

        $totalErrors = $feedback->where('diferencia', '>', 0.2)->count();
        $total = $feedback->count();

        if ($total === 0) {
            return [
                'age_error_rate' => 0,
                'severity_error_rate' => 0,
                'specialty_error_rate' => 0,
                'symptoms_error_rate' => 0
            ];
        }

        return [
            'age_error_rate' => $this->calculateComponentErrorRate($feedback, 'age'),
            'severity_error_rate' => $this->calculateComponentErrorRate($feedback, 'severity'),
            'specialty_error_rate' => $this->calculateComponentErrorRate($feedback, 'specialty'),
            'symptoms_error_rate' => $this->calculateComponentErrorRate($feedback, 'symptoms')
        ];
    }

    private function calculateComponentErrorRate($feedback, string $component): float
    {
        // Lógica específica para calcular tasa de error por componente
        $componentErrors = 0;
        $total = $feedback->count();

        foreach ($feedback as $item) {
            if ($item->diferencia > 0.2) {
                // Determinar si el error es del componente específico
                $registro = $item->solicitud->registroMedico;
                
                switch ($component) {
                    case 'age':
                        if ($registro->edad < 18 || $registro->edad > 65) {
                            $componentErrors++;
                        }
                        break;
                    case 'specialty':
                        if (in_array($registro->especialidad_solicitada, ['cardiologia', 'neurologia', 'cirugia'])) {
                            $componentErrors++;
                        }
                        break;
                    // Más casos...
                }
            }
        }

        return $total > 0 ? $componentErrors / $total : 0;
    }

    public function generateLearningReport(): array
    {
        return [
            'current_accuracy' => $this->calculateRecentAccuracy(),
            'total_feedback_cases' => FeedbackMedico::count(),
            'recent_improvements' => $this->getRecentImprovements(),
            'error_patterns' => $this->getErrorAnalysis(),
            'recommendations' => $this->generateRecommendations()
        ];
    }

    private function getRecentImprovements(): array
    {
        $thisMonth = $this->calculateAccuracyForPeriod(now()->startOfMonth(), now());
        $lastMonth = $this->calculateAccuracyForPeriod(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth());

        return [
            'current_month_accuracy' => $thisMonth,
            'last_month_accuracy' => $lastMonth,
            'improvement' => $thisMonth - $lastMonth
        ];
    }

    private function calculateAccuracyForPeriod($start, $end): float
    {
        $feedback = FeedbackMedico::whereBetween('created_at', [$start, $end])->get();
        
        if ($feedback->isEmpty()) return 0.95;

        $correct = $feedback->filter(fn($item) => $item->diferencia <= 0.2)->count();
        return $correct / $feedback->count();
    }

    private function generateRecommendations(): array
    {
        $accuracy = $this->calculateRecentAccuracy();
        $recommendations = [];

        if ($accuracy < 0.90) {
            $recommendations[] = 'Revisar y ajustar pesos del algoritmo';
            $recommendations[] = 'Aumentar casos de entrenamiento';
        }

        if ($accuracy < 0.85) {
            $recommendations[] = 'Revisión médica urgente del algoritmo';
            $recommendations[] = 'Considerar reentrenamiento completo';
        }

        return $recommendations;
    }
}