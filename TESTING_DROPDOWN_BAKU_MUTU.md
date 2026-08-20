# 🧪 Panduan Testing: Dropdown dengan Validasi Baku Mutu

## 🎯 Tujuan Testing

Memastikan dropdown parameter (`is_option = 1`) **tetap melakukan validasi baku mutu** dengan benar.

---

## 📋 Checklist Testing

### ✅ **Test 1: Kategorikal - Exact Match (Equal)**

**Setup Parameter:**
```
Nama: Kejernihan Air
is_option: 1 (checked)
Option Values: Jernih,Keruh,Agak Keruh
Equal: Jernih
Min: (kosong)
Max: (kosong)
```

**Expected Behavior:**
| User Pilih | Badge | Warna | Pesan |
|------------|-------|-------|-------|
| Jernih | ✓ | Hijau | Sesuai standar |
| Keruh | ✗ * | Merah | Tidak sesuai standar (Expected: Jernih) |
| Agak Keruh | ✗ * | Merah | Tidak sesuai standar (Expected: Jernih) |

**Cara Test:**
1. Buka form analis
2. Cari parameter "Kejernihan Air"
3. Dropdown harus muncul (bukan text editor)
4. Pilih "Jernih" → Badge hijau harus muncul
5. Pilih "Keruh" → Badge merah + bintang (*) harus muncul
6. Check console log: `Dropdown validation - Value: ...`

---

### ✅ **Test 2: Numerik - Range Check (Min & Max)**

**Setup Parameter:**
```
Nama: Total Coliform
is_option: 1 (checked)
Option Values: < 3,3 - 10,10 - 50,> 50
Equal: (kosong)
Min: 0
Max: 50
Satuan: CFU/100mL
```

**Expected Behavior:**
| User Pilih | Extract Value | Badge | Warna | Pesan |
|------------|---------------|-------|-------|-------|
| < 3 | 3 | ✓ | Hijau | Dalam rentang baku mutu (0 - 50) |
| 3 - 10 | 3 | ✓ | Hijau | Dalam rentang baku mutu (0 - 50) |
| 10 - 50 | 10 | ✓ | Hijau | Dalam rentang baku mutu (0 - 50) |
| > 50 | 50 | ✗ * | Merah | Di luar rentang baku mutu (0 - 50) |

**Cara Test:**
1. Buka form analis
2. Cari parameter "Total Coliform"
3. Dropdown harus muncul dengan 4 options
4. Pilih "< 3" → Badge hijau
5. Pilih "> 50" → Badge merah + bintang (*)
6. Check console: `Dropdown validation - Value: 50, Min: 0, Max: 50`

---

### ✅ **Test 3: Numerik - Exact Value (Equal)**

**Setup Parameter:**
```
Nama: pH Optimal
is_option: 1 (checked)
Option Values: 6,6.5,7,7.5,8
Equal: 7
Min: (kosong)
Max: (kosong)
```

**Expected Behavior:**
| User Pilih | Badge | Warna | Pesan |
|------------|-------|-------|-------|
| 6 | ✗ * | Merah | Tidak sesuai nilai baku mutu (Expected: 7) |
| 6.5 | ✗ * | Merah | Tidak sesuai nilai baku mutu (Expected: 7) |
| 7 | ✓ | Hijau | Sesuai nilai baku mutu |
| 7.5 | ✗ * | Merah | Tidak sesuai nilai baku mutu (Expected: 7) |
| 8 | ✗ * | Merah | Tidak sesuai nilai baku mutu (Expected: 7) |

**Cara Test:**
1. Pilih "7" → Badge hijau
2. Pilih "6.5" → Badge merah + bintang (*)
3. Console: `Dropdown validation - Value: 6.5, Min: null, Max: null, Equal: 7`

---

### ✅ **Test 4: Max Only**

**Setup Parameter:**
```
Nama: Kekeruhan Maksimum
is_option: 1 (checked)
Option Values: < 1,1 - 5,> 5,> 10
Equal: (kosong)
Min: (kosong)
Max: 5
Satuan: NTU
```

**Expected Behavior:**
| User Pilih | Extract Value | Badge | Warna | Pesan |
|------------|---------------|-------|-------|-------|
| < 1 | 1 | ✓ | Hijau | Di bawah batas maksimum (5) |
| 1 - 5 | 1 | ✓ | Hijau | Di bawah batas maksimum (5) |
| > 5 | 5 | ✗ * | Merah | Melebihi batas maksimum (5) |
| > 10 | 10 | ✗ * | Merah | Melebihi batas maksimum (5) |

---

### ✅ **Test 5: Min Only**

**Setup Parameter:**
```
Nama: Suhu Sterilisasi Minimum
is_option: 1 (checked)
Option Values: < 60°C,60 - 70°C,70 - 80°C,> 80°C
Equal: (kosong)
Min: 60
Max: (kosong)
```

**Expected Behavior:**
| User Pilih | Extract Value | Badge | Warna | Pesan |
|------------|---------------|-------|-------|-------|
| < 60°C | 60 | ✗ * | Merah | Di bawah batas minimum (60) |
| 60 - 70°C | 60 | ✓ | Hijau | Di atas batas minimum (60) |
| 70 - 80°C | 70 | ✓ | Hijau | Di atas batas minimum (60) |
| > 80°C | 80 | ✓ | Hijau | Di atas batas minimum (60) |

---

### ✅ **Test 6: Tanpa Validasi**

**Setup Parameter:**
```
Nama: Catatan Visual
is_option: 1 (checked)
Option Values: Normal,Ada Noda,Ada Endapan,Ada Sedimen
Equal: (kosong)
Min: (kosong)
Max: (kosong)
```

**Expected Behavior:**
| User Pilih | Badge | Warna | Pesan |
|------------|-------|-------|-------|
| Normal | ✓ | Hijau | Terpilih |
| Ada Noda | ✓ | Hijau | Terpilih |
| Ada Endapan | ✓ | Hijau | Terpilih |
| Ada Sedimen | ✓ | Hijau | Terpilih |

**Note:** Semua pilihan akan badge hijau karena tidak ada kriteria validasi.

---

### ✅ **Test 7: Format Number ID (Indonesia)**

**Setup Parameter:**
```
Nama: Konsentrasi Larutan
is_option: 1 (checked)
Option Values: 0,5,1,234,1,5,2,345
Number Format: id (Indonesia)
Equal: 1,5
Min: (kosong)
Max: (kosong)
```

**Expected Behavior:**
| User Pilih | Parse as Float | Badge | Warna | Pesan |
|------------|----------------|-------|-------|-------|
| 0,5 | 0.5 | ✗ * | Merah | Tidak sesuai nilai baku mutu (Expected: 1,5) |
| 1,234 | 1.234 | ✗ * | Merah | Tidak sesuai nilai baku mutu (Expected: 1,5) |
| 1,5 | 1.5 | ✓ | Hijau | Sesuai nilai baku mutu |
| 2,345 | 2.345 | ✗ * | Merah | Tidak sesuai nilai baku mutu (Expected: 1,5) |

**Cara Test:**
1. Set parameter dengan `number_format = 'id'`
2. Pilih "1,5" → Parse as 1.5 → Match with equal 1.5 → Badge hijau
3. Console: `Dropdown validation - Value: 1.5, ... Equal: 1.5`

---

### ✅ **Test 8: Mikrobiologi - Status Cemaran**

**Setup Parameter:**
```
Nama: E. coli
is_option: 1 (checked)
Option Values: Negatif,Positif (+),Positif (++),Positif (+++)
Equal: Negatif
Min: (kosong)
Max: (kosong)
```

**Expected Behavior:**
| User Pilih | Badge | Warna | Pesan |
|------------|-------|-------|-------|
| Negatif | ✓ | Hijau | Sesuai standar |
| Positif (+) | ✗ * | Merah | Tidak sesuai standar (Expected: Negatif) |
| Positif (++) | ✗ * | Merah | Tidak sesuai standar (Expected: Negatif) |
| Positif (+++) | ✗ * | Merah | Tidak sesuai standar (Expected: Negatif) |

---

## 🔍 Debugging Checklist

### Jika Badge Tidak Muncul:

1. **Check Console Log:**
   ```
   Creating dropdown for options: [...]
   Dropdown validation - Value: ..., Min: ..., Max: ..., Equal: ...
   ```

2. **Check Element Badge:**
   ```javascript
   $('#badge_0') // Ganti 0 dengan index parameter
   ```

3. **Check Data Attributes:**
   ```html
   <select data-min="0" data-max="50" data-equal="" data-number-format="en">
   ```

4. **Check parseNumberInput Available:**
   ```javascript
   console.log(typeof parseNumberInput); // Should be "function"
   ```

---

### Jika Validasi Salah:

1. **Check Extract Value:**
   - Console log akan show "Value: X"
   - Pastikan X adalah angka yang benar

2. **Check Baku Mutu Values:**
   - Console log akan show "Min: X, Max: Y, Equal: Z"
   - Pastikan nilai dari database benar

3. **Check Number Format:**
   - Untuk format ID: "1,5" → 1.5
   - Untuk format EN: "1.5" → 1.5

---

## 📊 Test Report Template

| Test Case | Parameter | Option Dipilih | Expected Badge | Actual Badge | Status |
|-----------|-----------|----------------|----------------|--------------|--------|
| Test 1 | Kejernihan | Jernih | ✓ Hijau | ✓ Hijau | ✅ PASS |
| Test 1 | Kejernihan | Keruh | ✗ Merah | ✗ Merah | ✅ PASS |
| Test 2 | Total Coliform | < 3 | ✓ Hijau | ✓ Hijau | ✅ PASS |
| Test 2 | Total Coliform | > 50 | ✗ Merah | ✗ Merah | ✅ PASS |
| Test 3 | pH Optimal | 7 | ✓ Hijau | ✓ Hijau | ✅ PASS |
| Test 3 | pH Optimal | 6.5 | ✗ Merah | ✗ Merah | ✅ PASS |
| ... | ... | ... | ... | ... | ... |

---

## 🎓 Kesimpulan Testing

**SEMUA Test Case HARUS PASS** untuk memastikan:

1. ✅ Dropdown muncul untuk parameter dengan `is_option = 1`
2. ✅ Badge validasi muncul saat pilih option
3. ✅ Validasi Equal berfungsi (kategorikal & numerik)
4. ✅ Validasi Min-Max berfungsi (range check)
5. ✅ Validasi Min/Max only berfungsi
6. ✅ Parsing numerik dengan operator (<, >, dll) berfungsi
7. ✅ Number format ID/EN ter-support
8. ✅ Console log muncul untuk debugging

---

**Test Document Version:** 1.0  
**Date:** 29 Desember 2025  
**Status:** ✅ READY FOR TESTING

