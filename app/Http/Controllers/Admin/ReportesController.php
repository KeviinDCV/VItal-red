<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReferenciasExport;

class ReportesController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only(['fecha_inicio', 'fecha_fin', 'estado', 'prioridad']);
        
        $query = SolicitudReferencia::with(['registroMedico.paciente', 'ips', 'decision']);
        
        if ($filtros['fecha_inicio']) {
            $query->whereDate('created_at', '>=', $filtros['fecha_inicio']);
        }
        if ($filtros['fecha_fin']) {
            $query->whereDate('created_at', '<=', $filtros['fecha_fin']);
        }
        if ($filtros['estado']) {
            $query->where('estado', $filtros['estado']);
        }
        if ($filtros['prioridad']) {
            $query->where('prioridad', $filtros['prioridad']);
        }

        $reportes = $query->paginate(50);
        
        return Inertia::render('Admin/Reportes', [
            'reportes' => $reportes,
            'filtros' => $filtros
        ]);
    }

    public function exportarExcel(Request $request)
    {
        $filtros = $request->only(['fecha_inicio', 'fecha_fin', 'estado', 'prioridad']);
        
        return Excel::download(new ReferenciasExport($filtros), 'referencias_' . date('Y-m-d') . '.xlsx');
    }

    public function graficos(Request $request)
    {
        $periodo = $request->get('periodo', '30'); // días
        
        $data = [
            'solicitudes_por_dia' => SolicitudReferencia::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays($periodo))
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get(),
            
            'por_prioridad' => SolicitudReferencia::selectRaw('prioridad, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays($periodo))
                ->groupBy('prioridad')
                ->get(),
                
            'tiempo_respuesta' => DecisionReferencia::selectRaw('DATE(decisiones_referencia.created_at) as fecha, AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as promedio')
                ->join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
                ->where('decisiones_referencia.created_at', '>=', now()->subDays($periodo))
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get()
        ];

        return response()->json($data);
    }
}