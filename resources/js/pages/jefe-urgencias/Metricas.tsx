import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface MetricasData {
    tiempo_real: {
        solicitudes_activas: number;
        casos_criticos: number;
        tiempo_promedio: number;
        eficiencia: number;
    };
    tendencias: {
        solicitudes_por_hora: Array<{hora: number; total: number}>;
        especialidades_demandadas: Array<{especialidad: string; total: number}>;
        rendimiento_medicos: Array<{medico: string; evaluaciones: number; tiempo_promedio: number}>;
    };
    alertas: Array<{
        id: number;
        tipo: string;
        mensaje: string;
        severidad: string;
        timestamp: string;
    }>;
    kpis: {
        sla_cumplimiento: number;
        satisfaccion_usuario: number;
        precision_ia: number;
        disponibilidad_sistema: number;
    };
}

export default function Metricas() {
    const [metricas, setMetricas] = useState<MetricasData | null>(null);
    const [periodo, setPeriodo] = useState('24h');
    const [autoRefresh, setAutoRefresh] = useState(true);

    const loadMetricas = async () => {
        try {
            const response = await fetch(`/jefe-urgencias/metricas?periodo=${periodo}`);
            const data = await response.json();
            setMetricas(data);
        } catch (error) {
            console.error('Error loading metrics:', error);
        }
    };

    useEffect(() => {
        loadMetricas();
        
        if (autoRefresh) {
            const interval = setInterval(loadMetricas, 30000); // 30 segundos
            return () => clearInterval(interval);
        }
    }, [periodo, autoRefresh]);

    const getSeverityColor = (severidad: string) => {
        switch (severidad) {
            case 'critica': return 'text-danger';
            case 'alta': return 'text-warning';
            case 'media': return 'text-info';
            default: return 'text-secondary';
        }
    };

    const getKPIColor = (valor: number, umbral: number = 80) => {
        if (valor >= umbral) return 'text-success';
        if (valor >= umbral * 0.7) return 'text-warning';
        return 'text-danger';
    };

    return (
        <AppLayout>
            <Head title="Métricas en Tiempo Real" />
            
            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header d-flex justify-content-between align-items-center">
                                <h3 className="card-title">Métricas en Tiempo Real</h3>
                                <div className="d-flex gap-2">
                                    <select 
                                        className="form-select"
                                        value={periodo}
                                        onChange={(e) => setPeriodo(e.target.value)}
                                    >
                                        <option value="1h">Última hora</option>
                                        <option value="24h">Últimas 24 horas</option>
                                        <option value="7d">Últimos 7 días</option>
                                        <option value="30d">Últimos 30 días</option>
                                    </select>
                                    <div className="form-check form-switch">
                                        <input
                                            className="form-check-input"
                                            type="checkbox"
                                            checked={autoRefresh}
                                            onChange={(e) => setAutoRefresh(e.target.checked)}
                                        />
                                        <label className="form-check-label">Auto-refresh</label>
                                    </div>
                                </div>
                            </div>
                            <div className="card-body">
                                {metricas ? (
                                    <>
                                        {/* Métricas en Tiempo Real */}
                                        <div className="row mb-4">
                                            <div className="col-md-3">
                                                <div className="card bg-primary text-white">
                                                    <div className="card-body text-center">
                                                        <h3>{metricas.tiempo_real.solicitudes_activas}</h3>
                                                        <p>Solicitudes Activas</p>
                                                        <small>En tiempo real</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-3">
                                                <div className="card bg-danger text-white">
                                                    <div className="card-body text-center">
                                                        <h3>{metricas.tiempo_real.casos_criticos}</h3>
                                                        <p>Casos Críticos</p>
                                                        <small>Requieren atención</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-3">
                                                <div className="card bg-info text-white">
                                                    <div className="card-body text-center">
                                                        <h3>{metricas.tiempo_real.tiempo_promedio.toFixed(1)}h</h3>
                                                        <p>Tiempo Promedio</p>
                                                        <small>Respuesta actual</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-3">
                                                <div className="card bg-success text-white">
                                                    <div className="card-body text-center">
                                                        <h3>{metricas.tiempo_real.eficiencia.toFixed(1)}%</h3>
                                                        <p>Eficiencia</p>
                                                        <small>Sistema general</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {/* KPIs Principales */}
                                        <div className="row mb-4">
                                            <div className="col-12">
                                                <div className="card">
                                                    <div className="card-header">
                                                        <h5>KPIs Principales</h5>
                                                    </div>
                                                    <div className="card-body">
                                                        <div className="row">
                                                            <div className="col-md-3 text-center">
                                                                <h4 className={getKPIColor(metricas.kpis.sla_cumplimiento, 90)}>
                                                                    {metricas.kpis.sla_cumplimiento.toFixed(1)}%
                                                                </h4>
                                                                <p>SLA Cumplimiento</p>
                                                                <div className="progress">
                                                                    <div 
                                                                        className="progress-bar bg-success" 
                                                                        style={{width: `${metricas.kpis.sla_cumplimiento}%`}}
                                                                    ></div>
                                                                </div>
                                                            </div>
                                                            <div className="col-md-3 text-center">
                                                                <h4 className={getKPIColor(metricas.kpis.satisfaccion_usuario, 85)}>
                                                                    {metricas.kpis.satisfaccion_usuario.toFixed(1)}%
                                                                </h4>
                                                                <p>Satisfacción Usuario</p>
                                                                <div className="progress">
                                                                    <div 
                                                                        className="progress-bar bg-info" 
                                                                        style={{width: `${metricas.kpis.satisfaccion_usuario}%`}}
                                                                    ></div>
                                                                </div>
                                                            </div>
                                                            <div className="col-md-3 text-center">
                                                                <h4 className={getKPIColor(metricas.kpis.precision_ia, 90)}>
                                                                    {metricas.kpis.precision_ia.toFixed(1)}%
                                                                </h4>
                                                                <p>Precisión IA</p>
                                                                <div className="progress">
                                                                    <div 
                                                                        className="progress-bar bg-warning" 
                                                                        style={{width: `${metricas.kpis.precision_ia}%`}}
                                                                    ></div>
                                                                </div>
                                                            </div>
                                                            <div className="col-md-3 text-center">
                                                                <h4 className={getKPIColor(metricas.kpis.disponibilidad_sistema, 99)}>
                                                                    {metricas.kpis.disponibilidad_sistema.toFixed(2)}%
                                                                </h4>
                                                                <p>Disponibilidad</p>
                                                                <div className="progress">
                                                                    <div 
                                                                        className="progress-bar bg-primary" 
                                                                        style={{width: `${metricas.kpis.disponibilidad_sistema}%`}}
                                                                    ></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="row">
                                            {/* Solicitudes por Hora */}
                                            <div className="col-md-6 mb-4">
                                                <div className="card">
                                                    <div className="card-header">
                                                        <h5>Solicitudes por Hora</h5>
                                                    </div>
                                                    <div className="card-body">
                                                        <div className="table-responsive">
                                                            <table className="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Hora</th>
                                                                        <th>Solicitudes</th>
                                                                        <th>Gráfico</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    {metricas.tendencias.solicitudes_por_hora.slice(-8).map((item) => (
                                                                        <tr key={item.hora}>
                                                                            <td>{item.hora}:00</td>
                                                                            <td>{item.total}</td>
                                                                            <td>
                                                                                <div className="progress" style={{height: '10px'}}>
                                                                                    <div 
                                                                                        className="progress-bar" 
                                                                                        style={{
                                                                                            width: `${(item.total / Math.max(...metricas.tendencias.solicitudes_por_hora.map(s => s.total))) * 100}%`
                                                                                        }}
                                                                                    ></div>
                                                                                </div>
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
                                                <div className="card">
                                                    <div className="card-header">
                                                        <h5>Especialidades Más Demandadas</h5>
                                                    </div>
                                                    <div className="card-body">
                                                        <div className="table-responsive">
                                                            <table className="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Especialidad</th>
                                                                        <th>Total</th>
                                                                        <th>%</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    {metricas.tendencias.especialidades_demandadas.slice(0, 5).map((item, index) => {
                                                                        const total = metricas.tendencias.especialidades_demandadas.reduce((sum, esp) => sum + esp.total, 0);
                                                                        const porcentaje = (item.total / total) * 100;
                                                                        return (
                                                                            <tr key={index}>
                                                                                <td>{item.especialidad}</td>
                                                                                <td>{item.total}</td>
                                                                                <td>{porcentaje.toFixed(1)}%</td>
                                                                            </tr>
                                                                        );
                                                                    })}
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Rendimiento de Médicos */}
                                            <div className="col-md-6 mb-4">
                                                <div className="card">
                                                    <div className="card-header">
                                                        <h5>Rendimiento de Médicos</h5>
                                                    </div>
                                                    <div className="card-body">
                                                        <div className="table-responsive">
                                                            <table className="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Médico</th>
                                                                        <th>Evaluaciones</th>
                                                                        <th>Tiempo Prom.</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    {metricas.tendencias.rendimiento_medicos.slice(0, 5).map((item, index) => (
                                                                        <tr key={index}>
                                                                            <td>{item.medico}</td>
                                                                            <td>{item.evaluaciones}</td>
                                                                            <td>{item.tiempo_promedio.toFixed(1)}h</td>
                                                                        </tr>
                                                                    ))}
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Alertas del Sistema */}
                                            <div className="col-md-6 mb-4">
                                                <div className="card">
                                                    <div className="card-header">
                                                        <h5>Alertas del Sistema</h5>
                                                    </div>
                                                    <div className="card-body">
                                                        {metricas.alertas.length > 0 ? (
                                                            <div className="list-group">
                                                                {metricas.alertas.slice(0, 5).map((alerta) => (
                                                                    <div key={alerta.id} className="list-group-item">
                                                                        <div className="d-flex justify-content-between">
                                                                            <div>
                                                                                <h6 className={getSeverityColor(alerta.severidad)}>
                                                                                    {alerta.tipo}
                                                                                </h6>
                                                                                <p className="mb-1 small">{alerta.mensaje}</p>
                                                                            </div>
                                                                            <small className="text-muted">
                                                                                {new Date(alerta.timestamp).toLocaleTimeString()}
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        ) : (
                                                            <p className="text-muted">No hay alertas activas</p>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </>
                                ) : (
                                    <div className="text-center">
                                        <div className="spinner-border" role="status">
                                            <span className="visually-hidden">Cargando...</span>
                                        </div>
                                        <p className="mt-2">Cargando métricas en tiempo real...</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}