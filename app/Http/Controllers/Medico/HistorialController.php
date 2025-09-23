<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\HistorialPaciente;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $query = HistorialPaciente::with('paciente');

        // Filtros
        if ($request->busqueda) {
            $query->whereHas('paciente', function($q) use ($request) {
                $q->where('nombre', 'LIKE', "%{$request->busqueda}%")
                  ->orWhere('apellidos', 'LIKE', "%{$request->busqueda}%")
                  ->orWhere('numero_identificacion', 'LIKE', "%{$request->busqueda}%");
            });
        }

        if ($request->especialidad) {
            $query->whereJsonContains('consultas', [['especialidad' => $request->especialidad]]);
        }

        if ($request->periodo) {
            switch ($request->periodo) {
                case 'ultima_semana':
                    $query->where('ultima_consulta', '>=', now()->subWeek());
                    break;
                case 'ultimo_mes':
                    $query->where('ultima_consulta', '>=', now()->subMonth());
                    break;
                case 'ultimos_3_meses':
                    $query->where('ultima_consulta', '>=', now()->subMonths(3));
                    break;
                case 'ultimo_ano':
                    $query->where('ultima_consulta', '>=', now()->subYear());
                    break;
            }
        }

        if ($request->estado) {
            if ($request->estado === 'activo') {
                $query->whereJsonContains('consultas', [['estado' => 'ACTIVO']]);
            } elseif ($request->estado === 'completado') {
                $query->whereJsonContains('consultas', [['estado' => 'COMPLETADO']]);
            } elseif ($request->estado === 'seguimiento') {
                $query->whereJsonContains('referencias', [['estado' => 'ACEPTADA']]);
            }
        }

        $historiales = $query->orderBy('ultima_consulta', 'desc')->paginate(20);

        $estadisticas = [
            'total_pacientes' => HistorialPaciente::count(),
            'consultas_mes' => HistorialPaciente::whereMonth('ultima_consulta', now()->month)->count(),
            'referencias_mes' => HistorialPaciente::whereJsonLength('referencias', '>', 0)
                ->whereMonth('updated_at', now()->month)->count(),
            'pacientes_activos' => HistorialPaciente::conConsultasRecientes(30)->count()
        ];

        return Inertia::render('medico/HistorialPacientes', [
            'historiales' => $historiales,
            'estadisticas' => $estadisticas
        ]);
    }

    public function show(HistorialPaciente $historial)
    {
        $historial->load('paciente');
        
        return Inertia::render('medico/DetalleHistorial', [
            'historial' => $historial
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'consulta' => 'required|array',
            'consulta.tipo' => 'required|in:CONSULTA,REFERENCIA,SEGUIMIENTO,URGENCIA',
            'consulta.especialidad' => 'required|string',
            'consulta.diagnostico' => 'required|string',
            'consulta.tratamiento' => 'required|string'
        ]);

        $historial = HistorialPaciente::firstOrCreate(
            ['paciente_id' => $request->paciente_id],
            [
                'consultas' => [],
                'referencias' => [],
                'total_consultas' => 0,
                'total_referencias' => 0
            ]
        );

        $nuevaConsulta = array_merge($request->consulta, [
            'id' => uniqid(),
            'fecha' => now()->toISOString(),
            'estado' => 'ACTIVO',
            'medico' => auth()->user()->name
        ]);

        $consultas = $historial->consultas ?? [];
        $consultas[] = $nuevaConsulta;

        $historial->update([
            'consultas' => $consultas,
            'ultima_consulta' => now(),
            'total_consultas' => count($consultas)
        ]);

        return redirect()->back()->with('success', 'Consulta agregada al historial');
    }

    public function agregarReferencia(Request $request, HistorialPaciente $historial)
    {
        $request->validate([
            'especialidad_origen' => 'required|string',
            'especialidad_destino' => 'required|string',
            'motivo' => 'required|string',
            'prioridad' => 'required|in:ROJO,AMARILLO,VERDE'
        ]);

        $nuevaReferencia = array_merge($request->all(), [
            'id' => uniqid(),
            'fecha' => now()->toISOString(),
            'estado' => 'PENDIENTE'
        ]);

        $referencias = $historial->referencias ?? [];
        $referencias[] = $nuevaReferencia;

        $historial->update([
            'referencias' => $referencias,
            'total_referencias' => count($referencias)
        ]);

        return response()->json(['success' => true]);
    }

    public function exportar(HistorialPaciente $historial)
    {
        // Lógica para exportar historial en PDF
        return response()->json(['message' => 'Exportación iniciada']);
    }
}