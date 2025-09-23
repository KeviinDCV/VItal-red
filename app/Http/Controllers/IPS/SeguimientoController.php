<?php

namespace App\Http\Controllers\IPS;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeguimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitudReferencia::with(['paciente', 'ips'])
            ->where('ips_id', auth()->user()->ips_id);

        // Filtros
        if ($request->busqueda) {
            $query->where(function($q) use ($request) {
                $q->where('codigo_solicitud', 'LIKE', "%{$request->busqueda}%")
                  ->orWhereHas('paciente', function($subQ) use ($request) {
                      $subQ->where('nombre', 'LIKE', "%{$request->busqueda}%")
                           ->orWhere('apellidos', 'LIKE', "%{$request->busqueda}%");
                  });
            });
        }

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->prioridad) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->especialidad) {
            $query->where('especialidad_solicitada', 'LIKE', "%{$request->especialidad}%");
        }

        $solicitudes = $query->orderBy('fecha_solicitud', 'desc')->paginate(20);

        $estadisticas = [
            'total' => SolicitudReferencia::where('ips_id', auth()->user()->ips_id)->count(),
            'pendientes' => SolicitudReferencia::where('ips_id', auth()->user()->ips_id)
                ->where('estado', 'PENDIENTE')->count(),
            'aceptadas' => SolicitudReferencia::where('ips_id', auth()->user()->ips_id)
                ->where('estado', 'ACEPTADA')->count(),
            'rechazadas' => SolicitudReferencia::where('ips_id', auth()->user()->ips_id)
                ->where('estado', 'RECHAZADA')->count(),
            'tiempo_promedio' => $this->calcularTiempoPromedio()
        ];

        return Inertia::render('IPS/SeguimientoSolicitudes', [
            'solicitudes' => $solicitudes,
            'estadisticas' => $estadisticas
        ]);
    }

    public function show(SolicitudReferencia $solicitud)
    {
        // Verificar que la solicitud pertenece a la IPS del usuario
        if ($solicitud->ips_id !== auth()->user()->ips_id) {
            abort(403);
        }

        $solicitud->load(['paciente', 'ips', 'decisiones.medico']);

        return Inertia::render('IPS/DetalleSolicitud', [
            'solicitud' => $solicitud
        ]);
    }

    public function contactarMedico(Request $request, SolicitudReferencia $solicitud)
    {
        $request->validate([
            'mensaje' => 'required|string|max:1000'
        ]);

        // Verificar que la solicitud pertenece a la IPS del usuario
        if ($solicitud->ips_id !== auth()->user()->ips_id) {
            abort(403);
        }

        // Lógica para enviar mensaje al médico
        // Esto podría crear una notificación o enviar un email

        return response()->json(['success' => true, 'message' => 'Mensaje enviado al médico']);
    }

    public function cancelarSolicitud(Request $request, SolicitudReferencia $solicitud)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:500'
        ]);

        // Verificar que la solicitud pertenece a la IPS del usuario
        if ($solicitud->ips_id !== auth()->user()->ips_id) {
            abort(403);
        }

        // Solo se puede cancelar si está pendiente
        if ($solicitud->estado !== 'PENDIENTE') {
            return response()->json(['error' => 'No se puede cancelar una solicitud que ya fue procesada'], 400);
        }

        $solicitud->update([
            'estado' => 'CANCELADA',
            'observaciones' => $request->motivo_cancelacion,
            'fecha_respuesta' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Solicitud cancelada exitosamente']);
    }

    public function exportarReporte(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'formato' => 'required|in:pdf,excel'
        ]);

        // Lógica para generar reporte de seguimiento
        return response()->json(['message' => 'Reporte generado exitosamente']);
    }

    private function calcularTiempoPromedio()
    {
        $solicitudesConRespuesta = SolicitudReferencia::where('ips_id', auth()->user()->ips_id)
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