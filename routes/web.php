<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Health check endpoints (no auth required)
Route::get('/health', [App\Http\Controllers\HealthController::class, 'check']);
Route::get('/health/ready', [App\Http\Controllers\HealthController::class, 'ready']);
Route::get('/health/live', [App\Http\Controllers\HealthController::class, 'live']);

Route::get('/', function () {
    // Redirigir automáticamente al login para aplicación médica
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified', 'web'])->group(function () {
    // Redirección basada en rol desde dashboard
    Route::get('dashboard', function () {
        $user = auth()->user();
        
        // Estadísticas básicas según rol
        $stats = [];
        switch ($user->role) {
            case 'administrador':
                $stats = [
                    'total' => \App\Models\User::count(),
                    'pendientes' => 25,
                    'completados' => 15
                ];
                break;
            case 'medico':
                $stats = [
                    'total' => 45,
                    'pendientes' => 8,
                    'completados' => 12
                ];
                break;
            case 'ips':
                $stats = [
                    'misSolicitudes' => 10,
                    'pendientes' => 3,
                    'completados' => 7
                ];
                break;
            default:
                $stats = ['total' => 0, 'pendientes' => 0, 'completados' => 0];
        }
        
        return Inertia::render('dashboard', [
            'user' => $user,
            'stats' => $stats
        ]);
    })->name('dashboard');

    // ==================== RUTAS ADMINISTRADOR ====================
    Route::middleware(['admin', 'check.permission:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // GESTIÓN DE USUARIOS
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\UsuarioController::class, 'store'])->name('store');
            Route::put('{usuario}', [App\Http\Controllers\Admin\UsuarioController::class, 'update'])->name('update');
            Route::patch('{usuario}/toggle-status', [App\Http\Controllers\Admin\UsuarioController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('{usuario}', [App\Http\Controllers\Admin\UsuarioController::class, 'destroy'])->name('destroy');
            Route::get('{usuario}/permisos', [App\Http\Controllers\Admin\UsuarioController::class, 'permisos'])->name('permisos');
            Route::post('{usuario}/permisos', [App\Http\Controllers\Admin\UsuarioController::class, 'updatePermisos'])->name('permisos.update');
            
            // Vista completa de gestión de usuarios
            Route::get('gestion', function() {
                return Inertia::render('admin/GestionUsuarios', [
                    'usuarios' => ['data' => []],
                    'estadisticas' => [
                        'total_usuarios' => 25,
                        'usuarios_activos' => 20,
                        'usuarios_inactivos' => 5,
                        'nuevos_este_mes' => 3
                    ]
                ]);
            })->name('gestion');
            
            // Nuevas rutas
            Route::get('perfiles', function() {
                return Inertia::render('admin/PerfilesUsuarios', [
                    'usuarios' => ['data' => []],
                    'estadisticas' => [
                        'total_usuarios' => 25,
                        'usuarios_activos' => 20,
                        'usuarios_inactivos' => 5,
                        'nuevos_este_mes' => 3
                    ]
                ]);
            })->name('perfiles');
            
            Route::get('permisos', function() {
                return Inertia::render('admin/GestionPermisos', [
                    'usuarios' => ['data' => []]
                ]);
            })->name('permisos.gestion');
        });
        
        // GESTIÓN DE REFERENCIAS
        Route::prefix('referencias')->name('referencias.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ReferenciasController::class, 'dashboard'])->name('index');
            Route::get('dashboard', [App\Http\Controllers\Admin\ReferenciasController::class, 'dashboard'])->name('dashboard');
            Route::post('{solicitud}/decidir', [App\Http\Controllers\Admin\ReferenciasController::class, 'decidir'])->name('decidir');
            Route::get('estadisticas', [App\Http\Controllers\Admin\ReferenciasController::class, 'estadisticas'])->name('estadisticas');
            Route::get('buscar-registros', [App\Http\Controllers\Admin\DashboardController::class, 'buscarRegistros'])->name('buscar-registros');
            Route::get('descargar-historia/{registro}', [App\Http\Controllers\Admin\DashboardController::class, 'descargarHistoria'])->name('descargar-historia');
        });
        
        // REPORTES Y ANALYTICS
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ReportesController::class, 'index'])->name('index');
            Route::get('completos', function() {
                return Inertia::render('admin/Reportes', [
                    'estadisticas' => [
                        'totalSolicitudes' => 150,
                        'tiempoPromedio' => 24,
                        'eficiencia' => 85,
                        'tendencias' => []
                    ]
                ]);
            })->name('completos');
            Route::get('tendencias', function() {
                return Inertia::render('admin/TrendsAnalysis', [
                    'tendencias' => []
                ]);
            })->name('tendencias');
            Route::get('exportar', [App\Http\Controllers\Admin\ReportesController::class, 'exportarExcel'])->name('exportar');
            Route::get('graficos', [App\Http\Controllers\Admin\ReportesController::class, 'graficos'])->name('graficos');
            Route::get('analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'dashboard'])->name('analytics');
            Route::post('analytics/export', [App\Http\Controllers\Admin\AnalyticsController::class, 'exportReport'])->name('analytics.export');
        });
        
        // CONFIGURACIÓN DEL SISTEMA
        Route::prefix('configuracion')->name('configuracion.')->group(function () {
            Route::get('ia', [App\Http\Controllers\Admin\IAConfigController::class, 'index'])->name('ia');
            Route::get('ia-completo', function() {
                return Inertia::render('admin/ConfigurarIA', [
                    'configuracion' => [
                        'peso_edad' => 0.3,
                        'peso_gravedad' => 0.5,
                        'peso_especialidad' => 0.2,
                        'criterios_rojo' => 'Pacientes críticos, urgencias',
                        'criterios_verde' => 'Consultas programadas, seguimiento'
                    ]
                ]);
            })->name('ia-completo');
            Route::post('ia', [App\Http\Controllers\Admin\IAConfigController::class, 'actualizar'])->name('ia.actualizar');
            Route::post('ia/probar', [App\Http\Controllers\Admin\IAConfigController::class, 'probar'])->name('ia.probar');
            Route::get('menu', [App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menu');
            Route::post('menu', [App\Http\Controllers\Admin\MenuController::class, 'actualizar'])->name('menu.actualizar');
            Route::post('menu/restaurar', [App\Http\Controllers\Admin\MenuController::class, 'restaurarDefecto'])->name('menu.restaurar');
        });
        
        // SISTEMA
        Route::prefix('sistema')->name('sistema.')->group(function () {
            Route::get('config', function() {
                return Inertia::render('admin/SystemConfig', [
                    'configuracion' => []
                ]);
            })->name('config');
            Route::get('cache', [App\Http\Controllers\Admin\CacheController::class, 'index'])->name('cache');
            Route::post('cache/clear', [App\Http\Controllers\Admin\CacheController::class, 'clear'])->name('cache.clear');
            Route::post('cache/optimize', [App\Http\Controllers\Admin\CacheController::class, 'optimize'])->name('cache.optimize');
            Route::get('cache/metrics', [App\Http\Controllers\Admin\CacheController::class, 'getMetrics'])->name('cache.metrics');
        });
        
        // MONITOREO Y ALERTAS
        Route::prefix('monitoreo')->name('monitoreo.')->group(function () {
            Route::get('supervision', function () {
                return Inertia::render('admin/supervision');
            })->name('supervision');
            
            // Auditoría y seguridad
            Route::get('auditoria', function() {
                return Inertia::render('admin/AuditoriaSeguridad', [
                    'eventos' => ['data' => []],
                    'estadisticas' => [
                        'total_eventos' => 1250,
                        'eventos_hoy' => 45,
                        'intentos_fallidos' => 3,
                        'accesos_sospechosos' => 1,
                        'usuarios_activos' => 28
                    ],
                    'alertas_seguridad' => []
                ]);
            })->name('auditoria');
            Route::get('alertas-criticas', function() {
                $alerts = \App\Models\CriticalAlert::active()->latest()->take(20)->get();
                return Inertia::render('admin/CriticalAlertsMonitor', ['alerts' => $alerts]);
            })->name('alertas-criticas');
            Route::get('alertas-criticas/data', [App\Http\Controllers\RealTimeNotificationController::class, 'index'])->name('alertas-criticas.data');
            Route::post('alertas-criticas/{alert}/acknowledge', [App\Http\Controllers\RealTimeNotificationController::class, 'acknowledgeAlert'])->name('alertas-criticas.acknowledge');
            Route::post('alertas-criticas/{alert}/resolve', function(\App\Models\CriticalAlert $alert) {
                $alert->resolve(auth()->id());
                return response()->json(['success' => true]);
            })->name('alertas-criticas.resolve');
            Route::get('metricas-tiempo-real', function() {
                return Inertia::render('admin/RealTimeMetrics');
            })->name('metricas-tiempo-real');
            Route::get('performance', [App\Http\Controllers\Admin\PerformanceController::class, 'dashboard'])->name('performance');
            Route::post('performance/optimize-database', [App\Http\Controllers\Admin\PerformanceController::class, 'optimizeDatabase'])->name('performance.optimize-database');
            Route::post('performance/clear-cache', [App\Http\Controllers\Admin\PerformanceController::class, 'clearCache'])->name('performance.clear-cache');
            Route::get('performance/metrics', [App\Http\Controllers\Admin\PerformanceController::class, 'getMetrics'])->name('performance.metrics');
        });
        
        // INTELIGENCIA ARTIFICIAL
        Route::prefix('ia')->name('ia.')->group(function () {
            Route::get('dashboard', [App\Http\Controllers\Admin\AIController::class, 'dashboard'])->name('dashboard');
            Route::post('process-document', [App\Http\Controllers\Admin\AIController::class, 'processDocument'])->name('process-document');
            Route::post('feedback', [App\Http\Controllers\Admin\AIController::class, 'submitFeedback'])->name('feedback');
            Route::post('update-weights', [App\Http\Controllers\Admin\AIController::class, 'updateWeights'])->name('update-weights');
            Route::post('test-classification', [App\Http\Controllers\Admin\AIController::class, 'testClassification'])->name('test-classification');
            Route::get('respuestas-automaticas', [App\Http\Controllers\Admin\AutomaticResponseController::class, 'index'])->name('respuestas-automaticas');
            Route::post('respuestas-automaticas/templates', [App\Http\Controllers\Admin\AutomaticResponseController::class, 'createTemplate'])->name('respuestas-automaticas.templates.create');
            Route::put('respuestas-automaticas/templates/{template}', [App\Http\Controllers\Admin\AutomaticResponseController::class, 'updateTemplate'])->name('respuestas-automaticas.templates.update');
            Route::post('respuestas-automaticas/templates/{template}/test', [App\Http\Controllers\Admin\AutomaticResponseController::class, 'testTemplate'])->name('respuestas-automaticas.templates.test');
            Route::get('respuestas-automaticas/metrics', [App\Http\Controllers\Admin\AutomaticResponseController::class, 'getMetrics'])->name('respuestas-automaticas.metrics');
        });
        
        // INTEGRACIONES
        Route::prefix('integraciones')->name('integraciones.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\IntegrationsController::class, 'index'])->name('index');
            Route::post('sync-patient', [App\Http\Controllers\Admin\IntegrationsController::class, 'syncPatient'])->name('sync-patient');
            Route::post('test-connection', [App\Http\Controllers\Admin\IntegrationsController::class, 'testConnection'])->name('test-connection');
        });

    });

    // Rutas para Jefe de Urgencias
    Route::middleware('admin')->prefix('jefe-urgencias')->name('jefe-urgencias.')->group(function () {
        Route::get('dashboard-ejecutivo', [App\Http\Controllers\JefeUrgencias\ExecutiveDashboardController::class, 'index'])->name('dashboard-ejecutivo');
        Route::get('executive-dashboard', function() {
            return Inertia::render('jefe-urgencias/ExecutiveDashboard', [
                'metricas' => []
            ]);
        })->name('executive-dashboard');
        Route::get('metricas', [App\Http\Controllers\JefeUrgencias\ExecutiveDashboardController::class, 'getRealTimeMetrics'])->name('metricas');
        Route::get('alertas', [App\Http\Controllers\JefeUrgencias\ExecutiveDashboardController::class, 'getCriticalAlerts'])->name('alertas');
        Route::get('performance/{period}', [App\Http\Controllers\JefeUrgencias\ExecutiveDashboardController::class, 'getPerformanceData'])->name('performance');
        Route::post('export-report', [App\Http\Controllers\JefeUrgencias\ExecutiveDashboardController::class, 'exportReport'])->name('export-report');
        
        // Gestión de recursos
        Route::get('recursos', function() {
            return Inertia::render('jefe-urgencias/GestionRecursos', [
                'recursos' => ['data' => []],
                'estadisticas' => [
                    'total_recursos' => 45,
                    'disponibles' => 32,
                    'ocupados' => 8,
                    'mantenimiento' => 3,
                    'utilizacion_promedio' => 75,
                    'alertas_criticas' => 2
                ],
                'alertas' => []
            ]);
        })->name('recursos');
    });
    
    // WebSocket Routes
    Route::prefix('websocket')->name('websocket.')->group(function () {
        Route::post('connect', [App\Http\Controllers\WebSocketController::class, 'connect'])->name('connect');
        Route::post('disconnect', [App\Http\Controllers\WebSocketController::class, 'disconnect'])->name('disconnect');
        Route::post('broadcast', [App\Http\Controllers\WebSocketController::class, 'broadcast'])->name('broadcast');
        Route::get('connections', [App\Http\Controllers\WebSocketController::class, 'getActiveConnections'])->name('connections');
    });

    // Rutas para Médico
    Route::middleware('medico')->prefix('medico')->name('medico.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Medico\DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/metricas', [App\Http\Controllers\Medico\DashboardController::class, 'getMetricas'])->name('dashboard.metricas');
        Route::get('mis-evaluaciones', [App\Http\Controllers\Medico\EvaluacionController::class, 'misEvaluaciones'])->name('mis-evaluaciones');
        Route::post('cancelar-evaluacion/{id}', [App\Http\Controllers\Medico\EvaluacionController::class, 'cancelarEvaluacion'])->name('cancelar-evaluacion');
        
        Route::get('ingresar-registro', [App\Http\Controllers\Medico\MedicoController::class, 'ingresarRegistro'])->name('ingresar-registro');
        Route::post('ingresar-registro', [App\Http\Controllers\Medico\MedicoController::class, 'storeRegistro'])->name('ingresar-registro.store');
        Route::get('consulta-pacientes', [App\Http\Controllers\Medico\MedicoController::class, 'consultaPacientes'])->name('consulta-pacientes');
        Route::get('descargar-historia/{registro}', [App\Http\Controllers\Medico\MedicoController::class, 'descargarHistoria'])->name('descargar-historia');

        // Rutas para IA
        Route::post('ai/extract-patient-data', [App\Http\Controllers\AIController::class, 'extractPatientData'])->name('ai.extract-patient-data');
        Route::post('ai/test-text-extraction', [App\Http\Controllers\AIController::class, 'testTextExtraction'])->name('ai.test-text-extraction');
        Route::post('ai/test-gemini', [App\Http\Controllers\AIController::class, 'testGeminiAPI'])->name('ai.test-gemini');
        
        // Nuevas rutas de la Fase 2
        Route::get('referencias', [App\Http\Controllers\Medico\ReferenciasController::class, 'gestionar'])->name('referencias');
        Route::get('referencias/{solicitud}', [App\Http\Controllers\Medico\ReferenciasController::class, 'detalle'])->name('referencias.detalle');
        Route::post('referencias/{solicitud}/procesar', [App\Http\Controllers\Medico\ReferenciasController::class, 'procesar'])->name('referencias.procesar');
        
        // Rutas para casos críticos
        Route::get('casos-criticos', function() {
            return Inertia::render('medico/CasosCriticos', [
                'casosCriticos' => [
                    'data' => [
                        [
                            'id' => 1,
                            'codigo_solicitud' => 'REF-2025-001',
                            'prioridad' => 'ROJO',
                            'estado' => 'PENDIENTE',
                            'fecha_solicitud' => now()->subHours(3)->toISOString(),
                            'tiempo_transcurrido' => 3.2,
                            'registro_medico' => [
                                'nombre' => 'Juan Carlos',
                                'apellidos' => 'Pérez González',
                                'numero_identificacion' => '12345678',
                                'edad' => 65,
                                'especialidad_solicitada' => 'Cardiología',
                                'motivo_consulta' => 'Dolor torácico intenso de 2 horas de evolución',
                                'diagnostico_principal' => 'Síndrome coronario agudo',
                                'signos_vitales' => 'TA: 180/110, FC: 120, FR: 24, SatO2: 88%',
                                'sintomas_alarma' => ['dolor_toracico', 'disnea']
                            ],
                            'ips' => [
                                'nombre' => 'Hospital San José',
                                'telefono' => '555-0123',
                                'email' => 'referencias@hospitalsanjose.com'
                            ],
                            'puntuacion_ia' => 0.92,
                            'observaciones_ia' => 'Caso crítico: Paciente adulto mayor con síndrome coronario agudo, signos vitales alterados y síntomas de alarma. Requiere atención inmediata.'
                        ]
                    ],
                    'meta' => []
                ]
            ]);
        })->name('casos-criticos');
        Route::post('casos-criticos/{caso}/procesar', [App\Http\Controllers\Medico\ReferenciasController::class, 'procesar'])->name('casos-criticos.procesar');
        
        Route::get('seguimiento', [App\Http\Controllers\Medico\SeguimientoController::class, 'index'])->name('seguimiento');
        Route::post('seguimiento/{solicitud}', [App\Http\Controllers\Medico\SeguimientoController::class, 'actualizar'])->name('seguimiento.actualizar');
        Route::post('seguimiento/{solicitud}/contrarreferencia', [App\Http\Controllers\Medico\SeguimientoController::class, 'contrarreferencia'])->name('seguimiento.contrarreferencia');
        
        // Vista completa de seguimiento
        Route::get('seguimiento/completo', function() {
            return Inertia::render('medico/SeguimientoPacientes', [
                'pacientes' => ['data' => []]
            ]);
        })->name('seguimiento.completo');
        
        // Rutas adicionales para gestión de referencias
        Route::get('referencias/gestionar', function() {
            return Inertia::render('medico/GestionarReferencias', [
                'solicitudes' => ['data' => []]
            ]);
        })->name('referencias.gestionar');
        
        // Ruta duplicada para compatibilidad
        Route::get('gestionar-referencias', function() {
            return Inertia::render('medico/GestionarReferencias', [
                'solicitudes' => ['data' => []]
            ]);
        })->name('gestionar-referencias');
        Route::get('referencias/detalle/{id?}', function($id = null) {
            return Inertia::render('medico/DetalleSolicitud', [
                'solicitud' => []
            ]);
        })->name('referencias.detalle');
        
        // Rutas para evaluaciones
        Route::get('evaluaciones/mis-evaluaciones', [App\Http\Controllers\Medico\EvaluacionController::class, 'misEvaluaciones'])->name('evaluaciones.mis-evaluaciones');
        
        // Rutas para pacientes
        Route::get('pacientes/buscar', function() {
            return Inertia::render('medico/BuscarPacientes');
        })->name('pacientes.buscar');
        
        Route::get('pacientes/historial', function() {
            return Inertia::render('medico/HistorialPacientes', [
                'historiales' => ['data' => []],
                'estadisticas' => [
                    'total_pacientes' => 150,
                    'consultas_mes' => 45,
                    'referencias_mes' => 12,
                    'pacientes_activos' => 28
                ]
            ]);
        })->name('pacientes.historial');
    });
    
    // Rutas para IPS
    Route::middleware('ips')->prefix('ips')->name('ips.')->group(function () {
        Route::get('dashboard', function() {
            return Inertia::render('IPS/Dashboard', [
                'estadisticas' => [
                    'total_solicitudes' => 25,
                    'pendientes' => 8,
                    'aceptadas' => 12,
                    'rechazadas' => 5,
                    'tiempo_promedio_respuesta' => 18.5
                ],
                'solicitudesRecientes' => [],
                'notificaciones' => []
            ]);
        })->name('dashboard');
        
        Route::get('solicitar', [App\Http\Controllers\IPS\SolicitudController::class, 'create'])->name('solicitar');
        Route::post('solicitar', [App\Http\Controllers\IPS\SolicitudController::class, 'store'])->name('solicitar.store');
        Route::get('mis-solicitudes', [App\Http\Controllers\IPS\SolicitudController::class, 'misSolicitudes'])->name('mis-solicitudes');
        
        // Rutas adicionales para IPS
        Route::get('solicitudes/crear', [App\Http\Controllers\IPS\SolicitudController::class, 'create'])->name('solicitudes.crear');
        Route::get('solicitudes/mis-solicitudes', [App\Http\Controllers\IPS\SolicitudController::class, 'misSolicitudes'])->name('solicitudes.mis-solicitudes');
        Route::get('seguimiento', function() {
            return Inertia::render('IPS/Dashboard', [
                'estadisticas' => [
                    'total_solicitudes' => 25,
                    'pendientes' => 8,
                    'aceptadas' => 12,
                    'rechazadas' => 5
                ]
            ]);
        })->name('seguimiento');
        
        // Vista completa de seguimiento de solicitudes
        Route::get('seguimiento/completo', function() {
            return Inertia::render('IPS/SeguimientoSolicitudes', [
                'solicitudes' => ['data' => []],
                'estadisticas' => [
                    'total' => 25,
                    'pendientes' => 8,
                    'aceptadas' => 12,
                    'rechazadas' => 5,
                    'tiempo_promedio' => 18.5
                ]
            ]);
        })->name('seguimiento.completo');
    });
    
    // Rutas de Notificaciones (para todos los roles)
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificacionesController::class, 'index'])->name('index');
        Route::post('{notificacion}/marcar-leida', [App\Http\Controllers\NotificacionesController::class, 'marcarLeida'])->name('marcar-leida');
        Route::post('marcar-todas-leidas', [App\Http\Controllers\NotificacionesController::class, 'marcarTodasLeidas'])->name('marcar-todas-leidas');
        Route::post('configurar', [App\Http\Controllers\NotificacionesController::class, 'configurar'])->name('configurar');
        Route::get('recientes', [App\Http\Controllers\NotificacionesController::class, 'obtenerRecientes'])->name('recientes');
        
        // Centro completo de notificaciones
        Route::get('completas', function() {
            return Inertia::render('Shared/NotificacionesCompletas', [
                'notificaciones' => [
                    'data' => [],
                    'links' => [],
                    'meta' => []
                ]
            ]);
        })->name('completas');
    });
    
    // Configuración personal (para todos los roles)
    Route::get('configuracion-personal', function() {
        $user = auth()->user();
        return Inertia::render('Shared/ConfiguracionPersonal', [
            'usuario' => $user,
            'configuracion_notificaciones' => [
                'email_nuevas_referencias' => true,
                'email_cambios_estado' => true,
                'email_recordatorios' => false,
                'sms_urgencias' => true,
                'sms_recordatorios' => false,
                'push_tiempo_real' => true,
                'push_alertas_criticas' => true,
                'frecuencia_resumen' => 'diario',
                'horario_no_molestar_inicio' => '22:00',
                'horario_no_molestar_fin' => '07:00'
            ],
            'configuracion_privacidad' => [
                'mostrar_perfil_publico' => true,
                'compartir_estadisticas' => true,
                'permitir_contacto_directo' => true,
                'mostrar_estado_online' => true,
                'historial_actividad_visible' => false,
                'datos_anonimos_investigacion' => true
            ],
            'configuracion_interfaz' => [
                'tema' => 'claro',
                'idioma' => 'es',
                'zona_horaria' => 'America/Bogota',
                'formato_fecha' => 'dd/mm/yyyy',
                'formato_hora' => '24h',
                'densidad_interfaz' => 'normal',
                'mostrar_ayuda_contextual' => true
            ]
        ]);
    })->name('configuracion-personal');
});

// ==================== RUTAS DE COMPATIBILIDAD ====================
Route::middleware(['auth', 'verified', 'web'])->group(function () {
    // Rutas adicionales que pueden estar siendo usadas
    Route::get('admin/usuarios', [App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('admin.usuarios');
    Route::get('admin/referencias', [App\Http\Controllers\Admin\ReferenciasController::class, 'dashboard'])->name('admin.referencias');
    Route::get('admin/reportes', [App\Http\Controllers\Admin\ReportesController::class, 'index'])->name('admin.reportes');
    Route::get('admin/reportes-completos', function() {
        return Inertia::render('admin/Reportes', [
            'estadisticas' => [
                'totalSolicitudes' => 150,
                'tiempoPromedio' => 24,
                'eficiencia' => 85,
                'tendencias' => []
            ]
        ]);
    })->name('admin.reportes-completos');
    Route::get('admin/configurar-ia-completo', function() {
        return Inertia::render('admin/ConfigurarIA', [
            'configuracion' => [
                'peso_edad' => 0.3,
                'peso_gravedad' => 0.5,
                'peso_especialidad' => 0.2,
                'criterios_rojo' => 'Pacientes críticos, urgencias',
                'criterios_verde' => 'Consultas programadas, seguimiento'
            ]
        ]);
    })->name('admin.configurar-ia-completo');
    Route::get('admin/supervision', function () {
        return Inertia::render('admin/supervision');
    })->name('admin.supervision');
    
    // API para obtener permisos de menú
    Route::get('/api/menu-permissions', function() {
        $user = auth()->user();
        $permissions = \App\Models\UserPermission::where('user_id', $user->id)
            ->where('granted', true)
            ->pluck('permission')
            ->toArray();
        
        return response()->json([
            'role' => $user->role,
            'permissions' => $permissions,
            'menuItems' => \App\Models\MenuPermiso::menuParaRol($user->role)
        ]);
    })->name('api.menu-permissions');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';