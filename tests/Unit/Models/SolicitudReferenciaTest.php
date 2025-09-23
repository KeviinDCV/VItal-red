<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SolicitudReferencia;
use App\Models\RegistroMedico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SolicitudReferenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitud_belongs_to_registro_medico()
    {
        $registro = RegistroMedico::factory()->create();
        $solicitud = SolicitudReferencia::factory()->create([
            'registro_medico_id' => $registro->id
        ]);

        $this->assertInstanceOf(RegistroMedico::class, $solicitud->registroMedico);
        $this->assertEquals($registro->id, $solicitud->registroMedico->id);
    }

    public function test_solicitud_belongs_to_user()
    {
        $user = User::factory()->create();
        $solicitud = SolicitudReferencia::factory()->create([
            'usuario_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $solicitud->usuario);
        $this->assertEquals($user->id, $solicitud->usuario->id);
    }

    public function test_generates_codigo_solicitud_on_creation()
    {
        $solicitud = SolicitudReferencia::factory()->create();

        $this->assertNotNull($solicitud->codigo_solicitud);
        $this->assertStringStartsWith('REF-', $solicitud->codigo_solicitud);
        $this->assertMatchesRegularExpression('/REF-\d{4}-\d{6}/', $solicitud->codigo_solicitud);
    }

    public function test_scope_pendientes_filters_correctly()
    {
        SolicitudReferencia::factory()->create(['estado' => 'PENDIENTE']);
        SolicitudReferencia::factory()->create(['estado' => 'ACEPTADO']);
        SolicitudReferencia::factory()->create(['estado' => 'PENDIENTE']);

        $pendientes = SolicitudReferencia::pendientes()->get();

        $this->assertCount(2, $pendientes);
        $this->assertTrue($pendientes->every(fn($s) => $s->estado === 'PENDIENTE'));
    }

    public function test_scope_rojas_filters_correctly()
    {
        SolicitudReferencia::factory()->create(['prioridad' => 'ROJO']);
        SolicitudReferencia::factory()->create(['prioridad' => 'VERDE']);
        SolicitudReferencia::factory()->create(['prioridad' => 'ROJO']);

        $rojas = SolicitudReferencia::rojas()->get();

        $this->assertCount(2, $rojas);
        $this->assertTrue($rojas->every(fn($s) => $s->prioridad === 'ROJO'));
    }

    public function test_is_critica_returns_true_for_rojo_pendiente()
    {
        $solicitud = SolicitudReferencia::factory()->create([
            'prioridad' => 'ROJO',
            'estado' => 'PENDIENTE'
        ]);

        $this->assertTrue($solicitud->isCritica());
    }

    public function test_is_critica_returns_false_for_verde()
    {
        $solicitud = SolicitudReferencia::factory()->create([
            'prioridad' => 'VERDE',
            'estado' => 'PENDIENTE'
        ]);

        $this->assertFalse($solicitud->isCritica());
    }

    public function test_tiempo_transcurrido_calculates_correctly()
    {
        $solicitud = SolicitudReferencia::factory()->create([
            'created_at' => now()->subHours(2)
        ]);

        $tiempo = $solicitud->getTiempoTranscurridoAttribute();

        $this->assertGreaterThanOrEqual(2, $tiempo);
        $this->assertLessThan(3, $tiempo);
    }

    public function test_casts_attributes_correctly()
    {
        $solicitud = SolicitudReferencia::factory()->create([
            'puntuacion_ia' => 0.85
        ]);

        $this->assertIsFloat($solicitud->puntuacion_ia);
        $this->assertEquals(0.85, $solicitud->puntuacion_ia);
    }
}