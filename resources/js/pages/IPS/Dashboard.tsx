import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    estadisticas: {
        total_solicitudes: number;
        pendientes: number;
        aceptadas: number;
        rechazadas: number;
        tiempo_promedio_respuesta: number;
    };
    solicitudesRecientes: Array<{
        id: number;
        prioridad: string;
        estado: string;
        created_at: string;
        registroMedico: {
            nombre: string;
            apellidos: string;
            especialidad_solicitada: string;
        };
    }>;
    notificaciones: Array<{
        id: number;
        titulo: string;
        mensaje: string;
        created_at: string;
        prioridad: string;
    }>;
}

export default function Dashboard({ estadisticas, solicitudesRecientes, notificaciones }: Props) {
    const [metricas, setMetricas] = useState<any>(null);

    useEffect(() => {
        // Cargar métricas adicionales
        fetch('/ips/dashboard/metricas')
            .then(res => res.json())
            .then(data => setMetricas(data))
            .catch(console.error);
    }, []);

    const getPrioridadColor = (prioridad: string) => {
        return prioridad === 'ROJO' ? 'text-danger' : 'text-success';
    };

    const getEstadoColor = (estado: string) => {
        switch (estado) {
            case 'PENDIENTE': return 'bg-warning';
            case 'ACEPTADO': return 'bg-success';
            case 'NO_ADMITIDO': return 'bg-danger';
            default: return 'bg-secondary';
        }
    };

    return (
        <AppLayout>
            <Head title="Dashboard IPS" />
            
            <div className="container-fluid py-4">
                {/* Estadísticas principales */}
                <div className="row mb-4">
                    <div className="col-md-2">
                        <div className="card bg-primary text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.total_solicitudes}</h3>
                                <p>Total Solicitudes</p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-2">
                        <div className="card bg-warning text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.pendientes}</h3>
                                <p>Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card bg-success text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.aceptadas}</h3>
                                <p>Aceptadas</p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-2">
                        <div className="card bg-danger text-white">
                            <div className="card-body text-center">
                                <h3>{estadisticas.rechazadas}</h3>
                                <p>Rechazadas</p>
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
                </div>

                <div className="row">
                    {/* Acciones Rápidas */}
                    <div className="col-md-4 mb-4">
                        <div className="card">
                            <div className="card-header">
                                <h5>Acciones Rápidas</h5>
                            </div>
                            <div className="card-body">
                                <div className="d-grid gap-2">
                                    <Link 
                                        href="/ips/solicitar" 
                                        className="btn btn-primary"
                                    >
                                        <i className="fas fa-plus me-2"></i>
                                        Nueva Solicitud
                                    </Link>
                                    <Link 
                                        href="/ips/mis-solicitudes" 
                                        className="btn btn-outline-primary"
                                    >
                                        <i className="fas fa-list me-2"></i>
                                        Ver Mis Solicitudes
                                    </Link>
                                    <Link 
                                        href="/notificaciones" 
                                        className="btn btn-outline-info"
                                    >
                                        <i className="fas fa-bell me-2"></i>
                                        Notificaciones ({notificaciones.length})
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Solicitudes Recientes */}
                    <div className="col-md-8 mb-4">
                        <div className="card">
                            <div className="card-header">
                                <h5>Solicitudes Recientes</h5>
                            </div>
                            <div className="card-body">
                                {solicitudesRecientes.length > 0 ? (
                                    <div className="table-responsive">
                                        <table className="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Paciente</th>
                                                    <th>Especialidad</th>
                                                    <th>Prioridad</th>
                                                    <th>Estado</th>
                                                    <th>Fecha</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {solicitudesRecientes.map((solicitud) => (
                                                    <tr key={solicitud.id}>
                                                        <td>
                                                            {solicitud.registroMedico.nombre} {solicitud.registroMedico.apellidos}
                                                        </td>
                                                        <td>{solicitud.registroMedico.especialidad_solicitada}</td>
                                                        <td>
                                                            <span className={`fw-bold ${getPrioridadColor(solicitud.prioridad)}`}>
                                                                {solicitud.prioridad}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span className={`badge ${getEstadoColor(solicitud.estado)}`}>
                                                                {solicitud.estado}
                                                            </span>
                                                        </td>
                                                        <td>{new Date(solicitud.created_at).toLocaleDateString()}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p className="text-muted">No hay solicitudes recientes</p>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Notificaciones */}
                    <div className="col-md-6 mb-4">
                        <div className="card">
                            <div className="card-header">
                                <h5>Notificaciones Recientes</h5>
                            </div>
                            <div className="card-body">
                                {notificaciones.length > 0 ? (
                                    <div className="list-group">
                                        {notificaciones.slice(0, 5).map((notif) => (
                                            <div key={notif.id} className="list-group-item">
                                                <div className="d-flex justify-content-between">
                                                    <div>
                                                        <h6>{notif.titulo}</h6>
                                                        <p className="mb-1 small">{notif.mensaje}</p>
                                                    </div>
                                                    <div>
                                                        <span className={`badge ${notif.prioridad === 'alta' ? 'bg-danger' : 'bg-info'}`}>
                                                            {notif.prioridad}
                                                        </span>
                                                        <br />
                                                        <small className="text-muted">
                                                            {new Date(notif.created_at).toLocaleDateString()}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-muted">No hay notificaciones</p>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Estadísticas Gráficas */}
                    <div className="col-md-6 mb-4">
                        <div className="card">
                            <div className="card-header">
                                <h5>Distribución de Estados</h5>
                            </div>
                            <div className="card-body">
                                <div className="row text-center">
                                    <div className="col-4">
                                        <div className="mb-2">
                                            <div className="progress" style={{height: '20px'}}>
                                                <div 
                                                    className="progress-bar bg-warning" 
                                                    style={{
                                                        width: `${(estadisticas.pendientes / estadisticas.total_solicitudes) * 100}%`
                                                    }}
                                                ></div>
                                            </div>
                                        </div>
                                        <small>Pendientes</small>
                                    </div>
                                    <div className="col-4">
                                        <div className="mb-2">
                                            <div className="progress" style={{height: '20px'}}>
                                                <div 
                                                    className="progress-bar bg-success" 
                                                    style={{
                                                        width: `${(estadisticas.aceptadas / estadisticas.total_solicitudes) * 100}%`
                                                    }}
                                                ></div>
                                            </div>
                                        </div>
                                        <small>Aceptadas</small>
                                    </div>
                                    <div className="col-4">
                                        <div className="mb-2">
                                            <div className="progress" style={{height: '20px'}}>
                                                <div 
                                                    className="progress-bar bg-danger" 
                                                    style={{
                                                        width: `${(estadisticas.rechazadas / estadisticas.total_solicitudes) * 100}%`
                                                    }}
                                                ></div>
                                            </div>
                                        </div>
                                        <small>Rechazadas</small>
                                    </div>
                                </div>

                                <hr />

                                <div className="text-center">
                                    <h6>Tasa de Aceptación</h6>
                                    <h3 className="text-success">
                                        {estadisticas.total_solicitudes > 0 
                                            ? ((estadisticas.aceptadas / estadisticas.total_solicitudes) * 100).toFixed(1)
                                            : 0
                                        }%
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Consejos y Ayuda */}
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h5>Consejos para Mejorar sus Solicitudes</h5>
                            </div>
                            <div className="card-body">
                                <div className="row">
                                    <div className="col-md-4">
                                        <div className="alert alert-info">
                                            <h6><i className="fas fa-lightbulb me-2"></i>Información Completa</h6>
                                            <p className="mb-0 small">
                                                Incluya toda la información médica relevante para mejorar la clasificación automática.
                                            </p>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className="alert alert-warning">
                                            <h6><i className="fas fa-clock me-2"></i>Tiempo de Respuesta</h6>
                                            <p className="mb-0 small">
                                                Las solicitudes ROJAS tienen prioridad. Tiempo promedio actual: {estadisticas.tiempo_promedio_respuesta.toFixed(1)} horas.
                                            </p>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className="alert alert-success">
                                            <h6><i className="fas fa-check me-2"></i>Seguimiento</h6>
                                            <p className="mb-0 small">
                                                Revise regularmente el estado de sus solicitudes en "Mis Solicitudes".
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}