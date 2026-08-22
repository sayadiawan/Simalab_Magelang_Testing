<?php

/**
 * Acak sedikit tanggal lahir pasien klinik & hasil numerik parameter klinik (dummy/testing).
 * Run: php database/scripts/randomize_klinik_dummy_hasil_tgllahir.php
 */

$root = dirname(__DIR__, 2);
$env = file_get_contents($root . '/.env');
preg_match('/^DB_HOST=(.*)$/m', $env, $m);
$host = trim($m[1], "\"' ");
preg_match('/^DB_PORT=(.*)$/m', $env, $m);
$port = trim($m[1] ?? '3306', "\"' ") ?: '3306';
preg_match('/^DB_DATABASE=(.*)$/m', $env, $m);
$db = trim($m[1], "\"' ");
preg_match('/^DB_USERNAME=(.*)$/m', $env, $m);
$user = trim($m[1], "\"' ");
preg_match('/^DB_PASSWORD=(.*)$/m', $env, $m);
$pass = trim($m[1], "\"' ");

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

function pseudoInt(string $seed, int $min, int $max): int
{
    $span = $max - $min + 1;
    if ($span <= 0) {
        return $min;
    }

    return $min + (int) (crc32($seed) % $span);
}

function pseudoFloat(string $seed, float $min, float $max): float
{
    $unit = (crc32($seed . ':f') & 0xffff) / 0xffff;

    return $min + $unit * ($max - $min);
}

function parseNumericHasil(string $raw): ?float
{
    $raw = trim($raw);
    if ($raw === '' || $raw === '-') {
        return null;
    }
    if (!preg_match('/^[0-9]+([.,][0-9]+)?$/', $raw)) {
        return null;
    }

    return (float) str_replace(',', '.', $raw);
}

function formatHasil(float $value, string $original, string $numberFormat): string
{
    $separator = (strpos($original, ',') !== false || $numberFormat === 'id') ? ',' : '.';
    $decimals = 0;
    if (preg_match('/[.,]([0-9]+)$/', $original, $m)) {
        $decimals = strlen($m[1]);
    } elseif (strpos($original, '.') !== false || strpos($original, ',') !== false) {
        $decimals = 1;
    }

    $formatted = number_format($value, $decimals, '.', '');
    if ($separator === ',') {
        $formatted = str_replace('.', ',', $formatted);
    }

    return $formatted;
}

function clampBirthDate(string $date, string $pasienId): string
{
    $dt = new DateTimeImmutable($date);
    $today = new DateTimeImmutable('today');
    $minDate = $today->modify('-100 years');
    $maxDate = $today->modify('-1 year');

    if ($dt < $minDate) {
        $dt = $minDate->modify('+' . pseudoInt($pasienId . ':min', 0, 365) . ' days');
    }
    if ($dt > $maxDate) {
        $dt = $maxDate->modify('-' . pseudoInt($pasienId . ':max', 0, 365) . ' days');
    }

    return $dt->format('Y-m-d');
}

echo "=== Randomize dummy klinik: tgllahir + hasil numerik ===\n";

$pdo->beginTransaction();

// --- Tanggal lahir: hanya pasien yang pernah punya permohonan klinik ---
$pasienStmt = $pdo->query("
    SELECT DISTINCT p.id_pasien, p.tgllahir_pasien
    FROM ms_pasien p
    INNER JOIN tb_permohonan_uji_klinik_2 k
        ON k.pasien_permohonan_uji_klinik = p.id_pasien
        AND k.deleted_at IS NULL
    WHERE p.deleted_at IS NULL
      AND p.tgllahir_pasien IS NOT NULL
      AND TRIM(p.tgllahir_pasien) != ''
");

$updatePasien = $pdo->prepare('UPDATE ms_pasien SET tgllahir_pasien = ? WHERE id_pasien = ?');
$pasienUpdated = 0;

while ($row = $pasienStmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['id_pasien'];
    $offsetDays = pseudoInt($id . ':dob', -120, 120);
    $base = new DateTimeImmutable($row['tgllahir_pasien']);
    $shifted = $base->modify(($offsetDays >= 0 ? '+' : '') . $offsetDays . ' days');
    $newDate = clampBirthDate($shifted->format('Y-m-d'), $id);
    if ($newDate !== $row['tgllahir_pasien']) {
        $updatePasien->execute([$newDate, $id]);
        $pasienUpdated++;
    }
}

echo "Pasien klinik — tanggal lahir diacak: {$pasienUpdated}\n";

// --- Hasil numerik parameter klinik ---
$hasilStmt = $pdo->query("
    SELECT
        pk.id_permohonan_uji_parameter_klinik AS id,
        pk.hasil_permohonan_uji_parameter_klinik AS hasil,
        COALESCE(psk.number_format, 'en') AS number_format
    FROM tb_permohonan_uji_parameter_klinik pk
    LEFT JOIN ms_parameter_satuan_klinik psk
        ON psk.id_parameter_satuan_klinik = pk.parameter_satuan_klinik
    WHERE pk.deleted_at IS NULL
      AND pk.hasil_permohonan_uji_parameter_klinik IS NOT NULL
      AND TRIM(pk.hasil_permohonan_uji_parameter_klinik) != ''
      AND pk.hasil_permohonan_uji_parameter_klinik != '-'
");

$updateHasil = $pdo->prepare('
    UPDATE tb_permohonan_uji_parameter_klinik
    SET hasil_permohonan_uji_parameter_klinik = ?
    WHERE id_permohonan_uji_parameter_klinik = ?
');

$hasilUpdated = 0;
$hasilSkipped = 0;

while ($row = $hasilStmt->fetch(PDO::FETCH_ASSOC)) {
    $original = $row['hasil'];
    $numeric = parseNumericHasil($original);
    if ($numeric === null) {
        $hasilSkipped++;
        continue;
    }

    $factor = pseudoFloat($row['id'] . ':hasil', 0.90, 1.10);
    $newNumeric = $numeric * $factor;

    if ($numeric >= 100) {
        $newNumeric = $numeric + pseudoInt($row['id'] . ':abs', -15, 15);
    } elseif ($numeric >= 10) {
        $newNumeric = $numeric + pseudoFloat($row['id'] . ':abs', -2.5, 2.5);
    } elseif ($numeric >= 1) {
        $newNumeric = $numeric + pseudoFloat($row['id'] . ':abs', -0.8, 0.8);
    }

    if ($newNumeric < 0) {
        $newNumeric = abs($newNumeric);
    }

    $formatted = formatHasil($newNumeric, $original, $row['number_format']);
    if ($formatted === $original) {
        // Pastikan ada perubahan minimal untuk nilai bulat kecil
        $formatted = formatHasil($numeric + pseudoFloat($row['id'] . ':nudge', 0.01, 0.09), $original, $row['number_format']);
    }

    if ($formatted !== $original) {
        $updateHasil->execute([$formatted, $row['id']]);
        $hasilUpdated++;
    }
}

$pdo->commit();

echo "Hasil klinik numerik — diacak: {$hasilUpdated}, dilewati (non-numerik): {$hasilSkipped}\n";

$sample = $pdo->query("
    SELECT tgllahir_pasien, COUNT(*) c
    FROM ms_pasien
    WHERE deleted_at IS NULL AND tgllahir_pasien IS NOT NULL
    GROUP BY tgllahir_pasien
    ORDER BY c DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nSample tanggal lahir terbanyak setelah acak:\n";
foreach ($sample as $s) {
    echo "  {$s['tgllahir_pasien']}: {$s['c']}\n";
}

echo "\nSelesai.\n";
