import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    solicitud: {
        id: number;
        prioridad: string;
        estado: string;
        created_at: string;
        urgencia_justificacion: string;
        puntuacion_ia?: number;
        observaciones_ia?: string;
        registroMedico: {
            nombre: string;
            apellidos: string;
            numero_identificacion: string;
            edad: number;
            especialidad_solicitada: string;
            motivo_consulta: string;
            diagnostico_principal: string;
            signos_vitales?: string;
            examenes_realizados?: string;
            tratamiento_actual?: string;
            paciente: {
                documento: string;
                telefono?: string;
                email?: string;
                eps?: string;
            };
        };
        ips: {
            nombre: string;
            telefono?: string;
            email?: string;
        };
        decision?: {
            decision: string;
            justificacion: string;
            especialista_asignado?: string;
            fecha_cita?: string;
            observaciones?: string;
            created_at: string;
            decididoPor: {
                name: string;
            };
        };
    };
}

export default function DetalleSolicitud({ solicitud }: Props) {
    const [procesando, setProcesando] = useState(false);
    const [formData, setFormData] = useState({
        decision: '',
        justificacion: '',
        especialista_asignado: '',
        fecha_cita: '',
        observaciones_medicas: '',
        recomendaciones: '',
        urgencia_real: '',
        feedback_ia: ''
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setProcesando(true);
        
        try {
            await router.post(`/medico/referencias/${solicitud.id}/procesar`, formData);
        } catch (error) {
            console.error('Error procesando solicitud:', error);
        } finally {
            setProcesando(false);
        }
    };

    const tiempoTranscurrido = () => {
        const ahora = new Date();
        const creacion = new Date(solicitud.created_at);
        const horas = Math.floor((ahora.getTime() - creacion.getTime()) / (1000 * 60 * 60));
        return horas;
    };

    return (
        <AppLayout>
            <Head title={`Solicitud ${solicitud.id}`} />
            
            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header d-flex justify-content-between align-items-center">
                                <h3>Detalle de Solicitud #{solicitud.id}</h3>
                                <div>
                                    <span className={`badge me-2 ${solicitud.prioridad === 'ROJO' ? 'bg-danger' : 'bg-success'}`}>
                                        {solicitud.prioridad}
                                    </span>
                                    <span className={`badge ${solicitud.estado === 'PENDIENTE' ? 'bg-warning' : 'bg-info'}`}>
                                        {solicitud.estado}
                                    </span>
                                </div>
                            </div>
                            <div className="card-body">
                                <div className="row">
                                    {/* Información del Paciente */}
                                    <div className="col-md-6">
                                        <h5>Información del Paciente</h5>
                                        <table className="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Nombre:</strong></td>
                                                    <td>{solicitud.registroMedico.nombre} {solicitud.registroMedico.apellidos}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Documento:</strong></td>
                                                    <td>{solicitud.registroMedico.paciente.documento}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Edad:</strong></td>
                                                    <td>{solicitud.registroMedico.edad} años</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Teléfono:</strong></td>
                                                    <td>{solicitud.registroMedico.paciente.telefono || 'No registrado'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>EPS:</strong></td>
                                                    <td>{solicitud.registroMedico.paciente.eps || 'No registrada'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* Información de la IPS */}
                                    <div className="col-md-6">
                                        <h5>Información de la IPS</h5>
                                        <table className="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Institución:</strong></td>
                                                    <td>{solicitud.ips.nombre}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Teléfono:</strong></td>
                                                    <td>{solicitud.ips.telefono || 'No registrado'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td>{solicitud.ips.email || 'No registrado'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Fecha Solicitud:</strong></td>
                                                    <td>{new Date(solicitud.created_at).toLocaleString()}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tiempo Transcurrido:</strong></td>
                                                    <td>{tiempoTranscurrido()} horas</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <hr />

                                {/* Información Médica */}
                                <div className="row">
                                    <div className="col-12">
                                        <h5>Información Médica</h5>
                                        <div className="row">
                                            <div className="col-md-6">
                                                <div className="mb-3">
                                                    <label className="form-label"><strong>Especialidad Solicitada:</strong></label>
                                                    <p>{solicitud.registroMedico.especialidad_solicitada}</p>
                                                </div>
                                                <div className="mb-3">
                                                    <label className="form-label"><strong>Motivo de Consulta:</strong></label>
                                                    <p>{solicitud.registroMedico.motivo_consulta}</p>
                                                </div>
                                                <div className="mb-3">
                                                    <label className="form-label"><strong>Diagnóstico Principal:</strong></label>
                                                    <p>{solicitud.registroMedico.diagnostico_principal}</p>
                                                </div>
                                            </div>
                                            <div className="col-md-6">
                                                {solicitud.registroMedico.signos_vitales && (
                                                    <div className="mb-3">
                                                        <label className="form-label"><strong>Signos Vitales:</strong></label>
                                                        <p>{solicitud.registroMedico.signos_vitales}</p>
                                                    </div>
                                                )}
                                                {solicitud.registroMedico.examenes_realizados && (
                                                    <div className="mb-3">
                                                        <label className="form-label"><strong>Exámenes Realizados:</strong></label>
                                                        <p>{solicitud.registroMedico.examenes_realizados}</p>
                                                    </div>
                                                )}
                                                {solicitud.registroMedico.tratamiento_actual && (
                                                    <div className="mb-3">
                                                        <label className="form-label"><strong>Tratamiento Actual:</strong></label>
                                                        <p>{solicitud.registroMedico.tratamiento_actual}</p>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                        
                                        <div className="mb-3">
                                            <label className="form-label"><strong>Justificación de Urgencia:</strong></label>
                                            <p>{solicitud.urgencia_justificacion}</p>
                                        </div>
                                    </div>
                                </div>

                                {/* Análisis de IA */}
                                {solicitud.puntuacion_ia && (
                                    <>
                                        <hr />
                                        <div className="row">
                                            <div className="col-12">
                                                <h5>Análisis de Inteligencia Artificial</h5>
                                                <div className="alert alert-info">
                                                    <div className="row">
                                                        <div className="col-md-3">
                                                            <strong>Puntuación IA:</strong> {(solicitud.puntuacion_ia * 100).toFixed(1)}%
                                                        </div>
                                                        <div className="col-md-9">
                                                            <strong>Observaciones:</strong> {solicitud.observaciones_ia}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </>
                                )}

                                {/* Decisión Previa */}
                                {solicitud.decision && (
                                    <>
                                        <hr />
                                        <div className="row">
                                            <div className="col-12">
                                                <h5>Decisión Tomada</h5>
                                                <div className={`alert ${solicitud.decision.decision === 'aceptada' ? 'alert-success' : 'alert-danger'}`}>
                                                    <div className="row">
                                                        <div className="col-md-6">
                                                            <strong>Decisión:</strong> {solicitud.decision.decision}<br />
                                                            <strong>Médico:</strong> {solicitud.decision.decididoPor.name}<br />
                                                            <strong>Fecha:</strong> {new Date(solicitud.decision.created_at).toLocaleString()}
                                                        </div>
                                                        <div className="col-md-6">
                                                            {solicitud.decision.especialista_asignado && (
                                                                <><strong>Especialista:</strong> {solicitud.decision.especialista_asignado}<br /></>
                                                            )}
                                                            {solicitud.decision.fecha_cita && (
                                                                <><strong>Fecha Cita:</strong> {new Date(solicitud.decision.fecha_cita).toLocaleDateString()}<br /></>
                                                            )}
                                                        </div>
                                                    </div>
                                                    <div className="mt-2">
                                                        <strong>Justificación:</strong> {solicitud.decision.justificacion}
                                                    </div>
                                                    {solicitud.decision.observaciones && (
                                                        <div className="mt-2">
                                                            <strong>Observaciones:</strong> {solicitud.decision.observaciones}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </>
                                )}

                                {/* Formulario de Evaluación */}
                                {solicitud.estado === 'PENDIENTE' && (
                                    <>
                                        <hr />
                                        <form onSubmit={handleSubmit}>
                                            <h5>Evaluación Médica</h5>
                                            <div className="row">
                                                <div className="col-md-6">
                                                    <div className="mb-3">
                                                        <label className="form-label">Decisión *</label>
                                                        <select
                                                            className="form-select"
                                                            value={formData.decision}
                                                            onChange={(e) => setFormData({...formData, decision: e.target.value})}
                                                            required
                                                        >
                                                            <option value="">Seleccionar decisión</option>
                                                            <option value="aceptada">Aceptar</option>
                                                            <option value="rechazada">Rechazar</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div className="mb-3">
                                                        <label className="form-label">Urgencia Real *</label>
                                                        <select
                                                            className="form-select"
                                                            value={formData.urgencia_real}
                                                            onChange={(e) => setFormData({...formData, urgencia_real: e.target.value})}
                                                            required
                                                        >
                                                            <option value="">Seleccionar urgencia</option>
                                                            <option value="alta">Alta</option>
                                                            <option value="media">Media</option>
                                                            <option value="baja">Baja</option>
                                                        </select>
                                                    </div>

                                                    {formData.decision === 'aceptada' && (
                                                        <>
                                                            <div className="mb-3">
                                                                <label className="form-label">Especialista Asignado</label>
                                                                <input
                                                                    type="text"
                                                                    className="form-control"
                                                                    value={formData.especialista_asignado}
                                                                    onChange={(e) => setFormData({...formData, especialista_asignado: e.target.value})}
                                                                />
                                                            </div>
                                                            
                                                            <div className="mb-3">
                                                                <label className="form-label">Fecha de Cita</label>
                                                                <input
                                                                    type="date"
                                                                    className="form-control"
                                                                    value={formData.fecha_cita}
                                                                    onChange={(e) => setFormData({...formData, fecha_cita: e.target.value})}
                                                                    min={new Date().toISOString().split('T')[0]}
                                                                />
                                                            </div>
                                                        </>
                                                    )}
                                                </div>
                                                
                                                <div className="col-md-6">
                                                    <div className="mb-3">
                                                        <label className="form-label">Justificación *</label>
                                                        <textarea
                                                            className="form-control"
                                                            rows={4}
                                                            value={formData.justificacion}
                                                            onChange={(e) => setFormData({...formData, justificacion: e.target.value})}
                                                            required
                                                        />
                                                    </div>
                                                    
                                                    <div className="mb-3">
                                                        <label className="form-label">Observaciones Médicas</label>
                                                        <textarea
                                                            className="form-control"
                                                            rows={3}
                                                            value={formData.observaciones_medicas}
                                                            onChange={(e) => setFormData({...formData, observaciones_medicas: e.target.value})}
                                                        />
                                                    </div>

                                                    {solicitud.puntuacion_ia && (
                                                        <div className="mb-3">
                                                            <label className="form-label">Feedback del Algoritmo IA</label>
                                                            <select
                                                                className="form-select"
                                                                value={formData.feedback_ia}
                                                                onChange={(e) => setFormData({...formData, feedback_ia: e.target.value})}
                                                            >
                                                                <option value="">Sin feedback</option>
                                                                <option value="correcto">Clasificación correcta</option>
                                                                <option value="incorrecto">Clasificación incorrecta</option>
                                                            </select>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            
                                            <div className="d-flex justify-content-end">
                                                <button
                                                    type="submit"
                                                    className="btn btn-primary"
                                                    disabled={procesando}
                                                >
                                                    {procesando ? 'Procesando...' : 'Guardar Evaluación'}
                                                </button>
                                            </div>
                                        </form>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}