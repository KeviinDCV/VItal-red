import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ReportChart } from '@/components/referencias/ReportChart';
import { ExportButton } from '@/components/referencias/ExportButton';
import { DateRangeFilter } from '@/components/referencias/DateRangeFilter';
import { BarChart3, TrendingUp, Users, Clock } from 'lucide-react';

interface Props {
    estadisticas: {
        totalSolicitudes: number;
        tiempoPromedio: number;
        eficiencia: number;
        tendencias: any[];
    };
}

export default function Reportes({ estadisticas }: Props) {
    return (
        <>
            <Head title="Reportes y Analíticas" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Reportes y Analíticas</h1>
                    <div className="flex gap-2">
                        <DateRangeFilter />
                        <ExportButton />
                    </div>
                </div>

                {/* Métricas principales */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Solicitudes</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.totalSolicitudes}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tiempo Promedio</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.tiempoPromedio}h</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Eficiencia</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.eficiencia}%</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tendencia</CardTitle>
                            <BarChart3 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">+12%</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Gráficos */}
                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Solicitudes por Mes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ReportChart data={estadisticas.tendencias} type="line" />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Por Especialidad</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ReportChart data={estadisticas.tendencias} type="bar" />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}