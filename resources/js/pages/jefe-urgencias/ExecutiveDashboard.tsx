import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
  Activity, 
  AlertTriangle, 
  Clock, 
  TrendingUp, 
  Users, 
  CheckCircle,
  XCircle,
  RefreshCw
} from 'lucide-react';

interface ExecutiveMetrics {
  solicitudes_hoy: number;
  casos_rojos_pendientes: number;
  casos_verdes_automaticos: number;
  tiempo_promedio_respuesta: number;
  eficiencia_ia: number;
  alertas_criticas: number;
  uptime_sistema: number;
  usuarios_activos: number;
  performance_score: number;
}

interface CriticalAlert {
  id: number;
  title: string;
  message: string;
  priority: string;
  time_elapsed: string;
  should_escalate: boolean;
}

export default function ExecutiveDashboard({ 
  metrics: initialMetrics, 
  alerts: initialAlerts 
}: { 
  metrics: ExecutiveMetrics;
  alerts: CriticalAlert[];
}) {
  const [metrics, setMetrics] = useState<ExecutiveMetrics>(initialMetrics);
  const [alerts, setAlerts] = useState<CriticalAlert[]>(initialAlerts);
  const [isRefreshing, setIsRefreshing] = useState(false);

  useEffect(() => {
    const interval = setInterval(async () => {
      try {
        const response = await fetch('/jefe-urgencias/metricas');
        const data = await response.json();
        setMetrics(data.metrics);
        setAlerts(data.alerts);
      } catch (error) {
        console.error('Error actualizando métricas:', error);
      }
    }, 30000); // Actualizar cada 30 segundos

    return () => clearInterval(interval);
  }, []);

  const refreshMetrics = async () => {
    setIsRefreshing(true);
    try {
      const response = await fetch('/jefe-urgencias/metricas');
      const data = await response.json();
      setMetrics(data.metrics);
      setAlerts(data.alerts);
    } catch (error) {
      console.error('Error refrescando métricas:', error);
    } finally {
      setIsRefreshing(false);
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'CRITICAL': return 'bg-red-500';
      case 'HIGH': return 'bg-orange-500';
      case 'MEDIUM': return 'bg-yellow-500';
      default: return 'bg-blue-500';
    }
  };

  return (
    <>
      <Head title="Dashboard Ejecutivo" />
      
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold">Dashboard Ejecutivo</h1>
          <Button 
            onClick={refreshMetrics} 
            disabled={isRefreshing}
            variant="outline"
          >
            <RefreshCw className={`h-4 w-4 mr-2 ${isRefreshing ? 'animate-spin' : ''}`} />
            Actualizar
          </Button>
        </div>

        {/* Métricas Principales */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Solicitudes Hoy</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{metrics.solicitudes_hoy}</div>
              <p className="text-xs text-muted-foreground">
                Objetivo: 1,000 diarias
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Casos ROJOS Pendientes</CardTitle>
              <AlertTriangle className="h-4 w-4 text-red-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">{metrics.casos_rojos_pendientes}</div>
              <p className="text-xs text-muted-foreground">
                Requieren atención inmediata
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Respuestas Automáticas</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{metrics.casos_verdes_automaticos}</div>
              <p className="text-xs text-muted-foreground">
                Objetivo: 700 diarias
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Tiempo Promedio</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{metrics.tiempo_promedio_respuesta}h</div>
              <p className="text-xs text-muted-foreground">
                Meta: &lt;2 horas
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Métricas de Performance */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <TrendingUp className="h-5 w-5 mr-2" />
                Eficiencia IA
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">{metrics.eficiencia_ia}%</div>
              <div className="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div 
                  className="bg-blue-600 h-2 rounded-full" 
                  style={{ width: `${metrics.eficiencia_ia}%` }}
                ></div>
              </div>
              <p className="text-sm text-muted-foreground mt-2">
                Objetivo: 95%
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <Users className="h-5 w-5 mr-2" />
                Sistema
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm">Uptime</span>
                  <span className="font-semibold">{metrics.uptime_sistema}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm">Usuarios Activos</span>
                  <span className="font-semibold">{metrics.usuarios_activos}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm">Score General</span>
                  <span className="font-semibold">{metrics.performance_score}/100</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <AlertTriangle className="h-5 w-5 mr-2 text-red-500" />
                Alertas Críticas
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold text-red-600">{metrics.alertas_criticas}</div>
              {metrics.alertas_criticas > 0 && (
                <Button variant="destructive" size="sm" className="mt-2 w-full">
                  Ver Alertas
                </Button>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Alertas Críticas */}
        {alerts.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle className="text-red-600">🚨 Alertas Críticas Activas</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {alerts.map((alert) => (
                  <Alert key={alert.id} className="border-red-200">
                    <AlertTriangle className="h-4 w-4" />
                    <AlertDescription>
                      <div className="flex justify-between items-start">
                        <div>
                          <div className="font-semibold">{alert.title}</div>
                          <div className="text-sm text-muted-foreground">{alert.message}</div>
                          <div className="text-xs text-muted-foreground mt-1">
                            {alert.time_elapsed}
                          </div>
                        </div>
                        <div className="flex items-center space-x-2">
                          <Badge className={getPriorityColor(alert.priority)}>
                            {alert.priority}
                          </Badge>
                          {alert.should_escalate && (
                            <Badge variant="destructive">ESCALAR</Badge>
                          )}
                        </div>
                      </div>
                    </AlertDescription>
                  </Alert>
                ))}
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </>
  );
}