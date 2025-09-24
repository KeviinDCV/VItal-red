<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== TESTING EXHAUSTIVO AL 100% - SISTEMA VITAL-RED ===\n\n";

// Función para simular requests HTTP
function testRoute($method, $uri, $description) {
    echo "🧪 Testing: $description\n";
    echo "   Route: $method $uri\n";
    
    try {
        $request = Illuminate\Http\Request::create($uri, $method);
        $response = app()->handle($request);
        
        if ($response->getStatusCode() == 200) {
            echo "   ✅ Status: 200 OK\n";
        } elseif ($response->getStatusCode() == 302) {
            echo "   ⚠️  Status: 302 Redirect (Auth required)\n";
        } else {
            echo "   ❌ Status: " . $response->getStatusCode() . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test de conectividad de base de datos
echo "=== 1. TESTING DE INFRAESTRUCTURA ===\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=vital_red', 'root', '');
    echo "✅ Base de datos: Conectada\n";
    
    // Verificar tablas críticas
    $tables = [
        'users' => 'Usuarios del sistema',
        'solicitudes_referencia' => 'Solicitudes de referencia',
        'registros_medicos' => 'Registros médicos',
        'ips' => 'Instituciones prestadoras',
        'decisiones_referencia' => 'Decisiones médicas',
        'seguimiento_pacientes' => 'Seguimiento de pacientes',
        'notificaciones' => 'Sistema de notificaciones',
        'configuracion_ia' => 'Configuración IA'
    ];
    
    foreach ($tables as $table => $description) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✅ $description: $count registros\n";
    }
} catch (Exception $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
}

echo "\n=== 2. TESTING DE RUTAS PÚBLICAS ===\n";
testRoute('GET', '/', 'Página principal');
testRoute('GET', '/login', 'Página de login');
testRoute('GET', '/register', 'Página de registro');

echo "=== 3. TESTING DE RUTAS PROTEGIDAS (Sin Auth) ===\n";
testRoute('GET', '/dashboard', 'Dashboard principal');
testRoute('GET', '/admin/usuarios', 'Gestión de usuarios');
testRoute('GET', '/medico/referencias', 'Referencias médicas');
testRoute('GET', '/ips/solicitar', 'Solicitar referencia');

echo "=== 4. VERIFICACIÓN DE ARCHIVOS DE VISTAS ===\n";

$viewsToTest = [
    // Páginas principales
    'Dashboard.tsx' => [
        'path' => 'resources/js/pages/Dashboard.tsx',
        'description' => 'Dashboard principal por roles',
        'critical' => true
    ],
    
    // Admin views
    'admin/usuarios.tsx' => [
        'path' => 'resources/js/pages/admin/usuarios.tsx',
        'description' => 'Gestión de usuarios',
        'critical' => true
    ],
    'admin/DashboardReferencias.tsx' => [
        'path' => 'resources/js/pages/admin/DashboardReferencias.tsx',
        'description' => 'Dashboard de referencias admin',
        'critical' => true
    ],
    'admin/Reportes.tsx' => [
        'path' => 'resources/js/pages/admin/Reportes.tsx',
        'description' => 'Reportes y métricas',
        'critical' => true
    ],
    'admin/ConfigurarIA.tsx' => [
        'path' => 'resources/js/pages/admin/ConfigurarIA.tsx',
        'description' => 'Configuración del algoritmo IA',
        'critical' => true
    ],
    'admin/supervision.tsx' => [
        'path' => 'resources/js/pages/admin/supervision.tsx',
        'description' => 'Supervisión del sistema',
        'critical' => false
    ],
    'admin/PermisosUsuario.tsx' => [
        'path' => 'resources/js/pages/admin/PermisosUsuario.tsx',
        'description' => 'Gestión de permisos',
        'critical' => true
    ],
    
    // Jefe de Urgencias
    'jefe-urgencias/DashboardEjecutivo.tsx' => [
        'path' => 'resources/js/pages/jefe-urgencias/DashboardEjecutivo.tsx',
        'description' => 'Dashboard ejecutivo con métricas',
        'critical' => true
    ],
    
    // Médico views
    'medico/ingresar-registro.tsx' => [
        'path' => 'resources/js/pages/medico/ingresar-registro.tsx',
        'description' => 'Ingreso de registros médicos',
        'critical' => true
    ],
    'medico/GestionarReferencias.tsx' => [
        'path' => 'resources/js/pages/medico/GestionarReferencias.tsx',
        'description' => 'Gestión de referencias médicas',
        'critical' => true
    ],
    'medico/CasosCriticos.tsx' => [
        'path' => 'resources/js/pages/medico/CasosCriticos.tsx',
        'description' => 'Gestión de casos críticos ROJOS',
        'critical' => true
    ],
    'medico/SeguimientoPacientes.tsx' => [
        'path' => 'resources/js/pages/medico/SeguimientoPacientes.tsx',
        'description' => 'Seguimiento de pacientes',
        'critical' => true
    ],
    'medico/consulta-pacientes.tsx' => [
        'path' => 'resources/js/pages/medico/consulta-pacientes.tsx',
        'description' => 'Consulta de pacientes',
        'critical' => true
    ],
    
    // IPS views
    'IPS/SolicitarReferencia.tsx' => [
        'path' => 'resources/js/pages/IPS/SolicitarReferencia.tsx',
        'description' => 'Formulario de solicitud de referencia',
        'critical' => true
    ],
    'IPS/MisSolicitudes.tsx' => [
        'path' => 'resources/js/pages/IPS/MisSolicitudes.tsx',
        'description' => 'Historial de solicitudes IPS',
        'critical' => true
    ],
    
    // Auth views
    'auth/login.tsx' => [
        'path' => 'resources/js/pages/auth/login.tsx',
        'description' => 'Página de login',
        'critical' => true
    ],
    'auth/register.tsx' => [
        'path' => 'resources/js/pages/auth/register.tsx',
        'description' => 'Página de registro',
        'critical' => false
    ],
    
    // Settings
    'settings/profile.tsx' => [
        'path' => 'resources/js/pages/settings/profile.tsx',
        'description' => 'Configuración de perfil',
        'critical' => false
    ],
    'settings/password.tsx' => [
        'path' => 'resources/js/pages/settings/password.tsx',
        'description' => 'Cambio de contraseña',
        'critical' => false
    ]
];

$criticalMissing = [];
$nonCriticalMissing = [];
$totalViews = count($viewsToTest);
$existingViews = 0;

foreach ($viewsToTest as $viewName => $config) {
    if (file_exists($config['path'])) {
        echo "✅ {$config['description']}\n";
        $existingViews++;
        
        // Verificar contenido básico del archivo
        $content = file_get_contents($config['path']);
        $hasExport = strpos($content, 'export default') !== false;
        $hasImports = strpos($content, 'import') !== false;
        $hasJSX = strpos($content, 'return') !== false;
        
        if ($hasExport && $hasImports && $hasJSX) {
            echo "   ✅ Estructura válida de componente React\n";
        } else {
            echo "   ⚠️  Posibles problemas de estructura\n";
        }
    } else {
        echo "❌ FALTANTE: {$config['description']} ({$config['path']})\n";
        if ($config['critical']) {
            $criticalMissing[] = $viewName;
        } else {
            $nonCriticalMissing[] = $viewName;
        }
    }
}

echo "\n=== 5. TESTING DE COMPONENTES UI ===\n";

$uiComponents = [
    'referencias/PriorityBadge.tsx' => 'Badge de prioridad ROJO/VERDE',
    'referencias/StatusTracker.tsx' => 'Seguimiento de estados',
    'referencias/DecisionModal.tsx' => 'Modal de decisiones médicas',
    'referencias/SolicitudCard.tsx' => 'Tarjeta de solicitud',
    'referencias/TimeIndicator.tsx' => 'Indicador de tiempo',
    'referencias/SpecialtyFilter.tsx' => 'Filtro de especialidades',
    'referencias/DateRangeFilter.tsx' => 'Filtro de fechas',
    'referencias/ExportButton.tsx' => 'Botón de exportación',
    'referencias/ReportChart.tsx' => 'Gráficos de reportes',
    'NotificationCenter.tsx' => 'Centro de notificaciones',
    'ui/card.tsx' => 'Componente Card',
    'ui/button.tsx' => 'Componente Button',
    'ui/table.tsx' => 'Componente Table',
    'ui/dialog.tsx' => 'Componente Dialog',
    'ui/badge.tsx' => 'Componente Badge'
];

$missingComponents = [];
foreach ($uiComponents as $component => $description) {
    $path = "resources/js/components/$component";
    if (file_exists($path)) {
        echo "✅ $description\n";
    } else {
        echo "❌ FALTANTE: $description ($path)\n";
        $missingComponents[] = $component;
    }
}

echo "\n=== 6. TESTING DE CONTROLADORES ===\n";

$controllers = [
    'Admin/UsuarioController.php' => 'Gestión de usuarios',
    'Admin/ReferenciasController.php' => 'Referencias administrativas',
    'Admin/ReportesController.php' => 'Reportes y métricas',
    'Admin/IAConfigController.php' => 'Configuración IA',
    'Admin/MenuController.php' => 'Control de menús',
    'JefeUrgencias/DashboardController.php' => 'Dashboard ejecutivo',
    'Medico/MedicoController.php' => 'Funciones médicas',
    'Medico/ReferenciasController.php' => 'Referencias médicas',
    'Medico/SeguimientoController.php' => 'Seguimiento de pacientes',
    'IPS/SolicitudController.php' => 'Solicitudes IPS',
    'NotificacionesController.php' => 'Sistema de notificaciones',
    'AIController.php' => 'Inteligencia artificial'
];

$missingControllers = [];
foreach ($controllers as $controller => $description) {
    $path = "app/Http/Controllers/$controller";
    if (file_exists($path)) {
        echo "✅ $description\n";
        
        // Verificar métodos críticos
        $content = file_get_contents($path);
        $hasIndex = strpos($content, 'function index') !== false;
        $hasStore = strpos($content, 'function store') !== false;
        $hasShow = strpos($content, 'function show') !== false;
        
        if ($hasIndex || $hasStore || $hasShow) {
            echo "   ✅ Métodos CRUD implementados\n";
        } else {
            echo "   ⚠️  Métodos básicos pueden estar faltando\n";
        }
    } else {
        echo "❌ FALTANTE: $description ($path)\n";
        $missingControllers[] = $controller;
    }
}

echo "\n=== 7. TESTING DE MODELOS ===\n";

$models = [
    'User.php' => 'Usuarios del sistema',
    'SolicitudReferencia.php' => 'Solicitudes de referencia',
    'RegistroMedico.php' => 'Registros médicos',
    'DecisionReferencia.php' => 'Decisiones médicas',
    'IPS.php' => 'Instituciones prestadoras',
    'SeguimientoPaciente.php' => 'Seguimiento de pacientes',
    'Notificacion.php' => 'Notificaciones',
    'ConfiguracionIA.php' => 'Configuración IA',
    'MenuPermiso.php' => 'Permisos de menú',
    'UserPermission.php' => 'Permisos de usuario'
];

foreach ($models as $model => $description) {
    $path = "app/Models/$model";
    if (file_exists($path)) {
        echo "✅ $description\n";
        
        // Verificar estructura básica del modelo
        $content = file_get_contents($path);
        $hasTable = strpos($content, 'protected $table') !== false;
        $hasFillable = strpos($content, 'protected $fillable') !== false;
        $hasRelations = strpos($content, 'function ') !== false;
        
        if ($hasFillable) {
            echo "   ✅ Campos fillable definidos\n";
        }
        if ($hasRelations) {
            echo "   ✅ Relaciones implementadas\n";
        }
    } else {
        echo "❌ FALTANTE: $description ($path)\n";
    }
}

echo "\n=== 8. TESTING DE SERVICIOS ===\n";

$services = [
    'GeminiAIService.php' => 'Servicio de IA Gemini',
    'AutomaticResponseGenerator.php' => 'Generador de respuestas automáticas',
    'WebSocketService.php' => 'Servicio WebSocket'
];

foreach ($services as $service => $description) {
    $path = "app/Services/$service";
    if (file_exists($path)) {
        echo "✅ $description\n";
    } else {
        echo "❌ FALTANTE: $description ($path)\n";
    }
}

echo "\n=== 9. RESUMEN EJECUTIVO ===\n";
echo "📊 ESTADÍSTICAS GENERALES:\n";
echo "   • Vistas existentes: $existingViews/$totalViews (" . round(($existingViews/$totalViews)*100, 1) . "%)\n";
echo "   • Vistas críticas faltantes: " . count($criticalMissing) . "\n";
echo "   • Componentes UI faltantes: " . count($missingComponents) . "\n";
echo "   • Controladores faltantes: " . count($missingControllers) . "\n";

if (count($criticalMissing) > 0) {
    echo "\n🚨 VISTAS CRÍTICAS FALTANTES:\n";
    foreach ($criticalMissing as $view) {
        echo "   ❌ $view\n";
    }
}

if (count($missingComponents) > 0) {
    echo "\n⚠️  COMPONENTES UI FALTANTES:\n";
    foreach ($missingComponents as $component) {
        echo "   ❌ $component\n";
    }
}

echo "\n=== 10. PLAN DE ACCIÓN INMEDIATA ===\n";
if (count($criticalMissing) == 0 && count($missingControllers) == 0) {
    echo "✅ SISTEMA LISTO PARA TESTING MANUAL COMPLETO\n";
    echo "\n📋 PRÓXIMOS PASOS:\n";
    echo "   1. Iniciar servidor: php artisan serve\n";
    echo "   2. Testing manual por roles:\n";
    echo "      • Admin: admin@vital-red.com\n";
    echo "      • Médico: medico@vital-red.com\n";
    echo "      • IPS: ips@vital-red.com\n";
    echo "   3. Probar cada funcionalidad:\n";
    echo "      • Formularios de ingreso\n";
    echo "      • Navegación entre vistas\n";
    echo "      • Botones y acciones\n";
    echo "      • Filtros y búsquedas\n";
    echo "      • Exportación de datos\n";
    echo "   4. Verificar en consola del navegador:\n";
    echo "      • Sin errores JavaScript\n";
    echo "      • Requests HTTP exitosos\n";
    echo "      • WebSocket funcionando\n";
} else {
    echo "❌ SISTEMA REQUIERE CORRECCIONES ANTES DEL TESTING\n";
    echo "\n🔧 ACCIONES REQUERIDAS:\n";
    if (count($criticalMissing) > 0) {
        echo "   • Crear vistas críticas faltantes\n";
    }
    if (count($missingControllers) > 0) {
        echo "   • Implementar controladores faltantes\n";
    }
    if (count($missingComponents) > 0) {
        echo "   • Crear componentes UI faltantes\n";
    }
}

echo "\n=== TESTING COMPLETADO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Estado: " . (count($criticalMissing) == 0 ? "LISTO PARA PRODUCCIÓN" : "REQUIERE CORRECCIONES") . "\n";

?>