<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionIA;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IAConfigController extends Controller
{
    public function index()
    {
        $configuraciones = ConfiguracionIA::orderBy('nombre')->get();
        
        return Inertia::render('Admin/ConfigurarIA', [
            'configuraciones' => $configuraciones
        ]);
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'parametros' => 'required|array',
            'parametros.peso_edad' => 'required|numeric|between:0,1',
            'parametros.peso_gravedad' => 'required|numeric|between:0,1',
            'parametros.peso_tiempo_espera' => 'required|numeric|between:0,1',
            'parametros.peso_especialidad' => 'required|numeric|between:0,1',
            'parametros.umbral_rojo' => 'required|numeric|between:0,1'
        ]);

        $config = ConfiguracionIA::firstOrCreate(['nombre' => $request->nombre]);
        $config->actualizarParametros($request->parametros, auth()->id());

        return back()->with('success', 'Configuración actualizada correctamente');
    }

    public function probar(Request $request)
    {
        $request->validate([
            'parametros' => 'required|array',
            'datos_prueba' => 'required|array'
        ]);

        $resultado = $this->calcularPrioridad($request->datos_prueba, $request->parametros);
        
        return response()->json([
            'prioridad' => $resultado >= $request->parametros['umbral_rojo'] ? 'ROJO' : 'VERDE',
            'puntuacion' => $resultado,
            'detalles' => $this->obtenerDetallesPuntuacion($request->datos_prueba, $request->parametros)
        ]);
    }

    private function calcularPrioridad($datos, $parametros)
    {
        $puntuacion = 0;
        
        // Edad (mayor edad = mayor prioridad)
        $puntuacion += ($datos['edad'] / 100) * $parametros['peso_edad'];
        
        // Gravedad (1-10 escala)
        $puntuacion += ($datos['gravedad'] / 10) * $parametros['peso_gravedad'];
        
        // Tiempo de espera (días)
        $puntuacion += min($datos['tiempo_espera'] / 30, 1) * $parametros['peso_tiempo_espera'];
        
        // Especialidad crítica
        $puntuacion += $datos['especialidad_critica'] * $parametros['peso_especialidad'];
        
        return min($puntuacion, 1);
    }

    private function obtenerDetallesPuntuacion($datos, $parametros)
    {
        return [
            'edad' => ($datos['edad'] / 100) * $parametros['peso_edad'],
            'gravedad' => ($datos['gravedad'] / 10) * $parametros['peso_gravedad'],
            'tiempo_espera' => min($datos['tiempo_espera'] / 30, 1) * $parametros['peso_tiempo_espera'],
            'especialidad' => $datos['especialidad_critica'] * $parametros['peso_especialidad']
        ];
    }
}