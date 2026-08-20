<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckBackupRequirements extends Command
{
    protected $signature = 'backup:check-requirements';
    protected $description = 'Check backup system requirements';

    public function handle()
    {
        $this->info('Checking Backup System Requirements');
        $this->info('==================================');
        
        $this->checkMysqldump();
        $this->checkZip();
        $this->checkDirectories();
        $this->checkDatabaseConnection();
        
        $this->info("\nRequirements check completed!");
    }
    
    private function checkMysqldump()
    {
        $this->info("\n1. Checking mysqldump...");
        
        $mysqldumpPaths = [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/mysql/bin/mysqldump',
            '/opt/lampp/bin/mysqldump',
            '/xampp/mysql/bin/mysqldump',
            'mysqldump'
        ];
        
        $found = false;
        foreach ($mysqldumpPaths as $path) {
            if ($path === 'mysqldump') {
                // Check if mysqldump is in PATH
                $output = [];
                exec('which mysqldump 2>/dev/null', $output, $returnCode);
                if ($returnCode === 0 && !empty($output)) {
                    $this->info("   ✅ mysqldump found in PATH: " . $output[0]);
                    $found = true;
                    break;
                }
            } else {
                if (file_exists($path)) {
                    $this->info("   ✅ mysqldump found: {$path}");
                    $found = true;
                    break;
                }
            }
        }
        
        if (!$found) {
            $this->error("   ❌ mysqldump not found!");
            $this->warn("   💡 To install MySQL client:");
            $this->warn("      Ubuntu/Debian: sudo apt-get install mysql-client");
            $this->warn("      CentOS/RHEL: sudo yum install mysql");
            $this->warn("      Or install XAMPP/LAMPP for local development");
        }
    }
    
    private function checkZip()
    {
        $this->info("\n2. Checking zip command...");
        
        $output = [];
        exec('which zip 2>/dev/null', $output, $returnCode);
        
        if ($returnCode === 0 && !empty($output)) {
            $this->info("   ✅ zip found: " . $output[0]);
        } else {
            $this->error("   ❌ zip not found!");
            $this->warn("   💡 To install zip:");
            $this->warn("      Ubuntu/Debian: sudo apt-get install zip");
            $this->warn("      CentOS/RHEL: sudo yum install zip");
        }
    }
    
    private function checkDirectories()
    {
        $this->info("\n3. Checking directories...");
        
        $directories = [
            'storage/app/backups' => storage_path('app/backups'),
            'storage/app/backup-temp' => storage_path('app/backup-temp'),
        ];
        
        foreach ($directories as $name => $path) {
            if (file_exists($path)) {
                if (is_writable($path)) {
                    $this->info("   ✅ {$name}: exists and writable");
                } else {
                    $this->error("   ❌ {$name}: exists but not writable");
                }
            } else {
                $this->warn("   ⚠️  {$name}: does not exist (will be created automatically)");
            }
        }
    }
    
    private function checkDatabaseConnection()
    {
        $this->info("\n4. Checking database connection...");
        
        try {
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbHost = config('database.connections.mysql.host');
            
            $this->info("   Database: {$dbName}");
            $this->info("   Host: {$dbHost}");
            $this->info("   User: {$dbUser}");
            
            // Test connection
            \DB::connection()->getPdo();
            $this->info("   ✅ Database connection successful");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Database connection failed: " . $e->getMessage());
        }
    }
} 