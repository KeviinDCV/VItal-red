import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface TrendData {
    volumen_solicitudes: {
        data: Array<{ fecha: string; total: number }>;
        tendencia: { slope: number; intercept: number; r_squared: number };
        crecimiento_porcentual: number;
    };
    tiempo_respuesta: {
        data: Array<{ fecha: string; tiempo_promedio: number }>;
        tendencia: { slope: number; intercept: number; r_squared: number };
        objetivo: number;
        cumplimiento: number;
    };
    especialidades_demandadas: {
        actual: Array<{ especialidad_solicitada: string; total: number }>;
        anterior: Array<{ especialidad_solicitada: string; total: number }>;
        cambios: Array<{ especialidad: string; actual: number; anterior: number; cambio_porcentual: number }>;
    };
    patrones_temporales: {
        por_dia_semana: Array<{ dia_semana: number; total: number }>;
        por_hora: Array<{ hora: number; total: number }>;
        por_mes: Array<{ mes: number; total: number }>;
    };
    predicciones: {
        proximos_30_dias: Array<{ fecha: string; prediccion: number }>;
        confianza: number;
        tendencia_general: string;
    };
}

export default function TrendsAnalysis({ tendencias, periodo }: { tendencias: TrendData; periodo: number }) {
    const [selectedPeriod, setSelectedPeriod] = useState(periodo);

    const getDayName = (dayNumber: number) => {
        const days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        return days[dayNumber - 1] || 'N/A';
    };

    const getMonthName = (monthNumber: number) => {
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return months[monthNumber - 1] || 'N/A';
    };

    const formatTrend = (slope: number) => {
        if (slope > 0.1) return { text: 'Creciente', color: 'text-success', icon: 'fa-arrow-up' };
        if (slope < -0.1) return { text: 'Decreciente', color: 'text-danger', icon: 'fa-arrow-down' };
        return { text: 'Estable', color: 'text-warning', icon: 'fa-minus' };
    };

    return (
        <AppLayout>
            <Head title="Análisis de Tendencias" />
            
            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header d-flex justify-content-between align-items-center">
                                <h3 className="card-title">Análisis de Tendencias</h3>
                                <select 
                                    className="form-select w-auto"
                                    value={selectedPeriod}
                                    onChange={(e) => setSelectedPeriod(parseInt(e.target.value))}
                                >
                                    <option value={30}>Últimos 30 días</option>
                                    <option value={60}>Últimos 60 días</option>
                                    <option value={90}>Últimos 90 días</option>
                                    <option value={180}>Últimos 6 meses</option>
                                </select>
                            </div>
                            <div className="card-body">
                                <div className="row">
                                    {/* Volumen de Solicitudes */}
                                    <div className="col-md-6 mb-4">
                                        <div className="card h-100">
                                            <div className="card-header">
                                                <h5>Volumen de Solicitudes</h5>
                                            </div>
                                            <div className="card-body">
                                                <div className="d-flex justify-content-between mb-3">
                                                    <div>
                                                        <span className="text-muted">Tendencia:</span>
                                                        <span className={`ms-2 ${formatTrend(tendencias.volumen_solicitudes.tendencia.slope).color}`}>
                                                            <i className={`fas ${formatTrend(tendencias.volumen_solicitudes.tendencia.slope).icon}`}></i>
                                                            {formatTrend(tendencias.volumen_solicitudes.tendencia.slope).text}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span className="text-muted">Crecimiento:</span>
                                                        <span className={`ms-2 ${tendencias.volumen_solicitudes.crecimiento_porcentual >= 0 ? 'text-success' : 'text-danger'}`}>
                                                            {tendencias.volumen_solicitudes.crecimiento_porcentual.toFixed(1)}%
                                                        </span>
                                                    </div>
                                                </div>
                                                <div className="table-responsive">
                                                    <table className="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Solicitudes</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {tendencias.volumen_solicitudes.data.slice(-7).map((item, index) => (
                                                                <tr key={index}>
                                                                    <td>{new Date(item.fecha).toLocaleDateString()}</td>
                                                                    <td>{item.total}</td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Tiempo de Respuesta */}
                                    <div className="col-md-6 mb-4">
                                        <div className="card h-100">
                                            <div className="card-header">
                                                <h5>Tiempo de Respuesta</h5>
                                            </div>
                                            <div className="card-body">
                                                <div className="d-flex justify-content-between mb-3">
                                                    <div>
                                                        <span className="text-muted">Objetivo:</span>
                                                        <span className="ms-2">{tendencias.tiempo_respuesta.objetivo}h</span>
                                                    </div>
                                                    <div>
                                                        <span className="text-muted">Cumplimiento:</span>
                                                        <span className={`ms-2 ${tendencias.tiempo_respuesta.cumplimiento >= 80 ? 'text-success' : 'text-warning'}`}>
                                                            {tendencias.tiempo_respuesta.cumplimiento.toFixed(1)}%
                                                        </span>
                                                    </div>
                                                </div>
                                                <div className="table-responsive">
                                                    <table className="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Tiempo Promedio</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {tendencias.tiempo_respuesta.data.slice(-7).map((item, index) => (
                                                                <tr key={index}>
                                                                    <td>{new Date(item.fecha).toLocaleDateString()}</td>
                                                                    <td>
                                                                        <span className={item.tiempo_promedio <= 24 ? 'text-success' : 'text-danger'}>
                                                                            {item.tiempo_promedio.toFixed(1)}h
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Especialidades Más Demandadas */}
                                    <div className="col-md-6 mb-4">
                                        <div className="card h-100">
                                            <div className="card-header">
                                                <h5>Especialidades Más Demandadas</h5>
                                            </div>
                                            <div className="card-body">
                                                <div className="table-responsive">
                                                    <table className="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Especialidad</th>
                                                                <th>Actual</th>
                                                                <th>Cambio</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {tendencias.especialidades_demandadas.cambios.slice(0, 5).map((item, index) => (
                                                                <tr key={index}>
                                                                    <td>{item.especialidad}</td>
                                                                    <td>{item.actual}</td>
                                                                    <td>
                                                                        <span className={`${item.cambio_porcentual >= 0 ? 'text-success' : 'text-danger'}`}>
                                                                            <i className={`fas ${item.cambio_porcentual >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'}`}></i>
                                                                            {Math.abs(item.cambio_porcentual).toFixed(1)}%
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Patrones Temporales */}
                                    <div className="col-md-6 mb-4">
                                        <div className="card h-100">
                                            <div className="card-header">
                                                <h5>Patrones Temporales</h5>
                                            </div>
                                            <div className="card-body">
                                                <div className="row">
                                                    <div className="col-12 mb-3">
                                                        <h6>Por Día de la Semana</h6>
                                                        <div className="d-flex justify-content-between">
                                                            {tendencias.patrones_temporales.por_dia_semana.map((item, index) => (
                                                                <div key={index} className="text-center">
                                                                    <div className="small text-muted">{getDayName(item.dia_semana)}</div>
                                                                    <div className="fw-bold">{item.total}</div>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    </div>
                                                    
                                                    <div className="col-12">
                                                        <h6>Horas Pico</h6>
                                                        <div className="table-responsive">
                                                            <table className="table table-sm">
                                                                <tbody>
                                                                    {tendencias.patrones_temporales.por_hora
                                                                        .sort((a, b) => b.total - a.total)
                                                                        .slice(0, 5)
                                                                        .map((item, index) => (
                                                                        <tr key={index}>
                                                                            <td>{item.hora}:00</td>
                                                                            <td>{item.total} solicitudes</td>
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

                                    {/* Predicciones */}
                                    <div className="col-12">
                                        <div className="card">
                                            <div className="card-header">
                                                <h5>Predicciones para los Próximos 30 Días</h5>
                                            </div>
                                            <div className="card-body">
                                                <div className="row mb-3">
                                                    <div className="col-md-4">
                                                        <div className="text-center">
                                                            <div className="text-muted">Tendencia General</div>
                                                            <div className={`fw-bold ${tendencias.predicciones.tendencia_general === 'creciente' ? 'text-success' : 'text-danger'}`}>
                                                                <i className={`fas ${tendencias.predicciones.tendencia_general === 'creciente' ? 'fa-arrow-up' : 'fa-arrow-down'}`}></i>
                                                                {tendencias.predicciones.tendencia_general.toUpperCase()}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="col-md-4">
                                                        <div className="text-center">
                                                            <div className="text-muted">Confianza del Modelo</div>
                                                            <div className={`fw-bold ${tendencias.predicciones.confianza >= 0.7 ? 'text-success' : 'text-warning'}`}>
                                                                {(tendencias.predicciones.confianza * 100).toFixed(1)}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="col-md-4">
                                                        <div className="text-center">
                                                            <div className="text-muted">Promedio Predicho</div>
                                                            <div className="fw-bold">
                                                                {Math.round(tendencias.predicciones.proximos_30_dias.reduce((sum, item) => sum + item.prediccion, 0) / 30)} solicitudes/día
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div className="table-responsive">
                                                    <table className="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Predicción</th>
                                                                <th>Fecha</th>
                                                                <th>Predicción</th>
                                                                <th>Fecha</th>
                                                                <th>Predicción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {Array.from({ length: 10 }, (_, i) => (
                                                                <tr key={i}>
                                                                    {[0, 1, 2].map(offset => {
                                                                        const index = i * 3 + offset;
                                                                        const item = tendencias.predicciones.proximos_30_dias[index];
                                                                        return item ? (
                                                                            <React.Fragment key={offset}>
                                                                                <td>{new Date(item.fecha).toLocaleDateString()}</td>
                                                                                <td>{item.prediccion}</td>
                                                                            </React.Fragment>
                                                                        ) : (
                                                                            <React.Fragment key={offset}>
                                                                                <td></td>
                                                                                <td></td>
                                                                            </React.Fragment>
                                                                        );
                                                                    })}
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
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}