<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class SecurityAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit security settings before pentest';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔒 Security Audit for Pentest Preparation');
        $this->line('==========================================');
        
        $issues = [];
        $warnings = [];
        
        // Check APP_DEBUG
        if (config('app.debug')) {
            $issues[] = 'APP_DEBUG is enabled. Set to false in production.';
        } else {
            $this->info('✓ APP_DEBUG is disabled');
        }
        
        // Check APP_ENV
        if (config('app.env') !== 'production') {
            $warnings[] = 'APP_ENV is set to: ' . config('app.env') . '. Should be "production" for pentest.';
        } else {
            $this->info('✓ APP_ENV is set to production');
        }
        
        // Check session secure
        if (!config('session.secure')) {
            $issues[] = 'SESSION_SECURE_COOKIE is false. Should be true for HTTPS.';
        } else {
            $this->info('✓ Session cookies are secure');
        }
        
        // Check session same_site
        if (config('session.same_site') === null) {
            $warnings[] = 'SESSION_SAME_SITE is null. Should be "lax" or "strict".';
        } else {
            $this->info('✓ Session same_site is configured');
        }
        
        // Check storage permissions
        $storagePath = storage_path();
        if (!is_writable($storagePath)) {
            $issues[] = 'Storage directory is not writable';
        } else {
            $this->info('✓ Storage directory is writable');
        }
        
        // Check log file size
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logSize = filesize($logFile);
            if ($logSize > 50 * 1024 * 1024) { // 50MB
                $warnings[] = 'Log file is large (' . round($logSize / 1024 / 1024, 2) . 'MB). Consider rotating.';
            } else {
                $this->info('✓ Log file size is acceptable');
            }
        }
        
        // Check for .env file exposure
        $envPath = base_path('.env');
        if (file_exists($envPath) && is_readable($envPath)) {
            $warnings[] = '.env file exists and is readable. Ensure it is not publicly accessible.';
        }
        
        // Check public directory for sensitive files
        $publicPath = public_path();
        $sensitivePatterns = ['*.env', '*.log', '*.sql', '*.bak', '*.backup'];
        foreach ($sensitivePatterns as $pattern) {
            $files = glob($publicPath . '/' . $pattern);
            if (!empty($files)) {
                $issues[] = 'Sensitive files found in public directory: ' . implode(', ', $files);
            }
        }
        
        // Summary
        $this->line('');
        $this->line('==========================================');
        $this->info('Audit Summary:');
        
        if (empty($issues) && empty($warnings)) {
            $this->info('✓ No issues found. System is ready for pentest.');
            return 0;
        }
        
        if (!empty($issues)) {
            $this->error('Issues found:');
            foreach ($issues as $issue) {
                $this->error('  ✗ ' . $issue);
            }
        }
        
        if (!empty($warnings)) {
            $this->warn('Warnings:');
            foreach ($warnings as $warning) {
                $this->warn('  ⚠ ' . $warning);
            }
        }
        
        return 1;
    }
}

