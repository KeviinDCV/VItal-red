<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\MenuPermiso;

class MenuPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Configurar permisos de menú por defecto
        MenuPermiso::configurarDefecto();

        // Asignar permisos por defecto a todos los usuarios existentes
        $users = User::all();
        
        foreach ($users as $user) {
            // Limpiar permisos existentes
            UserPermission::where('user_id', $user->id)->delete();
            
            // Obtener permisos por defecto según el rol
            $defaultPermissions = UserPermission::getDefaultPermissions($user->role);
            
            // Crear permisos
            foreach ($defaultPermissions as $permission) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permission' => $permission,
                    'granted' => true
                ]);
            }
        }

        $this->command->info('Permisos de menú configurados correctamente.');
    }
}