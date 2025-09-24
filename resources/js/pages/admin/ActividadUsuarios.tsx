import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Activity, Clock, User, Calendar, Filter } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';

interface ActividadUsuario {
    id: number;
    usuario: {
        name: string;
        email: string;
        role: string;
    };
    accion: string;
    descripcion: string;
    ip_address: string;
    created_at: string;
}

interface Props {
    actividades: {
        data: ActividadUsuario[];
        links: any[];
        meta: any;
    };
}

export default function ActividadUsuarios({ actividades }: Props) {
    const [filtroAccion, setFiltroAccion] = useState('');
    const [filtroUsuario, setFiltroUsuario] = useState('');

    const getAccionBadge = (accion: string) => {
        const variants = {
            'login': 'default',
            'logout': 'secondary',
            'create': 'default',
            'update': 'outline',
            'delete': 'destructive',
            'view': 'secondary'
        };
        return variants[accion as keyof typeof variants] || 'outline';
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleString('es-ES');
    };

    const actividadesFiltradas = actividades.data.filter(actividad => {
        const matchesAccion = !filtroAccion || actividad.accion === filtroAccion;
        const matchesUsuario = !filtroUsuario || 
            actividad.usuario.name.toLowerCase().includes(filtroUsuario.toLowerCase()) ||
            actividad.usuario.email.toLowerCase().includes(filtroUsuario.toLowerCase());
        return matchesAccion && matchesUsuario;
    });

    return (
        <AppLayout>
            <Head title="Actividad de Usuarios" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold flex items-center gap-2">
                        <Activity className="h-8 w-8" />
                        Actividad de Usuarios
                    </h1>
                </div>

                {/* Filtros */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex gap-4 items-center">
                            <div className="flex-1">
                                <Input
                                    placeholder="Buscar por usuario..."
                                    value={filtroUsuario}
                                    onChange={(e) => setFiltroUsuario(e.target.value)}
                                />
                            </div>
                            <Select value={filtroAccion} onValueChange={setFiltroAccion}>
                                <SelectTrigger className="w-48">
                                    <Filter className="mr-2 h-4 w-4" />
                                    <SelectValue placeholder="Filtrar por acción" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas las acciones</SelectItem>
                                    <SelectItem value="login">Inicios de sesión</SelectItem>
                                    <SelectItem value="logout">Cierres de sesión</SelectItem>
                                    <SelectItem value="create">Creaciones</SelectItem>
                                    <SelectItem value="update">Actualizaciones</SelectItem>
                                    <SelectItem value="delete">Eliminaciones</SelectItem>
                                    <SelectItem value="view">Visualizaciones</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Tabla de actividades */}
                <Card>
                    <CardHeader>
                        <CardTitle>Registro de Actividades</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Usuario</TableHead>
                                    <TableHead>Acción</TableHead>
                                    <TableHead>Descripción</TableHead>
                                    <TableHead>IP</TableHead>
                                    <TableHead>Fecha</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {actividadesFiltradas.map((actividad) => (
                                    <TableRow key={actividad.id}>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{actividad.usuario.name}</div>
                                                <div className="text-sm text-muted-foreground">{actividad.usuario.email}</div>
                                                <Badge variant="outline" className="text-xs mt-1">
                                                    {actividad.usuario.role}
                                                </Badge>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getAccionBadge(actividad.accion)}>
                                                {actividad.accion}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="max-w-md truncate">
                                                {actividad.descripcion}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <code className="text-xs bg-muted px-2 py-1 rounded">
                                                {actividad.ip_address}
                                            </code>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-1 text-sm">
                                                <Clock className="h-3 w-3" />
                                                {formatDate(actividad.created_at)}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {actividadesFiltradas.length === 0 && (
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center py-8">
                                <Activity className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900">
                                    No hay actividades
                                </h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    No se encontraron actividades que coincidan con los filtros aplicados.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}