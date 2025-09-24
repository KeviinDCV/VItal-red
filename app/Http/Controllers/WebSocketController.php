<?php

namespace App\Http\Controllers;

use App\Services\WebSocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebSocketController extends Controller
{
    protected $webSocketService;

    public function __construct(WebSocketService $webSocketService)
    {
        $this->webSocketService = $webSocketService;
    }

    public function connect(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'channel' => 'required|string',
            'token' => 'required|string'
        ]);

        try {
            $connection = $this->webSocketService->establishConnection($validated);
            
            return response()->json([
                'success' => true,
                'connection_id' => $connection['id'],
                'channels' => $connection['channels']
            ]);
        } catch (\Exception $e) {
            Log::error('WebSocket connection failed: ' . $e->getMessage());
            return response()->json(['error' => 'Connection failed'], 500);
        }
    }

    public function disconnect(Request $request)
    {
        $connectionId = $request->get('connection_id');
        
        $this->webSocketService->closeConnection($connectionId);
        
        return response()->json(['success' => true]);
    }

    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|string',
            'event' => 'required|string',
            'data' => 'required|array'
        ]);

        $this->webSocketService->broadcast(
            $validated['channel'],
            $validated['event'],
            $validated['data']
        );

        return response()->json(['success' => true]);
    }

    public function getActiveConnections()
    {
        $connections = $this->webSocketService->getActiveConnections();
        
        return response()->json([
            'total' => count($connections),
            'connections' => $connections
        ]);
    }
}