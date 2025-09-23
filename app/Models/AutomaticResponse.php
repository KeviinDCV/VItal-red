<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AutomaticResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'solicitud_referencia_id',
        'response_template_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'content',
        'status',
        'sent_at',
        'delivery_status',
        'error_message',
        'response_time_seconds',
        'variables_used'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'variables_used' => 'array',
        'response_time_seconds' => 'float'
    ];

    // Relaciones
    public function solicitud()
    {
        return $this->belongsTo(SolicitudReferencia::class, 'solicitud_referencia_id');
    }

    public function template()
    {
        return $this->belongsTo(ResponseTemplate::class, 'response_template_id');
    }

    // Scopes
    public function scopeSentToday($query)
    {
        return $query->whereDate('sent_at', today());
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'sent')
            ->where('delivery_status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed')
            ->orWhere('delivery_status', 'failed');
    }

    // Métodos
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'delivery_status' => 'pending'
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'delivery_status' => 'delivered'
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'delivery_status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }

    public function getResponseTimeAttribute()
    {
        if ($this->sent_at && $this->solicitud) {
            return $this->sent_at->diffInSeconds($this->solicitud->created_at);
        }
        return null;
    }

    public function isSuccessful()
    {
        return $this->status === 'sent' && $this->delivery_status === 'delivered';
    }
}