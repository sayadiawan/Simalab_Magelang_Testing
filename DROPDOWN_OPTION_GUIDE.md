# 📋 Panduan Dropdown/Option Parameter

## 🎯 Kapan Dropdown Muncul?

Form analis akan otomatis menampilkan **dropdown/select** untuk parameter yang memiliki:
- **`is_option = 1`** di database
- **List option values** yang sudah ditentukan

---

## 🔄 Jenis Input Per Parameter

### 1. **Dropdown/Select** (Parameter dengan Option)
**Karakteristik:**
- Parameter memiliki pilihan tetap
- Tidak perlu input manual
- Biasanya berupa kategori/status

**Contoh Parameter:**
```
✅ Kejernihan: Jernih, Keruh, Berwarna
✅ Hasil Uji: Negatif, Positif (+), Positif (++)
✅ Warna: Kuning, Merah, Hijau, Tidak Berwarna
✅ Bau: Tidak Berbau, Berbau
✅ Status: Memenuhi Syarat, Tidak Memenuhi Syarat
```

**Tampilan:**
```
┌─────────────────────────────────┐
│ - Pilih -                    ▼ │
├─────────────────────────────────┤
│ Negatif                         │
│ Positif (+)                     │
│ Positif (++)                    │
└─────────────────────────────────┘
```

---

### 2. **TinyMCE Editor** (Parameter Input Bebas)
**Karakteristik:**
- Parameter membutuhkan input angka/text
- Bisa menggunakan format khusus (pangkat, simbol)
- Ada validasi baku mutu (min/max)

**Contoh Parameter:**
```
✅ pH: 7,2
✅ Suhu: 28 °C
✅ Coliform: 10³ CFU/mL
✅ Besi (Fe): 0,15 mg/L
✅ Kekeruhan: < 5 NTU
```

**Tampilan:**
```
┌─────────────────────────────────┐
│ [editable text dengan toolbar]  │
│ 10³ CFU/mL                      │
└─────────────────────────────────┘
```

---

## 🛠️ Cara Kerja Dropdown

### Auto-Detection:
```
Database Parameter:
├─ is_option = 1
├─ option_values = "Negatif,Positif (+),Positif (++)"
└─ JavaScript reads this → Creates <select> dropdown
```

### Form Behavior:

**Saat Page Load:**
1. JavaScript scan semua textarea
2. Cek `data-is-option` attribute
3. Jika `= 1` → Create dropdown
4. Jika `= 0` → Create TinyMCE editor

**Saat User Pilih:**
1. User klik dropdown
2. Pilih nilai (contoh: "Positif (+)")
3. Nilai otomatis tersimpan ke hidden textarea
4. Badge hijau/merah muncul (jika ada validasi)

**Saat Submit Form:**
- Hidden textarea berisi nilai yang dipilih
- Backend receive seperti biasa
- Tidak ada perubahan di backend

---

## ⌨️ Keyboard Navigation untuk Dropdown

### Shortcuts:
| Tombol | Aksi |
|--------|------|
| **Space** | Buka dropdown |
| **Arrow Down** | Pilihan berikutnya |
| **Arrow Up** | Pilihan sebelumnya |
| **Enter** | Pilih & pindah ke parameter berikutnya ⬇️ |
| **Tab** | Pilih & pindah ke keterangan ➡️ |
| **Esc** | Tutup dropdown tanpa pilih |

---

## 🎨 Styling Dropdown

### Default State:
```css
- Border: 2px solid #e9ecef (abu-abu muda)
- Background: white
- Font: 14px
```

### Focus State:
```css
- Border: 2px solid #667eea (ungu)
- Box-shadow: glow effect
- Cursor: pointer
```

### Selected State:
```css
Badge muncul di bawah dropdown:
✅ Hijau = Sesuai standar
❌ Merah = Tidak sesuai standar (jika ada validasi)
```

---

## 🧪 Contoh Real Case

### Parameter: **Kejernihan Air**

**Setting di Master Parameter:**
```
Nama: Kejernihan
is_option: 1 (checkbox checked)
Option Values: Jernih, Keruh, Agak Keruh
Equal (Expected): Jernih
```

**Tampilan di Form Analis:**
```
┌─────────────────────────────────┐
│ Kejernihan         [Dropdown ▼] │
├─────────────────────────────────┤
│ - Pilih -                       │
│ Jernih                          │
│ Keruh                           │
│ Agak Keruh                      │
└─────────────────────────────────┘
```

**Saat User Pilih "Jernih":**
```
✅ Badge Hijau muncul: "✓ Jernih - Sesuai standar"
```

**Saat User Pilih "Keruh":**
```
❌ Badge Merah muncul: "✗ Keruh * - Tidak sesuai standar"
```

---

## 🔧 Troubleshooting

### Dropdown Tidak Muncul?
**Kemungkinan Penyebab:**
1. `is_option` di database = 0
2. `option_values` kosong
3. JavaScript error (check console)

**Solusi:**
1. Cek master parameter → Edit parameter → Centang "Is Option"
2. Isi "Option Values" dengan format: `Nilai1,Nilai2,Nilai3`
3. Refresh halaman analis

---

### Dropdown Kosong (Hanya "- Pilih -")?
**Penyebab:**
- Format `option_values` salah di database
- Seharusnya: `Negatif,Positif (+),Positif (++)`
- Jangan pakai: `["Negatif","Positif"]` (JSON format)

**Solusi:**
1. Edit master parameter
2. Ubah format option values ke comma-separated
3. Save & refresh

---

### Nilai Lama Tidak Ter-select?
**Normal!** Ini bisa terjadi jika:
- Nilai lama tidak ada di list options baru
- Case-sensitive mismatch (contoh: "negatif" vs "Negatif")

**Solusi:**
- Pilih ulang dari dropdown
- Atau update master parameter untuk include nilai lama

---

## 📝 Setting Parameter dengan Option

### Di Master Parameter (elits-parameter-satuan-klinik):

**Field:**
1. **Nama Parameter**: (contoh: Kejernihan)
2. **Is Option**: ☑️ **Checked**
3. **Option Values**: `Jernih,Keruh,Agak Keruh`
4. **Equal (Expected)**: `Jernih` (opsional, untuk validasi)

**Format Option Values:**
```
✅ Correct: Jernih,Keruh,Agak Keruh
✅ Correct: Negatif,Positif (+),Positif (++)
❌ Wrong: Jernih, Keruh, Agak Keruh (spasi di awal/akhir)
❌ Wrong: ["Jernih","Keruh"] (JSON format)
```

---

## 💡 Tips & Best Practices

### 1. Kapan Gunakan Dropdown?
**Gunakan untuk:**
- Hasil kategorikal (Negatif/Positif)
- Status (MS/TMS)
- Deskripsi fixed (Jernih/Keruh)
- Pilihan < 10 opsi

**Jangan gunakan untuk:**
- Nilai numerik (pH, Suhu, dll)
- Text bebas (Keterangan)
- Nilai yang butuh format khusus (pangkat, simbol)

### 2. Naming Options
**Good:**
```
Negatif
Positif (+)
Positif (++)
Positif (+++)
```

**Bad:**
```
neg
pos1
pos2
```

### 3. Order Matters
Urutan di `option_values` = urutan di dropdown:
```
Negatif,Positif (+),Positif (++)
→ Dropdown akan tampil dengan urutan yang sama
```

---

## 🚀 Advanced: Dynamic Badge Validation dengan Baku Mutu

Dropdown **TETAP MELAKUKAN PENGECEKAN BAKU MUTU** seperti input biasa!

### Jenis Validasi:

#### 1. **Validasi Kategorikal (Equal)**
Untuk option non-numerik (Jernih/Keruh, Positif/Negatif)

**Example:**
```
Parameter: Kejernihan
Equal: Jernih
Options: Jernih,Keruh,Agak Keruh
```

**Result:**
- User pilih "Jernih" → ✅ Badge hijau "Sesuai standar"
- User pilih "Keruh" → ❌ Badge merah + bintang (*) "Tidak sesuai standar (Expected: Jernih)"

---

#### 2. **Validasi Numerik (Min/Max)**
Untuk option yang mengandung angka (< 5, > 10, 15, dll)

**Example A - Range Check:**
```
Parameter: Coliform
Min: 0
Max: 50
Options: < 3, 3-10, 10-50, > 50
```

**Result:**
- User pilih "< 3" → ✅ Badge hijau "Dalam rentang baku mutu (0 - 50)"
- User pilih "> 50" → ❌ Badge merah (*) "Di luar rentang baku mutu (0 - 50)"

**Example B - Exact Value Check:**
```
Parameter: pH Ideal
Equal: 7
Options: 6, 6.5, 7, 7.5, 8
```

**Result:**
- User pilih "7" → ✅ Badge hijau "Sesuai nilai baku mutu"
- User pilih "6.5" → ❌ Badge merah (*) "Tidak sesuai nilai baku mutu (Expected: 7)"

**Example C - Max Only:**
```
Parameter: Kekeruhan
Max: 5
Options: < 1, 1-5, > 5, > 10
```

**Result:**
- User pilih "< 1" → ✅ Badge hijau "Di bawah batas maksimum (5)"
- User pilih "> 10" → ❌ Badge merah (*) "Melebihi batas maksimum (5)"

---

#### 3. **Tanpa Validasi**
Jika tidak ada Min, Max, atau Equal:

**Result:**
- Semua pilihan → ✅ Badge hijau "Terpilih"

---

## 🔬 Bagaimana Validasi Bekerja?

### Untuk Option Numerik:
```javascript
1. User pilih option (contoh: "< 5")
2. System extract angka: "5"
3. System parse dengan number format parameter (ID/EN)
4. System bandingkan dengan baku mutu:
   - Cek Equal dulu (exact match)
   - Jika tidak ada, cek Min-Max range
   - Jika tidak ada, cek Min saja atau Max saja
5. Badge hijau/merah muncul
```

### Untuk Option Non-Numerik:
```javascript
1. User pilih option (contoh: "Keruh")
2. System bandingkan dengan field Equal (case-insensitive)
3. Match → Badge hijau
4. Tidak match → Badge merah (*)
```

---

## 📊 Contoh Real-World Cases:

### Case 1: **Status Cemaran**
```
Parameter: Status Cemaran Mikrobiologi
Equal: Negatif
Options: Negatif, Positif (+), Positif (++), Positif (+++)
```

**Validasi:**
- "Negatif" = ✅ Sesuai standar
- "Positif (+)" = ❌ Tidak sesuai standar (Expected: Negatif)

---

### Case 2: **Kadar Logam Berat**
```
Parameter: Timbal (Pb)
Max: 0.01
Options: < 0.01, 0.01 - 0.05, > 0.05
```

**Validasi:**
- "< 0.01" → Extract angka: 0.01 → Cek: 0.01 <= 0.01 → ✅ Di bawah batas maksimum
- "> 0.05" → Extract angka: 0.05 → Cek: 0.05 > 0.01 → ❌ Melebihi batas maksimum

---

### Case 3: **Range Suhu**
```
Parameter: Suhu Penyimpanan
Min: 2
Max: 8
Options: < 2°C, 2-5°C, 5-8°C, > 8°C
```

**Validasi:**
- "2-5°C" → Extract: 2 atau 5 (ambil angka pertama) → ✅ Dalam rentang
- "> 8°C" → Extract: 8 → Cek: 8 > 8? No, but > means exceed → ❌ Di luar rentang

---

## 🎨 Visual Badge States:

### ✅ **Badge Hijau (Success)**
```
✓ Jernih
  Sesuai standar
```

### ❌ **Badge Merah (Danger)**
```
✗ Keruh *
  Tidak sesuai standar (Expected: Jernih)
```

### ℹ️ **Badge Info (No Validation)**
```
✓ Terpilih
```

---

## 💡 Tips untuk Setting Baku Mutu Dropdown:

### 1. **Parameter Kategorikal:**
Set hanya `Equal` field dengan nilai yang diharapkan.

### 2. **Parameter Numerik dengan Rentang:**
Set `Min` dan `Max` untuk range validation.

### 3. **Parameter Numerik dengan Nilai Tepat:**
Set hanya `Equal` dengan nilai target.

### 4. **Parameter Tanpa Standar:**
Kosongkan semua (`Min`, `Max`, `Equal`) untuk dropdown tanpa validasi.

---

**Happy Validating! 📋✅**

