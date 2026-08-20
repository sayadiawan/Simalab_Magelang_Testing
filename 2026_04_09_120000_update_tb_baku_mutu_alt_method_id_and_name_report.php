<?php

/**
 * Shim untuk perintah salah: php artisan migrate --path=2026_04_09_120000_update_tb_baku_mutu_alt_method_id_and_name_report.php
 * Laravel 6 mem-resolve path itu ke root proyek, bukan ke database/migrations/.
 *
 * Gunakan: php artisan migrate
 *    atau: php artisan migrate --path=database/migrations/2026_04_09_120000_update_tb_baku_mutu_alt_method_id_and_name_report.php
 */
require_once __DIR__.'/database/migrations/2026_04_09_120000_update_tb_baku_mutu_alt_method_id_and_name_report.php';
