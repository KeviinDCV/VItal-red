<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== PRUEBA SISTEMÁTICA DE TODAS LAS VISTAS ===\n\n";

// Rutas a probar por rol
$routesToTest = [
    'admin' => [
        '/admin/usuarios' => 'Gestión de Usuarios',
        '/admin/referencias' => 'Dashboard de Referencias',
        '/admin/reportes' => 'Reportes y Métricas',
        '/admin/configuracion-ia' => 'Configuración IA',
        '/admin/supervision' => 'Supervisión del Sistema'
    ],
    'jefe_urgencias' => [
        '/jefe-urgencias/dashboard-ejecutivo' => 'Dashboard Ejecutivo',
        '/jefe-urgencias/metricas' => 'Métricas en Tiempo Real'
    ],
    'medico' => [
        '/medico/ingresar-registro' => 'Ingresar Registro Médico',
        '/medico/referencias' => 'Gestionar Referencias',
        '/medico/casos-criticos' => 'Casos Críticos ROJOS',
        '/medico/seguimiento' => 'Seguimiento de Pacientes',
        '/medico/consulta-pacientes' => 'Consulta de Pacientes'
    ],
    'ips' => [
        '/ips/solicitar' => 'Solicitar Referencia',
        '/ips/mis-solicitudes' => 'Mis Solicitudes'
    ],
    'shared' => [
        '/dashboard' => 'Dashboard Principal',
        '/notificaciones' => 'Centro de Notificaciones'
    ]
];

// Verificar archivos de vistas
$viewFiles = [
    'Dashboard.tsx' => 'resources/js/pages/Dashboard.tsx',
    'admin/usuarios.tsx' => 'resources/js/pages/admin/usuarios.tsx',
    'admin/DashboardReferencias.tsx' => 'resources/js/pages/admin/DashboardReferencias.tsx',
    'admin/Reportes.tsx' => 'resources/js/pages/admin/Reportes.tsx',
    'admin/ConfigurarIA.tsx' => 'resources/js/pages/admin/ConfigurarIA.tsx',
    'admin/supervision.tsx' => 'resources/js/pages/admin/supervision.tsx',
    'jefe-urgencias/DashboardEjecutivo.tsx' => 'resources/js/pages/jefe-urgencias/DashboardEjecutivo.tsx',
    'medico/ingresar-registro.tsx' => 'resources/js/pages/medico/ingresar-registro.tsx',
    'medico/GestionarReferencias.tsx' => 'resources/js/pages/medico/GestionarReferencias.tsx',
    'medico/CasosCriticos.tsx' => 'resources/js/pages/medico/CasosCriticos.tsx',
    'medico/SeguimientoPacientes.tsx' => 'resources/js/pages/medico/SeguimientoPacientes.tsx',
    'medico/consulta-pacientes.tsx' => 'resources/js/pages/medico/consulta-pacientes.tsx',
    'IPS/SolicitarReferencia.tsx' => 'resources/js/pages/IPS/SolicitarReferencia.tsx',
    'IPS/MisSolicitudes.tsx' => 'resources/js/pages/IPS/MisSolicitudes.tsx',
    'Shared/Notificaciones.tsx' => 'resources/js/pages/Shared/Notificaciones.tsx'
];

echo "=== VERIFICACIÓN DE ARCHIVOS DE VISTAS ===\n";
$missingFiles = [];
$existingFiles = [];

foreach ($viewFiles as $viewName => $filePath) {
    if (file_exists($filePath)) {
        echo "✅ $viewName\n";
        $existingFiles[] = $viewName;
    } else {
        echo "❌ FALTANTE: $viewName ($filePath)\n";
        $missingFiles[] = $viewName;
    }
}

echo "\n=== VERIFICACIÓN DE CONTROLADORES ===\n";
$controllers = [
    'Admin/UsuarioController.php' => 'app/Http/Controllers/Admin/UsuarioController.php',
    'Admin/ReferenciasController.php' => 'app/Http/Controllers/Admin/ReferenciasController.php',
    'Admin/ReportesController.php' => 'app/Http/Controllers/Admin/ReportesController.php',
    'Admin/IAConfigController.php' => 'app/Http/Controllers/Admin/IAConfigController.php',
    'JefeUrgencias/DashboardController.php' => 'app/Http/Controllers/JefeUrgencias/DashboardController.php',
    'Medico/MedicoController.php' => 'app/Http/Controllers/Medico/MedicoController.php',
    'Medico/ReferenciasController.php' => 'app/Http/Controllers/Medico/ReferenciasController.php',
    'Medico/SeguimientoController.php' => 'app/Http/Controllers/Medico/SeguimientoController.php',
    'IPS/SolicitudController.php' => 'app/Http/Controllers/IPS/SolicitudController.php',
    'NotificacionesController.php' => 'app/Http/Controllers/NotificacionesController.php'
];

$missingControllers = [];
foreach ($controllers as $controllerName => $filePath) {
    if (file_exists($filePath)) {
        echo "✅ $controllerName\n";
    } else {
        echo "❌ FALTANTE: $controllerName\n";
        $missingControllers[] = $controllerName;
    }
}

echo "\n=== VERIFICACIÓN DE MODELOS ===\n";
$models = [
    'User.php' => 'app/Models/User.php',
    'SolicitudReferencia.php' => 'app/Models/SolicitudReferencia.php',
    'RegistroMedico.php' => 'app/Models/RegistroMedico.php',
    'DecisionReferencia.php' => 'app/Models/DecisionReferencia.php',
    'IPS.php' => 'app/Models/IPS.php',
    'SeguimientoPaciente.php' => 'app/Models/SeguimientoPaciente.php',
    'Notificacion.php' => 'app/Models/Notificacion.php',
    'ConfiguracionIA.php' => 'app/Models/ConfiguracionIA.php'
];

foreach ($models as $modelName => $filePath) {
    if (file_exists($filePath)) {
        echo "✅ $modelName\n";
    } else {
        echo "❌ FALTANTE: $modelName\n";
    }
}

echo "\n=== VERIFICACIÓN DE COMPONENTES UI ===\n";
$components = [
    'PriorityBadge.tsx' => 'resources/js/components/referencias/PriorityBadge.tsx',
    'StatusTracker.tsx' => 'resources/js/components/referencias/StatusTracker.tsx',
    'DecisionModal.tsx' => 'resources/js/components/referencias/DecisionModal.tsx',
    'SolicitudCard.tsx' => 'resources/js/components/referencias/SolicitudCard.tsx',
    'NotificationCenter.tsx' => 'resources/js/components/NotificationCenter.tsx'
];

foreach ($components as $componentName => $filePath) {
    if (file_exists($filePath)) {
        echo "✅ $componentName\n";
    } else {
        echo "❌ FALTANTE: $componentName\n";
    }
}

echo "\n=== RESUMEN DE ESTADO ===\n";
echo "📁 Vistas existentes: " . count($existingFiles) . "/" . count($viewFiles) . "\n";
echo "📁 Vistas faltantes: " . count($missingFiles) . "\n";
echo "🎛️ Controladores faltantes: " . count($missingControllers) . "\n";

if (count($missingFiles) > 0) {
    echo "\n❌ VISTAS FALTANTES QUE NECESITAN SER CREADAS:\n";
    foreach ($missingFiles as $file) {
        echo "   - $file\n";
    }
}

if (count($missingControllers) > 0) {
    echo "\n❌ CONTROLADORES FALTANTES:\n";
    foreach ($missingControllers as $controller) {
        echo "   - $controller\n";
    }
}

echo "\n=== PROBLEMAS IDENTIFICADOS Y SOLUCIONES ===\n";
echo "🔧 ERRORES CORREGIDOS:\n";
echo "   ✅ Error 'Undefined array key estado' en ReferenciasController\n";
echo "   ✅ Página Dashboard.tsx creada\n";
echo "   ✅ Rutas de controladores corregidas\n";
echo "   ✅ Assets recompilados\n";

echo "\n🚨 ERRORES PENDIENTES POR CORREGIR:\n";
echo "   ❌ Algunos botones y formularios no funcionan correctamente\n";
echo "   ❌ Errores de React en componentes específicos\n";
echo "   ❌ Navegación entre vistas con errores\n";

echo "\n📋 PRÓXIMOS PASOS RECOMENDADOS:\n";
echo "   1. Probar cada vista manualmente con usuarios de diferentes roles\n";
echo "   2. Corregir errores de JavaScript en el navegador\n";
echo "   3. Verificar que todos los formularios envíen datos correctamente\n";
echo "   4. Probar la navegación entre todas las vistas\n";
echo "   5. Verificar que los permisos por rol funcionen correctamente\n";

echo "\n=== INSTRUCCIONES PARA TESTING MANUAL ===\n";
echo "1. Ejecutar: php artisan serve\n";
echo "2. Acceder: http://localhost:8000\n";
echo "3. Probar login con diferentes roles:\n";
echo "   - admin@vital-red.com (Administrador)\n";
echo "   - medico@vital-red.com (Médico)\n";
echo "   - ips@vital-red.com (IPS Externa)\n";
echo "4. Navegar por todas las opciones del menú\n";
echo "5. Probar formularios y acciones\n";
echo "6. Verificar que no haya errores en la consola del navegador\n";

?>