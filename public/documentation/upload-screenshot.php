<?php
/**
 * Simple Upload Screenshot Script
 * Akses via: http://your-domain.com/documentation/upload-screenshot.php
 * 
 * ⚠️ HAPUS FILE INI SETELAH SELESAI UPLOAD (Security)
 */

// Konfigurasi
$uploadDir = __DIR__ . '/screenshots/';
$allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
$maxSize = 2 * 1024 * 1024; // 2MB

// Pastikan folder ada
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = '';
$error = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['screenshot'])) {
    $file = $_FILES['screenshot'];
    
    // Validasi
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error saat upload file.';
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $error = 'Format file tidak diizinkan. Hanya PNG, JPG, JPEG.';
    } elseif ($file['size'] > $maxSize) {
        $error = 'Ukuran file terlalu besar. Maksimal 2MB.';
    } else {
        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
        $filename = time() . '_' . $filename;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $message = "Screenshot berhasil diupload: <strong>{$filename}</strong>";
        } else {
            $error = 'Gagal menyimpan file.';
        }
    }
}

// List uploaded files
$uploadedFiles = [];
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
            $uploadedFiles[] = $file;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Screenshot - Dokumentasi</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2563eb;
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
        .upload-form {
            margin-bottom: 40px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            cursor: pointer;
        }
        input[type="file"]:hover {
            border-color: #2563eb;
            background: #eff6ff;
        }
        button {
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #1d4ed8;
        }
        .file-list {
            margin-top: 30px;
        }
        .file-list h2 {
            margin-bottom: 15px;
            color: #374151;
        }
        .file-item {
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-item a {
            color: #2563eb;
            text-decoration: none;
        }
        .file-item a:hover {
            text-decoration: underline;
        }
        .file-size {
            color: #6b7280;
            font-size: 14px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2563eb;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📸 Upload Screenshot Dokumentasi</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                ✅ <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-warning">
            ⚠️ <strong>Peringatan Keamanan:</strong> Hapus file ini setelah selesai upload screenshot!
        </div>
        
        <div class="upload-form">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="screenshot">Pilih Screenshot:</label>
                    <input type="file" id="screenshot" name="screenshot" accept="image/png,image/jpeg,image/jpg" required>
                    <small style="color: #6b7280; display: block; margin-top: 5px;">
                        Format: PNG, JPG, JPEG | Maksimal: 2MB
                    </small>
                </div>
                <button type="submit">📤 Upload Screenshot</button>
            </form>
        </div>
        
        <?php if (!empty($uploadedFiles)): ?>
            <div class="file-list">
                <h2>📁 File yang Sudah Diupload:</h2>
                <?php foreach ($uploadedFiles as $file): ?>
                    <div class="file-item">
                        <div>
                            <a href="screenshots/<?php echo htmlspecialchars($file); ?>" target="_blank">
                                <?php echo htmlspecialchars($file); ?>
                            </a>
                            <span class="file-size">
                                (<?php echo number_format(filesize($uploadDir . $file) / 1024, 2); ?> KB)
                            </span>
                        </div>
                        <div>
                            <code style="background: #e5e7eb; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                screenshots/<?php echo htmlspecialchars($file); ?>
                            </code>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <a href="index.html" class="back-link">← Kembali ke Dokumentasi</a>
    </div>
</body>
</html>

