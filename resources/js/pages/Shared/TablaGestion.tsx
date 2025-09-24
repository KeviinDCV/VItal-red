import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PriorityBadge } from '@/components/referencias/PriorityBadge';
import { StatusTracker } from '@/components/referencias/StatusTracker';
import { TimeIndicator } from '@/components/referencias/TimeIndicator';
import { Search, Filter, Eye, Edit, CheckCircle, XCircle } from 'lucide-react';
import { useState } from 'react';

interface Solicitud {
    id: number;
    codigo_solicitud: string;
    prioridad: 'ROJO' | 'VERDE';
    estado: 'PENDIENTE' | 'ACEPTADO' | 'NO_ADMITIDO';
    fecha_solicitud: string;
    paciente: {
        nombre: string;
        apellidos: string;
        numero_identificacion: string;
    };
    especialidad_solicitada: string;
    ips_origen?: string;
    puntuacion_ia: number;
    tiempo_transcurrido: number;
}

interface Props {
    solicitudes: {
        data: Solicitud[];
        links: any[];
        meta: any;
    };
    userRole: string;
    filtros: {
        especialidades: string[];
        estados: string[];
        ips: string[];
    };
}

export default function TablaGestion({ solicitudes, userRole, filtros }: Props) {
    const [busqueda, setBusqueda] = useState('');
    const [filtroEspecialidad, setFiltroEspecialidad] = useState('');
    const [filtroEstado, setFiltroEstado] = useState('');
    const [filtroPrioridad, setFiltroPrioridad] = useState('');

    const getRoleTitle = () => {
        switch (userRole) {
            case 'administrador': return 'Gestión de Referencias - Vista Ejecutiva';
            case 'jefe_urgencias': return 'Monitoreo de Referencias - Jefe de Urgencias';
            case 'centro_referencia': return 'Gestión Operativa de Solicitudes';
            case 'medico': return 'Gestión de Referencias Médicas';
            default: return 'Gestión de Solicitudes';
        }
    };

    const getAvailableActions = (solicitud: Solicitud) => {
        const actions = [];
        
        // Todos pueden ver
        actions.push({
            label: 'Ver',
            icon: Eye,
            variant: 'outline' as const,
            onClick: () => window.location.href = `/referencias/${solicitud.id}`
        });

        // Según rol y estado
        if (userRole === 'administrador') {
            if (solicitud.estado === 'PENDIENTE') {
                actions.push({
                    label: 'Aceptar',
                    icon: CheckCircle,
                    variant: 'default' as const,
                    onClick: () => procesarSolicitud(solicitud.id, 'ACEPTADO')
                });
                actions.push({
                    label: 'Rechazar',
                    icon: XCircle,
                    variant: 'destructive' as const,
                    onClick: () => procesarSolicitud(solicitud.id, 'NO_ADMITIDO')
                });
            }
        }

        if ((userRole === 'medico' || userRole === 'centro_referencia') && solicitud.estado === 'PENDIENTE') {
            actions.push({
                label: 'Procesar',
                icon: Edit,
                variant: 'default' as const,
                onClick: () => window.location.href = `/medico/referencias/${solicitud.id}`
            });
        }

        return actions;
    };

    const procesarSolicitud = (id: number, decision: string) => {
        // Implementar lógica de procesamiento
        console.log(`Procesando solicitud ${id} con decisión ${decision}`);
    };

    const solicitudesFiltradas = solicitudes.data.filter(solicitud => {
        const matchesBusqueda = 
            solicitud.codigo_solicitud.toLowerCase().includes(busqueda.toLowerCase()) ||
            `${solicitud.paciente.nombre} ${solicitud.paciente.apellidos}`.toLowerCase().includes(busqueda.toLowerCase()) ||
            solicitud.paciente.numero_identificacion.includes(busqueda);
        
        const matchesEspecialidad = !filtroEspecialidad || solicitud.especialidad_solicitada === filtroEspecialidad;
        const matchesEstado = !filtroEstado || solicitud.estado === filtroEstado;
        const matchesPrioridad = !filtroPrioridad || solicitud.prioridad === filtroPrioridad;

        return matchesBusqueda && matchesEspecialidad && matchesEstado && matchesPrioridad;
    });

    return (
        <>
            <Head title={getRoleTitle()} />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">{getRoleTitle()}</h1>
                    <Badge variant="outline" className="text-lg px-3 py-1">
                        {solicitudesFiltradas.length} solicitudes
                    </Badge>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Filtros y Búsqueda
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-5">
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Buscar por código, paciente o ID..."
                                    value={busqueda}
                                    onChange={(e) => setBusqueda(e.target.value)}
                                    className="pl-10"
                                />
                            </div>
                            
                            <Select value={filtroEspecialidad} onValueChange={setFiltroEspecialidad}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Especialidad" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas</SelectItem>
                                    {filtros.especialidades.map(esp => (
                                        <SelectItem key={esp} value={esp}>{esp}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            
                            <Select value={filtroEstado} onValueChange={setFiltroEstado}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="PENDIENTE">Pendiente</SelectItem>
                                    <SelectItem value="ACEPTADO">Aceptado</SelectItem>
                                    <SelectItem value="NO_ADMITIDO">No Admitido</SelectItem>
                                </SelectContent>
                            </Select>
                            
                            <Select value={filtroPrioridad} onValueChange={setFiltroPrioridad}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Prioridad" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas</SelectItem>
                                    <SelectItem value="ROJO">ROJO (Alta)</SelectItem>
                                    <SelectItem value="VERDE">VERDE (Normal)</SelectItem>
                                </SelectContent>
                            </Select>
                            
                            <Button 
                                variant="outline" 
                                onClick={() => {
                                    setBusqueda('');
                                    setFiltroEspecialidad('');
                                    setFiltroEstado('');
                                    setFiltroPrioridad('');
                                }}
                            >
                                Limpiar
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Tabla de Solicitudes */}
                <Card>
                    <CardHeader>
                        <CardTitle>Lista Priorizada de Solicitudes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Código</TableHead>
                                    <TableHead>Paciente</TableHead>
                                    <TableHead>Especialidad</TableHead>
                                    <TableHead>Prioridad</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Tiempo</TableHead>
                                    <TableHead>IA Score</TableHead>
                                    {userRole !== 'jefe_urgencias' && <TableHead>Acciones</TableHead>}
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {solicitudesFiltradas.map((solicitud) => (
                                    <TableRow key={solicitud.id} className={solicitud.prioridad === 'ROJO' ? 'bg-red-50' : ''}>
                                        <TableCell className="font-medium">
                                            {solicitud.codigo_solicitud}
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">
                                                    {solicitud.paciente.nombre} {solicitud.paciente.apellidos}
                                                </div>
                                                <div className="text-sm text-muted-foreground">
                                                    {solicitud.paciente.numero_identificacion}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{solicitud.especialidad_solicitada}</TableCell>
                                        <TableCell>
                                            <PriorityBadge priority={solicitud.prioridad} />
                                        </TableCell>
                                        <TableCell>
                                            <StatusTracker status={solicitud.estado} />
                                        </TableCell>
                                        <TableCell>
                                            <TimeIndicator date={solicitud.fecha_solicitud} />
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {Math.round(solicitud.puntuacion_ia * 100)}%
                                            </Badge>
                                        </TableCell>
                                        {userRole !== 'jefe_urgencias' && (
                                            <TableCell>
                                                <div className="flex gap-1">
                                                    {getAvailableActions(solicitud).map((action, index) => (
                                                        <Button
                                                            key={index}
                                                            size="sm"
                                                            variant={action.variant}
                                                            onClick={action.onClick}
                                                        >
                                                            <action.icon className="h-4 w-4" />
                                                        </Button>
                                                    ))}
                                                </div>
                                            </TableCell>
                                        )}
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        
                        {solicitudesFiltradas.length === 0 && (
                            <div className="text-center py-8">
                                <p className="text-muted-foreground">
                                    No se encontraron solicitudes que coincidan con los filtros.
                                </p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}