<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CasosCriticosController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitudReferencia::with(['paciente', 'ips'])
            ->where('prioridad', 'ROJO')
            ->where('estado', 'PENDIENTE');

        // Filtros
        if ($request->especialidad) {
            $query->where('especialidad_solicitada', 'LIKE', "%{$request->especialidad}%");
        }

        if ($request->tiempo_limite) {
            $horas = (int) $request->tiempo_limite;
            $query->where('fecha_solicitud', '<=', now()->subHours($horas));
        }

        $casosCriticos = $query->orderBy('fecha_solicitud', 'asc')
            ->paginate(20)
            ->through(function ($solicitud) {
                return [
                    'id' => $solicitud->id,
                    'codigo_solicitud' => $solicitud->codigo_solicitud,
                    'prioridad' => $solicitud->prioridad,
                    'estado' => $solicitud->estado,
                    'fecha_solicitud' => $solicitud->fecha_solicitud->toISOString(),
                    'tiempo_transcurrido' => $solicitud->fecha_solicitud->diffInHours(now(), true),
                    'registro_medico' => [
                        'nombre' => $solicitud->paciente->nombre ?? 'N/A',
                        'apellidos' => $solicitud->paciente->apellidos ?? 'N/A',
                        'numero_identificacion' => $solicitud->paciente->numero_identificacion ?? 'N/A',
                        'edad' => $solicitud->paciente->edad ?? 0,
                        'especialidad_solicitada' => $solicitud->especialidad_solicitada,
                        'motivo_consulta' => $solicitud->motivo_consulta,
                        'diagnostico_principal' => $solicitud->diagnostico_principal,
                        'signos_vitales' => $solicitud->signos_vitales,
                        'sintomas_alarma' => $solicitud->sintomas_alarma ?? []
                    ],
                    'ips' => [
                        'nombre' => $solicitud->ips->nombre ?? 'N/A',
                        'telefono' => $solicitud->ips->telefono ?? 'N/A',
                        'email' => $solicitud->ips->email ?? 'N/A'
                    ],
                    'puntuacion_ia' => $solicitud->puntuacion_ia ?? 0,
                    'observaciones_ia' => $solicitud->observaciones_ia ?? 'Sin observaciones'
                ];
            });

        return Inertia::render('medico/CasosCriticos', [
            'casosCriticos' => $casosCriticos
        ]);
    }

    public function procesar(Request $request, $casoId)
    {
        $request->validate([
            'decision' => 'required|in:ACEPTAR,RECHAZAR',
            'observaciones' => 'nullable|string|max:1000',
            'tiempo_estimado' => 'nullable|integer|min:1|max:168'
        ]);

        $solicitud = SolicitudReferencia::findOrFail($casoId);

        if ($solicitud->estado !== 'PENDIENTE') {
            return response()->json(['error' => 'Esta solicitud ya fue procesada'], 400);
        }

        $solicitud->update([
            'estado' => $request->decision === 'ACEPTAR' ? 'ACEPTADA' : 'RECHAZADA',
            'fecha_respuesta' => now(),
            'observaciones_medico' => $request->observaciones,
            'tiempo_estimado_atencion' => $request->tiempo_estimado
        ]);

        // Crear decisión
        \App\Models\DecisionReferencia::create([
            'solicitud_referencia_id' => $solicitud->id,
            'medico_id' => auth()->id(),
            'decision' => $request->decision,
            'observaciones' => $request->observaciones,
            'tiempo_estimado' => $request->tiempo_estimado
        ]);

        // Crear notificación para la IPS
        \App\Models\Notificacion::create([
            'user_id' => $solicitud->ips->user_id ?? null,
            'tipo' => 'DECISION_REFERENCIA',
            'titulo' => 'Decisión sobre referencia crítica',
            'mensaje' => "Su solicitud {$solicitud->codigo_solicitud} ha sido " . 
                        ($request->decision === 'ACEPTAR' ? 'aceptada' : 'rechazada'),
            'datos' => [
                'solicitud_id' => $solicitud->id,
                'decision' => $request->decision
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Caso crítico procesado exitosamente'
        ]);
    }
}