# Panduan Upload Screenshot ke Dokumentasi

## 📁 Lokasi Folder Screenshot

Screenshot harus disimpan di folder:
```
public/documentation/screenshots/
```

Path lengkap:
```
/home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/
```

---

## 🚀 Metode 1: Upload Manual (File Manager/FTP)

### A. Via File Manager (cPanel/Server)

1. **Login ke cPanel atau File Manager**
2. **Navigasi ke folder:**
   ```
   public/documentation/screenshots/
   ```
3. **Klik "Upload"**
4. **Pilih file screenshot** (bisa multiple files)
5. **Tunggu upload selesai**
6. **Pastikan file sudah ada di folder**

### B. Via FTP Client (FileZilla, WinSCP, dll)

1. **Buka FTP Client** (FileZilla, WinSCP, dll)
2. **Connect ke server**
3. **Navigasi ke folder:**
   ```
   public/documentation/screenshots/
   ```
4. **Drag & drop file screenshot** dari komputer ke server
5. **Tunggu transfer selesai**

---

## 💻 Metode 2: Upload via Command Line (SSH/Terminal)

### A. Via SCP (Secure Copy)

```bash
# Upload satu file
scp /path/to/screenshot.png user@server:/home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/

# Upload multiple files
scp /path/to/screenshots/*.png user@server:/home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/
```

### B. Via Terminal/SSH (jika sudah login)

```bash
# Login ke server via SSH
ssh user@server

# Navigasi ke folder
cd /home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/

# Upload file (jika file sudah ada di server lain)
# Atau gunakan wget/curl untuk download dari URL
wget https://example.com/screenshot.png -O screenshot.png
```

### C. Copy dari Lokal ke Server (jika akses langsung)

```bash
# Jika Anda sudah di server dan file ada di komputer lokal
# Gunakan scp dari komputer lokal:
scp screenshot.png user@server:/path/to/public/documentation/screenshots/
```

---

## 📋 Metode 3: Copy-Paste Langsung (Jika Akses Server Langsung)

### A. Via File Manager Desktop

1. **Buka File Manager** (Nautilus, Finder, Explorer)
2. **Navigasi ke folder screenshot:**
   ```
   /home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/
   ```
3. **Copy screenshot** dari komputer
4. **Paste ke folder** screenshot

### B. Via Terminal (Linux/Mac)

```bash
# Copy file ke folder screenshot
cp /path/to/screenshot.png /home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/

# Copy multiple files
cp /path/to/screenshots/*.png /home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/

# Atau gunakan mv untuk move file
mv /path/to/screenshot.png /home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/
```

---

## 🌐 Metode 4: Upload via Laravel (Fitur Upload)

Jika ingin membuat fitur upload di aplikasi Laravel, berikut contohnya:

### A. Buat Route

Tambahkan di `routes/web.php`:
```php
Route::post('/documentation/upload-screenshot', 'DocumentationController@uploadScreenshot');
```

### B. Buat Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentationController extends Controller
{
    public function uploadScreenshot(Request $request)
    {
        $request->validate([
            'screenshot' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('screenshot')) {
            $file = $request->file('screenshot');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->move(public_path('documentation/screenshots'), $filename);
            
            return response()->json([
                'success' => true,
                'message' => 'Screenshot berhasil diupload',
                'filename' => $filename,
                'path' => 'documentation/screenshots/' . $filename
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal upload screenshot'
        ], 400);
    }
}
```

### C. Buat Form Upload (Opsional)

```html
<form action="/documentation/upload-screenshot" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="screenshot" accept="image/png,image/jpeg,image/jpg">
    <button type="submit">Upload Screenshot</button>
</form>
```

---

## 📝 Metode 5: Upload via Git (Jika Menggunakan Version Control)

### A. Tambahkan File ke Git

```bash
# Tambahkan screenshot ke staging
git add public/documentation/screenshots/screenshot.png

# Commit
git commit -m "Add screenshot for documentation"

# Push ke repository
git push origin main
```

### B. Catatan untuk Git

**⚠️ PENTING:** Screenshot bisa membuat repository besar. Pertimbangkan:

1. **Gunakan Git LFS** untuk file besar:
   ```bash
   git lfs install
   git lfs track "*.png"
   git lfs track "*.jpg"
   ```

2. **Atau tambahkan ke .gitignore** jika tidak ingin commit screenshot:
   ```
   # Di .gitignore
   public/documentation/screenshots/*.png
   public/documentation/screenshots/*.jpg
   !public/documentation/screenshots/.gitkeep
   ```

---

## ✅ Checklist Setelah Upload

Setelah upload screenshot, pastikan:

- [ ] File sudah ada di folder `public/documentation/screenshots/`
- [ ] Nama file jelas dan deskriptif (contoh: `customer-list.png`)
- [ ] Format file: PNG, JPG, atau JPEG
- [ ] Ukuran file tidak terlalu besar (disarankan < 1MB per file)
- [ ] File dapat diakses via browser (test URL)
- [ ] Update `index.html` untuk menggunakan screenshot

---

## 🔗 Cara Menggunakan Screenshot di HTML

Setelah upload, update `index.html`:

```html
<!-- Ganti placeholder dengan screenshot -->
<div class="screenshot-container">
    <img src="screenshots/customer-list.png" alt="Halaman Daftar Customer" />
</div>
```

**Path relatif dari `index.html`:**
- Jika screenshot di: `public/documentation/screenshots/customer-list.png`
- Maka path di HTML: `screenshots/customer-list.png`

---

## 🛠️ Tools untuk Optimasi Screenshot

### A. Kompresi Ukuran File

**Online Tools:**
- [TinyPNG](https://tinypng.com/) - Kompres PNG & JPG
- [Squoosh](https://squoosh.app/) - Kompres dengan kontrol penuh
- [Compressor.io](https://compressor.io/) - Kompres berbagai format

**Command Line:**
```bash
# Install ImageMagick (jika belum ada)
sudo apt-get install imagemagick

# Kompres PNG
convert screenshot.png -quality 85 screenshot-compressed.png

# Kompres JPG
convert screenshot.jpg -quality 85 screenshot-compressed.jpg
```

### B. Resize Screenshot

```bash
# Resize dengan ImageMagick
convert screenshot.png -resize 1200x screenshot-resized.png

# Atau dengan width saja (height auto)
convert screenshot.png -resize 1200 screenshot-resized.png
```

---

## 📋 Contoh Nama File yang Baik

✅ **Nama File yang Baik:**
```
customer-list.png
customer-form-add.png
sample-type-list.png
permohonan-uji-form-step1.png
permohonan-uji-klinik-step2.png
```

❌ **Nama File yang Buruk:**
```
Screenshot 2024-01-15 123456.png
IMG_20240115_123456.jpg
screenshot.png
untitled.png
```

---

## 🚨 Troubleshooting

### Masalah: Screenshot tidak muncul

**Solusi:**
1. Cek path file di HTML
2. Pastikan file ada di folder yang benar
3. Cek permission folder (harus readable)
4. Cek nama file (case-sensitive di Linux)

### Masalah: Screenshot terlalu besar

**Solusi:**
1. Kompres screenshot dengan tool di atas
2. Resize screenshot ke ukuran yang lebih kecil
3. Gunakan format JPG untuk foto (lebih kecil dari PNG)

### Masalah: Permission denied

**Solusi:**
```bash
# Set permission folder
chmod 755 public/documentation/screenshots/

# Set permission file
chmod 644 public/documentation/screenshots/*.png
```

---

## 📞 Bantuan

Jika mengalami masalah upload, cek:
1. Permission folder dan file
2. Path file di HTML
3. Format file (harus PNG, JPG, atau JPEG)
4. Ukuran file (tidak terlalu besar)

---

**Selamat mengupload screenshot!** 🎉

