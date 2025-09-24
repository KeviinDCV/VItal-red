<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\RegistroMedico;
use App\Models\SolicitudReferencia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SolicitudReferenciaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_medico_can_create_solicitud()
    {
        $medico = User::factory()->create(['role' => 'medico']);
        $registro = RegistroMedico::factory()->create();

        $response = $this->actingAs($medico)
            ->postJson('/api/solicitudes', [
                'registro_medico_id' => $registro->id,
                'motivo_remision' => 'Caso complejo requiere especialista'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'codigo_solicitud',
                    'prioridad',
                    'estado',
                    'puntuacion_ia'
                ]
            ]);
    }

    public function test_admin_can_view_all_solicitudes()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        SolicitudReferencia::factory()->count(5)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/solicitudes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'codigo_solicitud',
                        'prioridad',
                        'estado'
                    ]
                ]
            ]);
    }

    public function test_admin_can_decide_on_solicitud()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $solicitud = SolicitudReferencia::factory()->create([
            'estado' => 'PENDIENTE',
            'prioridad' => 'ROJO'
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/solicitudes/{$solicitud->id}/decidir", [
                'decision' => 'ACEPTADO',
                'observaciones' => 'Caso aprobado para especialista'
            ]);

        $response->assertStatus(200);
        
        $solicitud->refresh();
        $this->assertEquals('ACEPTADO', $solicitud->estado);
    }

    public function test_ips_can_only_view_own_solicitudes()
    {
        $ips = User::factory()->create(['role' => 'ips']);
        $otherUser = User::factory()->create(['role' => 'ips']);
        
        SolicitudReferencia::factory()->create(['usuario_id' => $ips->id]);
        SolicitudReferencia::factory()->create(['usuario_id' => $otherUser->id]);

        $response = $this->actingAs($ips)
            ->getJson('/api/solicitudes');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals($ips->id, $data[0]['usuario_id']);
    }

    public function test_unauthorized_user_cannot_access_api()
    {
        $response = $this->getJson('/api/solicitudes');

        $response->assertStatus(401);
    }

    public function test_validation_errors_on_invalid_data()
    {
        $medico = User::factory()->create(['role' => 'medico']);

        $response = $this->actingAs($medico)
            ->postJson('/api/solicitudes', [
                'registro_medico_id' => 999, // Non-existent
                'motivo_remision' => '' // Empty
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['registro_medico_id', 'motivo_remision']);
    }
}