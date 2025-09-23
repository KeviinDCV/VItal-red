<?php

namespace Tests\Security;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sql_injection_protection()
    {
        $user = User::factory()->create(['role' => 'administrador']);
        
        // Intentar SQL injection en búsqueda
        $maliciousInput = "'; DROP TABLE users; --";
        
        $response = $this->actingAs($user)
            ->get('/admin/usuarios?search=' . urlencode($maliciousInput));
        
        $response->assertStatus(200);
        
        // Verificar que la tabla users sigue existiendo
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_xss_protection()
    {
        $user = User::factory()->create(['role' => 'medico']);
        
        // Intentar XSS en formulario
        $xssPayload = '<script>alert("XSS")</script>';
        
        $response = $this->actingAs($user)
            ->post('/medico/ingresar-registro', [
                'nombre' => $xssPayload,
                'apellidos' => 'Test',
                'numero_identificacion' => '12345678',
                'tipo_identificacion' => 'cc',
                'fecha_nacimiento' => '1990-01-01',
                'sexo' => 'masculino'
            ]);
        
        // El sistema debe escapar el contenido
        $this->assertDatabaseMissing('registros_medicos', [
            'nombre' => $xssPayload
        ]);
    }

    public function test_csrf_protection()
    {
        $user = User::factory()->create(['role' => 'administrador']);
        
        // Intentar request sin token CSRF
        $response = $this->post('/admin/usuarios', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'medico'
        ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }

    public function test_authentication_required()
    {
        // Intentar acceder a rutas protegidas sin autenticación
        $protectedRoutes = [
            '/dashboard',
            '/admin/usuarios',
            '/medico/ingresar-registro',
            '/ips/solicitar'
        ];
        
        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $this->assertTrue(
                $response->status() === 302 || $response->status() === 401,
                "Route {$route} should require authentication"
            );
        }
    }

    public function test_authorization_by_role()
    {
        $medico = User::factory()->create(['role' => 'medico']);
        $ips = User::factory()->create(['role' => 'ips']);
        
        // Médico no debe acceder a rutas de admin
        $response = $this->actingAs($medico)
            ->get('/admin/usuarios');
        $response->assertStatus(403);
        
        // IPS no debe acceder a rutas de médico
        $response = $this->actingAs($ips)
            ->get('/medico/ingresar-registro');
        $response->assertStatus(403);
    }

    public function test_password_security()
    {
        // Verificar que las contraseñas se hashean
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);
        
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_session_security()
    {
        $user = User::factory()->create();
        
        // Login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $this->assertAuthenticated();
        
        // Logout debe invalidar sesión
        $response = $this->post('/logout');
        $this->assertGuest();
    }

    public function test_file_upload_security()
    {
        $user = User::factory()->create(['role' => 'medico']);
        
        // Intentar subir archivo malicioso
        $maliciousFile = \Illuminate\Http\UploadedFile::fake()->create('malicious.php', 100);
        
        $response = $this->actingAs($user)
            ->post('/medico/ai/extract-patient-data', [
                'file' => $maliciousFile
            ]);
        
        // Debe rechazar archivos no permitidos
        $response->assertStatus(422);
    }

    public function test_rate_limiting()
    {
        $user = User::factory()->create();
        
        // Intentar múltiples logins fallidos
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password'
            ]);
        }
        
        // Debe activar rate limiting
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password'
        ]);
        
        $this->assertEquals(429, $response->status());
    }

    public function test_sensitive_data_exposure()
    {
        $user = User::factory()->create(['role' => 'medico']);
        
        $response = $this->actingAs($user)
            ->getJson('/api/users/' . $user->id);
        
        $data = $response->json();
        
        // No debe exponer información sensible
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
    }
}