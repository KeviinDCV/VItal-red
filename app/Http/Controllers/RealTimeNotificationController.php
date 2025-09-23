<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\CriticalAlert;
use App\Services\RealTimeNotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RealTimeNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(RealTimeNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $notifications = Notificacion::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        $criticalAlerts = CriticalAlert::active()
            ->where('assigned_to', auth()->id())
            ->orWhere('role', auth()->user()->role)
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('notifications/RealTimeCenter', [
            'notifications' => $notifications,
            'criticalAlerts' => $criticalAlerts,
            'settings' => auth()->user()->notification_settings ?? []
        ]);
    }

    public function sendCriticalAlert(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'target_role' => 'string',
            'target_user_id' => 'integer|exists:users,id',
            'action_required' => 'boolean',
            'expires_at' => 'date'
        ]);

        $alert = $this->notificationService->sendCriticalAlert($validated);

        return response()->json([
            'success' => true,
            'alert' => $alert
        ]);
    }

    public function markAsRead(Request $request, Notificacion $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['leida' => true, 'leida_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true, 'leida_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'critical_alerts_only' => 'boolean',
            'sound_enabled' => 'boolean',
            'notification_frequency' => 'in:immediate,hourly,daily'
        ]);

        auth()->user()->update([
            'notification_settings' => $validated
        ]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $count = Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function acknowledgeAlert(Request $request, CriticalAlert $alert)
    {
        $alert->update([
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
            'status' => 'acknowledged'
        ]);

        $this->notificationService->broadcastAlertAcknowledged($alert);

        return response()->json(['success' => true]);
    }
}