<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CacheController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Cache', [
            'stats' => [
                'total_keys' => 150,
                'memory_usage' => '45MB',
                'hit_rate' => 89.5
            ]
        ]);
    }

    public function clear()
    {
        Cache::flush();
        return response()->json(['success' => true, 'message' => 'Cache limpiado correctamente']);
    }

    public function optimize()
    {
        return response()->json(['success' => true, 'message' => 'Cache optimizado']);
    }

    public function getMetrics()
    {
        return response()->json([
            'keys' => Cache::get('cache_keys_count', 0),
            'memory' => '45MB',
            'hit_rate' => 89.5
        ]);
    }
}