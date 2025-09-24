import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FileText, Download, Eye, Calendar, User, Activity, Search, Filter } from 'lucide-react';

interface HistorialPaciente {
    id: number;
    paciente: {
        nombre: string;
        apellidos: string;
        numero_identificacion: string;
        edad: number;
        genero: string;
        telefono?: string;
        email?: string;
    };
    consultas: {
        id: number;
        fecha: string;
        tipo: 'CONSULTA' | 'REFERENCIA' | 'SEGUIMIENTO' | 'URGENCIA';
        especialidad: string;
        diagnostico: string;
        tratamiento: string;
        estado: 'ACTIVO' | 'COMPLETADO' | 'CANCELADO';
        medico: string;
        observaciones?: string;
    }[];
    referencias: {
        id: number;
        fecha: string;
        especialidad_origen: string;
        especialidad_destino: string;
        estado: 'PENDIENTE' | 'ACEPTADA' | 'RECHAZADA' | 'COMPLETADA';
        prioridad: 'ROJO' | 'AMARILLO' | 'VERDE';
        motivo: string;
    }[];
    ultima_consulta: string;
    total_consultas: number;
    total_referencias: number;
}

interface Props {
    historiales: {
        data: HistorialPaciente[];
        links: any[];
        meta: any;
    };
    estadisticas: {
        total_pacientes: number;
        consultas_mes: number;
        referencias_mes: number;
        pacientes_activos: number;
    };
}

export default function HistorialPacientes({ historiales, estadisticas }: Props) {
    const [filtros, setFiltros] = useState({
        busqueda: '',
        especialidad: '',
        periodo: '',
        estado: ''
    });

    const [pacienteSeleccionado, setPacienteSeleccionado] = useState<HistorialPaciente | null>(null);

    const getTipoBadge = (tipo: string) => {
        const colors = {
            CONSULTA: 'bg-blue-100 text-blue-800',
            REFERENCIA: 'bg-purple-100 text-purple-800',
            SEGUIMIENTO: 'bg-green-100 text-green-800',
            URGENCIA: 'bg-red-100 text-red-800'
        };
        return colors[tipo as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    const getEstadoBadge = (estado: string) => {
        const colors = {
            ACTIVO: 'bg-green-100 text-green-800',
            COMPLETADO: 'bg-gray-100 text-gray-800',
            CANCELADO: 'bg-red-100 text-red-800',
            PENDIENTE: 'bg-yellow-100 text-yellow-800',
            ACEPTADA: 'bg-green-100 text-green-800',
            RECHAZADA: 'bg-red-100 text-red-800'
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

    return (
        <>
            <Head title="Historial de Pacientes" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-3xl font-bold">Historial de Pacientes</h1>
                    <Button className="bg-blue-600 hover:bg-blue-700">
                        <FileText className="w-4 h-4 mr-2" />
                        Exportar Historiales
                    </Button>
                </div>

                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Pacientes</CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.total_pacientes}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Consultas Este Mes</CardTitle>
                            <Calendar className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{estadisticas.consultas_mes}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Referencias Este Mes</CardTitle>
                            <Activity className="h-4 w-4 text-purple-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-purple-600">{estadisticas.referencias_mes}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pacientes Activos</CardTitle>
                            <Activity className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{estadisticas.pacientes_activos}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="w-5 h-5" />
                            Filtros de Búsqueda
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                                <Input
                                    placeholder="Buscar paciente..."
                                    value={filtros.busqueda}
                                    onChange={(e) => setFiltros({...filtros, busqueda: e.target.value})}
                                    className="pl-10"
                                />
                            </div>
                            
                            <Select value={filtros.especialidad} onValueChange={(value) => setFiltros({...filtros, especialidad: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Especialidad" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas</SelectItem>
                                    <SelectItem value="cardiologia">Cardiología</SelectItem>
                                    <SelectItem value="neurologia">Neurología</SelectItem>
                                    <SelectItem value="pediatria">Pediatría</SelectItem>
                                    <SelectItem value="ginecologia">Ginecología</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={filtros.periodo} onValueChange={(value) => setFiltros({...filtros, periodo: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Período" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="ultima_semana">Última semana</SelectItem>
                                    <SelectItem value="ultimo_mes">Último mes</SelectItem>
                                    <SelectItem value="ultimos_3_meses">Últimos 3 meses</SelectItem>
                                    <SelectItem value="ultimo_ano">Último año</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={filtros.estado} onValueChange={(value) => setFiltros({...filtros, estado: value})}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="activo">Activo</SelectItem>
                                    <SelectItem value="completado">Completado</SelectItem>
                                    <SelectItem value="seguimiento">En seguimiento</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Lista de Pacientes */}
                <div className="grid gap-4">
                    {historiales.data.map((historial) => (
                        <Card key={historial.id} className="hover:shadow-md transition-shadow">
                            <CardContent className="p-6">
                                <div className="flex justify-between items-start mb-4">
                                    <div className="flex items-center gap-3">
                                        <User className="w-8 h-8 text-blue-600" />
                                        <div>
                                            <h3 className="font-semibold text-lg">
                                                {historial.paciente.nombre} {historial.paciente.apellidos}
                                            </h3>
                                            <p className="text-sm text-gray-600">
                                                {historial.paciente.numero_identificacion} • {historial.paciente.edad} años • {historial.paciente.genero}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-sm text-gray-500">Última consulta</p>
                                        <p className="text-sm font-medium">{new Date(historial.ultima_consulta).toLocaleDateString()}</p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div className="text-center p-3 bg-blue-50 rounded-lg">
                                        <p className="text-2xl font-bold text-blue-600">{historial.total_consultas}</p>
                                        <p className="text-sm text-blue-700">Consultas</p>
                                    </div>
                                    <div className="text-center p-3 bg-purple-50 rounded-lg">
                                        <p className="text-2xl font-bold text-purple-600">{historial.total_referencias}</p>
                                        <p className="text-sm text-purple-700">Referencias</p>
                                    </div>
                                    <div className="text-center p-3 bg-green-50 rounded-lg">
                                        <p className="text-2xl font-bold text-green-600">
                                            {historial.consultas.filter(c => c.estado === 'ACTIVO').length}
                                        </p>
                                        <p className="text-sm text-green-700">Activos</p>
                                    </div>
                                </div>

                                {/* Últimas consultas */}
                                {historial.consultas.slice(0, 3).length > 0 && (
                                    <div className="mb-4">
                                        <h4 className="font-medium mb-2">Últimas Consultas</h4>
                                        <div className="space-y-2">
                                            {historial.consultas.slice(0, 3).map((consulta) => (
                                                <div key={consulta.id} className="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                    <div className="flex items-center gap-2">
                                                        <Badge className={getTipoBadge(consulta.tipo)}>
                                                            {consulta.tipo}
                                                        </Badge>
                                                        <span className="text-sm">{consulta.especialidad}</span>
                                                        <span className="text-sm text-gray-500">
                                                            {new Date(consulta.fecha).toLocaleDateString()}
                                                        </span>
                                                    </div>
                                                    <Badge className={getEstadoBadge(consulta.estado)}>
                                                        {consulta.estado}
                                                    </Badge>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                <div className="flex justify-between items-center">
                                    <div className="text-sm text-gray-500">
                                        {historial.paciente.telefono && (
                                            <span>Tel: {historial.paciente.telefono}</span>
                                        )}
                                        {historial.paciente.email && (
                                            <span className="ml-4">Email: {historial.paciente.email}</span>
                                        )}
                                    </div>
                                    <div className="flex gap-2">
                                        <Button 
                                            size="sm" 
                                            variant="outline"
                                            onClick={() => setPacienteSeleccionado(historial)}
                                        >
                                            <Eye className="w-4 h-4 mr-2" />
                                            Ver Historial
                                        </Button>
                                        <Button size="sm" variant="outline">
                                            <Download className="w-4 h-4 mr-2" />
                                            Descargar
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Modal de Detalle del Paciente */}
                {pacienteSeleccionado && (
                    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div className="bg-white rounded-lg p-6 max-w-4xl max-h-[90vh] overflow-y-auto">
                            <div className="flex justify-between items-center mb-4">
                                <h2 className="text-2xl font-bold">
                                    Historial de {pacienteSeleccionado.paciente.nombre} {pacienteSeleccionado.paciente.apellidos}
                                </h2>
                                <Button 
                                    variant="outline" 
                                    onClick={() => setPacienteSeleccionado(null)}
                                >
                                    Cerrar
                                </Button>
                            </div>
                            
                            {/* Contenido del modal con historial completo */}
                            <div className="space-y-6">
                                {/* Información del paciente */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Información del Paciente</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <p><strong>Identificación:</strong> {pacienteSeleccionado.paciente.numero_identificacion}</p>
                                                <p><strong>Edad:</strong> {pacienteSeleccionado.paciente.edad} años</p>
                                                <p><strong>Género:</strong> {pacienteSeleccionado.paciente.genero}</p>
                                            </div>
                                            <div>
                                                <p><strong>Teléfono:</strong> {pacienteSeleccionado.paciente.telefono || 'No registrado'}</p>
                                                <p><strong>Email:</strong> {pacienteSeleccionado.paciente.email || 'No registrado'}</p>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Historial de consultas */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Historial de Consultas</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-3">
                                            {pacienteSeleccionado.consultas.map((consulta) => (
                                                <div key={consulta.id} className="border rounded-lg p-4">
                                                    <div className="flex justify-between items-start mb-2">
                                                        <div className="flex items-center gap-2">
                                                            <Badge className={getTipoBadge(consulta.tipo)}>
                                                                {consulta.tipo}
                                                            </Badge>
                                                            <span className="font-medium">{consulta.especialidad}</span>
                                                        </div>
                                                        <div className="text-right">
                                                            <Badge className={getEstadoBadge(consulta.estado)}>
                                                                {consulta.estado}
                                                            </Badge>
                                                            <p className="text-sm text-gray-500 mt-1">
                                                                {new Date(consulta.fecha).toLocaleDateString()}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p><strong>Diagnóstico:</strong> {consulta.diagnostico}</p>
                                                    <p><strong>Tratamiento:</strong> {consulta.tratamiento}</p>
                                                    <p><strong>Médico:</strong> {consulta.medico}</p>
                                                    {consulta.observaciones && (
                                                        <p><strong>Observaciones:</strong> {consulta.observaciones}</p>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Historial de referencias */}
                                {pacienteSeleccionado.referencias.length > 0 && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Historial de Referencias</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="space-y-3">
                                                {pacienteSeleccionado.referencias.map((referencia) => (
                                                    <div key={referencia.id} className="border rounded-lg p-4">
                                                        <div className="flex justify-between items-start mb-2">
                                                            <div className="flex items-center gap-2">
                                                                <Badge className={getPrioridadBadge(referencia.prioridad)}>
                                                                    {referencia.prioridad}
                                                                </Badge>
                                                                <span className="font-medium">
                                                                    {referencia.especialidad_origen} → {referencia.especialidad_destino}
                                                                </span>
                                                            </div>
                                                            <div className="text-right">
                                                                <Badge className={getEstadoBadge(referencia.estado)}>
                                                                    {referencia.estado}
                                                                </Badge>
                                                                <p className="text-sm text-gray-500 mt-1">
                                                                    {new Date(referencia.fecha).toLocaleDateString()}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <p><strong>Motivo:</strong> {referencia.motivo}</p>
                                                    </div>
                                                ))}
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}