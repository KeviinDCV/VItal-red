import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/react';
import { Activity, Calendar, FileText, User } from 'lucide-react';

interface Paciente {
    id: number;
    codigo_solicitud: string;
    registro_medico: {
        nombre: string;
        apellidos: string;
        numero_identificacion: string;
        especialidad_solicitada: string;
    };
    seguimiento?: {
        estado: string;
        fecha_ingreso?: string;
        observaciones?: string;
    };
    created_at: string;
}

interface Props {
    pacientes: {
        data: Paciente[];
    };
}

export default function SeguimientoPacientes({ pacientes }: Props) {
    const { data, setData, post, processing, reset } = useForm({
        estado_seguimiento: '',
        fecha_ingreso: '',
        observaciones: '',
        diagnostico_final: '',
        tratamiento_realizado: ''
    });

    const handleActualizar = (solicitudId: number) => {
        post(`/medico/seguimiento/${solicitudId}`, {
            onSuccess: () => reset()
        });
    };

    const getEstadoColor = (estado: string) => {
        switch (estado) {
            case 'programado': return 'bg-blue-500';
            case 'ingresado': return 'bg-green-500';
            case 'no_ingreso': return 'bg-red-500';
            case 'completado': return 'bg-gray-500';
            default: return 'bg-yellow-500';
        }
    };

    return (
        <>
            <Head title="Seguimiento de Pacientes" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Seguimiento de Pacientes</h1>
                    <Badge variant="outline">
                        {pacientes.data.length} pacientes aceptados
                    </Badge>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <Activity className="mr-2 h-5 w-5" />
                            Pacientes Aceptados
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Código</TableHead>
                                    <TableHead>Paciente</TableHead>
                                    <TableHead>Especialidad</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Fecha Aceptación</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pacientes.data.map((paciente) => (
                                    <TableRow key={paciente.id}>
                                        <TableCell className="font-medium">
                                            {paciente.codigo_solicitud}
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">
                                                    {paciente.registro_medico.nombre} {paciente.registro_medico.apellidos}
                                                </div>
                                                <div className="text-sm text-muted-foreground">
                                                    {paciente.registro_medico.numero_identificacion}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{paciente.registro_medico.especialidad_solicitada}</TableCell>
                                        <TableCell>
                                            <Badge className={`${getEstadoColor(paciente.seguimiento?.estado || 'programado')} text-white`}>
                                                {paciente.seguimiento?.estado || 'Programado'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {new Date(paciente.created_at).toLocaleDateString()}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Dialog>
                                                    <DialogTrigger asChild>
                                                        <Button size="sm" variant="outline">
                                                            <User className="h-4 w-4 mr-1" />
                                                            Actualizar
                                                        </Button>
                                                    </DialogTrigger>
                                                    <DialogContent>
                                                        <DialogHeader>
                                                            <DialogTitle>Actualizar Seguimiento</DialogTitle>
                                                        </DialogHeader>
                                                        <div className="space-y-4">
                                                            <div>
                                                                <Label>Estado del Seguimiento</Label>
                                                                <Select 
                                                                    value={data.estado_seguimiento} 
                                                                    onValueChange={(value) => setData('estado_seguimiento', value)}
                                                                >
                                                                    <SelectTrigger>
                                                                        <SelectValue placeholder="Seleccionar estado" />
                                                                    </SelectTrigger>
                                                                    <SelectContent>
                                                                        <SelectItem value="programado">Programado</SelectItem>
                                                                        <SelectItem value="ingresado">Ingresado</SelectItem>
                                                                        <SelectItem value="no_ingreso">No Ingresó</SelectItem>
                                                                        <SelectItem value="completado">Completado</SelectItem>
                                                                    </SelectContent>
                                                                </Select>
                                                            </div>
                                                            
                                                            {data.estado_seguimiento === 'ingresado' && (
                                                                <div>
                                                                    <Label>Fecha de Ingreso</Label>
                                                                    <Input 
                                                                        type="date"
                                                                        value={data.fecha_ingreso}
                                                                        onChange={(e) => setData('fecha_ingreso', e.target.value)}
                                                                    />
                                                                </div>
                                                            )}
                                                            
                                                            <div>
                                                                <Label>Observaciones</Label>
                                                                <Textarea 
                                                                    value={data.observaciones}
                                                                    onChange={(e) => setData('observaciones', e.target.value)}
                                                                    placeholder="Observaciones del seguimiento..."
                                                                    rows={3}
                                                                />
                                                            </div>
                                                            
                                                            {data.estado_seguimiento === 'completado' && (
                                                                <>
                                                                    <div>
                                                                        <Label>Diagnóstico Final</Label>
                                                                        <Textarea 
                                                                            value={data.diagnostico_final}
                                                                            onChange={(e) => setData('diagnostico_final', e.target.value)}
                                                                            placeholder="Diagnóstico final del paciente..."
                                                                            rows={2}
                                                                        />
                                                                    </div>
                                                                    
                                                                    <div>
                                                                        <Label>Tratamiento Realizado</Label>
                                                                        <Textarea 
                                                                            value={data.tratamiento_realizado}
                                                                            onChange={(e) => setData('tratamiento_realizado', e.target.value)}
                                                                            placeholder="Tratamiento realizado..."
                                                                            rows={2}
                                                                        />
                                                                    </div>
                                                                </>
                                                            )}
                                                            
                                                            <div className="flex gap-2 justify-end">
                                                                <Button 
                                                                    onClick={() => handleActualizar(paciente.id)}
                                                                    disabled={processing}
                                                                >
                                                                    {processing ? 'Actualizando...' : 'Actualizar'}
                                                                </Button>
                                                            </div>
                                                        </div>
                                                    </DialogContent>
                                                </Dialog>
                                                
                                                {paciente.seguimiento?.estado === 'completado' && (
                                                    <Dialog>
                                                        <DialogTrigger asChild>
                                                            <Button size="sm" variant="default">
                                                                <FileText className="h-4 w-4 mr-1" />
                                                                Contrarreferencia
                                                            </Button>
                                                        </DialogTrigger>
                                                        <DialogContent>
                                                            <DialogHeader>
                                                                <DialogTitle>Generar Contrarreferencia</DialogTitle>
                                                            </DialogHeader>
                                                            <div className="space-y-4">
                                                                <div>
                                                                    <Label>Diagnóstico</Label>
                                                                    <Textarea placeholder="Diagnóstico del paciente..." rows={3} />
                                                                </div>
                                                                <div>
                                                                    <Label>Tratamiento</Label>
                                                                    <Textarea placeholder="Tratamiento realizado..." rows={3} />
                                                                </div>
                                                                <div>
                                                                    <Label>Recomendaciones</Label>
                                                                    <Textarea placeholder="Recomendaciones para el paciente..." rows={3} />
                                                                </div>
                                                                <div className="flex gap-2 justify-end">
                                                                    <Button>Generar Contrarreferencia</Button>
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
                        
                        {pacientes.data.length === 0 && (
                            <div className="text-center py-8">
                                <Activity className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900">No hay pacientes aceptados</h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Los pacientes aceptados aparecerán aquí para seguimiento.
                                </p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}