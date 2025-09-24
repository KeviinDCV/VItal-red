<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SolicitudReferencia;
use App\Models\RegistroMedico;
use App\Models\DecisionReferencia;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Hash;

class QuickDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. USUARIOS ADICIONALES (20 por rol)
        $this->createUsers();
        
        // 2. REGISTROS MÉDICOS (50 adicionales)
        $this->createRegistrosMedicos();
        
        // 3. SOLICITUDES (40 adicionales)
        $this->createSolicitudes();
        
        // 4. DECISIONES (30 adicionales)
        $this->createDecisiones();
        
        // 5. NOTIFICACIONES (100 adicionales)
        $this->createNotificaciones();
    }

    private function createUsers()
    {
        $roles = ['administrador', 'medico', 'ips']; // Solo roles que existen
        
        foreach ($roles as $role) {
            for ($i = 1; $i <= 20; $i++) {
                $timestamp = time() + $i;
                User::create([
                    'name' => ucfirst($role) . " Test {$i}",
                    'email' => "{$role}_test_{$timestamp}@vital-red.com",
                    'telefono' => '+57300' . rand(1000000, 9999999),
                    'password' => Hash::make('password123'),
                    'role' => $role,
                    'is_active' => true,
                    'ips_id' => $role === 'ips' ? rand(1, 2) : null,
                ]);
            }
        }
    }

    private function createRegistrosMedicos()
    {
        $nombres = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Carmen', 'Pedro', 'Laura'];
        $apellidos = ['García', 'López', 'Martínez', 'González', 'Pérez', 'Sánchez'];
        $diagnosticos = [
            'Hipertensión arterial', 'Diabetes mellitus', 'Insuficiencia cardíaca',
            'Neumonía', 'Gastritis', 'Artritis', 'Migraña', 'Asma'
        ];
        $especialidades = [
            'Cardiología', 'Neurología', 'Gastroenterología', 'Neumología',
            'Endocrinología', 'Nefrología', 'Oncología', 'Dermatología'
        ];

        for ($i = 1; $i <= 50; $i++) {
            RegistroMedico::create([
                'tipo_identificacion' => 'CC',
                'numero_identificacion' => '1000' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'nombre' => $nombres[array_rand($nombres)],
                'apellidos' => $apellidos[array_rand($apellidos)],
                'fecha_nacimiento' => now()->subYears(rand(18, 85)),
                'edad' => rand(18, 85),
                'sexo' => rand(0, 1) ? 'M' : 'F',
                'diagnostico_principal' => $diagnosticos[array_rand($diagnosticos)],
                'especialidad_solicitada' => $especialidades[array_rand($especialidades)],
                'motivo_remision' => 'Paciente requiere evaluación especializada - Test ' . $i,
                'motivo_consulta' => 'Consulta de prueba ' . $i,
                'user_id' => User::where('role', 'ips')->inRandomOrder()->first()->id,
            ]);
        }
    }

    private function createSolicitudes()
    {
        $prioridades = ['ROJO', 'AMARILLO', 'VERDE'];
        $estados = ['PENDIENTE', 'EN_PROCESO', 'ACEPTADO', 'NO_ADMITIDO'];

        for ($i = 1; $i <= 40; $i++) {
            SolicitudReferencia::create([
                'registro_medico_id' => rand(1, 60), // Incluye los nuevos registros
                'codigo_solicitud' => 'TEST-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'prioridad' => $prioridades[array_rand($prioridades)],
                'estado' => $estados[array_rand($estados)],
                'fecha_solicitud' => now()->subDays(rand(0, 30)),
                'fecha_limite' => now()->addDays(rand(1, 15)),
                'puntuacion_ia' => rand(1, 10),
                'observaciones_ia' => 'Test - Clasificación automática ' . $i,
                'procesado_por' => rand(0, 1) ? User::where('role', 'medico')->inRandomOrder()->first()->id : null,
            ]);
        }
    }

    private function createDecisiones()
    {
        $decisiones = ['aceptada', 'rechazada'];
        $justificaciones = [
            'Paciente cumple criterios - Test',
            'Requiere manejo ambulatorio - Test',
            'Completar estudios - Test',
            'Apto para evaluación - Test'
        ];

        for ($i = 1; $i <= 30; $i++) {
            DecisionReferencia::create([
                'solicitud_referencia_id' => rand(1, 50), // Incluye nuevas solicitudes
                'decidido_por' => User::where('role', 'medico')->inRandomOrder()->first()->id,
                'decision' => $decisiones[array_rand($decisiones)],
                'observaciones' => $justificaciones[array_rand($justificaciones)] . ' ' . $i,
                'fecha_decision' => now()->subDays(rand(0, 20)),
            ]);
        }
    }

    private function createNotificaciones()
    {
        $tipos = ['solicitud_nueva', 'decision_tomada', 'recordatorio', 'test_notification'];
        $titulos = [
            'Nueva solicitud TEST',
            'Solicitud evaluada TEST',
            'Recordatorio TEST',
            'Notificación de prueba'
        ];

        for ($i = 1; $i <= 100; $i++) {
            Notificacion::create([
                'user_id' => User::inRandomOrder()->first()->id,
                'tipo' => $tipos[array_rand($tipos)],
                'titulo' => $titulos[array_rand($titulos)] . ' ' . $i,
                'mensaje' => 'Mensaje de prueba número ' . $i . ' para testing del sistema',
                'datos' => json_encode(['test_id' => $i, 'timestamp' => now()]),
                'leida' => rand(0, 1),
                'prioridad' => ['baja', 'media', 'alta'][rand(0, 2)],
            ]);
        }
    }
}