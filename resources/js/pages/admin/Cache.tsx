import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface CacheMetrics {
    redis_status: string;
    total_keys: number;
    memory_usage: string;
    hit_rate: number;
    miss_rate: number;
    operations_per_second: number;
    cache_size: string;
    uptime: string;
}

export default function Cache() {
    const [metrics, setMetrics] = useState<CacheMetrics | null>(null);
    const [loading, setLoading] = useState(false);
    const [operation, setOperation] = useState('');

    const loadMetrics = async () => {
        try {
            const response = await fetch('/admin/cache/metrics');
            const data = await response.json();
            setMetrics(data);
        } catch (error) {
            console.error('Error loading cache metrics:', error);
        }
    };

    useEffect(() => {
        loadMetrics();
        const interval = setInterval(loadMetrics, 30000); // Actualizar cada 30 segundos
        return () => clearInterval(interval);
    }, []);

    const handleClearCache = async () => {
        if (!confirm('¿Está seguro de limpiar toda la caché?')) return;
        
        setOperation('clearing');
        try {
            await router.post('/admin/cache/clear');
            await loadMetrics();
        } catch (error) {
            console.error('Error clearing cache:', error);
        } finally {
            setOperation('');
        }
    };

    const handleOptimizeCache = async () => {
        setOperation('optimizing');
        try {
            await router.post('/admin/cache/optimize');
            await loadMetrics();
        } catch (error) {
            console.error('Error optimizing cache:', error);
        } finally {
            setOperation('');
        }
    };

    return (
        <AppLayout>
            <Head title="Gestión de Cache" />
            
            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header d-flex justify-content-between align-items-center">
                                <h3 className="card-title">Gestión de Cache Redis</h3>
                                <div>
                                    <button 
                                        className="btn btn-warning me-2"
                                        onClick={handleOptimizeCache}
                                        disabled={operation === 'optimizing'}
                                    >
                                        {operation === 'optimizing' ? 'Optimizando...' : 'Optimizar Cache'}
                                    </button>
                                    <button 
                                        className="btn btn-danger"
                                        onClick={handleClearCache}
                                        disabled={operation === 'clearing'}
                                    >
                                        {operation === 'clearing' ? 'Limpiando...' : 'Limpiar Cache'}
                                    </button>
                                </div>
                            </div>
                            <div className="card-body">
                                {metrics ? (
                                    <>
                                        {/* Estado del Sistema */}
                                        <div className="row mb-4">
                                            <div className="col-md-2">
                                                <div className={`card ${metrics.redis_status === 'connected' ? 'bg-success' : 'bg-danger'} text-white`}>
                                                    <div className="card-body text-center">
                                                        <h5>Estado Redis</h5>
                                                        <p>{metrics.redis_status === 'connected' ? 'Conectado' : 'Desconectado'}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-2">
                                                <div className="card bg-primary text-white">
                                                    <div className="card-body text-center">
                                                        <h5>{metrics.total_keys}</h5>
                                                        <p>Total Keys</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-2">
                                                <div className="card bg-info text-white">
                                                    <div className="card-body text-center">
                                                        <h5>{metrics.memory_usage}</h5>
                                                        <p>Memoria Usada</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-2">
                                                <div className="card bg-success text-white">
                                                    <div className="card-body text-center">
                                                        <h5>{metrics.hit_rate.toFixed(1)}%</h5>
                                                        <p>Hit Rate</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-2">
                                                <div className="card bg-warning text-white">
                                                    <div className="card-body text-center">
                                                        <h5>{metrics.operations_per_second}</h5>
                                                        <p>Ops/Seg</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-2">
                                                <div className="card bg-secondary text-white">
                                                    <div className="card-body text-center">
                                                        <h5>{metrics.uptime}</h5>
                                                        <p>Uptime</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Métricas Detalladas */}
                                        <div className="row">
                                            <div className="col-md-6">
                                                <div className="card">
                                                    <div className="card-header">
                                                        <h5>Rendimiento de Cache</h5>
                                                    </div>
                                                    <div className="card-body">
                                                        <div className="mb-3">
                                                            <label className="form-label">Hit Rate</label>
                                                            <div className="progress">
                                                                <div 
                                                                    className="progress-bar bg-success" 
                                                                    style={{width: `${metrics.hit_rate}%`}}
                                                                >
                                                                    {metrics.hit_rate.toFixed(1)}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div className="mb-3">
                                                            <label className="form-label">Miss Rate</label>
                                                            <div className="progress">
                                                                <div 
                                                                    className="progress-bar bg-danger" 
                                                                    style={{width: `${metrics.miss_rate}%`}}
                                                                >
                                                                    {metrics.miss_rate.toFixed(1)}%
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <table className="table table-sm">
                                                            <tbody>
                                                                <tr>
                                                                    <td><strong>Operaciones por segundo:</strong></td>
                                                                    <td>{metrics.operations_per_second}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Tamaño total de cache:</strong></td>
                                                                    <td>{metrics.cache_size}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Tiempo activo:</strong></td>
                                                                    <td>{metrics.uptime}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="col-md-6">
                                                <div className="card">
                                                    <div className="card-header">
                                                        <h5>Configuración de Cache</h5>
                                                    </div>
                                                    <div className="card-body">
                                                        <div className="alert alert-info">
                                                            <h6>Configuración Actual</h6>
                                                            <ul className="mb-0">
                                                                <li><strong>Driver:</strong> Redis</li>
                                                                <li><strong>TTL por defecto:</strong> 3600 segundos</li>
                                                                <li><strong>Prefijo:</strong> vital_red_cache</li>
                                                                <li><strong>Serialización:</strong> PHP</li>
                                                            </ul>
                                                        </div>

                                                        <div className="alert alert-warning">
                                                            <h6>Recomendaciones</h6>
                                                            <ul className="mb-0">
                                                                <li>Hit Rate óptimo: > 90%</li>
                                                                <li>Limpiar cache si Hit Rate < 70%</li>
                                                                <li>Optimizar si memoria > 80%</li>
                                                                <li>Monitorear operaciones/segundo</li>
                                                            </ul>
                                                        </div>

                                                        <div className="d-grid gap-2">
                                                            <button 
                                                                className="btn btn-outline-primary"
                                                                onClick={loadMetrics}
                                                            >
                                                                <i className="fas fa-sync-alt"></i> Actualizar Métricas
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Tipos de Cache */}
                                        <div className="row mt-4">
                                            <div className="col-12">
                                                <div className="card">
                                                    <div className="card-header">
                                                        <h5>Tipos de Cache en el Sistema</h5>
                                                    </div>
                                                    <div className="card-body">
                                                        <div className="row">
                                                            <div className="col-md-3">
                                                                <div className="card border-primary">
                                                                    <div className="card-body text-center">
                                                                        <i className="fas fa-user-md fa-2x text-primary mb-2"></i>
                                                                        <h6>Cache de Usuarios</h6>
                                                                        <small>Permisos y roles</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div className="col-md-3">
                                                                <div className="card border-success">
                                                                    <div className="card-body text-center">
                                                                        <i className="fas fa-file-medical fa-2x text-success mb-2"></i>
                                                                        <h6>Cache de Solicitudes</h6>
                                                                        <small>Referencias médicas</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div className="col-md-3">
                                                                <div className="card border-info">
                                                                    <div className="card-body text-center">
                                                                        <i className="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                                                        <h6>Cache de Reportes</h6>
                                                                        <small>Estadísticas y métricas</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div className="col-md-3">
                                                                <div className="card border-warning">
                                                                    <div className="card-body text-center">
                                                                        <i className="fas fa-cog fa-2x text-warning mb-2"></i>
                                                                        <h6>Cache de Configuración</h6>
                                                                        <small>Configuración IA y sistema</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
                                        <p className="mt-2">Cargando métricas de cache...</p>
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