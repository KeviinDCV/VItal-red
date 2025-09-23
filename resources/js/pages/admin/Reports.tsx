import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    estadisticas: {
        total_solicitudes: number;
        pendientes: number;
        procesadas_hoy: number;
        tiempo_promedio: number;
    };
}

export default function Reports({ estadisticas }: Props) {
    const [reportData, setReportData] = useState<any>(null);
    const [periodo, setPeriodo] = useState(30);
    const [loading, setLoading] = useState(false);

    const loadReportData = async (type: string) => {
        setLoading(true);
        try {
            const response = await fetch(`/admin/reports/${type}?periodo=${periodo}`);
            const data = await response.json();
            setReportData({ ...reportData, [type]: data });
        } catch (error) {
            console.error('Error loading report data:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadReportData('medical-requests');
        loadReportData('performance');
        loadReportData('audit');
    }, [periodo]);

    return (
        <AppLayout>
            <Head title="Reportes del Sistema" />
            
            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header d-flex justify-content-between align-items-center">
                                <h3 className="card-title">Reportes del Sistema</h3>
                                <select 
                                    className="form-select w-auto"
                                    value={periodo}
                                    onChange={(e) => setPeriodo(parseInt(e.target.value))}
                                >
                                    <option value={7}>Últimos 7 días</option>
                                    <option value={30}>Últimos 30 días</option>
                                    <option value={60}>Últimos 60 días</option>
                                    <option value={90}>Últimos 90 días</option>
                                </select>
                            </div>
                            <div className="card-body">
                                {/* Estadísticas Generales */}
                                <div className="row mb-4">
                                    <div className="col-md-3">
                                        <div className="card bg-primary text-white">
                                            <div className="card-body text-center">
                                                <h3>{estadisticas.total_solicitudes}</h3>
                                                <p>Total Solicitudes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-3">
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
                                                <h3>{estadisticas.procesadas_hoy}</h3>
                                                <p>Procesadas Hoy</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-3">
                                        <div className="card bg-info text-white">
                                            <div className="card-body text-center">
                                                <h3>{estadisticas.tiempo_promedio.toFixed(1)}h</h3>
                                                <p>Tiempo Promedio</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="row">
                                    {/* Solicitudes Médicas */}
                                    <div className="col-md-6 mb-4">
                                        <div className="card">
                                            <div className="card-header">
                                                <h5>Solicitudes por Especialidad</h5>
                                            </div>
                                            <div className="card-body">
                                                {loading ? (
                                                    <div className="text-center">
                                                        <div className="spinner-border" role="status"></div>
                                                    </div>
                                                ) : reportData?.['medical-requests']?.por_especialidad ? (
                                                    <div className="table-responsive">
                                                        <table className="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Especialidad</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {reportData['medical-requests'].por_especialidad.map((item: any, index: number) => (
                                                                    <tr key={index}>
                                                                        <td>{item.especialidad_solicitada}</td>
                                                                        <td>{item.total}</td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                ) : (
                                                    <p className="text-muted">Cargando datos...</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Rendimiento */}
                                    <div className="col-md-6 mb-4">
                                        <div className="card">
                                            <div className="card-header">
                                                <h5>Solicitudes por Médico</h5>
                                            </div>
                                            <div className="card-body">
                                                {loading ? (
                                                    <div className="text-center">
                                                        <div className="spinner-border" role="status"></div>
                                                    </div>
                                                ) : reportData?.performance?.solicitudes_por_medico ? (
                                                    <div className="table-responsive">
                                                        <table className="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Médico</th>
                                                                    <th>Evaluaciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {reportData.performance.solicitudes_por_medico.map((item: any, index: number) => (
                                                                    <tr key={index}>
                                                                        <td>{item.name}</td>
                                                                        <td>{item.total}</td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                ) : (
                                                    <p className="text-muted">Cargando datos...</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Distribución por Prioridad */}
                                    <div className="col-md-6 mb-4">
                                        <div className="card">
                                            <div className="card-header">
                                                <h5>Distribución por Prioridad</h5>
                                            </div>
                                            <div className="card-body">
                                                {reportData?.['medical-requests']?.por_prioridad ? (
                                                    <div className="row text-center">
                                                        {reportData['medical-requests'].por_prioridad.map((item: any, index: number) => (
                                                            <div key={index} className="col-6">
                                                                <div className={`card ${item.prioridad === 'ROJO' ? 'bg-danger' : 'bg-success'} text-white mb-2`}>
                                                                    <div className="card-body">
                                                                        <h4>{item.total}</h4>
                                                                        <p>{item.prioridad}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                ) : (
                                                    <p className="text-muted">Cargando datos...</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Tasa de Aceptación */}
                                    <div className="col-md-6 mb-4">
                                        <div className="card">
                                            <div className="card-header">
                                                <h5>Tasa de Aceptación</h5>
                                            </div>
                                            <div className="card-body">
                                                {reportData?.['medical-requests']?.tasa_aceptacion !== undefined ? (
                                                    <div className="text-center">
                                                        <h2>{reportData['medical-requests'].tasa_aceptacion.toFixed(1)}%</h2>
                                                        <div className="progress mt-3">
                                                            <div 
                                                                className="progress-bar bg-success" 
                                                                style={{width: `${reportData['medical-requests'].tasa_aceptacion}%`}}
                                                            ></div>
                                                        </div>
                                                        <small className="text-muted">Porcentaje de solicitudes aceptadas</small>
                                                    </div>
                                                ) : (
                                                    <p className="text-muted">Cargando datos...</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Actividad por Hora */}
                                    <div className="col-12">
                                        <div className="card">
                                            <div className="card-header">
                                                <h5>Actividad por Hora del Día</h5>
                                            </div>
                                            <div className="card-body">
                                                {reportData?.audit?.actividad_por_hora ? (
                                                    <div className="table-responsive">
                                                        <table className="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    {Array.from({length: 24}, (_, i) => (
                                                                        <th key={i}>{i}:00</th>
                                                                    ))}
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    {Array.from({length: 24}, (_, i) => {
                                                                        const data = reportData.audit.actividad_por_hora.find((item: any) => item.hora === i);
                                                                        return (
                                                                            <td key={i} className="text-center">
                                                                                {data ? data.total : 0}
                                                                            </td>
                                                                        );
                                                                    })}
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                ) : (
                                                    <p className="text-muted">Cargando datos...</p>
                                                )}
                                            </div>
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