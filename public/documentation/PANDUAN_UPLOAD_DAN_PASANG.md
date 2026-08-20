# 📸 Panduan Upload & Pasang Screenshot ke Dokumentasi

## 🎯 Langkah-langkah Lengkap

### **STEP 1: Ambil Screenshot**

1. Buka halaman yang ingin di-screenshot (contoh: halaman Customer)
2. Ambil screenshot dengan cara:
   - **Windows**: `Windows + Shift + S` (Snipping Tool)
   - **Mac**: `Cmd + Shift + 4`
   - **Linux**: `Print Screen` atau gunakan Flameshot
3. Simpan screenshot dengan nama yang jelas, contoh: `customer-list.png`

---

### **STEP 2: Upload Screenshot**

#### **Opsi A: Upload via Web (Paling Mudah)**

1. Buka browser, akses:
   ```
   http://localhost:8000/documentation/upload-screenshot.php
   ```
   atau
   ```
   http://your-domain.com/documentation/upload-screenshot.php
   ```

2. Klik "Pilih Screenshot" dan pilih file yang sudah diambil
3. Klik "Upload Screenshot"
4. Screenshot akan tersimpan di folder `screenshots/`
5. Catat nama file yang muncul (contoh: `1234567890_customer-list.png`)

#### **Opsi B: Upload Manual (Copy-Paste)**

1. Buka File Manager
2. Navigasi ke folder:
   ```
   /home/elit/Program/Laravel/magelang-labkes/public/documentation/screenshots/
   ```
3. Copy screenshot dari komputer
4. Paste ke folder `screenshots/`
5. Pastikan nama file jelas (contoh: `customer-list.png`)

---

### **STEP 3: Ganti Placeholder dengan Screenshot**

#### **A. Buka File HTML**

Buka file:
```
public/documentation/index.html
```

#### **B. Cari Placeholder yang Ingin Diganti**

Cari bagian yang memiliki placeholder screenshot, contoh:

```html
<div class="screenshot-container">
    <div class="screenshot-placeholder">
        <i class="fas fa-image"></i>
        <p>Screenshot: Menu Customer</p>
        <small>Tambahkan screenshot halaman Customer di sini</small>
    </div>
</div>
```

#### **C. Ganti dengan Tag IMG**

Ganti seluruh placeholder dengan tag `<img>`, contoh:

```html
<div class="screenshot-container">
    <img src="screenshots/customer-list.png" alt="Halaman Daftar Customer" />
</div>
```

**Penjelasan:**
- `src="screenshots/customer-list.png"` → Path ke file screenshot
- `alt="Halaman Daftar Customer"` → Deskripsi untuk aksesibilitas

---

## 📝 Contoh Lengkap: Customer Section

### **Sebelum (Placeholder):**

```html
<section id="master-data-customer" class="doc-section">
    <div class="section-header">
        <h1>Customer (Pelanggan)</h1>
    </div>
    <div class="section-content">
        <div class="screenshot-container">
            <div class="screenshot-placeholder">
                <i class="fas fa-image"></i>
                <p>Screenshot: Menu Customer</p>
                <small>Tambahkan screenshot halaman Customer di sini</small>
            </div>
        </div>
        <div class="info-box">
            <p><strong>Fungsi:</strong> Mengelola data pelanggan yang akan melakukan pemeriksaan.</p>
        </div>
        <!-- ... langkah-langkah ... -->
    </div>
</section>
```

### **Sesudah (Dengan Screenshot):**

```html
<section id="master-data-customer" class="doc-section">
    <div class="section-header">
        <h1>Customer (Pelanggan)</h1>
    </div>
    <div class="section-content">
        <!-- Screenshot Halaman List Customer -->
        <div class="screenshot-container">
            <img src="screenshots/customer-list.png" alt="Halaman Daftar Customer" />
        </div>
        
        <!-- Screenshot Form Tambah Customer (opsional) -->
        <div class="screenshot-container">
            <img src="screenshots/customer-form.png" alt="Form Tambah Customer" />
        </div>
        
        <div class="info-box">
            <p><strong>Fungsi:</strong> Mengelola data pelanggan yang akan melakukan pemeriksaan.</p>
        </div>
        <!-- ... langkah-langkah ... -->
    </div>
</section>
```

---

## 🔍 Cara Mencari & Mengganti Semua Placeholder

### **Metode 1: Find & Replace di Editor**

1. Buka `index.html` di editor (VS Code, Sublime, dll)
2. Tekan `Ctrl + H` (atau `Cmd + H` di Mac) untuk Find & Replace
3. Cari: `screenshot-placeholder`
4. Ganti satu per satu dengan screenshot yang sesuai

### **Metode 2: Manual (Lebih Aman)**

1. Buka `index.html`
2. Cari section yang ingin diupdate (contoh: `master-data-customer`)
3. Ganti placeholder dengan screenshot
4. Simpan file

---

## 📋 Daftar Placeholder yang Perlu Diganti

Berikut daftar semua placeholder yang perlu diganti:

### **Master Data:**
- [ ] Customer → `screenshots/customer-list.png`
- [ ] Sample Type → `screenshots/sample-type-list.png`
- [ ] Laboratorium → `screenshots/laboratorium-list.png`
- [ ] Method → `screenshots/method-list.png`
- [ ] Packet → `screenshots/packet-list.png`
- [ ] Unit → `screenshots/unit-list.png`
- [ ] Container → `screenshots/container-list.png`
- [ ] Pasien → `screenshots/pasien-list.png`

### **Permohonan Uji:**
- [ ] Form Tambah Permohonan Uji → `screenshots/permohonan-uji-form.png`
- [ ] Detail Permohonan Uji → `screenshots/permohonan-uji-detail.png`

### **Sample:**
- [ ] Form Tambah Sample → `screenshots/sample-form.png`
- [ ] Form Penerimaan Sample → `screenshots/sample-receive-form.png`

### **Permohonan Uji Klinik:**
- [ ] Step 1: Tipe Dokter → `screenshots/klinik-step1.png`
- [ ] Step 2: Data Pasien → `screenshots/klinik-step2.png`
- [ ] Step 3: Informasi → `screenshots/klinik-step3.png`
- [ ] Form Parameter → `screenshots/klinik-parameter-form.png`

---

## 🎨 Tips Screenshot yang Baik

### **1. Ukuran & Format**
- **Format**: PNG (untuk screenshot dengan teks) atau JPG (untuk foto)
- **Ukuran**: Maksimal 1200px lebar (akan otomatis responsive)
- **File Size**: Kompres jika > 500KB (gunakan TinyPNG)

### **2. Nama File**
✅ **Baik:**
```
customer-list.png
customer-form-add.png
sample-type-list.png
permohonan-uji-form-step1.png
```

❌ **Buruk:**
```
Screenshot 2024-01-15.png
IMG_123456.png
screenshot.png
```

### **3. Multiple Screenshots**
Jika ada beberapa langkah, tambahkan beberapa screenshot:

```html
<h3>Langkah 1: Akses Menu</h3>
<div class="screenshot-container">
    <img src="screenshots/step1-menu.png" alt="Akses Menu Customer" />
</div>

<h3>Langkah 2: Klik Tambah</h3>
<div class="screenshot-container">
    <img src="screenshots/step2-tambah.png" alt="Klik Tombol Tambah" />
</div>

<h3>Langkah 3: Isi Form</h3>
<div class="screenshot-container">
    <img src="screenshots/step3-form.png" alt="Form Tambah Customer" />
</div>
```

---

## ✅ Checklist Setelah Upload & Pasang

Setelah upload dan pasang screenshot, pastikan:

- [ ] Screenshot sudah diupload ke folder `screenshots/`
- [ ] Nama file jelas dan deskriptif
- [ ] Placeholder sudah diganti dengan tag `<img>`
- [ ] Path `src` benar (relatif dari `index.html`)
- [ ] Alt text sudah diisi dengan deskripsi
- [ ] Screenshot muncul di browser (test dengan buka `index.html`)
- [ ] Screenshot bisa diklik untuk zoom (modal)

---

## 🧪 Test Screenshot

### **1. Test di Browser**

1. Buka `index.html` di browser:
   ```
   http://localhost:8000/documentation/index.html
   ```
2. Scroll ke section yang sudah dipasang screenshot
3. Pastikan screenshot muncul
4. Klik screenshot untuk test modal zoom

### **2. Test Path**

Jika screenshot tidak muncul, cek:
- Path file benar: `screenshots/nama-file.png`
- File ada di folder: `public/documentation/screenshots/`
- Nama file sesuai (case-sensitive di Linux)

---

## 🚨 Troubleshooting

### **Screenshot tidak muncul**

**Solusi:**
1. Cek path di HTML: `src="screenshots/nama-file.png"`
2. Pastikan file ada di folder `screenshots/`
3. Cek nama file (case-sensitive)
4. Buka Developer Tools (F12) → Console untuk lihat error

### **Screenshot terlalu besar**

**Solusi:**
1. Kompres dengan [TinyPNG](https://tinypng.com/)
2. Resize screenshot ke maksimal 1200px lebar
3. Gunakan format JPG jika tidak perlu transparansi

### **Path tidak ditemukan**

**Solusi:**
- Pastikan path relatif dari `index.html`
- Jangan gunakan path absolut seperti `/screenshots/...`
- Gunakan path relatif: `screenshots/nama-file.png`

---

## 📖 Contoh Lengkap: Update Customer Section

### **1. Upload Screenshot**

Upload file `customer-list.png` ke folder `screenshots/`

### **2. Buka index.html**

Cari section `master-data-customer` (sekitar line 150-215)

### **3. Ganti Placeholder**

**Cari:**
```html
<div class="screenshot-container">
    <div class="screenshot-placeholder">
        <i class="fas fa-image"></i>
        <p>Screenshot: Menu Customer</p>
        <small>Tambahkan screenshot halaman Customer di sini</small>
    </div>
</div>
```

**Ganti dengan:**
```html
<div class="screenshot-container">
    <img src="screenshots/customer-list.png" alt="Halaman Daftar Customer" />
</div>
```

### **4. Simpan & Test**

1. Simpan file `index.html`
2. Refresh browser
3. Screenshot akan muncul!

---

## 🎉 Selesai!

Setelah semua placeholder diganti dengan screenshot, dokumentasi akan lebih informatif dan mudah dipahami!

**Selamat mengupdate dokumentasi!** 📸✨

