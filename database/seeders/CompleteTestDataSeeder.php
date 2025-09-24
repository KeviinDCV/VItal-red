<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\IPS;
use App\Models\RegistroMedico;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\SeguimientoPaciente;
use App\Models\Notificacion;
use App\Models\ConfiguracionIA;
use App\Models\MenuPermiso;

class CompleteTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear usuarios de prueba
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Administrador Test',
                'password' => bcrypt('password'),
                'role' => 'administrador',
                'is_active' => true
            ]
        );

        $medico1 = User::firstOrCreate(
            ['email' => 'medico1@test.com'],
            [
                'name' => 'Dr. Carlos Mendoza',
                'password' => bcrypt('password'),
                'role' => 'medico',
                'is_active' => true
            ]
        );

        $medico2 = User::firstOrCreate(
            ['email' => 'medico2@test.com'],
            [
                'name' => 'Dra. Ana García',
                'password' => bcrypt('password'),
                'role' => 'medico',
                'is_active' => true
            ]
        );

        // 2. Crear IPS de prueba
        $ips = [
            [
                'codigo_prestador' => 'HSJD001',
                'nombre' => 'Hospital San Juan de Dios',
                'nit' => '890123456-1',
                'tipo_ips' => 'HOSPITAL',
                'departamento' => 'Valle del Cauca',
                'municipio' => 'Cali',
                'direccion' => 'Calle 10 #15-25',
                'telefono' => '3185551234',
                'email' => 'contacto@hsjd.com',
                'tiene_hospitalizacion' => true,
                'tiene_urgencias' => true,
                'tiene_uci' => true,
                'especialidades_disponibles' => ['cardiologia', 'neurologia', 'cirugia'],
                'activa' => true,
                'acepta_referencias' => true,
                'capacidad_diaria' => 50,
                'fecha_registro' => now()
            ],
            [
                'codigo_prestador' => 'CLIN002',
                'nombre' => 'Clínica del Valle',
                'nit' => '890987654-2',
                'tipo_ips' => 'CLINICA',
                'departamento' => 'Valle del Cauca',
                'municipio' => 'Palmira',
                'direccion' => 'Carrera 30 #20-15',
                'telefono' => '3185559876',
                'email' => 'info@clinicavalle.com',
                'tiene_hospitalizacion' => true,
                'tiene_urgencias' => false,
                'tiene_uci' => false,
                'especialidades_disponibles' => ['pediatria', 'ginecologia', 'medicina_interna'],
                'activa' => true,
                'acepta_referencias' => true,
                'capacidad_diaria' => 30,
                'fecha_registro' => now()
            ]
        ];

        foreach ($ips as $ipsData) {
            $ipsModel = IPS::firstOrCreate(
                ['codigo_prestador' => $ipsData['codigo_prestador']],
                $ipsData
            );

            // Crear usuario IPS
            User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '', $ipsData['nombre'])) . '@test.com'],
                [
                    'name' => 'Usuario ' . $ipsData['nombre'],
                    'password' => bcrypt('password'),
                    'role' => 'ips',
                    'is_active' => true,
                    'ips_id' => $ipsModel->id
                ]
            );
        }

        // 3. Crear registros médicos y solicitudes
        $pacientes = [
            ['nombre' => 'María', 'apellidos' => 'González López', 'documento' => '12345678', 'edad' => 45, 'especialidad' => 'Cardiología'],
            ['nombre' => 'Carlos', 'apellidos' => 'Rodríguez Pérez', 'documento' => '87654321', 'edad' => 50, 'especialidad' => 'Neurología'],
            ['nombre' => 'Ana', 'apellidos' => 'Martínez Silva', 'documento' => '11223344', 'edad' => 35, 'especialidad' => 'Oncología'],
            ['nombre' => 'Luis', 'apellidos' => 'Hernández Castro', 'documento' => '55667788', 'edad' => 60, 'especialidad' => 'Cardiología'],
            ['nombre' => 'Carmen', 'apellidos' => 'López Vargas', 'documento' => '99887766', 'edad' => 28, 'especialidad' => 'Ginecología'],
            ['nombre' => 'Pedro', 'apellidos' => 'Sánchez Morales', 'documento' => '44556677', 'edad' => 42, 'especialidad' => 'Urología'],
            ['nombre' => 'Rosa', 'apellidos' => 'Jiménez Torres', 'documento' => '33445566', 'edad' => 55, 'especialidad' => 'Dermatología'],
            ['nombre' => 'Miguel', 'apellidos' => 'Ramírez Ortega', 'documento' => '22334455', 'edad' => 38, 'especialidad' => 'Ortopedia'],
            ['nombre' => 'Elena', 'apellidos' => 'Vásquez Ruiz', 'documento' => '66778899', 'edad' => 47, 'especialidad' => 'Psiquiatría'],
            ['nombre' => 'Jorge', 'apellidos' => 'Moreno Díaz', 'documento' => '77889900', 'edad' => 52, 'especialidad' => 'Oftalmología']
        ];

        $usuarioIPS = User::where('role', 'ips')->first();
        $ipsModel = IPS::first();

        foreach ($pacientes as $index => $pacienteData) {
            $registro = RegistroMedico::create([
                'user_id' => $usuarioIPS->id,
                'tipo_identificacion' => 'CC',
                'numero_identificacion' => $pacienteData['documento'],
                'nombre' => $pacienteData['nombre'],
                'apellidos' => $pacienteData['apellidos'],
                'fecha_nacimiento' => now()->subYears($pacienteData['edad'])->format('Y-m-d'),
                'edad' => $pacienteData['edad'],
                'fecha_ingreso' => now()->subDays(rand(1, 30)),
                'dias_hospitalizados' => random_int(1, 10),
                'sexo' => random_int(0, 1) ? 'Masculino' : 'Femenino',
                'asegurador' => ['EPS Salud Total', 'Nueva EPS', 'Sanitas', 'Sura'][random_int(0, 3)],
                'departamento' => 'Valle del Cauca',
                'ciudad' => 'Cali',
                'institucion_remitente' => $ipsModel->nombre,
                'tipo_paciente' => ['Hospitalizado', 'Ambulatorio', 'Urgencias'][rand(0, 2)],
                'diagnostico_principal' => $this->getDiagnostico($pacienteData['especialidad']),
                'motivo_consulta' => $this->getMotivoConsulta($pacienteData['especialidad']),
                'clasificacion_triage' => ['I', 'II', 'III', 'IV', 'V'][rand(0, 4)],
                'enfermedad_actual' => 'Paciente con síntomas de ' . rand(1, 7) . ' días de evolución',
                'antecedentes' => $this->getAntecedentes(),
                'examen_fisico' => 'Paciente consciente, orientado, hemodinámicamente estable',
                'plan_terapeutico' => 'Manejo médico, evaluación por especialista',
                'frecuencia_cardiaca' => rand(60, 100),
                'frecuencia_respiratoria' => rand(12, 20),
                'temperatura' => rand(360, 380) / 10,
                'tension_sistolica' => rand(110, 160),
                'tension_diastolica' => rand(70, 100),
                'saturacion_oxigeno' => rand(95, 100),
                'glucometria' => rand(80, 140),
                'escala_glasgow' => rand(13, 15),
                'tratamiento' => $this->getTratamiento($pacienteData['especialidad']),
                'motivo_remision' => 'Evaluación por ' . $pacienteData['especialidad'],
                'tipo_solicitud' => 'Consulta',
                'requerimiento_oxigeno' => rand(0, 1) ? 'Sí' : 'No',
                'tipo_servicio' => 'Consulta Externa',
                'tipo_apoyo' => 'Ninguno',
                'especialidad_solicitada' => $pacienteData['especialidad'],
                'estado' => 'completado'
            ]);

            // Crear solicitud de referencia
            $prioridad = $this->calcularPrioridad($pacienteData['edad'], $pacienteData['especialidad']);
            $estado = ['PENDIENTE', 'ACEPTADO', 'NO_ADMITIDO'][rand(0, 2)];
            
            $solicitud = SolicitudReferencia::create([
                'registro_medico_id' => $registro->id,
                'codigo_solicitud' => 'REF-' . date('Y') . '-' . str_pad($registro->id, 6, '0', STR_PAD_LEFT),
                'prioridad' => $prioridad,
                'estado' => $estado,
                'fecha_solicitud' => now()->subDays(rand(0, 15)),
                'fecha_limite' => now()->addHours(48),
                'puntuacion_ia' => rand(60, 95) / 100,
                'factores_priorizacion' => [
                    'edad' => rand(20, 40) / 100,
                    'gravedad' => rand(30, 50) / 100,
                    'especialidad' => rand(10, 30) / 100,
                    'tiempo' => rand(5, 15) / 100
                ],
                'observaciones_ia' => $this->getObservacionIA($pacienteData['especialidad']),
                'procesado_por' => $estado !== 'PENDIENTE' ? [$medico1->id, $medico2->id][rand(0, 1)] : null
            ]);

            // Crear decisión si no está pendiente
            if ($estado !== 'PENDIENTE') {
                DecisionReferencia::create([
                    'solicitud_referencia_id' => $solicitud->id,
                    'decidido_por' => $solicitud->procesado_por,
                    'decision' => $estado === 'ACEPTADO' ? 'ACEPTADO' : 'NO_ADMITIDO',
                    'justificacion' => $this->getJustificacion($estado),
                    'especialista_asignado' => $estado === 'ACEPTADO' ? $this->getEspecialista($pacienteData['especialidad']) : null,
                    'fecha_cita' => $estado === 'ACEPTADO' ? now()->addDays(rand(1, 15)) : null,
                    'fecha_decision' => now()
                ]);

                // Crear seguimiento si fue aceptado
                if ($estado === 'ACEPTADO') {
                    SeguimientoPaciente::create([
                        'solicitud_referencia_id' => $solicitud->id,
                        'actualizado_por' => $solicitud->procesado_por
                    ]);
                }
            }
        }

        // 4. Crear notificaciones
        $usuarios = User::all();
        foreach ($usuarios as $usuario) {
            for ($i = 0; $i < rand(3, 8); $i++) {
                Notificacion::create([
                    'user_id' => $usuario->id,
                    'tipo' => ['solicitud_nueva', 'decision_tomada', 'recordatorio'][rand(0, 2)],
                    'titulo' => $this->getTituloNotificacion(),
                    'mensaje' => $this->getMensajeNotificacion(),
                    'prioridad' => ['baja', 'media', 'alta'][rand(0, 2)],
                    'leida' => rand(0, 1),
                    'leida_en' => rand(0, 1) ? now()->subDays(rand(0, 5)) : null
                ]);
            }
        }

        // 5. Configurar IA
        ConfiguracionIA::firstOrCreate(
            ['nombre' => 'configuracion_principal'],
            [
                'parametros' => [
                    'peso_edad' => 0.25,
                    'peso_gravedad' => 0.40,
                    'peso_tiempo_espera' => 0.20,
                    'peso_especialidad' => 0.15,
                    'umbral_rojo' => 0.70
                ],
                'activa' => true,
                'version' => '1.0',
                'creado_por' => $admin->id
            ]
        );

        // 6. Configurar permisos de menú
        MenuPermiso::configurarDefecto();

        echo "✅ Datos de prueba creados exitosamente:\n";
        echo "- " . User::count() . " usuarios\n";
        echo "- " . IPS::count() . " IPS\n";
        echo "- " . RegistroMedico::count() . " registros médicos\n";
        echo "- " . SolicitudReferencia::count() . " solicitudes de referencia\n";
        echo "- " . DecisionReferencia::count() . " decisiones\n";
        echo "- " . SeguimientoPaciente::count() . " seguimientos\n";
        echo "- " . Notificacion::count() . " notificaciones\n";
    }

    private function getDiagnostico($especialidad)
    {
        $diagnosticos = [
            'Cardiología' => ['Hipertensión arterial', 'Insuficiencia cardíaca', 'Arritmia cardíaca'],
            'Neurología' => ['Cefalea tensional', 'Epilepsia', 'Accidente cerebrovascular'],
            'Oncología' => ['Neoplasia maligna', 'Tumor benigno', 'Síndrome paraneoplásico'],
            'Ginecología' => ['Miomatosis uterina', 'Endometriosis', 'Quiste ovárico'],
            'Urología' => ['Hiperplasia prostática', 'Infección urinaria', 'Cálculo renal'],
            'Dermatología' => ['Dermatitis atópica', 'Psoriasis', 'Melanoma'],
            'Ortopedia' => ['Fractura ósea', 'Artritis', 'Lesión ligamentaria'],
            'Psiquiatría' => ['Trastorno depresivo', 'Trastorno de ansiedad', 'Trastorno bipolar'],
            'Oftalmología' => ['Catarata', 'Glaucoma', 'Retinopatía diabética']
        ];
        
        return $diagnosticos[$especialidad][rand(0, 2)] ?? 'Diagnóstico por determinar';
    }

    private function getMotivoConsulta($especialidad)
    {
        $motivos = [
            'Cardiología' => 'Dolor torácico y palpitaciones',
            'Neurología' => 'Cefalea intensa y mareos',
            'Oncología' => 'Masa palpable y pérdida de peso',
            'Ginecología' => 'Dolor pélvico y sangrado anormal',
            'Urología' => 'Disuria y hematuria',
            'Dermatología' => 'Lesión cutánea sospechosa',
            'Ortopedia' => 'Dolor articular y limitación funcional',
            'Psiquiatría' => 'Alteración del estado de ánimo',
            'Oftalmología' => 'Disminución de la agudeza visual'
        ];
        
        return $motivos[$especialidad] ?? 'Consulta por especialidad';
    }

    private function getAntecedentes()
    {
        $antecedentes = ['HTA', 'DM tipo 2', 'Dislipidemia', 'Tabaquismo', 'Ninguno'];
        return $antecedentes[rand(0, 4)];
    }

    private function getTratamiento($especialidad)
    {
        $tratamientos = [
            'Cardiología' => 'Enalapril 10mg, Atorvastatina 20mg',
            'Neurología' => 'Acetaminofén 500mg, Amitriptilina 25mg',
            'Oncología' => 'Analgésicos, soporte nutricional',
            'Ginecología' => 'Anticonceptivos orales, analgésicos',
            'Urología' => 'Antibióticos, analgésicos',
            'Dermatología' => 'Corticoides tópicos, emolientes',
            'Ortopedia' => 'AINEs, fisioterapia',
            'Psiquiatría' => 'Antidepresivos, psicoterapia',
            'Oftalmología' => 'Gotas oftálmicas, protección ocular'
        ];
        
        return $tratamientos[$especialidad] ?? 'Tratamiento sintomático';
    }

    private function calcularPrioridad($edad, $especialidad)
    {
        $especialidadesCriticas = ['Cardiología', 'Neurología', 'Oncología'];
        $esCritica = in_array($especialidad, $especialidadesCriticas);
        $edadCritica = $edad > 60;
        
        return ($esCritica || $edadCritica) ? 'ROJO' : 'VERDE';
    }

    private function getObservacionIA($especialidad)
    {
        return "Paciente requiere evaluación por {$especialidad} según criterios de priorización del algoritmo IA";
    }

    private function getJustificacion($estado)
    {
        if ($estado === 'ACEPTADO') {
            return 'Paciente cumple criterios para referencia especializada';
        } else {
            return 'Paciente puede ser manejado en nivel primario de atención';
        }
    }

    private function getEspecialista($especialidad)
    {
        $especialistas = [
            'Cardiología' => 'Dr. Juan Cardiólogo',
            'Neurología' => 'Dra. María Neuróloga',
            'Oncología' => 'Dr. Pedro Oncólogo',
            'Ginecología' => 'Dra. Ana Ginecóloga',
            'Urología' => 'Dr. Luis Urólogo'
        ];
        
        return $especialistas[$especialidad] ?? 'Especialista por asignar';
    }

    private function getTituloNotificacion()
    {
        $titulos = [
            'Nueva solicitud de referencia',
            'Decisión tomada sobre solicitud',
            'Recordatorio de cita médica',
            'Actualización de estado',
            'Solicitud urgente'
        ];
        
        return $titulos[rand(0, 4)];
    }

    private function getMensajeNotificacion()
    {
        $mensajes = [
            'Se ha recibido una nueva solicitud de referencia que requiere su atención',
            'La solicitud de referencia ha sido procesada exitosamente',
            'Recordatorio: tiene una cita médica programada',
            'El estado del paciente ha sido actualizado',
            'Solicitud marcada como urgente requiere atención inmediata'
        ];
        
        return $mensajes[rand(0, 4)];
    }
}