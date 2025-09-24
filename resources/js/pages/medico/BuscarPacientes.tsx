import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, User } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { useState } from 'react';

export default function BuscarPacientes() {
    const [searchTerm, setSearchTerm] = useState('');
    const [results, setResults] = useState([]);

    const handleSearch = () => {
        // Aquí iría la lógica de búsqueda
        console.log('Buscando:', searchTerm);
    };

    return (
        <AppLayout>
            <Head title="Buscar Pacientes" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Buscar Pacientes</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Search className="h-5 w-5" />
                            Búsqueda de Pacientes
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex gap-4">
                            <Input
                                placeholder="Buscar por nombre, apellido o número de identificación..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="flex-1"
                            />
                            <Button onClick={handleSearch}>
                                <Search className="h-4 w-4 mr-2" />
                                Buscar
                            </Button>
                        </div>

                        {results.length === 0 && (
                            <div className="text-center py-8 text-muted-foreground">
                                <User className="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p>No se encontraron pacientes</p>
                                <p className="text-sm">Ingrese un término de búsqueda para comenzar</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}