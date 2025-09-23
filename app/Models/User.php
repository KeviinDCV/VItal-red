<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'telefono',
        'password',
        'role',
        'is_active',
        'ips_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is administrator
     */
    public function isAdministrator(): bool
    {
        return $this->role === config('auth.roles.admin', 'administrador');
    }

    /**
     * Check if user is medico
     */
    public function isMedico(): bool
    {
        return $this->role === 'medico';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if user is IPS
     */
    public function isIPS(): bool
    {
        return $this->role === 'ips';
    }

    /**
     * Relationship with IPS
     */
    public function ips()
    {
        return $this->belongsTo(IPS::class);
    }

    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function hasPermission($permission)
    {
        // Admin tiene todos los permisos
        if ($this->role === 'administrador') {
            return true;
        }

        // Verificar permisos específicos del usuario
        $userPermission = $this->permissions()->where('permission', $permission)->first();
        if ($userPermission) {
            return $userPermission->granted;
        }

        // Verificar permisos por defecto del rol
        $defaultPermissions = UserPermission::getDefaultPermissions($this->role);
        
        foreach ($defaultPermissions as $defaultPerm) {
            if ($this->matchesPermission($permission, $defaultPerm)) {
                return true;
            }
        }

        return false;
    }

    private function matchesPermission($permission, $pattern)
    {
        // Convertir patrón con * a regex
        $regex = str_replace('*', '.*', $pattern);
        return preg_match('/^' . $regex . '$/', $permission);
    }

    public function grantPermission($permission)
    {
        return $this->permissions()->updateOrCreate(
            ['permission' => $permission],
            ['granted' => true]
        );
    }

    public function revokePermission($permission)
    {
        return $this->permissions()->updateOrCreate(
            ['permission' => $permission],
            ['granted' => false]
        );
    }
}
