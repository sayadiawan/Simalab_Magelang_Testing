<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SecurityCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up sensitive files and prepare for pentest';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will clear logs and cache. Continue?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }
        
        $this->info('🧹 Starting security cleanup...');
        
        // Clear application cache
        $this->info('Clearing application cache...');
        Artisan::call('cache:clear');
        $this->info('✓ Cache cleared');
        
        // Clear config cache
        $this->info('Clearing config cache...');
        Artisan::call('config:clear');
        $this->info('✓ Config cache cleared');
        
        // Clear route cache
        $this->info('Clearing route cache...');
        Artisan::call('route:clear');
        $this->info('✓ Route cache cleared');
        
        // Clear view cache
        $this->info('Clearing view cache...');
        Artisan::call('view:clear');
        $this->info('✓ View cache cleared');
        
        // Rotate log file
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile) && filesize($logFile) > 0) {
            $this->info('Rotating log file...');
            $backupName = storage_path('logs/laravel-' . date('Y-m-d-His') . '.log');
            if (copy($logFile, $backupName)) {
                file_put_contents($logFile, '');
                $this->info('✓ Log file rotated');
            }
        }
        
        // Remove temporary files
        $tempPaths = [
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];
        
        foreach ($tempPaths as $path) {
            if (is_dir($path)) {
                $this->info('Cleaning: ' . $path);
                $files = glob($path . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < (time() - 86400)) { // Older than 24 hours
                        unlink($file);
                    }
                }
            }
        }
        
        // Remove old log files (keep last 7 days)
        $logPath = storage_path('logs');
        $logFiles = glob($logPath . '/laravel-*.log');
        foreach ($logFiles as $logFile) {
            if (filemtime($logFile) < (time() - (7 * 86400))) {
                unlink($logFile);
                $this->info('Removed old log: ' . basename($logFile));
            }
        }
        
        $this->info('');
        $this->info('✓ Security cleanup completed!');
        $this->info('Next steps:');
        $this->line('  1. Run: php artisan security:audit');
        $this->line('  2. Ensure APP_DEBUG=false and APP_ENV=production in .env');
        $this->line('  3. Run: php artisan config:cache && php artisan route:cache');
        
        return 0;
    }
}

