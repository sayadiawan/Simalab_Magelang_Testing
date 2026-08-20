<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'satu_sehat' => [
      'version' => env('VERSION_SATUSEHAT'),
      'base_uri' => env('BASE_URL_SATUSEHAT'),
      'url' => env('URL_SATUSEHAT'), // Alias untuk base_uri
      'auth_url' => env('AUTH_URL_SATUSEHAT'),
      'consent_url' => env('CONSENT_URL_SATUSEHAT'),
      'client_id' => env('CLIENT_ID_SATUSEHAT'),
      'client_secret' => env('CLIENT_SECRET_SATUSEHAT'),
      'org_id' => env('ORG_ID'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', 'http://localhost/auth/google/callback'),
        'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'bsre' => [
        'base_url' => env('BSRE_BASE_URL'),
        'username' => env('BSRE_USERNAME'),
        'password' => env('BSRE_PASSWORD'),
    ],

    'recaptcha' => [
        'site_key' => env('CAPTCHA_SITE_KEY'),
    ],

    'wablas' => [
        'host' => env('WABLAS_HOST', 'https://api.wablas.com'),
        'token' => env('WABLAS_TOKEN'),
        'secret' => env('WABLAS_SECRET'),
    ],

];
