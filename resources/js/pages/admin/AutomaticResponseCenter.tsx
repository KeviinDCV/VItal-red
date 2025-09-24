import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { 
  Send, 
  Plus, 
  Edit, 
  Eye, 
  TrendingUp, 
  Clock, 
  CheckCircle, 
  XCircle 
} from 'lucide-react';

interface AutomaticResponse {
  id: number;
  recipient_name: string;
  subject: string;
  status: string;
  sent_at: string;
  delivery_status: string;
  solicitud: {
    codigo_solicitud: string;
    especialidad_solicitada: string;
  };
}

interface ResponseTemplate {
  id: number;
  name: string;
  specialty: string;
  priority: string;
  subject: string;
  content: string;
  active: boolean;
  usage_count: number;
}

interface Stats {
  total_sent: number;
  sent_today: number;
  success_rate: number;
  avg_response_time: number;
}

export default function AutomaticResponseCenter({ 
  responses, 
  templates, 
  stats 
}: { 
  responses: { data: AutomaticResponse[] };
  templates: ResponseTemplate[];
  stats: Stats;
}) {
  const [isCreatingTemplate, setIsCreatingTemplate] = useState(false);
  const [selectedTemplate, setSelectedTemplate] = useState<ResponseTemplate | null>(null);
  const [templateForm, setTemplateForm] = useState({
    name: '',
    specialty: 'general',
    priority: 'VERDE',
    subject: '',
    content: '',
    variables: []
  });

  const handleCreateTemplate = async () => {
    try {
      await router.post('/admin/automatic-responses/templates', templateForm);
      setIsCreatingTemplate(false);
      setTemplateForm({
        name: '',
        specialty: 'general',
        priority: 'VERDE',
        subject: '',
        content: '',
        variables: []
      });
    } catch (error) {
      console.error('Error creando plantilla:', error);
    }
  };

  const getStatusBadge = (status: string, deliveryStatus: string) => {
    if (status === 'sent' && deliveryStatus === 'delivered') {
      return <Badge className="bg-green-500"><CheckCircle className="h-3 w-3 mr-1" />Entregado</Badge>;
    } else if (status === 'failed' || deliveryStatus === 'failed') {
      return <Badge variant="destructive"><XCircle className="h-3 w-3 mr-1" />Fallido</Badge>;
    } else {
      return <Badge variant="secondary"><Clock className="h-3 w-3 mr-1" />Pendiente</Badge>;
    }
  };

  const specialties = [
    'general', 'Cardiología', 'Neurología', 'Oncología', 
    'Pediatría', 'Ginecología', 'Ortopedia', 'Dermatología'
  ];

  return (
    <>
      <Head title="Centro de Respuestas Automáticas" />
      
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold">Centro de Respuestas Automáticas</h1>
          <Dialog open={isCreatingTemplate} onOpenChange={setIsCreatingTemplate}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="h-4 w-4 mr-2" />
                Nueva Plantilla
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle>Crear Nueva Plantilla</DialogTitle>
              </DialogHeader>
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium">Nombre</label>
                    <Input
                      value={templateForm.name}
                      onChange={(e) => setTemplateForm({...templateForm, name: e.target.value})}
                      placeholder="Nombre de la plantilla"
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium">Especialidad</label>
                    <Select 
                      value={templateForm.specialty} 
                      onValueChange={(value) => setTemplateForm({...templateForm, specialty: value})}
                    >
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        {specialties.map(specialty => (
                          <SelectItem key={specialty} value={specialty}>
                            {specialty}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                
                <div>
                  <label className="text-sm font-medium">Asunto</label>
                  <Input
                    value={templateForm.subject}
                    onChange={(e) => setTemplateForm({...templateForm, subject: e.target.value})}
                    placeholder="Asunto del email (usar {patient_name}, {specialty}, etc.)"
                  />
                </div>
                
                <div>
                  <label className="text-sm font-medium">Contenido</label>
                  <Textarea
                    value={templateForm.content}
                    onChange={(e) => setTemplateForm({...templateForm, content: e.target.value})}
                    placeholder="Contenido del email (usar variables como {patient_name}, {ips_name}, {estimated_time})"
                    rows={8}
                  />
                </div>
                
                <div className="flex justify-end space-x-2">
                  <Button variant="outline" onClick={() => setIsCreatingTemplate(false)}>
                    Cancelar
                  </Button>
                  <Button onClick={handleCreateTemplate}>
                    Crear Plantilla
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>

        {/* Estadísticas */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Enviadas</CardTitle>
              <Send className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_sent}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Enviadas Hoy</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{stats.sent_today}</div>
              <p className="text-xs text-muted-foreground">Objetivo: 700</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Tasa de Éxito</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.success_rate}%</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Tiempo Promedio</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.avg_response_time}s</div>
            </CardContent>
          </Card>
        </div>

        {/* Plantillas Activas */}
        <Card>
          <CardHeader>
            <CardTitle>Plantillas de Respuesta</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {templates.map((template) => (
                <Card key={template.id} className="border">
                  <CardHeader className="pb-2">
                    <div className="flex justify-between items-start">
                      <CardTitle className="text-sm">{template.name}</CardTitle>
                      <Badge variant={template.active ? "default" : "secondary"}>
                        {template.active ? "Activa" : "Inactiva"}
                      </Badge>
                    </div>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-2 text-sm">
                      <div><strong>Especialidad:</strong> {template.specialty}</div>
                      <div><strong>Prioridad:</strong> {template.priority}</div>
                      <div><strong>Usos:</strong> {template.usage_count}</div>
                      <div className="flex space-x-2 mt-3">
                        <Button size="sm" variant="outline">
                          <Eye className="h-3 w-3 mr-1" />
                          Ver
                        </Button>
                        <Button size="sm" variant="outline">
                          <Edit className="h-3 w-3 mr-1" />
                          Editar
                        </Button>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Historial de Respuestas */}
        <Card>
          <CardHeader>
            <CardTitle>Historial de Respuestas Automáticas</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Destinatario</TableHead>
                  <TableHead>Solicitud</TableHead>
                  <TableHead>Especialidad</TableHead>
                  <TableHead>Asunto</TableHead>
                  <TableHead>Estado</TableHead>
                  <TableHead>Enviado</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {responses.data.map((response) => (
                  <TableRow key={response.id}>
                    <TableCell>{response.recipient_name}</TableCell>
                    <TableCell>{response.solicitud.codigo_solicitud}</TableCell>
                    <TableCell>{response.solicitud.especialidad_solicitada}</TableCell>
                    <TableCell className="max-w-xs truncate">{response.subject}</TableCell>
                    <TableCell>
                      {getStatusBadge(response.status, response.delivery_status)}
                    </TableCell>
                    <TableCell>
                      {response.sent_at ? new Date(response.sent_at).toLocaleString() : '-'}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </>
  );
}