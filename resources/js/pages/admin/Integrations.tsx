import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Zap, 
    Database, 
    Image, 
    Activity,
    CheckCircle,
    XCircle,
    RefreshCw,
    Settings
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { useState } from 'react';

interface Integration {
    name: string;
    status: 'connected' | 'disconnected';
    last_sync: string;
    total_syncs: number;
    errors_today: number;
}

interface Props {
    integrations: {
        his: Integration;
        lab: Integration;
        pacs: Integration;
    };
}

export default function Integrations({ integrations }: Props) {
    const [testing, setTesting] = useState<string | null>(null);

    const getStatusIcon = (status: string) => {
        return status === 'connected' 
            ? <CheckCircle className="h-4 w-4 text-green-500" />
            : <XCircle className="h-4 w-4 text-red-500" />;
    };

    const getStatusBadge = (status: string) => {
        return status === 'connected'
            ? <Badge className="bg-green-500">Conectado</Badge>
            : <Badge className="bg-red-500">Desconectado</Badge>;
    };

    const testConnection = async (service: string) => {
        setTesting(service);
        // Simulate API call
        setTimeout(() => {
            setTesting(null);
        }, 2000);
    };

    const getServiceIcon = (service: string) => {
        switch (service) {
            case 'his': return <Database className="h-6 w-6" />;
            case 'lab': return <Activity className="h-6 w-6" />;
            case 'pacs': return <Image className="h-6 w-6" />;
            default: return <Zap className="h-6 w-6" />;
        }
    };

    return (
        <AppLayout>
            <Head title="Integraciones Externas" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold flex items-center gap-2">
                            <Zap className="h-8 w-8" />
                            Integraciones Externas
                        </h1>
                        <p className="text-muted-foreground">
                            Conexiones con sistemas hospitalarios externos
                        </p>
                    </div>
                    <Button variant="outline">
                        <Settings className="h-4 w-4 mr-2" />
                        Configurar
                    </Button>
                </div>

                {/* Integration Status Overview */}
                <div className="grid gap-4 md:grid-cols-3">
                    {Object.entries(integrations).map(([key, integration]) => (
                        <Card key={key}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium flex items-center gap-2">
                                    {getServiceIcon(key)}
                                    {integration.name}
                                </CardTitle>
                                {getStatusIcon(integration.status)}
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    {getStatusBadge(integration.status)}
                                    <div className="text-xs text-muted-foreground">
                                        Última sincronización: {new Date(integration.last_sync).toLocaleString()}
                                    </div>
                                    <div className="flex justify-between text-xs">
                                        <span>Sincronizaciones: {integration.total_syncs}</span>
                                        <span className={integration.errors_today > 0 ? 'text-red-500' : 'text-green-500'}>
                                            Errores hoy: {integration.errors_today}
                                        </span>
                                    </div>
                                    <Button 
                                        size="sm" 
                                        variant="outline" 
                                        className="w-full"
                                        onClick={() => testConnection(key)}
                                        disabled={testing === key}
                                    >
                                        <RefreshCw className={`h-4 w-4 mr-2 ${testing === key ? 'animate-spin' : ''}`} />
                                        {testing === key ? 'Probando...' : 'Probar Conexión'}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* HIS Integration Details */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Database className="h-5 w-5" />
                            Hospital Information System (HIS)
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <h4 className="font-medium mb-2">Funcionalidades</h4>
                                <ul className="text-sm space-y-1 text-muted-foreground">
                                    <li>• Sincronización de datos de pacientes</li>
                                    <li>• Historial médico completo</li>
                                    <li>• Datos demográficos automáticos</li>
                                    <li>• Sincronización en tiempo real</li>
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-medium mb-2">Estado de Conexión</h4>
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span>Endpoint:</span>
                                        <span className="text-muted-foreground">https://his-api.hospital.com</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Latencia:</span>
                                        <span className="text-green-600">45ms</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Uptime:</span>
                                        <span className="text-green-600">99.8%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Lab Integration Details */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Activity className="h-5 w-5" />
                            Sistema de Laboratorio
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <h4 className="font-medium mb-2">Funcionalidades</h4>
                                <ul className="text-sm space-y-1 text-muted-foreground">
                                    <li>• Resultados automáticos de laboratorio</li>
                                    <li>• Alertas por valores críticos</li>
                                    <li>• Adjuntar resultados a referencias</li>
                                    <li>• Notificaciones automáticas</li>
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-medium mb-2">Métricas</h4>
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span>Resultados procesados hoy:</span>
                                        <span className="text-blue-600">342</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Valores críticos detectados:</span>
                                        <span className="text-red-600">8</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Tiempo promedio respuesta:</span>
                                        <span className="text-green-600">1.2s</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* PACS Integration Details */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Image className="h-5 w-5" />
                            Picture Archiving System (PACS)
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <h4 className="font-medium mb-2">Funcionalidades</h4>
                                <ul className="text-sm space-y-1 text-muted-foreground">
                                    <li>• Visualización de imágenes médicas</li>
                                    <li>• Reportes radiológicos automáticos</li>
                                    <li>• Análisis automático de imágenes con IA</li>
                                    <li>• Integración con referencias</li>
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-medium mb-2">Estadísticas</h4>
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span>Imágenes procesadas:</span>
                                        <span className="text-blue-600">156</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Análisis IA completados:</span>
                                        <span className="text-purple-600">89</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Hallazgos críticos:</span>
                                        <span className="text-red-600">3</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Integration Logs */}
                <Card>
                    <CardHeader>
                        <CardTitle>Logs de Integración</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2 text-sm">
                            <div className="flex items-center justify-between p-2 border rounded">
                                <div className="flex items-center gap-2">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>HIS: Sincronización de paciente ID 12345 completada</span>
                                </div>
                                <span className="text-muted-foreground">Hace 2 min</span>
                            </div>
                            <div className="flex items-center justify-between p-2 border rounded">
                                <div className="flex items-center gap-2">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>LAB: Resultados críticos procesados para paciente ID 67890</span>
                                </div>
                                <span className="text-muted-foreground">Hace 5 min</span>
                            </div>
                            <div className="flex items-center justify-between p-2 border rounded">
                                <div className="flex items-center gap-2">
                                    <XCircle className="h-4 w-4 text-red-500" />
                                    <span>PACS: Error de conexión temporal - reintentando</span>
                                </div>
                                <span className="text-muted-foreground">Hace 8 min</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}