<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventoAuditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = EventoAuditoria::with('usuario')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->usuario) {
            $query->whereHas('usuario', function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->usuario}%")
                  ->orWhere('email', 'LIKE', "%{$request->usuario}%");
            });
        }

        if ($request->accion) {
            $query->where('accion', 'LIKE', "%{$request->accion}%");
        }

        if ($request->nivel_riesgo) {
            $query->where('nivel_riesgo', $request->nivel_riesgo);
        }

        if ($request->fecha_inicio) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->fecha_fin) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        if ($request->ip) {
            $query->where('ip_address', 'LIKE', "%{$request->ip}%");
        }

        $eventos = $query->paginate(20);

        $estadisticas = [
            'total_eventos' => EventoAuditoria::count(),
            'eventos_hoy' => EventoAuditoria::hoy()->count(),
            'intentos_fallidos' => EventoAuditoria::intentosFallidos()->count(),
            'accesos_sospechosos' => EventoAuditoria::nivelRiesgo('ALTO')->orWhere('nivel_riesgo', 'CRITICO')->count(),
            'usuarios_activos' => User::whereDate('last_login_at', today())->count()
        ];

        $alertas_seguridad = EventoAuditoria::where('nivel_riesgo', 'CRITICO')
            ->orWhere('nivel_riesgo', 'ALTO')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($evento) {
                return [
                    'id' => $evento->id,
                    'tipo' => $evento->accion,
                    'mensaje' => "Actividad sospechosa detectada: {$evento->accion}",
                    'nivel' => $evento->nivel_riesgo,
                    'fecha' => $evento->created_at,
                    'resuelto' => false
                ];
            });

        return Inertia::render('admin/AuditoriaSeguridad', [
            'eventos' => $eventos,
            'estadisticas' => $estadisticas,
            'alertas_seguridad' => $alertas_seguridad
        ]);
    }

    public function exportar(Request $request)
    {
        // Lógica para exportar logs de auditoría
        return response()->json(['message' => 'Exportación iniciada']);
    }

    public function resolverAlerta(Request $request, $alertaId)
    {
        // Lógica para resolver alerta de seguridad
        return response()->json(['success' => true]);
    }
}