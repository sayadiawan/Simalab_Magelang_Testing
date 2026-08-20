<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupCleanup extends Command
{
    protected $signature = 'backup:cleanup';
    protected $description = 'Clean up old backups';

    public function handle()
    {
        $this->info('Cleaning up old backups...');
        
        try {
            $backupService = app(BackupService::class);
            $backupService->cleanupOldBackups();
            
            $this->info('Backup cleanup completed!');
            
        } catch (\Exception $e) {
            $this->error('Backup cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }
} 