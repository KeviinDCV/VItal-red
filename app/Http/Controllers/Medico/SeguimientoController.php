<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\SeguimientoPaciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeguimientoController extends Controller
{
    public function index()
    {
        $pacientes = SolicitudReferencia::with(['registroMedico', 'seguimiento'])
            ->where('estado', 'ACEPTADO')
            ->whereHas('decision', function($query) {
                $query->where('decidido_por', auth()->id());
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return Inertia::render('medico/SeguimientoPacientes', [
            'pacientes' => $pacientes
        ]);
    }

    public function actualizar(Request $request, SolicitudReferencia $solicitud)
    {
        $request->validate([
            'estado_seguimiento' => 'required|in:programado,ingresado,no_ingreso,completado',
            'fecha_ingreso' => 'nullable|date',
            'observaciones' => 'nullable|string|max:1000',
            'diagnostico_final' => 'nullable|string|max:500',
            'tratamiento_realizado' => 'nullable|string|max:500'
        ]);

        SeguimientoPaciente::updateOrCreate(
            ['solicitud_referencia_id' => $solicitud->id],
            [
                'medico_seguimiento_id' => auth()->id(),
                'estado' => $request->estado_seguimiento,
                'fecha_ingreso' => $request->fecha_ingreso,
                'observaciones' => $request->observaciones,
                'diagnostico_final' => $request->diagnostico_final,
                'tratamiento_realizado' => $request->tratamiento_realizado,
                'fecha_actualizacion' => now()
            ]
        );

        return back()->with('success', 'Seguimiento actualizado correctamente');
    }

    public function contrarreferencia(Request $request, SolicitudReferencia $solicitud)
    {
        $request->validate([
            'diagnostico' => 'required|string|max:1000',
            'tratamiento' => 'required|string|max:1000',
            'recomendaciones' => 'required|string|max:1000',
            'medicamentos' => 'nullable|string|max:500',
            'proxima_cita' => 'nullable|date|after:today'
        ]);

        $seguimiento = SeguimientoPaciente::where('solicitud_referencia_id', $solicitud->id)->first();
        
        if ($seguimiento) {
            $seguimiento->update([
                'contrarreferencia' => [
                    'diagnostico' => $request->diagnostico,
                    'tratamiento' => $request->tratamiento,
                    'recomendaciones' => $request->recomendaciones,
                    'medicamentos' => $request->medicamentos,
                    'proxima_cita' => $request->proxima_cita,
                    'fecha_contrarreferencia' => now()
                ],
                'estado' => 'completado'
            ]);
        }

        return back()->with('success', 'Contrarreferencia generada correctamente');
    }
}