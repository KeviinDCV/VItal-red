import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    AlertTriangle, 
    Clock, 
    User, 
    Stethoscope,
    CheckCircle,
    XCircle,
    Eye,
    Zap
} from 'lucide-react';
import { useState, useEffect } from 'react';
import { useForm } from '@inertiajs/react';

interface CasoCritico {
    id: number;
    codigo_solicitud: string;
    prioridad: 'ROJO';
    estado: 'PENDIENTE' | 'EN_REVISION';
    fecha_solicitud: string;
    tiempo_transcurrido: number; // en horas
    registro_medico: {
        nombre: string;
        apellidos: string;
        numero_identificacion: string;
        edad: number;
        especialidad_solicitada: string;
        motivo_consulta: string;
        diagnostico_principal: string;
        signos_vitales: string;
        sintomas_alarma: string[];
    };
    ips: {
        nombre: string;
        telefono: string;
        email: string;
    };
    puntuacion_ia: number;
    observaciones_ia: string;
    medico_asignado?: string;
}

interface Props {
    casosCriticos: {
        data: CasoCritico[];
        meta: any;
    };
}

export default function CasosCriticos({ casosCriticos }: Props) {
    const [selectedCaso, setSelectedCaso] = useState<CasoCritico | null>(null);
    const [showDecisionModal, setShowDecisionModal] = useState(false);
    const [timeoutAlerts, setTimeoutAlerts] = useState<number[]>([]);

    const { data, setData, post, processing, reset } = useForm({
        decision: '',
        justificacion: '',
        especialista_asignado: '',
        fecha_cita: '',
        observaciones: '',
        prioridad_ajustada: ''
    });

    useEffect(() => {
        // Identificar casos que están por vencer (>1.5 horas)
        const alerts = casosCriticos.data
            .filter(caso => caso.tiempo_transcurrido > 1.5)
            .map(caso => caso.id);
        setTimeoutAlerts(alerts);

        // Actualizar cada minuto
        const interval = setInterval(() => {
            window.location.reload();
        }, 60000);

        return () => clearInterval(interval);
    }, [casosCriticos.data]);

    const handleDecision = (casoId: number) => {
        post(`/medico/casos-criticos/${casoId}/procesar`, {
            onSuccess: () => {
                setShowDecisionModal(false);
                reset();
                setSelectedCaso(null);
            }
        });
    };

    const getUrgencyLevel = (tiempoTranscurrido: number) => {
        if (tiempoTranscurrido > 2) return 'CRÍTICO';
        if (tiempoTranscurrido > 1.5) return 'URGENTE';
        if (tiempoTranscurrido > 1) return 'ALTO';
        return 'NORMAL';
    };

    const getUrgencyColor = (nivel: string) => {
        switch (nivel) {
            case 'CRÍTICO': return 'bg-red-600 text-white animate-pulse';
            case 'URGENTE': return 'bg-red-500 text-white';
            case 'ALTO': return 'bg-orange-500 text-white';
            default: return 'bg-yellow-500 text-white';
        }
    };

    const formatTimeElapsed = (horas: number) => {
        if (horas < 1) {
            return `${Math.round(horas * 60)}m`;
        }
        return `${Math.round(horas * 10) / 10}h`;
    };

    const getSintomasAlarma = (sintomas: string[]) => {
        const sintomasGraves = ['dolor_toracico', 'disnea', 'alteracion_conciencia', 'sangrado_severo'];
        return sintomas.filter(s => sintomasGraves.includes(s));
    };

    return (
        <>
            <Head title="Casos Críticos - Gestión ROJA" />
            
            <div className="space-y-6">
                {/* Header con estadísticas críticas */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold flex items-center gap-2 text-red-600">
                            <AlertTriangle className="h-8 w-8" />
                            Casos Críticos ROJOS
                        </h1>
                        <p className="text-muted-foreground">
                            Requieren atención inmediata - Meta: menos de 2 horas respuesta
                        </p>
                    </div>
                    <div className="flex gap-4">
                        <Card className="p-4">
                            <div className="text-2xl font-bold text-red-600">
                                {casosCriticos.data.length}
                            </div>
                            <div className="text-sm text-muted-foreground">
                                Casos pendientes
                            </div>
                        </Card>
                        <Card className="p-4">
                            <div className="text-2xl font-bold text-orange-600">
                                {timeoutAlerts.length}
                            </div>
                            <div className="text-sm text-muted-foreground">
                                Por vencer
                            </div>
                        </Card>
                    </div>
                </div>

                {/* Alertas de timeout */}
                {timeoutAlerts.length > 0 && (
                    <Card className="border-red-200 bg-red-50">
                        <CardHeader>
                            <CardTitle className="text-red-700 flex items-center gap-2">
                                <Zap className="h-5 w-5" />
                                ⚠️ {timeoutAlerts.length} casos críticos requieren atención INMEDIATA
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-red-600">
                                Estos casos han superado el tiempo límite de respuesta de 2 horas.
                            </p>
                        </CardContent>
                    </Card>
                )}

                {/* Tabla de casos críticos */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <AlertTriangle className="h-5 w-5 text-red-500" />
                            Lista de Casos ROJOS Pendientes
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Urgencia</TableHead>
                                    <TableHead>Código</TableHead>
                                    <TableHead>Paciente</TableHead>
                                    <TableHead>Especialidad</TableHead>
                                    <TableHead>Tiempo</TableHead>
                                    <TableHead>IA Score</TableHead>
                                    <TableHead>Síntomas Alarma</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {casosCriticos.data.map((caso) => {
                                    const urgencyLevel = getUrgencyLevel(caso.tiempo_transcurrido);
                                    const isTimeout = timeoutAlerts.includes(caso.id);
                                    
                                    return (
                                        <TableRow key={caso.id} className={isTimeout ? 'bg-red-50' : ''}>
                                            <TableCell>
                                                <Badge className={getUrgencyColor(urgencyLevel)}>
                                                    {urgencyLevel}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {caso.codigo_solicitud}
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">
                                                        {caso.registro_medico.nombre} {caso.registro_medico.apellidos}
                                                    </div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {caso.registro_medico.edad} años - {caso.registro_medico.numero_identificacion}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {caso.registro_medico.especialidad_solicitada}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1">
                                                    <Clock className={`h-4 w-4 ${isTimeout ? 'text-red-500' : 'text-orange-500'}`} />
                                                    <span className={isTimeout ? 'text-red-600 font-bold' : 'text-orange-600'}>
                                                        {formatTimeElapsed(caso.tiempo_transcurrido)}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="destructive">
                                                    {Math.round(caso.puntuacion_ia * 100)}%
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-wrap gap-1">
                                                    {getSintomasAlarma(caso.registro_medico.sintomas_alarma).map((sintoma, idx) => (
                                                        <Badge key={idx} variant="destructive" className="text-xs">
                                                            {sintoma.replace('_', ' ')}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-2">
                                                    <Dialog>
                                                        <DialogTrigger asChild>
                                                            <Button 
                                                                size="sm" 
                                                                variant="outline"
                                                                onClick={() => setSelectedCaso(caso)}
                                                            >
                                                                <Eye className="h-4 w-4 mr-1" />
                                                                Ver
                                                            </Button>
                                                        </DialogTrigger>
                                                        <DialogContent className="max-w-4xl">
                                                            <DialogHeader>
                                                                <DialogTitle className="flex items-center gap-2">
                                                                    <AlertTriangle className="h-5 w-5 text-red-500" />
                                                                    Caso Crítico - {selectedCaso?.codigo_solicitud}
                                                                </DialogTitle>
                                                            </DialogHeader>
                                                            {selectedCaso && (
                                                                <div className="space-y-6">
                                                                    {/* Información del paciente */}
                                                                    <div className="grid grid-cols-2 gap-6">
                                                                        <Card>
                                                                            <CardHeader>
                                                                                <CardTitle className="text-lg flex items-center gap-2">
                                                                                    <User className="h-5 w-5" />
                                                                                    Información del Paciente
                                                                                </CardTitle>
                                                                            </CardHeader>
                                                                            <CardContent className="space-y-3">
                                                                                <div>
                                                                                    <Label>Nombre Completo</Label>
                                                                                    <p className="font-medium">
                                                                                        {selectedCaso.registro_medico.nombre} {selectedCaso.registro_medico.apellidos}
                                                                                    </p>
                                                                                </div>
                                                                                <div>
                                                                                    <Label>Documento</Label>
                                                                                    <p>{selectedCaso.registro_medico.numero_identificacion}</p>
                                                                                </div>
                                                                                <div>
                                                                                    <Label>Edad</Label>
                                                                                    <p>{selectedCaso.registro_medico.edad} años</p>
                                                                                </div>
                                                                            </CardContent>
                                                                        </Card>

                                                                        <Card>
                                                                            <CardHeader>
                                                                                <CardTitle className="text-lg flex items-center gap-2">
                                                                                    <Stethoscope className="h-5 w-5" />
                                                                                    Información Clínica
                                                                                </CardTitle>
                                                                            </CardHeader>
                                                                            <CardContent className="space-y-3">
                                                                                <div>
                                                                                    <Label>Especialidad Solicitada</Label>
                                                                                    <p className="font-medium text-red-600">
                                                                                        {selectedCaso.registro_medico.especialidad_solicitada}
                                                                                    </p>
                                                                                </div>
                                                                                <div>
                                                                                    <Label>Diagnóstico Principal</Label>
                                                                                    <p>{selectedCaso.registro_medico.diagnostico_principal}</p>
                                                                                </div>
                                                                                <div>
                                                                                    <Label>Signos Vitales</Label>
                                                                                    <p>{selectedCaso.registro_medico.signos_vitales}</p>
                                                                                </div>
                                                                            </CardContent>
                                                                        </Card>
                                                                    </div>

                                                                    {/* Motivo de consulta */}
                                                                    <Card>
                                                                        <CardHeader>
                                                                            <CardTitle>Motivo de Consulta</CardTitle>
                                                                        </CardHeader>
                                                                        <CardContent>
                                                                            <p>{selectedCaso.registro_medico.motivo_consulta}</p>
                                                                        </CardContent>
                                                                    </Card>

                                                                    {/* Análisis IA */}
                                                                    <Card className="border-red-200">
                                                                        <CardHeader>
                                                                            <CardTitle className="text-red-600">Análisis de Inteligencia Artificial</CardTitle>
                                                                        </CardHeader>
                                                                        <CardContent>
                                                                            <div className="grid grid-cols-2 gap-4 mb-4">
                                                                                <div>
                                                                                    <Label>Puntuación de Riesgo</Label>
                                                                                    <div className="text-2xl font-bold text-red-600">
                                                                                        {Math.round(selectedCaso.puntuacion_ia * 100)}%
                                                                                    </div>
                                                                                </div>
                                                                                <div>
                                                                                    <Label>Tiempo Transcurrido</Label>
                                                                                    <div className="text-2xl font-bold text-orange-600">
                                                                                        {formatTimeElapsed(selectedCaso.tiempo_transcurrido)}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <Label>Observaciones del Algoritmo</Label>
                                                                                <p className="bg-red-50 p-3 rounded mt-2">
                                                                                    {selectedCaso.observaciones_ia}
                                                                                </p>
                                                                            </div>
                                                                        </CardContent>
                                                                    </Card>
                                                                </div>
                                                            )}
                                                        </DialogContent>
                                                    </Dialog>

                                                    <Dialog open={showDecisionModal} onOpenChange={setShowDecisionModal}>
                                                        <DialogTrigger asChild>
                                                            <Button 
                                                                size="sm" 
                                                                className="bg-red-600 hover:bg-red-700"
                                                                onClick={() => setSelectedCaso(caso)}
                                                            >
                                                                <CheckCircle className="h-4 w-4 mr-1" />
                                                                DECIDIR
                                                            </Button>
                                                        </DialogTrigger>
                                                        <DialogContent>
                                                            <DialogHeader>
                                                                <DialogTitle className="text-red-600">
                                                                    Procesar Caso Crítico ROJO
                                                                </DialogTitle>
                                                            </DialogHeader>
                                                            <div className="space-y-4">
                                                                <div>
                                                                    <Label>Decisión *</Label>
                                                                    <Select value={data.decision} onValueChange={(value) => setData('decision', value)}>
                                                                        <SelectTrigger>
                                                                            <SelectValue placeholder="Seleccionar decisión" />
                                                                        </SelectTrigger>
                                                                        <SelectContent>
                                                                            <SelectItem value="ACEPTADO">✅ ACEPTAR - Ingreso inmediato</SelectItem>
                                                                            <SelectItem value="ACEPTADO_PROGRAMADO">📅 ACEPTAR - Programar cita urgente</SelectItem>
                                                                            <SelectItem value="NO_ADMITIDO">❌ NO ADMITIR - Justificar</SelectItem>
                                                                        </SelectContent>
                                                                    </Select>
                                                                </div>

                                                                <div>
                                                                    <Label>Justificación Médica *</Label>
                                                                    <Textarea 
                                                                        value={data.justificacion}
                                                                        onChange={(e) => setData('justificacion', e.target.value)}
                                                                        placeholder="Justificación detallada de la decisión médica..."
                                                                        rows={4}
                                                                    />
                                                                </div>

                                                                {(data.decision === 'ACEPTADO' || data.decision === 'ACEPTADO_PROGRAMADO') && (
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
                                                                            <Label>Fecha de Atención</Label>
                                                                            <Input 
                                                                                type="datetime-local"
                                                                                value={data.fecha_cita}
                                                                                onChange={(e) => setData('fecha_cita', e.target.value)}
                                                                            />
                                                                        </div>
                                                                    </>
                                                                )}

                                                                <div className="flex gap-2 justify-end pt-4">
                                                                    <Button 
                                                                        variant="outline" 
                                                                        onClick={() => setShowDecisionModal(false)}
                                                                    >
                                                                        Cancelar
                                                                    </Button>
                                                                    <Button 
                                                                        onClick={() => selectedCaso && handleDecision(selectedCaso.id)}
                                                                        disabled={processing || !data.decision || !data.justificacion}
                                                                        className="bg-red-600 hover:bg-red-700"
                                                                    >
                                                                        {processing ? 'Procesando...' : 'Confirmar Decisión'}
                                                                    </Button>
                                                                </div>
                                                            </div>
                                                        </DialogContent>
                                                    </Dialog>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>

                        {casosCriticos.data.length === 0 && (
                            <div className="text-center py-8">
                                <CheckCircle className="mx-auto h-12 w-12 text-green-500" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900">
                                    No hay casos críticos pendientes
                                </h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Todos los casos ROJOS han sido procesados.
                                </p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}