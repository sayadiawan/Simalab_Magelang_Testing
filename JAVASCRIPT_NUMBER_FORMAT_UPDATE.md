# JavaScript Number Format Update Guide

## Overview
Semua blade yang handle input/display angka perlu diupdate untuk support format Indonesia (ID) dan International (EN).

## ✅ File Helper yang Sudah Dibuat

### `public/assets/js/number-format-helper.js`
Helper functions untuk parsing dan formatting angka:

```javascript
// Parse number dari format ID/EN ke float
parseNumberInput(value, format)  // format: 'id' atau 'en'

// Format number ke ID/EN untuk display
formatNumberDisplay(value, format, decimals)

// Auto-detect format
detectNumberFormat(value)
parseNumberAuto(value)

// Compare numbers
compareNumbers(value1, value2, format)
```

## ✅ Update Yang Sudah Dilakukan

### 1. verification-permohonan-uji-paramater-klinik.blade.php
- ✅ Added `<script src="{{ asset('assets/js/number-format-helper.js') }}"></script>`
- ✅ Updated `function checkBakuMutu()` signature: added `numberFormat` parameter
- ✅ Replaced all `parseFloat()` dengan `parseNumberInput(value, numberFormat)`
- ✅ Min/max always parsed dengan format 'en' (database format)
- ✅ Backward compatible: default `numberFormat = 'en'` jika tidak diberikan

### 2. analis-permohonan-uji-paramater-klinik.blade.php
- ✅ Added `<script src="{{ asset('assets/js/number-format-helper.js') }}"></script>`
- ⏳ Perlu update fungsi yang handle input/validation angka

## 🚧 Yang Masih Perlu Dilakukan

### Step-by-Step untuk Setiap Blade:

#### 1. Add Helper Script (di section head atau setelah jQuery)
```blade
{{-- Number Format Helper --}}
<script src="{{ asset('assets/js/number-format-helper.js') }}"></script>
```

#### 2. Pass Number Format ke JavaScript
```blade
<script>
    // Set global number format dari PHP (dari parameter satuan)
    var globalNumberFormat = '{{ $numberFormat ?? "en" }}';
    
    // Atau per-parameter jika ada multiple formats
    var parameterFormats = {
        'param_id_1': 'id',
        'param_id_2': 'en'
    };
</script>
```

#### 3. Update Fungsi checkBakuMutu Calls
```javascript
// BEFORE (tanpa number format)
var badge = checkBakuMutu(value, min, max, equal, offset, multipleBM, kesimpulan);

// AFTER (dengan number format)
var badge = checkBakuMutu(value, min, max, equal, offset, multipleBM, kesimpulan, numberFormat);
```

#### 4. Update Input Validation
```javascript
// BEFORE
$('#hasil_input').on('blur', function() {
    var value = parseFloat($(this).val());
    if (!isNaN(value)) {
        // do something
    }
});

// AFTER
$('#hasil_input').on('blur', function() {
    var inputValue = $(this).val();
    var numberFormat = $(this).data('number-format') || 'en';
    var value = parseNumberInput(inputValue, numberFormat);
    if (value !== null && !isNaN(value)) {
        // do something
    }
});
```

#### 5. Format Display Output
```javascript
// BEFORE
$('#display').text(value);

// AFTER
var numberFormat = 'id'; // atau dari data attribute
var formatted = formatNumberDisplay(value, numberFormat);
$('#display').text(formatted);
```

## 📋 Checklist Files yang Perlu Update

### Admin Laboratorium Views:

- [x] `permohonan-uji-klinik-2/verification-permohonan-uji-paramater-klinik.blade.php` ✅ **DONE**
  - checkBakuMutu updated dengan parameter numberFormat
  - All parseFloat replaced dengan parseNumberInput
- [x] `permohonan-uji-klinik-2/analis-permohonan-uji-paramater-klinik.blade.php` ✅ **DONE**
  - checkBakuMutu updated dengan parameter numberFormat
  - All parseFloat replaced dengan parseNumberInput
  - Multiple baku mutu parsing updated
- [ ] `permohonan-uji-klinik-2/formatPrint/hasil-klinik.blade.php` ⏳ **TODO**
  - Format display values sesuai parameter
- [ ] `permohonan-uji-klinik-2/formatPrint/hasil-klinik-2.blade.php` ⏳ **TODO**
- [x] `baku-mutu-klinik/edit.blade.php` ✅ **DONE**
  - PHP side already updated
  - JavaScript validation updated

### Mobile Testing Views:

- [ ] `mobile/testing/klinik/pemeriksa.blade.php` ⏳ **TODO**
  - Input validation dengan format (uses parseFloat)
  - Real-time check baku mutu
  - Need to add number-format-helper.js
- [ ] `mobile/testing/baca-hasil.blade.php` ⏳ **TODO**
  - Display dengan format yang benar
  - Need to add number-format-helper.js
- [ ] `mobile/testing/klinik/verifikasi.blade.php` ⏳ **TODO**
  - Display dengan format yang benar
- [ ] `mobile/testing/verifikasi-hasil.blade.php` ⏳ **TODO**
- [ ] `mobile/testing/pengesahan-hasil.blade.php` ⏳ **TODO**

## 🔍 Cara Mencari Files yang Perlu Update

### Grep Commands:
```bash
# Cari semua yang gunakan parseFloat untuk input hasil
grep -r "parseFloat.*hasil" package/masterweb/src/views/

# Cari semua yang ada checkBakuMutu call (JavaScript)
grep -r "checkBakuMutu(" package/masterweb/src/views/

# Cari input fields untuk angka
grep -r "input.*type.*number\|input.*hasil\|input.*value" package/masterweb/src/views/module/admin/laboratorium/permohonan-uji-klinik-2/

# Cari display hasil/nilai
grep -r "hasil_permohonan_uji" package/masterweb/src/views/
```

## 📝 Example: Complete Update Pattern

### Before:
```javascript
$('#hasil_input_123').on('blur', function() {
    var value = $(this).val();
    var min = $('#min_123').val();
    var max = $('#max_123').val();
    
    var numValue = parseFloat(value);
    var numMin = parseFloat(min);
    var numMax = parseFloat(max);
    
    if (numValue < numMin || numValue > numMax) {
        alert('Nilai di luar baku mutu!');
    }
    
    // Display result
    var badge = checkBakuMutu(value, min, max, null, 'default', null, null);
    $('#result_123').html(badge);
});
```

### After:
```javascript
// Get number format from data attribute or global
var numberFormat = $('#hasil_input_123').data('number-format') || globalNumberFormat || 'en';

$('#hasil_input_123').on('blur', function() {
    var value = $(this).val();
    var min = $('#min_123').val();
    var max = $('#max_123').val();
    
    // Parse dengan format yang benar
    var numValue = parseNumberInput(value, numberFormat);
    var numMin = parseNumberInput(min, 'en'); // DB format always EN
    var numMax = parseNumberInput(max, 'en');
    
    if (numValue !== null && (numValue < numMin || numValue > numMax)) {
        alert('Nilai di luar baku mutu!');
    }
    
    // Display result dengan format
    var badge = checkBakuMutu(value, min, max, null, 'default', null, null, numberFormat);
    $('#result_123').html(badge);
});
```

## ⚠️ Important Notes

1. **Database values ALWAYS in EN format** (1234.56)
   - When parsing min/max from database, use format 'en'
   - When saving to database, convert input to EN format

2. **User input uses parameter's number_format**
   - Get from parameter satuan klinik
   - Pass to parseNumberInput()

3. **Display uses parameter's number_format**
   - Get from parameter satuan klinik
   - Pass to formatNumberDisplay()

4. **Backward Compatibility**
   - Always provide default: `numberFormat || 'en'`
   - Old data without format will work as before

5. **Testing**
   - Test dengan ID format: 1.234,56
   - Test dengan EN format: 1,234.56
   - Test edge cases: 0.05, 1000000, -123.45

## 🧪 Test Scenarios

```javascript
// Test parsing
console.log(parseNumberInput('1.234,56', 'id'));  // Should: 1234.56
console.log(parseNumberInput('1,234.56', 'en'));  // Should: 1234.56
console.log(parseNumberInput('0,05', 'id'));      // Should: 0.05

// Test formatting
console.log(formatNumberDisplay(1234.56, 'id'));  // Should: '1.234,56'
console.log(formatNumberDisplay(1234.56, 'en'));  // Should: '1,234.56'
console.log(formatNumberDisplay(0.05, 'id', 2));  // Should: '0,05'

// Test comparison
console.log(compareNumbers('1.234,56', '1000', 'id'));  // Should: 1 (greater)
console.log(compareNumbers('0,5', '5,0', 'id'));        // Should: -1 (less)
```

## 🔗 Related Files

- Helper: `public/assets/js/number-format-helper.js`
- PHP Helpers: `app/Smt.php` (rubahNilaikeForm, rubahNilaikeHtml)
- Documentation: `DOCS_NUMBER_FORMAT.md`
- Status: `NUMBER_FORMAT_IMPLEMENTATION_STATUS.md`

