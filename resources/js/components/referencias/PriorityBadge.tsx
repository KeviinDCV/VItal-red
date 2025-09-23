import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

interface PriorityBadgeProps {
    priority: 'ROJO' | 'VERDE';
    className?: string;
}

export function PriorityBadge({ priority, className }: PriorityBadgeProps) {
    const baseClasses = "text-white font-medium";
    const priorityClasses = {
        ROJO: "bg-red-500 hover:bg-red-600",
        VERDE: "bg-green-500 hover:bg-green-600"
    };

    return (
        <Badge className={cn(baseClasses, priorityClasses[priority], className)}>
            {priority}
        </Badge>
    );
}