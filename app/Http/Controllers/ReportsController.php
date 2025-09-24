<?php

namespace App\Http\Controllers;

use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        $stats = [
            'total_solicitudes' => SolicitudReferencia::count(),
            'pendientes' => SolicitudReferencia::where('estado', 'PENDIENTE')->count(),
            'procesadas_hoy' => SolicitudReferencia::whereDate('updated_at', today())->count(),
            'tiempo_promedio' => $this->getTiempoPromedioRespuesta()
        ];

        return Inertia::render('admin/Reports', [
            'estadisticas' => $stats
        ]);
    }

    public function medicalRequests(Request $request)
    {
        $periodo = $request->get('periodo', 30);
        
        $data = [
            'solicitudes_por_dia' => SolicitudReferencia::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays($periodo))
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get(),
            
            'por_especialidad' => SolicitudReferencia::join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
                ->selectRaw('registros_medicos.especialidad_solicitada, COUNT(*) as total')
                ->where('solicitudes_referencia.created_at', '>=', now()->subDays($periodo))
                ->groupBy('registros_medicos.especialidad_solicitada')
                ->orderBy('total', 'desc')
                ->get(),
                
            'por_prioridad' => SolicitudReferencia::selectRaw('prioridad, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays($periodo))
                ->groupBy('prioridad')
                ->get(),
                
            'tasa_aceptacion' => $this->getTasaAceptacion($periodo)
        ];

        return response()->json($data);
    }

    public function performance(Request $request)
    {
        $periodo = $request->get('periodo', 30);
        
        $data = [
            'tiempo_respuesta_promedio' => $this->getTiempoPromedioRespuesta($periodo),
            'solicitudes_por_medico' => $this->getSolicitudesPorMedico($periodo),
            'eficiencia_por_dia' => $this->getEficienciaPorDia($periodo),
            'casos_criticos_tiempo' => $this->getCasosCriticosTiempo($periodo)
        ];

        return response()->json($data);
    }

    public function audit(Request $request)
    {
        $periodo = $request->get('periodo', 30);
        
        $data = [
            'decisiones_por_usuario' => DecisionReferencia::join('users', 'decisiones_referencia.decidido_por', '=', 'users.id')
                ->selectRaw('users.name, COUNT(*) as total')
                ->where('decisiones_referencia.created_at', '>=', now()->subDays($periodo))
                ->groupBy('users.id', 'users.name')
                ->orderBy('total', 'desc')
                ->get(),
                
            'cambios_estado' => SolicitudReferencia::selectRaw('estado, COUNT(*) as total')
                ->where('updated_at', '>=', now()->subDays($periodo))
                ->groupBy('estado')
                ->get(),
                
            'actividad_por_hora' => SolicitudReferencia::selectRaw('HOUR(created_at) as hora, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays($periodo))
                ->groupBy('hora')
                ->orderBy('hora')
                ->get()
        ];

        return response()->json($data);
    }

    private function getTiempoPromedioRespuesta($periodo = null)
    {
        $query = DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as promedio');
            
        if ($periodo) {
            $query->where('decisiones_referencia.created_at', '>=', now()->subDays($periodo));
        }
        
        return $query->first()->promedio ?? 0;
    }

    private function getTasaAceptacion($periodo)
    {
        $total = SolicitudReferencia::where('created_at', '>=', now()->subDays($periodo))->count();
        $aceptadas = SolicitudReferencia::where('estado', 'ACEPTADO')
            ->where('created_at', '>=', now()->subDays($periodo))
            ->count();
            
        return $total > 0 ? ($aceptadas / $total) * 100 : 0;
    }

    private function getSolicitudesPorMedico($periodo)
    {
        return DecisionReferencia::join('users', 'decisiones_referencia.decidido_por', '=', 'users.id')
            ->selectRaw('users.name, COUNT(*) as total')
            ->where('decisiones_referencia.created_at', '>=', now()->subDays($periodo))
            ->groupBy('users.id', 'users.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
    }

    private function getEficienciaPorDia($periodo)
    {
        return SolicitudReferencia::selectRaw('DATE(created_at) as fecha, 
            COUNT(*) as total,
            SUM(CASE WHEN estado != "PENDIENTE" THEN 1 ELSE 0 END) as procesadas')
            ->where('created_at', '>=', now()->subDays($periodo))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(function($item) {
                $item->eficiencia = $item->total > 0 ? ($item->procesadas / $item->total) * 100 : 0;
                return $item;
            });
    }

    private function getCasosCriticosTiempo($periodo)
    {
        return SolicitudReferencia::join('decisiones_referencia', 'solicitudes_referencia.id', '=', 'decisiones_referencia.solicitud_referencia_id')
            ->selectRaw('solicitudes_referencia.prioridad, 
                AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as tiempo_promedio')
            ->where('solicitudes_referencia.created_at', '>=', now()->subDays($periodo))
            ->groupBy('solicitudes_referencia.prioridad')
            ->get();
    }
}