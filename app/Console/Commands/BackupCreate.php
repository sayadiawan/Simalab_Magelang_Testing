<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupCreate extends Command
{
    protected $signature = 'backup:create {--name=}';
    protected $description = 'Create a new backup';

    public function handle()
    {
        $this->info('Creating backup...');
        
        try {
            $backupService = app(BackupService::class);
            $backupPath = $backupService->createBackup($this->option('name'));
            
            $this->info('Backup created successfully!');
            $this->info('Backup location: ' . $backupPath);
            
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }
} 