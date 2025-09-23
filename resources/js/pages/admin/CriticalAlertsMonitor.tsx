import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
  AlertTriangle, 
  Clock, 
  CheckCircle, 
  XCircle, 
  User,
  RefreshCw
} from 'lucide-react';

interface CriticalAlert {
  id: number;
  title: string;
  message: string;
  priority: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  status: 'pending' | 'acknowledged' | 'resolved';
  created_at: string;
  time_elapsed: string;
  assigned_to?: string;
  should_escalate: boolean;
}

export default function CriticalAlertsMonitor({ 
  alerts: initialAlerts 
}: { 
  alerts: CriticalAlert[];
}) {
  const [alerts, setAlerts] = useState<CriticalAlert[]>(initialAlerts);
  const [filter, setFilter] = useState<string>('all');

  useEffect(() => {
    const interval = setInterval(async () => {
      try {
        const response = await fetch('/admin/alertas-criticas/data');
        const data = await response.json();
        setAlerts(data.alerts);
      } catch (error) {
        console.error('Error actualizando alertas:', error);
      }
    }, 10000); // Actualizar cada 10 segundos

    return () => clearInterval(interval);
  }, []);

  const acknowledgeAlert = async (alertId: number) => {
    try {
      await fetch(`/admin/alertas-criticas/${alertId}/acknowledge`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      setAlerts(alerts.map(alert => 
        alert.id === alertId 
          ? { ...alert, status: 'acknowledged' }
          : alert
      ));
    } catch (error) {
      console.error('Error acknowledging alert:', error);
    }
  };

  const resolveAlert = async (alertId: number) => {
    try {
      await fetch(`/admin/alertas-criticas/${alertId}/resolve`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      setAlerts(alerts.map(alert => 
        alert.id === alertId 
          ? { ...alert, status: 'resolved' }
          : alert
      ));
    } catch (error) {
      console.error('Error resolving alert:', error);
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'CRITICAL': return 'bg-red-500 text-white';
      case 'HIGH': return 'bg-orange-500 text-white';
      case 'MEDIUM': return 'bg-yellow-500 text-black';
      case 'LOW': return 'bg-blue-500 text-white';
      default: return 'bg-gray-500 text-white';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'resolved': return <CheckCircle className="h-4 w-4 text-green-500" />;
      case 'acknowledged': return <Clock className="h-4 w-4 text-yellow-500" />;
      case 'pending': return <AlertTriangle className="h-4 w-4 text-red-500" />;
      default: return <XCircle className="h-4 w-4 text-gray-500" />;
    }
  };

  const filteredAlerts = alerts.filter(alert => {
    if (filter === 'all') return true;
    if (filter === 'critical') return alert.priority === 'CRITICAL';
    if (filter === 'pending') return alert.status === 'pending';
    if (filter === 'escalation') return alert.should_escalate;
    return true;
  });

  const stats = {
    total: alerts.length,
    critical: alerts.filter(a => a.priority === 'CRITICAL').length,
    pending: alerts.filter(a => a.status === 'pending').length,
    escalation: alerts.filter(a => a.should_escalate).length
  };

  return (
    <>
      <Head title="Monitor de Alertas Críticas" />
      
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold">🚨 Monitor de Alertas Críticas</h1>
          <Button onClick={() => window.location.reload()} variant="outline">
            <RefreshCw className="h-4 w-4 mr-2" />
            Actualizar
          </Button>
        </div>

        {/* Estadísticas */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold">{stats.total}</div>
              <div className="text-sm text-muted-foreground">Total Alertas</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-red-600">{stats.critical}</div>
              <div className="text-sm text-muted-foreground">Críticas</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-yellow-600">{stats.pending}</div>
              <div className="text-sm text-muted-foreground">Pendientes</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-orange-600">{stats.escalation}</div>
              <div className="text-sm text-muted-foreground">Escalamiento</div>
            </CardContent>
          </Card>
        </div>

        {/* Filtros */}
        <div className="flex space-x-2">
          <Button 
            variant={filter === 'all' ? 'default' : 'outline'}
            onClick={() => setFilter('all')}
          >
            Todas
          </Button>
          <Button 
            variant={filter === 'critical' ? 'default' : 'outline'}
            onClick={() => setFilter('critical')}
          >
            Críticas
          </Button>
          <Button 
            variant={filter === 'pending' ? 'default' : 'outline'}
            onClick={() => setFilter('pending')}
          >
            Pendientes
          </Button>
          <Button 
            variant={filter === 'escalation' ? 'default' : 'outline'}
            onClick={() => setFilter('escalation')}
          >
            Escalamiento
          </Button>
        </div>

        {/* Lista de Alertas */}
        <div className="space-y-4">
          {filteredAlerts.map((alert) => (
            <Alert key={alert.id} className={`border-l-4 ${
              alert.priority === 'CRITICAL' ? 'border-l-red-500' :
              alert.priority === 'HIGH' ? 'border-l-orange-500' :
              alert.priority === 'MEDIUM' ? 'border-l-yellow-500' :
              'border-l-blue-500'
            }`}>
              <div className="flex items-start justify-between w-full">
                <div className="flex items-start space-x-3">
                  {getStatusIcon(alert.status)}
                  <div className="flex-1">
                    <div className="flex items-center space-x-2 mb-2">
                      <h3 className="font-semibold">{alert.title}</h3>
                      <Badge className={getPriorityColor(alert.priority)}>
                        {alert.priority}
                      </Badge>
                      {alert.should_escalate && (
                        <Badge variant="destructive">ESCALAR</Badge>
                      )}
                    </div>
                    <AlertDescription>
                      <p className="mb-2">{alert.message}</p>
                      <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                        <span>⏰ {alert.time_elapsed}</span>
                        {alert.assigned_to && (
                          <span className="flex items-center">
                            <User className="h-3 w-3 mr-1" />
                            {alert.assigned_to}
                          </span>
                        )}
                      </div>
                    </AlertDescription>
                  </div>
                </div>
                
                <div className="flex space-x-2 ml-4">
                  {alert.status === 'pending' && (
                    <Button 
                      size="sm" 
                      variant="outline"
                      onClick={() => acknowledgeAlert(alert.id)}
                    >
                      Reconocer
                    </Button>
                  )}
                  {alert.status !== 'resolved' && (
                    <Button 
                      size="sm" 
                      variant="default"
                      onClick={() => resolveAlert(alert.id)}
                    >
                      Resolver
                    </Button>
                  )}
                </div>
              </div>
            </Alert>
          ))}
        </div>

        {filteredAlerts.length === 0 && (
          <Card>
            <CardContent className="p-8 text-center">
              <CheckCircle className="h-12 w-12 text-green-500 mx-auto mb-4" />
              <h3 className="text-lg font-semibold mb-2">No hay alertas críticas</h3>
              <p className="text-muted-foreground">
                {filter === 'all' 
                  ? 'No hay alertas activas en el sistema'
                  : `No hay alertas que coincidan con el filtro "${filter}"`
                }
              </p>
            </CardContent>
          </Card>
        )}
      </div>
    </>
  );
}