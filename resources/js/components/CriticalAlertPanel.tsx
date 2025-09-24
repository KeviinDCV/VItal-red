import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertTriangle, Clock, CheckCircle } from 'lucide-react';

interface CriticalAlert {
  id: number;
  title: string;
  message: string;
  priority: 'CRITICAL' | 'HIGH' | 'MEDIUM' | 'LOW';
  status: 'pending' | 'acknowledged' | 'resolved';
  time_elapsed: string;
  should_escalate: boolean;
}

interface CriticalAlertPanelProps {
  alerts: CriticalAlert[];
  onAcknowledge?: (alertId: number) => void;
  onResolve?: (alertId: number) => void;
}

export default function CriticalAlertPanel({ 
  alerts, 
  onAcknowledge, 
  onResolve 
}: CriticalAlertPanelProps) {
  const [localAlerts, setLocalAlerts] = useState<CriticalAlert[]>(alerts);

  useEffect(() => {
    setLocalAlerts(alerts);
  }, [alerts]);

  const handleAcknowledge = async (alertId: number) => {
    if (onAcknowledge) {
      await onAcknowledge(alertId);
    }
    setLocalAlerts(prev => 
      prev.map(alert => 
        alert.id === alertId 
          ? { ...alert, status: 'acknowledged' }
          : alert
      )
    );
  };

  const handleResolve = async (alertId: number) => {
    if (onResolve) {
      await onResolve(alertId);
    }
    setLocalAlerts(prev => 
      prev.map(alert => 
        alert.id === alertId 
          ? { ...alert, status: 'resolved' }
          : alert
      )
    );
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'CRITICAL': return 'bg-red-500 text-white';
      case 'HIGH': return 'bg-orange-500 text-white';
      case 'MEDIUM': return 'bg-yellow-500 text-black';
      default: return 'bg-blue-500 text-white';
    }
  };

  const criticalAlerts = localAlerts.filter(alert => 
    alert.status === 'pending' && alert.priority === 'CRITICAL'
  );

  if (criticalAlerts.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center text-green-600">
            <CheckCircle className="h-5 w-5 mr-2" />
            Sin Alertas Críticas
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-muted-foreground">
            No hay alertas críticas pendientes en este momento.
          </p>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center text-red-600">
          <AlertTriangle className="h-5 w-5 mr-2" />
          Alertas Críticas ({criticalAlerts.length})
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-3">
          {criticalAlerts.map((alert) => (
            <Alert key={alert.id} className="border-red-200">
              <AlertTriangle className="h-4 w-4" />
              <AlertDescription>
                <div className="flex justify-between items-start">
                  <div className="flex-1">
                    <div className="flex items-center space-x-2 mb-1">
                      <h4 className="font-semibold">{alert.title}</h4>
                      <Badge className={getPriorityColor(alert.priority)}>
                        {alert.priority}
                      </Badge>
                      {alert.should_escalate && (
                        <Badge variant="destructive">ESCALAR</Badge>
                      )}
                    </div>
                    <p className="text-sm mb-2">{alert.message}</p>
                    <div className="flex items-center text-xs text-muted-foreground">
                      <Clock className="h-3 w-3 mr-1" />
                      {alert.time_elapsed}
                    </div>
                  </div>
                  <div className="flex space-x-2 ml-4">
                    {alert.status === 'pending' && (
                      <>
                        <Button 
                          size="sm" 
                          variant="outline"
                          onClick={() => handleAcknowledge(alert.id)}
                        >
                          Reconocer
                        </Button>
                        <Button 
                          size="sm" 
                          variant="default"
                          onClick={() => handleResolve(alert.id)}
                        >
                          Resolver
                        </Button>
                      </>
                    )}
                  </div>
                </div>
              </AlertDescription>
            </Alert>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}