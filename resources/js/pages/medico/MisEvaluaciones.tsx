import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface Evaluacion {
    id: number;
    decision: string;
    justificacion: string;
    created_at: string;
    solicitudReferencia: {
        id: number;
        prioridad: string;
        estado: string;
        registroMedico: {
            nombre: string;
            apellidos: string;
            especialidad_solicitada: string;
        };
        ips: {
            nombre: string;
        };
    };
}

interface Props {
    evaluaciones: {
        data: Evaluacion[];
        links: any[];
        meta: any;
    };
    estadisticas: {
        total_evaluaciones: number;
        aceptadas: number;
        rechazadas: number;
        tiempo_promedio: number;
    };
    filtros: {
        estado?: string;
        fecha_inicio?: string;
        fecha_fin?: string;
    };
}

export default function MisEvaluaciones({ evaluaciones, estadisticas, filtros }: Props) {
    const [filtrosLocal, setFiltrosLocal] = useState(filtros);

    const aplicarFiltros = () => {
        router.get('/medico/mis-evaluaciones', filtrosLocal);
    };

    const cancelarEvaluacion = (id: number) => {
        if (confirm('¿Está seguro de cancelar esta evaluación?')) {
            router.post(`/medico/cancelar-evaluacion/${id}`);
        }
    };

    return (
        <AppLayout>
            <Head title="Mis Evaluaciones" />
            
            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h3 className="card-title">Mis Evaluaciones</h3>
                            </div>
                            <div className="card-body">
                                {/* Estadísticas */}
                                <div className="row mb-4">
                                    <div className="col-md-3">
                                        <div className="card bg-primary text-white">
                                            <div className="card-body text-center">
                                                <h4>{estadisticas.total_evaluaciones}</h4>
                                                <p>Total Evaluaciones</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-3">
                                        <div className="card bg-success text-white">
                                            <div className="card-body text-center">
                                                <h4>{estadisticas.aceptadas}</h4>
                                                <p>Aceptadas</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-3">
                                        <div className="card bg-danger text-white">
                                            <div className="card-body text-center">
                                                <h4>{estadisticas.rechazadas}</h4>
                                                <p>Rechazadas</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-3">
                                        <div className="card bg-info text-white">
                                            <div className="card-body text-center">
                                                <h4>{(estadisticas.tiempo_promedio || 0).toFixed(1)}h</h4>
                                                <p>Tiempo Promedio</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Filtros */}
                                <div className="row mb-3">
                                    <div className="col-md-3">
                                        <select
                                            className="form-select"
                                            value={filtrosLocal.estado || ''}
                                            onChange={(e) => setFiltrosLocal({...filtrosLocal, estado: e.target.value})}
                                        >
                                            <option value="">Todos los estados</option>
                                            <option value="ACEPTADO">Aceptadas</option>
                                            <option value="NO_ADMITIDO">Rechazadas</option>
                                        </select>
                                    </div>
                                    <div className="col-md-3">
                                        <input
                                            type="date"
                                            className="form-control"
                                            placeholder="Fecha inicio"
                                            value={filtrosLocal.fecha_inicio || ''}
                                            onChange={(e) => setFiltrosLocal({...filtrosLocal, fecha_inicio: e.target.value})}
                                        />
                                    </div>
                                    <div className="col-md-3">
                                        <input
                                            type="date"
                                            className="form-control"
                                            placeholder="Fecha fin"
                                            value={filtrosLocal.fecha_fin || ''}
                                            onChange={(e) => setFiltrosLocal({...filtrosLocal, fecha_fin: e.target.value})}
                                        />
                                    </div>
                                    <div className="col-md-3">
                                        <button className="btn btn-primary" onClick={aplicarFiltros}>
                                            Filtrar
                                        </button>
                                    </div>
                                </div>

                                {/* Tabla de evaluaciones */}
                                <div className="table-responsive">
                                    <table className="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Paciente</th>
                                                <th>Especialidad</th>
                                                <th>IPS</th>
                                                <th>Prioridad</th>
                                                <th>Decisión</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {evaluaciones.data.map((evaluacion) => (
                                                <tr key={evaluacion.id}>
                                                    <td>{new Date(evaluacion.created_at).toLocaleDateString()}</td>
                                                    <td>
                                                        {evaluacion.solicitudReferencia.registroMedico.nombre} {evaluacion.solicitudReferencia.registroMedico.apellidos}
                                                    </td>
                                                    <td>{evaluacion.solicitudReferencia.registroMedico.especialidad_solicitada}</td>
                                                    <td>{evaluacion.solicitudReferencia.ips?.nombre || 'N/A'}</td>
                                                    <td>
                                                        <span className={`badge ${evaluacion.solicitudReferencia.prioridad === 'ROJO' ? 'bg-danger' : 'bg-success'}`}>
                                                            {evaluacion.solicitudReferencia.prioridad}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span className={`badge ${evaluacion.decision === 'aceptada' ? 'bg-success' : 'bg-danger'}`}>
                                                            {evaluacion.decision}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button
                                                            className="btn btn-sm btn-outline-danger"
                                                            onClick={() => cancelarEvaluacion(evaluacion.id)}
                                                            title="Cancelar evaluación"
                                                        >
                                                            <i className="fas fa-times"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}