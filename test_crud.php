<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CRUD TEST TODAS LAS TABLAS ===\n";

try {
    // 1. USERS
    echo "1. USERS: ";
    $u = App\Models\User::create([
        'name' => 'Test User',
        'email' => 'test' . time() . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'medico',
        'is_active' => 1,
        'telefono' => '+57300123456'
    ]);
    $u->update(['name' => 'Updated User']);
    $u->delete();
    echo "OK\n";

    // 2. REGISTROS MEDICOS
    echo "2. REGISTROS MEDICOS: ";
    $r = App\Models\RegistroMedico::create([
        'tipo_identificacion' => 'CC',
        'numero_identificacion' => '12345678',
        'nombre' => 'Test',
        'apellidos' => 'Patient',
        'fecha_nacimiento' => now()->subYears(30),
        'edad' => 30,
        'sexo' => 'M',
        'user_id' => 1,
        'asegurador' => 'EPS Test',
        'departamento' => 'Cundinamarca',
        'ciudad' => 'Bogotá',
        'institucion_remitente' => 'Hospital Test',
        'motivo_consulta' => 'Test consultation'
    ]);
    $r->update(['nombre' => 'Updated Patient']);
    $r->delete();
    echo "OK\n";

    // 3. SOLICITUDES REFERENCIA
    echo "3. SOLICITUDES: ";
    $s = App\Models\SolicitudReferencia::create([
        'registro_medico_id' => 1,
        'codigo_solicitud' => 'TEST-' . time(),
        'prioridad' => 'VERDE',
        'estado' => 'PENDIENTE',
        'fecha_solicitud' => now()
    ]);
    $s->update(['estado' => 'EN_PROCESO']);
    $s->delete();
    echo "OK\n";

    // 4. NOTIFICACIONES
    echo "4. NOTIFICACIONES: ";
    $n = App\Models\Notificacion::create([
        'user_id' => 1,
        'tipo' => 'test',
        'titulo' => 'Test Notification',
        'mensaje' => 'Test message',
        'prioridad' => 'media'
    ]);
    $n->update(['titulo' => 'Updated Notification']);
    $n->delete();
    echo "OK\n";

    // 5. DECISIONES
    echo "5. DECISIONES: ";
    $d = App\Models\DecisionReferencia::create([
        'solicitud_referencia_id' => 1,
        'decidido_por' => 1,
        'decision' => 'aceptada',
        'observaciones' => 'Test decision'
    ]);
    $d->update(['decision' => 'rechazada']);
    $d->delete();
    echo "OK\n";

    // 6. IPS
    echo "6. IPS: ";
    $i = App\Models\IPS::create([
        'codigo_prestador' => 'TEST' . time(),
        'nombre' => 'Test IPS',
        'nit' => '123456789-1',
        'tipo_ips' => 'HOSPITAL',
        'departamento' => 'Test',
        'municipio' => 'Test',
        'direccion' => 'Test Address',
        'telefono' => '1234567890',
        'email' => 'test@ips.com',
        'activa' => true,
        'acepta_referencias' => true,
        'capacidad_diaria' => 50,
        'fecha_registro' => now()
    ]);
    $i->update(['nombre' => 'Updated IPS']);
    $i->delete();
    echo "OK\n";

    // 7. SEGUIMIENTO PACIENTES
    echo "7. SEGUIMIENTOS: ";
    $seg = App\Models\SeguimientoPaciente::create([
        'solicitud_referencia_id' => 1,
        'medico_id' => 1,
        'estado' => 'activo',
        'observaciones' => 'Test follow-up'
    ]);
    $seg->update(['estado' => 'completado']);
    $seg->delete();
    echo "OK\n";

    // 8. EVENTOS AUDITORIA
    echo "8. AUDITORIA: ";
    $audit = App\Models\EventoAuditoria::create([
        'user_id' => 1,
        'evento' => 'test_event',
        'descripcion' => 'Test audit event',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent'
    ]);
    $audit->update(['descripcion' => 'Updated audit event']);
    $audit->delete();
    echo "OK\n";

    echo "\n=== TODOS LOS CRUD FUNCIONAN CORRECTAMENTE ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}