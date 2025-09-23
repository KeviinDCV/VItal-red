<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferenciasController extends Controller
{
    public function dashboard()
    {
        $estadisticas = [
            'pendientes' => SolicitudReferencia::where('estado', 'PENDIENTE')->count(),
            'aceptadas' => SolicitudReferencia::where('estado', 'ACEPTADO')->count(),
            'rechazadas' => SolicitudReferencia::where('estado', 'NO_ADMITIDO')->count(),
            'rojas' => SolicitudReferencia::where('prioridad', 'ROJO')->count(),
            'verdes' => SolicitudReferencia::where('prioridad', 'VERDE')->count()
        ];

        $solicitudes = SolicitudReferencia::with(['registroMedico', 'decision'])
            ->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/DashboardReferencias', [
            'estadisticas' => $estadisticas,
            'solicitudes' => $solicitudes
        ]);
    }

    public function decidir(Request $request, SolicitudReferencia $solicitud)
    {
        $request->validate([
            'decision' => 'required|in:aceptada,rechazada',
            'justificacion' => 'required|string|max:1000',
            'especialista_asignado' => 'nullable|string|max:255'
        ]);

        $decision = DecisionReferencia::create([
            'solicitud_referencia_id' => $solicitud->id,
            'decidido_por' => auth()->id(),
            'decision' => $request->decision,
            'justificacion' => $request->justificacion,
            'especialista_asignado' => $request->especialista_asignado
        ]);

        $solicitud->update([
            'estado' => $request->decision === 'aceptada' ? 'ACEPTADO' : 'NO_ADMITIDO',
            'procesado_por' => auth()->id()
        ]);

        // Crear notificación
        Notificacion::crearNotificacion(
            $solicitud->registroMedico->user_id,
            'decision_tomada',
            'Decisión sobre solicitud de referencia',
            "Su solicitud ha sido {$request->decision}. {$request->justificacion}",
            ['solicitud_id' => $solicitud->id],
            $request->decision === 'aceptada' ? 'alta' : 'media'
        );

        return back()->with('success', 'Decisión registrada correctamente');
    }

    public function estadisticas()
    {
        $stats = [
            'por_mes' => SolicitudReferencia::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                ->whereYear('created_at', date('Y'))
                ->groupBy('mes')
                ->get(),
            'por_prioridad' => SolicitudReferencia::selectRaw('prioridad, COUNT(*) as total')
                ->groupBy('prioridad')
                ->get(),
            'tiempo_respuesta' => DecisionReferencia::selectRaw('AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as promedio')
                ->join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
                ->first()
        ];

        return response()->json($stats);
    }
}