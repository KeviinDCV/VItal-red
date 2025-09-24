<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\RegistroMedico;
use App\Models\Paciente;
use App\Services\GeminiAIService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormularioIngresoController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiAIService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function index()
    {
        return Inertia::render('Shared/FormularioIngreso');
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente' => 'required|array',
            'paciente.nombre' => 'required|string|max:255',
            'paciente.apellidos' => 'required|string|max:255',
            'paciente.numero_identificacion' => 'required|string|unique:pacientes',
            'paciente.fecha_nacimiento' => 'required|date',
            'paciente.telefono' => 'nullable|string|max:20',
            'registro' => 'required|array',
            'registro.motivo_consulta' => 'required|string',
            'registro.antecedentes' => 'nullable|string',
            'registro.examen_fisico' => 'nullable|string',
            'registro.diagnostico' => 'required|string'
        ]);

        // Crear paciente
        $paciente = Paciente::create($request->paciente);

        // Crear registro médico
        $registro = RegistroMedico::create([
            'paciente_id' => $paciente->id,
            'medico_id' => auth()->id(),
            'motivo_consulta' => $request->registro['motivo_consulta'],
            'antecedentes' => $request->registro['antecedentes'],
            'examen_fisico' => $request->registro['examen_fisico'],
            'diagnostico_principal' => $request->registro['diagnostico'],
            'fecha_registro' => now()
        ]);

        return response()->json([
            'success' => true,
            'paciente_id' => $paciente->id,
            'registro_id' => $registro->id
        ]);
    }
}