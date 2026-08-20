# 🤖 Cara Auto Replace Screenshot

## Metode 1: Auto Replace via PHP Script (RECOMMENDED)

### Langkah-langkah:

1. **Upload Screenshot**
   - Upload screenshot ke folder `screenshots/`
   - Gunakan nama file yang sesuai pattern (lihat mapping di bawah)

2. **Akses Script Auto Replace**
   ```
   http://localhost:8000/documentation/auto-replace-screenshots.php
   ```

3. **Lihat Mapping**
   - Script akan menampilkan mapping screenshot ke section
   - Cek status: ✓ Ditemukan atau ✗ Tidak ditemukan

4. **Klik "Auto Replace Semua Placeholder"**
   - Script akan otomatis:
     - Membuat backup `index.html.backup`
     - Mencari screenshot berdasarkan pattern
     - Mengganti placeholder dengan screenshot yang sesuai

5. **Selesai!**
   - Screenshot sudah otomatis terpasang
   - Jika ada masalah, restore dari backup

---

## 📋 Pattern Nama File Screenshot

Agar auto-replace bekerja, gunakan nama file sesuai pattern berikut:

### Master Data:
- **Customer**: `customer-list.png`, `customer-form.png`, `customer-*.png`
- **Sample Type**: `sample-type-list.png`, `sample-type-form.png`
- **Laboratorium**: `laboratorium-list.png`, `laboratorium-form.png`
- **Method**: `method-list.png`, `method-form.png`
- **Packet**: `packet-list.png`, `packet-form.png`
- **Unit**: `unit-list.png`, `unit-form.png`
- **Container**: `container-list.png`, `container-form.png`
- **Pasien**: `pasien-list.png`, `pasien-form.png`

### Permohonan Uji:
- **Form**: `permohonan-uji-form.png`, `permohonan-uji-create.png`

### Sample:
- **Form**: `sample-form.png`, `sample-add.png`
- **Receive**: `sample-receive.png`, `sample-receive-form.png`

### Permohonan Uji Klinik:
- **Step 1**: `klinik-step1.png`, `klinik-step-1.png`
- **Step 2**: `klinik-step2.png`, `klinik-step-2.png`
- **Step 3**: `klinik-step3.png`, `klinik-step-3.png`
- **Parameter**: `klinik-parameter.png`, `klinik-parameter-form.png`

---

## 🔧 Metode 2: Manual Replace (Jika Auto Tidak Cocok)

Jika auto-replace tidak sesuai, gunakan cara manual:

1. Buka `index.html`
2. Cari placeholder yang ingin diganti
3. Ganti dengan:
   ```html
   <div class="screenshot-container">
       <img src="screenshots/NAMA_FILE.png" alt="DESKRIPSI" />
   </div>
   ```

---

## 📝 Contoh Nama File yang Benar

✅ **Nama File yang Benar (Auto-detect):**
```
customer-list.png
sample-type-form.png
permohonan-uji-create.png
klinik-step1.png
klinik-parameter-form.png
```

❌ **Nama File yang Salah (Tidak auto-detect):**
```
Screenshot 2024.png
IMG_123456.png
screenshot.png
customer.png (kurang spesifik)
```

---

## 🛠️ Customize Mapping

Jika ingin menambah atau mengubah mapping, edit file `auto-replace-screenshots.php`:

```php
$screenshotMapping = [
    'section-id' => [
        'pattern1.png',
        'pattern2.png',
        'pattern-*.png'  // wildcard support
    ],
];
```

---

## ✅ Checklist

Setelah auto-replace:

- [ ] Backup sudah dibuat (`index.html.backup`)
- [ ] Screenshot sudah terpasang di semua section
- [ ] Test di browser: `http://localhost:8000/documentation/index.html`
- [ ] Screenshot muncul dengan benar
- [ ] Klik screenshot untuk test modal zoom

---

## 🚨 Troubleshooting

### Screenshot tidak ter-replace

**Solusi:**
1. Cek nama file sesuai pattern
2. Pastikan file ada di folder `screenshots/`
3. Cek mapping di script

### Backup tidak dibuat

**Solusi:**
1. Cek permission folder
2. Pastikan folder writable

### Replace salah section

**Solusi:**
1. Restore dari backup: `index.html.backup`
2. Ubah mapping di script
3. Atau gunakan manual replace

---

## 💡 Tips

1. **Gunakan nama file yang jelas** sesuai pattern
2. **Test satu section dulu** sebelum replace semua
3. **Selalu backup** sebelum replace
4. **Kompres screenshot** jika terlalu besar

---

**Selamat menggunakan Auto Replace!** 🤖✨

