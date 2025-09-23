<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomaticResponse;
use App\Models\ResponseTemplate;
use App\Services\AutoResponseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutomaticResponseController extends Controller
{
    protected $autoResponseService;

    public function __construct(AutoResponseService $autoResponseService)
    {
        $this->autoResponseService = $autoResponseService;
    }

    public function index()
    {
        $responses = AutomaticResponse::with(['solicitud', 'template'])
            ->latest()
            ->paginate(20);

        $templates = ResponseTemplate::active()->get();
        
        $stats = [
            'total_sent' => AutomaticResponse::count(),
            'sent_today' => AutomaticResponse::whereDate('created_at', today())->count(),
            'success_rate' => $this->autoResponseService->getSuccessRate(),
            'avg_response_time' => $this->autoResponseService->getAverageResponseTime()
        ];

        return Inertia::render('admin/AutomaticResponseCenter', [
            'responses' => $responses,
            'templates' => $templates,
            'stats' => $stats
        ]);
    }

    public function createTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string',
            'priority' => 'required|in:ROJO,VERDE',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'array'
        ]);

        $template = ResponseTemplate::create($validated);

        return response()->json([
            'success' => true,
            'template' => $template
        ]);
    }

    public function updateTemplate(Request $request, ResponseTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'specialty' => 'string',
            'priority' => 'in:ROJO,VERDE',
            'subject' => 'string|max:255',
            'content' => 'string',
            'variables' => 'array',
            'active' => 'boolean'
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'template' => $template
        ]);
    }

    public function testTemplate(Request $request, ResponseTemplate $template)
    {
        $testData = $request->validate([
            'patient_name' => 'required|string',
            'specialty' => 'required|string',
            'ips_name' => 'required|string'
        ]);

        $preview = $this->autoResponseService->previewResponse($template, $testData);

        return response()->json([
            'preview' => $preview
        ]);
    }

    public function getMetrics(Request $request)
    {
        $period = $request->get('period', '7d');
        
        return response()->json([
            'metrics' => $this->autoResponseService->getMetrics($period)
        ]);
    }
}