<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuPermiso;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        $usuarios = User::with('permissions')->get();
        $menuItems = $this->getMenuStructure();
        
        return Inertia::render('admin/Menu', [
            'usuarios' => $usuarios,
            'menuItems' => $menuItems,
            'roles' => ['administrador', 'jefe_urgencias', 'centro_referencia', 'medico', 'ips']
        ]);
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permissions' => 'required|array',
            'permissions.*' => 'string'
        ]);

        $user = User::findOrFail($request->user_id);
        
        // Eliminar permisos existentes
        UserPermission::where('user_id', $user->id)->delete();
        
        // Agregar nuevos permisos
        foreach ($request->permissions as $permission) {
            UserPermission::create([
                'user_id' => $user->id,
                'permission' => $permission,
                'granted' => true
            ]);
        }

        return back()->with('success', 'Permisos actualizados correctamente');
    }

    public function restaurarDefecto()
    {
        // Restaurar permisos por defecto para todos los usuarios
        $users = User::all();
        
        foreach ($users as $user) {
            UserPermission::where('user_id', $user->id)->delete();
            
            $defaultPermissions = UserPermission::getDefaultPermissions($user->role);
            
            foreach ($defaultPermissions as $permission) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permission' => $permission,
                    'granted' => true
                ]);
            }
        }

        MenuPermiso::configurarDefecto();

        return back()->with('success', 'Permisos restaurados a configuración por defecto');
    }

    private function getMenuStructure()
    {
        return [
            'administrador' => [
                'dashboard' => 'Dashboard Principal',
                'admin.usuarios' => 'Gestión de Usuarios',
                'admin.usuarios.permisos' => 'Permisos de Usuario',
                'admin.referencias' => 'Dashboard Referencias',
                'admin.referencias.estadisticas' => 'Estadísticas Referencias',
                'admin.reportes' => 'Reportes Básicos',
                'admin.reportes.completos' => 'Reportes Completos',
                'admin.reportes.analytics' => 'Analytics Avanzado',
                'admin.configuracion.ia' => 'Configuración IA',
                'admin.ia.dashboard' => 'Dashboard IA',
                'admin.ia.respuestas-automaticas' => 'Respuestas Automáticas',
                'admin.monitoreo.supervision' => 'Panel Supervisión',
                'admin.monitoreo.alertas-criticas' => 'Alertas Críticas',
                'admin.monitoreo.metricas-tiempo-real' => 'Métricas Tiempo Real',
                'admin.monitoreo.performance' => 'Performance Sistema',
                'admin.configuracion.menu' => 'Configurar Menús',
                'admin.integraciones' => 'Integraciones Externas',
                'admin.sistema.cache' => 'Gestión Cache'
            ],
            'medico' => [
                'dashboard' => 'Dashboard Médico',
                'medico.pacientes.ingresar-registro' => 'Ingresar Registro',
                'medico.pacientes.consulta' => 'Consultar Pacientes',
                'medico.pacientes.buscar' => 'Buscar Pacientes',
                'medico.referencias.gestionar' => 'Gestionar Referencias',
                'medico.referencias.casos-criticos' => 'Casos Críticos',
                'medico.seguimiento' => 'Seguimiento Activo',
                'medico.seguimiento.completo' => 'Seguimiento Completo',
                'medico.evaluaciones.mis-evaluaciones' => 'Mis Evaluaciones'
            ],
            'ips' => [
                'dashboard' => 'Dashboard IPS',
                'ips.solicitudes.crear' => 'Crear Solicitud',
                'ips.solicitudes.mis-solicitudes' => 'Mis Solicitudes',
                'ips.seguimiento' => 'Seguimiento Solicitudes'
            ],
            'jefe_urgencias' => [
                'dashboard' => 'Dashboard Ejecutivo',
                'jefe-urgencias.dashboard-ejecutivo' => 'Dashboard Ejecutivo',
                'jefe-urgencias.metricas' => 'Métricas Tiempo Real',
                'admin.referencias' => 'Estado Referencias',
                'jefe-urgencias.alertas' => 'Alertas Críticas'
            ],
            'centro_referencia' => [
                'dashboard' => 'Dashboard Centro',
                'medico.referencias' => 'Solicitudes Pendientes',
                'medico.referencias.casos-criticos' => 'Casos Críticos',
                'medico.seguimiento.completo' => 'Seguimiento Pacientes',
                'admin.reportes' => 'Reportes Operativos'
            ]
        ];
    }
}