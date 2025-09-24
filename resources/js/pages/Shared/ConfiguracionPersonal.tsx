import { Head, useForm, router } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
    User, 
    Bell, 
    Shield, 
    Palette, 
    Globe, 
    Save, 
    Eye, 
    EyeOff,
    Smartphone,
    Mail,
    Clock
} from 'lucide-react';

interface Usuario {
    id: number;
    name: string;
    email: string;
    role: string;
    telefono?: string;
    especialidad?: string;
    numero_licencia?: string;
    ips_id?: number;
}

interface ConfiguracionNotificaciones {
    email_nuevas_referencias: boolean;
    email_cambios_estado: boolean;
    email_recordatorios: boolean;
    sms_urgencias: boolean;
    sms_recordatorios: boolean;
    push_tiempo_real: boolean;
    push_alertas_criticas: boolean;
    frecuencia_resumen: 'diario' | 'semanal' | 'mensual' | 'nunca';
    horario_no_molestar_inicio: string;
    horario_no_molestar_fin: string;
}

interface ConfiguracionPrivacidad {
    mostrar_perfil_publico: boolean;
    compartir_estadisticas: boolean;
    permitir_contacto_directo: boolean;
    mostrar_estado_online: boolean;
    historial_actividad_visible: boolean;
    datos_anonimos_investigacion: boolean;
}

interface ConfiguracionInterfaz {
    tema: 'claro' | 'oscuro' | 'auto';
    idioma: 'es' | 'en';
    zona_horaria: string;
    formato_fecha: 'dd/mm/yyyy' | 'mm/dd/yyyy' | 'yyyy-mm-dd';
    formato_hora: '12h' | '24h';
    densidad_interfaz: 'compacta' | 'normal' | 'espaciosa';
    mostrar_ayuda_contextual: boolean;
}

interface Props {
    usuario: Usuario;
    configuracion_notificaciones: ConfiguracionNotificaciones;
    configuracion_privacidad: ConfiguracionPrivacidad;
    configuracion_interfaz: ConfiguracionInterfaz;
}

export default function ConfiguracionPersonal({ 
    usuario, 
    configuracion_notificaciones, 
    configuracion_privacidad, 
    configuracion_interfaz 
}: Props) {
    const [datosPersonales, setDatosPersonales] = useState({
        name: usuario.name,
        email: usuario.email,
        telefono: usuario.telefono || '',
        especialidad: usuario.especialidad || '',
        numero_licencia: usuario.numero_licencia || ''
    });

    const [notificaciones, setNotificaciones] = useState(configuracion_notificaciones);
    const [privacidad, setPrivacidad] = useState(configuracion_privacidad);
    const [interfaz, setInterfaz] = useState(configuracion_interfaz);
    
    const [cambiarPassword, setCambiarPassword] = useState({
        password_actual: '',
        password_nuevo: '',
        password_confirmacion: '',
        mostrar_actual: false,
        mostrar_nuevo: false,
        mostrar_confirmacion: false
    });

    const [guardando, setGuardando] = useState(false);

    const handleGuardarDatosPersonales = () => {
        router.post(route('configuracion-personal.datos-personales'), datosPersonales, {
            onStart: () => setGuardando(true),
            onFinish: () => setGuardando(false)
        });
    };

    const handleGuardarNotificaciones = () => {
        router.post(route('configuracion-personal.notificaciones'), notificaciones, {
            onStart: () => setGuardando(true),
            onFinish: () => setGuardando(false)
        });
    };

    const handleGuardarPrivacidad = () => {
        router.post(route('configuracion-personal.privacidad'), privacidad, {
            onStart: () => setGuardando(true),
            onFinish: () => setGuardando(false)
        });
    };

    const handleGuardarInterfaz = () => {
        router.post(route('configuracion-personal.interfaz'), interfaz, {
            onStart: () => setGuardando(true),
            onFinish: () => setGuardando(false)
        });
    };

    const handleCambiarPassword = () => {
        if (cambiarPassword.password_nuevo !== cambiarPassword.password_confirmacion) {
            alert('Las contraseñas no coinciden');
            return;
        }
        
        router.post(route('configuracion-personal.cambiar-password'), {
            current_password: cambiarPassword.password_actual,
            password: cambiarPassword.password_nuevo,
            password_confirmation: cambiarPassword.password_confirmacion
        }, {
            onStart: () => setGuardando(true),
            onFinish: () => setGuardando(false),
            onSuccess: () => {
                setCambiarPassword({
                    password_actual: '',
                    password_nuevo: '',
                    password_confirmacion: '',
                    mostrar_actual: false,
                    mostrar_nuevo: false,
                    mostrar_confirmacion: false
                });
            }
        });
    };

    return (
        <>
            <Head title="Configuración Personal" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-3xl font-bold">Configuración Personal</h1>
                </div>

                <Tabs defaultValue="perfil" className="space-y-6">
                    <TabsList className="grid w-full grid-cols-5">
                        <TabsTrigger value="perfil" className="flex items-center gap-2">
                            <User className="w-4 h-4" />
                            Perfil
                        </TabsTrigger>
                        <TabsTrigger value="notificaciones" className="flex items-center gap-2">
                            <Bell className="w-4 h-4" />
                            Notificaciones
                        </TabsTrigger>
                        <TabsTrigger value="privacidad" className="flex items-center gap-2">
                            <Shield className="w-4 h-4" />
                            Privacidad
                        </TabsTrigger>
                        <TabsTrigger value="interfaz" className="flex items-center gap-2">
                            <Palette className="w-4 h-4" />
                            Interfaz
                        </TabsTrigger>
                        <TabsTrigger value="seguridad" className="flex items-center gap-2">
                            <Shield className="w-4 h-4" />
                            Seguridad
                        </TabsTrigger>
                    </TabsList>

                    {/* Pestaña de Perfil */}
                    <TabsContent value="perfil">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="w-5 h-5" />
                                    Información Personal
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">Nombre Completo</Label>
                                        <Input
                                            id="name"
                                            value={datosPersonales.name}
                                            onChange={(e) => setDatosPersonales({...datosPersonales, name: e.target.value})}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="email">Correo Electrónico</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={datosPersonales.email}
                                            onChange={(e) => setDatosPersonales({...datosPersonales, email: e.target.value})}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="telefono">Teléfono</Label>
                                        <Input
                                            id="telefono"
                                            value={datosPersonales.telefono}
                                            onChange={(e) => setDatosPersonales({...datosPersonales, telefono: e.target.value})}
                                        />
                                    </div>

                                    {usuario.role === 'medico' && (
                                        <>
                                            <div className="space-y-2">
                                                <Label htmlFor="especialidad">Especialidad</Label>
                                                <Select 
                                                    value={datosPersonales.especialidad} 
                                                    onValueChange={(value) => setDatosPersonales({...datosPersonales, especialidad: value})}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Seleccionar especialidad" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="cardiologia">Cardiología</SelectItem>
                                                        <SelectItem value="neurologia">Neurología</SelectItem>
                                                        <SelectItem value="pediatria">Pediatría</SelectItem>
                                                        <SelectItem value="ginecologia">Ginecología</SelectItem>
                                                        <SelectItem value="medicina_interna">Medicina Interna</SelectItem>
                                                        <SelectItem value="cirugia_general">Cirugía General</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="numero_licencia">Número de Licencia Médica</Label>
                                                <Input
                                                    id="numero_licencia"
                                                    value={datosPersonales.numero_licencia}
                                                    onChange={(e) => setDatosPersonales({...datosPersonales, numero_licencia: e.target.value})}
                                                />
                                            </div>
                                        </>
                                    )}
                                </div>

                                <div className="flex justify-end">
                                    <Button onClick={handleGuardarDatosPersonales} disabled={guardando}>
                                        <Save className="w-4 h-4 mr-2" />
                                        {guardando ? 'Guardando...' : 'Guardar Cambios'}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Pestaña de Notificaciones */}
                    <TabsContent value="notificaciones">
                        <div className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Mail className="w-5 h-5" />
                                        Notificaciones por Email
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Nuevas referencias</Label>
                                            <p className="text-sm text-gray-500">Recibir email cuando lleguen nuevas referencias</p>
                                        </div>
                                        <Switch
                                            checked={notificaciones.email_nuevas_referencias}
                                            onCheckedChange={(checked) => setNotificaciones({...notificaciones, email_nuevas_referencias: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Cambios de estado</Label>
                                            <p className="text-sm text-gray-500">Notificar cuando cambien los estados de las solicitudes</p>
                                        </div>
                                        <Switch
                                            checked={notificaciones.email_cambios_estado}
                                            onCheckedChange={(checked) => setNotificaciones({...notificaciones, email_cambios_estado: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Recordatorios</Label>
                                            <p className="text-sm text-gray-500">Recordatorios de tareas pendientes</p>
                                        </div>
                                        <Switch
                                            checked={notificaciones.email_recordatorios}
                                            onCheckedChange={(checked) => setNotificaciones({...notificaciones, email_recordatorios: checked})}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Smartphone className="w-5 h-5" />
                                        Notificaciones SMS y Push
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>SMS para urgencias</Label>
                                            <p className="text-sm text-gray-500">SMS para casos críticos y urgentes</p>
                                        </div>
                                        <Switch
                                            checked={notificaciones.sms_urgencias}
                                            onCheckedChange={(checked) => setNotificaciones({...notificaciones, sms_urgencias: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Notificaciones push en tiempo real</Label>
                                            <p className="text-sm text-gray-500">Notificaciones instantáneas en el navegador</p>
                                        </div>
                                        <Switch
                                            checked={notificaciones.push_tiempo_real}
                                            onCheckedChange={(checked) => setNotificaciones({...notificaciones, push_tiempo_real: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Alertas críticas</Label>
                                            <p className="text-sm text-gray-500">Notificaciones para alertas críticas del sistema</p>
                                        </div>
                                        <Switch
                                            checked={notificaciones.push_alertas_criticas}
                                            onCheckedChange={(checked) => setNotificaciones({...notificaciones, push_alertas_criticas: checked})}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Clock className="w-5 h-5" />
                                        Configuración de Horarios
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label>Frecuencia de resumen</Label>
                                        <Select 
                                            value={notificaciones.frecuencia_resumen} 
                                            onValueChange={(value: any) => setNotificaciones({...notificaciones, frecuencia_resumen: value})}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="diario">Diario</SelectItem>
                                                <SelectItem value="semanal">Semanal</SelectItem>
                                                <SelectItem value="mensual">Mensual</SelectItem>
                                                <SelectItem value="nunca">Nunca</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label>No molestar desde</Label>
                                            <Input
                                                type="time"
                                                value={notificaciones.horario_no_molestar_inicio}
                                                onChange={(e) => setNotificaciones({...notificaciones, horario_no_molestar_inicio: e.target.value})}
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label>No molestar hasta</Label>
                                            <Input
                                                type="time"
                                                value={notificaciones.horario_no_molestar_fin}
                                                onChange={(e) => setNotificaciones({...notificaciones, horario_no_molestar_fin: e.target.value})}
                                            />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <div className="flex justify-end">
                                <Button onClick={handleGuardarNotificaciones} disabled={guardando}>
                                    <Save className="w-4 h-4 mr-2" />
                                    {guardando ? 'Guardando...' : 'Guardar Configuración'}
                                </Button>
                            </div>
                        </div>
                    </TabsContent>

                    {/* Pestaña de Privacidad */}
                    <TabsContent value="privacidad">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Shield className="w-5 h-5" />
                                    Configuración de Privacidad
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Mostrar perfil público</Label>
                                            <p className="text-sm text-gray-500">Permitir que otros usuarios vean tu información básica</p>
                                        </div>
                                        <Switch
                                            checked={privacidad.mostrar_perfil_publico}
                                            onCheckedChange={(checked) => setPrivacidad({...privacidad, mostrar_perfil_publico: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Compartir estadísticas</Label>
                                            <p className="text-sm text-gray-500">Incluir tus estadísticas en reportes generales</p>
                                        </div>
                                        <Switch
                                            checked={privacidad.compartir_estadisticas}
                                            onCheckedChange={(checked) => setPrivacidad({...privacidad, compartir_estadisticas: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Permitir contacto directo</Label>
                                            <p className="text-sm text-gray-500">Otros usuarios pueden contactarte directamente</p>
                                        </div>
                                        <Switch
                                            checked={privacidad.permitir_contacto_directo}
                                            onCheckedChange={(checked) => setPrivacidad({...privacidad, permitir_contacto_directo: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Mostrar estado online</Label>
                                            <p className="text-sm text-gray-500">Mostrar cuando estás conectado al sistema</p>
                                        </div>
                                        <Switch
                                            checked={privacidad.mostrar_estado_online}
                                            onCheckedChange={(checked) => setPrivacidad({...privacidad, mostrar_estado_online: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Historial de actividad visible</Label>
                                            <p className="text-sm text-gray-500">Permitir que se vea tu historial de actividad</p>
                                        </div>
                                        <Switch
                                            checked={privacidad.historial_actividad_visible}
                                            onCheckedChange={(checked) => setPrivacidad({...privacidad, historial_actividad_visible: checked})}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label>Datos anónimos para investigación</Label>
                                            <p className="text-sm text-gray-500">Permitir uso de datos anónimos para mejorar el sistema</p>
                                        </div>
                                        <Switch
                                            checked={privacidad.datos_anonimos_investigacion}
                                            onCheckedChange={(checked) => setPrivacidad({...privacidad, datos_anonimos_investigacion: checked})}
                                        />
                                    </div>
                                </div>

                                <div className="flex justify-end">
                                    <Button onClick={handleGuardarPrivacidad} disabled={guardando}>
                                        <Save className="w-4 h-4 mr-2" />
                                        {guardando ? 'Guardando...' : 'Guardar Configuración'}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Pestaña de Interfaz */}
                    <TabsContent value="interfaz">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Palette className="w-5 h-5" />
                                    Configuración de Interfaz
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <Label>Tema</Label>
                                        <Select 
                                            value={interfaz.tema} 
                                            onValueChange={(value: any) => setInterfaz({...interfaz, tema: value})}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="claro">Claro</SelectItem>
                                                <SelectItem value="oscuro">Oscuro</SelectItem>
                                                <SelectItem value="auto">Automático</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Idioma</Label>
                                        <Select 
                                            value={interfaz.idioma} 
                                            onValueChange={(value: any) => setInterfaz({...interfaz, idioma: value})}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="es">Español</SelectItem>
                                                <SelectItem value="en">English</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Zona Horaria</Label>
                                        <Select 
                                            value={interfaz.zona_horaria} 
                                            onValueChange={(value) => setInterfaz({...interfaz, zona_horaria: value})}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="America/Bogota">Bogotá (GMT-5)</SelectItem>
                                                <SelectItem value="America/Mexico_City">Ciudad de México (GMT-6)</SelectItem>
                                                <SelectItem value="America/New_York">Nueva York (GMT-5)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Formato de Fecha</Label>
                                        <Select 
                                            value={interfaz.formato_fecha} 
                                            onValueChange={(value: any) => setInterfaz({...interfaz, formato_fecha: value})}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="dd/mm/yyyy">DD/MM/YYYY</SelectItem>
                                                <SelectItem value="mm/dd/yyyy">MM/DD/YYYY</SelectItem>
                                                <SelectItem value="yyyy-mm-dd">YYYY-MM-DD</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Formato de Hora</Label>
                                        <Select 
                                            value={interfaz.formato_hora} 
                                            onValueChange={(value: any) => setInterfaz({...interfaz, formato_hora: value})}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="12h">12 horas (AM/PM)</SelectItem>
                                                <SelectItem value="24h">24 horas</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Densidad de Interfaz</Label>
                                        <Select 
                                            value={interfaz.densidad_interfaz} 
                                            onValueChange={(value: any) => setInterfaz({...interfaz, densidad_interfaz: value})}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="compacta">Compacta</SelectItem>
                                                <SelectItem value="normal">Normal</SelectItem>
                                                <SelectItem value="espaciosa">Espaciosa</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div className="flex items-center justify-between">
                                    <div>
                                        <Label>Mostrar ayuda contextual</Label>
                                        <p className="text-sm text-gray-500">Mostrar tooltips y ayuda en la interfaz</p>
                                    </div>
                                    <Switch
                                        checked={interfaz.mostrar_ayuda_contextual}
                                        onCheckedChange={(checked) => setInterfaz({...interfaz, mostrar_ayuda_contextual: checked})}
                                    />
                                </div>

                                <div className="flex justify-end">
                                    <Button onClick={handleGuardarInterfaz} disabled={guardando}>
                                        <Save className="w-4 h-4 mr-2" />
                                        {guardando ? 'Guardando...' : 'Guardar Configuración'}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Pestaña de Seguridad */}
                    <TabsContent value="seguridad">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Shield className="w-5 h-5" />
                                    Cambiar Contraseña
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="password_actual">Contraseña Actual</Label>
                                        <div className="relative">
                                            <Input
                                                id="password_actual"
                                                type={cambiarPassword.mostrar_actual ? "text" : "password"}
                                                value={cambiarPassword.password_actual}
                                                onChange={(e) => setCambiarPassword({...cambiarPassword, password_actual: e.target.value})}
                                            />
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                                onClick={() => setCambiarPassword({...cambiarPassword, mostrar_actual: !cambiarPassword.mostrar_actual})}
                                            >
                                                {cambiarPassword.mostrar_actual ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                            </Button>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="password_nuevo">Nueva Contraseña</Label>
                                        <div className="relative">
                                            <Input
                                                id="password_nuevo"
                                                type={cambiarPassword.mostrar_nuevo ? "text" : "password"}
                                                value={cambiarPassword.password_nuevo}
                                                onChange={(e) => setCambiarPassword({...cambiarPassword, password_nuevo: e.target.value})}
                                            />
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                                onClick={() => setCambiarPassword({...cambiarPassword, mostrar_nuevo: !cambiarPassword.mostrar_nuevo})}
                                            >
                                                {cambiarPassword.mostrar_nuevo ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                            </Button>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="password_confirmacion">Confirmar Nueva Contraseña</Label>
                                        <div className="relative">
                                            <Input
                                                id="password_confirmacion"
                                                type={cambiarPassword.mostrar_confirmacion ? "text" : "password"}
                                                value={cambiarPassword.password_confirmacion}
                                                onChange={(e) => setCambiarPassword({...cambiarPassword, password_confirmacion: e.target.value})}
                                            />
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                                onClick={() => setCambiarPassword({...cambiarPassword, mostrar_confirmacion: !cambiarPassword.mostrar_confirmacion})}
                                            >
                                                {cambiarPassword.mostrar_confirmacion ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex justify-end">
                                    <Button onClick={handleCambiarPassword} disabled={guardando}>
                                        <Save className="w-4 h-4 mr-2" />
                                        {guardando ? 'Cambiando...' : 'Cambiar Contraseña'}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </>
    );
}