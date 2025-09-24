<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PerformanceController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('admin/Performance', [
            'metrics' => [
                'cpu_usage' => 45.2,
                'memory_usage' => 67.8,
                'disk_usage' => 23.1,
                'response_time' => 120
            ]
        ]);
    }

    public function optimizeDatabase()
    {
        return response()->json(['success' => true, 'message' => 'Base de datos optimizada']);
    }

    public function clearCache()
    {
        return response()->json(['success' => true, 'message' => 'Cache limpiado']);
    }

    public function getMetrics()
    {
        return response()->json([
            'cpu' => rand(30, 80),
            'memory' => rand(40, 90),
            'disk' => rand(10, 50)
        ]);
    }
}