<?php
/**
 * Auto Replace Screenshot Placeholder
 * Script ini akan otomatis mengganti placeholder dengan screenshot yang sudah diupload
 * 
 * Akses: http://your-domain.com/documentation/auto-replace-screenshots.php
 * 
 * ⚠️ BACKUP index.html SEBELUM MENJALANKAN SCRIPT INI!
 */

// Konfigurasi
$htmlFile = __DIR__ . '/index.html';
$screenshotsDir = __DIR__ . '/screenshots/';
$backupFile = __DIR__ . '/index.html.backup';

// Mapping screenshot ke section
$screenshotMapping = [
    // Master Data
    'master-data-customer' => [
        'customer-list.png',
        'customer-form.png',
        'customer-*.png'
    ],
    'master-data-sample-type' => [
        'sample-type-list.png',
        'sample-type-form.png',
        'sample-type-*.png'
    ],
    'master-data-laboratorium' => [
        'laboratorium-list.png',
        'laboratorium-form.png',
        'laboratorium-*.png'
    ],
    'master-data-method' => [
        'method-list.png',
        'method-form.png',
        'method-*.png'
    ],
    'master-data-packet' => [
        'packet-list.png',
        'packet-form.png',
        'packet-*.png'
    ],
    'master-data-unit' => [
        'unit-list.png',
        'unit-form.png',
        'unit-*.png'
    ],
    'master-data-container' => [
        'container-list.png',
        'container-form.png',
        'container-*.png'
    ],
    'master-data-pasien' => [
        'pasien-list.png',
        'pasien-form.png',
        'pasien-*.png'
    ],
    // Permohonan Uji
    'permohonan-uji-create' => [
        'permohonan-uji-form.png',
        'permohonan-uji-create.png',
        'permohonan-uji-*.png'
    ],
    // Sample
    'sample-add' => [
        'sample-form.png',
        'sample-add.png',
        'sample-*.png'
    ],
    'sample-receive' => [
        'sample-receive.png',
        'sample-receive-form.png',
        'sample-receive-*.png'
    ],
    // Permohonan Uji Klinik
    'klinik-step1' => [
        'klinik-step1.png',
        'klinik-step-1.png',
        'klinik-1.png'
    ],
    'klinik-step2' => [
        'klinik-step2.png',
        'klinik-step-2.png',
        'klinik-2.png'
    ],
    'klinik-step3' => [
        'klinik-step3.png',
        'klinik-step-3.png',
        'klinik-3.png'
    ],
    'klinik-parameter' => [
        'klinik-parameter.png',
        'klinik-parameter-form.png',
        'klinik-parameter-*.png'
    ],
];

// Fungsi untuk mencari file screenshot
function findScreenshot($patterns, $screenshotsDir) {
    $files = [];
    if (is_dir($screenshotsDir)) {
        $allFiles = scandir($screenshotsDir);
        foreach ($allFiles as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                continue;
            }
            
            foreach ($patterns as $pattern) {
                // Support wildcard
                if (strpos($pattern, '*') !== false) {
                    $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/i';
                    if (preg_match($regex, $file)) {
                        $files[] = $file;
                    }
                } elseif (stripos($file, $pattern) !== false) {
                    $files[] = $file;
                }
            }
        }
    }
    return array_unique($files);
}

// Fungsi untuk replace placeholder
function replacePlaceholder($html, $sectionId, $screenshots) {
    if (empty($screenshots)) {
        return $html;
    }
    
    // Pattern untuk placeholder
    $placeholderPattern = '/<div class="screenshot-container">\s*<div class="screenshot-placeholder">.*?<\/div>\s*<\/div>/s';
    
    // Buat tag img untuk setiap screenshot
    $imgTags = '';
    foreach ($screenshots as $screenshot) {
        $altText = ucfirst(str_replace(['-', '_', '.png', '.jpg', '.jpeg'], [' ', ' ', '', '', ''], $screenshot));
        $imgTags .= "\n        <div class=\"screenshot-container\">\n";
        $imgTags .= "            <img src=\"screenshots/{$screenshot}\" alt=\"{$altText}\" />\n";
        $imgTags .= "        </div>";
    }
    
    // Replace pertama placeholder di section
    $sectionPattern = '/(<section[^>]*id="' . preg_quote($sectionId, '/') . '"[^>]*>.*?<div class="section-content">)/s';
    
    if (preg_match($sectionPattern, $html, $matches)) {
        $sectionStart = $matches[1];
        $afterSectionStart = substr($html, strpos($html, $sectionStart) + strlen($sectionStart));
        
        // Cari placeholder pertama setelah section-content
        if (preg_match($placeholderPattern, $afterSectionStart, $placeholderMatch, PREG_OFFSET_CAPTURE)) {
            $offset = strpos($html, $sectionStart) + strlen($sectionStart) + $placeholderMatch[0][1];
            $html = substr_replace($html, $imgTags, $offset, strlen($placeholderMatch[0][0]));
        }
    }
    
    return $html;
}

// Process
$message = '';
$error = '';
$replaced = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'replace') {
    if (!file_exists($htmlFile)) {
        $error = 'File index.html tidak ditemukan!';
    } else {
        // Backup file
        copy($htmlFile, $backupFile);
        
        // Baca HTML
        $html = file_get_contents($htmlFile);
        
        // Process setiap section
        foreach ($screenshotMapping as $sectionId => $patterns) {
            $screenshots = findScreenshot($patterns, $screenshotsDir);
            if (!empty($screenshots)) {
                $html = replacePlaceholder($html, $sectionId, $screenshots);
                $replaced[$sectionId] = $screenshots;
            }
        }
        
        // Simpan HTML
        if (file_put_contents($htmlFile, $html)) {
            $message = 'Screenshot berhasil di-replace otomatis!';
        } else {
            $error = 'Gagal menyimpan file!';
        }
    }
}

// Get list of screenshots
$uploadedScreenshots = [];
if (is_dir($screenshotsDir)) {
    $files = scandir($screenshotsDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
            $uploadedScreenshots[] = $file;
        }
    }
}

// Get current placeholders
$currentPlaceholders = [];
if (file_exists($htmlFile)) {
    $html = file_get_contents($htmlFile);
    preg_match_all('/<div class="screenshot-placeholder">.*?<p>Screenshot: (.*?)<\/p>/s', $html, $matches);
    if (!empty($matches[1])) {
        $currentPlaceholders = $matches[1];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Replace Screenshot - Dokumentasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2563eb;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #64748b;
            margin-bottom: 30px;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #3b82f6;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .section h2 {
            color: #374151;
            margin-bottom: 15px;
        }
        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .file-item {
            padding: 10px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
        }
        .file-item code {
            color: #2563eb;
            font-size: 12px;
        }
        .mapping-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .mapping-table th,
        .mapping-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .mapping-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }
        .mapping-table td code {
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn {
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-right: 10px;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .replaced-item {
            background: #d1fae5;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 5px 0;
        }
        .replaced-item code {
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Auto Replace Screenshot</h1>
        <p class="subtitle">Otomatis mengganti placeholder dengan screenshot yang sudah diupload</p>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                ✅ <?php echo $message; ?>
                <?php if (!empty($replaced)): ?>
                    <div style="margin-top: 15px;">
                        <strong>Section yang di-replace:</strong>
                        <?php foreach ($replaced as $section => $files): ?>
                            <div class="replaced-item">
                                <strong><?php echo $section; ?>:</strong>
                                <?php foreach ($files as $file): ?>
                                    <code><?php echo htmlspecialchars($file); ?></code>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-warning">
            ⚠️ <strong>PENTING:</strong> Script ini akan membuat backup otomatis ke <code>index.html.backup</code> sebelum melakukan replace.
        </div>
        
        <div class="section">
            <h2>📁 Screenshot yang Sudah Diupload</h2>
            <?php if (empty($uploadedScreenshots)): ?>
                <p style="color: #6b7280;">Belum ada screenshot yang diupload.</p>
            <?php else: ?>
                <div class="file-list">
                    <?php foreach ($uploadedScreenshots as $file): ?>
                        <div class="file-item">
                            <code><?php echo htmlspecialchars($file); ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>🔗 Mapping Screenshot ke Section</h2>
            <p style="color: #6b7280; margin-bottom: 15px;">Script akan mencari screenshot berdasarkan pattern berikut:</p>
            <table class="mapping-table">
                <thead>
                    <tr>
                        <th>Section ID</th>
                        <th>Pattern Screenshot</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($screenshotMapping as $sectionId => $patterns): ?>
                        <tr>
                            <td><code><?php echo $sectionId; ?></code></td>
                            <td>
                                <?php foreach ($patterns as $pattern): ?>
                                    <code><?php echo $pattern; ?></code><br>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php
                                $found = findScreenshot($patterns, $screenshotsDir);
                                if (!empty($found)) {
                                    echo '<span style="color: #10b981;">✓ Ditemukan</span>';
                                } else {
                                    echo '<span style="color: #ef4444;">✗ Tidak ditemukan</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>⚙️ Aksi</h2>
            <form method="POST" onsubmit="return confirm('Yakin ingin melakukan auto-replace? Backup akan dibuat otomatis.');">
                <input type="hidden" name="action" value="replace">
                <button type="submit" class="btn">
                    🤖 Auto Replace Semua Placeholder
                </button>
                <a href="index.html" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">
                    📄 Lihat index.html
                </a>
                <?php if (file_exists($backupFile)): ?>
                    <a href="index.html.backup" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">
                        💾 Lihat Backup
                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="alert alert-info">
            <strong>💡 Cara Kerja:</strong>
            <ol style="margin-top: 10px; padding-left: 20px;">
                <li>Script akan mencari screenshot di folder <code>screenshots/</code> berdasarkan pattern</li>
                <li>Jika ditemukan, placeholder di section yang sesuai akan diganti</li>
                <li>Backup otomatis dibuat ke <code>index.html.backup</code></li>
                <li>Jika ada masalah, restore dari backup</li>
            </ol>
        </div>
    </div>
</body>
</html>

