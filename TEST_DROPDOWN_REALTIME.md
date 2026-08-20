# 🧪 Quick Test: Dropdown Real-Time Validation

## 🎯 Test Langsung di Browser

Ikuti langkah-langkah ini untuk memverifikasi bahwa **validasi baku mutu muncul saat select option**.

---

## ✅ Test 1: Badge Muncul Saat Select

### **Langkah:**
1. Buka form analis di browser
2. Buka **Console** (tekan F12)
3. Cari parameter yang memiliki dropdown (bukan text editor)
4. **Pilih option dari dropdown**
5. **Lihat badge muncul di bawah dropdown**

### **Expected Result:**
```
✅ Badge langsung muncul (< 1 detik)
✅ Badge berwarna hijau (normal) atau merah (abnormal)
✅ Badge ada text "Sesuai standar" atau "Tidak sesuai standar"
✅ Console log muncul (jika numerik)
```

### **Jika Badge TIDAK Muncul:**
```javascript
// Di console, coba:
$('select.hasil-input-inline').length
// Harus return > 0

$('select.hasil-input-inline').first().trigger('change')
// Force trigger event
```

---

## ✅ Test 2: Validasi Kategorikal (Equal)

### **Setup Parameter (di Master Parameter):**
```
Nama: Kejernihan Air
is_option: 1 (checked)
Option Values: Jernih,Keruh,Agak Keruh
Equal: Jernih
```

### **Test Steps:**
1. Refresh form analis
2. Cari parameter "Kejernihan Air"
3. **Pilih "Jernih"**
   - Expected: ✅ Badge HIJAU "Sesuai standar"
4. **Ganti ke "Keruh"**
   - Expected: ❌ Badge MERAH "Tidak sesuai standar (Expected: Jernih)" + Bintang (*)

### **Screenshot Expected:**

**Pilih "Jernih":**
```
[Dropdown: Jernih      ▼]

✅ Jernih
   Sesuai standar
```

**Ganti ke "Keruh":**
```
[Dropdown: Keruh       ▼]

❌ Keruh *
   Tidak sesuai standar
   (Expected: Jernih)
```

---

## ✅ Test 3: Validasi Numerik (Min-Max)

### **Setup Parameter:**
```
Nama: Total Coliform
is_option: 1 (checked)
Option Values: < 3,3 - 10,10 - 50,> 50
Min: 0
Max: 50
Satuan: CFU/100mL
```

### **Test Steps:**
1. Refresh form analis
2. Cari parameter "Total Coliform"
3. **Pilih "< 3"**
   - Expected: ✅ Badge HIJAU "Dalam rentang baku mutu (0 - 50)"
   - Console: `Dropdown validation - Value: 3, Min: 0, Max: 50, Equal: null`
4. **Ganti ke "> 50"**
   - Expected: ❌ Badge MERAH "Di luar rentang baku mutu (0 - 50)" + Bintang (*)
   - Console: `Dropdown validation - Value: 50, Min: 0, Max: 50, Equal: null`

---

## ✅ Test 4: Validasi Numerik (Equal)

### **Setup Parameter:**
```
Nama: pH Optimal
is_option: 1 (checked)
Option Values: 6,6.5,7,7.5,8
Equal: 7
```

### **Test Steps:**
1. **Pilih "7"**
   - Expected: ✅ Badge HIJAU "Sesuai nilai baku mutu"
2. **Ganti ke "6.5"**
   - Expected: ❌ Badge MERAH "Tidak sesuai nilai baku mutu (Expected: 7)" + Bintang (*)

---

## ✅ Test 5: Dropdown vs Text Editor

### **Test Scenario:**
Pastikan dropdown hanya muncul untuk parameter dengan `is_option = 1`

### **Test Steps:**
1. Cari parameter dengan `is_option = 0` (contoh: pH yang input bebas)
   - Expected: **TinyMCE text editor** (bukan dropdown)
2. Cari parameter dengan `is_option = 1` (contoh: Kejernihan)
   - Expected: **Dropdown select** (bukan text editor)

### **Console Check:**
```javascript
// Count dropdown
$('select.hasil-input-inline').length

// Count TinyMCE editor
$('.inline-hasil-editor').length

// Total harus match dengan jumlah parameter
```

---

## ✅ Test 6: Multiple Select (Ganti-ganti Option)

### **Test Scenario:**
Ganti option berkali-kali, badge harus selalu update

### **Test Steps:**
1. Pilih parameter dropdown apapun
2. **Pilih option 1** → Badge muncul
3. **Ganti ke option 2** → Badge update (bukan duplikat)
4. **Ganti ke option 3** → Badge update lagi
5. **Kembali ke option 1** → Badge update lagi

### **Expected Result:**
```
✅ Hanya 1 badge yang tampil (tidak duplikat)
✅ Badge selalu update sesuai pilihan terbaru
✅ Warna badge sesuai validasi (hijau/merah)
```

---

## ✅ Test 7: Console Log Debugging

### **Test Steps:**
1. Buka Console (F12)
2. Pilih option yang mengandung angka (contoh: "< 3")
3. **Lihat console log:**

### **Expected Console Output:**
```
Creating dropdown for options: Array(4) ["< 3", "3 - 10", "10 - 50", "> 50"]
Dropdown validation - Value: 3, Min: 0, Max: 50, Equal: null
```

### **Jika Console KOSONG:**
```javascript
// Check apakah parseNumberInput available:
console.log(typeof parseNumberInput);
// Should return: "function"

// Check apakah number-format-helper.js loaded:
console.log(document.querySelector('script[src*="number-format-helper"]'));
// Should return: <script> element
```

---

## ✅ Test 8: Badge untuk "- Pilih -"

### **Test Steps:**
1. Dropdown default (belum pilih apa-apa): "- Pilih -"
2. **Pilih option valid** → Badge muncul
3. **Ganti kembali ke "- Pilih -"** → Badge hilang

### **Expected Result:**
```
State: "- Pilih -"     → Tidak ada badge
State: "Jernih"        → Badge hijau muncul
State: "- Pilih -"     → Badge hilang lagi
```

---

## ✅ Test 9: Form Submit

### **Test Scenario:**
Pastikan nilai dropdown tersimpan ke database

### **Test Steps:**
1. Pilih option dari dropdown (contoh: "Positif (+)")
2. Badge merah muncul (abnormal)
3. **Klik "Simpan Hasil"**
4. Refresh page atau buka form lagi
5. **Lihat dropdown** → Harus ter-select "Positif (+)"
6. **Lihat badge** → Harus muncul badge merah lagi

### **Expected Result:**
```
✅ Nilai tersimpan ke database
✅ Dropdown ter-select dengan benar
✅ Badge validasi muncul lagi (konsisten)
```

---

## ✅ Test 10: Number Format ID vs EN

### **Setup 2 Parameter:**

**Parameter A:**
```
Nama: Konsentrasi A
is_option: 1
Option Values: 0,5,1,0,1,5,2,0
Number Format: id (Indonesia)
Equal: 1,5
```

**Parameter B:**
```
Nama: Konsentrasi B
is_option: 1
Option Values: 0.5,1.0,1.5,2.0
Number Format: en (International)
Equal: 1.5
```

### **Test Steps:**
1. **Parameter A (Format ID):**
   - Pilih "1,5" → ✅ Badge HIJAU (parse as 1.5, match)
   - Pilih "1,0" → ❌ Badge MERAH (parse as 1.0, not match)

2. **Parameter B (Format EN):**
   - Pilih "1.5" → ✅ Badge HIJAU (parse as 1.5, match)
   - Pilih "1.0" → ❌ Badge MERAH (parse as 1.0, not match)

---

## 🐛 Troubleshooting Checklist

### **Badge Tidak Muncul?**

| Check | Command/Action | Expected |
|-------|---------------|----------|
| Dropdown exists? | `$('select.hasil-input-inline').length` | > 0 |
| Badge container exists? | `$('[id^="badge_"]').length` | > 0 |
| Event handler attached? | `$('select.hasil-input-inline').first().data('events')` | {change: Array(1)} |
| Function available? | `typeof window.AnalisInlineEdit` | "object" |
| parseNumberInput loaded? | `typeof parseNumberInput` | "function" |

### **Validasi Salah?**

| Check | Action |
|-------|--------|
| Check baku mutu value | Inspect `data-min`, `data-max`, `data-equal` on select |
| Check parsing | Console log "Dropdown validation - Value: X" |
| Check number format | Inspect `data-number-format` on select |

### **Console Error?**

```javascript
// Common errors:
"parseNumberInput is not defined"
→ Fix: Pastikan number-format-helper.js loaded

"Cannot read property 'updateResultBadgeForOption' of undefined"
→ Fix: Pastikan analis-inline-editing.js loaded

"$ is not defined"
→ Fix: Pastikan jQuery loaded dulu
```

---

## 📊 Test Result Template

| Test # | Test Name | Status | Notes |
|--------|-----------|--------|-------|
| 1 | Badge muncul saat select | ☐ PASS ☐ FAIL | |
| 2 | Validasi kategorikal (Equal) | ☐ PASS ☐ FAIL | |
| 3 | Validasi numerik (Min-Max) | ☐ PASS ☐ FAIL | |
| 4 | Validasi numerik (Equal) | ☐ PASS ☐ FAIL | |
| 5 | Dropdown vs Text Editor | ☐ PASS ☐ FAIL | |
| 6 | Multiple select (ganti-ganti) | ☐ PASS ☐ FAIL | |
| 7 | Console log debugging | ☐ PASS ☐ FAIL | |
| 8 | Badge untuk "- Pilih -" | ☐ PASS ☐ FAIL | |
| 9 | Form submit | ☐ PASS ☐ FAIL | |
| 10 | Number format ID vs EN | ☐ PASS ☐ FAIL | |

---

## 🎓 Quick Reference

### **Manual Test di Console:**

```javascript
// 1. Lihat semua dropdown
$('select.hasil-input-inline').each(function(i, el) {
    console.log(i, $(el).attr('data-param-id'), $(el).val());
});

// 2. Trigger validasi manual
var $dropdown = $('select.hasil-input-inline').first();
$dropdown.val('Positif (+)').trigger('change');

// 3. Lihat badge content
$('[id^="badge_"]').each(function(i, el) {
    console.log(i, $(el).html());
});

// 4. Check data attributes
var $dropdown = $('select.hasil-input-inline').first();
console.log({
    min: $dropdown.data('min'),
    max: $dropdown.data('max'),
    equal: $dropdown.data('equal'),
    numberFormat: $dropdown.data('number-format')
});
```

---

## ✅ Success Criteria

**SEMUA Test HARUS PASS untuk memastikan:**
- ✅ Dropdown muncul untuk `is_option = 1`
- ✅ Badge validasi muncul **real-time** saat select
- ✅ Validasi Equal/Min/Max berfungsi
- ✅ Parsing numerik dengan operator berfungsi
- ✅ Number format ID/EN ter-support
- ✅ Console log muncul untuk debugging
- ✅ Form submit menyimpan nilai dengan benar

---

**Quick Test Guide**  
**Version:** 1.0  
**Date:** 29 Desember 2025  
**Estimated Test Time:** 10-15 menit  
**Status:** ✅ READY TO TEST

