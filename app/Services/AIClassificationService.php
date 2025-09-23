<?php

namespace App\Services;

use App\Models\ConfiguracionIA;
use App\Models\AIClassificationLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIClassificationService
{
    protected $config;

    public function __construct()
    {
        $this->config = Cache::get('ia_config', [
            'peso_edad' => 0.3,
            'peso_gravedad' => 0.5,
            'peso_especialidad' => 0.2,
            'peso_sintomas' => 0.4,
            'umbral_rojo' => 0.7,
            'umbral_verde' => 0.3
        ]);
    }

    public function clasificarSolicitud($solicitudData, $pacienteData)
    {
        $puntuacion = $this->calcularPuntuacion($solicitudData, $pacienteData);
        $clasificacion = $this->determinarClasificacion($puntuacion);
        
        // Registrar clasificación para aprendizaje
        $this->registrarClasificacion($solicitudData, $pacienteData, $puntuacion, $clasificacion);
        
        return [
            'clasificacion' => $clasificacion,
            'puntuacion' => $puntuacion,
            'confianza' => $this->calcularConfianza($puntuacion),
            'factores' => $this->obtenerFactoresInfluyentes($solicitudData, $pacienteData)
        ];
    }

    protected function calcularPuntuacion($solicitudData, $pacienteData)
    {
        $puntuacion = 0;

        // Factor edad
        $edad = $this->calcularEdad($pacienteData['fecha_nacimiento']);
        $factorEdad = $this->evaluarFactorEdad($edad);
        $puntuacion += $factorEdad * $this->config['peso_edad'];

        // Factor gravedad
        $factorGravedad = $this->evaluarGravedad($solicitudData);
        $puntuacion += $factorGravedad * $this->config['peso_gravedad'];

        // Factor especialidad
        $factorEspecialidad = $this->evaluarEspecialidad($solicitudData['especialidad_solicitada']);
        $puntuacion += $factorEspecialidad * $this->config['peso_especialidad'];

        // Factor síntomas
        $factorSintomas = $this->evaluarSintomas($solicitudData);
        $puntuacion += $factorSintomas * $this->config['peso_sintomas'];

        return min(max($puntuacion, 0), 1);
    }

    protected function evaluarFactorEdad($edad)
    {
        if ($edad >= 80) return 1.0;
        if ($edad >= 65) return 0.8;
        if ($edad >= 50) return 0.6;
        if ($edad >= 18) return 0.4;
        if ($edad >= 5) return 0.7;
        return 0.9; // Menores de 5 años
    }

    protected function evaluarGravedad($solicitudData)
    {
        $texto = strtolower($solicitudData['urgencia_justificacion'] . ' ' . 
                           ($solicitudData['motivo_consulta'] ?? ''));
        
        $palabrasCriticas = [
            'urgente' => 0.9,
            'emergencia' => 1.0,
            'crítico' => 0.95,
            'grave' => 0.8,
            'severo' => 0.8,
            'dolor intenso' => 0.7,
            'hemorragia' => 0.9,
            'dificultad respiratoria' => 0.85,
            'pérdida de conciencia' => 1.0,
            'convulsiones' => 0.9,
            'infarto' => 1.0,
            'accidente cerebrovascular' => 1.0,
            'trauma' => 0.8
        ];

        $puntuacion = 0.3; // Base
        foreach ($palabrasCriticas as $palabra => $peso) {
            if (strpos($texto, $palabra) !== false) {
                $puntuacion = max($puntuacion, $peso);
            }
        }

        return $puntuacion;
    }

    protected function evaluarEspecialidad($especialidad)
    {
        $especialidadesCriticas = [
            'cardiología' => 0.9,
            'neurología' => 0.85,
            'oncología' => 0.8,
            'cirugía' => 0.75,
            'urgencias' => 1.0,
            'medicina interna' => 0.7,
            'pediatría' => 0.8,
            'ginecología' => 0.6,
            'ortopedia' => 0.5,
            'dermatología' => 0.3,
            'oftalmología' => 0.4,
            'otorrinolaringología' => 0.4
        ];

        $especialidadLower = strtolower($especialidad);
        return $especialidadesCriticas[$especialidadLower] ?? 0.5;
    }

    protected function evaluarSintomas($solicitudData)
    {
        $texto = strtolower($solicitudData['urgencia_justificacion'] . ' ' . 
                           ($solicitudData['motivo_consulta'] ?? '') . ' ' .
                           ($solicitudData['diagnostico_presuntivo'] ?? ''));

        $sintomasAlarma = [
            'dolor torácico' => 0.9,
            'disnea' => 0.8,
            'síncope' => 0.85,
            'cefalea intensa' => 0.7,
            'fiebre alta' => 0.6,
            'vómito' => 0.5,
            'mareo' => 0.4,
            'palpitaciones' => 0.6,
            'sudoración' => 0.5,
            'confusión' => 0.8,
            'debilidad' => 0.6,
            'sangrado' => 0.8
        ];

        $puntuacion = 0.2; // Base
        $sintomasEncontrados = 0;

        foreach ($sintomasAlarma as $sintoma => $peso) {
            if (strpos($texto, $sintoma) !== false) {
                $puntuacion += $peso * 0.3;
                $sintomasEncontrados++;
            }
        }

        // Bonus por múltiples síntomas
        if ($sintomasEncontrados > 2) {
            $puntuacion += 0.2;
        }

        return min($puntuacion, 1.0);
    }

    protected function determinarClasificacion($puntuacion)
    {
        if ($puntuacion >= $this->config['umbral_rojo']) {
            return 'ROJO';
        } elseif ($puntuacion <= $this->config['umbral_verde']) {
            return 'VERDE';
        } else {
            // Zona intermedia - tender hacia ROJO por seguridad
            return 'ROJO';
        }
    }

    protected function calcularConfianza($puntuacion)
    {
        $distanciaUmbralRojo = abs($puntuacion - $this->config['umbral_rojo']);
        $distanciaUmbralVerde = abs($puntuacion - $this->config['umbral_verde']);
        
        $distanciaMinima = min($distanciaUmbralRojo, $distanciaUmbralVerde);
        
        return min(0.5 + ($distanciaMinima * 2), 1.0);
    }

    protected function obtenerFactoresInfluyentes($solicitudData, $pacienteData)
    {
        $edad = $this->calcularEdad($pacienteData['fecha_nacimiento']);
        
        return [
            'edad' => [
                'valor' => $edad,
                'impacto' => $this->evaluarFactorEdad($edad),
                'descripcion' => $this->getDescripcionEdad($edad)
            ],
            'gravedad' => [
                'impacto' => $this->evaluarGravedad($solicitudData),
                'descripcion' => 'Basado en palabras clave en la justificación'
            ],
            'especialidad' => [
                'valor' => $solicitudData['especialidad_solicitada'],
                'impacto' => $this->evaluarEspecialidad($solicitudData['especialidad_solicitada']),
                'descripcion' => 'Criticidad de la especialidad solicitada'
            ],
            'sintomas' => [
                'impacto' => $this->evaluarSintomas($solicitudData),
                'descripcion' => 'Síntomas de alarma identificados'
            ]
        ];
    }

    protected function calcularEdad($fechaNacimiento)
    {
        return \Carbon\Carbon::parse($fechaNacimiento)->age;
    }

    protected function getDescripcionEdad($edad)
    {
        if ($edad >= 80) return 'Adulto mayor de alto riesgo';
        if ($edad >= 65) return 'Adulto mayor';
        if ($edad >= 50) return 'Adulto maduro';
        if ($edad >= 18) return 'Adulto joven';
        if ($edad >= 5) return 'Menor de edad';
        return 'Primera infancia - alta prioridad';
    }

    protected function registrarClasificacion($solicitudData, $pacienteData, $puntuacion, $clasificacion)
    {
        try {
            AIClassificationLog::create([
                'input_data' => json_encode([
                    'solicitud' => $solicitudData,
                    'paciente' => $pacienteData
                ]),
                'puntuacion' => $puntuacion,
                'clasificacion' => $clasificacion,
                'config_utilizada' => json_encode($this->config),
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando clasificación IA: ' . $e->getMessage());
        }
    }
}