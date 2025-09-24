import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Bell, CheckCircle, Clock } from 'lucide-react';

interface Notificacion {
    id: number;
    tipo: string;
    titulo: string;
    mensaje: string;
    leida: boolean;
    prioridad: 'baja' | 'media' | 'alta';
    created_at: string;
}

interface Props {
    notificaciones: {
        data: Notificacion[];
    };
    estadisticas: {
        no_leidas: number;
        total: number;
    };
}

export default function Notificaciones({ notificaciones, estadisticas }: Props) {
    const getPriorityColor = (prioridad: string) => {
        switch (prioridad) {
            case 'alta': return 'bg-red-500';
            case 'media': return 'bg-yellow-500';
            case 'baja': return 'bg-blue-500';
            default: return 'bg-gray-500';
        }
    };

    return (
        <>
            <Head title="Notificaciones" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Notificaciones</h1>
                    <div className="flex gap-2">
                        <Badge variant="outline">
                            {estadisticas.no_leidas} sin leer
                        </Badge>
                        <Badge variant="secondary">
                            {estadisticas.total} total
                        </Badge>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <Bell className="mr-2 h-5 w-5" />
                            Centro de Notificaciones
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {notificaciones.data.map((notificacion) => (
                                <div 
                                    key={notificacion.id}
                                    className={`p-4 rounded-lg border ${!notificacion.leida ? 'bg-blue-50 border-blue-200' : 'bg-gray-50'}`}
                                >
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center gap-2 mb-2">
                                                <h3 className="font-medium">{notificacion.titulo}</h3>
                                                <Badge className={`${getPriorityColor(notificacion.prioridad)} text-white text-xs`}>
                                                    {notificacion.prioridad}
                                                </Badge>
                                                {!notificacion.leida && (
                                                    <Badge variant="outline" className="text-xs">
                                                        Nuevo
                                                    </Badge>
                                                )}
                                            </div>
                                            <p className="text-sm text-gray-600 mb-2">
                                                {notificacion.mensaje}
                                            </p>
                                            <div className="flex items-center text-xs text-gray-500">
                                                <Clock className="h-3 w-3 mr-1" />
                                                {new Date(notificacion.created_at).toLocaleString()}
                                            </div>
                                        </div>
                                        {!notificacion.leida && (
                                            <Button size="sm" variant="outline">
                                                <CheckCircle className="h-4 w-4 mr-1" />
                                                Marcar leída
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))}
                            
                            {notificaciones.data.length === 0 && (
                                <div className="text-center py-8">
                                    <Bell className="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">No hay notificaciones</h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        Cuando reciba notificaciones, aparecerán aquí.
                                    </p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}