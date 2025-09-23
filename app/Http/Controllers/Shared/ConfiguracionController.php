<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionUsuario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        $configuracion = ConfiguracionUsuario::obtenerConfiguracion($usuario->id);

        return Inertia::render('Shared/ConfiguracionPersonal', [
            'usuario' => $usuario,
            'configuracion_notificaciones' => $configuracion->notificaciones,
            'configuracion_privacidad' => $configuracion->privacidad,
            'configuracion_interfaz' => $configuracion->interfaz
        ]);
    }

    public function actualizarDatosPersonales(Request $request)
    {
        $usuario = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:20',
            'especialidad' => 'nullable|string|max:100',
            'numero_licencia' => 'nullable|string|max:50'
        ]);

        $usuario->update($request->only([
            'name', 
            'email', 
            'telefono', 
            'especialidad', 
            'numero_licencia'
        ]));

        return response()->json(['success' => true, 'message' => 'Datos personales actualizados']);
    }

    public function actualizarNotificaciones(Request $request)
    {
        $usuario = auth()->user();
        $configuracion = ConfiguracionUsuario::obtenerConfiguracion($usuario->id);

        $request->validate([
            'email_nuevas_referencias' => 'boolean',
            'email_cambios_estado' => 'boolean',
            'email_recordatorios' => 'boolean',
            'sms_urgencias' => 'boolean',
            'sms_recordatorios' => 'boolean',
            'push_tiempo_real' => 'boolean',
            'push_alertas_criticas' => 'boolean',
            'frecuencia_resumen' => 'in:diario,semanal,mensual,nunca',
            'horario_no_molestar_inicio' => 'required|date_format:H:i',
            'horario_no_molestar_fin' => 'required|date_format:H:i'
        ]);

        $configuracion->update([
            'notificaciones' => $request->all()
        ]);

        return response()->json(['success' => true, 'message' => 'Configuración de notificaciones actualizada']);
    }

    public function actualizarPrivacidad(Request $request)
    {
        $usuario = auth()->user();
        $configuracion = ConfiguracionUsuario::obtenerConfiguracion($usuario->id);

        $request->validate([
            'mostrar_perfil_publico' => 'boolean',
            'compartir_estadisticas' => 'boolean',
            'permitir_contacto_directo' => 'boolean',
            'mostrar_estado_online' => 'boolean',
            'historial_actividad_visible' => 'boolean',
            'datos_anonimos_investigacion' => 'boolean'
        ]);

        $configuracion->update([
            'privacidad' => $request->all()
        ]);

        return response()->json(['success' => true, 'message' => 'Configuración de privacidad actualizada']);
    }

    public function actualizarInterfaz(Request $request)
    {
        $usuario = auth()->user();
        $configuracion = ConfiguracionUsuario::obtenerConfiguracion($usuario->id);

        $request->validate([
            'tema' => 'in:claro,oscuro,auto',
            'idioma' => 'in:es,en',
            'zona_horaria' => 'string',
            'formato_fecha' => 'in:dd/mm/yyyy,mm/dd/yyyy,yyyy-mm-dd',
            'formato_hora' => 'in:12h,24h',
            'densidad_interfaz' => 'in:compacta,normal,espaciosa',
            'mostrar_ayuda_contextual' => 'boolean'
        ]);

        $configuracion->update([
            'interfaz' => $request->all()
        ]);

        return response()->json(['success' => true, 'message' => 'Configuración de interfaz actualizada']);
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password_nuevo' => 'required|string|min:8|confirmed',
        ]);

        $usuario = auth()->user();

        if (!Hash::check($request->password_actual, $usuario->password)) {
            return response()->json(['error' => 'La contraseña actual es incorrecta'], 400);
        }

        $usuario->update([
            'password' => Hash::make($request->password_nuevo)
        ]);

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada exitosamente']);
    }

    public function obtenerConfiguracion()
    {
        $usuario = auth()->user();
        $configuracion = ConfiguracionUsuario::obtenerConfiguracion($usuario->id);

        return response()->json([
            'notificaciones' => $configuracion->notificaciones,
            'privacidad' => $configuracion->privacidad,
            'interfaz' => $configuracion->interfaz
        ]);
    }

    public function resetearConfiguracion(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:notificaciones,privacidad,interfaz,todo'
        ]);

        $usuario = auth()->user();
        $configuracion = ConfiguracionUsuario::obtenerConfiguracion($usuario->id);

        switch ($request->tipo) {
            case 'notificaciones':
                $configuracion->update(['notificaciones' => $this->getDefaultNotificaciones()]);
                break;
            case 'privacidad':
                $configuracion->update(['privacidad' => $this->getDefaultPrivacidad()]);
                break;
            case 'interfaz':
                $configuracion->update(['interfaz' => $this->getDefaultInterfaz()]);
                break;
            case 'todo':
                $configuracion->update([
                    'notificaciones' => $this->getDefaultNotificaciones(),
                    'privacidad' => $this->getDefaultPrivacidad(),
                    'interfaz' => $this->getDefaultInterfaz()
                ]);
                break;
        }

        return response()->json(['success' => true, 'message' => 'Configuración restablecida']);
    }

    private function getDefaultNotificaciones()
    {
        return [
            'email_nuevas_referencias' => true,
            'email_cambios_estado' => true,
            'email_recordatorios' => false,
            'sms_urgencias' => true,
            'sms_recordatorios' => false,
            'push_tiempo_real' => true,
            'push_alertas_criticas' => true,
            'frecuencia_resumen' => 'diario',
            'horario_no_molestar_inicio' => '22:00',
            'horario_no_molestar_fin' => '07:00'
        ];
    }

    private function getDefaultPrivacidad()
    {
        return [
            'mostrar_perfil_publico' => true,
            'compartir_estadisticas' => true,
            'permitir_contacto_directo' => true,
            'mostrar_estado_online' => true,
            'historial_actividad_visible' => false,
            'datos_anonimos_investigacion' => true
        ];
    }

    private function getDefaultInterfaz()
    {
        return [
            'tema' => 'claro',
            'idioma' => 'es',
            'zona_horaria' => 'America/Bogota',
            'formato_fecha' => 'dd/mm/yyyy',
            'formato_hora' => '24h',
            'densidad_interfaz' => 'normal',
            'mostrar_ayuda_contextual' => true
        ];
    }
}