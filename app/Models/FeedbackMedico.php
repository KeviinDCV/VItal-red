<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackMedico extends Model
{
    protected $table = 'feedback_medico';

    protected $fillable = [
        'solicitud_id',
        'puntuacion_original',
        'puntuacion_correcta',
        'feedback',
        'diferencia',
        'medico_id'
    ];

    protected $casts = [
        'puntuacion_original' => 'float',
        'puntuacion_correcta' => 'float',
        'diferencia' => 'float'
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudReferencia::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medico_id');
    }
}