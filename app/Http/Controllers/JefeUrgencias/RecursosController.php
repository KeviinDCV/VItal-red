<?php

namespace App\Http\Controllers\JefeUrgencias;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecursosController extends Controller
{
    public function index(Request $request)
    {
        $query = Recurso::query();

        // Filtros
        if ($request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->ubicacion) {
            $query->where('ubicacion', 'LIKE', "%{$request->ubicacion}%");
        }

        if ($request->turno) {
            $query->where('turno', $request->turno);
        }

        $recursos = $query->orderBy('nombre')->paginate(20);

        $estadisticas = [
            'total_recursos' => Recurso::count(),
            'disponibles' => Recurso::disponibles()->count(),
            'ocupados' => Recurso::ocupados()->count(),
            'mantenimiento' => Recurso::mantenimiento()->count(),
            'utilizacion_promedio' => $this->calcularUtilizacionPromedio(),
            'alertas_criticas' => $this->contarAlertasCriticas()
        ];

        $alertas = $this->obtenerAlertas();

        return Inertia::render('jefe-urgencias/GestionRecursos', [
            'recursos' => $recursos,
            'estadisticas' => $estadisticas,
            'alertas' => $alertas
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:MEDICO,ENFERMERO,CAMA,EQUIPO,SALA',
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'estado' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO,FUERA_SERVICIO'
        ]);

        Recurso::create($request->all());

        return redirect()->back()->with('success', 'Recurso creado exitosamente');
    }

    public function update(Request $request, Recurso $recurso)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'estado' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO,FUERA_SERVICIO'
        ]);

        $recurso->update($request->all());

        return redirect()->back()->with('success', 'Recurso actualizado exitosamente');
    }

    public function asignar(Request $request, Recurso $recurso)
    {
        $recurso->update([
            'estado' => 'OCUPADO',
            'capacidad_actual' => $recurso->capacidad_actual + 1
        ]);

        return response()->json(['success' => true]);
    }

    public function liberar(Recurso $recurso)
    {
        $recurso->update([
            'estado' => 'DISPONIBLE',
            'capacidad_actual' => max(0, $recurso->capacidad_actual - 1)
        ]);

        return response()->json(['success' => true]);
    }

    private function calcularUtilizacionPromedio()
    {
        $recursos = Recurso::whereNotNull('capacidad_maxima')
            ->whereNotNull('capacidad_actual')
            ->get();

        if ($recursos->isEmpty()) return 0;

        $utilizacionTotal = $recursos->sum(function($recurso) {
            return $recurso->calcularUtilizacion();
        });

        return round($utilizacionTotal / $recursos->count());
    }

    private function contarAlertasCriticas()
    {
        return Recurso::where('estado', 'FUERA_SERVICIO')
            ->orWhere(function($query) {
                $query->whereNotNull('capacidad_maxima')
                      ->whereRaw('capacidad_actual >= capacidad_maxima');
            })
            ->count();
    }

    private function obtenerAlertas()
    {
        $alertas = [];

        // Recursos fuera de servicio
        $fueraServicio = Recurso::where('estado', 'FUERA_SERVICIO')->get();
        foreach ($fueraServicio as $recurso) {
            $alertas[] = [
                'id' => $recurso->id,
                'tipo' => 'EQUIPO',
                'mensaje' => "Recurso {$recurso->nombre} fuera de servicio",
                'prioridad' => 'ALTA',
                'fecha' => $recurso->updated_at,
                'recurso_id' => $recurso->id
            ];
        }

        // Capacidad al máximo
        $capacidadMaxima = Recurso::whereNotNull('capacidad_maxima')
            ->whereRaw('capacidad_actual >= capacidad_maxima')
            ->get();
        
        foreach ($capacidadMaxima as $recurso) {
            $alertas[] = [
                'id' => $recurso->id + 1000,
                'tipo' => 'CAPACIDAD',
                'mensaje' => "Recurso {$recurso->nombre} al máximo de capacidad",
                'prioridad' => 'MEDIA',
                'fecha' => now(),
                'recurso_id' => $recurso->id
            ];
        }

        return collect($alertas)->sortByDesc('fecha')->values()->all();
    }
}