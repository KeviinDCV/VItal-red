<?php

echo "🌐 PROBANDO NAVEGACIÓN FRONTEND - VITAL RED\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Verificar archivos de vistas React
$viewsToCheck = [
    // Dashboard principal
    'resources/js/pages/dashboard.tsx' => 'Dashboard Principal',
    
    // Vistas de administrador
    'resources/js/pages/admin/usuarios.tsx' => 'Gestión de Usuarios',
    'resources/js/pages/admin/supervision.tsx' => 'Supervisión',
    'resources/js/pages/admin/DashboardReferencias.tsx' => 'Dashboard Referencias',
    'resources/js/pages/admin/Reportes.tsx' => 'Reportes',
    'resources/js/pages/admin/ConfigurarIA.tsx' => 'Configuración IA',
    'resources/js/pages/admin/PermisosUsuario.tsx' => 'Permisos de Usuario',
    
    // Vistas de médico
    'resources/js/pages/medico/ingresar-registro.tsx' => 'Ingresar Registro',
    'resources/js/pages/medico/consulta-pacientes.tsx' => 'Consulta Pacientes',
    'resources/js/pages/medico/CasosCriticos.tsx' => 'Casos Críticos',
    'resources/js/pages/medico/GestionarReferencias.tsx' => 'Gestionar Referencias',
    'resources/js/pages/medico/SeguimientoPacientes.tsx' => 'Seguimiento Pacientes',
    
    // Vistas de IPS
    'resources/js/pages/IPS/SolicitarReferencia.tsx' => 'Solicitar Referencia',
    'resources/js/pages/IPS/MisSolicitudes.tsx' => 'Mis Solicitudes',
    
    // Vistas de Jefe de Urgencias
    'resources/js/pages/jefe-urgencias/DashboardEjecutivo.tsx' => 'Dashboard Ejecutivo',
    
    // Vistas compartidas
    'resources/js/pages/Shared/Notificaciones.tsx' => 'Centro de Notificaciones',
    'resources/js/pages/Shared/NotificacionesCompletas.tsx' => 'Notificaciones Completas',
    'resources/js/pages/Shared/FormularioIngreso.tsx' => 'Formulario de Ingreso',
    'resources/js/pages/Shared/TablaGestion.tsx' => 'Tabla de Gestión',
    
    // Vistas de autenticación
    'resources/js/pages/auth/login.tsx' => 'Login',
    'resources/js/pages/auth/register.tsx' => 'Register',
    'resources/js/pages/auth/forgot-password.tsx' => 'Recuperar Contraseña',
    'resources/js/pages/auth/reset-password.tsx' => 'Restablecer Contraseña',
    
    // Vistas de configuración
    'resources/js/pages/settings/profile.tsx' => 'Perfil de Usuario',
    'resources/js/pages/settings/password.tsx' => 'Cambiar Contraseña',
];

$existingViews = 0;
$missingViews = 0;
$viewsWithIssues = [];

echo "📁 VERIFICANDO ARCHIVOS DE VISTAS:\n";
echo str_repeat("-", 40) . "\n";

foreach ($viewsToCheck as $filePath => $description) {
    $fullPath = __DIR__ . '/' . $filePath;
    
    if (file_exists($fullPath)) {
        echo "✅ {$description}\n";
        echo "   📄 {$filePath}\n";
        
        // Verificar contenido básico del archivo
        $content = file_get_contents($fullPath);
        
        // Verificaciones básicas
        $checks = [
            'export default' => 'Exportación por defecto',
            'import' => 'Importaciones',
            'AppLayout' => 'Layout de aplicación',
            'Head' => 'Título de página'
        ];
        
        $issues = [];
        foreach ($checks as $pattern => $checkName) {
            if (strpos($content, $pattern) === false) {
                $issues[] = $checkName;
            }
        }
        
        if (!empty($issues)) {
            $viewsWithIssues[] = [
                'file' => $filePath,
                'description' => $description,
                'issues' => $issues
            ];
            echo "   ⚠️  Posibles problemas: " . implode(', ', $issues) . "\n";
        }
        
        $existingViews++;
    } else {
        echo "❌ {$description}\n";
        echo "   📄 {$filePath} - ARCHIVO FALTANTE\n";
        $missingViews++;
    }
    echo "\n";
}

// Verificar componentes principales
echo "\n🧩 VERIFICANDO COMPONENTES PRINCIPALES:\n";
echo str_repeat("-", 40) . "\n";

$componentsToCheck = [
    'resources/js/layouts/app-layout.tsx' => 'Layout Principal',
    'resources/js/components/app-sidebar.tsx' => 'Sidebar de Aplicación',
    'resources/js/components/nav-main.tsx' => 'Navegación Principal',
    'resources/js/components/NotificationCenter.tsx' => 'Centro de Notificaciones',
    'resources/js/components/ui/button.tsx' => 'Componente Button',
    'resources/js/components/ui/card.tsx' => 'Componente Card',
    'resources/js/components/ui/select.tsx' => 'Componente Select',
    'resources/js/components/ui/input.tsx' => 'Componente Input',
];

$existingComponents = 0;
$missingComponents = 0;

foreach ($componentsToCheck as $filePath => $description) {
    $fullPath = __DIR__ . '/' . $filePath;
    
    if (file_exists($fullPath)) {
        echo "✅ {$description}\n";
        $existingComponents++;
    } else {
        echo "❌ {$description} - FALTANTE\n";
        $missingComponents++;
    }
}

// Verificar configuración de rutas
echo "\n🛣️  VERIFICANDO CONFIGURACIÓN DE RUTAS:\n";
echo str_repeat("-", 40) . "\n";

$routeFiles = [
    'routes/web.php' => 'Rutas Web',
    'routes/auth.php' => 'Rutas de Autenticación',
    'routes/settings.php' => 'Rutas de Configuración',
];

foreach ($routeFiles as $filePath => $description) {
    $fullPath = __DIR__ . '/' . $filePath;
    
    if (file_exists($fullPath)) {
        echo "✅ {$description}\n";
        
        $content = file_get_contents($fullPath);
        $routeCount = substr_count($content, 'Route::');
        echo "   📊 Rutas definidas: {$routeCount}\n";
    } else {
        echo "❌ {$description} - FALTANTE\n";
    }
}

// Resumen final
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RESUMEN DE VERIFICACIÓN FRONTEND\n";
echo str_repeat("=", 50) . "\n";

$totalViews = count($viewsToCheck);
$totalComponents = count($componentsToCheck);

echo "📄 VISTAS:\n";
echo "   Total: {$totalViews}\n";
echo "   ✅ Existentes: {$existingViews}\n";
echo "   ❌ Faltantes: {$missingViews}\n";
echo "   📈 Porcentaje: " . round(($existingViews / $totalViews) * 100, 2) . "%\n\n";

echo "🧩 COMPONENTES:\n";
echo "   Total: {$totalComponents}\n";
echo "   ✅ Existentes: {$existingComponents}\n";
echo "   ❌ Faltantes: {$missingComponents}\n";
echo "   📈 Porcentaje: " . round(($existingComponents / $totalComponents) * 100, 2) . "%\n\n";

if (!empty($viewsWithIssues)) {
    echo "⚠️  VISTAS CON POSIBLES PROBLEMAS:\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($viewsWithIssues as $view) {
        echo "📄 {$view['description']}\n";
        echo "   Archivo: {$view['file']}\n";
        echo "   Problemas: " . implode(', ', $view['issues']) . "\n\n";
    }
}

// Recomendaciones
echo "🎯 RECOMENDACIONES:\n";
echo str_repeat("-", 40) . "\n";

if ($missingViews > 0) {
    echo "1. Crear las vistas faltantes identificadas\n";
}

if ($missingComponents > 0) {
    echo "2. Implementar los componentes faltantes\n";
}

if (!empty($viewsWithIssues)) {
    echo "3. Revisar y corregir las vistas con problemas\n";
}

if ($existingViews > ($totalViews * 0.9)) {
    echo "🎉 ¡Excelente! La mayoría de las vistas están implementadas.\n";
    echo "4. Probar la navegación entre vistas en el navegador\n";
    echo "5. Verificar que todas las funcionalidades trabajen correctamente\n";
} else {
    echo "4. Completar la implementación de vistas faltantes\n";
    echo "5. Realizar pruebas de integración\n";
}

echo "\n📋 CHECKLIST DE NAVEGACIÓN:\n";
echo "□ Todas las vistas cargan sin errores\n";
echo "□ La navegación entre vistas funciona\n";
echo "□ Los roles de usuario se respetan\n";
echo "□ Los formularios se envían correctamente\n";
echo "□ Las notificaciones aparecen\n";
echo "□ Los datos se muestran correctamente\n";

echo "\n✨ Verificación completada - " . date('Y-m-d H:i:s') . "\n";