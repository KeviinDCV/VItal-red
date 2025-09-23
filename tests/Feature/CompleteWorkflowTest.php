<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RegistroMedico;
use App\Models\SolicitudReferencia;
use App\Services\GeminiAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class CompleteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_medical_reference_workflow()
    {
        Event::fake();
        
        // 1. Médico crea registro
        $medico = User::factory()->create(['role' => 'medico']);
        
        $response = $this->actingAs($medico)
            ->post('/medico/ingresar-registro', [
                'tipo_identificacion' => 'cc',
                'numero_identificacion' => '12345678',
                'nombre' => 'Juan',
                'apellidos' => 'Pérez',
                'fecha_nacimiento' => '1980-01-01',
                'sexo' => 'masculino',
                'especialidad_solicitada' => 'cardiologia',
                'motivo_consulta' => 'Dolor torácico intenso',
                'diagnostico_principal' => 'Síndrome coronario agudo',
                'motivo_remision' => 'Requiere evaluación urgente por cardiología'
            ]);

        $response->assertRedirect();
        
        // 2. Verificar que se creó el registro
        $this->assertDatabaseHas('registros_medicos', [
            'numero_identificacion' => '12345678',
            'nombre' => 'Juan'
        ]);

        // 3. Verificar que se creó la solicitud
        $solicitud = SolicitudReferencia::where('motivo_remision', 'Requiere evaluación urgente por cardiología')->first();
        $this->assertNotNull($solicitud);
        $this->assertNotNull($solicitud->codigo_solicitud);

        // 4. Admin revisa caso ROJO
        $admin = User::factory()->create(['role' => 'administrador']);
        
        $response = $this->actingAs($admin)
            ->post("/admin/referencias/{$solicitud->id}/decidir", [
                'decision' => 'ACEPTADO',
                'observaciones' => 'Caso crítico aprobado'
            ]);

        $response->assertRedirect();
        
        // 5. Verificar decisión guardada
        $this->assertDatabaseHas('decisiones_referencia', [
            'solicitud_id' => $solicitud->id,
            'decision' => 'ACEPTADO'
        ]);

        // 6. Verificar notificación enviada
        Event::assertDispatched(\App\Events\NuevaNotificacion::class);
    }

    public function test_automatic_green_case_workflow()
    {
        Event::fake();
        
        $medico = User::factory()->create(['role' => 'medico']);
        
        // Crear caso VERDE (rutinario)
        $response = $this->actingAs($medico)
            ->post('/medico/ingresar-registro', [
                'tipo_identificacion' => 'cc',
                'numero_identificacion' => '87654321',
                'nombre' => 'María',
                'apellidos' => 'González',
                'fecha_nacimiento' => '1990-05-15',
                'sexo' => 'femenino',
                'especialidad_solicitada' => 'medicina_interna',
                'motivo_consulta' => 'Control rutinario',
                'diagnostico_principal' => 'Examen médico general',
                'motivo_remision' => 'Control de rutina anual'
            ]);

        $solicitud = SolicitudReferencia::where('motivo_remision', 'Control de rutina anual')->first();
        
        // Verificar que se clasificó como VERDE
        $this->assertEquals('VERDE', $solicitud->prioridad);
        
        // Verificar que se generó respuesta automática
        $this->assertNotNull($solicitud->respuesta_automatica);
    }

    public function test_notification_workflow()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $medico = User::factory()->create(['role' => 'medico']);
        
        // Crear caso crítico
        $solicitud = SolicitudReferencia::factory()->create([
            'prioridad' => 'ROJO',
            'estado' => 'PENDIENTE'
        ]);

        // Simular timeout de caso crítico
        $response = $this->actingAs($admin)
            ->post('/admin/notificaciones/timeout-alert', [
                'solicitud_id' => $solicitud->id
            ]);

        // Verificar que se creó notificación
        $this->assertDatabaseHas('notificaciones', [
            'tipo' => 'timeout_alert',
            'user_id' => $admin->id
        ]);
    }

    public function test_performance_monitoring_workflow()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        
        // Simular múltiples solicitudes para testing de carga
        SolicitudReferencia::factory()->count(100)->create();
        
        $response = $this->actingAs($admin)
            ->get('/admin/performance');

        $response->assertStatus(200);
        
        // Verificar métricas de performance
        $response = $this->actingAs($admin)
            ->get('/admin/performance/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'metrics' => [
                    'memory_usage',
                    'active_users',
                    'requests_per_minute'
                ]
            ]);
    }
}