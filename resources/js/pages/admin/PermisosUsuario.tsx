import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Shield, Save, ArrowLeft } from 'lucide-react';

interface Usuario {
    id: number;
    name: string;
    email: string;
    role: string;
    permissions: Array<{permission: string, granted: boolean}>;
}

interface Props {
    usuario: Usuario;
    availablePermissions: Record<string, string[]>;
}

export default function PermisosUsuario({ usuario, availablePermissions }: Props) {
    const { data, setData, post, processing } = useForm({
        permissions: usuario.permissions.reduce((acc, perm) => {
            acc[perm.permission] = perm.granted;
            return acc;
        }, {} as Record<string, boolean>)
    });

    const handlePermissionChange = (permission: string, granted: boolean) => {
        setData('permissions', {
            ...data.permissions,
            [permission]: granted
        });
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/usuarios/${usuario.id}/permisos`);
    };

    const getRoleLabel = (role: string) => {
        const labels = {
            'administrador': 'Administrador',
            'jefe_urgencias': 'Jefe de Urgencias',
            'centro_referencia': 'Centro de Referencia',
            'medico': 'Médico',
            'ips': 'IPS'
        };
        return labels[role as keyof typeof labels] || role;
    };

    return (
        <>
            <Head title={`Permisos - ${usuario.name}`} />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="outline" onClick={() => window.history.back()}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Volver
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold flex items-center gap-2">
                                <Shield className="h-8 w-8" />
                                Gestión de Permisos
                            </h1>
                            <p className="text-muted-foreground">
                                {usuario.name} ({usuario.email})
                            </p>
                        </div>
                    </div>
                    <Badge variant="outline" className="text-lg px-3 py-1">
                        {getRoleLabel(usuario.role)}
                    </Badge>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {Object.entries(availablePermissions).map(([category, permissions]) => (
                        <Card key={category}>
                            <CardHeader>
                                <CardTitle className="capitalize">
                                    {category.replace('_', ' ')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 md:grid-cols-2">
                                    {permissions.map((permission) => (
                                        <div key={permission} className="flex items-center space-x-2">
                                            <Checkbox
                                                id={permission}
                                                checked={data.permissions[permission] || false}
                                                onCheckedChange={(checked) => 
                                                    handlePermissionChange(permission, !!checked)
                                                }
                                            />
                                            <label 
                                                htmlFor={permission}
                                                className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                            >
                                                {permission.replace(/\./g, ' → ').replace(/_/g, ' ')}
                                            </label>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    ))}

                    <div className="flex justify-end">
                        <Button type="submit" disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            {processing ? 'Guardando...' : 'Guardar Permisos'}
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}