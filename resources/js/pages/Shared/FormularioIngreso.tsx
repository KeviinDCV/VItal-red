import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { FileText, Upload, User, Stethoscope } from 'lucide-react';
import { useState } from 'react';

interface Props {
    userRole: string;
    especialidades: string[];
    ips?: Array<{id: number, nombre: string}>;
}

export default function FormularioIngreso({ userRole, especialidades, ips }: Props) {
    const [step, setStep] = useState(1);
    const [files, setFiles] = useState<File[]>([]);
    
    const { data, setData, post, processing, errors } = useForm({
        // Datos del paciente
        nombre: '',
        apellidos: '',
        numero_identificacion: '',
        fecha_nacimiento: '',
        telefono: '',
        direccion: '',
        
        // Datos médicos
        especialidad_solicitada: '',
        motivo_consulta: '',
        antecedentes: '',
        medicamentos_actuales: '',
        examenes_realizados: '',
        
        // Datos específicos por rol
        ips_origen: userRole === 'ips' ? '' : undefined,
        medico_tratante: userRole === 'medico' ? '' : undefined,
        
        // Archivos
        historia_clinica: null as File | null,
        examenes_adjuntos: [] as File[]
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const route = userRole === 'ips' ? '/ips/solicitar' : '/medico/ingresar-registro';
        post(route);
    };

    const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const uploadedFiles = Array.from(e.target.files || []);
        setFiles(prev => [...prev, ...uploadedFiles]);
        setData('examenes_adjuntos', [...data.examenes_adjuntos, ...uploadedFiles]);
    };

    const getRoleTitle = () => {
        switch (userRole) {
            case 'ips': return 'Solicitud de Referencia - IPS Externa';
            case 'medico': return 'Registro de Paciente - Médico';
            case 'centro_referencia': return 'Ingreso de Solicitud - Centro de Referencia';
            default: return 'Formulario de Ingreso';
        }
    };

    const getRoleIcon = () => {
        switch (userRole) {
            case 'ips': return <FileText className="h-6 w-6" />;
            case 'medico': return <Stethoscope className="h-6 w-6" />;
            default: return <User className="h-6 w-6" />;
        }
    };

    return (
        <>
            <Head title={getRoleTitle()} />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        {getRoleIcon()}
                        <div>
                            <h1 className="text-3xl font-bold">{getRoleTitle()}</h1>
                            <Badge variant="outline" className="mt-1">
                                Paso {step} de 3
                            </Badge>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Paso 1: Datos del Paciente */}
                    {step === 1 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="h-5 w-5" />
                                    Datos del Paciente
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label htmlFor="nombre">Nombre</Label>
                                        <Input
                                            id="nombre"
                                            value={data.nombre}
                                            onChange={(e) => setData('nombre', e.target.value)}
                                            required
                                        />
                                        {errors.nombre && <p className="text-red-500 text-sm">{errors.nombre}</p>}
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="apellidos">Apellidos</Label>
                                        <Input
                                            id="apellidos"
                                            value={data.apellidos}
                                            onChange={(e) => setData('apellidos', e.target.value)}
                                            required
                                        />
                                        {errors.apellidos && <p className="text-red-500 text-sm">{errors.apellidos}</p>}
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="numero_identificacion">Número de Identificación</Label>
                                        <Input
                                            id="numero_identificacion"
                                            value={data.numero_identificacion}
                                            onChange={(e) => setData('numero_identificacion', e.target.value)}
                                            required
                                        />
                                        {errors.numero_identificacion && <p className="text-red-500 text-sm">{errors.numero_identificacion}</p>}
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="fecha_nacimiento">Fecha de Nacimiento</Label>
                                        <Input
                                            id="fecha_nacimiento"
                                            type="date"
                                            value={data.fecha_nacimiento}
                                            onChange={(e) => setData('fecha_nacimiento', e.target.value)}
                                            required
                                        />
                                        {errors.fecha_nacimiento && <p className="text-red-500 text-sm">{errors.fecha_nacimiento}</p>}
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="telefono">Teléfono</Label>
                                        <Input
                                            id="telefono"
                                            value={data.telefono}
                                            onChange={(e) => setData('telefono', e.target.value)}
                                        />
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="direccion">Dirección</Label>
                                        <Input
                                            id="direccion"
                                            value={data.direccion}
                                            onChange={(e) => setData('direccion', e.target.value)}
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Paso 2: Información Médica */}
                    {step === 2 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Stethoscope className="h-5 w-5" />
                                    Información Médica
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="especialidad_solicitada">Especialidad Solicitada</Label>
                                    <Select value={data.especialidad_solicitada} onValueChange={(value) => setData('especialidad_solicitada', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar especialidad" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {especialidades.map((esp) => (
                                                <SelectItem key={esp} value={esp}>
                                                    {esp}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.especialidad_solicitada && <p className="text-red-500 text-sm">{errors.especialidad_solicitada}</p>}
                                </div>
                                
                                <div>
                                    <Label htmlFor="motivo_consulta">Motivo de Consulta</Label>
                                    <Textarea
                                        id="motivo_consulta"
                                        value={data.motivo_consulta}
                                        onChange={(e) => setData('motivo_consulta', e.target.value)}
                                        placeholder="Describa el motivo de la consulta..."
                                        rows={4}
                                        required
                                    />
                                    {errors.motivo_consulta && <p className="text-red-500 text-sm">{errors.motivo_consulta}</p>}
                                </div>
                                
                                <div>
                                    <Label htmlFor="antecedentes">Antecedentes Médicos</Label>
                                    <Textarea
                                        id="antecedentes"
                                        value={data.antecedentes}
                                        onChange={(e) => setData('antecedentes', e.target.value)}
                                        placeholder="Antecedentes médicos relevantes..."
                                        rows={3}
                                    />
                                </div>
                                
                                <div>
                                    <Label htmlFor="medicamentos_actuales">Medicamentos Actuales</Label>
                                    <Textarea
                                        id="medicamentos_actuales"
                                        value={data.medicamentos_actuales}
                                        onChange={(e) => setData('medicamentos_actuales', e.target.value)}
                                        placeholder="Medicamentos que toma actualmente..."
                                        rows={3}
                                    />
                                </div>

                                {userRole === 'ips' && ips && (
                                    <div>
                                        <Label htmlFor="ips_origen">IPS de Origen</Label>
                                        <Select value={data.ips_origen} onValueChange={(value) => setData('ips_origen', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Seleccionar IPS" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {ips.map((ipsItem) => (
                                                    <SelectItem key={ipsItem.id} value={ipsItem.id.toString()}>
                                                        {ipsItem.nombre}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                )}

                                {userRole === 'medico' && (
                                    <div>
                                        <Label htmlFor="medico_tratante">Médico Tratante</Label>
                                        <Input
                                            id="medico_tratante"
                                            value={data.medico_tratante}
                                            onChange={(e) => setData('medico_tratante', e.target.value)}
                                            placeholder="Nombre del médico tratante"
                                        />
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}

                    {/* Paso 3: Documentos */}
                    {step === 3 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Upload className="h-5 w-5" />
                                    Documentos y Archivos
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="examenes_realizados">Exámenes Realizados</Label>
                                    <Textarea
                                        id="examenes_realizados"
                                        value={data.examenes_realizados}
                                        onChange={(e) => setData('examenes_realizados', e.target.value)}
                                        placeholder="Describa los exámenes realizados..."
                                        rows={4}
                                    />
                                </div>
                                
                                <div>
                                    <Label htmlFor="archivos">Adjuntar Archivos</Label>
                                    <Input
                                        id="archivos"
                                        type="file"
                                        multiple
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                        onChange={handleFileUpload}
                                        className="cursor-pointer"
                                    />
                                    <p className="text-sm text-muted-foreground mt-1">
                                        Formatos permitidos: PDF, JPG, PNG, DOC, DOCX
                                    </p>
                                </div>
                                
                                {files.length > 0 && (
                                    <div>
                                        <Label>Archivos Seleccionados:</Label>
                                        <div className="space-y-2 mt-2">
                                            {files.map((file, index) => (
                                                <div key={index} className="flex items-center gap-2 p-2 bg-muted rounded">
                                                    <FileText className="h-4 w-4" />
                                                    <span className="text-sm">{file.name}</span>
                                                    <Badge variant="outline" className="ml-auto">
                                                        {(file.size / 1024 / 1024).toFixed(2)} MB
                                                    </Badge>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}

                    {/* Navegación */}
                    <div className="flex justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setStep(Math.max(1, step - 1))}
                            disabled={step === 1}
                        >
                            Anterior
                        </Button>
                        
                        {step < 3 ? (
                            <Button
                                type="button"
                                onClick={() => setStep(Math.min(3, step + 1))}
                            >
                                Siguiente
                            </Button>
                        ) : (
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Enviando...' : 'Enviar Solicitud'}
                            </Button>
                        )}
                    </div>
                </form>
            </div>
        </>
    );
}