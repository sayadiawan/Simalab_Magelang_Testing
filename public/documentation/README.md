# Dokumentasi Web - Panduan Menambahkan Screenshot

## Cara Menambahkan Screenshot

### 1. Siapkan Screenshot
- Ambil screenshot dari halaman yang ingin didokumentasikan
- Simpan dengan format: `PNG` atau `JPG`
- Ukuran disarankan: lebar maksimal 1200px untuk performa optimal
- Nama file: gunakan nama yang deskriptif, contoh: `customer-list.png`, `sample-form.png`

### 2. Simpan Screenshot
Simpan screenshot di folder:
```
public/documentation/screenshots/
```

Contoh struktur:
```
public/documentation/
├── index.html
├── styles.css
├── script.js
└── screenshots/
    ├── customer-list.png
    ├── customer-form.png
    ├── sample-type-list.png
    ├── sample-form.png
    └── ...
```

### 3. Update HTML
Ganti placeholder screenshot dengan tag `<img>`. Contoh:

**Sebelum (Placeholder):**
```html
<div class="screenshot-container">
    <div class="screenshot-placeholder">
        <i class="fas fa-image"></i>
        <p>Screenshot: Menu Customer</p>
        <small>Tambahkan screenshot halaman Customer di sini</small>
    </div>
</div>
```

**Sesudah (Dengan Screenshot):**
```html
<div class="screenshot-container">
    <img src="screenshots/customer-list.png" alt="Halaman Daftar Customer" />
</div>
```

### 4. Contoh Lengkap

#### Master Data - Customer
```html
<section id="master-data-customer" class="doc-section">
    <div class="section-header">
        <h1>Customer (Pelanggan)</h1>
    </div>
    <div class="section-content">
        <!-- Screenshot Halaman List -->
        <div class="screenshot-container">
            <img src="screenshots/customer-list.png" alt="Halaman Daftar Customer" />
        </div>
        
        <!-- Screenshot Form Tambah -->
        <div class="screenshot-container">
            <img src="screenshots/customer-form.png" alt="Form Tambah Customer" />
        </div>
        
        <div class="info-box">
            <p><strong>Fungsi:</strong> Mengelola data pelanggan yang akan melakukan pemeriksaan.</p>
        </div>
        
        <!-- Langkah-langkah -->
        ...
    </div>
</section>
```

## Tips Screenshot

### 1. Kualitas Screenshot
- Gunakan resolusi tinggi untuk kejelasan
- Pastikan teks mudah dibaca
- Hindari screenshot yang terlalu gelap atau terlalu terang

### 2. Anotasi (Opsional)
Jika perlu menambahkan penjelasan pada screenshot:
- Gunakan tool seperti Snagit, Lightshot, atau Annotate
- Tambahkan panah atau kotak untuk highlight area penting
- Tambahkan nomor urut untuk langkah-langkah

### 3. Multiple Screenshots
Untuk proses yang panjang, tambahkan beberapa screenshot:
```html
<!-- Screenshot Step 1 -->
<div class="screenshot-container">
    <img src="screenshots/permohonan-uji-step1.png" alt="Step 1: Pilih Customer" />
</div>

<!-- Screenshot Step 2 -->
<div class="screenshot-container">
    <img src="screenshots/permohonan-uji-step2.png" alt="Step 2: Isi Data" />
</div>
```

### 4. Responsive
- Screenshot akan otomatis responsive
- Pastikan screenshot tidak terlalu lebar (maks 1200px)
- Screenshot akan di-scale down di mobile

## Fitur Screenshot

### 1. Click to Zoom
Screenshot dapat diklik untuk melihat dalam ukuran penuh (modal).

### 2. Hover Effect
Screenshot memiliki efek hover untuk indikasi interaktif.

### 3. Modal View
- Klik screenshot untuk membuka modal fullscreen
- Tekan ESC atau klik di luar gambar untuk menutup
- Scroll untuk zoom (jika gambar lebih besar dari viewport)

## Struktur File Screenshot yang Disarankan

```
screenshots/
├── master-data/
│   ├── customer-list.png
│   ├── customer-form.png
│   ├── sample-type-list.png
│   ├── sample-type-form.png
│   ├── laboratorium-list.png
│   ├── method-list.png
│   ├── method-form.png
│   ├── packet-list.png
│   └── packet-form.png
├── permohonan-uji/
│   ├── permohonan-uji-list.png
│   ├── permohonan-uji-form-step1.png
│   ├── permohonan-uji-form-step2.png
│   └── permohonan-uji-detail.png
├── sample/
│   ├── sample-list.png
│   ├── sample-form.png
│   └── sample-receive-form.png
└── permohonan-uji-klinik/
    ├── klinik-step1.png
    ├── klinik-step2.png
    ├── klinik-step3.png
    └── klinik-parameter-form.png
```

## Contoh Path Screenshot

Jika menggunakan struktur folder:
```html
<img src="screenshots/master-data/customer-list.png" alt="Halaman Daftar Customer" />
```

Jika semua screenshot di satu folder:
```html
<img src="screenshots/customer-list.png" alt="Halaman Daftar Customer" />
```

## Catatan Penting

1. **Alt Text**: Selalu tambahkan `alt` text yang deskriptif untuk aksesibilitas
2. **File Size**: Optimalkan ukuran file screenshot (gunakan tool seperti TinyPNG)
3. **Naming Convention**: Gunakan nama file yang konsisten dan mudah dipahami
4. **Version Control**: Jika menggunakan Git, pastikan screenshot tidak terlalu besar

## Tools untuk Screenshot

### Desktop
- **Windows**: Snipping Tool, Snagit, Greenshot
- **Mac**: Cmd+Shift+4, Snagit
- **Linux**: Flameshot, Shutter

### Browser Extension
- **Lightshot**: Screenshot & annotate
- **Awesome Screenshot**: Screenshot & annotate
- **Nimbus Screenshot**: Full page screenshot

### Online Tools
- **Annotate**: Untuk menambahkan anotasi
- **TinyPNG**: Untuk kompresi gambar

---

**Selamat mendokumentasikan!** 🎉

