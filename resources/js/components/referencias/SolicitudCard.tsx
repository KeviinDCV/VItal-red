import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PriorityBadge } from './PriorityBadge';
import { StatusTracker } from './StatusTracker';
import { TimeIndicator } from './TimeIndicator';
import { User, Stethoscope, Eye } from 'lucide-react';

interface SolicitudCardProps {
    solicitud: {
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
        };
        puntuacion_ia: number;
    };
    onView?: (id: number) => void;
    onDecision?: (id: number) => void;
    showActions?: boolean;
}

export function SolicitudCard({ solicitud, onView, onDecision, showActions = true }: SolicitudCardProps) {
    return (
        <Card className="hover:shadow-md transition-shadow">
            <CardHeader className="pb-3">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <span className="font-mono text-sm text-muted-foreground">
                            {solicitud.codigo_solicitud}
                        </span>
                        <PriorityBadge priority={solicitud.prioridad} />
                    </div>
                    <StatusTracker status={solicitud.estado} />
                </div>
            </CardHeader>
            
            <CardContent className="space-y-4">
                <div className="flex items-start gap-3">
                    <User className="h-5 w-5 text-muted-foreground mt-0.5" />
                    <div>
                        <p className="font-medium">
                            {solicitud.registro_medico.nombre} {solicitud.registro_medico.apellidos}
                        </p>
                        <p className="text-sm text-muted-foreground">
                            {solicitud.registro_medico.numero_identificacion}
                        </p>
                    </div>
                </div>
                
                <div className="flex items-center gap-3">
                    <Stethoscope className="h-5 w-5 text-muted-foreground" />
                    <span className="text-sm">
                        {solicitud.registro_medico.especialidad_solicitada}
                    </span>
                </div>
                
                <div className="flex items-center justify-between">
                    <TimeIndicator date={solicitud.fecha_solicitud} />
                    <Badge variant="outline">
                        IA: {Math.round(solicitud.puntuacion_ia * 100)}%
                    </Badge>
                </div>
                
                {showActions && (
                    <div className="flex gap-2 pt-2">
                        <Button 
                            size="sm" 
                            variant="outline" 
                            onClick={() => onView?.(solicitud.id)}
                            className="flex-1"
                        >
                            <Eye className="h-4 w-4 mr-1" />
                            Ver
                        </Button>
                        {solicitud.estado === 'PENDIENTE' && (
                            <Button 
                                size="sm" 
                                onClick={() => onDecision?.(solicitud.id)}
                                className="flex-1"
                            >
                                Decidir
                            </Button>
                        )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}