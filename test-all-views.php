<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Crear una instancia de la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Lista de rutas a probar
$routes = [
    // Rutas de autenticación
    ['GET', '/login', 'Login'],
    ['GET', '/register', 'Register'],
    
    // Dashboard principal
    ['GET', '/dashboard', 'Dashboard Principal'],
    
    // Rutas de administrador
    ['GET', '/admin/usuarios', 'Gestión de Usuarios'],
    ['GET', '/admin/supervision', 'Supervisión'],
    ['GET', '/admin/referencias', 'Dashboard Referencias'],
    ['GET', '/admin/reportes', 'Reportes'],
    ['GET', '/admin/configuracion-ia', 'Configuración IA'],
    
    // Rutas de médico
    ['GET', '/medico/ingresar-registro', 'Ingresar Registro'],
    ['GET', '/medico/consulta-pacientes', 'Consulta Pacientes'],
    ['GET', '/medico/casos-criticos', 'Casos Críticos'],
    ['GET', '/medico/seguimiento-completo', 'Seguimiento Pacientes'],
    
    // Rutas de IPS
    ['GET', '/ips/solicitar', 'Solicitar Referencia'],
    ['GET', '/ips/mis-solicitudes', 'Mis Solicitudes'],
    
    // Rutas de Jefe de Urgencias
    ['GET', '/jefe-urgencias/dashboard-ejecutivo', 'Dashboard Ejecutivo'],
    
    // Notificaciones
    ['GET', '/notificaciones', 'Centro de Notificaciones'],
    ['GET', '/notificaciones/completas', 'Notificaciones Completas'],
];

echo "🔍 PROBANDO TODAS LAS VISTAS DEL SISTEMA VITAL RED\n";
echo "=" . str_repeat("=", 60) . "\n\n";

$totalRoutes = count($routes);
$successCount = 0;
$errorCount = 0;
$results = [];

foreach ($routes as $index => $route) {
    $method = $route[0];
    $uri = $route[1];
    $description = $route[2];
    
    echo sprintf("[%d/%d] Probando: %s (%s %s)\n", 
        $index + 1, $totalRoutes, $description, $method, $uri);
    
    try {
        // Crear request
        $request = Request::create($uri, $method);
        
        // Simular usuario autenticado para rutas protegidas
        if (strpos($uri, '/login') === false && strpos($uri, '/register') === false) {
            // Crear un usuario de prueba
            $user = new \App\Models\User();
            $user->id = 1;
            $user->name = 'Usuario Test';
            $user->email = 'test@test.com';
            $user->role = 'administrador';
            
            // Simular autenticación
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        }
        
        // Procesar request
        $response = $kernel->handle($request);
        
        $statusCode = $response->getStatusCode();
        
        if ($statusCode >= 200 && $statusCode < 400) {
            echo "   ✅ ÉXITO - Código: {$statusCode}\n";
            $successCount++;
            $results[] = ['status' => 'success', 'route' => $uri, 'description' => $description, 'code' => $statusCode];
        } else {
            echo "   ⚠️  ADVERTENCIA - Código: {$statusCode}\n";
            $results[] = ['status' => 'warning', 'route' => $uri, 'description' => $description, 'code' => $statusCode];
        }
        
    } catch (Exception $e) {
        echo "   ❌ ERROR - " . $e->getMessage() . "\n";
        $errorCount++;
        $results[] = ['status' => 'error', 'route' => $uri, 'description' => $description, 'error' => $e->getMessage()];
    }
    
    echo "\n";
}

// Resumen final
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RESUMEN DE PRUEBAS\n";
echo str_repeat("=", 60) . "\n";
echo "Total de rutas probadas: {$totalRoutes}\n";
echo "✅ Exitosas: {$successCount}\n";
echo "❌ Con errores: {$errorCount}\n";
echo "⚠️  Advertencias: " . ($totalRoutes - $successCount - $errorCount) . "\n";
echo "📈 Porcentaje de éxito: " . round(($successCount / $totalRoutes) * 100, 2) . "%\n\n";

// Detalles de errores
if ($errorCount > 0) {
    echo "🔍 DETALLES DE ERRORES:\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($results as $result) {
        if ($result['status'] === 'error') {
            echo "❌ {$result['description']} ({$result['route']})\n";
            echo "   Error: {$result['error']}\n\n";
        }
    }
}

// Rutas exitosas
echo "✅ RUTAS FUNCIONANDO CORRECTAMENTE:\n";
echo str_repeat("-", 40) . "\n";
foreach ($results as $result) {
    if ($result['status'] === 'success') {
        echo "✅ {$result['description']} ({$result['route']}) - Código: {$result['code']}\n";
    }
}

echo "\n🎯 RECOMENDACIONES:\n";
echo str_repeat("-", 40) . "\n";

if ($errorCount > 0) {
    echo "1. Revisar y corregir las rutas con errores\n";
    echo "2. Verificar que todos los controladores existan\n";
    echo "3. Comprobar que las vistas React estén correctamente nombradas\n";
}

if ($successCount > ($totalRoutes * 0.8)) {
    echo "🎉 ¡Excelente! La mayoría de las vistas están funcionando correctamente.\n";
} else {
    echo "⚠️  Se necesita trabajo adicional para mejorar la estabilidad del sistema.\n";
}

echo "\n📝 PRÓXIMOS PASOS:\n";
echo "1. Corregir errores identificados\n";
echo "2. Probar funcionalidades específicas de cada vista\n";
echo "3. Verificar la navegación entre vistas\n";
echo "4. Probar con diferentes roles de usuario\n";

echo "\n✨ Prueba completada - " . date('Y-m-d H:i:s') . "\n";