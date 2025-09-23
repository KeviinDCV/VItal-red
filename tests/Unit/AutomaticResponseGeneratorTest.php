<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AutomaticResponseGenerator;
use App\Models\SolicitudReferencia;
use App\Models\RegistroMedico;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AutomaticResponseGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private AutomaticResponseGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new AutomaticResponseGenerator();
    }

    public function test_generates_response_for_verde_case()
    {
        $registro = RegistroMedico::factory()->create([
            'especialidad_solicitada' => 'cardiologia'
        ]);

        $solicitud = SolicitudReferencia::factory()->create([
            'prioridad' => 'VERDE',
            'registro_medico_id' => $registro->id
        ]);

        $response = $this->generator->generateResponse($solicitud);

        $this->assertNotNull($response);
        $this->assertStringContainsString('cardiología', strtolower($response['mensaje']));
        $this->assertArrayHasKey('recomendaciones', $response);
        $this->assertArrayHasKey('tiempo_estimado', $response);
    }

    public function test_does_not_generate_response_for_rojo_case()
    {
        $registro = RegistroMedico::factory()->create();
        $solicitud = SolicitudReferencia::factory()->create([
            'prioridad' => 'ROJO',
            'registro_medico_id' => $registro->id
        ]);

        $response = $this->generator->generateResponse($solicitud);

        $this->assertNull($response);
    }

    public function test_customizes_response_by_specialty()
    {
        $registro = RegistroMedico::factory()->create([
            'especialidad_solicitada' => 'pediatria'
        ]);

        $solicitud = SolicitudReferencia::factory()->create([
            'prioridad' => 'VERDE',
            'registro_medico_id' => $registro->id
        ]);

        $response = $this->generator->generateResponse($solicitud);

        $this->assertStringContainsString('pediatría', strtolower($response['mensaje']));
        $this->assertStringContainsString('niño', strtolower($response['recomendaciones']));
    }

    public function test_includes_estimated_time()
    {
        $registro = RegistroMedico::factory()->create();
        $solicitud = SolicitudReferencia::factory()->create([
            'prioridad' => 'VERDE',
            'registro_medico_id' => $registro->id
        ]);

        $response = $this->generator->generateResponse($solicitud);

        $this->assertArrayHasKey('tiempo_estimado', $response);
        $this->assertIsString($response['tiempo_estimado']);
        $this->assertMatchesRegularExpression('/\d+.*días?/', $response['tiempo_estimado']);
    }
}