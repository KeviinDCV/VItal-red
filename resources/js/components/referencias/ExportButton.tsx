import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Download, FileSpreadsheet, FileText, Printer } from 'lucide-react';
import { useState } from 'react';

interface ExportButtonProps {
    onExport: (format: 'excel' | 'pdf' | 'csv') => void;
    loading?: boolean;
    disabled?: boolean;
    formats?: ('excel' | 'pdf' | 'csv')[];
}

export function ExportButton({ 
    onExport, 
    loading = false, 
    disabled = false,
    formats = ['excel', 'pdf', 'csv']
}: ExportButtonProps) {
    const [isExporting, setIsExporting] = useState(false);

    const handleExport = async (format: 'excel' | 'pdf' | 'csv') => {
        setIsExporting(true);
        try {
            await onExport(format);
        } finally {
            setIsExporting(false);
        }
    };

    const getFormatIcon = (format: string) => {
        switch (format) {
            case 'excel': return <FileSpreadsheet className="h-4 w-4" />;
            case 'pdf': return <FileText className="h-4 w-4" />;
            case 'csv': return <FileSpreadsheet className="h-4 w-4" />;
            default: return <Download className="h-4 w-4" />;
        }
    };

    const getFormatLabel = (format: string) => {
        switch (format) {
            case 'excel': return 'Exportar a Excel';
            case 'pdf': return 'Exportar a PDF';
            case 'csv': return 'Exportar a CSV';
            default: return 'Exportar';
        }
    };

    if (formats.length === 1) {
        return (
            <Button
                onClick={() => handleExport(formats[0])}
                disabled={disabled || loading || isExporting}
                variant="outline"
            >
                {getFormatIcon(formats[0])}
                <span className="ml-2">
                    {isExporting ? 'Exportando...' : getFormatLabel(formats[0])}
                </span>
            </Button>
        );
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button 
                    variant="outline" 
                    disabled={disabled || loading || isExporting}
                >
                    <Download className="h-4 w-4" />
                    <span className="ml-2">
                        {isExporting ? 'Exportando...' : 'Exportar'}
                    </span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {formats.includes('excel') && (
                    <DropdownMenuItem onClick={() => handleExport('excel')}>
                        <FileSpreadsheet className="h-4 w-4 mr-2" />
                        Excel (.xlsx)
                    </DropdownMenuItem>
                )}
                {formats.includes('pdf') && (
                    <DropdownMenuItem onClick={() => handleExport('pdf')}>
                        <FileText className="h-4 w-4 mr-2" />
                        PDF (.pdf)
                    </DropdownMenuItem>
                )}
                {formats.includes('csv') && (
                    <DropdownMenuItem onClick={() => handleExport('csv')}>
                        <FileSpreadsheet className="h-4 w-4 mr-2" />
                        CSV (.csv)
                    </DropdownMenuItem>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}