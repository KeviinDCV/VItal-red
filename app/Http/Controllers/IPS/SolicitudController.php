<?php

namespace App\Http\Controllers\IPS;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\RegistroMedico;
use App\Models\Paciente;
use App\Models\Notificacion;
use App\Models\ConfiguracionIA;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SolicitudController extends Controller
{
    public function create()
    {
        return Inertia::render('IPS/SolicitarReferencia');
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente' => 'required|array',
            'paciente.nombre' => 'required|string|max:255',
            'paciente.documento' => 'required|string|max:20',
            'paciente.fecha_nacimiento' => 'required|date',
            'paciente.telefono' => 'nullable|string|max:20',
            'motivo_consulta' => 'required|string|max:1000',
            'especialidad_solicitada' => 'required|string|max:100',
            'diagnostico_presuntivo' => 'required|string|max:500',
            'examenes_realizados' => 'nullable|string|max:1000',
            'tratamiento_actual' => 'nullable|string|max:500',
            'urgencia_justificacion' => 'required|string|max:1000',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        // Crear o actualizar paciente
        $paciente = Paciente::updateOrCreate(
            ['documento' => $request->paciente['documento']],
            $request->paciente
        );

        // Crear registro médico
        $registroMedico = RegistroMedico::create([
            'paciente_id' => $paciente->id,
            'motivo_consulta' => $request->motivo_consulta,
            'diagnostico_presuntivo' => $request->diagnostico_presuntivo,
            'examenes_realizados' => $request->examenes_realizados,
            'tratamiento_actual' => $request->tratamiento_actual,
            'created_by' => auth()->id()
        ]);

        // Calcular prioridad usando IA
        $prioridad = $this->calcularPrioridad($request->all(), $paciente);

        // Crear solicitud de referencia
        $solicitud = SolicitudReferencia::create([
            'registro_medico_id' => $registroMedico->id,
            'ips_id' => auth()->user()->ips_id,
            'especialidad_solicitada' => $request->especialidad_solicitada,
            'urgencia_justificacion' => $request->urgencia_justificacion,
            'prioridad' => $prioridad,
            'estado' => 'pendiente'
        ]);

        // Procesar archivos si existen
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $path = $archivo->store('referencias', 'public');
                // Guardar referencia del archivo en la base de datos
            }
        }

        // Notificar a médicos
        $medicos = \App\Models\User::where('role', 'medico')->get();
        foreach ($medicos as $medico) {
            Notificacion::crearNotificacion(
                $medico->id,
                'solicitud_nueva',
                'Nueva solicitud de referencia',
                "Nueva solicitud de prioridad {$prioridad} para {$request->paciente['nombre']}",
                ['solicitud_id' => $solicitud->id],
                $prioridad === 'ROJO' ? 'alta' : 'media'
            );
        }

        return redirect()->route('ips.mis-solicitudes')->with('success', 'Solicitud enviada correctamente');
    }

    public function misSolicitudes()
    {
        $solicitudes = SolicitudReferencia::with(['registroMedico', 'decision'])
            ->whereHas('registroMedico', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('IPS/MisSolicitudes', [
            'solicitudes' => $solicitudes
        ]);
    }

    private function calcularPrioridad($datos, $paciente)
    {
        $config = ConfiguracionIA::configuracionPrioridad();
        
        $edad = now()->diffInYears($paciente->fecha_nacimiento);
        $puntuacion = 0;
        
        // Edad (mayor edad = mayor prioridad)
        $puntuacion += ($edad / 100) * $config['peso_edad'];
        
        // Gravedad basada en palabras clave
        $gravedad = $this->evaluarGravedad($datos['urgencia_justificacion']);
        $puntuacion += ($gravedad / 10) * $config['peso_gravedad'];
        
        // Especialidad crítica
        $especialidadCritica = $this->esEspecialidadCritica($datos['especialidad_solicitada']);
        $puntuacion += $especialidadCritica * $config['peso_especialidad'];
        
        return $puntuacion >= $config['umbral_rojo'] ? 'ROJO' : 'VERDE';
    }

    private function evaluarGravedad($texto)
    {
        $palabrasCriticas = ['urgente', 'grave', 'severo', 'crítico', 'emergencia', 'dolor intenso'];
        $puntuacion = 5; // Base
        
        foreach ($palabrasCriticas as $palabra) {
            if (stripos($texto, $palabra) !== false) {
                $puntuacion += 2;
            }
        }
        
        return min($puntuacion, 10);
    }

    private function esEspecialidadCritica($especialidad)
    {
        $especialidadesCriticas = ['cardiología', 'neurología', 'oncología', 'cirugía'];
        return in_array(strtolower($especialidad), $especialidadesCriticas) ? 1 : 0;
    }
}