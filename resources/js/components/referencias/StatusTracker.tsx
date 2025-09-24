import { Badge } from '@/components/ui/badge';
import { Clock, CheckCircle, XCircle, AlertTriangle } from 'lucide-react';
import { cn } from '@/lib/utils';

interface StatusTrackerProps {
    status: 'PENDIENTE' | 'ACEPTADO' | 'NO_ADMITIDO';
    className?: string;
    showIcon?: boolean;
}

export function StatusTracker({ status, className, showIcon = true }: StatusTrackerProps) {
    const statusConfig = {
        PENDIENTE: {
            label: 'Pendiente',
            color: 'bg-yellow-500 text-white',
            icon: Clock
        },
        ACEPTADO: {
            label: 'Aceptada',
            color: 'bg-green-500 text-white',
            icon: CheckCircle
        },
        NO_ADMITIDO: {
            label: 'Rechazada',
            color: 'bg-red-500 text-white',
            icon: XCircle
        }
    };

    const config = statusConfig[status] || {
        label: status,
        color: 'bg-gray-500 text-white',
        icon: AlertTriangle
    };

    const Icon = config.icon;

    return (
        <Badge className={cn(config.color, className)}>
            {showIcon && <Icon className="h-3 w-3 mr-1" />}
            {config.label}
        </Badge>
    );
}