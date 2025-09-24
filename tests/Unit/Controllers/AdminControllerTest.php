<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Http\Controllers\Admin\UsuarioController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_users_list()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        User::factory()->count(5)->create();

        $this->actingAs($admin);
        
        $controller = new UsuarioController();
        $response = $controller->index();

        $this->assertEquals(200, $response->status());
    }

    public function test_store_creates_new_user()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $this->actingAs($admin);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'medico'
        ];

        $request = Request::create('/admin/usuarios', 'POST', $userData);
        $controller = new UsuarioController();
        
        $response = $controller->store($request);

        $this->assertEquals(302, $response->status());
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'medico'
        ]);
    }

    public function test_update_modifies_existing_user()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $user = User::factory()->create(['name' => 'Old Name']);
        
        $this->actingAs($admin);

        $updateData = [
            'name' => 'New Name',
            'email' => $user->email,
            'role' => $user->role
        ];

        $request = Request::create("/admin/usuarios/{$user->id}", 'PUT', $updateData);
        $controller = new UsuarioController();
        
        $response = $controller->update($request, $user);

        $this->assertEquals(302, $response->status());
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name'
        ]);
    }

    public function test_destroy_deletes_user()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $user = User::factory()->create();
        
        $this->actingAs($admin);

        $controller = new UsuarioController();
        $response = $controller->destroy($user);

        $this->assertEquals(302, $response->status());
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_toggle_status_changes_user_status()
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $user = User::factory()->create(['activo' => true]);
        
        $this->actingAs($admin);

        $request = Request::create("/admin/usuarios/{$user->id}/toggle-status", 'PATCH');
        $controller = new UsuarioController();
        
        $response = $controller->toggleStatus($request, $user);

        $this->assertEquals(200, $response->status());
        $user->refresh();
        $this->assertFalse($user->activo);
    }
}