import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Activity, Clock, CheckCircle, XCircle, AlertTriangle } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';

interface Estadisticas {
    pendientes: number;
    aceptadas: number;
    rechazadas: number;
    rojas: number;
    verdes: number;
}

interface Solicitud {
    id: number;
    codigo_solicitud: string;
    prioridad: 'ROJO' | 'VERDE';
    estado: 'PENDIENTE' | 'ACEPTADO' | 'NO_ADMITIDO';
    fecha_solicitud: string;
    registro_medico: {
        nombre: string;
        apellidos: string;
        numero_identificacion: string;
        especialidad_solicitada: string;
    };
    puntuacion_ia: number;
    observaciones_ia: string;
}

interface Props {
    estadisticas: Estadisticas;
    solicitudes: {
        data: Solicitud[];
        links: any[];
        meta: any;
    };
}

export default function DashboardReferencias({ estadisticas, solicitudes }: Props) {
    const [selectedSolicitud, setSelectedSolicitud] = useState<Solicitud | null>(null);

    const getPriorityColor = (prioridad: string) => {
        return prioridad === 'ROJO' ? 'bg-red-500' : 'bg-green-500';
    };

    const getStatusIcon = (estado: string) => {
        switch (estado) {
            case 'PENDIENTE':
                return <Clock className="h-4 w-4 text-yellow-500" />;
            case 'ACEPTADO':
                return <CheckCircle className="h-4 w-4 text-green-500" />;
            case 'NO_ADMITIDO':
                return <XCircle className="h-4 w-4 text-red-500" />;
            default:
                return <AlertTriangle className="h-4 w-4 text-gray-500" />;
        }
    };

    return (
        <AppLayout>
            <Head title="Dashboard de Referencias" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Dashboard de Referencias</h1>
                    <Button variant="outline">
                        <Activity className="mr-2 h-4 w-4" />
                        Actualizar
                    </Button>
                </div>

                {/* Métricas principales */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pendientes</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.pendientes}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Aceptadas</CardTitle>
                            <CheckCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{estadisticas.aceptadas}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Rechazadas</CardTitle>
                            <XCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{estadisticas.rechazadas}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Prioridad ROJA</CardTitle>
                            <div className="h-4 w-4 rounded-full bg-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{estadisticas.rojas}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Prioridad VERDE</CardTitle>
                            <div className="h-4 w-4 rounded-full bg-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{estadisticas.verdes}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabla de solicitudes */}
                <Card>
                    <CardHeader>
                        <CardTitle>Solicitudes de Referencia</CardTitle>
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
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>IA Score</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {solicitudes.data.map((solicitud) => (
                                    <TableRow key={solicitud.id}>
                                        <TableCell className="font-medium">
                                            {solicitud.codigo_solicitud}
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">
                                                    {solicitud.registro_medico.nombre} {solicitud.registro_medico.apellidos}
                                                </div>
                                                <div className="text-sm text-muted-foreground">
                                                    {solicitud.registro_medico.numero_identificacion}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{solicitud.registro_medico.especialidad_solicitada}</TableCell>
                                        <TableCell>
                                            <Badge className={`${getPriorityColor(solicitud.prioridad)} text-white`}>
                                                {solicitud.prioridad}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                {getStatusIcon(solicitud.estado)}
                                                {solicitud.estado}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {new Date(solicitud.fecha_solicitud).toLocaleDateString()}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {Math.round(solicitud.puntuacion_ia * 100)}%
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Button size="sm" variant="outline">
                                                    Ver
                                                </Button>
                                                {solicitud.estado === 'PENDIENTE' && (
                                                    <>
                                                        <Button size="sm" variant="default">
                                                            Aceptar
                                                        </Button>
                                                        <Button size="sm" variant="destructive">
                                                            Rechazar
                                                        </Button>
                                                    </>
                                                )}
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