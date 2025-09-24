import { Clock } from 'lucide-react';
import { cn } from '@/lib/utils';

interface TimeIndicatorProps {
    date: string;
    className?: string;
    showIcon?: boolean;
}

export function TimeIndicator({ date, className, showIcon = true }: TimeIndicatorProps) {
    const getTimeElapsed = (fecha: string) => {
        const now = new Date();
        const targetDate = new Date(fecha);
        const diffMs = now.getTime() - targetDate.getTime();
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        
        if (diffHours < 1) {
            const diffMinutes = Math.floor(diffMs / (1000 * 60));
            return `${diffMinutes}m`;
        } else if (diffHours < 24) {
            return `${diffHours}h`;
        } else {
            const diffDays = Math.floor(diffHours / 24);
            return `${diffDays}d`;
        }
    };

    const getUrgencyColor = (fecha: string) => {
        const now = new Date();
        const targetDate = new Date(fecha);
        const diffHours = Math.floor((now.getTime() - targetDate.getTime()) / (1000 * 60 * 60));
        
        if (diffHours > 48) return 'text-red-600'; // Más de 48 horas
        if (diffHours > 24) return 'text-yellow-600'; // Más de 24 horas
        return 'text-green-600'; // Menos de 24 horas
    };

    const timeElapsed = getTimeElapsed(date);
    const colorClass = getUrgencyColor(date);

    return (
        <div className={cn('flex items-center gap-1', colorClass, className)}>
            {showIcon && <Clock className="h-4 w-4" />}
            <span className="text-sm font-medium">{timeElapsed}</span>
        </div>
    );
}