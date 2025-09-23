<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;

class SystemConfigController extends Controller
{
    public function index()
    {
        $configuracion = [
            'sistema' => [
                'nombre_aplicacion' => config('app.name', 'VItal-red'),
                'version' => '1.0.0',
                'entorno' => config('app.env'),
                'debug_habilitado' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale')
            ],
            'base_datos' => [
                'conexion' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'puerto' => config('database.connections.mysql.port'),
                'base_datos' => config('database.connections.mysql.database')
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'ttl_defecto' => 3600,
                'prefijo' => config('cache.prefix')
            ],
            'correo' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'puerto' => config('mail.mailers.smtp.port'),
                'encriptacion' => config('mail.mailers.smtp.encryption'),
                'desde_direccion' => config('mail.from.address'),
                'desde_nombre' => config('mail.from.name')
            ],
            'sesiones' => [
                'driver' => config('session.driver'),
                'tiempo_vida' => config('session.lifetime'),
                'dominio' => config('session.domain'),
                'segura' => config('session.secure'),
                'http_only' => config('session.http_only')
            ],
            'seguridad' => [
                'hash_driver' => config('hashing.driver'),
                'bcrypt_rounds' => config('hashing.bcrypt.rounds'),
                'csrf_habilitado' => true,
                'rate_limiting_habilitado' => true
            ],
            'archivos' => [
                'disco_defecto' => config('filesystems.default'),
                'disco_publico' => config('filesystems.disks.public.driver'),
                'tamaño_maximo_subida' => ini_get('upload_max_filesize'),
                'tiempo_maximo_ejecucion' => ini_get('max_execution_time')
            ],
            'logs' => [
                'canal_defecto' => config('logging.default'),
                'nivel_log' => config('logging.channels.single.level', 'debug'),
                'ruta_logs' => storage_path('logs')
            ]
        ];

        return Inertia::render('admin/SystemConfig', [
            'configuracion' => $configuracion
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'configuracion.sistema.nombre_aplicacion' => 'required|string|max:255',
            'configuracion.sistema.timezone' => 'required|string',
            'configuracion.cache.ttl_defecto' => 'required|integer|min:60|max:86400',
            'configuracion.correo.desde_direccion' => 'required|email',
            'configuracion.correo.desde_nombre' => 'required|string|max:255',
            'configuracion.sesiones.tiempo_vida' => 'required|integer|min:60|max:43200',
            'configuracion.seguridad.bcrypt_rounds' => 'required|integer|min:4|max:20'
        ]);

        $config = $request->input('configuracion');

        // Actualizar configuraciones que se pueden cambiar dinámicamente
        Cache::put('system_config', [
            'app_name' => $config['sistema']['nombre_aplicacion'],
            'timezone' => $config['sistema']['timezone'],
            'cache_ttl' => $config['cache']['ttl_defecto'],
            'mail_from_address' => $config['correo']['desde_direccion'],
            'mail_from_name' => $config['correo']['desde_nombre'],
            'session_lifetime' => $config['sesiones']['tiempo_vida'],
            'bcrypt_rounds' => $config['seguridad']['bcrypt_rounds']
        ], now()->addDays(30));

        // Limpiar cache de configuración
        Cache::forget('config');

        return redirect()->back()->with('success', 'Configuración del sistema actualizada exitosamente');
    }

    public function testConnection(Request $request)
    {
        $tipo = $request->input('tipo');
        $resultado = ['success' => false, 'mensaje' => ''];

        try {
            switch ($tipo) {
                case 'base_datos':
                    \DB::connection()->getPdo();
                    $resultado = ['success' => true, 'mensaje' => 'Conexión a base de datos exitosa'];
                    break;

                case 'cache':
                    Cache::put('test_connection', 'test', 60);
                    $value = Cache::get('test_connection');
                    Cache::forget('test_connection');
                    
                    if ($value === 'test') {
                        $resultado = ['success' => true, 'mensaje' => 'Conexión a cache exitosa'];
                    } else {
                        $resultado = ['success' => false, 'mensaje' => 'Error en conexión a cache'];
                    }
                    break;

                case 'correo':
                    // Test básico de configuración de correo
                    $mailer = config('mail.default');
                    if ($mailer) {
                        $resultado = ['success' => true, 'mensaje' => 'Configuración de correo válida'];
                    } else {
                        $resultado = ['success' => false, 'mensaje' => 'Configuración de correo inválida'];
                    }
                    break;

                default:
                    $resultado = ['success' => false, 'mensaje' => 'Tipo de conexión no válido'];
            }
        } catch (\Exception $e) {
            $resultado = ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }

        return response()->json($resultado);
    }

    public function getSystemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'servidor_web' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
            'sistema_operativo' => PHP_OS,
            'memoria_limite' => ini_get('memory_limit'),
            'tiempo_maximo_ejecucion' => ini_get('max_execution_time'),
            'tamaño_maximo_subida' => ini_get('upload_max_filesize'),
            'extensiones_php' => get_loaded_extensions(),
            'espacio_disco' => [
                'total' => disk_total_space('/'),
                'libre' => disk_free_space('/'),
                'usado' => disk_total_space('/') - disk_free_space('/')
            ]
        ];

        return response()->json($info);
    }

    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'mensaje' => 'Cache del sistema limpiado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al limpiar cache: ' . $e->getMessage()
            ]);
        }
    }

    public function optimizeSystem()
    {
        try {
            \Artisan::call('optimize');
            \Artisan::call('config:cache');
            \Artisan::call('route:cache');
            \Artisan::call('view:cache');

            return response()->json([
                'success' => true,
                'mensaje' => 'Sistema optimizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al optimizar sistema: ' . $e->getMessage()
            ]);
        }
    }
}