<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specialty',
        'priority',
        'subject',
        'content',
        'variables',
        'active',
        'created_by',
        'usage_count'
    ];

    protected $casts = [
        'variables' => 'array',
        'active' => 'boolean',
        'usage_count' => 'integer'
    ];

    // Relaciones
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses()
    {
        return $this->hasMany(AutomaticResponse::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForSpecialty($query, $specialty)
    {
        return $query->where('specialty', $specialty)
            ->orWhere('specialty', 'general');
    }

    public function scopeForPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Métodos
    public function renderContent($variables = [])
    {
        $content = $this->content;
        
        foreach ($variables as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        
        return $content;
    }

    public function renderSubject($variables = [])
    {
        $subject = $this->subject;
        
        foreach ($variables as $key => $value) {
            $subject = str_replace("{{$key}}", $value, $subject);
        }
        
        return $subject;
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function getAvailableVariables()
    {
        return $this->variables ?? [
            'patient_name',
            'patient_id',
            'specialty',
            'ips_name',
            'reference_code',
            'date',
            'estimated_time'
        ];
    }

    public function isCompatibleWith($solicitud)
    {
        // Verificar si la plantilla es compatible con la solicitud
        if ($this->specialty !== 'general' && 
            $this->specialty !== $solicitud->especialidad_solicitada) {
            return false;
        }

        return true;
    }
}