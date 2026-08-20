<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use Google\Http\MediaFileUpload;

class BackupService
{
    /**
     * Create a new backup
     */
    public function createBackup($name = null)
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupName = $name ?: "kab-magelang-labkes-backup-{$timestamp}";
            
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
            
            // Database backup only
            $this->backupDatabase($backupName);
            
            // Create zip archive
            $zipPath = $this->createZipArchive($backupName);
            
            // Upload to Google Drive
            $this->uploadToGoogleDrive($zipPath);
            
            // Delete all local backup files after successful upload
            $this->deleteAllLocalBackups();
            
            // Cleanup temporary files
            $this->cleanupTempFiles();
            
            Log::info("Backup created successfully: {$backupName}");
            
            return $zipPath;
            
        } catch (\Exception $e) {
            Log::error("Backup failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Backup database
     */
    private function backupDatabase($backupName)
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        
        $dumpFile = storage_path("app/backup-temp/{$backupName}.sql");
        
        // Try to find mysqldump in common locations
        $mysqldumpPaths = [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/mysql/bin/mysqldump',
            '/opt/lampp/bin/mysqldump',
            '/xampp/mysql/bin/mysqldump',
            'mysqldump' // fallback to PATH
        ];
        
        $mysqldump = null;
        foreach ($mysqldumpPaths as $path) {
            if (file_exists($path) || $path === 'mysqldump') {
                $mysqldump = $path;
                break;
            }
        }
        
        if (!$mysqldump) {
            throw new \Exception("mysqldump not found. Please install MySQL client or specify correct path.");
        }
        
        // Build command with proper escaping
        $command = escapeshellcmd($mysqldump);
        $command .= " -h " . escapeshellarg($dbHost);
        $command .= " -u " . escapeshellarg($dbUser);
        
        if ($dbPass) {
            $command .= " -p" . escapeshellarg($dbPass);
        }
        
        $command .= " " . escapeshellarg($dbName);
        $command .= " > " . escapeshellarg($dumpFile);
        
        // Execute command and capture output
        $output = [];
        $returnCode = 0;
        
        exec($command . " 2>&1", $output, $returnCode);
        
        if ($returnCode !== 0) {
            $errorMessage = "Database backup failed. Return code: {$returnCode}. ";
            if (!empty($output)) {
                $errorMessage .= "Error: " . implode("\n", $output);
            }
            throw new \Exception($errorMessage);
        }
        
        // Check if file was created and has content
        if (!file_exists($dumpFile) || filesize($dumpFile) === 0) {
            throw new \Exception("Database backup file was not created or is empty");
        }
    }
    
    /**
     * Create zip archive
     */
    private function createZipArchive($backupName)
    {
        $zipPath = storage_path("app/backups/{$backupName}.zip");
        $tempPath = storage_path('app/backup-temp');
        
        // $command = "cd {$tempPath} && zip -r {$zipPath} .";
        $command = "cd {$tempPath} && zip -r -9 {$zipPath} .";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Zip creation failed");
        }
        
        return $zipPath;
    }
    
    /**
     * Upload to Google Drive
     */
    // private function uploadToGoogleDrive($filePath)
    // {
    //     try {
    //         // Check if Google Drive credentials are configured
    //         if (!config('services.google.client_id') || !config('services.google.folder_id')) {
    //             Log::info("Google Drive credentials not configured, skipping upload");
    //             return;
    //         }
            
    //         $drive = app('google.drive');
    //         $fileName = basename($filePath);
            
    //         $fileMetadata = new \Google_Service_Drive_DriveFile([
    //             'name' => $fileName,
    //             'parents' => [config('services.google.folder_id')]
    //         ]);
            
    //         $content = file_get_contents($filePath);
    //         $file = $drive->files->create($fileMetadata, [
    //             'data' => $content,
    //             'mimeType' => 'application/zip',
    //             'uploadType' => 'multipart'
    //         ]);
            
    //         Log::info("Uploaded to Google Drive: {$fileName} (ID: {$file->getId()})");
            
    //         // Cleanup old Google Drive backups (keep only 2 latest)
    //         $this->cleanupGoogleDriveBackups(2);
            
    //     } catch (\Exception $e) {
    //         Log::warning("Google Drive upload failed: " . $e->getMessage());
    //         // Don't throw exception, just log warning
    //     }
    // }

    

    // NOTE: jika Anda pakai namespace lama, \Google_Service_Drive_* masih valid.
    // Kalau pakai namespace baru: use Google\Service\Drive; use Google\Service\Drive\DriveFile;

    private function uploadToGoogleDrive(string $filePath): void
    {
        try {
            // 0) Validasi config minimal
            $folderId = config('services.google.folder_id');
            if (empty(config('services.google.client_id')) || empty($folderId)) {
                Log::info("Google Drive credentials not configured, skipping upload");
                return;
            }

            // 1) Ambil service dari container (punya Anda: app('google.drive'))
            /** @var \Google_Service_Drive $drive */
            $drive = app('google.drive');
            $client = $drive->getClient(); // ambil Google\Client untuk setDefer()

            $fileName = basename($filePath);
            $fileSize = @filesize($filePath);
            if ($fileSize === false) {
                throw new \RuntimeException("Cannot read file size: {$filePath}");
            }

            // 2) Wajib: aktifkan deferred agar bisa pakai MediaFileUpload per-chunk
            $client->setDefer(true);

            // 3) Siapkan metadata (tetap sama)
            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name'    => $fileName,
                'parents' => [$folderId],
            ]);

            // 4) Buat request resumable (JANGAN isi 'data' di sini)
            $mimeType = mime_content_type($filePath) ?: 'application/zip';
            $request = $drive->files->create($fileMetadata, [
                'mimeType'   => $mimeType,
                'uploadType' => 'resumable',
                'fields'     => 'id,name,webViewLink,webContentLink',
            ]);

            // 5) Tentukan ukuran chunk kecil agar hemat RAM (5 MB umumnya aman)
            $chunkSizeBytes = 5 * 1024 * 1024;

            // 6) Siapkan MediaFileUpload untuk streaming per-chunk
            $media = new MediaFileUpload(
                $client,
                $request,
                $mimeType,
                null,          // jangan kirim seluruh data!
                true,          // resumable = true
                $chunkSizeBytes
            );
            $media->setFileSize($fileSize);

            // 7) Stream dari file handle (bukan file_get_contents)
            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                throw new \RuntimeException("fopen() failed: {$filePath}");
            }

            $status = false;
            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                if ($chunk === false) {
                    fclose($handle);
                    throw new \RuntimeException("fread() failed during upload: {$filePath}");
                }
                // Kirim chunk – tidak menumpuk di memori
                $status = $media->nextChunk($chunk);
            }
            fclose($handle);

            // 8) Kembalikan client ke mode normal
            $client->setDefer(false);

            Log::info("Uploaded to Google Drive: {$fileName} (ID: {$status->id})");

            // 9) (opsional) bersihkan backup lama
            $this->cleanupGoogleDriveBackups(2);

        } catch (\Throwable $e) {
            // Pastikan defer dimatikan jika error terjadi di tengah
            try {
                if (isset($drive) && isset($client)) {
                    $client->setDefer(false);
                }
            } catch (\Throwable $ignore) {}
            Log::warning("Google Drive upload failed: " . $e->getMessage());
            // Jangan throw—sesuai perilaku Anda sebelumnya
        }
    }

    
    /**
     * Cleanup temporary files
     */
    private function cleanupTempFiles()
    {
        $tempPath = storage_path('app/backup-temp');
        
        if (file_exists($tempPath)) {
            $command = "rm -rf {$tempPath}/*";
            exec($command);
        }
    }
    
    /**
     * Delete all local backup files
     */
    private function deleteAllLocalBackups()
    {
        $backupPath = storage_path('app/backups');
        
        if (!file_exists($backupPath)) {
            return;
        }
        
        $files = glob($backupPath . '/*.zip');
        
        foreach ($files as $file) {
            unlink($file);
            Log::info("Deleted local backup: " . basename($file));
        }
        
        Log::info("All local backup files deleted after successful upload to Google Drive");
    }
    
    /**
     * Cleanup old Google Drive backups (keep only specified number of latest backups)
     */
    private function cleanupGoogleDriveBackups($keepCount = 2)
    {
        try {
            // Check if Google Drive credentials are configured
            if (!config('services.google.client_id') || !config('services.google.folder_id')) {
                Log::info("Google Drive credentials not configured, skipping cleanup");
                return;
            }
            
            $drive = app('google.drive');
            $folderId = config('services.google.folder_id');
            
            $results = $drive->files->listFiles([
                'q' => "'{$folderId}' in parents and trashed=false",
                'fields' => 'files(id,name,createdTime)',
                'orderBy' => 'createdTime desc'
            ]);
            
            $files = $results->getFiles();
            
            if (count($files) <= $keepCount) {
                return;
            }
            
            // Delete old files (keep only the latest $keepCount files)
            $filesToDelete = array_slice($files, $keepCount);
            
            foreach ($filesToDelete as $file) {
                $drive->files->delete($file->getId());
                Log::info("Deleted old Google Drive backup: {$file->getName()}");
            }
            
        } catch (\Exception $e) {
            Log::warning("Google Drive cleanup failed: " . $e->getMessage());
            // Don't throw exception, just log warning
        }
    }
    
    /**
     * Cleanup old backups (legacy method for command compatibility)
     */
    public function cleanupOldBackups()
    {
        try {
            // Cleanup Google Drive backups (keep only 2 latest)
            $this->cleanupGoogleDriveBackups(2);
            
            Log::info("Backup cleanup completed");
            
        } catch (\Exception $e) {
            Log::error("Backup cleanup failed: " . $e->getMessage());
            throw $e;
        }
    }
} 