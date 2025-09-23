<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionIA;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SystemConfigController extends Controller
{
    public function index()
    {
        $config = [
            'general' => [
                'app_name' => config('app.name'),
                'timezone' => config('app.timezone'),
                'debug_mode' => config('app.debug')
            ],
            'ia' => ConfiguracionIA::first(),
            'notificaciones' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => true
            ]
        ];

        return Inertia::render('admin/SystemConfig', [
            'config' => $config
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'section' => 'required|string',
            'data' => 'required|array'
        ]);

        // Actualizar configuración según la sección
        switch ($request->section) {
            case 'ia':
                ConfiguracionIA::updateOrCreate(
                    ['id' => 1],
                    $request->data
                );
                break;
        }

        return response()->json(['success' => true]);
    }
}