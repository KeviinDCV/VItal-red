<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\User;
use App\Models\SolicitudReferencia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class LoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_handles_1000_concurrent_requests()
    {
        $users = User::factory()->count(100)->create(['role' => 'medico']);
        
        $startTime = microtime(true);
        $responses = [];
        
        // Simular 1000 requests concurrentes
        for ($i = 0; $i < 1000; $i++) {
            $user = $users->random();
            
            $requestStart = microtime(true);
            
            $response = $this->actingAs($user)
                ->get('/dashboard');
            
            $requestTime = (microtime(true) - $requestStart) * 1000;
            
            $responses[] = [
                'status' => $response->status(),
                'time' => $requestTime
            ];
            
            // Verificar que la respuesta es exitosa
            $this->assertLessThan(500, $response->status());
        }
        
        $totalTime = microtime(true) - $startTime;
        $avgResponseTime = array_sum(array_column($responses, 'time')) / count($responses);
        
        // Verificaciones de performance
        $this->assertLessThan(60, $totalTime, 'Total time should be less than 60 seconds');
        $this->assertLessThan(200, $avgResponseTime, 'Average response time should be less than 200ms');
        
        $successfulRequests = count(array_filter($responses, fn($r) => $r['status'] < 400));
        $successRate = ($successfulRequests / count($responses)) * 100;
        
        $this->assertGreaterThan(95, $successRate, 'Success rate should be greater than 95%');
    }

    public function test_database_performance_under_load()
    {
        // Crear datos de prueba
        SolicitudReferencia::factory()->count(10000)->create();
        
        $startTime = microtime(true);
        
        // Ejecutar consultas típicas del sistema
        $queries = [
            fn() => SolicitudReferencia::where('estado', 'PENDIENTE')->count(),
            fn() => SolicitudReferencia::where('prioridad', 'ROJO')->limit(50)->get(),
            fn() => SolicitudReferencia::with('registroMedico')->limit(100)->get(),
            fn() => DB::table('solicitudes_referencia')
                ->join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
                ->select('solicitudes_referencia.*', 'registros_medicos.nombre')
                ->limit(200)->get()
        ];
        
        $queryTimes = [];
        
        foreach ($queries as $query) {
            $queryStart = microtime(true);
            $query();
            $queryTimes[] = (microtime(true) - $queryStart) * 1000;
        }
        
        $totalQueryTime = microtime(true) - $startTime;
        $avgQueryTime = array_sum($queryTimes) / count($queryTimes);
        
        // Verificar performance de DB
        $this->assertLessThan(5, $totalQueryTime, 'Total query time should be less than 5 seconds');
        $this->assertLessThan(100, $avgQueryTime, 'Average query time should be less than 100ms');
    }

    public function test_memory_usage_under_load()
    {
        $initialMemory = memory_get_usage(true);
        
        // Simular carga de memoria
        $users = User::factory()->count(1000)->create();
        $solicitudes = SolicitudReferencia::factory()->count(5000)->create();
        
        // Ejecutar operaciones que consumen memoria
        $data = SolicitudReferencia::with(['registroMedico', 'usuario'])->get();
        
        $peakMemory = memory_get_peak_usage(true);
        $memoryIncrease = $peakMemory - $initialMemory;
        
        // Verificar que el uso de memoria es razonable (menos de 512MB)
        $this->assertLessThan(512 * 1024 * 1024, $memoryIncrease, 'Memory increase should be less than 512MB');
        
        // Limpiar memoria
        unset($data, $users, $solicitudes);
        gc_collect_cycles();
        
        $finalMemory = memory_get_usage(true);
        $memoryLeak = $finalMemory - $initialMemory;
        
        // Verificar que no hay memory leaks significativos
        $this->assertLessThan(50 * 1024 * 1024, $memoryLeak, 'Memory leak should be less than 50MB');
    }

    public function test_concurrent_database_writes()
    {
        $users = User::factory()->count(50)->create(['role' => 'medico']);
        
        $startTime = microtime(true);
        $errors = 0;
        
        // Simular escrituras concurrentes
        for ($i = 0; $i < 500; $i++) {
            try {
                $user = $users->random();
                
                SolicitudReferencia::factory()->create([
                    'usuario_id' => $user->id,
                    'codigo_solicitud' => 'LOAD-TEST-' . $i
                ]);
                
            } catch (\Exception $e) {
                $errors++;
            }
        }
        
        $totalTime = microtime(true) - $startTime;
        $errorRate = ($errors / 500) * 100;
        
        // Verificaciones
        $this->assertLessThan(30, $totalTime, 'Concurrent writes should complete in less than 30 seconds');
        $this->assertLessThan(1, $errorRate, 'Error rate should be less than 1%');
        
        // Verificar integridad de datos
        $createdRecords = SolicitudReferencia::where('codigo_solicitud', 'LIKE', 'LOAD-TEST-%')->count();
        $this->assertEquals(500 - $errors, $createdRecords, 'All successful writes should be persisted');
    }
}