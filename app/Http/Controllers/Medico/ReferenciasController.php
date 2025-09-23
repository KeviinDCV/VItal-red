<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferenciasController extends Controller
{
    public function gestionar(Request $request)
    {
        $filtros = $request->only(['estado', 'prioridad', 'especialidad']);
        
        $query = SolicitudReferencia::with(['registroMedico', 'decision'])
            ->where('estado', 'PENDIENTE');
        
        if (isset($filtros['estado']) && $filtros['estado']) {
            $query->where('estado', $filtros['estado']);
        }
        if (isset($filtros['prioridad']) && $filtros['prioridad']) {
            $query->where('prioridad', $filtros['prioridad']);
        }
        if (isset($filtros['especialidad']) && $filtros['especialidad']) {
            $query->whereHas('registroMedico', function($q) use ($filtros) {
                $q->where('especialidad_solicitada', $filtros['especialidad']);
            });
        }

        $solicitudes = $query->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return Inertia::render('medico/GestionarReferencias', [
            'solicitudes' => $solicitudes,
            'filtros' => $filtros
        ]);
    }

    public function detalle(SolicitudReferencia $solicitud)
    {
        $solicitud->load(['registroMedico.paciente', 'ips', 'decision.decididoPor']);
        
        return Inertia::render('Medico/DetalleSolicitud', [
            'solicitud' => $solicitud
        ]);
    }

    public function procesar(Request $request, SolicitudReferencia $solicitud)
    {
        $request->validate([
            'decision' => 'required|in:aceptada,rechazada',
            'justificacion' => 'required|string|max:1000',
            'especialista_asignado' => 'nullable|string|max:255',
            'fecha_cita' => 'nullable|date|after:today',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $decision = DecisionReferencia::create([
            'solicitud_referencia_id' => $solicitud->id,
            'decidido_por' => auth()->id(),
            'decision' => $request->decision,
            'justificacion' => $request->justificacion,
            'especialista_asignado' => $request->especialista_asignado,
            'fecha_cita' => $request->fecha_cita,
            'observaciones' => $request->observaciones
        ]);

        $solicitud->update([
            'estado' => $request->decision === 'aceptada' ? 'ACEPTADO' : 'NO_ADMITIDO',
            'procesado_por' => auth()->id()
        ]);

        // Notificar al usuario que creó el registro médico
        Notificacion::crearNotificacion(
            $solicitud->registroMedico->user_id,
            'decision_tomada',
            'Decisión sobre solicitud de referencia',
            "Su solicitud para {$solicitud->registroMedico->nombre} {$solicitud->registroMedico->apellidos} ha sido {$request->decision}",
            ['solicitud_id' => $solicitud->id],
            $request->decision === 'aceptada' ? 'alta' : 'media'
        );

        return back()->with('success', 'Decisión procesada correctamente');
    }
}