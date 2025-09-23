import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Bell, Search, Filter, Check, Settings } from 'lucide-react';
import { useState } from 'react';

interface Notificacion {
    id: number;
    tipo: string;
    titulo: string;
    mensaje: string;
    prioridad: 'baja' | 'media' | 'alta';
    leida: boolean;
    created_at: string;
}

interface Props {
    notificaciones: {
        data: Notificacion[];
        links: any[];
        meta: any;
    };
}

export default function NotificacionesCompletas({ notificaciones }: Props) {
    const [filtro, setFiltro] = useState('todas');
    const [busqueda, setBusqueda] = useState('');
    
    const { post } = useForm();

    const marcarLeida = (id: number) => {
        post(`/notificaciones/${id}/marcar-leida`);
    };

    const marcarTodasLeidas = () => {
        post('/notificaciones/marcar-todas-leidas');
    };

    const getPriorityColor = (prioridad: string) => {
        switch (prioridad) {
            case 'alta': return 'bg-red-500';
            case 'media': return 'bg-yellow-500';
            case 'baja': return 'bg-blue-500';
            default: return 'bg-gray-500';
        }
    };

    const formatTime = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleString();
    };

    const notificacionesFiltradas = notificaciones.data.filter(n => {
        const matchesFiltro = filtro === 'todas' || 
            (filtro === 'no_leidas' && !n.leida) ||
            (filtro === 'leidas' && n.leida) ||
            (filtro === n.prioridad);
        
        const matchesBusqueda = n.titulo.toLowerCase().includes(busqueda.toLowerCase()) ||
            n.mensaje.toLowerCase().includes(busqueda.toLowerCase());
        
        return matchesFiltro && matchesBusqueda;
    });

    return (
        <>
            <Head title="Centro de Notificaciones" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold flex items-center gap-2">
                        <Bell className="h-8 w-8" />
                        Centro de Notificaciones
                    </h1>
                    <div className="flex gap-2">
                        <Button onClick={marcarTodasLeidas} variant="outline">
                            <Check className="mr-2 h-4 w-4" />
                            Marcar Todas Leídas
                        </Button>
                        <Button variant="outline">
                            <Settings className="mr-2 h-4 w-4" />
                            Configurar
                        </Button>
                    </div>
                </div>

                {/* Filtros */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex gap-4 items-center">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Buscar notificaciones..."
                                        value={busqueda}
                                        onChange={(e) => setBusqueda(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            <Select value={filtro} onValueChange={setFiltro}>
                                <SelectTrigger className="w-48">
                                    <Filter className="mr-2 h-4 w-4" />
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="todas">Todas</SelectItem>
                                    <SelectItem value="no_leidas">No Leídas</SelectItem>
                                    <SelectItem value="leidas">Leídas</SelectItem>
                                    <SelectItem value="alta">Alta Prioridad</SelectItem>
                                    <SelectItem value="media">Media Prioridad</SelectItem>
                                    <SelectItem value="baja">Baja Prioridad</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Lista de notificaciones */}
                <div className="space-y-4">
                    {notificacionesFiltradas.map((notificacion) => (
                        <Card 
                            key={notificacion.id} 
                            className={`cursor-pointer transition-colors ${
                                !notificacion.leida ? 'bg-blue-50 border-blue-200' : ''
                            }`}
                            onClick={() => !notificacion.leida && marcarLeida(notificacion.id)}
                        >
                            <CardContent className="pt-6">
                                <div className="flex items-start gap-4">
                                    <div 
                                        className={`w-3 h-3 rounded-full mt-2 ${getPriorityColor(notificacion.prioridad)}`}
                                    />
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center justify-between mb-2">
                                            <h3 className={`font-medium ${!notificacion.leida ? 'font-bold' : ''}`}>
                                                {notificacion.titulo}
                                            </h3>
                                            <div className="flex items-center gap-2">
                                                <Badge variant="outline" className={getPriorityColor(notificacion.prioridad)}>
                                                    {notificacion.prioridad}
                                                </Badge>
                                                <span className="text-sm text-muted-foreground">
                                                    {formatTime(notificacion.created_at)}
                                                </span>
                                            </div>
                                        </div>
                                        <p className="text-muted-foreground">
                                            {notificacion.mensaje}
                                        </p>
                                        <div className="flex items-center justify-between mt-3">
                                            <Badge variant="secondary">
                                                {notificacion.tipo}
                                            </Badge>
                                            {!notificacion.leida && (
                                                <Badge className="bg-blue-500">
                                                    Nueva
                                                </Badge>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {notificacionesFiltradas.length === 0 && (
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center py-8">
                                <Bell className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900">
                                    No hay notificaciones
                                </h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    {filtro === 'todas' 
                                        ? 'No tienes notificaciones en este momento.'
                                        : `No hay notificaciones que coincidan con el filtro "${filtro}".`
                                    }
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Paginación */}
                {notificaciones.links && (
                    <div className="flex justify-center gap-2">
                        {notificaciones.links.map((link: any, index: number) => (
                            <Button
                                key={index}
                                variant={link.active ? "default" : "outline"}
                                size="sm"
                                disabled={!link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}