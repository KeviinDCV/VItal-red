import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, Users, Shield, FileText, Search, Activity, BarChart3, Settings, Bell, Plus } from 'lucide-react';
import AppLogo from './app-logo';

// Menú dinámico según rol del usuario
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
                    href: '/admin/usuarios',
                    icon: Users,
                },
                {
                    title: 'Referencias',
                    href: '/admin/referencias',
                    icon: Activity,
                },
                {
                    title: 'Reportes',
                    href: '/admin/reportes-completos',
                    icon: BarChart3,
                },
                {
                    title: 'Configurar IA',
                    href: '/admin/configurar-ia-completo',
                    icon: Settings,
                },
                {
                    title: 'Panel de Supervisión',
                    href: '/admin/supervision',
                    icon: Shield,
                }
            ];
        
        case 'jefe_urgencias':
            return [
                ...baseItems,
                {
                    title: 'Métricas Ejecutivas',
                    href: '/admin/reportes',
                    icon: BarChart3,
                },
                {
                    title: 'Estado Referencias',
                    href: '/admin/referencias',
                    icon: Activity,
                }
            ];
        
        case 'centro_referencia':
            return [
                ...baseItems,
                {
                    title: 'Gestionar Solicitudes',
                    href: '/medico/referencias',
                    icon: Activity,
                },
                {
                    title: 'Seguimiento Pacientes',
                    href: '/medico/seguimiento',
                    icon: Search,
                },
                {
                    title: 'Reportes Operativos',
                    href: '/admin/reportes',
                    icon: BarChart3,
                }
            ];
        
        case 'medico':
            return [
                ...baseItems,
                {
                    title: 'Ingresar Registro',
                    href: '/medico/ingresar-registro',
                    icon: FileText,
                },
                {
                    title: 'Consulta Pacientes',
                    href: '/medico/consulta-pacientes',
                    icon: Search,
                },
                {
                    title: 'Gestionar Referencias',
                    href: '/medico/referencias',
                    icon: Activity,
                },
                {
                    title: 'Seguimiento',
                    href: '/medico/seguimiento',
                    icon: Search,
                }
            ];
        
        case 'ips':
            return [
                ...baseItems,
                {
                    title: 'Solicitar Referencia',
                    href: '/ips/solicitar',
                    icon: Plus,
                },
                {
                    title: 'Mis Solicitudes',
                    href: '/ips/mis-solicitudes',
                    icon: FileText,
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
