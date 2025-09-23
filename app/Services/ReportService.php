<?php

namespace App\Services;

use App\Models\SolicitudReferencia;
use App\Models\User;
use App\Models\EventoAuditoria;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    public function generateDailyReport($date = null)
    {
        $date = $date ?: Carbon::today();
        
        return [
            'fecha' => $date->format('Y-m-d'),
            'solicitudes_total' => SolicitudReferencia::whereDate('created_at', $date)->count(),
            'solicitudes_rojas' => SolicitudReferencia::whereDate('created_at', $date)->where('prioridad', 'ROJO')->count(),
            'solicitudes_procesadas' => SolicitudReferencia::whereDate('updated_at', $date)->where('estado', '!=', 'PENDIENTE')->count(),
            'usuarios_activos' => User::whereDate('updated_at', $date)->count(),
            'eventos_auditoria' => EventoAuditoria::whereDate('created_at', $date)->count()
        ];
    }

    public function generateWeeklyReport($startDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        return [
            'periodo' => $startDate->format('Y-m-d') . ' - ' . $endDate->format('Y-m-d'),
            'solicitudes_por_dia' => SolicitudReferencia::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
                ->groupBy('fecha')
                ->get(),
            'rendimiento_medicos' => DB::table('solicitudes_referencia')
                ->join('decisiones_referencia', 'solicitudes_referencia.id', '=', 'decisiones_referencia.solicitud_id')
                ->whereBetween('decisiones_referencia.created_at', [$startDate, $endDate])
                ->selectRaw('medico_id, COUNT(*) as decisiones_tomadas')
                ->groupBy('medico_id')
                ->get()
        ];
    }

    public function exportToCSV($data, $filename)
    {
        $csv = fopen('php://temp', 'w+');
        
        if (!empty($data)) {
            fputcsv($csv, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($csv, $row);
            }
        }
        
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}