import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Slider } from '@/components/ui/slider';
import { Textarea } from '@/components/ui/textarea';
import { Settings, TestTube, Save } from 'lucide-react';

interface Props {
    configuracion: {
        peso_edad: number;
        peso_gravedad: number;
        peso_especialidad: number;
        criterios_rojo: string;
        criterios_verde: string;
    };
}

export default function ConfigurarIA({ configuracion }: Props) {
    const { data, setData, post, processing } = useForm({
        peso_edad: configuracion.peso_edad || 0.3,
        peso_gravedad: configuracion.peso_gravedad || 0.5,
        peso_especialidad: configuracion.peso_especialidad || 0.2,
        criterios_rojo: configuracion.criterios_rojo || '',
        criterios_verde: configuracion.criterios_verde || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/configuracion-ia');
    };

    const probarAlgoritmo = () => {
        post('/admin/configuracion-ia/probar');
    };

    return (
        <>
            <Head title="Configuración IA" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Configuración del Algoritmo IA</h1>
                    <Button onClick={probarAlgoritmo} variant="outline">
                        <TestTube className="mr-2 h-4 w-4" />
                        Probar Algoritmo
                    </Button>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Settings className="h-5 w-5" />
                                Pesos del Algoritmo
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div>
                                <Label>Peso Edad ({Math.round(data.peso_edad * 100)}%)</Label>
                                <Slider
                                    value={[data.peso_edad]}
                                    onValueChange={(value) => setData('peso_edad', value[0])}
                                    max={1}
                                    min={0}
                                    step={0.1}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <Label>Peso Gravedad ({Math.round(data.peso_gravedad * 100)}%)</Label>
                                <Slider
                                    value={[data.peso_gravedad]}
                                    onValueChange={(value) => setData('peso_gravedad', value[0])}
                                    max={1}
                                    min={0}
                                    step={0.1}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <Label>Peso Especialidad ({Math.round(data.peso_especialidad * 100)}%)</Label>
                                <Slider
                                    value={[data.peso_especialidad]}
                                    onValueChange={(value) => setData('peso_especialidad', value[0])}
                                    max={1}
                                    min={0}
                                    step={0.1}
                                    className="mt-2"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <div className="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-red-600">Criterios ROJO (Alta Prioridad)</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Textarea
                                    value={data.criterios_rojo}
                                    onChange={(e) => setData('criterios_rojo', e.target.value)}
                                    placeholder="Defina los criterios para clasificación ROJA..."
                                    rows={6}
                                />
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-green-600">Criterios VERDE (Baja Prioridad)</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Textarea
                                    value={data.criterios_verde}
                                    onChange={(e) => setData('criterios_verde', e.target.value)}
                                    placeholder="Defina los criterios para clasificación VERDE..."
                                    rows={6}
                                />
                            </CardContent>
                        </Card>
                    </div>

                    <Button type="submit" disabled={processing} className="w-full">
                        <Save className="mr-2 h-4 w-4" />
                        Guardar Configuración
                    </Button>
                </form>
            </div>
        </>
    );
}