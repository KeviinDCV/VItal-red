<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\CriticalAlert;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CasosCriticosController extends Controller
{
    public function index()
    {
        $casosCriticos = SolicitudReferencia::with(['paciente', 'ips'])
            ->where('prioridad', 'ROJO')
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('medico/CasosCriticos', [
            'casosCriticos' => $casosCriticos
        ]);
    }

    public function procesar(Request $request, $id)
    {
        $solicitud = SolicitudReferencia::findOrFail($id);
        
        $request->validate([
            'decision' => 'required|in:aceptar,rechazar,solicitar_info',
            'observaciones' => 'required|string|max:1000',
            'tiempo_estimado' => 'nullable|integer|min:1'
        ]);

        $solicitud->update([
            'estado' => $request->decision === 'aceptar' ? 'aceptada' : 'rechazada',
            'observaciones_medico' => $request->observaciones,
            'tiempo_estimado_atencion' => $request->tiempo_estimado,
            'medico_evaluador_id' => auth()->id(),
            'fecha_evaluacion' => now()
        ]);

        // Crear alerta si es necesario
        if ($request->decision === 'aceptar' && $solicitud->prioridad === 'ROJO') {
            CriticalAlert::create([
                'title' => 'Caso crítico aceptado',
                'message' => "Solicitud {$solicitud->codigo_solicitud} aceptada para atención inmediata",
                'priority' => 'high',
                'assigned_to' => auth()->id(),
                'alertable_type' => SolicitudReferencia::class,
                'alertable_id' => $solicitud->id
            ]);
        }

        return response()->json(['success' => true]);
    }
}