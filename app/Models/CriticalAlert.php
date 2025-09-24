<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CriticalAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'priority',
        'type',
        'source_type',
        'source_id',
        'target_role',
        'assigned_to',
        'status',
        'action_required',
        'action_url',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_by',
        'resolved_at',
        'expires_at',
        'metadata'
    ];

    protected $casts = [
        'action_required' => 'boolean',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relaciones
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function source()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'acknowledged'])
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'CRITICAL');
    }

    public function scopeForRole($query, $role)
    {
        return $query->where('target_role', $role)
            ->orWhereNull('target_role');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Métodos
    public function acknowledge($userId = null)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $userId ?? auth()->id(),
            'acknowledged_at' => now()
        ]);
    }

    public function resolve($userId = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $userId ?? auth()->id(),
            'resolved_at' => now()
        ]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPending()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function isAcknowledged()
    {
        return $this->status === 'acknowledged';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function getUrgencyLevel()
    {
        if ($this->priority === 'CRITICAL') {
            return 5;
        } elseif ($this->priority === 'HIGH') {
            return 4;
        } elseif ($this->priority === 'MEDIUM') {
            return 3;
        } elseif ($this->priority === 'LOW') {
            return 2;
        }
        return 1;
    }

    public function getTimeElapsed()
    {
        return $this->created_at->diffForHumans();
    }

    public function shouldEscalate()
    {
        if ($this->priority === 'CRITICAL' && $this->status === 'pending') {
            return $this->created_at->diffInMinutes(now()) > 15;
        }
        
        if ($this->priority === 'HIGH' && $this->status === 'pending') {
            return $this->created_at->diffInHours(now()) > 1;
        }
        
        return false;
    }
    
    public function getTimeElapsedAttribute()
    {
        return $this->created_at->diffForHumans();
    }
    
    public function getShouldEscalateAttribute()
    {
        return $this->shouldEscalate();
    }
}