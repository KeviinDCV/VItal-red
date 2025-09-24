<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SystemConfigController extends Controller
{
    public function index()
    {
        $config = [
            'sistema' => [
                'nombre' => config('app.name'),
                'version' => '2.0.0',
                'mantenimiento' => Cache::get('sistema_mantenimiento', false),
                'max_solicitudes_dia' => Cache::get('max_solicitudes_dia', 1000),
                'tiempo_respuesta_objetivo' => Cache::get('tiempo_respuesta_objetivo', 24)
            ],
            'notificaciones' => [
                'email_enabled' => Cache::get('notif_email_enabled', true),
                'sms_enabled' => Cache::get('notif_sms_enabled', false),
                'push_enabled' => Cache::get('notif_push_enabled', true),
                'sonido_enabled' => Cache::get('notif_sonido_enabled', true)
            ],
            'ia' => [
                'auto_classification' => Cache::get('ia_auto_classification', true),
                'confidence_threshold' => Cache::get('ia_confidence_threshold', 0.8),
                'learning_enabled' => Cache::get('ia_learning_enabled', true)
            ],
            'integraciones' => [
                'his_enabled' => Cache::get('his_enabled', false),
                'lab_enabled' => Cache::get('lab_enabled', false),
                'pacs_enabled' => Cache::get('pacs_enabled', false)
            ]
        ];

        return Inertia::render('admin/SystemConfig', [
            'configuracion' => $config
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sistema.mantenimiento' => 'boolean',
            'sistema.max_solicitudes_dia' => 'integer|min:100|max:10000',
            'sistema.tiempo_respuesta_objetivo' => 'integer|min:1|max:168',
            'notificaciones.email_enabled' => 'boolean',
            'notificaciones.sms_enabled' => 'boolean',
            'notificaciones.push_enabled' => 'boolean',
            'notificaciones.sonido_enabled' => 'boolean',
            'ia.auto_classification' => 'boolean',
            'ia.confidence_threshold' => 'numeric|between:0.1,1.0',
            'ia.learning_enabled' => 'boolean',
            'integraciones.his_enabled' => 'boolean',
            'integraciones.lab_enabled' => 'boolean',
            'integraciones.pacs_enabled' => 'boolean'
        ]);

        foreach ($validated as $section => $configs) {
            foreach ($configs as $key => $value) {
                Cache::put("{$section}_{$key}", $value, now()->addDays(30));
            }
        }

        return back()->with('success', 'Configuración actualizada correctamente');
    }
}