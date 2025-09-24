import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { useAutoRefresh } from '@/hooks/useAutoRefresh';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    Users, 
    Bed, 
    Activity, 
    Clock, 
    AlertTriangle, 
    CheckCircle, 
    TrendingUp, 
    TrendingDown,
    Calendar,
    MapPin,
    Phone,
    Mail
} from 'lucide-react';

interface Recurso {
    id: number;
    tipo: 'MEDICO' | 'ENFERMERO' | 'CAMA' | 'EQUIPO' | 'SALA';
    nombre: string;
    especialidad?: string;
    ubicacion: string;
    estado: 'DISPONIBLE' | 'OCUPADO' | 'MANTENIMIENTO' | 'FUERA_SERVICIO';
    capacidad_maxima?: number;
    capacidad_actual?: number;
    turno?: 'MAÑANA' | 'TARDE' | 'NOCHE' | '24H';
    contacto?: {
        telefono?: string;
        email?: string;
        extension?: string;
    };
    ultima_actualizacion: string;
    metricas?: {
        utilizacion_promedio: number;
        tiempo_promedio_ocupacion: number;
        casos_atendidos_hoy: number;
    };
}

interface Props {
    recursos: {
        data: Recurso[];
        links: any[];
        meta: any;
    };
    estadisticas: {
        total_recursos: number;
        disponibles: number;
        ocupados: number;
        mantenimiento: number;
        utilizacion_promedio: number;
        alertas_criticas: number;
    };
    alertas: {
        id: number;
        tipo: 'CAPACIDAD' | 'MANTENIMIENTO' | 'PERSONAL' | 'EQUIPO';
        mensaje: string;
        prioridad: 'ALTA' | 'MEDIA' | 'BAJA';
        fecha: string;
        recurso_id: number;
    }[];
}

export default function GestionRecursos({ recursos, estadisticas, alertas }: Props) {
    const [filtros, setFiltros] = useState({
        tipo: '',
        estado: '',
        ubicacion: '',
        turno: ''
    });

    const [vistaActual, setVistaActual] = useState<'lista' | 'mapa' | 'calendario'>('lista');
    const [autoRefreshEnabled, setAutoRefreshEnabled] = useState(true);

    // Auto-refresh cada segundo para recursos
    const { stopRefresh, startRefresh } = useAutoRefresh({
        interval: 1000,
        enabled: autoRefreshEnabled,
        only: ['recursos', 'estadisticas', 'alertas']
    });

    const getEstadoBadge = (estado: string) => {
        const colors = {
            DISPONIBLE: 'bg-green-100 text-green-800',
            OCUPADO: 'bg-yellow-100 text-yellow-800',
            MANTENIMIENTO: 'bg-blue-100 text-blue-800',
            FUERA_SERVICIO: 'bg-red-100 text-red-800'
        };
        return colors[estado as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    const getTipoBadge = (tipo: string) => {
        const colors = {
            MEDICO: 'bg-blue-100 text-blue-800',
            ENFERMERO: 'bg-green-100 text-green-800',
            CAMA: 'bg-purple-100 text-purple-800',
            EQUIPO: 'bg-orange-100 text-orange-800',
            SALA: 'bg-gray-100 text-gray-800'
        };
        return colors[tipo as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    const getPrioridadBadge = (prioridad: string) => {
        const colors = {
            ALTA: 'bg-red-500 text-white',
            MEDIA: 'bg-yellow-500 text-white',
            BAJA: 'bg-green-500 text-white'
        };
        return colors[prioridad as keyof typeof colors] || 'bg-gray-500 text-white';
    };

    const getIconoTipo = (tipo: string) => {
        switch (tipo) {
            case 'MEDICO':
            case 'ENFERMERO':
                return <Users className="w-5 h-5" />;
            case 'CAMA':
                return <Bed className="w-5 h-5" />;
            case 'EQUIPO':
                return <Activity className="w-5 h-5" />;
            case 'SALA':
                return <MapPin className="w-5 h-5" />;
            default:
                return <Activity className="w-5 h-5" />;
        }
    };

    const calcularPorcentajeUtilizacion = (recurso: Recurso) => {
        if (recurso.capacidad_maxima && recurso.capacidad_actual !== undefined) {
            return Math.round((recurso.capacidad_actual / recurso.capacidad_maxima) * 100);
        }
        return 0;
    };

    const handleAsignarRecurso = (recursoId: number) => {
        router.post(route('jefe-urgencias.recursos.asignar', recursoId));
    };

    const handleLiberarRecurso = (recursoId: number) => {
        router.post(route('jefe-urgencias.recursos.liberar', recursoId));
    };

    const handleFiltrar = () => {
        router.get(route('jefe-urgencias.recursos'), filtros, {
            preserveState: true,
            preserveScroll: true
        });
    };

    return (
        <>
            <Head title="Gestión de Recursos" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold">Gestión de Recursos</h1>
                        <div className="flex items-center gap-2 mt-2">
                            <div className={`w-2 h-2 rounded-full ${autoRefreshEnabled ? 'bg-blue-500 animate-pulse' : 'bg-gray-400'}`}></div>
                            <span className="text-sm text-gray-600">
                                {autoRefreshEnabled ? 'Monitoreando recursos' : 'Monitoreo pausado'}
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
                    <div className="flex gap-2">
                        <Button 
                            variant={vistaActual === 'lista' ? 'default' : 'outline'}
                            onClick={() => setVistaActual('lista')}
                        >
                            Lista
                        </Button>
                        <Button 
                            variant={vistaActual === 'mapa' ? 'default' : 'outline'}
                            onClick={() => setVistaActual('mapa')}
                        >
                            Mapa
                        </Button>
                        <Button 
                            variant={vistaActual === 'calendario' ? 'default' : 'outline'}
                            onClick={() => setVistaActual('calendario')}
                        >
                            <Calendar className="w-4 h-4 mr-2" />
                            Calendario
                        </Button>
                    </div>
                </div>

                {/* Estadísticas Generales */}
                <div className="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Total Recursos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.total_recursos}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Disponibles</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{estadisticas.disponibles}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Ocupados</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-yellow-600">{estadisticas.ocupados}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Mantenimiento</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{estadisticas.mantenimiento}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Utilización</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-purple-600">{estadisticas.utilizacion_promedio}%</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Alertas Críticas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{estadisticas.alertas_criticas}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Alertas Críticas */}
                {alertas.length > 0 && (
                    <Card className="border-red-200 bg-red-50">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-red-800">
                                <AlertTriangle className="w-5 h-5" />
                                Alertas Críticas
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {alertas.slice(0, 3).map((alerta) => (
                                    <div key={alerta.id} className="flex justify-between items-center p-3 bg-white rounded-lg">
                                        <div className="flex items-center gap-3">
                                            <Badge className={getPrioridadBadge(alerta.prioridad)}>
                                                {alerta.prioridad}
                                            </Badge>
                                            <span className="font-medium">{alerta.tipo}</span>
                                            <span className="text-sm text-gray-600">{alerta.mensaje}</span>
                                        </div>
                                        <div className="text-sm text-gray-500">
                                            {new Date(alerta.fecha).toLocaleTimeString()}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <Select value={filtros.tipo} onValueChange={(value) => setFiltros({...filtros, tipo: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Tipo de recurso" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="MEDICO">Médico</SelectItem>
                                    <SelectItem value="ENFERMERO">Enfermero</SelectItem>
                                    <SelectItem value="CAMA">Cama</SelectItem>
                                    <SelectItem value="EQUIPO">Equipo</SelectItem>
                                    <SelectItem value="SALA">Sala</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={filtros.estado} onValueChange={(value) => setFiltros({...filtros, estado: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="DISPONIBLE">Disponible</SelectItem>
                                    <SelectItem value="OCUPADO">Ocupado</SelectItem>
                                    <SelectItem value="MANTENIMIENTO">Mantenimiento</SelectItem>
                                    <SelectItem value="FUERA_SERVICIO">Fuera de Servicio</SelectItem>
                                </SelectContent>
                            </Select>

                            <Input
                                placeholder="Ubicación..."
                                value={filtros.ubicacion}
                                onChange={(e) => setFiltros({...filtros, ubicacion: e.target.value})}
                            />

                            <Select value={filtros.turno} onValueChange={(value) => setFiltros({...filtros, turno: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Turno" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="MAÑANA">Mañana</SelectItem>
                                    <SelectItem value="TARDE">Tarde</SelectItem>
                                    <SelectItem value="NOCHE">Noche</SelectItem>
                                    <SelectItem value="24H">24 Horas</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Lista de Recursos */}
                {vistaActual === 'lista' && (
                    <div className="grid gap-4">
                        {recursos.data.map((recurso) => (
                            <Card key={recurso.id} className="hover:shadow-md transition-shadow">
                                <CardContent className="p-6">
                                    <div className="flex justify-between items-start mb-4">
                                        <div className="flex items-center gap-3">
                                            {getIconoTipo(recurso.tipo)}
                                            <div>
                                                <h3 className="font-semibold text-lg">{recurso.nombre}</h3>
                                                <p className="text-sm text-gray-600">
                                                    {recurso.especialidad && `${recurso.especialidad} • `}
                                                    {recurso.ubicacion}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex gap-2">
                                            <Badge className={getTipoBadge(recurso.tipo)}>
                                                {recurso.tipo}
                                            </Badge>
                                            <Badge className={getEstadoBadge(recurso.estado)}>
                                                {recurso.estado}
                                            </Badge>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                        {/* Capacidad */}
                                        {recurso.capacidad_maxima && (
                                            <div className="text-center p-3 bg-blue-50 rounded-lg">
                                                <p className="text-2xl font-bold text-blue-600">
                                                    {calcularPorcentajeUtilizacion(recurso)}%
                                                </p>
                                                <p className="text-sm text-blue-700">
                                                    Utilización ({recurso.capacidad_actual}/{recurso.capacidad_maxima})
                                                </p>
                                            </div>
                                        )}

                                        {/* Turno */}
                                        {recurso.turno && (
                                            <div className="text-center p-3 bg-green-50 rounded-lg">
                                                <p className="text-lg font-bold text-green-600">{recurso.turno}</p>
                                                <p className="text-sm text-green-700">Turno</p>
                                            </div>
                                        )}

                                        {/* Métricas */}
                                        {recurso.metricas && (
                                            <>
                                                <div className="text-center p-3 bg-purple-50 rounded-lg">
                                                    <p className="text-lg font-bold text-purple-600">
                                                        {recurso.metricas.casos_atendidos_hoy}
                                                    </p>
                                                    <p className="text-sm text-purple-700">Casos Hoy</p>
                                                </div>
                                                <div className="text-center p-3 bg-orange-50 rounded-lg">
                                                    <p className="text-lg font-bold text-orange-600">
                                                        {recurso.metricas.tiempo_promedio_ocupacion}h
                                                    </p>
                                                    <p className="text-sm text-orange-700">Tiempo Promedio</p>
                                                </div>
                                            </>
                                        )}
                                    </div>

                                    {/* Información de contacto */}
                                    {recurso.contacto && (
                                        <div className="mb-4 p-3 bg-gray-50 rounded-lg">
                                            <h4 className="font-medium mb-2">Información de Contacto</h4>
                                            <div className="flex gap-4 text-sm">
                                                {recurso.contacto.telefono && (
                                                    <div className="flex items-center gap-1">
                                                        <Phone className="w-4 h-4" />
                                                        {recurso.contacto.telefono}
                                                    </div>
                                                )}
                                                {recurso.contacto.email && (
                                                    <div className="flex items-center gap-1">
                                                        <Mail className="w-4 h-4" />
                                                        {recurso.contacto.email}
                                                    </div>
                                                )}
                                                {recurso.contacto.extension && (
                                                    <div className="flex items-center gap-1">
                                                        <Phone className="w-4 h-4" />
                                                        Ext: {recurso.contacto.extension}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )}

                                    <div className="flex justify-between items-center">
                                        <div className="text-sm text-gray-500">
                                            Actualizado: {new Date(recurso.ultima_actualizacion).toLocaleString()}
                                        </div>
                                        <div className="flex gap-2">
                                            <Button 
                                                size="sm" 
                                                variant="outline"
                                                onClick={() => router.get(route('jefe-urgencias.recursos.show', recurso.id))}
                                            >
                                                Ver Detalle
                                            </Button>
                                            <Button size="sm" variant="outline">
                                                Programar
                                            </Button>
                                            {recurso.estado === 'DISPONIBLE' ? (
                                                <Button 
                                                    size="sm" 
                                                    className="bg-green-600 hover:bg-green-700"
                                                    onClick={() => handleAsignarRecurso(recurso.id)}
                                                >
                                                    Asignar
                                                </Button>
                                            ) : recurso.estado === 'OCUPADO' && (
                                                <Button 
                                                    size="sm" 
                                                    className="bg-orange-600 hover:bg-orange-700"
                                                    onClick={() => handleLiberarRecurso(recurso.id)}
                                                >
                                                    Liberar
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                {/* Vista de Mapa */}
                {vistaActual === 'mapa' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Mapa de Recursos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="h-96 bg-gray-100 rounded-lg flex items-center justify-center">
                                <div className="text-center">
                                    <MapPin className="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                    <p className="text-gray-600">Vista de mapa en desarrollo</p>
                                    <p className="text-sm text-gray-500">Aquí se mostrará la distribución espacial de los recursos</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Vista de Calendario */}
                {vistaActual === 'calendario' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Calendario de Recursos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="h-96 bg-gray-100 rounded-lg flex items-center justify-center">
                                <div className="text-center">
                                    <Calendar className="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                    <p className="text-gray-600">Vista de calendario en desarrollo</p>
                                    <p className="text-sm text-gray-500">Aquí se mostrará la programación de recursos por tiempo</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}