import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { X } from 'lucide-react';

interface SpecialtyFilterProps {
    value?: string;
    onValueChange: (value: string) => void;
    onClear?: () => void;
    placeholder?: string;
    showClear?: boolean;
}

const especialidades = [
    'Cardiología',
    'Neurología', 
    'Oncología',
    'Cirugía General',
    'Medicina Interna',
    'Pediatría',
    'Ginecología',
    'Urología',
    'Ortopedia',
    'Dermatología',
    'Psiquiatría',
    'Oftalmología',
    'Otorrinolaringología',
    'Anestesiología',
    'Radiología'
];

export function SpecialtyFilter({ 
    value, 
    onValueChange, 
    onClear, 
    placeholder = "Todas las especialidades",
    showClear = true 
}: SpecialtyFilterProps) {
    return (
        <div className="flex items-center gap-2">
            <Select value={value} onValueChange={onValueChange}>
                <SelectTrigger className="w-48">
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todas las especialidades</SelectItem>
                    {especialidades.map((especialidad) => (
                        <SelectItem key={especialidad} value={especialidad}>
                            {especialidad}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            
            {value && value !== 'all' && showClear && (
                <div className="flex items-center gap-1">
                    <Badge variant="secondary" className="flex items-center gap-1">
                        {value}
                        <Button
                            variant="ghost"
                            size="sm"
                            className="h-4 w-4 p-0 hover:bg-transparent"
                            onClick={onClear}
                        >
                            <X className="h-3 w-3" />
                        </Button>
                    </Badge>
                </div>
            )}
        </div>
    );
}