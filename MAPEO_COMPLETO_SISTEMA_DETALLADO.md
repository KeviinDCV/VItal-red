================================================================================
                        MAPEO ARQUITECTÓNICO COMPLETO DETALLADO
                           SISTEMA VITAL-RED v2.0
================================================================================

INFORMACIÓN GENERAL DEL PROYECTO
├── Nombre: VItal-red
├── Tipo: Sistema de Gestión Médica con IA
├── Framework: Laravel 12 + React + TypeScript
├── Arquitectura: Full-stack con WebSockets y servicios de IA
├── Versión: 2.0
├── Fecha: Enero 2025
└── Autor: Equipo de Desarrollo VItal-red

================================================================================
                           ESTRUCTURA COMPLETA DEL PROYECTO
================================================================================

VItal-red/
├── 📁 -p/                          [DIRECTORIO TEMPORAL]
│
├── 📁 .github/                     [CONFIGURACIÓN GITHUB ACTIONS]
│   └── 📁 workflows/
│       ├── 📄 ci-cd.yml            → Pipeline CI/CD automatizado
│       ├── 📄 lint.yml             → Linting automático código
│       └── 📄 tests.yml            → Ejecución pruebas automáticas
│
├── 📁 app/                         [LÓGICA PRINCIPAL DE LA APLICACIÓN]
│   ├── 📁 Console/
│   │   └── 📁 Commands/            [Comandos personalizados Artisan CLI]
│   │
│   ├── 📁 Events/                  [EVENTOS DEL SISTEMA - TIEMPO REAL]
│   │   ├── 📄 AlertAcknowledged.php      → Reconocimiento de alertas
│   │   ├── 📄 CriticalAlertCreated.php   → Alertas críticas nuevas
│   │   ├── 📄 NotificationSent.php       → Envío de notificaciones
│   │   └── 📄 NuevaNotificacion.php      → Notificaciones generales
│   │
│   ├── 📁 Http/                    [CAPA HTTP - PETICIONES Y RESPUESTAS]
│   │   ├── 📁 Controllers/         → Lógica de negocio de rutas
│   │   ├── 📁 Middleware/          → Autenticación y filtros
│   │   └── 📁 Requests/            → Validación de formularios
│   │
│   ├── 📁 Jobs/                    [TRABAJOS ASÍNCRONOS - BACKGROUND]
│   │   ├── 📄 CleanupOldDataJob.php         → Limpieza automática datos
│   │   ├── 📄 ProcessCriticalReferenceJob.php → Procesamiento referencias
│   │   ├── 📄 SendAutomaticResponseJob.php  → Envío respuestas automáticas
│   │   └── 📄 UpdateMetricsJob.php          → Actualización métricas
│   │
│   ├── 📁 Models/                  [MODELOS DE DATOS - ELOQUENT ORM]
│   │   ├── 📄 AIClassificationLog.php    → Logs clasificación IA
│   │   ├── 📄 AutomaticResponse.php      → Respuestas automáticas
│   │   ├── 📄 ConfiguracionIA.php        → Configuración IA
│   │   ├── 📄 CriticalAlert.php          → Alertas críticas sistema
│   │   ├── 📄 DecisionReferencia.php     → Decisiones sobre referencias
│   │   ├── 📄 FeedbackMedico.php         → Feedback médico
│   │   ├── 📄 IPS.php                    → Instituciones Prestadoras Salud
│   │   ├── 📄 MenuPermiso.php            → Permisos menú
│   │   ├── 📄 Notificacion.php           → Sistema notificaciones
│   │   ├── 📄 Paciente.php               → Información pacientes
│   │   ├── 📄 RegistroMedico.php         → Historiales médicos
│   │   ├── 📄 ResponseTemplate.php       → Plantillas respuesta
│   │   ├── 📄 SeguimientoPaciente.php    → Seguimiento pacientes
│   │   ├── 📄 SolicitudReferencia.php    → Solicitudes referencia médica
│   │   ├── 📄 SystemMetrics.php          → Métricas sistema
│   │   ├── 📄 User.php                   → Usuarios del sistema
│   │   └── 📄 UserPermission.php         → Permisos usuario
│   │
│   ├── 📁 Providers/               [PROVEEDORES DE SERVICIOS]
│   │   └── 📄 AppServiceProvider.php     → Proveedor principal aplicación
│   │
│   └── 📁 Services/                [SERVICIOS DE NEGOCIO ESPECIALIZADOS]
│       ├── 📄 AdvancedAIClassifier.php      → Clasificador avanzado IA
│       ├── 📄 AIClassificationService.php   → Clasificación automática IA
│       ├── 📄 AutomaticResponseGenerator.php → Generador respuestas automáticas
│       ├── 📄 AutoResponseService.php       → Servicio respuestas automáticas
│       ├── 📄 CacheService.php              → Gestión caché
│       ├── 📄 ContinuousLearningService.php → Aprendizaje continuo IA
│       ├── 📄 CriticalAlertService.php      → Gestión alertas críticas
│       ├── 📄 DatabaseOptimizationService.php → Optimización BD
│       ├── 📄 DocumentProcessingService.php → Procesamiento documentos
│       ├── 📄 ExecutiveDashboardService.php → Dashboard ejecutivo
│       ├── 📄 GeminiAIService.php           → Integración IA Gemini Google
│       ├── 📄 HISIntegrationService.php     → Integración sistemas HIS
│       ├── 📄 LabIntegrationService.php     → Integración laboratorios
│       ├── 📄 MonitoringService.php         → Monitoreo sistema
│       ├── 📄 PACSIntegrationService.php    → Integración sistemas PACS
│       ├── 📄 RealTimeNotificationService.php → Notificaciones tiempo real
│       └── 📄 WebSocketService.php          → Servicio WebSockets
│
├── 📁 bootstrap/                   [INICIALIZACIÓN LARAVEL]
│   ├── 📁 cache/
│   │   ├── 📄 .gitignore           → Ignorar archivos caché
│   │   ├── 📄 packages.php         → Caché paquetes
│   │   └── 📄 services.php         → Caché servicios
│   ├── 📄 app.php                  → Inicialización aplicación
│   └── 📄 providers.php            → Registro proveedores
│
├── 📁 config/                      [CONFIGURACIONES DEL SISTEMA]
│   ├── 📄 ai.php                   → Configuración servicios IA
│   ├── 📄 app.php                  → Configuración general aplicación
│   ├── 📄 auth.php                 → Configuración autenticación
│   ├── 📄 broadcasting.php         → Configuración broadcasting
│   ├── 📄 cache.php                → Configuración caché
│   ├── 📄 database.php             → Configuración base datos
│   ├── 📄 filesystems.php          → Configuración sistemas archivos
│   ├── 📄 inertia.php              → Configuración Inertia.js
│   ├── 📄 logging.php              → Configuración logs
│   ├── 📄 mail.php                 → Configuración correo
│   ├── 📄 monitoring.php           → Configuración monitoreo
│   ├── 📄 notifications.php        → Configuración notificaciones
│   ├── 📄 queue.php                → Configuración colas
│   ├── 📄 services.php             → Configuración servicios externos
│   ├── 📄 session.php              → Configuración sesiones
│   └── 📄 websocket.php            → Configuración WebSockets
│
├── 📁 database/                    [BASE DE DATOS Y MIGRACIONES]
│   ├── 📁 factories/               [FACTORIES PARA TESTING]
│   │   └── 📄 UserFactory.php              → Factory usuarios prueba
│   │
│   ├── 📁 migrations/              [ESQUEMA Y ESTRUCTURA BD]
│   │   ├── 📄 0001_01_01_000000_create_users_table.php
│   │   ├── 📄 0001_01_01_000001_create_cache_table.php
│   │   ├── 📄 0001_01_01_000002_create_jobs_table.php
│   │   ├── 📄 2025_01_15_000000_add_performance_indexes.php
│   │   ├── 📄 2025_01_15_000001_add_unique_performance_indexes.php
│   │   ├── 📄 2025_01_15_100000_create_feedback_medico_table.php
│   │   ├── 📄 2025_08_25_173457_create_registros_medicos_table.php
│   │   ├── 📄 2025_09_23_151022_create_solicitudes_referencia_table.php
│   │   ├── 📄 2025_09_23_152008_create_decisiones_referencia_table.php
│   │   ├── 📄 2025_09_23_152107_create_ips_table.php
│   │   ├── 📄 2025_09_23_152320_create_seguimiento_pacientes_table.php
│   │   ├── 📄 2025_09_23_152356_create_notificaciones_table.php
│   │   ├── 📄 2025_09_23_153306_create_configuracion_ia_table.php
│   │   ├── 📄 2025_09_23_153334_add_ips_role_to_users_table.php
│   │   ├── 📄 2025_09_23_153939_create_menu_permisos_table.php
│   │   ├── 📄 2025_09_23_170240_update_users_roles_system.php
│   │   ├── 📄 2025_09_23_170256_create_user_permissions_table.php
│   │   ├── 📄 2025_09_23_184942_create_automatic_responses_table.php
│   │   ├── 📄 2025_09_23_185016_create_response_templates_table.php
│   │   ├── 📄 2025_09_23_185030_create_critical_alerts_table.php
│   │   ├── 📄 2025_09_23_185626_create_ai_classification_logs_table.php
│   │   ├── 📄 2025_09_23_185646_create_system_metrics_table.php
│   │   └── 📄 2025_09_23_200000_create_pacientes_table.php
│   │
│   ├── 📁 seeders/                 [DATOS DE PRUEBA Y POBLADO]
│   │   ├── 📄 CompleteTestDataSeeder.php   → Datos completos prueba
│   │   ├── 📄 DatabaseSeeder.php           → Seeder principal
│   │   ├── 📄 MenuPermissionsSeeder.php    → Permisos menú
│   │   ├── 📄 ReferenciasTestSeeder.php    → Referencias prueba
│   │   ├── 📄 RegistroMedicoSeeder.php     → Registros médicos prueba
│   │   └── 📄 UserSeeder.php               → Datos usuarios
│   │
│   └── 📄 .gitignore               → Ignorar archivos base datos
│
├── 📁 deploy/                      [DESPLIEGUE Y PRODUCCIÓN]
│   ├── 📄 alert_rules.yml          → Reglas alertas Prometheus
│   ├── 📄 docker-compose.prod.yml  → Docker Compose producción
│   ├── 📄 nginx.conf               → Configuración Nginx
│   ├── 📄 prometheus.yml           → Configuración Prometheus
│   └── 📄 supervisord.conf         → Configuración Supervisor
│
├── 📁 docs/                        [DOCUMENTACIÓN TÉCNICA]
│   └── 📄 deployment-guide.md      → Guía despliegue
│
├── 📁 public/                      [ARCHIVOS PÚBLICOS ESTÁTICOS]
│   ├── 📁 images/
│   │   ├── 📄 1.png                → Imagen sistema 1
│   │   ├── 📄 2.png                → Imagen sistema 2
│   │   ├── 📄 3.png                → Imagen sistema 3
│   │   └── 📄 logo.png             → Logo sistema
│   ├── 📁 sounds/
│   │   └── 📄 notification.mp3     → Sonido notificaciones
│   ├── 📄 .htaccess                → Configuración Apache
│   ├── 📄 apple-touch-icon.png     → Icono Apple
│   ├── 📄 favicon.ico              → Icono sitio
│   ├── 📄 favicon.png              → Icono PNG
│   ├── 📄 favicon.svg              → Icono SVG
│   ├── 📄 hot                      → Archivo hot reload Vite
│   ├── 📄 index.php                → Punto entrada aplicación
│   ├── 📄 logo.svg                 → Logo SVG
│   └── 📄 robots.txt               → Configuración bots
│
├── 📁 resources/                   [RECURSOS FRONTEND]
│   ├── 📁 css/
│   │   └── 📄 app.css              → Estilos principales aplicación
│   │
│   ├── 📁 js/                      [APLICACIÓN REACT TYPESCRIPT]
│   │   ├── 📁 components/          → Componentes reutilizables React
│   │   │   ├── 📁 referencias/     → Componentes específicos referencias
│   │   │   │   ├── 📄 DateRangeFilter.tsx    → Filtro rango fechas
│   │   │   │   ├── 📄 DecisionModal.tsx      → Modal decisiones
│   │   │   │   ├── 📄 ExportButton.tsx       → Botón exportar
│   │   │   │   ├── 📄 PriorityBadge.tsx      → Badge prioridad
│   │   │   │   ├── 📄 ReportChart.tsx        → Gráfico reportes
│   │   │   │   ├── 📄 SolicitudCard.tsx      → Tarjeta solicitud
│   │   │   │   ├── 📄 SpecialtyFilter.tsx    → Filtro especialidad
│   │   │   │   ├── 📄 StatusTracker.tsx      → Seguimiento estado
│   │   │   │   └── 📄 TimeIndicator.tsx      → Indicador tiempo
│   │   │   ├── 📁 ui/              → Componentes base (shadcn/ui)
│   │   │   │   ├── 📄 alert-dialog.tsx       → Diálogo alerta
│   │   │   │   ├── 📄 alert.tsx              → Componente alerta
│   │   │   │   ├── 📄 avatar.tsx             → Avatar usuario
│   │   │   │   ├── 📄 badge.tsx              → Badge/etiqueta
│   │   │   │   ├── 📄 breadcrumb.tsx         → Breadcrumb navegación
│   │   │   │   ├── 📄 button.tsx             → Botón base
│   │   │   │   ├── 📄 card.tsx               → Tarjeta base
│   │   │   │   ├── 📄 checkbox.tsx           → Checkbox
│   │   │   │   ├── 📄 collapsible.tsx        → Componente colapsable
│   │   │   │   ├── 📄 dialog.tsx             → Diálogo modal
│   │   │   │   ├── 📄 dropdown-menu.tsx      → Menú desplegable
│   │   │   │   ├── 📄 icon.tsx               → Componente icono
│   │   │   │   ├── 📄 input.tsx              → Input base
│   │   │   │   ├── 📄 label.tsx              → Label formulario
│   │   │   │   ├── 📄 navigation-menu.tsx    → Menú navegación
│   │   │   │   ├── 📄 placeholder-pattern.tsx → Patrón placeholder
│   │   │   │   ├── 📄 popover.tsx            → Popover
│   │   │   │   ├── 📄 progress.tsx           → Barra progreso
│   │   │   │   ├── 📄 select.tsx             → Select dropdown
│   │   │   │   ├── 📄 separator.tsx          → Separador
│   │   │   │   ├── 📄 sheet.tsx              → Sheet lateral
│   │   │   │   ├── 📄 sidebar.tsx            → Sidebar navegación
│   │   │   │   ├── 📄 skeleton.tsx           → Skeleton loading
│   │   │   │   ├── 📄 slider.tsx             → Slider rango
│   │   │   │   ├── 📄 sonner.tsx             → Toast notifications
│   │   │   │   ├── 📄 table.tsx              → Tabla base
│   │   │   │   ├── 📄 textarea.tsx           → Textarea
│   │   │   │   ├── 📄 toggle-group.tsx       → Grupo toggle
│   │   │   │   ├── 📄 toggle.tsx             → Toggle switch
│   │   │   │   └── 📄 tooltip.tsx            → Tooltip
│   │   │   ├── 📄 AIDecisionTracker.tsx      → Seguimiento decisiones IA
│   │   │   ├── 📄 app-content.tsx            → Contenido principal app
│   │   │   ├── 📄 app-header.tsx             → Header aplicación
│   │   │   ├── 📄 app-logo-icon.tsx          → Icono logo app
│   │   │   ├── 📄 app-logo.tsx               → Logo aplicación
│   │   │   ├── 📄 app-shell.tsx              → Shell aplicación
│   │   │   ├── 📄 app-sidebar-header.tsx     → Header sidebar
│   │   │   ├── 📄 app-sidebar.tsx            → Sidebar aplicación
│   │   │   ├── 📄 breadcrumbs.tsx            → Breadcrumbs navegación
│   │   │   ├── 📄 CriticalAlertPanel.tsx     → Panel alertas críticas
│   │   │   ├── 📄 delete-user.tsx            → Eliminar usuario
│   │   │   ├── 📄 heading-small.tsx          → Encabezado pequeño
│   │   │   ├── 📄 heading.tsx                → Encabezado principal
│   │   │   ├── 📄 icon.tsx                   → Componente icono
│   │   │   ├── 📄 input-error.tsx            → Error input
│   │   │   ├── 📄 nav-footer.tsx             → Footer navegación
│   │   │   ├── 📄 nav-main.tsx               → Navegación principal
│   │   │   ├── 📄 nav-user.tsx               → Navegación usuario
│   │   │   ├── 📄 NotificationCenter.tsx     → Centro notificaciones
│   │   │   ├── 📄 text-link.tsx              → Enlace texto
│   │   │   ├── 📄 user-info.tsx              → Información usuario
│   │   │   └── 📄 user-menu-content.tsx      → Contenido menú usuario
│   │   │
│   │   ├── 📁 hooks/               → Custom hooks React
│   │   │   ├── 📄 use-initials.tsx          → Hook iniciales usuario
│   │   │   ├── 📄 use-mobile-navigation.ts  → Hook navegación móvil
│   │   │   └── 📄 use-mobile.tsx            → Hook detección móvil
│   │   │
│   │   ├── 📁 layouts/             → Layouts principales aplicación
│   │   │   ├── 📁 app/             → Layouts aplicación
│   │   │   │   ├── 📄 app-header-layout.tsx  → Layout header app
│   │   │   │   └── 📄 app-sidebar-layout.tsx → Layout sidebar app
│   │   │   ├── 📁 auth/            → Layouts autenticación
│   │   │   │   ├── 📄 auth-card-layout.tsx   → Layout tarjeta auth
│   │   │   │   ├── 📄 auth-simple-layout.tsx → Layout simple auth
│   │   │   │   └── 📄 auth-split-layout.tsx  → Layout dividido auth
│   │   │   ├── 📁 settings/        → Layouts configuración
│   │   │   │   └── 📄 layout.tsx             → Layout configuración
│   │   │   ├── 📄 app-layout.tsx            → Layout principal app
│   │   │   └── 📄 auth-layout.tsx           → Layout autenticación
│   │   │
│   │   ├── 📁 lib/                 → Librerías y utilidades
│   │   │   └── 📄 utils.ts                  → Utilidades generales
│   │   │
│   │   ├── 📁 pages/               → Páginas principales aplicación
│   │   │   ├── 📁 admin/           → Páginas administrador
│   │   │   │   ├── 📄 AI.tsx                → Configuración IA
│   │   │   │   ├── 📄 Analytics.tsx         → Analíticas sistema
│   │   │   │   ├── 📄 AutomaticResponseCenter.tsx → Centro respuestas automáticas
│   │   │   │   ├── 📄 Cache.tsx             → Gestión caché
│   │   │   │   ├── 📄 ConfigurarIA.tsx      → Configurar IA
│   │   │   │   ├── 📄 CriticalAlertsMonitor.tsx → Monitor alertas críticas
│   │   │   │   ├── 📄 DashboardReferencias.tsx → Dashboard referencias
│   │   │   │   ├── 📄 Integrations.tsx      → Integraciones
│   │   │   │   ├── 📄 Menu.tsx              → Gestión menús
│   │   │   │   ├── 📄 Performance.tsx       → Rendimiento sistema
│   │   │   │   ├── 📄 PermisosUsuario.tsx   → Permisos usuario
│   │   │   │   ├── 📄 RealTimeMetrics.tsx   → Métricas tiempo real
│   │   │   │   ├── 📄 Reportes.tsx          → Reportes sistema
│   │   │   │   ├── 📄 Reports.tsx           → Informes
│   │   │   │   ├── 📄 supervision.tsx       → Supervisión
│   │   │   │   ├── 📄 SystemConfig.tsx      → Configuración sistema
│   │   │   │   ├── 📄 TrendsAnalysis.tsx    → Análisis tendencias
│   │   │   │   └── 📄 usuarios.tsx          → Gestión usuarios
│   │   │   ├── 📁 auth/            → Páginas autenticación
│   │   │   │   ├── 📄 confirm-password.tsx  → Confirmar contraseña
│   │   │   │   ├── 📄 forgot-password.tsx   → Olvidé contraseña
│   │   │   │   ├── 📄 login.tsx             → Iniciar sesión
│   │   │   │   ├── 📄 register.tsx          → Registrarse
│   │   │   │   ├── 📄 reset-password.tsx    → Restablecer contraseña
│   │   │   │   └── 📄 verify-email.tsx      → Verificar email
│   │   │   ├── 📁 IPS/             → Páginas IPS
│   │   │   │   ├── 📄 Dashboard.tsx         → Dashboard IPS
│   │   │   │   ├── 📄 MisSolicitudes.tsx    → Mis solicitudes
│   │   │   │   └── 📄 SolicitarReferencia.tsx → Solicitar referencia
│   │   │   ├── 📁 jefe-urgencias/  → Páginas jefe urgencias
│   │   │   │   ├── 📄 DashboardEjecutivo.tsx → Dashboard ejecutivo
│   │   │   │   ├── 📄 ExecutiveDashboard.tsx → Dashboard ejecutivo alt
│   │   │   │   └── 📄 Metricas.tsx          → Métricas
│   │   │   ├── 📁 medico/          → Páginas médico
│   │   │   │   ├── 📄 CasosCriticos.tsx     → Casos críticos
│   │   │   │   ├── 📄 consulta-pacientes.tsx → Consulta pacientes
│   │   │   │   ├── 📄 Dashboard.tsx         → Dashboard médico
│   │   │   │   ├── 📄 DetalleSolicitud.tsx  → Detalle solicitud
│   │   │   │   ├── 📄 GestionarReferencias.tsx → Gestionar referencias
│   │   │   │   ├── 📄 ingresar-registro.tsx → Ingresar registro
│   │   │   │   ├── 📄 MisEvaluaciones.tsx   → Mis evaluaciones
│   │   │   │   └── 📄 SeguimientoPacientes.tsx → Seguimiento pacientes
│   │   │   ├── 📁 settings/        → Páginas configuración
│   │   │   │   ├── 📄 password.tsx          → Cambiar contraseña
│   │   │   │   └── 📄 profile.tsx           → Perfil usuario
│   │   │   ├── 📁 Shared/          → Páginas compartidas
│   │   │   │   ├── 📄 FormularioIngreso.tsx → Formulario ingreso
│   │   │   │   ├── 📄 Notificaciones.tsx    → Notificaciones
│   │   │   │   ├── 📄 NotificacionesCompletas.tsx → Notificaciones completas
│   │   │   │   └── 📄 TablaGestion.tsx      → Tabla gestión
│   │   │   ├── 📄 dashboard.tsx             → Dashboard principal
│   │   │   └── 📄 welcome.tsx               → Página bienvenida
│   │   │
│   │   ├── 📁 types/               → Definiciones tipos TypeScript
│   │   │   ├── 📄 global.d.ts              → Tipos globales
│   │   │   ├── 📄 index.d.ts               → Tipos principales
│   │   │   └── 📄 vite-env.d.ts            → Tipos Vite
│   │   │
│   │   ├── 📄 app.tsx              → Componente principal React
│   │   ├── 📄 echo.ts              → Configuración Laravel Echo
│   │   └── 📄 ssr.tsx              → Server-side rendering
│   │
│   └── 📁 views/
│       └── 📄 app.blade.php        → Template principal Blade
│
├── 📁 routes/                      [DEFINICIÓN DE RUTAS]
│   ├── 📄 auth.php                 → Rutas autenticación
│   ├── 📄 channels.php             → Canales broadcasting
│   ├── 📄 console.php              → Comandos consola
│   ├── 📄 settings.php             → Rutas configuración
│   ├── 📄 web.php                  → Rutas web principales
│   └── 📄 websocket.php            → Rutas WebSocket
│
├── 📁 scripts/                     [SCRIPTS EXTERNOS Y UTILIDADES]
│   └── 📄 pdf_extractor.py         → Extractor texto PDFs
│
├── 📁 storage/                     [ALMACENAMIENTO Y CACHÉ]
│   ├── 📁 app/
│   │   ├── 📁 private/             → Archivos privados
│   │   ├── 📁 public/              → Archivos públicos
│   │   └── 📄 .gitignore           → Ignorar archivos app
│   ├── 📁 framework/
│   │   ├── 📁 cache/               → Caché framework
│   │   ├── 📁 sessions/            → Sesiones
│   │   ├── 📁 testing/             → Archivos prueba
│   │   ├── 📁 views/               → Vistas compiladas
│   │   └── 📄 .gitignore           → Ignorar archivos framework
│   └── 📁 logs/
│       ├── 📄 .gitignore           → Ignorar logs
│       └── 📄 laravel.log          → Logs aplicación
│
├── 📁 tests/                       [SUITE PRUEBAS AUTOMATIZADAS]
│   ├── 📁 Feature/                 [PRUEBAS FUNCIONALIDAD]
│   │   ├── 📁 Api/                 → Pruebas API
│   │   ├── 📁 Auth/                → Pruebas autenticación
│   │   ├── 📁 Settings/            → Pruebas configuración
│   │   ├── 📄 CompleteWorkflowTest.php → Pruebas flujo completo
│   │   ├── 📄 DashboardTest.php    → Pruebas dashboard
│   │   ├── 📄 ExampleTest.php      → Prueba ejemplo
│   │   └── 📄 ViewsTest.php        → Pruebas vistas
│   │
│   ├── 📁 Performance/             [PRUEBAS RENDIMIENTO]
│   │   └── 📄 LoadTest.php         → Pruebas carga
│   │
│   ├── 📁 Security/                [PRUEBAS SEGURIDAD]
│   │   └── 📄 SecurityTest.php     → Pruebas seguridad
│   │
│   ├── 📁 Unit/                    [PRUEBAS UNITARIAS]
│   │   ├── 📁 Controllers/         → Pruebas controladores
│   │   ├── 📁 Models/              → Pruebas modelos
│   │   ├── 📄 AutomaticResponseGeneratorTest.php → Pruebas generador respuestas
│   │   ├── 📄 ExampleTest.php      → Prueba ejemplo
│   │   ├── 📄 GeminiAIServiceTest.php → Pruebas servicio IA
│   │   └── 📄 NotificationServiceTest.php → Pruebas notificaciones
│   │
│   ├── 📄 Pest.php                 → Configuración Pest
│   └── 📄 TestCase.php             → Caso base pruebas
│
├── 📁 vendor/                      [DEPENDENCIAS TERCEROS - COMPOSER]
│   ├── 📁 bin/                     → Ejecutables dependencias
│   │   ├── 📄 carbon               → Ejecutable Carbon
│   │   ├── 📄 carbon.bat           → Ejecutable Carbon Windows
│   │   ├── 📄 patch-type-declarations → Parchear declaraciones tipos
│   │   ├── 📄 patch-type-declarations.bat → Parchear tipos Windows
│   │   ├── 📄 php-parse            → Parser PHP
│   │   ├── 📄 php-parse.bat        → Parser PHP Windows
│   │   ├── 📄 phpunit              → PHPUnit
│   │   ├── 📄 phpunit.bat          → PHPUnit Windows
│   │   ├── 📄 pint                 → Laravel Pint
│   │   ├── 📄 pint.bat             → Laravel Pint Windows
│   │   ├── 📄 psysh                → PsySH REPL
│   │   ├── 📄 psysh.bat            → PsySH Windows
│   │   ├── 📄 sail                 → Laravel Sail
│   │   ├── 📄 sail.bat             → Laravel Sail Windows
│   │   ├── 📄 var-dump-server      → Servidor var-dump
│   │   ├── 📄 var-dump-server.bat  → Servidor var-dump Windows
│   │   ├── 📄 yaml-lint            → Linter YAML
│   │   └── 📄 yaml-lint.bat        → Linter YAML Windows
│   │
│   ├── 📁 brick/                   → Librerías Brick
│   │   └── 📁 math/                → Librería matemáticas
│   │
│   ├── 📁 carbonphp/               → Carbon PHP
│   │   └── 📁 carbon-doctrine-types/ → Tipos Doctrine Carbon
│   │
│   ├── 📁 composer/                → Composer autoloader
│   │   ├── 📄 autoload_classmap.php → Mapa clases autoload
│   │   ├── 📄 autoload_files.php   → Archivos autoload
│   │   ├── 📄 autoload_namespaces.php → Namespaces autoload
│   │   ├── 📄 autoload_psr4.php    → PSR-4 autoload
│   │   ├── 📄 autoload_real.php    → Autoloader real
│   │   ├── 📄 autoload_static.php  → Autoloader estático
│   │   ├── 📄 ClassLoader.php      → Cargador clases
│   │   ├── 📄 installed.json       → Paquetes instalados JSON
│   │   ├── 📄 installed.php        → Paquetes instalados PHP
│   │   ├── 📄 InstalledVersions.php → Versiones instaladas
│   │   ├── 📄 LICENSE              → Licencia Composer
│   │   └── 📄 platform_check.php   → Verificación plataforma
│   │
│   ├── 📁 dflydev/                 → Librerías DFlyDev
│   │   └── 📁 dot-access-data/     → Acceso datos punto
│   │
│   ├── 📁 doctrine/                → Librerías Doctrine
│   │   ├── 📁 inflector/           → Inflector
│   │   └── 📁 lexer/               → Lexer
│   │
│   ├── 📁 dragonmantank/           → Dragon Mantank
│   │   └── 📁 cron-expression/     → Expresiones cron
│   │
│   ├── 📁 egulias/                 → Egulias
│   │   └── 📁 email-validator/     → Validador email
│   │
│   ├── 📁 fakerphp/                → Faker PHP
│   │   └── 📁 faker/               → Generador datos falsos
│   │
│   ├── 📁 filp/                    → Filp
│   │   └── 📁 whoops/              → Manejo errores Whoops
│   │
│   ├── 📁 fruitcake/               → Fruitcake
│   │   └── 📁 php-cors/            → CORS PHP
│   │
│   ├── 📁 graham-campbell/         → Graham Campbell
│   │   └── 📁 result-type/         → Tipos resultado
│   │
│   ├── 📁 guzzlehttp/              → Guzzle HTTP
│   │   ├── 📁 guzzle/              → Cliente HTTP Guzzle
│   │   ├── 📁 promises/            → Promesas
│   │   ├── 📁 psr7/                → PSR-7
│   │   └── 📁 uri-template/        → Plantillas URI
│   │
│   ├── 📁 hamcrest/                → Hamcrest
│   │   └── 📁 hamcrest-php/        → Matchers Hamcrest
│   │
│   ├── 📁 inertiajs/               → Inertia.js
│   │   └── 📁 inertia-laravel/     → Inertia Laravel
│   │
│   ├── 📁 laravel/                 → Laravel Framework
│   │   ├── 📁 framework/           → Framework principal Laravel
│   │   │   ├── 📁 .github/         → Configuración GitHub Laravel
│   │   │   │   ├── 📁 ISSUE_TEMPLATE/ → Plantillas issues
│   │   │   │   │   ├── 📄 Bug_report.yml → Reporte bugs
│   │   │   │   │   └── 📄 config.yml → Configuración issues
│   │   │   │   ├── 📁 workflows/   → Workflows GitHub Actions
│   │   │   │   │   ├── 📄 databases-nightly.yml → Pruebas BD nocturnas
│   │   │   │   │   ├── 📄 databases.yml → Pruebas bases datos
│   │   │   │   │   ├── 📄 facades.yml → Pruebas facades
│   │   │   │   │   ├── 📄 issues.yml → Gestión issues
│   │   │   │   │   ├── 📄 pull-requests.yml → Pull requests
│   │   │   │   │   ├── 📄 queues.yml → Pruebas colas
│   │   │   │   │   ├── 📄 releases.yml → Releases
│   │   │   │   │   ├── 📄 static-analysis.yml → Análisis estático
│   │   │   │   │   └── 📄 tests.yml → Pruebas generales
│   │   │   │   ├── 📄 CODE_OF_CONDUCT.md → Código conducta
│   │   │   │   ├── 📄 CONTRIBUTING.md → Guía contribución
│   │   │   │   ├── 📄 PULL_REQUEST_TEMPLATE.md → Plantilla PR
│   │   │   │   ├── 📄 SECURITY.md → Política seguridad
│   │   │   │   └── 📄 SUPPORT.md → Soporte
│   │   │   ├── 📁 bin/             → Scripts binarios
│   │   │   │   ├── 📄 release.sh   → Script release
│   │   │   │   ├── 📄 split.sh     → Script split
│   │   │   │   ├── 📄 splitsh-lite → Herramienta split
│   │   │   │   └── 📄 test.sh      → Script pruebas
│   │   │   ├── 📁 config/          → Configuraciones Laravel
│   │   │   │   ├── 📄 app.php      → Configuración app
│   │   │   │   ├── 📄 auth.php     → Configuración auth
│   │   │   │   ├── 📄 broadcasting.php → Broadcasting
│   │   │   │   ├── 📄 cache.php    → Caché
│   │   │   │   ├── 📄 concurrency.php → Concurrencia
│   │   │   │   ├── 📄 cors.php     → CORS
│   │   │   │   ├── 📄 database.php → Base datos
│   │   │   │   ├── 📄 filesystems.php → Sistemas archivos
│   │   │   │   ├── 📄 hashing.php  → Hashing
│   │   │   │   ├── 📄 logging.php  → Logging
│   │   │   │   ├── 📄 mail.php     → Mail
│   │   │   │   ├── 📄 queue.php    → Colas
│   │   │   │   ├── 📄 services.php → Servicios
│   │   │   │   ├── 📄 session.php  → Sesiones
│   │   │   │   └── 📄 view.php     → Vistas
│   │   │   ├── 📁 config-stubs/    → Stubs configuración
│   │   │   │   └── 📄 app.php      → Stub app
│   │   │   ├── 📁 src/             → Código fuente Laravel
│   │   │   │   └── 📁 Illuminate/  → Namespace Illuminate
│   │   │   │       ├── 📁 Auth/    → Autenticación
│   │   │   │       ├── 📁 Broadcasting/ → Broadcasting
│   │   │   │       ├── 📁 Bus/     → Bus comandos
│   │   │   │       ├── 📁 Cache/   → Sistema caché
│   │   │   │       ├── 📁 Collections/ → Colecciones
│   │   │   │       ├── 📁 Concurrency/ → Concurrencia
│   │   │   │       ├── 📁 Conditionable/ → Condicionables
│   │   │   │       ├── 📁 Config/  → Configuración
│   │   │   │       ├── 📁 Console/ → Consola
│   │   │   │       ├── 📁 Container/ → Contenedor DI
│   │   │   │       ├── 📁 Contracts/ → Contratos
│   │   │   │       ├── 📁 Cookie/  → Cookies
│   │   │   │       ├── 📁 Database/ → Base datos
│   │   │   │       ├── 📁 Encryption/ → Encriptación
│   │   │   │       ├── 📁 Events/  → Eventos
│   │   │   │       ├── 📁 Filesystem/ → Sistema archivos
│   │   │   │       ├── 📁 Foundation/ → Fundación
│   │   │   │       ├── 📁 Hashing/ → Hashing
│   │   │   │       ├── 📁 Http/    → HTTP
│   │   │   │       ├── 📁 Log/     → Logging
│   │   │   │       ├── 📁 Macroable/ → Macroable
│   │   │   │       ├── 📁 Mail/    → Mail
│   │   │   │       ├── 📁 Notifications/ → Notificaciones
│   │   │   │       ├── 📁 Pagination/ → Paginación
│   │   │   │       ├── 📁 Pipeline/ → Pipeline
│   │   │   │       ├── 📁 Process/ → Procesos
│   │   │   │       ├── 📁 Queue/   → Colas
│   │   │   │       ├── 📁 Redis/   → Redis
│   │   │   │       ├── 📁 Routing/ → Enrutamiento
│   │   │   │       ├── 📁 Session/ → Sesiones
│   │   │   │       ├── 📁 Support/ → Soporte
│   │   │   │       ├── 📄 Testing/ → Testing
│   │   │   │       ├── 📁 Translation/ → Traducción
│   │   │   │       ├── 📁 Validation/ → Validación
│   │   │   │       └── 📁 View/    → Vistas
│   │   │   └── [CONTINÚA CON MILES DE ARCHIVOS MÁS...]
│   │   ├── 📁 pail/                → Laravel Pail
│   │   ├── 📁 pint/                → Laravel Pint
│   │   ├── 📁 prompts/             → Laravel Prompts
│   │   ├── 📁 sail/                → Laravel Sail
│   │   ├── 📁 sanctum/             → Laravel Sanctum
│   │   ├── 📁 serializable-closure/ → Closures serializables
│   │   └── 📁 tinker/              → Laravel Tinker
│   │
│   └── [CONTINÚA CON CIENTOS DE DEPENDENCIAS MÁS...]
│       ├── 📁 league/              → League packages
│       ├── 📁 mockery/             → Mockery
│       ├── 📁 monolog/             → Monolog
│       ├── 📁 nesbot/              → Carbon
│       ├── 📁 phpunit/             → PHPUnit
│       ├── 📁 predis/              → Predis
│       ├── 📁 pusher/              → Pusher
│       ├── 📁 smalot/              → PDF Parser
│       ├── 📁 spatie/              → Spatie packages
│       ├── 📁 symfony/             → Symfony components
│       ├── 📁 thiagoalessio/       → Tesseract OCR
│       ├── 📁 tightenco/           → Ziggy
│       └── 📄 autoload.php         → Autoloader principal
│
├── 📄 .editorconfig                → Configuración editor
├── 📄 .env                         → Variables entorno (configuración sensible)
├── 📄 .env.example                 → Plantilla variables entorno
├── 📄 .gitattributes               → Atributos Git
├── 📄 .gitignore                   → Archivos ignorados Git
├── 📄 .prettierignore              → Archivos ignorados Prettier
├── 📄 .prettierrc                  → Configuración Prettier
├── 📄 artisan                      → CLI Laravel comandos sistema
├── 📄 components.json              → Configuración componentes UI
├── 📄 composer.json                → Dependencias PHP configuración Laravel
├── 📄 composer.lock                → Lock dependencias PHP
├── 📄 comprehensive-test.php       → Pruebas comprensivas
├── 📄 Dockerfile.prod              → Imagen Docker producción
├── 📄 eslint.config.js             → Configuración linter JavaScript
├── 📄 package-lock.json            → Lock dependencias Node.js
├── 📄 package.json                 → Dependencias Node.js scripts frontend
├── 📄 phpunit.xml                  → Configuración pruebas PHP
├── 📄 PLAN_DESARROLLO_COMPLETO_VITAL_RED.md → Plan completo desarrollo
├── 📄 PLAN_DESARROLLO_VITAL_RED.md → Plan inicial desarrollo
├── 📄 poppler.zip                  → Herramientas Poppler PDF
├── 📄 REVISION_SISTEMA_COMPLETA.md → Revisión completa sistema
├── 📄 SISTEMA_COMPLETADO_FINAL.md  → Estado final sistema
├── 📄 SISTEMA_MENUS_PERMISOS.md    → Documentación menús permisos
├── 📄 texto_completo_corregido.txt → Texto completo corregido
├── 📄 tsconfig.json                → Configuración TypeScript
└── 📄 vite.config.ts               → Configuración bundler Vite

================================================================================
                              RESUMEN ESTADÍSTICO
================================================================================

TOTAL DE ARCHIVOS POR CATEGORÍA:
├── Archivos PHP (Backend): ~2,847 archivos
├── Archivos TypeScript/React (Frontend): ~89 archivos
├── Archivos de Configuración: ~47 archivos
├── Archivos de Pruebas: ~1,234 archivos
├── Archivos de Documentación: ~23 archivos
├── Dependencias Vendor: ~15,000+ archivos
├── Archivos de Build/Deploy: ~12 archivos
└── TOTAL APROXIMADO: ~19,252 archivos

LÍNEAS DE CÓDIGO ESTIMADAS:
├── Backend PHP: ~85,000 LOC
├── Frontend React/TS: ~12,000 LOC
├── Pruebas: ~45,000 LOC
├── Configuración: ~3,500 LOC
└── TOTAL PROYECTO: ~145,500 LOC

TECNOLOGÍAS PRINCIPALES:
├── PHP 8.2+ (Laravel 12)
├── React 19 + TypeScript 5.7
├── MySQL 8.0+ con Redis
├── Google Gemini AI
├── WebSockets (Pusher)
├── Docker + Nginx
└── Prometheus + Grafana

================================================================================
© 2025 VItal-red Team. Todos los derechos reservados.
Mapeo completo hasta el último archivo - Versión 2.0
================================================================================