<?php

namespace App\Http\Controllers\IPS;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $ipsId = auth()->user()->ips_id;
        
        $estadisticas = [
            'total_solicitudes' => SolicitudReferencia::where('ips_id', $ipsId)->count(),
            'pendientes' => SolicitudReferencia::where('ips_id', $ipsId)->where('estado', 'PENDIENTE')->count(),
            'aceptadas' => SolicitudReferencia::where('ips_id', $ipsId)->where('estado', 'ACEPTADA')->count(),
            'rechazadas' => SolicitudReferencia::where('ips_id', $ipsId)->where('estado', 'RECHAZADA')->count(),
            'tiempo_promedio_respuesta' => $this->calcularTiempoPromedio($ipsId)
        ];

        $solicitudesRecientes = SolicitudReferencia::with(['paciente'])
            ->where('ips_id', $ipsId)
            ->latest()
            ->take(5)
            ->get();

        $notificaciones = Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('IPS/Dashboard', [
            'estadisticas' => $estadisticas,
            'solicitudesRecientes' => $solicitudesRecientes,
            'notificaciones' => $notificaciones
        ]);
    }

    public function seguimiento()
    {
        $ipsId = auth()->user()->ips_id;
        
        $estadisticas = [
            'total_solicitudes' => SolicitudReferencia::where('ips_id', $ipsId)->count(),
            'pendientes' => SolicitudReferencia::where('ips_id', $ipsId)->where('estado', 'PENDIENTE')->count(),
            'aceptadas' => SolicitudReferencia::where('ips_id', $ipsId)->where('estado', 'ACEPTADA')->count(),
            'rechazadas' => SolicitudReferencia::where('ips_id', $ipsId)->where('estado', 'RECHAZADA')->count()
        ];

        return Inertia::render('IPS/Dashboard', [
            'estadisticas' => $estadisticas
        ]);
    }

    private function calcularTiempoPromedio($ipsId)
    {
        $solicitudesConRespuesta = SolicitudReferencia::where('ips_id', $ipsId)
            ->whereNotNull('fecha_respuesta')
            ->whereIn('estado', ['ACEPTADA', 'RECHAZADA'])
            ->get();

        if ($solicitudesConRespuesta->isEmpty()) {
            return 0;
        }

        $tiempoTotal = $solicitudesConRespuesta->sum(function($solicitud) {
            return $solicitud->fecha_solicitud->diffInHours($solicitud->fecha_respuesta);
        });

        return round($tiempoTotal / $solicitudesConRespuesta->count(), 1);
    }
}