import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Shield, Users, Settings, Search, Edit, Trash2 } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';

interface Usuario {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
    permisos: string[];
}

interface Props {
    usuarios: {
        data: Usuario[];
        links: any[];
        meta: any;
    };
}

export default function GestionPermisos({ usuarios }: Props) {
    const [busqueda, setBusqueda] = useState('');
    const [filtroRol, setFiltroRol] = useState('');
    const { post, put, delete: destroy } = useForm();

    const togglePermiso = (userId: number, permiso: string) => {
        post(`/admin/usuarios/${userId}/permisos`, {
            permiso: permiso
        });
    };

    const toggleEstado = (userId: number) => {
        put(`/admin/usuarios/${userId}/toggle-status`);
    };

    const eliminarUsuario = (userId: number) => {
        if (confirm('¿Está seguro de eliminar este usuario?')) {
            destroy(`/admin/usuarios/${userId}`);
        }
    };

    const getRoleBadge = (role: string) => {
        const variants = {
            'administrador': 'bg-red-500',
            'medico': 'bg-blue-500',
            'ips': 'bg-green-500',
            'jefe_urgencias': 'bg-purple-500'
        };
        return variants[role as keyof typeof variants] || 'bg-gray-500';
    };

    const usuariosFiltrados = usuarios.data.filter(usuario => {
        const matchesBusqueda = usuario.name.toLowerCase().includes(busqueda.toLowerCase()) ||
            usuario.email.toLowerCase().includes(busqueda.toLowerCase());
        const matchesRol = !filtroRol || usuario.role === filtroRol;
        return matchesBusqueda && matchesRol;
    });

    return (
        <AppLayout>
            <Head title="Gestión de Permisos" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold flex items-center gap-2">
                        <Shield className="h-8 w-8" />
                        Gestión de Permisos
                    </h1>
                    <Button>
                        <Users className="mr-2 h-4 w-4" />
                        Nuevo Usuario
                    </Button>
                </div>

                {/* Filtros */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex gap-4 items-center">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Buscar usuarios..."
                                        value={busqueda}
                                        onChange={(e) => setBusqueda(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            <Select value={filtroRol} onValueChange={setFiltroRol}>
                                <SelectTrigger className="w-48">
                                    <SelectValue placeholder="Filtrar por rol" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos los roles</SelectItem>
                                    <SelectItem value="administrador">Administrador</SelectItem>
                                    <SelectItem value="medico">Médico</SelectItem>
                                    <SelectItem value="ips">IPS</SelectItem>
                                    <SelectItem value="jefe_urgencias">Jefe Urgencias</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Tabla de usuarios */}
                <Card>
                    <CardHeader>
                        <CardTitle>Usuarios y Permisos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Usuario</TableHead>
                                    <TableHead>Rol</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Permisos</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {usuariosFiltrados.map((usuario) => (
                                    <TableRow key={usuario.id}>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{usuario.name}</div>
                                                <div className="text-sm text-muted-foreground">{usuario.email}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge className={getRoleBadge(usuario.role)}>
                                                {usuario.role}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={usuario.is_active ? "default" : "secondary"}>
                                                {usuario.is_active ? 'Activo' : 'Inactivo'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                {usuario.permisos.map((permiso) => (
                                                    <Badge key={permiso} variant="outline" className="text-xs">
                                                        {permiso}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => toggleEstado(usuario.id)}
                                                >
                                                    <Settings className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => eliminarUsuario(usuario.id)}
                                                    className="text-red-600 hover:text-red-700"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}