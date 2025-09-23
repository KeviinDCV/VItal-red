import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    TrendingUp, 
    AlertTriangle, 
    Clock, 
    Brain,
    Users,
    Activity,
    Download,
    RefreshCw
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { useState, useEffect } from 'react';

interface Analytics {
    kpis: {
        total_solicitudes: number;
        solicitudes_hoy: number;
        tiempo_promedio: number;
        eficiencia_ia: number;
        satisfaccion: number;
        casos_criticos: number;
    };
    trends: Array<{
        date: string;
        solicitudes: number;
        rojas: number;
        verdes: number;
    }>;
    predictions: {
        next_hour: number;
        next_day: number;
        next_week: number;
        peak_hours: number[];
        demand_forecast: Array<{
            hour: number;
            predicted_demand: number;
        }>;
    };
    efficiency: {
        processing_rate: number;
        auto_responses: number;
        manual_reviews: number;
        avg_decision_time: number;
        specialties_performance: Array<{
            especialidad_solicitada: string;
            total: number;
            avg_score: number;
        }>;
    };
    alerts: Array<{
        type: string;
        message: string;
        severity: string;
    }>;
}

interface Props {
    analytics: Analytics;
}

export default function Analytics({ analytics }: Props) {
    const [realTimeData, setRealTimeData] = useState(analytics);
    const [isUpdating, setIsUpdating] = useState(false);

    useEffect(() => {
        const interval = setInterval(() => {
            updateMetrics();
        }, 300000); // Update every 5 minutes

        return () => clearInterval(interval);
    }, []);

    const updateMetrics = async () => {
        setIsUpdating(true);
        // In real implementation, this would fetch updated data
        setTimeout(() => {
            setIsUpdating(false);
        }, 2000);
    };

    const getSeverityColor = (severity: string) => {
        switch (severity) {
            case 'CRITICAL': return 'bg-red-500';
            case 'HIGH': return 'bg-orange-500';
            case 'MEDIUM': return 'bg-yellow-500';
            default: return 'bg-blue-500';
        }
    };

    const exportReport = (format: string) => {
        // Implementation for report export
        console.log(`Exporting ${format} report`);
    };

    return (
        <AppLayout>
            <Head title="Analytics y Business Intelligence" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold flex items-center gap-2">
                            <TrendingUp className="h-8 w-8" />
                            Analytics & BI
                        </h1>
                        <p className="text-muted-foreground">
                            Análisis en tiempo real y business intelligence
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button 
                            variant="outline" 
                            onClick={() => updateMetrics()}
                            disabled={isUpdating}
                        >
                            <RefreshCw className={`h-4 w-4 mr-2 ${isUpdating ? 'animate-spin' : ''}`} />
                            {isUpdating ? 'Actualizando...' : 'Actualizar'}
                        </Button>
                        <Button onClick={() => exportReport('excel')}>
                            <Download className="h-4 w-4 mr-2" />
                            Exportar
                        </Button>
                    </div>
                </div>

                {/* Performance Alerts */}
                {realTimeData.alerts.length > 0 && (
                    <Card className="border-orange-200 bg-orange-50">
                        <CardHeader>
                            <CardTitle className="text-orange-700 flex items-center gap-2">
                                <AlertTriangle className="h-5 w-5" />
                                Alertas de Performance ({realTimeData.alerts.length})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {realTimeData.alerts.map((alert, index) => (
                                    <div key={index} className="flex items-center gap-3 p-3 bg-white rounded-lg">
                                        <div className={`h-2 w-2 rounded-full ${getSeverityColor(alert.severity)}`} />
                                        <span className="text-sm font-medium">{alert.message}</span>
                                        <Badge variant="outline">{alert.severity}</Badge>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* KPIs Dashboard */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Solicitudes Hoy</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">
                                {realTimeData.kpis.solicitudes_hoy}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Total: {realTimeData.kpis.total_solicitudes}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tiempo Promedio</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">
                                {realTimeData.kpis.tiempo_promedio}h
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Meta: &lt;24h
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Eficiencia IA</CardTitle>
                            <Brain className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-purple-600">
                                {realTimeData.kpis.eficiencia_ia}%
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Meta: 95%
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Satisfacción</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {realTimeData.kpis.satisfaccion}%
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Usuarios satisfechos
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Casos Críticos</CardTitle>
                            <AlertTriangle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {realTimeData.kpis.casos_criticos}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Pendientes ROJOS
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tasa de Procesamiento</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-indigo-600">
                                {realTimeData.efficiency.processing_rate}%
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Solicitudes procesadas
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Predictions */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Brain className="h-5 w-5" />
                            Análisis Predictivo
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-4">
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-blue-600">
                                    {realTimeData.predictions.next_hour}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Próxima hora
                                </div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-green-600">
                                    {realTimeData.predictions.next_day}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Mañana
                                </div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-purple-600">
                                    {realTimeData.predictions.next_week}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Próxima semana
                                </div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-sm font-medium text-orange-600">
                                    Horas Pico
                                </div>
                                <div className="text-xs text-muted-foreground">
                                    {realTimeData.predictions.peak_hours.join(', ')}h
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Specialties Performance */}
                <Card>
                    <CardHeader>
                        <CardTitle>Performance por Especialidad</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {realTimeData.efficiency.specialties_performance.slice(0, 5).map((specialty, index) => (
                                <div key={index} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div>
                                        <div className="font-medium">{specialty.especialidad_solicitada}</div>
                                        <div className="text-sm text-muted-foreground">
                                            {specialty.total} solicitudes
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <Badge variant="outline">
                                            Score: {(specialty.avg_score * 100).toFixed(1)}%
                                        </Badge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Efficiency Metrics */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Métricas de Eficiencia</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex justify-between">
                                    <span>Respuestas Automáticas:</span>
                                    <Badge className="bg-green-500">{realTimeData.efficiency.auto_responses}</Badge>
                                </div>
                                <div className="flex justify-between">
                                    <span>Revisiones Manuales:</span>
                                    <Badge className="bg-orange-500">{realTimeData.efficiency.manual_reviews}</Badge>
                                </div>
                                <div className="flex justify-between">
                                    <span>Tiempo Promedio Decisión:</span>
                                    <Badge variant="outline">{realTimeData.efficiency.avg_decision_time}h</Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Exportación de Reportes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <Button 
                                    className="w-full" 
                                    variant="outline"
                                    onClick={() => exportReport('excel')}
                                >
                                    <Download className="h-4 w-4 mr-2" />
                                    Exportar Excel
                                </Button>
                                <Button 
                                    className="w-full" 
                                    variant="outline"
                                    onClick={() => exportReport('pdf')}
                                >
                                    <Download className="h-4 w-4 mr-2" />
                                    Exportar PDF
                                </Button>
                                <div className="text-xs text-muted-foreground">
                                    Reportes se actualizan cada 5 minutos
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}