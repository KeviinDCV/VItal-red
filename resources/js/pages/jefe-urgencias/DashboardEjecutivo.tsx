import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Activity, 
    AlertTriangle, 
    Clock, 
    TrendingUp, 
    Users,
    Brain,
    Zap,
    Target
} from 'lucide-react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';

interface ExecutiveMetrics {
    totalSolicitudesHoy: number;
    casosRojosPendientes: number;
    casosVerdesAutomaticos: number;
    tiempoPromedioRespuesta: number;
    eficienciaIA: number;
    alertasCriticas: CriticalAlert[];
    tendenciasSemanal: number[];
    especialidadesDemandadas: SpecialtyDemand[];
    prediccionDemanda: number;
}

interface CriticalAlert {
    id: number;
    message: string;
    severity: 'HIGH' | 'MEDIUM' | 'LOW';
    timestamp: string;
    solicitudId?: number;
}

interface SpecialtyDemand {
    especialidad: string;
    cantidad: number;
    porcentaje: number;
    tendencia: 'up' | 'down' | 'stable';
}

interface Props {
    metrics: ExecutiveMetrics;
}

export default function DashboardEjecutivo({ metrics }: Props) {
    const [realTimeMetrics, setRealTimeMetrics] = useState<ExecutiveMetrics>(metrics);
    const [isConnected, setIsConnected] = useState(false);

    useEffect(() => {
        // WebSocket connection for real-time updates
        const ws = new WebSocket(`ws://localhost:6001/executive-dashboard`);
        
        ws.onopen = () => {
            setIsConnected(true);
            console.log('Connected to executive dashboard WebSocket');
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            if (data.type === 'metrics_update') {
                setRealTimeMetrics(data.metrics);
            }
            if (data.type === 'critical_alert') {
                playAlertSound();
                showCriticalAlert(data.alert);
            }
        };

        ws.onclose = () => {
            setIsConnected(false);
        };

        return () => ws.close();
    }, []);

    const playAlertSound = () => {
        const audio = new Audio('/sounds/critical-alert.mp3');
        audio.play().catch(e => console.log('Could not play alert sound:', e));
    };

    const showCriticalAlert = (alert: CriticalAlert) => {
        if (Notification.permission === 'granted') {
            new Notification('🚨 ALERTA CRÍTICA', {
                body: alert.message,
                icon: '/images/logo.png',
                requireInteraction: true
            });
        }
    };

    const getSeverityColor = (severity: string) => {
        switch (severity) {
            case 'HIGH': return 'bg-red-500';
            case 'MEDIUM': return 'bg-yellow-500';
            case 'LOW': return 'bg-blue-500';
            default: return 'bg-gray-500';
        }
    };

    const getTrendIcon = (trend: string) => {
        switch (trend) {
            case 'up': return <TrendingUp className="h-4 w-4 text-green-500" />;
            case 'down': return <TrendingUp className="h-4 w-4 text-red-500 rotate-180" />;
            default: return <Activity className="h-4 w-4 text-gray-500" />;
        }
    };

    return (
        <AppLayout>
            <Head title="Dashboard Ejecutivo - Jefe de Urgencias" />
            
            <div className="space-y-6">
                {/* Header with connection status */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold flex items-center gap-2">
                            <Target className="h-8 w-8" />
                            Dashboard Ejecutivo
                        </h1>
                        <p className="text-muted-foreground">
                            Métricas en tiempo real del sistema de referencias
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className={`h-3 w-3 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-500'}`} />
                        <span className="text-sm">
                            {isConnected ? 'Conectado' : 'Desconectado'}
                        </span>
                        <Button variant="outline" size="sm">
                            <Activity className="h-4 w-4 mr-2" />
                            Actualizar
                        </Button>
                    </div>
                </div>

                {/* Critical Alerts */}
                {realTimeMetrics.alertasCriticas.length > 0 && (
                    <Card className="border-red-200 bg-red-50">
                        <CardHeader>
                            <CardTitle className="text-red-700 flex items-center gap-2">
                                <AlertTriangle className="h-5 w-5" />
                                Alertas Críticas ({realTimeMetrics.alertasCriticas.length})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {realTimeMetrics.alertasCriticas.slice(0, 3).map((alert) => (
                                    <div key={alert.id} className="flex items-center justify-between p-3 bg-white rounded-lg">
                                        <div className="flex items-center gap-3">
                                            <div className={`h-2 w-2 rounded-full ${getSeverityColor(alert.severity)}`} />
                                            <span className="text-sm font-medium">{alert.message}</span>
                                        </div>
                                        <span className="text-xs text-gray-500">
                                            {new Date(alert.timestamp).toLocaleTimeString()}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Main KPIs */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Solicitudes Hoy</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">
                                {realTimeMetrics.totalSolicitudesHoy}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Meta: 1,000 diarias
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Casos ROJOS Pendientes</CardTitle>
                            <AlertTriangle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {realTimeMetrics.casosRojosPendientes}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Requieren atención inmediata
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Respuestas Automáticas</CardTitle>
                            <Zap className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {realTimeMetrics.casosVerdesAutomaticos}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Meta: 700 diarias
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
                                {realTimeMetrics.eficienciaIA}%
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Meta: 95% precisión
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Specialty Analysis */}
                <Card>
                    <CardHeader>
                        <CardTitle>Análisis por Especialidad</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {realTimeMetrics.especialidadesDemandadas.map((specialty, index) => (
                                <div key={index} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center gap-3">
                                        <div className="font-medium">{specialty.especialidad}</div>
                                        {getTrendIcon(specialty.tendencia)}
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <Badge variant="outline">
                                            {specialty.cantidad} solicitudes
                                        </Badge>
                                        <div className="text-sm text-muted-foreground">
                                            {specialty.porcentaje}%
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Predictive Analysis */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TrendingUp className="h-5 w-5" />
                            Análisis Predictivo
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-blue-600">
                                    {realTimeMetrics.prediccionDemanda}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Solicitudes próxima hora
                                </div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-orange-600">
                                    {Math.round(realTimeMetrics.tiempoPromedioRespuesta)}h
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Tiempo promedio respuesta
                                </div>
                            </div>
                            <div className="text-center p-4 border rounded-lg">
                                <div className="text-2xl font-bold text-green-600">
                                    99.2%
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Uptime del sistema
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}