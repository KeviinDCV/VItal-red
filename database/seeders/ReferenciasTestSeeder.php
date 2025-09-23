<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IPS;
use App\Models\User;
use App\Models\RegistroMedico;
use App\Models\SolicitudReferencia;

class ReferenciasTestSeeder extends Seeder
{
    public function run(): void
    {
        // Crear IPS de prueba
        $ips = IPS::firstOrCreate(
            ['codigo_prestador' => 'HSJD001'],
            [
            'codigo_prestador' => 'HSJD001',
            'nombre' => 'Hospital San Juan de Dios',
            'nit' => '890123456-1',
            'tipo_ips' => 'HOSPITAL',
            'departamento' => 'Valle del Cauca',
            'municipio' => 'Cali',
            'direccion' => 'Calle 10 #15-25, Cali',
            'telefono' => '3185551234',
            'email' => 'contacto@hsjd.com',
            'tiene_hospitalizacion' => true,
            'tiene_urgencias' => true,
            'tiene_uci' => true,
            'especialidades_disponibles' => ['cardiologia', 'neurologia', 'cirugia'],
            'contacto_referencias' => 'Dr. Juan Pérez',
            'telefono_referencias' => '3185551235',
            'email_referencias' => 'referencias@hsjd.com',
            'activa' => true,
            'acepta_referencias' => true,
            'capacidad_diaria' => 50,
            'fecha_registro' => now()
        ]);
        
        if (!$ips->wasRecentlyCreated) {
            echo "IPS ya existe, usando existente\n";
        }

        // Crear usuario IPS
        $usuarioIPS = User::firstOrCreate(
            ['email' => 'ips@test.com'],
            [
            'name' => 'Usuario IPS Test',
            'email' => 'ips@test.com',
            'password' => bcrypt('password'),
            'role' => 'ips',
            'is_active' => true,
            'ips_id' => $ips->id
        ]);
        
        if (!$usuarioIPS->wasRecentlyCreated) {
            echo "Usuario IPS ya existe, usando existente\n";
        }

        // Crear registros médicos de prueba
        $registros = [
            [
                'nombre' => 'María',
                'apellidos' => 'González López',
                'numero_identificacion' => '12345678',
                'fecha_nacimiento' => '1980-05-15',
                'especialidad' => 'Cardiología'
            ],
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Rodríguez Pérez',
                'numero_identificacion' => '87654321',
                'fecha_nacimiento' => '1975-12-03',
                'especialidad' => 'Neurología'
            ]
        ];

        foreach ($registros as $registroData) {
            $registro = RegistroMedico::create([
                'user_id' => $usuarioIPS->id,
                'tipo_identificacion' => 'CC',
                'numero_identificacion' => $registroData['numero_identificacion'],
                'nombre' => $registroData['nombre'],
                'apellidos' => $registroData['apellidos'],
                'fecha_nacimiento' => $registroData['fecha_nacimiento'],
                'edad' => \Carbon\Carbon::parse($registroData['fecha_nacimiento'])->age,
                'fecha_ingreso' => now()->subDays(2),
                'dias_hospitalizados' => 2,
                'sexo' => 'Masculino',
                'asegurador' => 'EPS Salud Total',
                'departamento' => 'Valle del Cauca',
                'ciudad' => 'Cali',
                'institucion_remitente' => 'Hospital San Juan de Dios',
                'tipo_paciente' => 'Hospitalizado',
                'diagnostico_principal' => 'Dolor torácico',
                'motivo_consulta' => 'Dolor torácico y dificultad respiratoria',
                'clasificacion_triage' => 'III',
                'enfermedad_actual' => 'Paciente con dolor torácico de 2 horas de evolución',
                'antecedentes' => 'HTA, DM tipo 2',
                'examen_fisico' => 'Paciente consciente, orientado, hemodinámicamente estable',
                'plan_terapeutico' => 'Manejo médico, evaluación por especialista',
                'frecuencia_cardiaca' => 85,
                'frecuencia_respiratoria' => 18,
                'temperatura' => 36.5,
                'tension_sistolica' => 140,
                'tension_diastolica' => 90,
                'saturacion_oxigeno' => 98,
                'glucometria' => 110,
                'escala_glasgow' => 15,
                'tratamiento' => 'Aspirina 100mg, Atorvastatina 40mg',
                'motivo_remision' => 'Evaluación por cardiología',
                'tipo_solicitud' => 'Consulta',
                'requerimiento_oxigeno' => 'No',
                'tipo_servicio' => 'Consulta Externa',
                'tipo_apoyo' => 'Ninguno',
                'especialidad_solicitada' => $registroData['especialidad'],
                'estado' => 'completado'
            ]);

            SolicitudReferencia::create([
                'registro_medico_id' => $registro->id,
                'codigo_solicitud' => 'REF-' . str_pad($registro->id, 6, '0', STR_PAD_LEFT),
                'prioridad' => rand(0, 1) ? 'ROJO' : 'VERDE',
                'estado' => 'PENDIENTE',
                'fecha_solicitud' => now(),
                'fecha_limite' => now()->addHours(48),
                'puntuacion_ia' => rand(60, 95) / 100,
                'factores_priorizacion' => [
                    'edad' => 0.3,
                    'gravedad' => 0.4,
                    'especialidad' => 0.2,
                    'tiempo' => 0.1
                ],
                'observaciones_ia' => 'Paciente con factores de riesgo cardiovascular que requiere evaluación especializada'
            ]);
        }
    }
}