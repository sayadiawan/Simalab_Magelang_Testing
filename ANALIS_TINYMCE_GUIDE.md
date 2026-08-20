# 📝 Panduan Menggunakan TinyMCE Inline Editor di Form Analis

## ✨ Fitur Baru: Support Pangkat & Simbol

Form analis sekarang mendukung **rich text editing** dengan TinyMCE inline mode untuk kolom **Hasil** dan **Keterangan**.

---

## 🎯 Cara Menggunakan

### 1. **Input Hasil dengan Format**

#### Memasukkan Pangkat (Superscript):
**Contoh:** 10³ mg/L

**Cara 1 - Via Toolbar:**
1. Ketik: `103`
2. Blok/select angka `3`
3. Klik icon **x²** di toolbar (superscript)
4. Hasil: 10³

**Cara 2 - Keyboard Shortcut:**
1. Ketik: `103`
2. Blok angka `3`
3. Tekan: **Ctrl + Shift + =** (Windows) atau **Cmd + Shift + =** (Mac)
4. Hasil: 10³

#### Memasukkan Subscript:
**Contoh:** H₂O

**Cara:**
1. Ketik: `H2O`
2. Blok angka `2`
3. Klik icon **x₂** di toolbar (subscript)
4. Hasil: H₂O

### 2. **Memasukkan Simbol Khusus**

#### Via Charmap (Peta Karakter):
1. Klik area input (hasil/keterangan)
2. Toolbar floating akan muncul
3. Klik icon **Ω** (charmap/symbol)
4. Pilih simbol yang dibutuhkan
5. Simbol akan muncul di posisi cursor

#### Simbol Yang Tersedia:

**Matematika & Operator:**
- `±` Plus-minus
- `≤` Kurang dari atau sama dengan
- `≥` Lebih dari atau sama dengan
- `≈` Hampir sama dengan
- `≠` Tidak sama dengan
- `×` Perkalian
- `÷` Pembagian

**Pangkat & Fraksi:**
- `²` Pangkat dua
- `³` Pangkat tiga
- `¼` Seperempat
- `½` Setengah
- `¾` Tiga perempat

**Satuan & Simbol Umum:**
- `°` Derajat
- `℃` Celsius
- `µ` Mikro (untuk µg, µL, dll)

**Greek Letters (Notasi):**
- `α` Alpha
- `β` Beta
- `γ` Gamma
- `µ` Mu

---

## 📖 Contoh Penggunaan Nyata

### Contoh 1: Konsentrasi dengan Pangkat
```
Input: 5 × 10³ CFU/mL
```
**Cara:**
1. Ketik: `5 × 103 CFU/mL`
2. Blok `3`
3. Klik superscript (x²)

### Contoh 2: Batas Deteksi
```
Input: < 0,01 mg/L
```
**Cara:**
1. Klik charmap (Ω)
2. Pilih simbol `<`
3. Ketik: `0,01 mg/L`

### Contoh 3: Plus-Minus
```
Input: 25,5 ± 0,5 °C
```
**Cara:**
1. Ketik: `25,5`
2. Klik charmap, pilih `±`
3. Ketik: `0,5`
4. Klik charmap, pilih `°`
5. Ketik: `C`

### Contoh 4: Mikro Satuan
```
Input: 150 µg/L
```
**Cara:**
1. Ketik: `150`
2. Klik charmap, pilih `µ` (mikro)
3. Ketik: `g/L`

### Contoh 5: Formula Kimia
```
Input: H₂SO₄
```
**Cara:**
1. Ketik: `H2SO4`
2. Blok `2`, klik subscript (x₂)
3. Blok `4`, klik subscript (x₂)

---

## ⌨️ Keyboard Shortcuts

### Formatting:
- **Ctrl + B** / **Cmd + B** → Bold
- **Ctrl + I** / **Cmd + I** → Italic
- **Ctrl + U** / **Cmd + U** → Underline
- **Ctrl + Shift + =** → Superscript (pangkat)
- **Ctrl + =** → Subscript

### Navigation:

**Dari Kolom "Hasil":**
- **Enter** → Pindah ke kolom "Hasil" di baris berikutnya
- **Tab** → Pindah ke kolom "Keterangan" di baris yang sama
- **Ctrl + ↓** → Pindah ke "Hasil" di baris bawah
- **Ctrl + ↑** → Pindah ke "Hasil" di baris atas

**Dari Kolom "Keterangan":**
- **Enter** → New line (untuk input multi-line)
- **Ctrl + Enter** → Pindah ke "Keterangan" di baris berikutnya
- **Tab** → Pindah ke "Hasil" di baris berikutnya
- **Shift + Tab** → Kembali ke "Hasil" di baris yang sama
- **Ctrl + ↓** → Pindah ke "Keterangan" di baris bawah
- **Ctrl + ↑** → Pindah ke "Keterangan" di baris atas

---

## 🎨 Tips & Trik

### 1. **Cepat Input Pangkat**
Untuk nilai eksponensial yang sering digunakan:
- Ketik nomor, select digit terakhir, tekan Ctrl+Shift+=
- Contoh: `103` → select `3` → Ctrl+Shift+= → 10³

### 2. **Template Hasil Umum**
Simpan template di notepad untuk hasil yang sering digunakan:
```
Negatif
Positif (+)
< 1 mg/L
10³ CFU/mL
± 0,5
```

### 3. **Copy-Paste Format**
- Format (bold, pangkat, simbol) akan ter-copy saat paste
- Bisa copy dari hasil parameter lain

### 4. **Preview Real-time**
- Badge hijau/merah akan update otomatis
- Validasi baku mutu tetap jalan meski ada format

---

## 🔍 Troubleshooting

### Toolbar Tidak Muncul?
**Solusi:**
1. Klik 2x pada area input
2. Tunggu 1-2 detik untuk toolbar muncul
3. Jika masih tidak muncul, refresh halaman

### Simbol Tidak Tersedia?
**Solusi:**
- Gunakan Windows Character Map (charmap.exe)
- Copy-paste simbol langsung dari sana
- Atau gunakan Alt+code (Windows)

### Format Hilang Setelah Save?
**Cek:**
1. Pastikan tidak menekan "Clear Formatting"
2. Jangan paste dari Word (gunakan Paste as Text)
3. Format HTML akan tersimpan di database

### Badge Tidak Update?
**Normal!** Badge hanya membaca nilai text, bukan format.
- Contoh: `10³` akan dibaca sebagai `103`
- Validasi tetap bekerja dengan benar

---

## 📐 Format Yang Didukung

### ✅ Supported:
- **Bold**, *Italic*, <u>Underline</u>
- Superscript (x²)
- Subscript (x₂)
- Simbol khusus (±, ≤, ≥, °, ℃, µ, dll)
- Greek letters (α, β, γ, µ)
- Bullet list & Numbered list

### ❌ Not Supported:
- Warna text (untuk konsistensi laporan)
- Font size/family berbeda
- Image/table
- Link

---

## 📊 Contoh Lengkap Per Jenis Pemeriksaan

### Mikrobiologi:
```
Coliform: < 50 CFU/100mL
E. coli: Negatif
TPC: 2,5 × 10³ CFU/mL
```

### Kimia:
```
pH: 7,2 ± 0,1
Besi (Fe): 0,15 mg/L
Sulfat (SO₄²⁻): 25 mg/L
```

### Fisika:
```
Suhu: 28 °C
Kekeruhan: < 5 NTU
TDS: 150 mg/L
```

---

## 💾 Auto-Save

Editor akan otomatis menyimpan perubahan ketika:
1. **Blur** - Klik keluar dari area input
2. **Tab/Enter** - Pindah ke input lain
3. **Submit Form** - Klik tombol "Simpan Hasil"

**Tidak perlu klik "Save" setelah tiap input!**

---

## 🎓 Best Practices

### 1. Konsistensi Format
Gunakan format yang sama untuk semua parameter sejenis:
- Selalu gunakan `×` untuk perkalian (bukan `x` atau `*`)
- Selalu gunakan superscript untuk pangkat
- Gunakan `±` untuk rentang uncertainty

### 2. Jelas & Ringkas
```
✅ Good: 10³ CFU/mL
❌ Bad: 10^3 CFU/mL
❌ Bad: 1000 CFU/mL (untuk hasil eksponensial)
```

### 3. Simbol Standar
Gunakan simbol yang diakui secara internasional:
- `µ` untuk mikro (bukan `u` atau `mc`)
- `°C` untuk celsius (bukan `degC` atau `C`)
- `≤` untuk kurang dari sama dengan (bukan `<=`)

---

## 📞 Bantuan Lebih Lanjut

Jika ada pertanyaan atau butuh simbol khusus yang belum tersedia:
1. Check console browser (F12) untuk error
2. Screenshot toolbar yang muncul
3. Coba di browser lain (Chrome/Firefox)

**Happy Editing! 🎉**

