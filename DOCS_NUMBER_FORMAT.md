# Dokumentasi Format Angka (Number Format)

## Overview
Sistem ini mendukung 2 format angka:
- **ID (Indonesia)**: Pemisah ribuan = titik (.), Pemisah desimal = koma (,). Contoh: 1.234,56
- **EN (International/English)**: Pemisah ribuan = koma (,), Pemisah desimal = titik (.). Contoh: 1,234.56

## Database Standard
**PENTING**: Database SELALU menyimpan angka dalam format EN (1234.56 atau 1,234.56)
- Ini memastikan konsistensi dan kemudahan perhitungan
- Field yang menyimpan angka: min, max, equal, hasil, dll.

## Konfigurasi Per Parameter
Setiap `ParameterSatuanKlinik` memiliki kolom `number_format`:
- Default: 'en'
- Options: 'id' | 'en'
- Lokasi: tabel `ms_parameter_satuan_klinik`, kolom `number_format`

## Helper Functions (PHP)

### 1. `parseNumberInput($value, $format = 'en')`
**Tujuan**: Parse number string ke float untuk comparison
- Input: string dengan format ID atau EN
- Output: float untuk calculation
- **PENTING**: Hapus SEMUA pemisah ribuan dan whitespace

**Logic (Sama dengan JavaScript):**
```php
// Step 1: Remove ALL whitespace (space, tab, nbsp)
$value = preg_replace('/\s+/', '', $value);

// Step 2: Format ID
if ($format === 'id') {
    $value = str_replace('.', '', $value);  // Remove ribuan (titik)
    $value = str_replace(',', '.', $value); // Koma → titik (desimal)
}
// Format EN
else {
    $value = str_replace(',', '', $value);  // Remove ribuan (koma)
}

// Step 3: Cleanup non-numeric
$value = preg_replace('/[^\d.-]/', '', $value);
```

**Contoh:**
```php
parseNumberInput('1.234,56', 'id')   // → 1234.56
parseNumberInput('1 234,56', 'id')   // → 1234.56 (hapus whitespace)
parseNumberInput('1,234.56', 'en')   // → 1234.56
```

### 2. `rubahNilaikeForm($value, $numberFormat = null)`
**Tujuan**: Convert dari database format (EN) ke format form input
- Input: nilai dari database (EN format)
- Output: nilai untuk ditampilkan di form input
- Parameter `$numberFormat`:
  - `null` atau `'en'`: tidak ada konversi (keep EN format)
  - `'id'`: convert ke ID format (1234.56 → 1.234,56)

**Logic:**
- Hapus SEMUA whitespace dengan `preg_replace('/\s+/', '', $value)`
- Remove ribuan sesuai format
- Cleanup dengan regex `preg_replace('/[^\d.-]/', '', $value)`

**Contoh:**
```php
rubahNilaikeForm('1234.56', 'id')     // → 1.234,56
rubahNilaikeForm('1 234.56', 'id')    // → 1.234,56 (robust dengan whitespace)
```

### 3. `rubahNilaikeHtml($value, $inputNumberFormat = null)`
**Tujuan**: Convert dari form input ke database format (EN) + HTML processing
- Input: nilai dari form user
- Output: nilai untuk disimpan ke database (EN format) dengan HTML entities
- Parameter `$inputNumberFormat`:
  - `null` atau `'en'`: input sudah EN format
  - `'id'`: input dalam ID format, convert ke EN

**Logic:**
- Hapus SEMUA whitespace dengan `preg_replace('/\s+/', '', $value)`
- Remove pemisah ribuan (titik untuk ID, koma untuk EN)
- Cleanup dengan regex

**Contoh:**
```php
rubahNilaikeHtml('1.234,56', 'id')    // → 1234.56 (ke database)
rubahNilaikeHtml('1 234,56', 'id')    // → 1234.56 (robust dengan whitespace)
```

### 4. `convertNumberToDatabase($value, $inputFormat = 'en')`
**Tujuan**: Wrapper untuk convert ke database format
- Menggunakan `parseNumberInput()` internally
- Return string dengan format EN
- 10 decimals untuk preserve precision

**Contoh:**
```php
convertNumberToDatabase('1.234,56', 'id')  // → "1234.5600000000"
```

## Implementasi di Blade

### Saat Display (Load Data)
```blade
{{-- Ambil number format dari parameter satuan --}}
@php
    $numberFormat = $parameter_satuan->number_format ?? 'en';
@endphp

{{-- Display nilai dengan format yang sesuai --}}
<input type="text" value="{{ rubahNilaikeForm($item->min, $numberFormat) }}">
```

### Saat Save (Submit Form)
```blade
<form id="myForm">
    <input type="hidden" name="input_number_format" value="{{ $numberFormat }}">
    <input type="text" name="min" id="min">
</form>

<script>
// Saat submit, pass format ke backend
$('#myForm').submit(function() {
    // Backend controller akan handle konversi
});
</script>
```

### Di Controller (Save)
```php
public function store(Request $request)
{
    $inputFormat = $request->post('input_number_format', 'en');
    
    // Convert input value ke database format (EN)
    $minValue = rubahNilaikeHtml($request->post('min'), $inputFormat);
    
    // Save ke database (sudah EN format)
    $model->min = $minValue;
    $model->save();
}
```

## Implementasi di Controller

### Saat Retrieve Data
```php
public function edit($id)
{
    $item = BakuMutu::with('parametersatuanklinik')->find($id);
    
    // Get number format dari parameter satuan
    $numberFormat = $item->parametersatuanklinik->number_format ?? 'en';
    
    return view('edit', [
        'item' => $item,
        'numberFormat' => $numberFormat
    ]);
}
```

## JavaScript Number Parsing

### Parsing Database Values (min/max/equal)

**PENTING**: Selalu cek `numberFormat` dulu, jika tidak ada baru fallback ke 'en'

```javascript
// ❌ BAD - Hardcoded 'en'
var minNum = parseNumberInput(min, 'en');

// ✅ GOOD - Check numberFormat first
var dbFormat = numberFormat || 'en';
var minNum = parseNumberInput(min, dbFormat);
```

**Rationale**:
- Database values **seharusnya** selalu dalam format EN
- Namun untuk **backward compatibility** dan **safety**, kita tetap cek `numberFormat`
- Jika parameter memiliki `number_format = 'id'`, kemungkinan ada data lama yang disimpan dalam format ID
- Dengan `numberFormat || 'en'`, kita ensure parsing yang benar untuk semua skenario

### Parsing User Input

User input SELALU menggunakan `numberFormat` dari parameter:

```javascript
// User input
var numValue = parseNumberInput(value, numberFormat);

// Database values (min/max)
var dbFormat = numberFormat || 'en';
var minNum = parseNumberInput(min, dbFormat);
var maxNum = parseNumberInput(max, dbFormat);

// Comparison
if (numValue < minNum || numValue > maxNum) {
    melewati = true;
}
```

### Parsing Behavior (PENTING!)

**Saat Perbandingan:**
1. ✅ **Hilangkan SEMUA pemisah ribuan** (titik untuk ID, koma untuk EN)
2. ✅ **Parse hanya pemisah desimal** (koma untuk ID → titik, titik untuk EN → tetap)
3. ✅ **Hapus whitespace** sebelum parsing

**Contoh Parsing:**
```javascript
// Format ID
parseNumberInput('1.234,56', 'id')  // → 1234.56 (hapus titik ribuan, koma→titik desimal)
parseNumberInput('1.234', 'id')     // → 1234 (hapus titik ribuan)

// Format EN
parseNumberInput('1,234.56', 'en')  // → 1234.56 (hapus koma ribuan, titik desimal tetap)
parseNumberInput('1,234', 'en')     // → 1234 (hapus koma ribuan)
```

**Saat Display:**
- ✅ **Tampilkan nilai ASLI** (original input dari user)
- ❌ **JANGAN format ulang** setelah perbandingan
- ✅ Gunakan `toFormatHtml(value)` yang hanya convert karakter khusus (^, _, ≤, ≥, ±)

**Contoh Display:**
```javascript
// User input: "1.234,56"
var numValue = parseNumberInput("1.234,56", 'id'); // → 1234.56 (untuk comparison)
var displayValue = toFormatHtml("1.234,56");       // → "1.234,56" (display original)

// Result badge
createResultBadge(displayValue, status); // Display: "1.234,56" (bukan "1,234.56")
```

## Best Practices

1. **Konsistensi Database**: SELALU simpan dalam format EN
2. **User Experience**: Display sesuai preferensi user (ID/EN)
3. **Validation**: Validate input sesuai format yang dipilih
4. **Backward Compatibility**: Jika `number_format` NULL, default ke 'en'
5. **JavaScript Parsing**: SELALU gunakan `numberFormat || 'en'` untuk database values (min/max/equal)
6. **Safety First**: Jangan hardcode format, selalu cek variable dulu

## Files Yang Diupdate

### Phase 1 - Foundation
- ✅ Migration: `2025_12_29_151132_add_number_format_to_ms_parameter_satuan_klinik_table.php`
- ✅ Model: `ParameterSatuanKlinik.php` (fillable)
- ✅ Controller: `LaboratoriumParameterSatuanKlinikManagement.php` (store/update)
- ✅ Views: `parameter-satuan-klinik/add.blade.php`, `parameter-satuan-klinik/edit.blade.php`
- ✅ Helpers: `app/Smt.php` (rubahNilaikeForm, rubahNilaikeHtml)

### Phase 2 - Implementation (TODO)
- [ ] `baku-mutu-klinik/edit.blade.php` - Display baku mutu dengan format
- [ ] `analis-permohonan-uji-paramater-klinik.blade.php` - Input hasil dengan format
- [ ] `verification.blade.php` - Display verifikasi dengan format
- [ ] `hasil-klinik.blade.php` - Print hasil dengan format
- [ ] Mobile testing views - Input/display dengan format

## Catatan Penting

- **Jangan** ubah format di database yang sudah ada (tetap EN)
- **Selalu** pass `$numberFormat` parameter saat menggunakan helper functions
- **Test** dengan berbagai skenario: angka bulat, desimal, range, dll.
- **Backward Compatibility**: Kode lama tetap berfungsi (default EN)

