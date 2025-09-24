import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Users, 
    Clock, 
    CheckCircle, 
    AlertTriangle,
    Activity,
    FileText,
    TrendingUp,
    Bell
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';

interface DashboardProps {
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
    };
    stats: {
        total?: number;
        pendientes?: number;
        completados?: number;
        misSolicitudes?: number;
    };
}

export default function Dashboard({ user, stats }: DashboardProps) {
    const getRoleDisplayName = (role: string) => {
        const roles = {
            'administrador': 'Administrador',
            'medico': 'Médico',
            'ips': 'IPS',
            'jefe_urgencias': 'Jefe de Urgencias'
        };
        return roles[role as keyof typeof roles] || role;
    };

    const getRoleColor = (role: string) => {
        const colors = {
            'administrador': 'bg-red-100 text-red-800',
            'medico': 'bg-blue-100 text-blue-800',
            'ips': 'bg-green-100 text-green-800',
            'jefe_urgencias': 'bg-purple-100 text-purple-800'
        };
        return colors[role as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    const getQuickActions = () => {
        switch (user.role) {
            case 'administrador':
                return [
                    { title: 'Gestionar Usuarios', href: '/admin/usuarios', icon: Users },
                    { title: 'Ver Reportes', href: '/admin/reportes', icon: FileText },
                    { title: 'Configurar IA', href: '/admin/configuracion-ia', icon: Activity },
                    { title: 'Dashboard Referencias', href: '/admin/referencias', icon: TrendingUp }
                ];
            case 'medico':
                return [
                    { title: 'Ingresar Registro', href: '/medico/ingresar-registro', icon: FileText },
                    { title: 'Consultar Pacientes', href: '/medico/consulta-pacientes', icon: Users },
                    { title: 'Casos Críticos', href: '/medico/casos-criticos', icon: AlertTriangle },
                    { title: 'Seguimiento', href: '/medico/seguimiento-completo', icon: Activity }
                ];
            case 'ips':
                return [
                    { title: 'Solicitar Referencia', href: '/ips/solicitar', icon: FileText },
                    { title: 'Mis Solicitudes', href: '/ips/mis-solicitudes', icon: Clock },
                ];
            case 'jefe_urgencias':
                return [
                    { title: 'Dashboard Ejecutivo', href: '/jefe-urgencias/dashboard-ejecutivo', icon: TrendingUp },
                    { title: 'Métricas', href: '/jefe-urgencias/metricas', icon: Activity },
                ];
            default:
                return [];
        }
    };

    return (
        <AppLayout>
            <Head title="Dashboard" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
                        <p className="text-muted-foreground">
                            Bienvenido, {user.name}
                        </p>
                    </div>
                    <Badge className={getRoleColor(user.role)}>
                        {getRoleDisplayName(user.role)}
                    </Badge>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                {user.role === 'ips' ? 'Mis Solicitudes' : 'Total'}
                            </CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.total || stats.misSolicitudes || 0}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pendientes</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">
                                {stats.pendientes || 0}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completados</CardTitle>
                            <CheckCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {stats.completados || 0}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Notificaciones</CardTitle>
                            <Bell className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                <Link href="/notificaciones" className="hover:underline">
                                    Ver todas
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Actions */}
                <Card>
                    <CardHeader>
                        <CardTitle>Acciones Rápidas</CardTitle>
                        <CardDescription>
                            Accede rápidamente a las funciones principales de tu rol
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            {getQuickActions().map((action, index) => {
                                const Icon = action.icon;
                                return (
                                    <Link key={index} href={action.href}>
                                        <Button 
                                            variant="outline" 
                                            className="h-20 w-full flex flex-col items-center justify-center space-y-2 hover:bg-primary/5"
                                        >
                                            <Icon className="h-6 w-6" />
                                            <span className="text-sm text-center">{action.title}</span>
                                        </Button>
                                    </Link>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>

                {/* Recent Activity */}
                <Card>
                    <CardHeader>
                        <CardTitle>Actividad Reciente</CardTitle>
                        <CardDescription>
                            Últimas acciones en el sistema
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex items-center space-x-4">
                                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                <div className="flex-1">
                                    <p className="text-sm">Sistema funcionando correctamente</p>
                                    <p className="text-xs text-muted-foreground">Hace 5 minutos</p>
                                </div>
                            </div>
                            <div className="flex items-center space-x-4">
                                <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <div className="flex-1">
                                    <p className="text-sm">Notificaciones en tiempo real activas</p>
                                    <p className="text-xs text-muted-foreground">Hace 10 minutos</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}