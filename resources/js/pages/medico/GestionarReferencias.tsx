import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Clock, Filter, Eye, CheckCircle, XCircle } from 'lucide-react';
import { useState } from 'react';
import { useForm } from '@inertiajs/react';

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
        enfermedad_actual: string;
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
    filtros: {
        estado?: string;
        prioridad?: string;
        especialidad?: string;
    };
}

export default function GestionarReferencias({ solicitudes, filtros }: Props) {
    const [selectedSolicitud, setSelectedSolicitud] = useState<Solicitud | null>(null);
    const [showDecisionModal, setShowDecisionModal] = useState(false);

    const { data, setData, post, processing } = useForm({
        decision: '',
        justificacion: '',
        especialista_asignado: '',
        fecha_cita: '',
        observaciones: ''
    });

    const handleDecision = (solicitudId: number) => {
        post(`/medico/referencias/${solicitudId}/procesar`, {
            onSuccess: () => {
                setShowDecisionModal(false);
                setData({
                    decision: '',
                    justificacion: '',
                    especialista_asignado: '',
                    fecha_cita: '',
                    observaciones: ''
                });
            }
        });
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
            <Head title="Gestionar Referencias" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Gestionar Referencias</h1>
                    <div className="flex gap-2">
                        <Select defaultValue={filtros.estado}>
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder="Estado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="PENDIENTE">Pendientes</SelectItem>
                                <SelectItem value="ACEPTADO">Aceptadas</SelectItem>
                                <SelectItem value="NO_ADMITIDO">Rechazadas</SelectItem>
                            </SelectContent>
                        </Select>
                        
                        <Select defaultValue={filtros.prioridad}>
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder="Prioridad" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas</SelectItem>
                                <SelectItem value="ROJO">ROJO</SelectItem>
                                <SelectItem value="VERDE">VERDE</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Solicitudes Asignadas</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Código</TableHead>
                                    <TableHead>Paciente</TableHead>
                                    <TableHead>Especialidad</TableHead>
                                    <TableHead>Prioridad</TableHead>
                                    <TableHead>Tiempo</TableHead>
                                    <TableHead>Estado</TableHead>
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
                                            <div className="flex items-center gap-1">
                                                <Clock className="h-4 w-4" />
                                                {getTimeElapsed(solicitud.fecha_solicitud)}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={solicitud.estado === 'PENDIENTE' ? 'secondary' : 'default'}>
                                                {solicitud.estado}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Dialog>
                                                    <DialogTrigger asChild>
                                                        <Button size="sm" variant="outline" onClick={() => setSelectedSolicitud(solicitud)}>
                                                            <Eye className="h-4 w-4 mr-1" />
                                                            Ver
                                                        </Button>
                                                    </DialogTrigger>
                                                    <DialogContent className="max-w-2xl">
                                                        <DialogHeader>
                                                            <DialogTitle>Detalle de Solicitud</DialogTitle>
                                                        </DialogHeader>
                                                        {selectedSolicitud && (
                                                            <div className="space-y-4">
                                                                <div className="grid grid-cols-2 gap-4">
                                                                    <div>
                                                                        <Label>Paciente</Label>
                                                                        <p>{selectedSolicitud.registro_medico.nombre} {selectedSolicitud.registro_medico.apellidos}</p>
                                                                    </div>
                                                                    <div>
                                                                        <Label>Documento</Label>
                                                                        <p>{selectedSolicitud.registro_medico.numero_identificacion}</p>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <Label>Motivo de Consulta</Label>
                                                                    <p>{selectedSolicitud.registro_medico.motivo_consulta}</p>
                                                                </div>
                                                                <div>
                                                                    <Label>Diagnóstico Principal</Label>
                                                                    <p>{selectedSolicitud.registro_medico.diagnostico_principal}</p>
                                                                </div>
                                                                <div>
                                                                    <Label>Enfermedad Actual</Label>
                                                                    <p>{selectedSolicitud.registro_medico.enfermedad_actual}</p>
                                                                </div>
                                                                <div>
                                                                    <Label>Observaciones IA</Label>
                                                                    <p>{selectedSolicitud.observaciones_ia}</p>
                                                                </div>
                                                            </div>
                                                        )}
                                                    </DialogContent>
                                                </Dialog>
                                                
                                                {solicitud.estado === 'PENDIENTE' && (
                                                    <Dialog open={showDecisionModal} onOpenChange={setShowDecisionModal}>
                                                        <DialogTrigger asChild>
                                                            <Button size="sm" onClick={() => setSelectedSolicitud(solicitud)}>
                                                                Decidir
                                                            </Button>
                                                        </DialogTrigger>
                                                        <DialogContent>
                                                            <DialogHeader>
                                                                <DialogTitle>Procesar Solicitud</DialogTitle>
                                                            </DialogHeader>
                                                            <div className="space-y-4">
                                                                <div>
                                                                    <Label>Decisión</Label>
                                                                    <Select value={data.decision} onValueChange={(value) => setData('decision', value)}>
                                                                        <SelectTrigger>
                                                                            <SelectValue placeholder="Seleccionar decisión" />
                                                                        </SelectTrigger>
                                                                        <SelectContent>
                                                                            <SelectItem value="aceptada">Aceptar</SelectItem>
                                                                            <SelectItem value="rechazada">Rechazar</SelectItem>
                                                                        </SelectContent>
                                                                    </Select>
                                                                </div>
                                                                
                                                                <div>
                                                                    <Label>Justificación</Label>
                                                                    <Textarea 
                                                                        value={data.justificacion}
                                                                        onChange={(e) => setData('justificacion', e.target.value)}
                                                                        placeholder="Justificación de la decisión..."
                                                                    />
                                                                </div>
                                                                
                                                                {data.decision === 'aceptada' && (
                                                                    <>
                                                                        <div>
                                                                            <Label>Especialista Asignado</Label>
                                                                            <Input 
                                                                                value={data.especialista_asignado}
                                                                                onChange={(e) => setData('especialista_asignado', e.target.value)}
                                                                                placeholder="Nombre del especialista"
                                                                            />
                                                                        </div>
                                                                        
                                                                        <div>
                                                                            <Label>Fecha de Cita</Label>
                                                                            <Input 
                                                                                type="date"
                                                                                value={data.fecha_cita}
                                                                                onChange={(e) => setData('fecha_cita', e.target.value)}
                                                                            />
                                                                        </div>
                                                                    </>
                                                                )}
                                                                
                                                                <div className="flex gap-2 justify-end">
                                                                    <Button variant="outline" onClick={() => setShowDecisionModal(false)}>
                                                                        Cancelar
                                                                    </Button>
                                                                    <Button 
                                                                        onClick={() => selectedSolicitud && handleDecision(selectedSolicitud.id)}
                                                                        disabled={processing}
                                                                    >
                                                                        Confirmar
                                                                    </Button>
                                                                </div>
                                                            </div>
                                                        </DialogContent>
                                                    </Dialog>
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
        </>
    );
}