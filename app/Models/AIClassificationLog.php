<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIClassificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'solicitud_referencia_id',
        'classification_result',
        'confidence_score',
        'processing_time_ms',
        'algorithm_version',
        'input_data',
        'decision_factors',
        'manual_override',
        'feedback_score',
        'created_by'
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'processing_time_ms' => 'integer',
        'input_data' => 'array',
        'decision_factors' => 'array',
        'manual_override' => 'boolean',
        'feedback_score' => 'integer'
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudReferencia::class, 'solicitud_referencia_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', 0.8);
    }

    public function scopeCorrectPredictions($query)
    {
        return $query->whereHas('solicitud.decision', function($q) {
            $q->where(function($subQ) {
                $subQ->where('classification_result', 'ROJO')
                     ->where('decision', 'aceptada')
                     ->orWhere('classification_result', 'VERDE')
                     ->where('decision', 'rechazada');
            });
        });
    }

    public function isCorrectPrediction()
    {
        if (!$this->solicitud || !$this->solicitud->decision) {
            return null;
        }

        $decision = $this->solicitud->decision->decision;
        
        return ($this->classification_result === 'ROJO' && $decision === 'aceptada') ||
               ($this->classification_result === 'VERDE' && $decision === 'rechazada');
    }
}