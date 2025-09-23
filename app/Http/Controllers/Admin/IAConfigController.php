<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AIClassificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class IAConfigController extends Controller
{
    protected $aiService;

    public function __construct(AIClassificationService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $configuracion = Cache::get('ia_config', [
            'peso_edad' => 0.3,
            'peso_gravedad' => 0.5,
            'peso_especialidad' => 0.2,
            'peso_sintomas' => 0.4,
            'criterios_rojo' => 'Pacientes críticos, urgencias médicas, síntomas de alarma',
            'criterios_verde' => 'Consultas programadas, seguimiento rutinario, casos no urgentes',
            'umbral_rojo' => 0.7,
            'umbral_verde' => 0.3
        ]);

        return Inertia::render('admin/ConfigurarIA', [
            'configuracion' => $configuracion
        ]);
    }

    public function actualizar(Request $request)
    {
        $validated = $request->validate([
            'peso_edad' => 'required|numeric|between:0,1',
            'peso_gravedad' => 'required|numeric|between:0,1',
            'peso_especialidad' => 'required|numeric|between:0,1',
            'peso_sintomas' => 'required|numeric|between:0,1',
            'criterios_rojo' => 'required|string|max:1000',
            'criterios_verde' => 'required|string|max:1000',
            'umbral_rojo' => 'required|numeric|between:0.5,1',
            'umbral_verde' => 'required|numeric|between:0,0.5'
        ]);

        Cache::put('ia_config', $validated, now()->addDays(30));

        return redirect()->back()->with('success', 'Configuración de IA actualizada correctamente');
    }

    public function probar(Request $request)
    {
        $config = $request->input('config');
        $testCases = $request->input('testCases');
        
        $results = [];
        $correctPredictions = 0;

        foreach ($testCases as $case) {
            $prediction = $this->classifyCase($case, $config);
            $case['resultado_actual'] = $prediction;
            
            if ($prediction === $case['resultado_esperado']) {
                $correctPredictions++;
            }
            
            $results[] = $case;
        }

        $precision = count($testCases) > 0 ? $correctPredictions / count($testCases) : 0;

        return response()->json([
            'testCases' => $results,
            'precision' => $precision,
            'correctPredictions' => $correctPredictions,
            'totalCases' => count($testCases)
        ]);
    }

    private function classifyCase($case, $config)
    {
        $score = 0;

        // Peso por edad (mayor edad = mayor riesgo)
        $edadScore = $case['edad'] > 65 ? 0.8 : ($case['edad'] > 45 ? 0.5 : 0.2);
        $score += $edadScore * $config['peso_edad'];

        // Peso por gravedad
        $gravedadScore = match($case['gravedad']) {
            'alta' => 0.9,
            'media' => 0.5,
            'baja' => 0.1,
            default => 0.3
        };
        $score += $gravedadScore * $config['peso_gravedad'];

        // Peso por especialidad (algunas más críticas)
        $especialidadesCriticas = ['Cardiología', 'Neurología', 'Urgencias', 'Cirugía'];
        $especialidadScore = in_array($case['especialidad'], $especialidadesCriticas) ? 0.8 : 0.3;
        $score += $especialidadScore * $config['peso_especialidad'];

        // Peso por síntomas críticos
        $sintomasCriticos = ['dolor_toracico', 'disnea', 'confusion', 'perdida_conciencia', 'hemorragia'];
        $sintomasCount = count(array_intersect($case['sintomas'], $sintomasCriticos));
        $sintomasScore = min($sintomasCount * 0.3, 1.0);
        $score += $sintomasScore * $config['peso_sintomas'];

        // Normalizar score
        $normalizedScore = min($score, 1.0);

        // Clasificar según umbrales
        if ($normalizedScore >= $config['umbral_rojo']) {
            return 'ROJO';
        } elseif ($normalizedScore <= $config['umbral_verde']) {
            return 'VERDE';
        } else {
            // En caso de zona intermedia, tender hacia ROJO por seguridad
            return 'ROJO';
        }
    }
}