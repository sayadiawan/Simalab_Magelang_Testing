<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupGoogleCleanup extends Command
{
    protected $signature = 'backup:google-cleanup';
    protected $description = 'Clean up old backups from Google Drive';

    public function handle()
    {
        $this->info('Cleaning up old backups from Google Drive...');
        
        try {
            $backupService = app(BackupService::class);
            $backupService->cleanupOldBackups();
            
            $this->info('Google Drive backup cleanup completed!');
            
        } catch (\Exception $e) {
            $this->error('Google Drive backup cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }
} 