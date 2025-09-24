<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseOptimizationService;
use App\Services\CacheService;
use App\Services\MonitoringService;

class OptimizeSystem extends Command
{
    protected $signature = 'system:optimize {--force : Force optimization even if not needed}';
    protected $description = 'Optimize system performance and clean old data';

    public function __construct(
        private DatabaseOptimizationService $dbService,
        private CacheService $cacheService,
        private MonitoringService $monitoringService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🚀 Starting system optimization...');
        
        // Check system health first
        $health = $this->monitoringService->checkSystemHealth();
        $this->info("System status: {$health['status']}");
        
        if ($health['status'] === 'critical' && !$this->option('force')) {
            $this->error('System is in critical state. Use --force to proceed anyway.');
            return 1;
        }
        
        // Database optimization
        $this->info('🗄️  Optimizing database...');
        $dbResults = $this->dbService->optimizeQueries();
        $this->info('Database queries optimized');
        
        // Clean old data
        $this->info('🧹 Cleaning old data...');
        $cleaned = $this->dbService->cleanOldData();
        $this->info("Cleaned {$cleaned} old records");
        
        // Cache optimization
        $this->info('⚡ Optimizing cache...');
        $cacheStats = $this->cacheService->getCacheStats();
        $this->info("Cache hit rate: {$cacheStats['hit_rate']}%");
        
        // Performance analysis
        $this->info('📊 Analyzing performance...');
        $performance = $this->dbService->analyzePerformance();
        
        if (!empty($performance['slow_queries'])) {
            $this->warn('Found ' . count($performance['slow_queries']) . ' slow queries');
        }
        
        $this->info('✅ System optimization completed successfully!');
        
        return 0;
    }
}