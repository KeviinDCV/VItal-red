import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { 
    Brain, 
    TrendingUp, 
    FileText, 
    Settings,
    Target,
    Zap,
    AlertCircle,
    CheckCircle
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface AIMetrics {
    accuracy: {
        precision: number;
        recall: number;
        f1_score: number;
        accuracy: number;
    };
    learning_report: {
        current_accuracy: number;
        total_feedback_cases: number;
        recent_improvements: {
            current_month_accuracy: number;
            last_month_accuracy: number;
            improvement: number;
        };
        error_patterns: {
            age_error_rate: number;
            severity_error_rate: number;
            specialty_error_rate: number;
            symptoms_error_rate: number;
        };
        recommendations: string[];
    };
    recent_performance: {
        total_classifications_today: number;
        accuracy_today: number;
        red_cases_today: number;
        green_cases_today: number;
        feedback_received_today: number;
        algorithm_updates_this_week: number;
    };
}

interface Props {
    metrics: AIMetrics;
}

export default function AI({ metrics }: Props) {
    const getAccuracyColor = (accuracy: number) => {
        if (accuracy >= 0.95) return 'text-green-600';
        if (accuracy >= 0.90) return 'text-yellow-600';
        return 'text-red-600';
    };

    const getAccuracyBadge = (accuracy: number) => {
        if (accuracy >= 0.95) return <Badge className="bg-green-500">Excelente</Badge>;
        if (accuracy >= 0.90) return <Badge className="bg-yellow-500">Bueno</Badge>;
        return <Badge className="bg-red-500">Requiere Atención</Badge>;
    };

    return (
        <AppLayout>
            <Head title="Motor de IA Avanzado" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold flex items-center gap-2">
                            <Brain className="h-8 w-8" />
                            Motor de IA Avanzado
                        </h1>
                        <p className="text-muted-foreground">
                            Sistema de clasificación inteligente con aprendizaje continuo
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline">
                            <Settings className="h-4 w-4 mr-2" />
                            Configurar
                        </Button>
                        <Button>
                            <FileText className="h-4 w-4 mr-2" />
                            Procesar Documento
                        </Button>
                    </div>
                </div>

                {/* Accuracy Overview */}
                <Card className="border-2 border-blue-200 bg-blue-50">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-blue-700">
                            <Target className="h-5 w-5" />
                            Precisión del Algoritmo
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-4">
                            <div className="text-center">
                                <div className={`text-3xl font-bold ${getAccuracyColor(metrics.accuracy.accuracy)}`}>
                                    {(metrics.accuracy.accuracy * 100).toFixed(1)}%
                                </div>
                                <div className="text-sm text-muted-foreground">Precisión General</div>
                                {getAccuracyBadge(metrics.accuracy.accuracy)}
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-purple-600">
                                    {(metrics.accuracy.precision * 100).toFixed(1)}%
                                </div>
                                <div className="text-sm text-muted-foreground">Precisión</div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-green-600">
                                    {(metrics.accuracy.recall * 100).toFixed(1)}%
                                </div>
                                <div className="text-sm text-muted-foreground">Recall</div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-blue-600">
                                    {(metrics.accuracy.f1_score * 100).toFixed(1)}%
                                </div>
                                <div className="text-sm text-muted-foreground">F1-Score</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Performance Today */}
                <div className="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Clasificaciones Hoy</CardTitle>
                            <Zap className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">
                                {metrics.recent_performance.total_classifications_today}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Precisión Hoy</CardTitle>
                            <Target className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className={`text-2xl font-bold ${getAccuracyColor(metrics.recent_performance.accuracy_today)}`}>
                                {(metrics.recent_performance.accuracy_today * 100).toFixed(1)}%
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Casos ROJOS</CardTitle>
                            <AlertCircle className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {metrics.recent_performance.red_cases_today}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Casos VERDES</CardTitle>
                            <CheckCircle className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {metrics.recent_performance.green_cases_today}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Feedback Recibido</CardTitle>
                            <Brain className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-purple-600">
                                {metrics.recent_performance.feedback_received_today}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Actualizaciones</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-indigo-600">
                                {metrics.recent_performance.algorithm_updates_this_week}
                            </div>
                            <p className="text-xs text-muted-foreground">Esta semana</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Learning Progress */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TrendingUp className="h-5 w-5" />
                            Progreso de Aprendizaje
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <h4 className="font-medium mb-3">Mejora Mensual</h4>
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span>Mes Actual:</span>
                                        <span className="font-medium">
                                            {(metrics.learning_report.recent_improvements.current_month_accuracy * 100).toFixed(1)}%
                                        </span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span>Mes Anterior:</span>
                                        <span className="font-medium">
                                            {(metrics.learning_report.recent_improvements.last_month_accuracy * 100).toFixed(1)}%
                                        </span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span>Mejora:</span>
                                        <span className={`font-medium ${
                                            metrics.learning_report.recent_improvements.improvement > 0 
                                                ? 'text-green-600' 
                                                : 'text-red-600'
                                        }`}>
                                            {metrics.learning_report.recent_improvements.improvement > 0 ? '+' : ''}
                                            {(metrics.learning_report.recent_improvements.improvement * 100).toFixed(2)}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 className="font-medium mb-3">Casos de Entrenamiento</h4>
                                <div className="text-3xl font-bold text-blue-600">
                                    {metrics.learning_report.total_feedback_cases}
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Total de casos con feedback médico
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Error Analysis */}
                <Card>
                    <CardHeader>
                        <CardTitle>Análisis de Errores por Componente</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div>
                                <div className="flex justify-between mb-2">
                                    <span className="text-sm font-medium">Errores por Edad</span>
                                    <span className="text-sm">{(metrics.learning_report.error_patterns.age_error_rate * 100).toFixed(1)}%</span>
                                </div>
                                <Progress value={metrics.learning_report.error_patterns.age_error_rate * 100} className="h-2" />
                            </div>
                            <div>
                                <div className="flex justify-between mb-2">
                                    <span className="text-sm font-medium">Errores por Gravedad</span>
                                    <span className="text-sm">{(metrics.learning_report.error_patterns.severity_error_rate * 100).toFixed(1)}%</span>
                                </div>
                                <Progress value={metrics.learning_report.error_patterns.severity_error_rate * 100} className="h-2" />
                            </div>
                            <div>
                                <div className="flex justify-between mb-2">
                                    <span className="text-sm font-medium">Errores por Especialidad</span>
                                    <span className="text-sm">{(metrics.learning_report.error_patterns.specialty_error_rate * 100).toFixed(1)}%</span>
                                </div>
                                <Progress value={metrics.learning_report.error_patterns.specialty_error_rate * 100} className="h-2" />
                            </div>
                            <div>
                                <div className="flex justify-between mb-2">
                                    <span className="text-sm font-medium">Errores por Síntomas</span>
                                    <span className="text-sm">{(metrics.learning_report.error_patterns.symptoms_error_rate * 100).toFixed(1)}%</span>
                                </div>
                                <Progress value={metrics.learning_report.error_patterns.symptoms_error_rate * 100} className="h-2" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Recommendations */}
                {metrics.learning_report.recommendations.length > 0 && (
                    <Card className="border-yellow-200 bg-yellow-50">
                        <CardHeader>
                            <CardTitle className="text-yellow-700 flex items-center gap-2">
                                <AlertCircle className="h-5 w-5" />
                                Recomendaciones del Sistema
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="space-y-2">
                                {metrics.learning_report.recommendations.map((recommendation, index) => (
                                    <li key={index} className="flex items-start gap-2">
                                        <div className="h-2 w-2 rounded-full bg-yellow-500 mt-2" />
                                        <span className="text-sm">{recommendation}</span>
                                    </li>
                                ))}
                            </ul>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}