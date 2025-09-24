import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { 
  Brain, 
  TrendingUp, 
  TrendingDown, 
  Target, 
  Clock,
  CheckCircle,
  XCircle
} from 'lucide-react';

interface AIDecision {
  id: number;
  classification_result: 'ROJO' | 'VERDE';
  confidence_score: number;
  processing_time_ms: number;
  created_at: string;
  is_correct?: boolean;
}

interface AIMetrics {
  total_decisions: number;
  accuracy_rate: number;
  avg_confidence: number;
  avg_processing_time: number;
  red_cases: number;
  green_cases: number;
}

interface AIDecisionTrackerProps {
  decisions?: AIDecision[];
  metrics?: AIMetrics;
}

export default function AIDecisionTracker({ 
  decisions = [], 
  metrics 
}: AIDecisionTrackerProps) {
  const [recentDecisions, setRecentDecisions] = useState<AIDecision[]>(decisions);
  const [aiMetrics, setAiMetrics] = useState<AIMetrics>(metrics || {
    total_decisions: 0,
    accuracy_rate: 0,
    avg_confidence: 0,
    avg_processing_time: 0,
    red_cases: 0,
    green_cases: 0
  });

  useEffect(() => {
    // Simular actualizaciones en tiempo real
    const interval = setInterval(() => {
      // Simular nueva decisión IA
      const newDecision: AIDecision = {
        id: Date.now(),
        classification_result: Math.random() > 0.3 ? 'VERDE' : 'ROJO',
        confidence_score: Math.random() * 0.4 + 0.6, // 0.6 - 1.0
        processing_time_ms: Math.floor(Math.random() * 3000) + 1000,
        created_at: new Date().toISOString(),
        is_correct: Math.random() > 0.1 // 90% accuracy simulation
      };

      setRecentDecisions(prev => [newDecision, ...prev.slice(0, 9)]);
      
      // Actualizar métricas
      setAiMetrics(prev => ({
        total_decisions: prev.total_decisions + 1,
        accuracy_rate: Math.min(95, prev.accuracy_rate + (Math.random() - 0.5)),
        avg_confidence: (prev.avg_confidence + newDecision.confidence_score) / 2,
        avg_processing_time: (prev.avg_processing_time + newDecision.processing_time_ms) / 2,
        red_cases: prev.red_cases + (newDecision.classification_result === 'ROJO' ? 1 : 0),
        green_cases: prev.green_cases + (newDecision.classification_result === 'VERDE' ? 1 : 0)
      }));
    }, 10000); // Cada 10 segundos

    return () => clearInterval(interval);
  }, []);

  const getConfidenceColor = (confidence: number) => {
    if (confidence >= 0.8) return 'text-green-600';
    if (confidence >= 0.6) return 'text-yellow-600';
    return 'text-red-600';
  };

  const getAccuracyStatus = (accuracy: number) => {
    if (accuracy >= 95) return { color: 'text-green-600', icon: TrendingUp };
    if (accuracy >= 85) return { color: 'text-yellow-600', icon: Target };
    return { color: 'text-red-600', icon: TrendingDown };
  };

  const accuracyStatus = getAccuracyStatus(aiMetrics.accuracy_rate);
  const AccuracyIcon = accuracyStatus.icon;

  return (
    <div className="space-y-6">
      {/* Métricas Principales */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Precisión IA</CardTitle>
            <AccuracyIcon className={`h-4 w-4 ${accuracyStatus.color}`} />
          </CardHeader>
          <CardContent>
            <div className={`text-2xl font-bold ${accuracyStatus.color}`}>
              {aiMetrics.accuracy_rate.toFixed(1)}%
            </div>
            <Progress value={aiMetrics.accuracy_rate} className="mt-2" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Confianza Promedio</CardTitle>
            <Brain className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className={`text-2xl font-bold ${getConfidenceColor(aiMetrics.avg_confidence)}`}>
              {(aiMetrics.avg_confidence * 100).toFixed(1)}%
            </div>
            <Progress value={aiMetrics.avg_confidence * 100} className="mt-2" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Tiempo Procesamiento</CardTitle>
            <Clock className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {(aiMetrics.avg_processing_time / 1000).toFixed(1)}s
            </div>
            <p className="text-xs text-muted-foreground">
              Objetivo: &lt;5s
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Decisiones</CardTitle>
            <Target className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{aiMetrics.total_decisions}</div>
            <p className="text-xs text-muted-foreground">
              Hoy: {aiMetrics.red_cases + aiMetrics.green_cases}
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Distribución de Casos */}
      <Card>
        <CardHeader>
          <CardTitle>Distribución de Clasificaciones</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 gap-4">
            <div className="text-center">
              <div className="text-3xl font-bold text-red-600">{aiMetrics.red_cases}</div>
              <div className="text-sm text-muted-foreground">Casos ROJOS</div>
              <Badge variant="destructive" className="mt-1">Críticos</Badge>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-green-600">{aiMetrics.green_cases}</div>
              <div className="text-sm text-muted-foreground">Casos VERDES</div>
              <Badge variant="default" className="mt-1 bg-green-500">Normales</Badge>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Decisiones Recientes */}
      <Card>
        <CardHeader>
          <CardTitle>Decisiones Recientes de IA</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {recentDecisions.slice(0, 5).map((decision) => (
              <div key={decision.id} className="flex items-center justify-between p-3 border rounded">
                <div className="flex items-center space-x-3">
                  <Badge 
                    variant={decision.classification_result === 'ROJO' ? 'destructive' : 'default'}
                    className={decision.classification_result === 'VERDE' ? 'bg-green-500' : ''}
                  >
                    {decision.classification_result}
                  </Badge>
                  <div>
                    <div className={`font-semibold ${getConfidenceColor(decision.confidence_score)}`}>
                      Confianza: {(decision.confidence_score * 100).toFixed(1)}%
                    </div>
                    <div className="text-sm text-muted-foreground">
                      Procesado en {decision.processing_time_ms}ms
                    </div>
                  </div>
                </div>
                <div className="flex items-center space-x-2">
                  {decision.is_correct !== undefined && (
                    decision.is_correct ? 
                      <CheckCircle className="h-4 w-4 text-green-500" /> :
                      <XCircle className="h-4 w-4 text-red-500" />
                  )}
                  <span className="text-xs text-muted-foreground">
                    {new Date(decision.created_at).toLocaleTimeString()}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}