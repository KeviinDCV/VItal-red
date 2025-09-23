<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TablaGestionController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitudReferencia::with(['paciente', 'ips', 'medicoEvaluador']);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Filtro por rol
        $user = auth()->user();
        if ($user->role === 'medico') {
            $query->where('medico_evaluador_id', $user->id);
        } elseif ($user->role === 'ips') {
            $query->where('ips_id', $user->ips_id);
        }

        $solicitudes = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Shared/TablaGestion', [
            'solicitudes' => $solicitudes,
            'filtros' => $request->only(['estado', 'prioridad', 'fecha_desde', 'fecha_hasta'])
        ]);
    }

    public function exportar(Request $request)
    {
        // Lógica de exportación
        return response()->json(['success' => true]);
    }
}