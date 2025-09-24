<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\MenuPermiso;

class ManagePermissions extends Command
{
    protected $signature = 'permissions:manage 
                            {action : grant, revoke, list, reset}
                            {--user= : ID del usuario}
                            {--permission= : Permiso específico}
                            {--role= : Rol específico}';

    protected $description = 'Gestionar permisos de usuarios y menús';

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'grant':
                $this->grantPermission();
                break;
            case 'revoke':
                $this->revokePermission();
                break;
            case 'list':
                $this->listPermissions();
                break;
            case 'reset':
                $this->resetPermissions();
                break;
            default:
                $this->error('Acción no válida. Use: grant, revoke, list, reset');
        }
    }

    private function grantPermission()
    {
        $userId = $this->option('user');
        $permission = $this->option('permission');

        if (!$userId || !$permission) {
            $this->error('Se requieren --user y --permission');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error('Usuario no encontrado');
            return;
        }

        $user->grantPermission($permission);
        $this->info("Permiso '{$permission}' otorgado a {$user->name}");
    }

    private function revokePermission()
    {
        $userId = $this->option('user');
        $permission = $this->option('permission');

        if (!$userId || !$permission) {
            $this->error('Se requieren --user y --permission');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error('Usuario no encontrado');
            return;
        }

        $user->revokePermission($permission);
        $this->info("Permiso '{$permission}' revocado a {$user->name}");
    }

    private function listPermissions()
    {
        $userId = $this->option('user');
        $role = $this->option('role');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error('Usuario no encontrado');
                return;
            }

            $this->info("Permisos de {$user->name} ({$user->role}):");
            
            $permissions = UserPermission::where('user_id', $user->id)
                ->where('granted', true)
                ->get();

            foreach ($permissions as $permission) {
                $this->line("  - {$permission->permission}");
            }

            $this->info("\nPermisos por defecto del rol:");
            $defaultPermissions = UserPermission::getDefaultPermissions($user->role);
            foreach ($defaultPermissions as $permission) {
                $this->line("  - {$permission}");
            }

        } elseif ($role) {
            $this->info("Permisos por defecto para rol '{$role}':");
            $defaultPermissions = UserPermission::getDefaultPermissions($role);
            foreach ($defaultPermissions as $permission) {
                $this->line("  - {$permission}");
            }

            $this->info("\nUsuarios con este rol:");
            $users = User::where('role', $role)->get();
            foreach ($users as $user) {
                $this->line("  - {$user->name} ({$user->email})");
            }

        } else {
            $this->info("Todos los usuarios y sus permisos:");
            $users = User::with('permissions')->get();
            
            foreach ($users as $user) {
                $this->info("\n{$user->name} ({$user->role}):");
                $permissions = $user->permissions()->where('granted', true)->get();
                foreach ($permissions as $permission) {
                    $this->line("  - {$permission->permission}");
                }
            }
        }
    }

    private function resetPermissions()
    {
        $role = $this->option('role');

        if ($role) {
            $users = User::where('role', $role)->get();
            $this->info("Restaurando permisos para rol '{$role}'...");
        } else {
            $users = User::all();
            $this->info("Restaurando permisos para todos los usuarios...");
        }

        foreach ($users as $user) {
            // Eliminar permisos existentes
            UserPermission::where('user_id', $user->id)->delete();
            
            // Asignar permisos por defecto
            $defaultPermissions = UserPermission::getDefaultPermissions($user->role);
            
            foreach ($defaultPermissions as $permission) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permission' => $permission,
                    'granted' => true
                ]);
            }
            
            $this->line("  - {$user->name}: permisos restaurados");
        }

        // Restaurar configuración de menús
        MenuPermiso::configurarDefecto();
        
        $this->info("Permisos restaurados correctamente");
    }
}