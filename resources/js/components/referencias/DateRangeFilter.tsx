import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Badge } from '@/components/ui/badge';
import { Calendar, X } from 'lucide-react';
import { useState } from 'react';

interface DateRange {
    from?: string;
    to?: string;
}

interface DateRangeFilterProps {
    value?: DateRange;
    onChange: (range: DateRange) => void;
    onClear?: () => void;
    placeholder?: string;
    showClear?: boolean;
}

export function DateRangeFilter({ 
    value, 
    onChange, 
    onClear, 
    placeholder = "Seleccionar rango de fechas",
    showClear = true 
}: DateRangeFilterProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [tempRange, setTempRange] = useState<DateRange>(value || {});

    const handleApply = () => {
        onChange(tempRange);
        setIsOpen(false);
    };

    const handleClear = () => {
        setTempRange({});
        onChange({});
        onClear?.();
        setIsOpen(false);
    };

    const formatDateRange = (range: DateRange) => {
        if (!range.from && !range.to) return null;
        
        const fromDate = range.from ? new Date(range.from).toLocaleDateString() : '';
        const toDate = range.to ? new Date(range.to).toLocaleDateString() : '';
        
        if (range.from && range.to) {
            return `${fromDate} - ${toDate}`;
        } else if (range.from) {
            return `Desde ${fromDate}`;
        } else if (range.to) {
            return `Hasta ${toDate}`;
        }
        
        return null;
    };

    const displayText = formatDateRange(value || {});

    return (
        <div className="flex items-center gap-2">
            <Popover open={isOpen} onOpenChange={setIsOpen}>
                <PopoverTrigger asChild>
                    <Button variant="outline" className="justify-start text-left font-normal">
                        <Calendar className="mr-2 h-4 w-4" />
                        {displayText || placeholder}
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-80" align="start">
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="from-date">Fecha desde</Label>
                            <Input
                                id="from-date"
                                type="date"
                                value={tempRange.from || ''}
                                onChange={(e) => setTempRange({ ...tempRange, from: e.target.value })}
                            />
                        </div>
                        
                        <div className="space-y-2">
                            <Label htmlFor="to-date">Fecha hasta</Label>
                            <Input
                                id="to-date"
                                type="date"
                                value={tempRange.to || ''}
                                onChange={(e) => setTempRange({ ...tempRange, to: e.target.value })}
                                min={tempRange.from}
                            />
                        </div>
                        
                        <div className="flex gap-2">
                            <Button onClick={handleApply} className="flex-1">
                                Aplicar
                            </Button>
                            <Button onClick={handleClear} variant="outline" className="flex-1">
                                Limpiar
                            </Button>
                        </div>
                        
                        <div className="space-y-2">
                            <Label>Rangos rápidos</Label>
                            <div className="grid grid-cols-2 gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        const today = new Date();
                                        const weekAgo = new Date(today);
                                        weekAgo.setDate(today.getDate() - 7);
                                        setTempRange({
                                            from: weekAgo.toISOString().split('T')[0],
                                            to: today.toISOString().split('T')[0]
                                        });
                                    }}
                                >
                                    Última semana
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        const today = new Date();
                                        const monthAgo = new Date(today);
                                        monthAgo.setMonth(today.getMonth() - 1);
                                        setTempRange({
                                            from: monthAgo.toISOString().split('T')[0],
                                            to: today.toISOString().split('T')[0]
                                        });
                                    }}
                                >
                                    Último mes
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        const today = new Date();
                                        const yearAgo = new Date(today);
                                        yearAgo.setFullYear(today.getFullYear() - 1);
                                        setTempRange({
                                            from: yearAgo.toISOString().split('T')[0],
                                            to: today.toISOString().split('T')[0]
                                        });
                                    }}
                                >
                                    Último año
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        const today = new Date();
                                        setTempRange({
                                            from: today.toISOString().split('T')[0],
                                            to: today.toISOString().split('T')[0]
                                        });
                                    }}
                                >
                                    Hoy
                                </Button>
                            </div>
                        </div>
                    </div>
                </PopoverContent>
            </Popover>
            
            {displayText && showClear && (
                <Badge variant="secondary" className="flex items-center gap-1">
                    {displayText}
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-4 w-4 p-0 hover:bg-transparent"
                        onClick={handleClear}
                    >
                        <X className="h-3 w-3" />
                    </Button>
                </Badge>
            )}
        </div>
    );
}