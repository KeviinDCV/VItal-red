<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\FeedbackMedico;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

class EvaluacionController extends Controller
{
    public function guardarEvaluacion(Request $request, $id)
    {
        $solicitud = SolicitudReferencia::findOrFail($id);
        
        $validated = $request->validate([
            'decision' => 'required|in:aceptada,rechazada',
            'justificacion' => 'required|string|max:1000',
            'especialista_asignado' => 'nullable|string|max:255',
            'fecha_cita' => 'nullable|date|after:today',
            'observaciones_medicas' => 'nullable|string|max:1000',
            'recomendaciones' => 'nullable|string|max:500',
            'urgencia_real' => 'required|in:alta,media,baja',
            'feedback_ia' => 'nullable|in:correcto,incorrecto'
        ]);

        // Crear decisión
        $decision = DecisionReferencia::create([
            'solicitud_referencia_id' => $solicitud->id,
            'decidido_por' => auth()->id(),
            'decision' => $validated['decision'],
            'justificacion' => $validated['justificacion'],
            'especialista_asignado' => $validated['especialista_asignado'],
            'fecha_cita' => $validated['fecha_cita'],
            'observaciones' => $validated['observaciones_medicas'],
            'recomendaciones' => $validated['recomendaciones']
        ]);

        // Actualizar solicitud
        $solicitud->update([
            'estado' => $validated['decision'] === 'aceptada' ? 'ACEPTADO' : 'NO_ADMITIDO',
            'procesado_por' => auth()->id(),
            'urgencia_real' => $validated['urgencia_real']
        ]);

        // Guardar feedback de IA si se proporciona
        if (isset($validated['feedback_ia'])) {
            FeedbackMedico::create([
                'solicitud_referencia_id' => $solicitud->id,
                'medico_id' => auth()->id(),
                'clasificacion_ia' => $solicitud->prioridad,
                'clasificacion_medico' => $validated['urgencia_real'],
                'feedback' => $validated['feedback_ia'],
                'comentarios' => $validated['observaciones_medicas']
            ]);
        }

        // Notificar al solicitante
        Notificacion::crearNotificacion(
            $solicitud->registroMedico->user_id,
            'evaluacion_completada',
            'Evaluación de solicitud completada',
            "Su solicitud ha sido {$validated['decision']}. {$validated['justificacion']}",
            [
                'solicitud_id' => $solicitud->id,
                'decision_id' => $decision->id,
                'fecha_cita' => $validated['fecha_cita']
            ],
            $validated['decision'] === 'aceptada' ? 'alta' : 'media'
        );

        return redirect()->route('medico.referencias')->with('success', 'Evaluación guardada correctamente');
    }

    public function misEvaluaciones(Request $request)
    {
        $filtros = $request->only(['estado', 'fecha_inicio', 'fecha_fin']);
        
        $query = DecisionReferencia::with(['solicitudReferencia.registroMedico.user.ips'])
            ->where('decidido_por', auth()->id());
            
        if (isset($filtros['estado']) && $filtros['estado']) {
            $query->whereHas('solicitudReferencia', function($q) use ($filtros) {
                $q->where('estado', $filtros['estado']);
            });
        }
        
        if (isset($filtros['fecha_inicio']) && $filtros['fecha_inicio']) {
            $query->whereDate('created_at', '>=', $filtros['fecha_inicio']);
        }
        
        if (isset($filtros['fecha_fin']) && $filtros['fecha_fin']) {
            $query->whereDate('created_at', '<=', $filtros['fecha_fin']);
        }

        $evaluaciones = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $estadisticas = [
            'total_evaluaciones' => DecisionReferencia::where('decidido_por', auth()->id())->count(),
            'aceptadas' => DecisionReferencia::where('decidido_por', auth()->id())
                ->where('decision', 'aceptada')->count(),
            'rechazadas' => DecisionReferencia::where('decidido_por', auth()->id())
                ->where('decision', 'rechazada')->count(),
            'tiempo_promedio' => $this->getTiempoPromedioEvaluacion()
        ];

        return Inertia::render('medico/MisEvaluaciones', [
            'evaluaciones' => $evaluaciones,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros
        ]);
    }

    public function cancelarEvaluacion($id)
    {
        $decision = DecisionReferencia::where('decidido_por', auth()->id())->findOrFail($id);
        $solicitud = $decision->solicitudReferencia;
        
        // Solo se puede cancelar si no ha pasado mucho tiempo
        if ($decision->created_at->diffInHours(now()) > 24) {
            return back()->withErrors(['error' => 'No se puede cancelar una evaluación después de 24 horas']);
        }

        // Revertir estado de la solicitud
        $solicitud->update([
            'estado' => 'PENDIENTE',
            'procesado_por' => null
        ]);

        // Eliminar decisión
        $decision->delete();

        // Notificar cancelación
        Notificacion::crearNotificacion(
            $solicitud->registroMedico->user_id,
            'evaluacion_cancelada',
            'Evaluación cancelada',
            'La evaluación de su solicitud ha sido cancelada y será reevaluada',
            ['solicitud_id' => $solicitud->id],
            'media'
        );

        return back()->with('success', 'Evaluación cancelada correctamente');
    }

    private function getTiempoPromedioEvaluacion()
    {
        return DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
            ->where('decisiones_referencia.decidido_por', auth()->id())
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as promedio')
            ->first()->promedio ?? 0;
    }

    /**
     * API: Evaluar solicitud
     */
    public function apiEvaluar(Request $request, $id): JsonResponse
    {
        $solicitud = SolicitudReferencia::findOrFail($id);
        
        $validated = $request->validate([
            'decision' => 'required|in:aceptada,rechazada',
            'justificacion' => 'required|string|max:1000',
        ]);

        $decision = DecisionReferencia::create([
            'solicitud_referencia_id' => $solicitud->id,
            'decidido_por' => auth()->id(),
            'decision' => $validated['decision'],
            'justificacion' => $validated['justificacion'],
        ]);

        $solicitud->update([
            'estado' => $validated['decision'] === 'aceptada' ? 'ACEPTADO' : 'NO_ADMITIDO',
            'procesado_por' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evaluación guardada correctamente',
            'decision' => $decision,
            'solicitud' => $solicitud
        ]);
    }
}