<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\RegistroMedico;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $medico = auth()->user();
        
        // Estadísticas principales
        $estadisticas = [
            'solicitudes_pendientes' => SolicitudReferencia::where('estado', 'PENDIENTE')->count(),
            'casos_criticos' => SolicitudReferencia::where('prioridad', 'ROJO')
                ->where('estado', 'PENDIENTE')->count(),
            'evaluaciones_hoy' => DecisionReferencia::where('decidido_por', $medico->id)
                ->whereDate('created_at', today())->count(),
            'tiempo_promedio_respuesta' => $this->getTiempoPromedioRespuesta($medico->id),
            'mis_pacientes' => RegistroMedico::where('user_id', $medico->id)->count()
        ];

        // Casos urgentes que requieren atención inmediata
        $casosUrgentes = SolicitudReferencia::with(['registroMedico', 'ips'])
            ->where('prioridad', 'ROJO')
            ->where('estado', 'PENDIENTE')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        // Actividad reciente
        $actividadReciente = DecisionReferencia::with(['solicitudReferencia.registroMedico'])
            ->where('decidido_por', $medico->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Métricas de rendimiento
        $rendimiento = [
            'casos_por_dia' => $this->getCasosPorDia($medico->id),
            'tasa_aceptacion' => $this->getTasaAceptacion($medico->id),
            'especialidades_atendidas' => $this->getEspecialidadesAtendidas($medico->id),
            'feedback_ia' => $this->getFeedbackIA($medico->id)
        ];

        // Notificaciones no leídas
        $notificaciones = Notificacion::where('user_id', $medico->id)
            ->where('leida', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('medico/Dashboard', [
            'estadisticas' => $estadisticas,
            'casosUrgentes' => $casosUrgentes,
            'actividadReciente' => $actividadReciente,
            'rendimiento' => $rendimiento,
            'notificaciones' => $notificaciones
        ]);
    }

    public function getMetricas(Request $request)
    {
        $periodo = $request->get('periodo', 30);
        $medico = auth()->user();

        return response()->json([
            'solicitudes_por_dia' => $this->getSolicitudesPorDia($medico->id, $periodo),
            'tiempo_respuesta_trend' => $this->getTiempoRespuestaTrend($medico->id, $periodo),
            'distribucion_decisiones' => $this->getDistribucionDecisiones($medico->id, $periodo),
            'casos_por_especialidad' => $this->getCasosPorEspecialidad($medico->id, $periodo)
        ]);
    }

    private function getTiempoPromedioRespuesta($medicoId)
    {
        return DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
            ->where('decisiones_referencia.decidido_por', $medicoId)
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as promedio')
            ->first()->promedio ?? 0;
    }

    private function getCasosPorDia($medicoId)
    {
        return DecisionReferencia::where('decidido_por', $medicoId)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getTasaAceptacion($medicoId)
    {
        $total = DecisionReferencia::where('decidido_por', $medicoId)->count();
        $aceptadas = DecisionReferencia::where('decidido_por', $medicoId)
            ->where('decision', 'aceptada')->count();
            
        return $total > 0 ? ($aceptadas / $total) * 100 : 0;
    }

    private function getEspecialidadesAtendidas($medicoId)
    {
        return DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
            ->join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
            ->where('decisiones_referencia.decidido_por', $medicoId)
            ->selectRaw('registros_medicos.especialidad_solicitada, COUNT(*) as total')
            ->groupBy('registros_medicos.especialidad_solicitada')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
    }

    private function getFeedbackIA($medicoId)
    {
        $feedback = \App\Models\FeedbackMedico::where('medico_id', $medicoId)->get();
        
        return [
            'total' => $feedback->count(),
            'correctos' => $feedback->where('feedback', 'correcto')->count(),
            'incorrectos' => $feedback->where('feedback', 'incorrecto')->count(),
            'precision_ia' => $feedback->count() > 0 ? 
                ($feedback->where('feedback', 'correcto')->count() / $feedback->count()) * 100 : 0
        ];
    }

    private function getSolicitudesPorDia($medicoId, $periodo)
    {
        return DecisionReferencia::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->where('decidido_por', $medicoId)
            ->where('created_at', '>=', now()->subDays($periodo))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    private function getTiempoRespuestaTrend($medicoId, $periodo)
    {
        return DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
            ->selectRaw('DATE(decisiones_referencia.created_at) as fecha, 
                AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as tiempo_promedio')
            ->where('decisiones_referencia.decidido_por', $medicoId)
            ->where('decisiones_referencia.created_at', '>=', now()->subDays($periodo))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    private function getDistribucionDecisiones($medicoId, $periodo)
    {
        return DecisionReferencia::selectRaw('decision, COUNT(*) as total')
            ->where('decidido_por', $medicoId)
            ->where('created_at', '>=', now()->subDays($periodo))
            ->groupBy('decision')
            ->get();
    }

    private function getCasosPorEspecialidad($medicoId, $periodo)
    {
        return DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
            ->join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
            ->selectRaw('registros_medicos.especialidad_solicitada, COUNT(*) as total')
            ->where('decisiones_referencia.decidido_por', $medicoId)
            ->where('decisiones_referencia.created_at', '>=', now()->subDays($periodo))
            ->groupBy('registros_medicos.especialidad_solicitada')
            ->orderBy('total', 'desc')
            ->get();
    }
}