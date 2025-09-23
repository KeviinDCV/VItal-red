<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AIClassificationLog;
use Illuminate\Support\Facades\Log;

class LogAIDecisions
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log AI decisions if response contains AI classification
        if ($response->getStatusCode() === 200 && $request->is('*/ai/*')) {
            $responseData = json_decode($response->getContent(), true);
            
            if (isset($responseData['classification_result'])) {
                try {
                    AIClassificationLog::create([
                        'solicitud_referencia_id' => $responseData['solicitud_id'] ?? null,
                        'classification_result' => $responseData['classification_result'],
                        'confidence_score' => $responseData['confidence_score'] ?? 0,
                        'processing_time_ms' => $responseData['processing_time_ms'] ?? 0,
                        'algorithm_version' => config('ai.classification.algorithm_version', '2.0'),
                        'input_data' => $request->all(),
                        'decision_factors' => $responseData['decision_factors'] ?? [],
                        'created_by' => auth()->id()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to log AI decision: ' . $e->getMessage());
                }
            }
        }

        return $response;
    }
}
