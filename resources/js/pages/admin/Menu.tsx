import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Users, Settings, Shield, CheckCircle, XCircle, RotateCcw } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    permissions: Array<{
        permission: string;
        granted: boolean;
    }>;
}

interface MenuProps {
    usuarios: User[];
    menuItems: Record<string, Record<string, string>>;
    roles: string[];
}

export default function Menu({ usuarios, menuItems, roles }: MenuProps) {
    const [selectedUser, setSelectedUser] = useState<User | null>(null);
    const [selectedPermissions, setSelectedPermissions] = useState<string[]>([]);

    const { data, setData, post, processing, errors, reset } = useForm({
        user_id: '',
        permissions: [] as string[]
    });

    const handleUserSelect = (userId: string) => {
        const user = usuarios.find(u => u.id.toString() === userId);
        if (user) {
            setSelectedUser(user);
            const userPermissions = user.permissions
                .filter(p => p.granted)
                .map(p => p.permission);
            setSelectedPermissions(userPermissions);
            setData({
                user_id: userId,
                permissions: userPermissions
            });
        }
    };

    const handlePermissionToggle = (permission: string, checked: boolean) => {
        let newPermissions;
        if (checked) {
            newPermissions = [...selectedPermissions, permission];
        } else {
            newPermissions = selectedPermissions.filter(p => p !== permission);
        }
        
        setSelectedPermissions(newPermissions);
        setData('permissions', newPermissions);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.configuracion.menu.actualizar'), {
            onSuccess: () => {
                // Actualizar usuario seleccionado
                const updatedUser = usuarios.find(u => u.id.toString() === data.user_id);
                if (updatedUser) {
                    setSelectedUser(updatedUser);
                }
            }
        });
    };

    const handleRestoreDefaults = () => {
        post(route('admin.configuracion.menu.restaurar'), {
            onSuccess: () => {
                setSelectedUser(null);
                setSelectedPermissions([]);
                reset();
            }
        });
    };

    const getRoleColor = (role: string) => {
        const colors = {
            'administrador': 'bg-red-100 text-red-800',
            'medico': 'bg-blue-100 text-blue-800',
            'ips': 'bg-green-100 text-green-800',
            'jefe_urgencias': 'bg-purple-100 text-purple-800',
            'centro_referencia': 'bg-orange-100 text-orange-800'
        };
        return colors[role as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    const getRoleLabel = (role: string) => {
        const labels = {
            'administrador': 'Administrador',
            'medico': 'Médico',
            'ips': 'IPS',
            'jefe_urgencias': 'Jefe de Urgencias',
            'centro_referencia': 'Centro de Referencia'
        };
        return labels[role as keyof typeof labels] || role;
    };

    return (
        <AppLayout>
            <Head title="Configuración de Menús y Permisos" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Configuración de Menús</h1>
                        <p className="text-muted-foreground">
                            Gestiona los permisos de acceso a menús por usuario
                        </p>
                    </div>
                    <Button
                        onClick={handleRestoreDefaults}
                        variant="outline"
                        disabled={processing}
                        className="flex items-center gap-2"
                    >
                        <RotateCcw className="h-4 w-4" />
                        Restaurar por Defecto
                    </Button>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Selección de Usuario */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Users className="h-5 w-5" />
                                Seleccionar Usuario
                            </CardTitle>
                            <CardDescription>
                                Elige el usuario para configurar sus permisos
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Select onValueChange={handleUserSelect}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar usuario..." />
                                </SelectTrigger>
                                <SelectContent>
                                    {usuarios.map((user) => (
                                        <SelectItem key={user.id} value={user.id.toString()}>
                                            <div className="flex items-center justify-between w-full">
                                                <span>{user.name}</span>
                                                <Badge className={getRoleColor(user.role)}>
                                                    {getRoleLabel(user.role)}
                                                </Badge>
                                            </div>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            {selectedUser && (
                                <div className="mt-4 p-4 bg-muted rounded-lg">
                                    <h4 className="font-medium">{selectedUser.name}</h4>
                                    <p className="text-sm text-muted-foreground">{selectedUser.email}</p>
                                    <Badge className={`mt-2 ${getRoleColor(selectedUser.role)}`}>
                                        {getRoleLabel(selectedUser.role)}
                                    </Badge>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Configuración de Permisos */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Settings className="h-5 w-5" />
                                Permisos de Menú
                            </CardTitle>
                            <CardDescription>
                                Configura qué elementos del menú puede ver el usuario
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {!selectedUser ? (
                                <Alert>
                                    <Shield className="h-4 w-4" />
                                    <AlertDescription>
                                        Selecciona un usuario para configurar sus permisos de menú
                                    </AlertDescription>
                                </Alert>
                            ) : (
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    {Object.entries(menuItems).map(([role, items]) => (
                                        <div key={role} className="space-y-4">
                                            <div className="flex items-center gap-2">
                                                <h3 className="text-lg font-semibold">
                                                    {getRoleLabel(role)}
                                                </h3>
                                                <Badge className={getRoleColor(role)}>
                                                    {Object.keys(items).length} elementos
                                                </Badge>
                                            </div>
                                            
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                {Object.entries(items).map(([permission, label]) => {
                                                    const isChecked = selectedPermissions.includes(permission);
                                                    const isDisabled = selectedUser.role !== role && role !== 'shared';
                                                    
                                                    return (
                                                        <div
                                                            key={permission}
                                                            className={`flex items-center space-x-3 p-3 rounded-lg border ${
                                                                isDisabled ? 'bg-muted opacity-50' : 'bg-background'
                                                            }`}
                                                        >
                                                            <Checkbox
                                                                id={permission}
                                                                checked={isChecked}
                                                                disabled={isDisabled}
                                                                onCheckedChange={(checked) => 
                                                                    handlePermissionToggle(permission, checked as boolean)
                                                                }
                                                            />
                                                            <div className="flex-1">
                                                                <label
                                                                    htmlFor={permission}
                                                                    className="text-sm font-medium cursor-pointer"
                                                                >
                                                                    {label}
                                                                </label>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {permission}
                                                                </p>
                                                            </div>
                                                            {isChecked ? (
                                                                <CheckCircle className="h-4 w-4 text-green-600" />
                                                            ) : (
                                                                <XCircle className="h-4 w-4 text-gray-400" />
                                                            )}
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                            
                                            {role !== Object.keys(menuItems)[Object.keys(menuItems).length - 1] && (
                                                <Separator />
                                            )}
                                        </div>
                                    ))}

                                    <div className="flex justify-end gap-3 pt-4">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => {
                                                setSelectedUser(null);
                                                setSelectedPermissions([]);
                                                reset();
                                            }}
                                        >
                                            Cancelar
                                        </Button>
                                        <Button type="submit" disabled={processing}>
                                            {processing ? 'Guardando...' : 'Guardar Permisos'}
                                        </Button>
                                    </div>
                                </form>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Resumen de Permisos */}
                {selectedUser && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Resumen de Permisos Activos</CardTitle>
                            <CardDescription>
                                Permisos actualmente asignados a {selectedUser.name}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-wrap gap-2">
                                {selectedPermissions.length > 0 ? (
                                    selectedPermissions.map((permission) => (
                                        <Badge key={permission} variant="secondary">
                                            {permission}
                                        </Badge>
                                    ))
                                ) : (
                                    <p className="text-muted-foreground">No hay permisos asignados</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}