import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Shield, AlertTriangle, Eye, Download, Search, Filter, Lock, User, Activity } from 'lucide-react';

interface EventoAuditoria {
    id: number;
    usuario: {
        id: number;
        name: string;
        email: string;
        role: string;
    };
    accion: string;
    recurso: string;
    ip_address: string;
    user_agent: string;
    fecha: string;
    nivel_riesgo: 'BAJO' | 'MEDIO' | 'ALTO' | 'CRITICO';
    detalles: {
        metodo: string;
        url: string;
        parametros?: any;
        respuesta_codigo: number;
        tiempo_respuesta: number;
    };
    ubicacion?: {
        pais: string;
        ciudad: string;
        coordenadas?: string;
    };
}

interface Props {
    eventos: {
        data: EventoAuditoria[];
        links: any[];
        meta: any;
    };
    estadisticas: {
        total_eventos: number;
        eventos_hoy: number;
        intentos_fallidos: number;
        accesos_sospechosos: number;
        usuarios_activos: number;
    };
    alertas_seguridad: {
        id: number;
        tipo: string;
        mensaje: string;
        nivel: 'BAJO' | 'MEDIO' | 'ALTO' | 'CRITICO';
        fecha: string;
        resuelto: boolean;
    }[];
}

export default function AuditoriaSeguridad({ eventos, estadisticas, alertas_seguridad }: Props) {
    const [filtros, setFiltros] = useState({
        usuario: '',
        accion: '',
        nivel_riesgo: '',
        fecha_inicio: '',
        fecha_fin: '',
        ip: ''
    });

    const getNivelRiesgoBadge = (nivel: string) => {
        const colors = {
            BAJO: 'bg-green-100 text-green-800',
            MEDIO: 'bg-yellow-100 text-yellow-800',
            ALTO: 'bg-orange-100 text-orange-800',
            CRITICO: 'bg-red-100 text-red-800'
        };
        return colors[nivel as keyof typeof colors] || 'bg-gray-100 text-gray-800';
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

    const getAccionIcon = (accion: string) => {
        if (accion.includes('login') || accion.includes('auth')) return <Lock className="w-4 h-4" />;
        if (accion.includes('create') || accion.includes('store')) return <User className="w-4 h-4" />;
        if (accion.includes('view') || accion.includes('show')) return <Eye className="w-4 h-4" />;
        return <Activity className="w-4 h-4" />;
    };

    return (
        <>
            <Head title="Auditoría y Seguridad" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-3xl font-bold">Auditoría y Seguridad</h1>
                    <div className="flex gap-2">
                        <Button variant="outline">
                            <Download className="w-4 h-4 mr-2" />
                            Exportar Log
                        </Button>
                        <Button className="bg-red-600 hover:bg-red-700">
                            <Shield className="w-4 h-4 mr-2" />
                            Generar Reporte
                        </Button>
                    </div>
                </div>

                {/* Estadísticas de Seguridad */}
                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Total Eventos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.total_eventos}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Eventos Hoy</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{estadisticas.eventos_hoy}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Intentos Fallidos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{estadisticas.intentos_fallidos}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Accesos Sospechosos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">{estadisticas.accesos_sospechosos}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium">Usuarios Activos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{estadisticas.usuarios_activos}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Alertas de Seguridad */}
                {alertas_seguridad.length > 0 && (
                    <Card className="border-red-200 bg-red-50">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-red-800">
                                <AlertTriangle className="w-5 h-5" />
                                Alertas de Seguridad Activas
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {alertas_seguridad.filter(a => !a.resuelto).slice(0, 3).map((alerta) => (
                                    <div key={alerta.id} className="flex justify-between items-center p-3 bg-white rounded-lg">
                                        <div className="flex items-center gap-3">
                                            <Badge className={getNivelRiesgoBadge(alerta.nivel)}>
                                                {alerta.nivel}
                                            </Badge>
                                            <span className="font-medium">{alerta.tipo}</span>
                                            <span className="text-sm text-gray-600">{alerta.mensaje}</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm text-gray-500">
                                                {new Date(alerta.fecha).toLocaleString()}
                                            </span>
                                            <Button size="sm" variant="outline">
                                                Resolver
                                            </Button>
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
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="w-5 h-5" />
                            Filtros de Auditoría
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                                <Input
                                    placeholder="Buscar usuario..."
                                    value={filtros.usuario}
                                    onChange={(e) => setFiltros({...filtros, usuario: e.target.value})}
                                    className="pl-10"
                                />
                            </div>
                            
                            <Select value={filtros.accion} onValueChange={(value) => setFiltros({...filtros, accion: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Tipo de acción" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas</SelectItem>
                                    <SelectItem value="login">Inicio de sesión</SelectItem>
                                    <SelectItem value="logout">Cierre de sesión</SelectItem>
                                    <SelectItem value="create">Crear</SelectItem>
                                    <SelectItem value="update">Actualizar</SelectItem>
                                    <SelectItem value="delete">Eliminar</SelectItem>
                                    <SelectItem value="view">Ver</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={filtros.nivel_riesgo} onValueChange={(value) => setFiltros({...filtros, nivel_riesgo: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Nivel de riesgo" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="BAJO">Bajo</SelectItem>
                                    <SelectItem value="MEDIO">Medio</SelectItem>
                                    <SelectItem value="ALTO">Alto</SelectItem>
                                    <SelectItem value="CRITICO">Crítico</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Input
                                type="date"
                                placeholder="Fecha inicio"
                                value={filtros.fecha_inicio}
                                onChange={(e) => setFiltros({...filtros, fecha_inicio: e.target.value})}
                            />
                            
                            <Input
                                type="date"
                                placeholder="Fecha fin"
                                value={filtros.fecha_fin}
                                onChange={(e) => setFiltros({...filtros, fecha_fin: e.target.value})}
                            />
                            
                            <Input
                                placeholder="Dirección IP..."
                                value={filtros.ip}
                                onChange={(e) => setFiltros({...filtros, ip: e.target.value})}
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* Log de Eventos */}
                <Card>
                    <CardHeader>
                        <CardTitle>Log de Eventos de Auditoría</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {eventos.data.map((evento) => (
                                <div key={evento.id} className="border rounded-lg p-4 hover:bg-gray-50">
                                    <div className="flex justify-between items-start mb-3">
                                        <div className="flex items-center gap-3">
                                            {getAccionIcon(evento.accion)}
                                            <div>
                                                <h4 className="font-medium">{evento.accion}</h4>
                                                <p className="text-sm text-gray-600">
                                                    {evento.usuario.name} ({evento.usuario.email})
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Badge className={getRoleBadge(evento.usuario.role)}>
                                                {evento.usuario.role}
                                            </Badge>
                                            <Badge className={getNivelRiesgoBadge(evento.nivel_riesgo)}>
                                                {evento.nivel_riesgo}
                                            </Badge>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3 text-sm">
                                        <div>
                                            <p className="font-medium text-gray-700">Recurso</p>
                                            <p className="text-gray-600">{evento.recurso}</p>
                                        </div>
                                        <div>
                                            <p className="font-medium text-gray-700">IP Address</p>
                                            <p className="text-gray-600">{evento.ip_address}</p>
                                        </div>
                                        <div>
                                            <p className="font-medium text-gray-700">Método</p>
                                            <p className="text-gray-600">{evento.detalles.metodo}</p>
                                        </div>
                                        <div>
                                            <p className="font-medium text-gray-700">Código Respuesta</p>
                                            <p className={`font-medium ${
                                                evento.detalles.respuesta_codigo >= 200 && evento.detalles.respuesta_codigo < 300 
                                                    ? 'text-green-600' 
                                                    : evento.detalles.respuesta_codigo >= 400 
                                                        ? 'text-red-600' 
                                                        : 'text-yellow-600'
                                            }`}>
                                                {evento.detalles.respuesta_codigo}
                                            </p>
                                        </div>
                                    </div>

                                    {evento.ubicacion && (
                                        <div className="mb-3 p-2 bg-blue-50 rounded text-sm">
                                            <p><strong>Ubicación:</strong> {evento.ubicacion.ciudad}, {evento.ubicacion.pais}</p>
                                        </div>
                                    )}

                                    <div className="flex justify-between items-center text-sm">
                                        <div className="text-gray-500">
                                            <span>Tiempo de respuesta: {evento.detalles.tiempo_respuesta}ms</span>
                                            <span className="ml-4">
                                                {new Date(evento.fecha).toLocaleString()}
                                            </span>
                                        </div>
                                        <div className="flex gap-2">
                                            <Button size="sm" variant="outline">
                                                <Eye className="w-4 h-4 mr-1" />
                                                Detalles
                                            </Button>
                                            {evento.nivel_riesgo === 'ALTO' || evento.nivel_riesgo === 'CRITICO' && (
                                                <Button size="sm" variant="outline" className="text-red-600 border-red-200">
                                                    <AlertTriangle className="w-4 h-4 mr-1" />
                                                    Investigar
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}