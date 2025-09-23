import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface SystemConfig {
    sistema: {
        nombre: string;
        version: string;
        mantenimiento: boolean;
        max_solicitudes_dia: number;
        tiempo_respuesta_objetivo: number;
    };
    notificaciones: {
        email_enabled: boolean;
        sms_enabled: boolean;
        push_enabled: boolean;
        sonido_enabled: boolean;
    };
    ia: {
        auto_classification: boolean;
        confidence_threshold: number;
        learning_enabled: boolean;
    };
    integraciones: {
        his_enabled: boolean;
        lab_enabled: boolean;
        pacs_enabled: boolean;
    };
}

export default function SystemConfig({ configuracion }: { configuracion: SystemConfig }) {
    const [config, setConfig] = useState(configuracion);
    const [saving, setSaving] = useState(false);

    const handleChange = (section: keyof SystemConfig, field: string, value: any) => {
        setConfig(prev => ({
            ...prev,
            [section]: {
                ...prev[section],
                [field]: value
            }
        }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);
        
        try {
            await router.post('/admin/config', config);
        } catch (error) {
            console.error('Error saving config:', error);
        } finally {
            setSaving(false);
        }
    };

    return (
        <AppLayout>
            <Head title="Configuración del Sistema" />
            
            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h3 className="card-title">Configuración del Sistema</h3>
                            </div>
                            <div className="card-body">
                                <form onSubmit={handleSubmit}>
                                    <div className="row">
                                        {/* Configuración del Sistema */}
                                        <div className="col-md-6">
                                            <h5>Sistema</h5>
                                            
                                            <div className="mb-3">
                                                <label className="form-label">Nombre del Sistema</label>
                                                <input
                                                    type="text"
                                                    className="form-control"
                                                    value={config.sistema.nombre}
                                                    readOnly
                                                />
                                            </div>

                                            <div className="mb-3">
                                                <label className="form-label">Versión</label>
                                                <input
                                                    type="text"
                                                    className="form-control"
                                                    value={config.sistema.version}
                                                    readOnly
                                                />
                                            </div>

                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.sistema.mantenimiento}
                                                    onChange={(e) => handleChange('sistema', 'mantenimiento', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Modo Mantenimiento
                                                </label>
                                            </div>

                                            <div className="mb-3">
                                                <label className="form-label">Máximo Solicitudes por Día</label>
                                                <input
                                                    type="number"
                                                    className="form-control"
                                                    value={config.sistema.max_solicitudes_dia}
                                                    onChange={(e) => handleChange('sistema', 'max_solicitudes_dia', parseInt(e.target.value))}
                                                    min="100"
                                                    max="10000"
                                                />
                                            </div>

                                            <div className="mb-3">
                                                <label className="form-label">Tiempo Respuesta Objetivo (horas)</label>
                                                <input
                                                    type="number"
                                                    className="form-control"
                                                    value={config.sistema.tiempo_respuesta_objetivo}
                                                    onChange={(e) => handleChange('sistema', 'tiempo_respuesta_objetivo', parseInt(e.target.value))}
                                                    min="1"
                                                    max="168"
                                                />
                                            </div>
                                        </div>

                                        {/* Configuración de Notificaciones */}
                                        <div className="col-md-6">
                                            <h5>Notificaciones</h5>
                                            
                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.notificaciones.email_enabled}
                                                    onChange={(e) => handleChange('notificaciones', 'email_enabled', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Notificaciones por Email
                                                </label>
                                            </div>

                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.notificaciones.sms_enabled}
                                                    onChange={(e) => handleChange('notificaciones', 'sms_enabled', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Notificaciones por SMS
                                                </label>
                                            </div>

                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.notificaciones.push_enabled}
                                                    onChange={(e) => handleChange('notificaciones', 'push_enabled', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Notificaciones Push
                                                </label>
                                            </div>

                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.notificaciones.sonido_enabled}
                                                    onChange={(e) => handleChange('notificaciones', 'sonido_enabled', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Sonidos de Notificación
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="row mt-4">
                                        {/* Configuración de IA */}
                                        <div className="col-md-6">
                                            <h5>Inteligencia Artificial</h5>
                                            
                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.ia.auto_classification}
                                                    onChange={(e) => handleChange('ia', 'auto_classification', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Clasificación Automática
                                                </label>
                                            </div>

                                            <div className="mb-3">
                                                <label className="form-label">Umbral de Confianza: {config.ia.confidence_threshold}</label>
                                                <input
                                                    type="range"
                                                    className="form-range"
                                                    min="0.1"
                                                    max="1.0"
                                                    step="0.1"
                                                    value={config.ia.confidence_threshold}
                                                    onChange={(e) => handleChange('ia', 'confidence_threshold', parseFloat(e.target.value))}
                                                />
                                            </div>

                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.ia.learning_enabled}
                                                    onChange={(e) => handleChange('ia', 'learning_enabled', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Aprendizaje Continuo
                                                </label>
                                            </div>
                                        </div>

                                        {/* Configuración de Integraciones */}
                                        <div className="col-md-6">
                                            <h5>Integraciones</h5>
                                            
                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.integraciones.his_enabled}
                                                    onChange={(e) => handleChange('integraciones', 'his_enabled', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Sistema HIS
                                                </label>
                                            </div>

                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.integraciones.lab_enabled}
                                                    onChange={(e) => handleChange('integraciones', 'lab_enabled', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Sistema de Laboratorio
                                                </label>
                                            </div>

                                            <div className="mb-3 form-check">
                                                <input
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={config.integraciones.pacs_enabled}
                                                    onChange={(e) => handleChange('integraciones', 'pacs_enabled', e.target.checked)}
                                                />
                                                <label className="form-check-label">
                                                    Sistema PACS
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="row mt-4">
                                        <div className="col-12">
                                            <button
                                                type="submit"
                                                className="btn btn-primary"
                                                disabled={saving}
                                            >
                                                {saving ? 'Guardando...' : 'Guardar Configuración'}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}