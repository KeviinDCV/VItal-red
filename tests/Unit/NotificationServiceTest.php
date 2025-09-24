<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WebSocketService;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebSocketService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new WebSocketService();
    }

    public function test_creates_notification_for_user()
    {
        $user = User::factory()->create();
        
        $notification = $this->notificationService->createNotification([
            'user_id' => $user->id,
            'tipo' => 'caso_critico',
            'titulo' => 'Caso Crítico',
            'mensaje' => 'Nuevo caso ROJO requiere atención',
            'prioridad' => 'alta'
        ]);

        $this->assertInstanceOf(Notificacion::class, $notification);
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('caso_critico', $notification->tipo);
        $this->assertFalse($notification->leida);
    }

    public function test_sends_broadcast_notification()
    {
        Event::fake();

        $this->notificationService->broadcastNotification([
            'tipo' => 'system_alert',
            'titulo' => 'Alerta del Sistema',
            'mensaje' => 'Mantenimiento programado',
            'prioridad' => 'media'
        ]);

        Event::assertDispatched(\App\Events\NuevaNotificacion::class);
    }

    public function test_marks_notification_as_read()
    {
        $user = User::factory()->create();
        $notification = Notificacion::factory()->create([
            'user_id' => $user->id,
            'leida' => false
        ]);

        $result = $this->notificationService->markAsRead($notification->id);

        $this->assertTrue($result);
        $notification->refresh();
        $this->assertTrue($notification->leida);
        $this->assertNotNull($notification->leida_en);
    }

    public function test_gets_unread_notifications_count()
    {
        $user = User::factory()->create();
        Notificacion::factory()->count(3)->create([
            'user_id' => $user->id,
            'leida' => false
        ]);
        Notificacion::factory()->count(2)->create([
            'user_id' => $user->id,
            'leida' => true
        ]);

        $count = $this->notificationService->getUnreadCount($user->id);

        $this->assertEquals(3, $count);
    }

    public function test_filters_notifications_by_priority()
    {
        $user = User::factory()->create();
        Notificacion::factory()->create([
            'user_id' => $user->id,
            'prioridad' => 'alta'
        ]);
        Notificacion::factory()->create([
            'user_id' => $user->id,
            'prioridad' => 'baja'
        ]);

        $highPriority = $this->notificationService->getNotificationsByPriority($user->id, 'alta');

        $this->assertCount(1, $highPriority);
        $this->assertEquals('alta', $highPriority->first()->prioridad);
    }
}