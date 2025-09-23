import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/react';
import { CheckCircle, XCircle } from 'lucide-react';

interface DecisionModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    solicitudId: number;
    onSuccess?: () => void;
}

export function DecisionModal({ open, onOpenChange, solicitudId, onSuccess }: DecisionModalProps) {
    const { data, setData, post, processing, reset } = useForm({
        decision: '',
        justificacion: '',
        especialista_asignado: '',
        fecha_cita: '',
        observaciones: ''
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/referencias/${solicitudId}/decidir`, {
            onSuccess: () => {
                reset();
                onOpenChange(false);
                onSuccess?.();
            }
        });
    };

    const handleClose = () => {
        reset();
        onOpenChange(false);
    };

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Procesar Solicitud</DialogTitle>
                </DialogHeader>
                
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <Label>Decisión</Label>
                        <Select value={data.decision} onValueChange={(value) => setData('decision', value)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Seleccionar decisión" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="aceptada">
                                    <div className="flex items-center">
                                        <CheckCircle className="h-4 w-4 mr-2 text-green-500" />
                                        Aceptar
                                    </div>
                                </SelectItem>
                                <SelectItem value="rechazada">
                                    <div className="flex items-center">
                                        <XCircle className="h-4 w-4 mr-2 text-red-500" />
                                        Rechazar
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    
                    <div>
                        <Label>Justificación</Label>
                        <Textarea 
                            value={data.justificacion}
                            onChange={(e) => setData('justificacion', e.target.value)}
                            placeholder="Justificación de la decisión..."
                            rows={3}
                            required
                        />
                    </div>
                    
                    {data.decision === 'aceptada' && (
                        <>
                            <div>
                                <Label>Especialista Asignado</Label>
                                <Input 
                                    value={data.especialista_asignado}
                                    onChange={(e) => setData('especialista_asignado', e.target.value)}
                                    placeholder="Nombre del especialista"
                                />
                            </div>
                            
                            <div>
                                <Label>Fecha de Cita (Opcional)</Label>
                                <Input 
                                    type="date"
                                    value={data.fecha_cita}
                                    onChange={(e) => setData('fecha_cita', e.target.value)}
                                    min={new Date().toISOString().split('T')[0]}
                                />
                            </div>
                        </>
                    )}
                    
                    <div>
                        <Label>Observaciones Adicionales</Label>
                        <Textarea 
                            value={data.observaciones}
                            onChange={(e) => setData('observaciones', e.target.value)}
                            placeholder="Observaciones adicionales..."
                            rows={2}
                        />
                    </div>
                    
                    <div className="flex gap-2 justify-end pt-4">
                        <Button type="button" variant="outline" onClick={handleClose}>
                            Cancelar
                        </Button>
                        <Button 
                            type="submit" 
                            disabled={processing || !data.decision || !data.justificacion}
                            className={data.decision === 'aceptada' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}
                        >
                            {processing ? 'Procesando...' : 'Confirmar Decisión'}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}