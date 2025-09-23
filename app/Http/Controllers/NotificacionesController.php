<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificacionesController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only(['tipo', 'leida', 'prioridad']);
        
        $query = Notificacion::where('user_id', auth()->id());
        
        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }
        if (isset($filtros['leida']) && $filtros['leida'] !== '') {
            $query->where('leida', $filtros['leida']);
        }
        if (!empty($filtros['prioridad'])) {
            $query->where('prioridad', $filtros['prioridad']);
        }

        $notificaciones = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $estadisticas = [
            'no_leidas' => Notificacion::where('user_id', auth()->id())->noLeidas()->count(),
            'total' => Notificacion::where('user_id', auth()->id())->count()
        ];

        return Inertia::render('Shared/Notificaciones', [
            'notificaciones' => $notificaciones,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros
        ]);
    }

    public function marcarLeida(Notificacion $notificacion)
    {
        if ($notificacion->user_id !== auth()->id()) {
            abort(403);
        }

        $notificacion->marcarComoLeida();

        return response()->json(['success' => true]);
    }

    public function marcarTodasLeidas()
    {
        Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_en' => now()
            ]);

        return back()->with('success', 'Todas las notificaciones marcadas como leídas');
    }

    public function configurar(Request $request)
    {
        $request->validate([
            'tipos_habilitados' => 'required|array',
            'tipos_habilitados.*' => 'string',
            'sonido_habilitado' => 'boolean',
            'email_habilitado' => 'boolean'
        ]);

        // Guardar configuración en sesión o base de datos
        session([
            'notificaciones_config' => [
                'tipos_habilitados' => $request->tipos_habilitados,
                'sonido_habilitado' => $request->sonido_habilitado ?? false,
                'email_habilitado' => $request->email_habilitado ?? false
            ]
        ]);

        return back()->with('success', 'Configuración de notificaciones actualizada');
    }

    public function obtenerRecientes()
    {
        $notificaciones = Notificacion::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'notificaciones' => $notificaciones,
            'no_leidas' => $notificaciones->where('leida', false)->count()
        ]);
    }
}