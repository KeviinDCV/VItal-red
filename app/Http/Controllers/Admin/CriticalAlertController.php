<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CriticalAlert;
use App\Services\CriticalAlertService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CriticalAlertController extends Controller
{
    protected $alertService;

    public function __construct(CriticalAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function index()
    {
        $alerts = CriticalAlert::active()->latest()->paginate(20);
        
        return Inertia::render('admin/CriticalAlertsMonitor', [
            'alerts' => $alerts
        ]);
    }

    public function acknowledge(CriticalAlert $alert)
    {
        $alert->acknowledge(auth()->id());
        
        return response()->json(['success' => true]);
    }

    public function resolve(CriticalAlert $alert)
    {
        $this->alertService->resolveAlert($alert, auth()->id());
        
        return response()->json(['success' => true]);
    }
}