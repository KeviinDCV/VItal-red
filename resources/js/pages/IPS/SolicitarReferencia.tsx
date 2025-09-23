import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Progress } from '@/components/ui/progress';
import { Upload, FileText, User, Stethoscope, Send } from 'lucide-react';
import { useState } from 'react';

export default function SolicitarReferencia() {
    const [currentStep, setCurrentStep] = useState(1);
    const totalSteps = 3;

    const { data, setData, post, processing, errors } = useForm({
        paciente: {
            nombre: '',
            documento: '',
            fecha_nacimiento: '',
            telefono: ''
        },
        motivo_consulta: '',
        especialidad_solicitada: '',
        diagnostico_presuntivo: '',
        examenes_realizados: '',
        tratamiento_actual: '',
        urgencia_justificacion: '',
        archivos: []
    });

    const especialidades = [
        'Cardiología', 'Neurología', 'Oncología', 'Cirugía General',
        'Medicina Interna', 'Pediatría', 'Ginecología', 'Urología'
    ];

    const nextStep = () => currentStep < totalSteps && setCurrentStep(currentStep + 1);
    const prevStep = () => currentStep > 1 && setCurrentStep(currentStep - 1);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/ips/solicitar');
    };

    const progress = (currentStep / totalSteps) * 100;

    return (
        <>
            <Head title="Solicitar Referencia" />
            
            <div className="max-w-4xl mx-auto space-y-6">
                <div className="text-center">
                    <h1 className="text-3xl font-bold">Solicitar Referencia</h1>
                    <p className="text-muted-foreground mt-2">Complete el formulario para solicitar una referencia médica</p>
                </div>

                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-sm font-medium">Paso {currentStep} de {totalSteps}</span>
                            <span className="text-sm text-muted-foreground">{Math.round(progress)}% completado</span>
                        </div>
                        <Progress value={progress} className="w-full" />
                    </CardContent>
                </Card>

                <form onSubmit={handleSubmit}>
                    {currentStep === 1 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <User className="mr-2 h-5 w-5" />
                                    Información del Paciente
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor="nombre">Nombre Completo</Label>
                                        <Input
                                            id="nombre"
                                            value={data.paciente.nombre}
                                            onChange={(e) => setData('paciente', { ...data.paciente, nombre: e.target.value })}
                                            placeholder="Nombre completo del paciente"
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="documento">Documento</Label>
                                        <Input
                                            id="documento"
                                            value={data.paciente.documento}
                                            onChange={(e) => setData('paciente', { ...data.paciente, documento: e.target.value })}
                                            placeholder="Número de documento"
                                        />
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor="fecha_nacimiento">Fecha de Nacimiento</Label>
                                        <Input
                                            id="fecha_nacimiento"
                                            type="date"
                                            value={data.paciente.fecha_nacimiento}
                                            onChange={(e) => setData('paciente', { ...data.paciente, fecha_nacimiento: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="telefono">Teléfono</Label>
                                        <Input
                                            id="telefono"
                                            value={data.paciente.telefono}
                                            onChange={(e) => setData('paciente', { ...data.paciente, telefono: e.target.value })}
                                            placeholder="Número de contacto"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {currentStep === 2 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Stethoscope className="mr-2 h-5 w-5" />
                                    Información Clínica
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="motivo_consulta">Motivo de Consulta</Label>
                                    <Textarea
                                        id="motivo_consulta"
                                        value={data.motivo_consulta}
                                        onChange={(e) => setData('motivo_consulta', e.target.value)}
                                        placeholder="Describa el motivo principal de la consulta..."
                                        rows={3}
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <Label>Especialidad Solicitada</Label>
                                        <Select value={data.especialidad_solicitada} onValueChange={(value) => setData('especialidad_solicitada', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Seleccionar especialidad" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {especialidades.map((esp) => (
                                                    <SelectItem key={esp} value={esp}>{esp}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label htmlFor="diagnostico">Diagnóstico Presuntivo</Label>
                                        <Input
                                            id="diagnostico"
                                            value={data.diagnostico_presuntivo}
                                            onChange={(e) => setData('diagnostico_presuntivo', e.target.value)}
                                            placeholder="Diagnóstico presuntivo"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <Label htmlFor="examenes">Exámenes Realizados</Label>
                                    <Textarea
                                        id="examenes"
                                        value={data.examenes_realizados}
                                        onChange={(e) => setData('examenes_realizados', e.target.value)}
                                        placeholder="Liste los exámenes realizados..."
                                        rows={3}
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {currentStep === 3 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <FileText className="mr-2 h-5 w-5" />
                                    Justificación y Documentos
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="justificacion">Justificación de Urgencia</Label>
                                    <Textarea
                                        id="justificacion"
                                        value={data.urgencia_justificacion}
                                        onChange={(e) => setData('urgencia_justificacion', e.target.value)}
                                        placeholder="Explique por qué esta referencia es urgente..."
                                        rows={4}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="archivos">Documentos de Soporte</Label>
                                    <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                        <Upload className="mx-auto h-12 w-12 text-gray-400" />
                                        <div className="mt-4">
                                            <Label htmlFor="file-upload" className="cursor-pointer">
                                                <span className="mt-2 block text-sm font-medium">Subir archivos</span>
                                                <span className="mt-1 block text-xs text-gray-500">PDF, JPG, PNG hasta 5MB</span>
                                            </Label>
                                            <Input
                                                id="file-upload"
                                                type="file"
                                                multiple
                                                accept=".pdf,.jpg,.jpeg,.png"
                                                onChange={(e) => setData('archivos', Array.from(e.target.files || []))}
                                                className="hidden"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    <div className="flex justify-between">
                        <Button type="button" variant="outline" onClick={prevStep} disabled={currentStep === 1}>
                            Anterior
                        </Button>
                        {currentStep < totalSteps ? (
                            <Button type="button" onClick={nextStep}>Siguiente</Button>
                        ) : (
                            <Button type="submit" disabled={processing}>
                                <Send className="mr-2 h-4 w-4" />
                                {processing ? 'Enviando...' : 'Enviar Solicitud'}
                            </Button>
                        )}
                    </div>
                </form>
            </div>
        </>
    );
}