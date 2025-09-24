import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Eye, MessageSquare, Clock, CheckCircle, XCircle, AlertCircle, Search } from 'lucide-react';

interface Solicitud {
    id: number;
    codigo_solicitud: string;
    paciente: {
        nombre: string;
        apellidos: string;
        numero_identificacion: string;
        edad: number;
    };
    especialidad_solicitada: string;
    estado: 'PENDIENTE' | 'EN_REVISION' | 'ACEPTADA' | 'RECHAZADA' | 'COMPLETADA';
    prioridad: 'ROJO' | 'AMARILLO' | 'VERDE';
    fecha_solicitud: string;
    fecha_respuesta?: string;
    tiempo_respuesta?: number;
    observaciones_medico?: string;
    motivo_rechazo?: string;
}

interface Props {
    solicitudes: {
        data: Solicitud[];
        links: any[];
        meta: any;
    };
    estadisticas: {
        total: number;
        pendientes: number;
        aceptadas: number;
        rechazadas: number;
        tiempo_promedio: number;
    };
}

export default function SeguimientoSolicitudes({ solicitudes, estadisticas }: Props) {
    const [filtros, setFiltros] = useState({
        busqueda: '',
        estado: '',
        prioridad: '',
        especialidad: ''
    });

    const getEstadoBadge = (estado: string) => {
        const colors = {
            PENDIENTE: 'bg-yellow-100 text-yellow-800',
            EN_REVISION: 'bg-blue-100 text-blue-800',
            ACEPTADA: 'bg-green-100 text-green-800',
            RECHAZADA: 'bg-red-100 text-red-800',
            COMPLETADA: 'bg-gray-100 text-gray-800'
        };
        return colors[estado as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    const getPrioridadBadge = (prioridad: string) => {
        const colors = {
            ROJO: 'bg-red-500 text-white',
            AMARILLO: 'bg-yellow-500 text-white',
            VERDE: 'bg-green-500 text-white'
        };
        return colors[prioridad as keyof typeof colors] || 'bg-gray-500 text-white';
    };

    const getEstadoIcon = (estado: string) => {
        switch (estado) {
            case 'PENDIENTE':
                return <Clock className="w-4 h-4" />;
            case 'EN_REVISION':
                return <AlertCircle className="w-4 h-4" />;
            case 'ACEPTADA':
                return <CheckCircle className="w-4 h-4" />;
            case 'RECHAZADA':
                return <XCircle className="w-4 h-4" />;
            case 'COMPLETADA':
                return <CheckCircle className="w-4 h-4" />;
            default:
                return <Clock className="w-4 h-4" />;
        }
    };

    return (
        <>
            <Head title="Seguimiento de Solicitudes" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-3xl font-bold">Seguimiento de Solicitudes</h1>
                </div>

                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Total</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.total}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Pendientes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-yellow-600">{estadisticas.pendientes}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Aceptadas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{estadisticas.aceptadas}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Rechazadas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{estadisticas.rechazadas}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Tiempo Promedio</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{estadisticas.tiempo_promedio}h</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros de Búsqueda</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                                <Input
                                    placeholder="Buscar por código o paciente..."
                                    value={filtros.busqueda}
                                    onChange={(e) => setFiltros({...filtros, busqueda: e.target.value})}
                                    className="pl-10"
                                />
                            </div>
                            
                            <Select value={filtros.estado} onValueChange={(value) => setFiltros({...filtros, estado: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="PENDIENTE">Pendiente</SelectItem>
                                    <SelectItem value="EN_REVISION">En Revisión</SelectItem>
                                    <SelectItem value="ACEPTADA">Aceptada</SelectItem>
                                    <SelectItem value="RECHAZADA">Rechazada</SelectItem>
                                    <SelectItem value="COMPLETADA">Completada</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={filtros.prioridad} onValueChange={(value) => setFiltros({...filtros, prioridad: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Prioridad" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas</SelectItem>
                                    <SelectItem value="ROJO">Rojo</SelectItem>
                                    <SelectItem value="AMARILLO">Amarillo</SelectItem>
                                    <SelectItem value="VERDE">Verde</SelectItem>
                                </SelectContent>
                            </Select>

                            <Input
                                placeholder="Especialidad..."
                                value={filtros.especialidad}
                                onChange={(e) => setFiltros({...filtros, especialidad: e.target.value})}
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* Lista de Solicitudes */}
                <div className="grid gap-4">
                    {solicitudes.data.map((solicitud) => (
                        <Card key={solicitud.id} className="hover:shadow-md transition-shadow">
                            <CardContent className="p-6">
                                <div className="flex justify-between items-start mb-4">
                                    <div className="flex items-center gap-3">
                                        {getEstadoIcon(solicitud.estado)}
                                        <div>
                                            <h3 className="font-semibold text-lg">{solicitud.codigo_solicitud}</h3>
                                            <p className="text-sm text-gray-600">
                                                {solicitud.paciente.nombre} {solicitud.paciente.apellidos}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex gap-2">
                                        <Badge className={getPrioridadBadge(solicitud.prioridad)}>
                                            {solicitud.prioridad}
                                        </Badge>
                                        <Badge className={getEstadoBadge(solicitud.estado)}>
                                            {solicitud.estado}
                                        </Badge>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <p className="text-sm font-medium text-gray-700">Paciente</p>
                                        <p className="text-sm">{solicitud.paciente.numero_identificacion}</p>
                                        <p className="text-sm">{solicitud.paciente.edad} años</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-700">Especialidad</p>
                                        <p className="text-sm">{solicitud.especialidad_solicitada}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-700">Fechas</p>
                                        <p className="text-sm">Solicitud: {new Date(solicitud.fecha_solicitud).toLocaleDateString()}</p>
                                        {solicitud.fecha_respuesta && (
                                            <p className="text-sm">Respuesta: {new Date(solicitud.fecha_respuesta).toLocaleDateString()}</p>
                                        )}
                                    </div>
                                </div>

                                {solicitud.observaciones_medico && (
                                    <div className="mb-4 p-3 bg-blue-50 rounded-lg">
                                        <p className="text-sm font-medium text-blue-800">Observaciones del Médico:</p>
                                        <p className="text-sm text-blue-700">{solicitud.observaciones_medico}</p>
                                    </div>
                                )}

                                {solicitud.motivo_rechazo && (
                                    <div className="mb-4 p-3 bg-red-50 rounded-lg">
                                        <p className="text-sm font-medium text-red-800">Motivo de Rechazo:</p>
                                        <p className="text-sm text-red-700">{solicitud.motivo_rechazo}</p>
                                    </div>
                                )}

                                <div className="flex justify-between items-center">
                                    <div className="text-sm text-gray-500">
                                        {solicitud.tiempo_respuesta && (
                                            <span>Tiempo de respuesta: {solicitud.tiempo_respuesta}h</span>
                                        )}
                                    </div>
                                    <div className="flex gap-2">
                                        <Button size="sm" variant="outline">
                                            <Eye className="w-4 h-4 mr-2" />
                                            Ver Detalle
                                        </Button>
                                        {solicitud.estado === 'ACEPTADA' && (
                                            <Button size="sm" variant="outline">
                                                <MessageSquare className="w-4 h-4 mr-2" />
                                                Contactar
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}