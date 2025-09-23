<?php

namespace App\Http\Controllers\IPS;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeguimientoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $solicitudes = SolicitudReferencia::with(['registroMedico', 'decision'])
            ->whereHas('registroMedico', function($query) use ($user) {
                $query->where('ips_id', $user->ips_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('IPS/Seguimiento', [
            'solicitudes' => $solicitudes
        ]);
    }

    public function show($id)
    {
        $user = auth()->user();
        
        $solicitud = SolicitudReferencia::with(['registroMedico', 'decision', 'seguimiento'])
            ->whereHas('registroMedico', function($query) use ($user) {
                $query->where('ips_id', $user->ips_id);
            })
            ->findOrFail($id);

        return Inertia::render('IPS/DetalleSeguimiento', [
            'solicitud' => $solicitud
        ]);
    }
}