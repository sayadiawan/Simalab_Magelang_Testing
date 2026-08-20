<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupGoogleDrive extends Command
{
    protected $signature = 'backup:setup-google-drive';
    protected $description = 'Setup Google Drive credentials for backup';

    public function handle()
    {
        $this->info('Google Drive Setup for Backup System');
        $this->info('=====================================');
        
        $this->info("\nLangkah-langkah setup Google Drive:");
        $this->info('1. Buka https://console.cloud.google.com/');
        $this->info('2. Buat project baru atau pilih yang sudah ada');
        $this->info('3. Aktifkan Google Drive API');
        $this->info('4. Buat OAuth 2.0 credentials');
        $this->info('5. Download credentials dan dapatkan Client ID & Client Secret');
        $this->info('6. Buat folder di Google Drive untuk backup');
        $this->info('7. Ambil folder ID dari URL folder');
        $this->info('8. Generate refresh token menggunakan Google OAuth 2.0 Playground');
        
        $this->info("\nTambahkan ke file .env:");
        $this->info('GOOGLE_CLIENT_ID=your-client-id');
        $this->info('GOOGLE_CLIENT_SECRET=your-client-secret');
        $this->info('GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback');
        $this->info('GOOGLE_REFRESH_TOKEN=your-refresh-token');
        $this->info('GOOGLE_DRIVE_FOLDER_ID=your-folder-id');
        
        $this->info("\nUntuk generate refresh token:");
        $this->info('1. Buka https://developers.google.com/oauthplayground/');
        $this->info('2. Pilih "Google Drive API v3"');
        $this->info('3. Pilih scope: https://www.googleapis.com/auth/drive.file');
        $this->info('4. Masukkan Client ID dan Client Secret');
        $this->info('5. Authorize dan dapatkan refresh token');
        
        $this->info("\nSetelah setup selesai, test dengan:");
        $this->info('php artisan backup:create');
    }
} 