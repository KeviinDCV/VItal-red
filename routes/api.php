<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReferenciasController;
use App\Http\Controllers\Medico\EvaluacionController;
use App\Http\Controllers\IPS\SolicitudController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API v1
Route::prefix('v1')->group(function () {
    
    // Autenticación API
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'apiLogin']);
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'apiLogout'])->middleware('auth:sanctum');
    
    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        
        // Referencias
        Route::apiResource('referencias', ReferenciasController::class);
        Route::post('/referencias/{id}/evaluar', [EvaluacionController::class, 'apiEvaluar']);
        
        // Solicitudes IPS
        Route::post('/solicitudes', [SolicitudController::class, 'apiStore']);
        Route::get('/solicitudes/{id}/estado', [SolicitudController::class, 'apiEstado']);
        
        // Notificaciones
        Route::get('/notificaciones', function () {
            return auth()->user()->notificaciones()->latest()->limit(10)->get();
        });
        
        // Métricas
        Route::get('/metricas/dashboard', function () {
            return response()->json([
                'solicitudes_pendientes' => \App\Models\SolicitudReferencia::where('estado', 'PENDIENTE')->count(),
                'casos_criticos' => \App\Models\SolicitudReferencia::where('prioridad', 'ROJO')->count(),
                'usuarios_activos' => \App\Models\User::where('is_active', true)->count()
            ]);
        });
        
        // WebHooks externos
        Route::post('/webhook/his', function (Request $request) {
            // Integración HIS
            return response()->json(['status' => 'received']);
        });
        
        Route::post('/webhook/lab', function (Request $request) {
            // Integración Laboratorio
            return response()->json(['status' => 'received']);
        });
    });
});