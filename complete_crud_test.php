<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST CRUD COMPLETO - TODAS LAS TABLAS ===\n\n";

$results = [];

// 1. USERS
echo "1. TESTING USERS...\n";
try {
    $user = App\Models\User::create([
        'name' => 'Test User CRUD',
        'email' => 'testcrud' . time() . '@test.com',
        'telefono' => '+573001234567',
        'password' => bcrypt('password'),
        'role' => 'medico',
        'is_active' => true
    ]);
    echo "   CREATE: ✅ ID: {$user->id}\n";
    
    $user->update(['name' => 'Updated User CRUD']);
    echo "   UPDATE: ✅ Name: {$user->fresh()->name}\n";
    
    $found = App\Models\User::find($user->id);
    echo "   READ: ✅ Found: {$found->name}\n";
    
    $user->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['users'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['users'] = 'FAIL';
}

// 2. IPS
echo "\n2. TESTING IPS...\n";
try {
    $ips = App\Models\IPS::create([
        'codigo_prestador' => 'TEST' . time(),
        'nombre' => 'Test IPS CRUD',
        'nit' => '123456789-1',
        'tipo_ips' => 'HOSPITAL',
        'departamento' => 'Cundinamarca',
        'municipio' => 'Bogotá',
        'direccion' => 'Test Address 123',
        'telefono' => '3001234567',
        'email' => 'testips@test.com',
        'activa' => true,
        'acepta_referencias' => true,
        'capacidad_diaria' => 50,
        'fecha_registro' => now()
    ]);
    echo "   CREATE: ✅ ID: {$ips->id}\n";
    
    $ips->update(['nombre' => 'Updated IPS CRUD']);
    echo "   UPDATE: ✅ Name: {$ips->fresh()->nombre}\n";
    
    $found = App\Models\IPS::find($ips->id);
    echo "   READ: ✅ Found: {$found->nombre}\n";
    
    $ips->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['ips'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['ips'] = 'FAIL';
}

// 3. PACIENTES
echo "\n3. TESTING PACIENTES...\n";
try {
    $paciente = App\Models\Paciente::create([
        'nombre' => 'Test',
        'apellidos' => 'Patient CRUD',
        'documento' => '12345678' . rand(10, 99),
        'tipo_documento' => 'CC',
        'fecha_nacimiento' => now()->subYears(30),
        'telefono' => '3001234567',
        'email' => 'patient@test.com'
    ]);
    echo "   CREATE: ✅ ID: {$paciente->id}\n";
    
    $paciente->update(['nombre' => 'Updated Patient']);
    echo "   UPDATE: ✅ Name: {$paciente->fresh()->nombre}\n";
    
    $found = App\Models\Paciente::find($paciente->id);
    echo "   READ: ✅ Found: {$found->nombre}\n";
    
    $paciente->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['pacientes'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['pacientes'] = 'FAIL';
}

// 4. NOTIFICACIONES
echo "\n4. TESTING NOTIFICACIONES...\n";
try {
    $notif = App\Models\Notificacion::create([
        'user_id' => 1,
        'tipo' => 'test_crud',
        'titulo' => 'Test Notification CRUD',
        'mensaje' => 'Test message for CRUD operations',
        'prioridad' => 'media'
    ]);
    echo "   CREATE: ✅ ID: {$notif->id}\n";
    
    $notif->update(['titulo' => 'Updated Notification CRUD']);
    echo "   UPDATE: ✅ Title: {$notif->fresh()->titulo}\n";
    
    $found = App\Models\Notificacion::find($notif->id);
    echo "   READ: ✅ Found: {$found->titulo}\n";
    
    $notif->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['notificaciones'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['notificaciones'] = 'FAIL';
}

// 5. EVENTOS AUDITORIA
echo "\n5. TESTING EVENTOS AUDITORIA...\n";
try {
    $audit = App\Models\EventoAuditoria::create([
        'user_id' => 1,
        'evento' => 'test_crud',
        'descripcion' => 'Test audit event CRUD',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent CRUD'
    ]);
    echo "   CREATE: ✅ ID: {$audit->id}\n";
    
    $audit->update(['descripcion' => 'Updated audit event CRUD']);
    echo "   UPDATE: ✅ Description: {$audit->fresh()->descripcion}\n";
    
    $found = App\Models\EventoAuditoria::find($audit->id);
    echo "   READ: ✅ Found: {$found->descripcion}\n";
    
    $audit->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['auditoria'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['auditoria'] = 'FAIL';
}

// 6. CONFIGURACION IA
echo "\n6. TESTING CONFIGURACION IA...\n";
try {
    $config = App\Models\ConfiguracionIA::create([
        'nombre' => 'test_config_crud',
        'valor' => 'test_value_crud',
        'descripcion' => 'Test configuration for CRUD'
    ]);
    echo "   CREATE: ✅ ID: {$config->id}\n";
    
    $config->update(['valor' => 'updated_value_crud']);
    echo "   UPDATE: ✅ Value: {$config->fresh()->valor}\n";
    
    $found = App\Models\ConfiguracionIA::find($config->id);
    echo "   READ: ✅ Found: {$found->valor}\n";
    
    $config->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['config_ia'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['config_ia'] = 'FAIL';
}

// 7. USER PERMISSIONS
echo "\n7. TESTING USER PERMISSIONS...\n";
try {
    $perm = App\Models\UserPermission::create([
        'user_id' => 1,
        'permission' => 'test.crud.permission',
        'granted' => true
    ]);
    echo "   CREATE: ✅ ID: {$perm->id}\n";
    
    $perm->update(['granted' => false]);
    echo "   UPDATE: ✅ Granted: " . ($perm->fresh()->granted ? 'true' : 'false') . "\n";
    
    $found = App\Models\UserPermission::find($perm->id);
    echo "   READ: ✅ Found: {$found->permission}\n";
    
    $perm->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['permissions'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['permissions'] = 'FAIL';
}

// 8. RECURSOS
echo "\n8. TESTING RECURSOS...\n";
try {
    $recurso = App\Models\Recurso::create([
        'nombre' => 'Test Resource CRUD',
        'tipo' => 'test_type',
        'disponible' => true,
        'ubicacion' => 'Test Location',
        'descripcion' => 'Test resource for CRUD operations'
    ]);
    echo "   CREATE: ✅ ID: {$recurso->id}\n";
    
    $recurso->update(['nombre' => 'Updated Resource CRUD']);
    echo "   UPDATE: ✅ Name: {$recurso->fresh()->nombre}\n";
    
    $found = App\Models\Recurso::find($recurso->id);
    echo "   READ: ✅ Found: {$found->nombre}\n";
    
    $recurso->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['recursos'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['recursos'] = 'FAIL';
}

// 9. CRITICAL ALERTS
echo "\n9. TESTING CRITICAL ALERTS...\n";
try {
    $alert = App\Models\CriticalAlert::create([
        'titulo' => 'Test Critical Alert CRUD',
        'mensaje' => 'Test critical alert message',
        'nivel' => 'high',
        'activa' => true
    ]);
    echo "   CREATE: ✅ ID: {$alert->id}\n";
    
    $alert->update(['titulo' => 'Updated Critical Alert CRUD']);
    echo "   UPDATE: ✅ Title: {$alert->fresh()->titulo}\n";
    
    $found = App\Models\CriticalAlert::find($alert->id);
    echo "   READ: ✅ Found: {$found->titulo}\n";
    
    $alert->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['critical_alerts'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['critical_alerts'] = 'FAIL';
}

// 10. SYSTEM METRICS
echo "\n10. TESTING SYSTEM METRICS...\n";
try {
    $metric = App\Models\SystemMetrics::create([
        'metrica' => 'test_metric_crud',
        'valor' => 100.5,
        'fecha' => now(),
        'tipo' => 'test'
    ]);
    echo "   CREATE: ✅ ID: {$metric->id}\n";
    
    $metric->update(['valor' => 200.5]);
    echo "   UPDATE: ✅ Value: {$metric->fresh()->valor}\n";
    
    $found = App\Models\SystemMetrics::find($metric->id);
    echo "   READ: ✅ Found: {$found->metrica}\n";
    
    $metric->delete();
    echo "   DELETE: ✅ Deleted\n";
    $results['system_metrics'] = 'OK';
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    $results['system_metrics'] = 'FAIL';
}

// RESUMEN FINAL
echo "\n" . str_repeat("=", 50) . "\n";
echo "RESUMEN FINAL DE PRUEBAS CRUD:\n";
echo str_repeat("=", 50) . "\n";

$total = count($results);
$passed = count(array_filter($results, fn($r) => $r === 'OK'));
$failed = $total - $passed;

foreach ($results as $table => $status) {
    $icon = $status === 'OK' ? '✅' : '❌';
    echo sprintf("%-20s %s %s\n", strtoupper($table), $icon, $status);
}

echo str_repeat("-", 50) . "\n";
echo sprintf("TOTAL: %d | PASSED: %d | FAILED: %d\n", $total, $passed, $failed);
echo sprintf("SUCCESS RATE: %.1f%%\n", ($passed / $total) * 100);
echo str_repeat("=", 50) . "\n";

if ($failed === 0) {
    echo "🎉 TODOS LOS CRUD FUNCIONAN PERFECTAMENTE! 🎉\n";
} else {
    echo "⚠️  ALGUNAS TABLAS NECESITAN REVISIÓN ⚠️\n";
}