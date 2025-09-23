<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemMetrics;
use App\Models\CriticalAlert;
use App\Models\User;
use App\Models\SolicitudReferencia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupervisionController extends Controller
{
    public function index()
    {
        $metrics = [
            'sistema' => [
                'usuarios_activos' => User::where('active', true)->count(),
                'solicitudes_pendientes' => SolicitudReferencia::where('estado', 'pendiente')->count(),
                'alertas_criticas' => CriticalAlert::where('status', 'active')->count(),
                'tiempo_respuesta_promedio' => 2.3
            ],
            'rendimiento' => [
                'cpu_usage' => 45,
                'memory_usage' => 62,
                'disk_usage' => 78,
                'network_latency' => 12
            ]
        ];

        return Inertia::render('admin/supervision', [
            'metrics' => $metrics
        ]);
    }

    public function getMetrics()
    {
        return response()->json([
            'timestamp' => now(),
            'metrics' => SystemMetrics::latest()->first()
        ]);
    }
}