import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Bell, X, Check, Settings } from 'lucide-react';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { router } from '@inertiajs/react';

interface Notificacion {
    id: number;
    tipo: string;
    titulo: string;
    mensaje: string;
    prioridad: 'baja' | 'media' | 'alta';
    leida: boolean;
    created_at: string;
}

interface NotificationCenterProps {
    notificaciones: Notificacion[];
    userId: number;
}

export function NotificationCenter({ notificaciones: initialNotificaciones, userId }: NotificationCenterProps) {
    const [notificaciones, setNotificaciones] = useState<Notificacion[]>(initialNotificaciones);
    const [isOpen, setIsOpen] = useState(false);
    const [audio] = useState(new Audio('/sounds/notification.mp3'));

    const noLeidas = notificaciones.filter(n => !n.leida).length;

    useEffect(() => {
        // Configurar Echo para escuchar notificaciones en tiempo real
        if (window.Echo) {
            window.Echo.private(`user.${userId}`)
                .listen('.nueva-notificacion', (e: any) => {
                    const nuevaNotificacion: Notificacion = {
                        id: e.id,
                        tipo: e.tipo,
                        titulo: e.titulo,
                        mensaje: e.mensaje,
                        prioridad: e.prioridad,
                        leida: false,
                        created_at: e.created_at
                    };

                    setNotificaciones(prev => [nuevaNotificacion, ...prev]);
                    
                    // Reproducir sonido
                    audio.play().catch(() => {});
                    
                    // Mostrar notificación del navegador
                    if (Notification.permission === 'granted') {
                        new Notification(e.titulo, {
                            body: e.mensaje,
                            icon: '/favicon.ico'
                        });
                    }
                });
        }

        // Solicitar permisos de notificación
        if (Notification.permission === 'default') {
            Notification.requestPermission();
        }

        return () => {
            if (window.Echo) {
                window.Echo.leaveChannel(`user.${userId}`);
            }
        };
    }, [userId, audio]);

    const marcarLeida = (id: number) => {
        router.post(`/notificaciones/${id}/marcar-leida`, {}, {
            onSuccess: () => {
                setNotificaciones(prev => 
                    prev.map(n => n.id === id ? { ...n, leida: true } : n)
                );
            }
        });
    };

    const marcarTodasLeidas = () => {
        router.post('/notificaciones/marcar-todas-leidas', {}, {
            onSuccess: () => {
                setNotificaciones(prev => 
                    prev.map(n => ({ ...n, leida: true }))
                );
            }
        });
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
        const now = new Date();
        const diff = now.getTime() - date.getTime();
        const minutes = Math.floor(diff / 60000);
        
        if (minutes < 1) return 'Ahora';
        if (minutes < 60) return `${minutes}m`;
        if (minutes < 1440) return `${Math.floor(minutes / 60)}h`;
        return `${Math.floor(minutes / 1440)}d`;
    };

    return (
        <Popover open={isOpen} onOpenChange={setIsOpen}>
            <PopoverTrigger asChild>
                <Button variant="ghost" size="sm" className="relative">
                    <Bell className="h-5 w-5" />
                    {noLeidas > 0 && (
                        <Badge 
                            className="absolute -top-1 -right-1 h-5 w-5 p-0 flex items-center justify-center text-xs bg-red-500"
                        >
                            {noLeidas > 99 ? '99+' : noLeidas}
                        </Badge>
                    )}
                </Button>
            </PopoverTrigger>
            
            <PopoverContent className="w-80 p-0" align="end">
                <Card className="border-0 shadow-none">
                    <CardHeader className="pb-3">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-lg">Notificaciones</CardTitle>
                            <div className="flex gap-2">
                                {noLeidas > 0 && (
                                    <Button 
                                        size="sm" 
                                        variant="ghost"
                                        onClick={marcarTodasLeidas}
                                    >
                                        <Check className="h-4 w-4" />
                                    </Button>
                                )}
                                <Button 
                                    size="sm" 
                                    variant="ghost"
                                    onClick={() => router.visit('/notificaciones')}
                                >
                                    <Settings className="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    
                    <CardContent className="p-0">
                        <div className="max-h-96 overflow-y-auto">
                            {notificaciones.length === 0 ? (
                                <div className="p-4 text-center text-muted-foreground">
                                    No hay notificaciones
                                </div>
                            ) : (
                                notificaciones.slice(0, 10).map((notificacion) => (
                                    <div
                                        key={notificacion.id}
                                        className={`p-3 border-b hover:bg-muted/50 cursor-pointer ${
                                            !notificacion.leida ? 'bg-blue-50' : ''
                                        }`}
                                        onClick={() => marcarLeida(notificacion.id)}
                                    >
                                        <div className="flex items-start gap-3">
                                            <div 
                                                className={`w-2 h-2 rounded-full mt-2 ${getPriorityColor(notificacion.prioridad)}`}
                                            />
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center justify-between">
                                                    <p className="font-medium text-sm truncate">
                                                        {notificacion.titulo.replace(/<[^>]*>/g, '')}
                                                    </p>
                                                    <span className="text-xs text-muted-foreground">
                                                        {formatTime(notificacion.created_at)}
                                                    </span>
                                                </div>
                                                <p className="text-sm text-muted-foreground line-clamp-2">
                                                    {notificacion.mensaje.replace(/<[^>]*>/g, '')}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                        
                        {notificaciones.length > 10 && (
                            <div className="p-3 border-t">
                                <Button 
                                    variant="ghost" 
                                    className="w-full"
                                    onClick={() => router.visit('/notificaciones')}
                                >
                                    Ver todas las notificaciones
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </PopoverContent>
        </Popover>
    );
}