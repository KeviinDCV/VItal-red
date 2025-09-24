<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SolicitudReferencia;
use App\Models\RegistroMedico;
use App\Models\DecisionReferencia;
use App\Models\Notificacion;
use App\Models\IPS;
use App\Models\SeguimientoPaciente;
use App\Models\EventoAuditoria;
use App\Models\Recurso;
use App\Models\HistorialPaciente;
use Illuminate\Support\Facades\Hash;

class MassiveDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. IPS ADICIONALES PRIMERO (15 IPS)
        $this->createIPS();
        
        // 2. USUARIOS MASIVOS (30 usuarios por rol)
        $this->createUsers();
        
        // 3. REGISTROS MÉDICOS MASIVOS (100 registros)
        $this->createRegistrosMedicos();
        
        // 4. SOLICITUDES MASIVAS (80 solicitudes)
        $this->createSolicitudes();
        
        // 5. DECISIONES MASIVAS (60 decisiones)
        $this->createDecisiones();
        
        // 6. NOTIFICACIONES MASIVAS (200 notificaciones)
        $this->createNotificaciones();
        
        // 7. SEGUIMIENTOS MASIVOS (50 seguimientos)
        $this->createSeguimientos();
        
        // 8. EVENTOS AUDITORÍA MASIVOS (100 eventos)
        $this->createEventosAuditoria();
        
        // 9. RECURSOS MASIVOS (20 recursos)
        $this->createRecursos();
        
        // 10. HISTORIALES MASIVOS (40 historiales)
        $this->createHistoriales();
    }

    private function createUsers()
    {
        $roles = ['administrador', 'medico', 'ips', 'jefe-urgencias'];
        
        foreach ($roles as $role) {
            for ($i = 1; $i <= 30; $i++) {
                User::create([
                    'name' => ucfirst($role) . " Usuario {$i}",
                    'email' => "{$role}{$i}" . time() . rand(100, 999) . "@vital-red.com", // Email único
                    'telefono' => '+57300' . rand(1000000, 9999999),
                    'password' => Hash::make('password123'),
                    'role' => $role,
                    'is_active' => rand(0, 10) > 1, // 90% activos
                    'ips_id' => $role === 'ips' ? rand(1, 17) : null, // Ahora hay más IPS
                ]);
            }
        }
    }

    private function createIPS()
    {
        $nombres = [
            'Hospital San Juan de Dios', 'Clínica del Country', 'Hospital Militar',
            'Fundación Santa Fe', 'Hospital Kennedy', 'Clínica Reina Sofía',
            'Hospital Simón Bolívar', 'Clínica Colsanitas', 'Hospital El Tunal',
            'Clínica Shaio', 'Hospital Occidente', 'Clínica Marly',
            'Hospital Meissen', 'Clínica Universidad Javeriana', 'Hospital Cardio Infantil'
        ];

        foreach ($nombres as $index => $nombre) {
            IPS::create([
                'codigo_prestador' => 'IPS' . str_pad($index + 100, 3, '0', STR_PAD_LEFT), // Evitar duplicados
                'nombre' => $nombre,
                'nit' => '890' . rand(100000, 999999) . '-' . rand(1, 9),
                'tipo_ips' => 'HOSPITAL', // Usar valor fijo que sabemos que funciona
                'departamento' => ['Cundinamarca', 'Valle del Cauca', 'Antioquia'][rand(0, 2)],
                'municipio' => ['Bogotá', 'Cali', 'Medellín'][rand(0, 2)],
                'direccion' => 'Calle ' . rand(1, 200) . ' # ' . rand(1, 50) . '-' . rand(1, 99),
                'telefono' => '318' . rand(1000000, 9999999),
                'email' => strtolower(str_replace(' ', '', $nombre)) . '@hospital.com',
                'tiene_hospitalizacion' => rand(0, 1),
                'tiene_urgencias' => true,
                'tiene_uci' => rand(0, 1),
                'tiene_cirugia' => rand(0, 1),
                'especialidades_disponibles' => json_encode(['cardiologia', 'neurologia', 'medicina_interna']),
                'activa' => true,
                'acepta_referencias' => true,
                'capacidad_diaria' => rand(20, 100),
                'fecha_registro' => now()
            ]);
        }
    }

    private function createRegistrosMedicos()
    {
        $nombres = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Carmen', 'Pedro', 'Laura', 'Miguel', 'Sofia'];
        $apellidos = ['García', 'Rodríguez', 'López', 'Martínez', 'González', 'Pérez', 'Sánchez', 'Ramírez'];
        $diagnosticos = [
            'Hipertensión arterial', 'Diabetes mellitus tipo 2', 'Insuficiencia cardíaca',
            'Neumonía adquirida en comunidad', 'Gastritis crónica', 'Artritis reumatoide',
            'Migraña crónica', 'Asma bronquial', 'Depresión mayor', 'Ansiedad generalizada'
        ];
        $especialidades = [
            'Cardiología', 'Neurología', 'Gastroenterología', 'Neumología', 'Reumatología',
            'Psiquiatría', 'Endocrinología', 'Nefrología', 'Oncología', 'Dermatología'
        ];

        for ($i = 1; $i <= 100; $i++) {
            RegistroMedico::create([
                'nombre' => $nombres[array_rand($nombres)],
                'apellidos' => $apellidos[array_rand($apellidos)] . ' ' . $apellidos[array_rand($apellidos)],
                'documento' => rand(10000000, 99999999),
                'edad' => rand(18, 85),
                'sexo' => rand(0, 1) ? 'M' : 'F',
                'diagnostico_principal' => $diagnosticos[array_rand($diagnosticos)],
                'especialidad_solicitada' => $especialidades[array_rand($especialidades)],
                'motivo_remision' => 'Paciente requiere evaluación especializada por ' . strtolower($diagnosticos[array_rand($diagnosticos)]),
                'user_id' => User::where('role', 'ips')->inRandomOrder()->first()->id,
            ]);
        }
    }

    private function createSolicitudes()
    {
        $prioridades = ['ROJO', 'AMARILLO', 'VERDE'];
        $estados = ['PENDIENTE', 'EN_PROCESO', 'ACEPTADO', 'NO_ADMITIDO'];

        for ($i = 1; $i <= 80; $i++) {
            SolicitudReferencia::create([
                'registro_medico_id' => rand(1, 100),
                'codigo_solicitud' => 'SOL-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'prioridad' => $prioridades[array_rand($prioridades)],
                'estado' => $estados[array_rand($estados)],
                'fecha_solicitud' => now()->subDays(rand(0, 30)),
                'fecha_limite' => now()->addDays(rand(1, 15)),
                'puntuacion_ia' => rand(1, 10),
                'observaciones_ia' => 'Clasificación automática basada en criterios médicos',
                'procesado_por' => rand(0, 1) ? User::where('role', 'medico')->inRandomOrder()->first()->id : null,
            ]);
        }
    }

    private function createDecisiones()
    {
        $decisiones = ['aceptada', 'rechazada'];
        $justificaciones = [
            'Paciente cumple criterios para referencia especializada',
            'Caso requiere manejo ambulatorio inicial',
            'Necesario completar estudios diagnósticos',
            'Paciente apto para evaluación especializada',
            'Requiere optimización de tratamiento actual'
        ];

        for ($i = 1; $i <= 60; $i++) {
            DecisionReferencia::create([
                'solicitud_referencia_id' => rand(1, 80),
                'decidido_por' => User::where('role', 'medico')->inRandomOrder()->first()->id,
                'decision' => $decisiones[array_rand($decisiones)],
                'observaciones' => $justificaciones[array_rand($justificaciones)],
                'fecha_decision' => now()->subDays(rand(0, 20)),
            ]);
        }
    }

    private function createNotificaciones()
    {
        $tipos = ['solicitud_nueva', 'decision_tomada', 'recordatorio', 'alerta_critica', 'seguimiento'];
        $titulos = [
            'Nueva solicitud de referencia',
            'Solicitud evaluada',
            'Recordatorio de seguimiento',
            'Alerta crítica del sistema',
            'Actualización de seguimiento'
        ];

        for ($i = 1; $i <= 200; $i++) {
            Notificacion::create([
                'user_id' => User::inRandomOrder()->first()->id,
                'tipo' => $tipos[array_rand($tipos)],
                'titulo' => $titulos[array_rand($titulos)],
                'mensaje' => 'Mensaje de notificación número ' . $i . ' generado automáticamente',
                'datos' => json_encode(['id' => $i, 'timestamp' => now()]),
                'leida' => rand(0, 1),
                'prioridad' => ['baja', 'media', 'alta'][rand(0, 2)],
            ]);
        }
    }

    private function createSeguimientos()
    {
        $estados = ['activo', 'completado', 'cancelado'];
        
        for ($i = 1; $i <= 50; $i++) {
            SeguimientoPaciente::create([
                'solicitud_referencia_id' => rand(1, 80),
                'medico_id' => User::where('role', 'medico')->inRandomOrder()->first()->id,
                'estado' => $estados[array_rand($estados)],
                'observaciones' => 'Seguimiento número ' . $i . ' - Evolución favorable del paciente',
                'proxima_cita' => now()->addDays(rand(7, 30)),
            ]);
        }
    }

    private function createEventosAuditoria()
    {
        $eventos = ['login', 'logout', 'crear_solicitud', 'evaluar_solicitud', 'modificar_usuario'];
        
        for ($i = 1; $i <= 100; $i++) {
            EventoAuditoria::create([
                'user_id' => User::inRandomOrder()->first()->id,
                'evento' => $eventos[array_rand($eventos)],
                'descripcion' => 'Evento de auditoría número ' . $i,
                'ip_address' => '192.168.1.' . rand(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'datos_adicionales' => json_encode(['evento_id' => $i, 'timestamp' => now()]),
            ]);
        }
    }

    private function createRecursos()
    {
        $tipos = ['cama_uci', 'cama_hospitalizacion', 'quirofano', 'ambulancia', 'equipo_medico'];
        $nombres = [
            'Cama UCI Adultos', 'Cama Hospitalización General', 'Quirófano Cirugía General',
            'Ambulancia Básica', 'Ventilador Mecánico', 'Monitor Cardíaco', 'Ecógrafo',
            'Rayos X Portátil', 'Desfibrilador', 'Bomba de Infusión'
        ];

        for ($i = 1; $i <= 20; $i++) {
            Recurso::create([
                'nombre' => $nombres[array_rand($nombres)] . ' ' . $i,
                'tipo' => $tipos[array_rand($tipos)],
                'disponible' => rand(0, 1),
                'ubicacion' => 'Piso ' . rand(1, 5) . ' - Ala ' . ['A', 'B', 'C'][rand(0, 2)],
                'descripcion' => 'Recurso médico número ' . $i . ' disponible para uso hospitalario',
            ]);
        }
    }

    private function createHistoriales()
    {
        for ($i = 1; $i <= 40; $i++) {
            HistorialPaciente::create([
                'registro_medico_id' => rand(1, 100),
                'fecha_evento' => now()->subDays(rand(0, 365)),
                'tipo_evento' => ['consulta', 'hospitalizacion', 'cirugia', 'urgencias'][rand(0, 3)],
                'descripcion' => 'Evento médico número ' . $i . ' en el historial del paciente',
                'medico_responsable' => User::where('role', 'medico')->inRandomOrder()->first()->name,
                'diagnostico' => 'Diagnóstico del evento número ' . $i,
            ]);
        }
    }
}