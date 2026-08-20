# Konsep Penyederhanaan Form Analis

## Perubahan Yang Dilakukan

### 1. Urutan Kolom Baru
```
| Nama Test | Hasil | Satuan | Keterangan | Kadar Maksimum |
```

### 2. Input Langsung (Tanpa Modal)

#### Untuk Parameter dengan Option (Dropdown):
```html
<td>
    <select class="form-control parameter-input" 
            data-param-id="{{ $id }}" 
            data-type="hasil"
            data-index="{{ $no }}">
        <option value="">-Pilih-</option>
        @foreach($options as $opt)
            <option>{{ $opt }}</option>
        @endforeach
    </select>
</td>
```

#### Untuk Parameter Numeric/Text (Input):
```html
<td>
    <input type="text" 
           class="form-control parameter-input" 
           data-param-id="{{ $id }}"
           data-type="hasil"
           data-index="{{ $no }}"
           data-min="{{ $min }}"
           data-max="{{ $max }}"
           data-number-format="{{ $format }}"
           value="{{ $current_value }}"
           placeholder="Masukkan hasil...">
    <!-- Preview Badge -->
    <div class="result-badge mt-2" id="badge_{{ $no }}"></div>
</td>
```

#### Untuk Keterangan (Textarea):
```html
<td>
    <textarea class="form-control parameter-input" 
              data-param-id="{{ $id }}"
              data-type="keterangan"
              data-index="{{ $no }}"
              rows="2"
              placeholder="Masukkan keterangan...">{{ $current_ket }}</textarea>
</td>
```

### 3. JavaScript untuk Keyboard Navigation

```javascript
$(document).ready(function() {
    // Auto-save on change
    $('.parameter-input').on('change blur', function() {
        var $input = $(this);
        var type = $input.data('type');
        var paramId = $input.data('param-id');
        var value = $input.val();
        var index = $input.data('index');
        
        // Update badge if type is 'hasil'
        if (type === 'hasil') {
            updateResultBadge(index, value);
        }
        
        // Auto-save (optional)
        // saveParameter(paramId, type, value);
    });
    
    // Enter key: move to next input
    $('.parameter-input').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            var $inputs = $('.parameter-input');
            var currentIndex = $inputs.index(this);
            var $nextInput = $inputs.eq(currentIndex + 1);
            
            if ($nextInput.length) {
                $nextInput.focus();
            }
        }
    });
    
    // Arrow Down: move to same column, next row
    $('.parameter-input').on('keydown', function(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            var $currentTd = $(this).closest('td');
            var currentColIndex = $currentTd.index();
            var $currentTr = $currentTd.closest('tr');
            var $nextTr = $currentTr.nextAll('tr').not('[class*="group-header"]').first();
            
            if ($nextTr.length) {
                var $nextInput = $nextTr.find('td').eq(currentColIndex).find('.parameter-input');
                if ($nextInput.length) {
                    $nextInput.focus();
                }
            }
        }
    });
    
    // Arrow Up: move to same column, previous row
    $('.parameter-input').on('keydown', function(e) {
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            var $currentTd = $(this).closest('td');
            var currentColIndex = $currentTd.index();
            var $currentTr = $currentTd.closest('tr');
            var $prevTr = $currentTr.prevAll('tr').not('[class*="group-header"]').first();
            
            if ($prevTr.length) {
                var $prevInput = $prevTr.find('td').eq(currentColIndex).find('.parameter-input');
                if ($prevInput.length) {
                    $prevInput.focus();
                }
            }
        }
    });
    
    // Real-time badge update
    function updateResultBadge(index, value) {
        var $badge = $('#badge_' + index);
        var $input = $('.parameter-input[data-index="' + index + '"][data-type="hasil"]');
        var min = $input.data('min');
        var max = $input.data('max');
        var equal = $input.data('equal');
        var numberFormat = $input.data('number-format') || 'en';
        
        // Parse value
        var numValue = parseNumberInput(value, numberFormat);
        
        // Check baku mutu
        var isNormal = true;
        if (equal && value != equal) {
            isNormal = false;
        } else if (numValue !== null) {
            if (min && numValue < parseFloat(min)) isNormal = false;
            if (max && numValue > parseFloat(max)) isNormal = false;
        }
        
        // Update badge
        if (value) {
            if (isNormal) {
                $badge.html('<span class="badge badge-success"><i class="fa fa-check-circle"></i> ' + value + '</span>');
            } else {
                $badge.html('<span class="badge badge-danger"><i class="fa fa-times-circle"></i> ' + value + ' *</span>');
            }
        } else {
            $badge.html('');
        }
    }
});
```

### 4. CSS Tambahan

```css
.parameter-input {
    font-size: 14px;
    padding: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s;
}

.parameter-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.result-badge {
    margin-top: 8px;
}

.result-badge .badge {
    font-size: 13px;
    padding: 6px 10px;
}

/* Highlight row on input focus */
tr:has(.parameter-input:focus) {
    background-color: #f8f9ff;
}
```

## Keuntungan Pendekatan Ini

1. ✅ **Lebih Cepat**: Tidak perlu buka modal
2. ✅ **Keyboard-Friendly**: Enter/Arrow untuk navigasi
3. ✅ **Real-time Feedback**: Badge langsung update
4. ✅ **Lebih Sederhana**: UI yang lebih bersih
5. ✅ **Auto-save Ready**: Bisa tambah auto-save jika diperlukan

## Catatan Implementasi

- Modal existing bisa tetap di-keep sebagai backup untuk fungsi advanced (method, offset baku mutu, dll)
- Bisa tambahkan icon "Advanced" kecil di samping input untuk buka modal jika diperlukan
- Form submission tetap menggunakan textarea/input yang sudah ada, jadi backend tidak perlu diubah

