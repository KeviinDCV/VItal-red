<?php

namespace App\Policies;

use App\Models\SolicitudReferencia;
use App\Models\User;

class SolicitudReferenciaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['administrador', 'medico', 'jefe-urgencias']);
    }

    public function view(User $user, SolicitudReferencia $solicitud): bool
    {
        return match($user->role) {
            'administrador', 'jefe-urgencias' => true,
            'medico' => true,
            'ips' => $solicitud->registroMedico->user_id === $user->id,
            default => false
        };
    }

    public function create(User $user): bool
    {
        return $user->role === 'ips';
    }

    public function update(User $user, SolicitudReferencia $solicitud): bool
    {
        return match($user->role) {
            'administrador' => true,
            'medico' => $solicitud->estado === 'PENDIENTE',
            'ips' => $solicitud->estado === 'PENDIENTE' && $solicitud->registroMedico->user_id === $user->id,
            default => false
        };
    }

    public function delete(User $user, SolicitudReferencia $solicitud): bool
    {
        return $user->role === 'administrador';
    }

    public function evaluate(User $user, SolicitudReferencia $solicitud): bool
    {
        return $user->role === 'medico' && $solicitud->estado === 'PENDIENTE';
    }
}