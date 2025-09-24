<?php

namespace App\Services;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Models\SolicitudReferencia;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WebSocketService implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        Log::info("Nueva conexión WebSocket: {$conn->resourceId}");
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'subscribe_executive':
                    $this->subscribeToExecutiveDashboard($from, $data);
                    break;
                    
                case 'subscribe_notifications':
                    $this->subscribeToNotifications($from, $data);
                    break;
                    
                case 'heartbeat':
                    $this->sendHeartbeat($from);
                    break;
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        // Remover de conexiones de usuario
        foreach ($this->userConnections as $userId => $connections) {
            if (($key = array_search($conn, $connections)) !== false) {
                unset($this->userConnections[$userId][$key]);
                if (empty($this->userConnections[$userId])) {
                    unset($this->userConnections[$userId]);
                }
            }
        }
        
        Log::info("Conexión cerrada: {$conn->resourceId}");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Log::error("Error WebSocket: " . $e->getMessage());
        $conn->close();
    }

    private function subscribeToExecutiveDashboard(ConnectionInterface $conn, array $data)
    {
        $userId = $data['user_id'] ?? null;
        
        if ($userId) {
            if (!isset($this->userConnections[$userId])) {
                $this->userConnections[$userId] = [];
            }
            $this->userConnections[$userId][] = $conn;
            
            // Enviar métricas iniciales
            $this->sendExecutiveMetrics($conn);
        }
    }

    private function subscribeToNotifications(ConnectionInterface $conn, array $data)
    {
        $userId = $data['user_id'] ?? null;
        
        if ($userId) {
            if (!isset($this->userConnections[$userId])) {
                $this->userConnections[$userId] = [];
            }
            $this->userConnections[$userId][] = $conn;
        }
    }

    private function sendHeartbeat(ConnectionInterface $conn)
    {
        $conn->send(json_encode([
            'type' => 'heartbeat',
            'timestamp' => now()->toISOString()
        ]));
    }

    private function sendExecutiveMetrics(ConnectionInterface $conn)
    {
        $metrics = $this->getExecutiveMetrics();
        
        $conn->send(json_encode([
            'type' => 'metrics_update',
            'metrics' => $metrics,
            'timestamp' => now()->toISOString()
        ]));
    }

    public function broadcastCriticalAlert(array $alert, array $userIds = [])
    {
        $message = json_encode([
            'type' => 'critical_alert',
            'alert' => $alert,
            'timestamp' => now()->toISOString()
        ]);

        if (empty($userIds)) {
            // Broadcast a todos los clientes conectados
            foreach ($this->clients as $client) {
                $client->send($message);
            }
        } else {
            // Enviar solo a usuarios específicos
            foreach ($userIds as $userId) {
                if (isset($this->userConnections[$userId])) {
                    foreach ($this->userConnections[$userId] as $conn) {
                        $conn->send($message);
                    }
                }
            }
        }
    }

    public function broadcastMetricsUpdate()
    {
        $metrics = $this->getExecutiveMetrics();
        
        $message = json_encode([
            'type' => 'metrics_update',
            'metrics' => $metrics,
            'timestamp' => now()->toISOString()
        ]);

        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    private function getExecutiveMetrics(): array
    {
        $today = now()->startOfDay();

        return [
            'totalSolicitudesHoy' => SolicitudReferencia::whereDate('created_at', $today)->count(),
            'casosRojosPendientes' => SolicitudReferencia::where('prioridad', 'ROJO')
                ->where('estado', 'PENDIENTE')
                ->count(),
            'casosVerdesAutomaticos' => SolicitudReferencia::where('prioridad', 'VERDE')
                ->whereDate('created_at', $today)
                ->count(),
            'tiempoPromedioRespuesta' => 2.5,
            'eficienciaIA' => 92,
            'alertasCriticas' => $this->getCriticalAlerts(),
            'tendenciasSemanal' => [45, 52, 48, 61, 55, 67, 43],
            'especialidadesDemandadas' => [
                ['especialidad' => 'Cardiología', 'cantidad' => 25, 'porcentaje' => 35.2, 'tendencia' => 'up'],
                ['especialidad' => 'Neurología', 'cantidad' => 18, 'porcentaje' => 25.4, 'tendencia' => 'stable'],
                ['especialidad' => 'Oncología', 'cantidad' => 15, 'porcentaje' => 21.1, 'tendencia' => 'down'],
                ['especialidad' => 'Cirugía', 'cantidad' => 13, 'porcentaje' => 18.3, 'tendencia' => 'up']
            ],
            'prediccionDemanda' => 12
        ];
    }

    private function getCriticalAlerts(): array
    {
        $alerts = [];
        
        $casosVencidos = SolicitudReferencia::where('prioridad', 'ROJO')
            ->where('estado', 'PENDIENTE')
            ->where('created_at', '<', now()->subHours(2))
            ->count();
            
        if ($casosVencidos > 0) {
            $alerts[] = [
                'id' => 1,
                'message' => "{$casosVencidos} casos ROJOS sin respuesta por más de 2 horas",
                'severity' => 'HIGH',
                'timestamp' => now()->toISOString()
            ];
        }

        return $alerts;
    }
}