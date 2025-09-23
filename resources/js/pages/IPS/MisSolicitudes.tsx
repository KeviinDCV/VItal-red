import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Clock, Eye, Plus, FileText } from 'lucide-react';
import { useState } from 'react';
import { useAutoRefresh } from '@/hooks/useAutoRefresh';

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
        motivo_consulta: string;
        diagnostico_principal: string;
    };
    decision?: {
        decision: string;
        justificacion: string;
        especialista_asignado?: string;
        fecha_cita?: string;
    };
    observaciones_ia: string;
    puntuacion_ia: number;
}

interface Props {
    solicitudes: {
        data: Solicitud[];
        links: any[];
        meta: any;
    };
}

export default function MisSolicitudes({ solicitudes }: Props) {
    const [autoRefreshEnabled, setAutoRefreshEnabled] = useState(true);

    // Auto-refresh cada segundo para solicitudes IPS
    const { stopRefresh, startRefresh } = useAutoRefresh({
        interval: 1000,
        enabled: autoRefreshEnabled,
        only: ['solicitudes']
    });

    const getStatusColor = (estado: string) => {
        switch (estado) {
            case 'PENDIENTE':
                return 'bg-yellow-500';
            case 'ACEPTADO':
                return 'bg-green-500';
            case 'NO_ADMITIDO':
                return 'bg-red-500';
            default:
                return 'bg-gray-500';
        }
    };

    const getStatusText = (estado: string) => {
        switch (estado) {
            case 'PENDIENTE':
                return 'Pendiente';
            case 'ACEPTADO':
                return 'Aceptada';
            case 'NO_ADMITIDO':
                return 'Rechazada';
            default:
                return estado;
        }
    };

    const getTimeElapsed = (fecha: string) => {
        const now = new Date();
        const solicitudDate = new Date(fecha);
        const diffHours = Math.floor((now.getTime() - solicitudDate.getTime()) / (1000 * 60 * 60));
        
        if (diffHours < 24) {
            return `${diffHours}h`;
        } else {
            const diffDays = Math.floor(diffHours / 24);
            return `${diffDays}d`;
        }
    };

    return (
        <>
            <Head title="Mis Solicitudes" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Mis Solicitudes de Referencia</h1>
                        <div className="flex items-center gap-2 mt-2">
                            <div className={`w-2 h-2 rounded-full ${autoRefreshEnabled ? 'bg-green-500 animate-pulse' : 'bg-gray-400'}`}></div>
                            <span className="text-sm text-gray-600">
                                {autoRefreshEnabled ? 'Actualizando solicitudes' : 'Actualización pausada'}
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
                    <Button asChild>
                        <a href="/ips/solicitar">
                            <Plus className="mr-2 h-4 w-4" />
                            Nueva Solicitud
                        </a>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Historial de Solicitudes</CardTitle>
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
                                            <Badge className={solicitud.prioridad === 'ROJO' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}>
                                                {solicitud.prioridad}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge className={`${getStatusColor(solicitud.estado)} text-white`}>
                                                {getStatusText(solicitud.estado)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-1">
                                                <Clock className="h-4 w-4" />
                                                {getTimeElapsed(solicitud.fecha_solicitud)}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {Math.round(solicitud.puntuacion_ia * 100)}%
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button size="sm" variant="outline">
                                                        <Eye className="h-4 w-4 mr-1" />
                                                        Ver
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent className="max-w-3xl">
                                                    <DialogHeader>
                                                        <DialogTitle>Detalle de Solicitud - {solicitud.codigo_solicitud}</DialogTitle>
                                                    </DialogHeader>
                                                    <div className="space-y-6">
                                                        {/* Información del Paciente */}
                                                        <div>
                                                            <h3 className="text-lg font-semibold mb-3">Información del Paciente</h3>
                                                            <div className="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <Label>Nombre Completo</Label>
                                                                    <p className="text-sm">{solicitud.registro_medico.nombre} {solicitud.registro_medico.apellidos}</p>
                                                                </div>
                                                                <div>
                                                                    <Label>Documento</Label>
                                                                    <p className="text-sm">{solicitud.registro_medico.numero_identificacion}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {/* Información Clínica */}
                                                        <div>
                                                            <h3 className="text-lg font-semibold mb-3">Información Clínica</h3>
                                                            <div className="space-y-3">
                                                                <div>
                                                                    <Label>Especialidad Solicitada</Label>
                                                                    <p className="text-sm">{solicitud.registro_medico.especialidad_solicitada}</p>
                                                                </div>
                                                                <div>
                                                                    <Label>Motivo de Consulta</Label>
                                                                    <p className="text-sm">{solicitud.registro_medico.motivo_consulta}</p>
                                                                </div>
                                                                <div>
                                                                    <Label>Diagnóstico Principal</Label>
                                                                    <p className="text-sm">{solicitud.registro_medico.diagnostico_principal}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {/* Estado de la Solicitud */}
                                                        <div>
                                                            <h3 className="text-lg font-semibold mb-3">Estado de la Solicitud</h3>
                                                            <div className="grid grid-cols-3 gap-4">
                                                                <div>
                                                                    <Label>Estado</Label>
                                                                    <Badge className={`${getStatusColor(solicitud.estado)} text-white`}>
                                                                        {getStatusText(solicitud.estado)}
                                                                    </Badge>
                                                                </div>
                                                                <div>
                                                                    <Label>Prioridad</Label>
                                                                    <Badge className={solicitud.prioridad === 'ROJO' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}>
                                                                        {solicitud.prioridad}
                                                                    </Badge>
                                                                </div>
                                                                <div>
                                                                    <Label>Puntuación IA</Label>
                                                                    <p className="text-sm">{Math.round(solicitud.puntuacion_ia * 100)}%</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {/* Observaciones IA */}
                                                        <div>
                                                            <Label>Observaciones del Algoritmo IA</Label>
                                                            <p className="text-sm bg-gray-50 p-3 rounded">{solicitud.observaciones_ia}</p>
                                                        </div>

                                                        {/* Decisión (si existe) */}
                                                        {solicitud.decision && (
                                                            <div>
                                                                <h3 className="text-lg font-semibold mb-3">Decisión Médica</h3>
                                                                <div className="space-y-3">
                                                                    <div>
                                                                        <Label>Decisión</Label>
                                                                        <p className="text-sm font-medium">{solicitud.decision.decision}</p>
                                                                    </div>
                                                                    <div>
                                                                        <Label>Justificación</Label>
                                                                        <p className="text-sm">{solicitud.decision.justificacion}</p>
                                                                    </div>
                                                                    {solicitud.decision.especialista_asignado && (
                                                                        <div>
                                                                            <Label>Especialista Asignado</Label>
                                                                            <p className="text-sm">{solicitud.decision.especialista_asignado}</p>
                                                                        </div>
                                                                    )}
                                                                    {solicitud.decision.fecha_cita && (
                                                                        <div>
                                                                            <Label>Fecha de Cita</Label>
                                                                            <p className="text-sm">{new Date(solicitud.decision.fecha_cita).toLocaleDateString()}</p>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                </DialogContent>
                                            </Dialog>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {solicitudes.data.length === 0 && (
                            <div className="text-center py-8">
                                <FileText className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900">No hay solicitudes</h3>
                                <p className="mt-1 text-sm text-gray-500">Comience creando una nueva solicitud de referencia.</p>
                                <div className="mt-6">
                                    <Button asChild>
                                        <a href="/ips/solicitar">
                                            <Plus className="mr-2 h-4 w-4" />
                                            Nueva Solicitud
                                        </a>
                                    </Button>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}