import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface IAConfig {
    peso_edad: number;
    peso_gravedad: number;
    peso_especialidad: number;
    peso_sintomas: number;
    criterios_rojo: string;
    criterios_verde: string;
    umbral_rojo: number;
    umbral_verde: number;
}

interface TestCase {
    id: number;
    nombre: string;
    edad: number;
    especialidad: string;
    sintomas: string[];
    gravedad: string;
    resultado_esperado: 'ROJO' | 'VERDE';
    resultado_actual?: 'ROJO' | 'VERDE';
    precision?: number;
}

export default function ConfigurarIA({ configuracion: initialConfig }: { configuracion: IAConfig }) {
    const [config, setConfig] = useState<IAConfig>(initialConfig);
    const [testCases, setTestCases] = useState<TestCase[]>([]);
    const [testing, setTesting] = useState(false);
    const [precision, setPrecision] = useState(0);
    const [saving, setSaving] = useState(false);

    const defaultTestCases: TestCase[] = [
        {
            id: 1,
            nombre: 'Juan Pérez',
            edad: 65,
            especialidad: 'Cardiología',
            sintomas: ['dolor_toracico', 'disnea', 'sudoracion'],
            gravedad: 'alta',
            resultado_esperado: 'ROJO'
        },
        {
            id: 2,
            nombre: 'María García',
            edad: 35,
            especialidad: 'Dermatología',
            sintomas: ['erupcion_cutanea'],
            gravedad: 'baja',
            resultado_esperado: 'VERDE'
        },
        {
            id: 3,
            nombre: 'Carlos López',
            edad: 78,
            especialidad: 'Neurología',
            sintomas: ['confusion', 'mareo', 'cefalea'],
            gravedad: 'alta',
            resultado_esperado: 'ROJO'
        }
    ];

    useEffect(() => {
        setTestCases(defaultTestCases);
    }, []);

    const handleConfigChange = (field: keyof IAConfig, value: number | string) => {
        setConfig(prev => ({ ...prev, [field]: value }));
    };

    const testAlgorithm = async () => {
        setTesting(true);
        try {
            const response = await fetch('/admin/ai/test-classification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ config, testCases })
            });

            const results = await response.json();
            setTestCases(results.testCases);
            setPrecision(results.precision);
        } catch (error) {
            console.error('Error testing algorithm:', error);
        } finally {
            setTesting(false);
        }
    };

    const saveConfiguration = async () => {
        setSaving(true);
        try {
            await router.post('/admin/configuracion-ia', config);
        } catch (error) {
            console.error('Error saving configuration:', error);
        } finally {
            setSaving(false);
        }
    };

    return (
        <AppLayout>
            <Head title="Configurar IA" />
            
            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h3 className="card-title">Configuración del Algoritmo de IA</h3>
                            </div>
                            <div className="card-body">
                                <div className="row">
                                    {/* Panel de Configuración */}
                                    <div className="col-md-6">
                                        <h5>Pesos de Variables</h5>
                                        
                                        <div className="mb-4">
                                            <label className="form-label">Peso Edad: {config.peso_edad}</label>
                                            <input
                                                type="range"
                                                className="form-range"
                                                min="0"
                                                max="1"
                                                step="0.1"
                                                value={config.peso_edad}
                                                onChange={(e) => handleConfigChange('peso_edad', parseFloat(e.target.value))}
                                            />
                                        </div>

                                        <div className="mb-4">
                                            <label className="form-label">Peso Gravedad: {config.peso_gravedad}</label>
                                            <input
                                                type="range"
                                                className="form-range"
                                                min="0"
                                                max="1"
                                                step="0.1"
                                                value={config.peso_gravedad}
                                                onChange={(e) => handleConfigChange('peso_gravedad', parseFloat(e.target.value))}
                                            />
                                        </div>

                                        <div className="mb-4">
                                            <label className="form-label">Peso Especialidad: {config.peso_especialidad}</label>
                                            <input
                                                type="range"
                                                className="form-range"
                                                min="0"
                                                max="1"
                                                step="0.1"
                                                value={config.peso_especialidad}
                                                onChange={(e) => handleConfigChange('peso_especialidad', parseFloat(e.target.value))}
                                            />
                                        </div>

                                        <div className="mb-4">
                                            <label className="form-label">Peso Síntomas: {config.peso_sintomas}</label>
                                            <input
                                                type="range"
                                                className="form-range"
                                                min="0"
                                                max="1"
                                                step="0.1"
                                                value={config.peso_sintomas}
                                                onChange={(e) => handleConfigChange('peso_sintomas', parseFloat(e.target.value))}
                                            />
                                        </div>

                                        <h5>Umbrales de Clasificación</h5>
                                        
                                        <div className="mb-3">
                                            <label className="form-label">Umbral ROJO: {config.umbral_rojo}</label>
                                            <input
                                                type="range"
                                                className="form-range"
                                                min="0.5"
                                                max="1"
                                                step="0.05"
                                                value={config.umbral_rojo}
                                                onChange={(e) => handleConfigChange('umbral_rojo', parseFloat(e.target.value))}
                                            />
                                        </div>

                                        <div className="mb-4">
                                            <label className="form-label">Umbral VERDE: {config.umbral_verde}</label>
                                            <input
                                                type="range"
                                                className="form-range"
                                                min="0"
                                                max="0.5"
                                                step="0.05"
                                                value={config.umbral_verde}
                                                onChange={(e) => handleConfigChange('umbral_verde', parseFloat(e.target.value))}
                                            />
                                        </div>

                                        <div className="d-flex gap-2">
                                            <button
                                                className="btn btn-primary"
                                                onClick={testAlgorithm}
                                                disabled={testing}
                                            >
                                                {testing ? 'Probando...' : 'Probar Algoritmo'}
                                            </button>
                                            <button
                                                className="btn btn-success"
                                                onClick={saveConfiguration}
                                                disabled={saving}
                                            >
                                                {saving ? 'Guardando...' : 'Guardar Configuración'}
                                            </button>
                                        </div>
                                    </div>

                                    {/* Panel de Pruebas */}
                                    <div className="col-md-6">
                                        <h5>Casos de Prueba</h5>
                                        
                                        {precision > 0 && (
                                            <div className="alert alert-info mb-3">
                                                <strong>Precisión del Algoritmo: {(precision * 100).toFixed(1)}%</strong>
                                            </div>
                                        )}

                                        <div className="table-responsive">
                                            <table className="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Paciente</th>
                                                        <th>Esperado</th>
                                                        <th>Actual</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {testCases.map(testCase => (
                                                        <tr key={testCase.id}>
                                                            <td>
                                                                <div>
                                                                    <strong>{testCase.nombre}</strong><br />
                                                                    <small>{testCase.edad} años - {testCase.especialidad}</small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span className={`badge ${testCase.resultado_esperado === 'ROJO' ? 'bg-danger' : 'bg-success'}`}>
                                                                    {testCase.resultado_esperado}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                {testCase.resultado_actual && (
                                                                    <span className={`badge ${testCase.resultado_actual === 'ROJO' ? 'bg-danger' : 'bg-success'}`}>
                                                                        {testCase.resultado_actual}
                                                                    </span>
                                                                )}
                                                            </td>
                                                            <td>
                                                                {testCase.resultado_actual && (
                                                                    <i className={`fas ${testCase.resultado_esperado === testCase.resultado_actual ? 'fa-check text-success' : 'fa-times text-danger'}`}></i>
                                                                )}
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {/* Criterios de Clasificación */}
                                <div className="row mt-4">
                                    <div className="col-md-6">
                                        <h5>Criterios ROJO (Urgente)</h5>
                                        <textarea
                                            className="form-control"
                                            rows={4}
                                            value={config.criterios_rojo}
                                            onChange={(e) => handleConfigChange('criterios_rojo', e.target.value)}
                                            placeholder="Definir criterios para casos urgentes..."
                                        />
                                    </div>
                                    <div className="col-md-6">
                                        <h5>Criterios VERDE (No Urgente)</h5>
                                        <textarea
                                            className="form-control"
                                            rows={4}
                                            value={config.criterios_verde}
                                            onChange={(e) => handleConfigChange('criterios_verde', e.target.value)}
                                            placeholder="Definir criterios para casos no urgentes..."
                                        />
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