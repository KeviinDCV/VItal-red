<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GeminiAIService;
use Illuminate\Support\Facades\Http;

class GeminiAIServiceTest extends TestCase
{
    private GeminiAIService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = new GeminiAIService();
    }

    public function test_classify_medical_text_returns_rojo_for_critical_case()
    {
        $criticalText = "Paciente de 65 años con dolor torácico intenso, disnea severa, TA 180/110, FC 120";
        
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '{"prioridad":"ROJO","puntuacion":0.92,"observaciones":"Caso crítico"}']]]]
                ]
            ])
        ]);

        $result = $this->aiService->classifyMedicalText($criticalText);

        $this->assertEquals('ROJO', $result['prioridad']);
        $this->assertGreaterThan(0.8, $result['puntuacion']);
        $this->assertNotEmpty($result['observaciones']);
    }

    public function test_classify_medical_text_returns_verde_for_routine_case()
    {
        $routineText = "Paciente de 30 años para control rutinario, sin síntomas agudos";
        
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '{"prioridad":"VERDE","puntuacion":0.3,"observaciones":"Consulta rutinaria"}']]]]
                ]
            ])
        ]);

        $result = $this->aiService->classifyMedicalText($routineText);

        $this->assertEquals('VERDE', $result['prioridad']);
        $this->assertLessThan(0.7, $result['puntuacion']);
    }

    public function test_extract_patient_data_from_text()
    {
        $medicalText = "Juan Pérez, CC 12345678, 45 años, masculino, cardiología";
        
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '{"nombre":"Juan","apellidos":"Pérez","numero_identificacion":"12345678","edad":45,"sexo":"masculino"}']]]]
                ]
            ])
        ]);

        $result = $this->aiService->extractPatientData($medicalText);

        $this->assertEquals('Juan', $result['nombre']);
        $this->assertEquals('Pérez', $result['apellidos']);
        $this->assertEquals('12345678', $result['numero_identificacion']);
        $this->assertEquals(45, $result['edad']);
    }

    public function test_handles_api_failure_gracefully()
    {
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        $result = $this->aiService->classifyMedicalText("test text");

        $this->assertEquals('VERDE', $result['prioridad']);
        $this->assertEquals(0.5, $result['puntuacion']);
        $this->assertStringContainsString('Error', $result['observaciones']);
    }
}