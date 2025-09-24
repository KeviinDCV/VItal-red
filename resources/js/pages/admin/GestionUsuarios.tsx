import { Head, useForm, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { useAutoRefresh } from '@/hooks/useAutoRefresh';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Users, UserPlus, Shield, Activity, Search, Filter, Edit, Settings } from 'lucide-react';

interface Usuario {
    id: number;
    name: string;
    email: string;
    role: string;
    status: 'activo' | 'inactivo';
    last_login: string;
    created_at: string;
}

interface Props {
    usuarios: {
        data: Usuario[];
        links: any[];
        meta: any;
    };
    estadisticas: {
        total_usuarios: number;
        usuarios_activos: number;
        usuarios_inactivos: number;
        nuevos_este_mes: number;
    };
}

export default function GestionUsuarios({ usuarios, estadisticas }: Props) {
    const [filtros, setFiltros] = useState({
        busqueda: '',
        rol: '',
        estado: ''
    });

    const [showCreateModal, setShowCreateModal] = useState(false);
    const [editingUser, setEditingUser] = useState<Usuario | null>(null);
    const [autoRefreshEnabled, setAutoRefreshEnabled] = useState(true);

    // Auto-refresh cada segundo
    const { stopRefresh, startRefresh } = useAutoRefresh({
        interval: 1000,
        enabled: autoRefreshEnabled,
        only: ['usuarios', 'estadisticas']
    });

    const { data, setData, post, put, delete: destroy, processing } = useForm({
        name: '',
        email: '',
        password: '',
        role: ''
    });

    const handleSearch = () => {
        router.get(route('admin.usuarios.gestion'), filtros, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const handleCreateUser = () => {
        post(route('admin.usuarios.gestion.store'), {
            onSuccess: () => {
                setShowCreateModal(false);
                setData({ name: '', email: '', password: '', role: '' });
            }
        });
    };

    const handleEditUser = (user: Usuario) => {
        setEditingUser(user);
        setData({
            name: user.name,
            email: user.email,
            password: '',
            role: user.role
        });
    };

    const handleUpdateUser = () => {
        if (editingUser) {
            put(route('admin.usuarios.gestion.update', editingUser.id), {
                onSuccess: () => {
                    setEditingUser(null);
                    setData({ name: '', email: '', password: '', role: '' });
                }
            });
        }
    };

    const handleToggleStatus = (userId: number) => {
        router.patch(route('admin.usuarios.gestion.toggle-status', userId));
    };

    const handleDeleteUser = (userId: number) => {
        if (confirm('¿Está seguro de eliminar este usuario?')) {
            destroy(route('admin.usuarios.gestion.destroy', userId));
        }
    };

    const getRoleBadge = (role: string) => {
        const colors = {
            administrador: 'bg-red-100 text-red-800',
            medico: 'bg-blue-100 text-blue-800',
            ips: 'bg-green-100 text-green-800',
            'jefe-urgencias': 'bg-purple-100 text-purple-800'
        };
        return colors[role as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    const getStatusBadge = (status: string) => {
        return status === 'activo' 
            ? 'bg-green-100 text-green-800' 
            : 'bg-red-100 text-red-800';
    };

    return (
        <>
            <Head title="Gestión de Usuarios" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold">Gestión de Usuarios</h1>
                        <div className="flex items-center gap-2 mt-2">
                            <div className={`w-2 h-2 rounded-full ${autoRefreshEnabled ? 'bg-green-500 animate-pulse' : 'bg-gray-400'}`}></div>
                            <span className="text-sm text-gray-600">
                                {autoRefreshEnabled ? 'Actualizando cada segundo' : 'Actualización pausada'}
                            </span>
                            <Button 
                                size="sm" 
                                variant="outline"
                                onClick={() => {
                                    if (autoRefreshEnabled) {
                                        stopRefresh();
                                        setAutoRefreshEnabled(false);
                                    } else {
                                        startRefresh();
                                        setAutoRefreshEnabled(true);
                                    }
                                }}
                            >
                                {autoRefreshEnabled ? 'Pausar' : 'Reanudar'}
                            </Button>
                        </div>
                    </div>
                    <Button 
                        className="bg-blue-600 hover:bg-blue-700"
                        onClick={() => setShowCreateModal(true)}
                    >
                        <UserPlus className="w-4 h-4 mr-2" />
                        Nuevo Usuario
                    </Button>
                </div>

                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Usuarios</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.total_usuarios}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Usuarios Activos</CardTitle>
                            <Activity className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{estadisticas.usuarios_activos}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Usuarios Inactivos</CardTitle>
                            <Shield className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{estadisticas.usuarios_inactivos}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Nuevos Este Mes</CardTitle>
                            <UserPlus className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{estadisticas.nuevos_este_mes}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="w-5 h-5" />
                            Filtros
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                                <Input
                                    placeholder="Buscar por nombre o email..."
                                    value={filtros.busqueda}
                                    onChange={(e) => setFiltros({...filtros, busqueda: e.target.value})}
                                    onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                    className="pl-10"
                                />
                            </div>
                            
                            <Select value={filtros.rol} onValueChange={(value) => setFiltros({...filtros, rol: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Filtrar por rol" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos los roles</SelectItem>
                                    <SelectItem value="administrador">Administrador</SelectItem>
                                    <SelectItem value="medico">Médico</SelectItem>
                                    <SelectItem value="ips">IPS</SelectItem>
                                    <SelectItem value="jefe-urgencias">Jefe Urgencias</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={filtros.estado} onValueChange={(value) => setFiltros({...filtros, estado: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Filtrar por estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos los estados</SelectItem>
                                    <SelectItem value="activo">Activo</SelectItem>
                                    <SelectItem value="inactivo">Inactivo</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Lista de Usuarios */}
                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Usuarios</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left p-4">Usuario</th>
                                        <th className="text-left p-4">Rol</th>
                                        <th className="text-left p-4">Estado</th>
                                        <th className="text-left p-4">Último Acceso</th>
                                        <th className="text-left p-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {usuarios.data.map((usuario) => (
                                        <tr key={usuario.id} className="border-b hover:bg-gray-50">
                                            <td className="p-4">
                                                <div>
                                                    <div className="font-medium">{usuario.name}</div>
                                                    <div className="text-sm text-gray-500">{usuario.email}</div>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <Badge className={getRoleBadge(usuario.role)}>
                                                    {usuario.role}
                                                </Badge>
                                            </td>
                                            <td className="p-4">
                                                <Badge className={getStatusBadge(usuario.status)}>
                                                    {usuario.status}
                                                </Badge>
                                            </td>
                                            <td className="p-4 text-sm text-gray-500">
                                                {usuario.last_login || 'Nunca'}
                                            </td>
                                            <td className="p-4">
                                                <div className="flex gap-2">
                                                    <Button 
                                                        size="sm" 
                                                        variant="outline"
                                                        onClick={() => handleEditUser(usuario)}
                                                    >
                                                        <Edit className="w-3 h-3 mr-1" />
                                                        Editar
                                                    </Button>
                                                    <Button 
                                                        size="sm" 
                                                        variant="outline"
                                                        onClick={() => router.get(route('admin.usuarios.permisos', usuario.id))}
                                                    >
                                                        <Settings className="w-3 h-3 mr-1" />
                                                        Permisos
                                                    </Button>
                                                    <Button 
                                                        size="sm" 
                                                        variant={usuario.status === 'activo' ? 'destructive' : 'default'}
                                                        onClick={() => handleToggleStatus(usuario.id)}
                                                    >
                                                        {usuario.status === 'activo' ? 'Desactivar' : 'Activar'}
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}