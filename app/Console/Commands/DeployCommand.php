<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DeployCommand extends Command
{
    protected $signature = 'deploy:production {--rollback : Rollback to previous version}';
    protected $description = 'Deploy application to production with blue-green strategy';

    public function handle()
    {
        if ($this->option('rollback')) {
            return $this->rollback();
        }

        $this->info('🚀 Starting production deployment...');

        // Pre-deployment checks
        if (!$this->preDeploymentChecks()) {
            $this->error('❌ Pre-deployment checks failed');
            return 1;
        }

        // Run deployment
        $this->runDeployment();

        // Post-deployment verification
        if ($this->postDeploymentChecks()) {
            $this->info('✅ Deployment completed successfully!');
            return 0;
        } else {
            $this->error('❌ Post-deployment checks failed');
            return 1;
        }
    }

    private function preDeploymentChecks(): bool
    {
        $this->info('🔍 Running pre-deployment checks...');

        // Check database connection
        try {
            \DB::select('SELECT 1');
            $this->info('✅ Database connection OK');
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed');
            return false;
        }

        // Check Redis connection
        try {
            \Redis::ping();
            $this->info('✅ Redis connection OK');
        } catch (\Exception $e) {
            $this->error('❌ Redis connection failed');
            return false;
        }

        // Check external services
        if (!$this->checkExternalServices()) {
            return false;
        }

        return true;
    }

    private function checkExternalServices(): bool
    {
        $services = [
            'Gemini AI' => config('services.gemini.base_url'),
            'HIS' => config('services.his.base_url'),
            'LAB' => config('services.lab.base_url'),
            'PACS' => config('services.pacs.base_url')
        ];

        foreach ($services as $name => $url) {
            try {
                $response = Http::timeout(5)->get($url . '/health');
                if ($response->successful()) {
                    $this->info("✅ {$name} service OK");
                } else {
                    $this->warn("⚠️ {$name} service not responding");
                }
            } catch (\Exception $e) {
                $this->warn("⚠️ {$name} service check failed");
            }
        }

        return true;
    }

    private function runDeployment(): void
    {
        $this->info('📦 Running deployment steps...');

        // Clear caches
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->info('✅ Caches cleared and rebuilt');

        // Run migrations
        $this->call('migrate', ['--force' => true]);
        $this->info('✅ Database migrations completed');

        // Optimize autoloader
        $this->info('🔧 Optimizing autoloader...');
        exec('composer dump-autoload --optimize');

        // Restart queue workers
        $this->call('queue:restart');
        $this->info('✅ Queue workers restarted');
    }

    private function postDeploymentChecks(): bool
    {
        $this->info('🔍 Running post-deployment checks...');

        // Health check
        try {
            $response = Http::get(config('app.url') . '/health');
            if ($response->successful()) {
                $this->info('✅ Application health check passed');
            } else {
                $this->error('❌ Application health check failed');
                return false;
            }
        } catch (\Exception $e) {
            $this->error('❌ Health check request failed');
            return false;
        }

        // Performance check
        $start = microtime(true);
        try {
            Http::get(config('app.url') . '/dashboard');
            $responseTime = (microtime(true) - $start) * 1000;
            
            if ($responseTime < 2000) {
                $this->info("✅ Performance check passed ({$responseTime}ms)");
            } else {
                $this->warn("⚠️ Slow response time ({$responseTime}ms)");
            }
        } catch (\Exception $e) {
            $this->error('❌ Performance check failed');
            return false;
        }

        return true;
    }

    private function rollback(): int
    {
        $this->info('🔄 Starting rollback process...');

        // Execute rollback script
        $output = shell_exec('cd deploy && ./blue-green-deploy.sh rollback 2>&1');
        
        if (strpos($output, 'Rollback completed successfully') !== false) {
            $this->info('✅ Rollback completed successfully');
            return 0;
        } else {
            $this->error('❌ Rollback failed');
            $this->line($output);
            return 1;
        }
    }
}