<?php

/**
 * One-shot: title-case ms_wilayah.wilayah (same rules as migration).
 * Run: php database/scripts/title_case_ms_wilayah.php
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

$keep = array_flip(['DIY', 'DI', 'DKI', 'NAD', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X']);

$formatWord = static function (string $word) use ($keep): string {
    if ($word === '') {
        return $word;
    }
    $upper = mb_strtoupper($word, 'UTF-8');
    if (isset($keep[$upper])) {
        return $upper;
    }
    $lower = mb_strtolower($word, 'UTF-8');

    return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($lower, 1, null, 'UTF-8');
};

$formatToken = static function (string $token) use ($formatWord): string {
    // Sertakan apostrophe/hyphen di inti kata (contoh: HILIGANOWOSA'UA)
    if (!preg_match('/^([^\p{L}\p{N}]*)([\p{L}\p{N}\-\x{27}\x{2019}]+)([^\p{L}\p{N}]*)$/u', $token, $m)) {
        return $token;
    }
    $core = $m[2];
    if (strpos($core, '-') !== false) {
        $core = implode('-', array_map($formatWord, explode('-', $core)));
    } elseif (preg_match('/[\x{27}\x{2019}]/u', $core)) {
        $core = preg_replace_callback(
            '/[^\x{27}\x{2019}]+|[\x{27}\x{2019}]/u',
            static function ($mm) use ($formatWord) {
                $seg = $mm[0];
                if ($seg === "'" || $seg === "\u{2019}") {
                    return $seg;
                }

                return $formatWord($seg);
            },
            $core
        );
    } else {
        $core = $formatWord($core);
    }

    return $m[1] . $core . $m[3];
};

$format = static function (string $name) use ($formatToken): string {
    $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
    if ($name === '') {
        return $name;
    }
    $parts = preg_split('/(\s+|\/|\.|\(|\)|`)/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = '';
    foreach ($parts as $part) {
        if ($part === '' || preg_match('/^[\s\/\.()\`]+$/u', $part)) {
            $out .= $part;
            continue;
        }
        $out .= $formatToken($part);
    }

    return $out;
};

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
]);

$total = (int) $pdo->query('SELECT COUNT(*) FROM ms_wilayah')->fetchColumn();
$updated = 0;
$checked = 0;
$offset = 0;
$batchSize = 3000;

echo "Processing {$total} rows...\n";

while ($offset < $total) {
    $rows = $pdo->query(
        'SELECT id_wilayah, wilayah FROM ms_wilayah ORDER BY id_wilayah ASC LIMIT ' . (int) $batchSize . ' OFFSET ' . (int) $offset
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        break;
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE ms_wilayah SET wilayah = ? WHERE id_wilayah = ?');
    try {
        foreach ($rows as $row) {
            $checked++;
            $original = (string) $row['wilayah'];
            $new = $format($original);
            if ($new !== $original) {
                $stmt->execute([$new, $row['id_wilayah']]);
                $updated++;
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    $offset += count($rows);
    echo "checked={$checked}/{$total} updated={$updated}\n";
}

$mig = '2026_07_15_200000_title_case_ms_wilayah_wilayah';
$exists = $pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
$exists->execute([$mig]);
if (!(int) $exists->fetchColumn()) {
    $batchNo = (int) $pdo->query('SELECT COALESCE(MAX(batch),0)+1 FROM migrations')->fetchColumn();
    $nextId = (int) $pdo->query('SELECT COALESCE(MAX(id),0)+1 FROM migrations')->fetchColumn();
    $ins = $pdo->prepare('INSERT INTO migrations (id, migration, batch) VALUES (?, ?, ?)');
    $ins->execute([$nextId, $mig, $batchNo]);
}

echo "Done. total={$total} checked={$checked} updated={$updated}\n";
