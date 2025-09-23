import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { 
    LayoutGrid, Users, Shield, FileText, Search, Activity, BarChart3, Settings, Bell, Plus,
    Brain, Monitor, Cog, Database, Zap, UserCheck, Stethoscope, ClipboardList,
    TrendingUp, AlertTriangle, Cpu, Network, HardDrive
} from 'lucide-react';
import AppLogo from './app-logo';

// Menús organizados por categorías con submenús desplegables
const getMenuByRole = (role: string): NavItem[] => {
    const baseItems = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        }
    ];

    switch (role) {
        case 'administrador':
            return [
                ...baseItems,
                {
                    title: 'Gestión de Usuarios',
                    icon: Users,
                    items: [
                        {
                            title: 'Lista de Usuarios',
                            href: '/admin/usuarios',
                            icon: Users,
                        },
                        {
                            title: 'Permisos y Roles',
                            href: '/admin/usuarios/permisos',
                            icon: UserCheck,
                        }
                    ]
                },
                {
                    title: 'Referencias Médicas',
                    icon: Activity,
                    items: [
                        {
                            title: 'Dashboard Referencias',
                            href: '/admin/referencias',
                            icon: Activity,
                        },
                        {
                            title: 'Estadísticas',
                            href: '/admin/referencias/estadisticas',
                            icon: TrendingUp,
                        },
                        {
                            title: 'Buscar Registros',
                            href: '/admin/referencias/buscar-registros',
                            icon: Search,
                        }
                    ]
                },
                {
                    title: 'Reportes y Analytics',
                    icon: BarChart3,
                    items: [
                        {
                            title: 'Reportes Completos',
                            href: '/admin/reportes/completos',
                            icon: BarChart3,
                        },
                        {
                            title: 'Analytics Avanzado',
                            href: '/admin/reportes/analytics',
                            icon: TrendingUp,
                        },
                        {
                            title: 'Exportar Datos',
                            href: '/admin/reportes/exportar',
                            icon: FileText,
                        }
                    ]
                },
                {
                    title: 'Inteligencia Artificial',
                    icon: Brain,
                    items: [
                        {
                            title: 'Dashboard IA',
                            href: '/admin/ia/dashboard',
                            icon: Brain,
                        },
                        {
                            title: 'Configurar Algoritmo',
                            href: '/admin/configuracion/ia-completo',
                            icon: Cog,
                        },
                        {
                            title: 'Respuestas Automáticas',
                            href: '/admin/ia/respuestas-automaticas',
                            icon: Cpu,
                        }
                    ]
                },
                {
                    title: 'Monitoreo y Alertas',
                    icon: Monitor,
                    items: [
                        {
                            title: 'Panel de Supervisión',
                            href: '/admin/monitoreo/supervision',
                            icon: Shield,
                        },
                        {
                            title: 'Alertas Críticas',
                            href: '/admin/monitoreo/alertas-criticas',
                            icon: AlertTriangle,
                        },
                        {
                            title: 'Métricas Tiempo Real',
                            href: '/admin/monitoreo/metricas-tiempo-real',
                            icon: Zap,
                        },
                        {
                            title: 'Performance',
                            href: '/admin/monitoreo/performance',
                            icon: TrendingUp,
                        }
                    ]
                },
                {
                    title: 'Configuración Sistema',
                    icon: Settings,
                    items: [
                        {
                            title: 'Configurar Menús',
                            href: '/admin/configuracion/menu',
                            icon: Settings,
                        },
                        {
                            title: 'Integraciones',
                            href: '/admin/integraciones',
                            icon: Network,
                        },
                        {
                            title: 'Gestión de Cache',
                            href: '/admin/sistema/cache',
                            icon: HardDrive,
                        }
                    ]
                }
            ];
        
        case 'jefe_urgencias':
            return [
                ...baseItems,
                {
                    title: 'Dashboard Ejecutivo',
                    href: '/jefe-urgencias/dashboard-ejecutivo',
                    icon: BarChart3,
                },
                {
                    title: 'Métricas y Reportes',
                    icon: TrendingUp,
                    items: [
                        {
                            title: 'Métricas en Tiempo Real',
                            href: '/jefe-urgencias/metricas',
                            icon: Zap,
                        },
                        {
                            title: 'Estado Referencias',
                            href: '/admin/referencias',
                            icon: Activity,
                        },
                        {
                            title: 'Alertas Críticas',
                            href: '/jefe-urgencias/alertas',
                            icon: AlertTriangle,
                        }
                    ]
                }
            ];
        
        case 'centro_referencia':
            return [
                ...baseItems,
                {
                    title: 'Gestión Referencias',
                    icon: Activity,
                    items: [
                        {
                            title: 'Solicitudes Pendientes',
                            href: '/medico/referencias',
                            icon: ClipboardList,
                        },
                        {
                            title: 'Casos Críticos',
                            href: '/medico/referencias/casos-criticos',
                            icon: AlertTriangle,
                        }
                    ]
                },
                {
                    title: 'Seguimiento',
                    icon: Search,
                    items: [
                        {
                            title: 'Seguimiento Pacientes',
                            href: '/medico/seguimiento/completo',
                            icon: Stethoscope,
                        },
                        {
                            title: 'Reportes Operativos',
                            href: '/admin/reportes',
                            icon: BarChart3,
                        }
                    ]
                }
            ];
        
        case 'medico':
            return [
                ...baseItems,
                {
                    title: 'Gestión de Pacientes',
                    icon: Stethoscope,
                    items: [
                        {
                            title: 'Ingresar Registro',
                            href: '/medico/pacientes/ingresar-registro',
                            icon: Plus,
                        },
                        {
                            title: 'Consultar Pacientes',
                            href: '/medico/pacientes/consulta',
                            icon: Search,
                        },
                        {
                            title: 'Buscar Pacientes',
                            href: '/medico/pacientes/buscar',
                            icon: FileText,
                        }
                    ]
                },
                {
                    title: 'Referencias',
                    icon: Activity,
                    items: [
                        {
                            title: 'Gestionar Referencias',
                            href: '/medico/referencias/gestionar',
                            icon: Activity,
                        },
                        {
                            title: 'Casos Críticos',
                            href: '/medico/referencias/casos-criticos',
                            icon: AlertTriangle,
                        }
                    ]
                },
                {
                    title: 'Seguimiento',
                    icon: ClipboardList,
                    items: [
                        {
                            title: 'Seguimiento Activo',
                            href: '/medico/seguimiento',
                            icon: Monitor,
                        },
                        {
                            title: 'Seguimiento Completo',
                            href: '/medico/seguimiento/completo',
                            icon: FileText,
                        }
                    ]
                },
                {
                    title: 'Evaluaciones',
                    icon: ClipboardList,
                    items: [
                        {
                            title: 'Mis Evaluaciones',
                            href: '/medico/evaluaciones/mis-evaluaciones',
                            icon: ClipboardList,
                        }
                    ]
                }
            ];
        
        case 'ips':
            return [
                ...baseItems,
                {
                    title: 'Solicitudes',
                    icon: FileText,
                    items: [
                        {
                            title: 'Crear Solicitud',
                            href: '/ips/solicitudes/crear',
                            icon: Plus,
                        },
                        {
                            title: 'Mis Solicitudes',
                            href: '/ips/solicitudes/mis-solicitudes',
                            icon: FileText,
                        }
                    ]
                },
                {
                    title: 'Seguimiento',
                    icon: Search,
                    items: [
                        {
                            title: 'Estado Solicitudes',
                            href: '/ips/seguimiento',
                            icon: Monitor,
                        }
                    ]
                }
            ];
        
        default:
            return baseItems;
    }
};

export function AppSidebar() {
    const { auth, menuPermisos } = usePage<SharedData>().props;
    const user = auth.user;

    // Obtener menú según rol del usuario
    const navItems = getMenuByRole(user.role);
    
    // Agregar notificaciones para todos los roles
    navItems.push({
        title: 'Notificaciones',
        href: '/notificaciones/completas',
        icon: Bell,
    });

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
