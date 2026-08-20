<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Google_Client;
use Google_Service_Drive;

class GoogleDriveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('google.client', function ($app) {
            $client = new Google_Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect_uri'));
            $client->setScopes([
                'https://www.googleapis.com/auth/drive.file',
                'https://www.googleapis.com/auth/drive.metadata.readonly'
            ]);
            
            // Set refresh token if available
            if (config('services.google.refresh_token')) {
                $client->refreshToken(config('services.google.refresh_token'));
            }
            
            return $client;
        });

        $this->app->singleton('google.drive', function ($app) {
            return new Google_Service_Drive($app['google.client']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
} 