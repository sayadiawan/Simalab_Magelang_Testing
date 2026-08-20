<?php

/**
 * Shim jika menjalankan: php artisan migrate --path=2026_04_11_000000_fix_tb_baku_mutu_method_id_collation_match_ms_method.php
 * (nama file saja — Laravel memuat dari root proyek).
 *
 * Hindari path absolut tanpa --realpath; Laravel akan menggandakan path:
 *   basePath() + '/' + '/home/.../database/migrations/...'  → error file not found
 *
 * Benar:
 *   php artisan migrate
 *   php artisan migrate --path=database/migrations/2026_04_11_000000_fix_tb_baku_mutu_method_id_collation_match_ms_method.php
 *   php artisan migrate --realpath --path=/path/lengkap/ke/database/migrations/2026_04_11_000000_fix_tb_baku_mutu_method_id_collation_match_ms_method.php
 */
require_once __DIR__.'/database/migrations/2026_04_11_000000_fix_tb_baku_mutu_method_id_collation_match_ms_method.php';
