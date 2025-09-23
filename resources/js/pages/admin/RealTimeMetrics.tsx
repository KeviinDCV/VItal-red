import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  Activity, 
  TrendingUp, 
  TrendingDown, 
  Clock, 
  Users, 
  Server,
  Zap,
  RefreshCw
} from 'lucide-react';

interface MetricData {
  name: string;
  value: number;
  unit: string;
  trend: 'up' | 'down' | 'stable';
  change: number;
  status: 'good' | 'warning' | 'critical';
}

interface SystemHealth {
  cpu_usage: number;
  memory_usage: number;
  disk_usage: number;
  network_latency: number;
  active_connections: number;
  queue_size: number;
}

export default function RealTimeMetrics() {
  const [metrics, setMetrics] = useState<MetricData[]>([]);
  const [systemHealth, setSystemHealth] = useState<SystemHealth | null>(null);
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    // Simular conexión WebSocket para métricas en tiempo real
    const connectWebSocket = () => {
      setIsConnected(true);
      
      // Simular datos en tiempo real
      const interval = setInterval(() => {
        updateMetrics();
        updateSystemHealth();
      }, 2000);

      return () => {
        clearInterval(interval);
        setIsConnected(false);
      };
    };

    const cleanup = connectWebSocket();
    return cleanup;
  }, []);

  const updateMetrics = () => {
    const newMetrics: MetricData[] = [
      {
        name: 'Solicitudes/min',
        value: Math.floor(Math.random() * 50) + 20,
        unit: 'req/min',
        trend: Math.random() > 0.5 ? 'up' : 'down',
        change: Math.floor(Math.random() * 20) - 10,
        status: 'good'
      },
      {
        name: 'Tiempo Respuesta',
        value: Math.floor(Math.random() * 200) + 50,
        unit: 'ms',
        trend: Math.random() > 0.6 ? 'down' : 'up',
        change: Math.floor(Math.random() * 30) - 15,
        status: Math.random() > 0.8 ? 'warning' : 'good'
      },
      {
        name: 'Precisión IA',
        value: Math.floor(Math.random() * 10) + 90,
        unit: '%',
        trend: 'stable',
        change: Math.floor(Math.random() * 4) - 2,
        status: 'good'
      },
      {
        name: 'Casos Pendientes',
        value: Math.floor(Math.random() * 30) + 5,
        unit: 'casos',
        trend: Math.random() > 0.7 ? 'up' : 'down',
        change: Math.floor(Math.random() * 10) - 5,
        status: Math.random() > 0.9 ? 'critical' : 'good'
      },
      {
        name: 'Usuarios Activos',
        value: Math.floor(Math.random() * 20) + 10,
        unit: 'usuarios',
        trend: 'up',
        change: Math.floor(Math.random() * 5),
        status: 'good'
      },
      {
        name: 'Respuestas Automáticas',
        value: Math.floor(Math.random() * 100) + 50,
        unit: 'hoy',
        trend: 'up',
        change: Math.floor(Math.random() * 20) + 5,
        status: 'good'
      }
    ];
    
    setMetrics(newMetrics);
  };

  const updateSystemHealth = () => {
    setSystemHealth({
      cpu_usage: Math.floor(Math.random() * 40) + 20,
      memory_usage: Math.floor(Math.random() * 30) + 40,
      disk_usage: Math.floor(Math.random() * 20) + 60,
      network_latency: Math.floor(Math.random() * 50) + 10,
      active_connections: Math.floor(Math.random() * 100) + 50,
      queue_size: Math.floor(Math.random() * 20)
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'good': return 'text-green-600';
      case 'warning': return 'text-yellow-600';
      case 'critical': return 'text-red-600';
      default: return 'text-gray-600';
    }
  };

  const getTrendIcon = (trend: string) => {
    switch (trend) {
      case 'up': return <TrendingUp className="h-4 w-4 text-green-500" />;
      case 'down': return <TrendingDown className="h-4 w-4 text-red-500" />;
      default: return <Activity className="h-4 w-4 text-gray-500" />;
    }
  };

  const getHealthStatus = (value: number, type: string) => {
    if (type === 'cpu_usage' || type === 'memory_usage') {
      if (value > 80) return 'critical';
      if (value > 60) return 'warning';
      return 'good';
    }
    if (type === 'network_latency') {
      if (value > 100) return 'critical';
      if (value > 50) return 'warning';
      return 'good';
    }
    return 'good';
  };

  return (
    <>
      <Head title="Métricas en Tiempo Real" />
      
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold">📊 Métricas en Tiempo Real</h1>
          <div className="flex items-center space-x-2">
            <Badge variant={isConnected ? "default" : "destructive"}>
              {isConnected ? "🟢 Conectado" : "🔴 Desconectado"}
            </Badge>
            <Button onClick={() => window.location.reload()} variant="outline">
              <RefreshCw className="h-4 w-4 mr-2" />
              Actualizar
            </Button>
          </div>
        </div>

        {/* Métricas Principales */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {metrics.map((metric, index) => (
            <Card key={index}>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{metric.name}</CardTitle>
                {getTrendIcon(metric.trend)}
              </CardHeader>
              <CardContent>
                <div className={`text-2xl font-bold ${getStatusColor(metric.status)}`}>
                  {metric.value} {metric.unit}
                </div>
                <p className="text-xs text-muted-foreground">
                  {metric.change > 0 ? '+' : ''}{metric.change}% desde la última hora
                </p>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Estado del Sistema */}
        {systemHealth && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <Server className="h-5 w-5 mr-2" />
                Estado del Sistema
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm">CPU</span>
                    <span className={`text-sm font-semibold ${getStatusColor(getHealthStatus(systemHealth.cpu_usage, 'cpu_usage'))}`}>
                      {systemHealth.cpu_usage}%
                    </span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className={`h-2 rounded-full ${
                        systemHealth.cpu_usage > 80 ? 'bg-red-500' :
                        systemHealth.cpu_usage > 60 ? 'bg-yellow-500' : 'bg-green-500'
                      }`}
                      style={{ width: `${systemHealth.cpu_usage}%` }}
                    ></div>
                  </div>
                </div>

                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm">Memoria</span>
                    <span className={`text-sm font-semibold ${getStatusColor(getHealthStatus(systemHealth.memory_usage, 'memory_usage'))}`}>
                      {systemHealth.memory_usage}%
                    </span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className={`h-2 rounded-full ${
                        systemHealth.memory_usage > 80 ? 'bg-red-500' :
                        systemHealth.memory_usage > 60 ? 'bg-yellow-500' : 'bg-green-500'
                      }`}
                      style={{ width: `${systemHealth.memory_usage}%` }}
                    ></div>
                  </div>
                </div>

                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm">Disco</span>
                    <span className="text-sm font-semibold">{systemHealth.disk_usage}%</span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className="bg-blue-500 h-2 rounded-full"
                      style={{ width: `${systemHealth.disk_usage}%` }}
                    ></div>
                  </div>
                </div>

                <div className="flex items-center space-x-2">
                  <Clock className="h-4 w-4" />
                  <span className="text-sm">Latencia: {systemHealth.network_latency}ms</span>
                </div>

                <div className="flex items-center space-x-2">
                  <Users className="h-4 w-4" />
                  <span className="text-sm">Conexiones: {systemHealth.active_connections}</span>
                </div>

                <div className="flex items-center space-x-2">
                  <Zap className="h-4 w-4" />
                  <span className="text-sm">Cola: {systemHealth.queue_size} trabajos</span>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Gráfico de Tendencias (Simulado) */}
        <Card>
          <CardHeader>
            <CardTitle>Tendencias de las Últimas 24 Horas</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64 flex items-center justify-center bg-gray-50 rounded">
              <div className="text-center">
                <Activity className="h-12 w-12 text-gray-400 mx-auto mb-2" />
                <p className="text-gray-500">Gráfico de tendencias en tiempo real</p>
                <p className="text-sm text-gray-400">Integración con Chart.js pendiente</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </>
  );
}