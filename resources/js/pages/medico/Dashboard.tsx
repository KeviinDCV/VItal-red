import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface Props {
    estadisticas: {
        solicitudes_pendientes: number;
        casos_criticos: number;
        evaluaciones_hoy: number;
        tiempo_promedio_respuesta: number;
        mis_pacientes: number;
    };
    casosUrgentes: Array<{
        id: number;
        prioridad: string;
        created_at: string;
        registroMedico: {
            nombre: string;
            apellidos: string;
            especialidad_solicitada: string;
        };
        ips: {
            nombre: string;
        };
    }>;
    actividadReciente: Array<{
        id: number;
        decision: string;
        created_at: string;
        solicitudReferencia: {
            registroMedico: {
                nombre: string;
                apellidos: string;
            };
        };
    }>;
    rendimiento: {
        casos_por_dia: number;
        tasa_aceptacion: number;
        especialidades_atendidas: Array<{
            especialidad_solicitada: string;
            total: number;
        }>;
        feedback_ia: {
            total: number;
            correctos: number;
            incorrectos: number;
            precision_ia: number;
        };
    };
    notificaciones: Array<{
        id: number;
        titulo: string;
        mensaje: string;
        created_at: string;
        prioridad: string;
    }>;
}

export default function Dashboard({ estadisticas, casosUrgentes, actividadReciente, rendimiento, notificaciones }: Props) {
    const [metricas, setMetricas] = useState<any>(null);

    useEffect(() => {
        // Cargar métricas adicionales
        fetch('/medico/dashboard/metricas?periodo=30')
            .then(res => res.json())
            .then(data => setMetricas(data))
            .catch(console.error);
    }, []);

    return (
        <AppLayout>
            <Head title="Dashboard Médico" />
            
            <div className="container-fluid py-4">
                {/* Estadísticas principales */}
                <div className="row mb-4">
                    <div className="col-md-2">
                        <div className="card bg-primary text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.solicitudes_pendientes}</h3>
                                <p>Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-2">
                        <div className="card bg-danger text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.casos_criticos}</h3>
                                <p>Críticos</p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-2">
                        <div className="card bg-success text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.evaluaciones_hoy}</h3>
                                <p>Hoy</p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card bg-info text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.tiempo_promedio_respuesta.toFixed(1)}h</h3>
                                <p>Tiempo Promedio</p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card bg-warning text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.mis_pacientes}</h3>
                                <p>Mis Pacientes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row">
                    {/* Casos Urgentes */}
                    <div className="col-md-6 mb-4">
                        <div className="card">
                            <div className="card-header">
                                <h5>Casos Urgentes</h5>
                            </div>
                            <div className="card-body">
                                {casosUrgentes.length > 0 ? (
                                    <div className="list-group">
                                        {casosUrgentes.map((caso) => (
                                            <div key={caso.id} className="list-group-item">
                                                <div className="d-flex justify-content-between">
                                                    <div>
                                                        <h6>{caso.registroMedico.nombre} {caso.registroMedico.apellidos}</h6>
                                                        <small>{caso.registroMedico.especialidad_solicitada}</small>
                                                    </div>
                                                    <span className="badge bg-danger">ROJO</span>
                                                </div>
                                                <small className="text-muted">{caso.ips.nombre}</small>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-muted">No hay casos urgentes pendientes</p>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Actividad Reciente */}
                    <div className="col-md-6 mb-4">
                        <div className="card">
                            <div className="card-header">
                                <h5>Actividad Reciente</h5>
                            </div>
                            <div className="card-body">
                                {actividadReciente.length > 0 ? (
                                    <div className="list-group">
                                        {actividadReciente.map((actividad) => (
                                            <div key={actividad.id} className="list-group-item">
                                                <div className="d-flex justify-content-between">
                                                    <div>
                                                        <h6>{actividad.solicitudReferencia.registroMedico.nombre} {actividad.solicitudReferencia.registroMedico.apellidos}</h6>
                                                        <small>Decisión: {actividad.decision}</small>
                                                    </div>
                                                    <small>{new Date(actividad.created_at).toLocaleDateString()}</small>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-muted">No hay actividad reciente</p>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Rendimiento */}
                    <div className="col-md-6 mb-4">
                        <div className="card">
                            <div className="card-header">
                                <h5>Mi Rendimiento</h5>
                            </div>
                            <div className="card-body">
                                <div className="row">
                                    <div className="col-6">
                                        <div className="text-center">
                                            <h4>{rendimiento.casos_por_dia}</h4>
                                            <small>Casos Hoy</small>
                                        </div>
                                    </div>
                                    <div className="col-6">
                                        <div className="text-center">
                                            <h4>{rendimiento.tasa_aceptacion.toFixed(1)}%</h4>
                                            <small>Tasa Aceptación</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr />
                                
                                <h6>Especialidades Atendidas</h6>
                                {rendimiento.especialidades_atendidas.map((esp, index) => (
                                    <div key={index} className="d-flex justify-content-between">
                                        <span>{esp.especialidad_solicitada}</span>
                                        <span>{esp.total}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Feedback IA */}
                    <div className="col-md-6 mb-4">
                        <div className="card">
                            <div className="card-header">
                                <h5>Feedback IA</h5>
                            </div>
                            <div className="card-body">
                                <div className="row text-center">
                                    <div className="col-4">
                                        <h4>{rendimiento.feedback_ia.total}</h4>
                                        <small>Total</small>
                                    </div>
                                    <div className="col-4">
                                        <h4 className="text-success">{rendimiento.feedback_ia.correctos}</h4>
                                        <small>Correctos</small>
                                    </div>
                                    <div className="col-4">
                                        <h4 className="text-danger">{rendimiento.feedback_ia.incorrectos}</h4>
                                        <small>Incorrectos</small>
                                    </div>
                                </div>
                                
                                <hr />
                                
                                <div className="text-center">
                                    <h5>Precisión IA: {rendimiento.feedback_ia.precision_ia.toFixed(1)}%</h5>
                                    <div className="progress">
                                        <div 
                                            className="progress-bar" 
                                            style={{width: `${rendimiento.feedback_ia.precision_ia}%`}}
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Notificaciones */}
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h5>Notificaciones Recientes</h5>
                            </div>
                            <div className="card-body">
                                {notificaciones.length > 0 ? (
                                    <div className="list-group">
                                        {notificaciones.map((notif) => (
                                            <div key={notif.id} className="list-group-item">
                                                <div className="d-flex justify-content-between">
                                                    <div>
                                                        <h6>{notif.titulo}</h6>
                                                        <p className="mb-1">{notif.mensaje}</p>
                                                    </div>
                                                    <div>
                                                        <span className={`badge ${notif.prioridad === 'alta' ? 'bg-danger' : 'bg-info'}`}>
                                                            {notif.prioridad}
                                                        </span>
                                                        <br />
                                                        <small>{new Date(notif.created_at).toLocaleDateString()}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-muted">No hay notificaciones pendientes</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}