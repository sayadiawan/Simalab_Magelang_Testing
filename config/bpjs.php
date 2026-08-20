<?php

// return [
//     'cons_id' => env('BPJS_CONS_ID'), // consumer ID dari BPJS Kesehatan
//     'secret_key' => env('BPJS_SECRET_KEY'), // consumer secret key
//     'username' => env('BPJS_USERNAME'), // username pcare
//     'password' => env('BPJS_PASSWORD'), // password pcare
//     'user_key' => env('BPJS_USER_KEY'), // user_key untuk akses webservice
//     'app_code' => env('BPJS_APP_CODE', '0149L005'), // kode aplikasi
//     'base_url' => env('BPJS_BASE_URL', 'https://apijkn-dev.bpjs-kesehatan.go.id'), // base url aplikasi
// ];

return [
    'base_url'      => env('BPJS_PCARE_BASE_URL', 'https://new-api.bpjs-kesehatan.go.id'),
    'service'       => env('BPJS_PCARE_SERVICE', ''),
    'service_name'  => env('BPJS_PCARE_SERVICE_NAME'),
    'cons_id'       => env('BPJS_PCARE_CONS_ID'),
    'consid'        => env('BPJS_PCARE_CONSID'), // Alias untuk konsistensi dengan kode yang ada
    'secret_key'    => env('BPJS_PCARE_SECRET_KEY'),
    'screet_key'    => env('BPJS_PCARE_SCREET_KEY'), // Typo di env, tetap support
    'user_key'      => env('BPJS_PCARE_USER_KEY'),
    'pcare_user'    => env('BPJS_PCARE_USER'),
    'username'      => env('BPJS_PCARE_USERNAME'), // Alias untuk konsistensi
    'pcare_pass'    => env('BPJS_PCARE_PASS'),
    'password'      => env('BPJS_PCARE_PASSWORD'), // Alias untuk konsistensi
    'kd_aplikasi'   => env('BPJS_PCARE_KD_APLIKASI'),
    'app_code'      => env('BPJS_PCARE_APP_CODE'), // Alias untuk konsistensi
    // VClaim Configuration
    'vclaim' => [
        'cons_id'    => env('BPJS_CONS_ID'),
        'secret_key' => env('BPJS_SECRET_KEY'),
        'user_key'   => env('BPJS_USER_KEY'),
        'base_url'   => env('BPJS_VCLAIM_BASE_URL'),
    ],
    // header names bisa beda antar versi/dokumen; gunakan ini kalau perlu ganti cepat
    'headers' => [
        'cons_id'      => 'X-cons-id',
        'timestamp'    => 'X-timestamp',
        'signature'    => 'X-signature',
        'user_key'     => 'X-User-Key',   // beberapa implementasi: 'user_key'
        'authorization'=> 'Authorization' // beberapa implementasi: 'X-Authorization'
    ],
];