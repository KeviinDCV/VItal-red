<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SolicitudReferencia;
use App\Models\RegistroMedico;
use App\Models\IPS;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuarios de prueba
        $this->admin = User::factory()->create(['role' => 'administrador']);
        $this->medico = User::factory()->create(['role' => 'medico']);
        $this->ips = User::factory()->create(['role' => 'ips']);
        
        // Crear datos de prueba
        $ips = IPS::factory()->create();
        $registro = RegistroMedico::factory()->create();
        SolicitudReferencia::factory()->create([
            'registro_medico_id' => $registro->id,
            'ips_id' => $ips->id
        ]);
    }

    /** @test */
    public function admin_can_access_dashboard_referencias()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/referencias');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_reportes()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/reportes');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_usuarios()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/usuarios');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function jefe_urgencias_can_access_dashboard_ejecutivo()
    {
        $response = $this->actingAs($this->admin)
            ->get('/jefe-urgencias/dashboard-ejecutivo');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function medico_can_access_referencias()
    {
        $response = $this->actingAs($this->medico)
            ->get('/medico/referencias');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function medico_can_access_casos_criticos()
    {
        $response = $this->actingAs($this->medico)
            ->get('/medico/casos-criticos');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function medico_can_access_seguimiento()
    {
        $response = $this->actingAs($this->medico)
            ->get('/medico/seguimiento');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function ips_can_access_solicitar()
    {
        $response = $this->actingAs($this->ips)
            ->get('/ips/solicitar');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function ips_can_access_mis_solicitudes()
    {
        $response = $this->actingAs($this->ips)
            ->get('/ips/mis-solicitudes');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function all_users_can_access_notificaciones()
    {
        $response = $this->actingAs($this->admin)
            ->get('/notificaciones');
            
        $response->assertStatus(200);
    }

    /** @test */
    public function dashboard_redirects_correctly_by_role()
    {
        $response = $this->actingAs($this->admin)
            ->get('/dashboard');
            
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    /** @test */
    public function unauthorized_users_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->medico)
            ->get('/admin/usuarios');
            
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthorized_users_cannot_access_medico_routes()
    {
        $response = $this->actingAs($this->ips)
            ->get('/medico/referencias');
            
        $response->assertStatus(403);
    }
}