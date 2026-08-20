<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class TestBackup extends Command
{
    protected $signature = 'backup:test';
    protected $description = 'Test backup functionality (database only)';

    public function handle()
    {
        $this->info('Testing backup functionality (database only)...');
        
        try {
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupName = "test-backup-{$timestamp}";
            
            // Create backup directory if it doesn't exist
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            // Create temporary directory
            $tempPath = storage_path('app/backup-temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $this->info('Testing database backup...');
            $this->testDatabaseBackup($backupName);
            
            $this->info('Testing zip creation...');
            $zipPath = $this->testZipCreation($backupName);
            
            $this->info('Backup test completed successfully!');
            $this->info('Test backup location: ' . $zipPath);
            
            // Cleanup test files
            $this->cleanupTestFiles();
            
        } catch (\Exception $e) {
            $this->error('Backup test failed: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function testDatabaseBackup($backupName)
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        
        $dumpFile = storage_path("app/backup-temp/{$backupName}.sql");
        
        $command = "mysqldump -h {$dbHost} -u {$dbUser}";
        if ($dbPass) {
            $command .= " -p{$dbPass}";
        }
        $command .= " {$dbName} > {$dumpFile}";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Database backup test failed");
        }
        
        $this->info('Database backup test passed');
    }
    
    private function testZipCreation($backupName)
    {
        $zipPath = storage_path("app/backups/{$backupName}.zip");
        $tempPath = storage_path('app/backup-temp');
        
        $command = "cd {$tempPath} && zip -r {$zipPath} .";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Zip creation test failed");
        }
        
        $this->info('Zip creation test passed');
        
        return $zipPath;
    }
    
    private function cleanupTestFiles()
    {
        $tempPath = storage_path('app/backup-temp');
        
        if (file_exists($tempPath)) {
            $command = "rm -rf {$tempPath}/*";
            exec($command);
            $this->info('Test files cleaned up');
        }
    }
} 