<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin', function ($user) {
    return $user->role === 'administrador';
});

Broadcast::channel('medicos', function ($user) {
    return $user->role === 'medico';
});

Broadcast::channel('ips', function ($user) {
    return $user->role === 'ips';
});