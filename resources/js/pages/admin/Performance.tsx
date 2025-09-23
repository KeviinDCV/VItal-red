import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Zap, 
    Database, 
    Activity,
    Server,
    RefreshCw,
    Trash2,
    AlertTriangle,
    CheckCircle
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { useState, useEffect } from 'react';

interface Performance {
    system_health: {
        status: string;
        metrics: {
            database: number;
            cache: number;
            memory: number;
            response_time: number;
            error_rate: number;
        };
        alerts: Array<{
            metric: string;
            value: number;
            threshold: number;
            severity: string;
        }>;
    };
    cache_stats: {
        memory_usage: string;
        connected_clients: number;
        total_commands: number;
        hit_rate: number;
        keys_count: number;
    };
    system_metrics: {
        uptime: string;
        memory_usage: number;
        cpu_usage: number;
        disk_usage: number;
        active_users: number;
        requests_per_minute: number;
        error_rate: number;
        database_connections: number;
    };
}

interface Props {
    performance: Performance;
}

export default function Performance({ performance }: Props) {
    const [isOptimizing, setIsOptimizing] = useState(false);
    const [isClearingCache, setIsClearingCache] = useState(false);
    const [realTimeMetrics, setRealTimeMetrics] = useState(performance);

    useEffect(() => {
        const interval = setInterval(() => {
            // Update metrics every 30 seconds
            fetch('/admin/performance/metrics')
                .then(res => res.json())
                .then(data => {
                    setRealTimeMetrics(prev => ({
                        ...prev,
                        system_metrics: data.metrics
                    }));
                });
        }, 30000);

        return () => clearInterval(interval);
    }, []);

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'healthy': return 'text-green-600';
            case 'warning': return 'text-yellow-600';
            case 'critical': return 'text-red-600';
            default: return 'text-gray-600';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'healthy': return <CheckCircle className="h-5 w-5 text-green-500" />;
            case 'warning': return <AlertTriangle className="h-5 w-5 text-yellow-500" />;
            case 'critical': return <AlertTriangle className="h-5 w-5 text-red-500" />;
            default: return <Activity className="h-5 w-5 text-gray-500" />;
        }
    };

    const optimizeDatabase = async () => {
        setIsOptimizing(true);
        try {
            await fetch('/admin/performance/optimize-database', { method: 'POST' });
        } finally {
            setIsOptimizing(false);
        }
    };

    const clearCache = async (type: string) => {
        setIsClearingCache(true);
        try {
            await fetch('/admin/performance/clear-cache', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type })
            });
        } finally {
            setIsClearingCache(false);
        }
    };

    return (
        <AppLayout>
            <Head title="Performance y Monitoreo" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold flex items-center gap-2">
                            <Zap className="h-8 w-8" />
                            Performance & Monitoreo
                        </h1>
                        <p className="text-muted-foreground">
                            Optimización y monitoreo del sistema en tiempo real
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button 
                            variant="outline"
                            onClick={() => optimizeDatabase()}
                            disabled={isOptimizing}
                        >
                            <Database className={`h-4 w-4 mr-2 ${isOptimizing ? 'animate-spin' : ''}`} />
                            {isOptimizing ? 'Optimizando...' : 'Optimizar DB'}
                        </Button>
                        <Button 
                            variant="outline"
                            onClick={() => clearCache('all')}
                            disabled={isClearingCache}
                        >
                            <Trash2 className={`h-4 w-4 mr-2 ${isClearingCache ? 'animate-spin' : ''}`} />
                            Limpiar Cache
                        </Button>
                    </div>
                </div>

                {/* System Health Status */}
                <Card className={`border-2 ${
                    realTimeMetrics.system_health.status === 'healthy' ? 'border-green-200 bg-green-50' :
                    realTimeMetrics.system_health.status === 'warning' ? 'border-yellow-200 bg-yellow-50' :
                    'border-red-200 bg-red-50'
                }`}>
                    <CardHeader>
                        <CardTitle className={`flex items-center gap-2 ${getStatusColor(realTimeMetrics.system_health.status)}`}>
                            {getStatusIcon(realTimeMetrics.system_health.status)}
                            Estado del Sistema: {realTimeMetrics.system_health.status.toUpperCase()}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {realTimeMetrics.system_health.alerts.length > 0 && (
                            <div className="space-y-2">
                                <h4 className="font-medium">Alertas Activas:</h4>
                                {realTimeMetrics.system_health.alerts.map((alert, index) => (
                                    <div key={index} className="flex items-center justify-between p-2 bg-white rounded border">
                                        <span className="text-sm">
                                            {alert.metric}: {alert.value} (umbral: {alert.threshold})
                                        </span>
                                        <Badge className={
                                            alert.severity === 'critical' ? 'bg-red-500' :
                                            alert.severity === 'high' ? 'bg-orange-500' :
                                            'bg-yellow-500'
                                        }>
                                            {alert.severity}
                                        </Badge>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Performance Metrics */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tiempo Respuesta DB</CardTitle>
                            <Database className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {realTimeMetrics.system_health.metrics.database.toFixed(1)}ms
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Meta: &lt;200ms
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Cache Hit Rate</CardTitle>
                            <Zap className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {realTimeMetrics.cache_stats.hit_rate}%
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {realTimeMetrics.cache_stats.keys_count} keys
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Uso de Memoria</CardTitle>
                            <Server className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {realTimeMetrics.system_metrics.memory_usage}MB
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Sistema
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Usuarios Activos</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">
                                {realTimeMetrics.system_metrics.active_users}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Conectados ahora
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Detailed Metrics */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Métricas del Sistema</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex justify-between">
                                    <span>Uptime:</span>
                                    <Badge className="bg-green-500">{realTimeMetrics.system_metrics.uptime}</Badge>
                                </div>
                                <div className="flex justify-between">
                                    <span>CPU Usage:</span>
                                    <span className="font-medium">{realTimeMetrics.system_metrics.cpu_usage}%</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Disk Usage:</span>
                                    <span className="font-medium">{realTimeMetrics.system_metrics.disk_usage}%</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Error Rate:</span>
                                    <span className={`font-medium ${realTimeMetrics.system_metrics.error_rate < 5 ? 'text-green-600' : 'text-red-600'}`}>
                                        {realTimeMetrics.system_metrics.error_rate}%
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Requests/min:</span>
                                    <span className="font-medium">{realTimeMetrics.system_metrics.requests_per_minute}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Cache y Base de Datos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex justify-between">
                                    <span>Redis Memory:</span>
                                    <span className="font-medium">{realTimeMetrics.cache_stats.memory_usage}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Redis Clients:</span>
                                    <span className="font-medium">{realTimeMetrics.cache_stats.connected_clients}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Total Commands:</span>
                                    <span className="font-medium">{realTimeMetrics.cache_stats.total_commands.toLocaleString()}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>DB Connections:</span>
                                    <span className="font-medium">{realTimeMetrics.system_metrics.database_connections}</span>
                                </div>
                                <div className="flex gap-2">
                                    <Button size="sm" variant="outline" onClick={() => clearCache('dashboard')}>
                                        Limpiar Dashboard
                                    </Button>
                                    <Button size="sm" variant="outline" onClick={() => clearCache('user')}>
                                        Limpiar Usuarios
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Performance Recommendations */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recomendaciones de Optimización</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2 text-sm">
                            <div className="flex items-center gap-2">
                                <CheckCircle className="h-4 w-4 text-green-500" />
                                <span>Índices de base de datos optimizados</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <CheckCircle className="h-4 w-4 text-green-500" />
                                <span>Cache Redis configurado correctamente</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <CheckCircle className="h-4 w-4 text-green-500" />
                                <span>Monitoreo en tiempo real activo</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <RefreshCw className="h-4 w-4 text-blue-500" />
                                <span>Limpieza automática de datos antiguos programada</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}