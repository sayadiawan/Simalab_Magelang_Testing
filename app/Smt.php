<?php

use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\StartNum;

if (!function_exists('set_input_crud')) {
  function set_input_crud($val_type, $val_name, $value = null, $relation = null)
  {
    //check kalo upload
    if ($val_type == "upload") {
      $ar =  '<input type="file" class="form-control dropify" name="' . $val_name . '" id="' . $val_name . '">';
      return $ar;
    }

    if (isset($relation[$val_name])) {
      $get_relation = $relation[$val_name];
      $data_relation = DB::table($get_relation[0])->whereNull('deleted_at')->get(); //get tabel


      $ar =   '<select name="' . $val_name . '" id="' . $val_name . '" class="form-control selected2">
                        <option value="">Pilih</option>';
      foreach ($data_relation as $item) {
        $id_relation = $get_relation[1];
        $val_relation = $get_relation[2];

        if ($item->$id_relation == $value) {
          $is_selected = "selected";
        } else {
          $is_selected = null;
        }


        $ar .= '<option value="' . $item->$id_relation . '" ' . $is_selected . '>' . $item->$val_relation . '</option>';
      }
      $ar .=  '</select>';
    } elseif ($val_type == "\BigInt") {
      $ar =  '<input type="number" class="form-control" name="' . $val_name . '" value="' . $value . '" id="' . $val_name . '" required>';
    } elseif ($val_type == "\String") {
      $ar = '<input type="text" class="form-control" name="' . $val_name . '" value="' . $value . '" id="' . $val_name . '" required>';
    } elseif ($val_type == "\Text") {
      $ar = '<textarea class="form-control" name="' . $val_name . '" id="' . $val_name . '" cols="30" rows="10" required>' . $value . '</textarea>';
    } elseif ($val_type == "\Date") {
      $ar = '<input type="date" class="form-control" name="' . $val_name . '" id="' . $val_name . '" value="' . $value . '" required>';
    } elseif ($val_type == "\Boolean") {
      //$cekhed = null;
      if ($value == "1") {
        $cekhed = "checked";
      } else {
        $cekhed = null;
      }


      $ar = '<div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" name="' . $val_name . '" id="' . $val_name . '" value="1" class="form-check-input" ' . $cekhed . '>
                            Aktif
                            <i class="input-helper"></i>
                        </label>
                    </div>';
    } else {
      $ar = "belum terdefinisi " . $val_type;
    }

    return $ar;
  }
}

if (!function_exists('rupiah')) {
  function rupiah($angka)
  {

    $hasil_rupiah = "Rp. " . number_format($angka, 0, ',', '.');
    return $hasil_rupiah;
  }
}

if (!function_exists('rupiahTanpaRp')) {
  function rupiahTanpaRp($angka)
  {

    $hasil_rupiah = number_format($angka, 0, ',', '.');
    return $hasil_rupiah;
  }
}



if (!function_exists('isNilaiBakuMutuHtmlTable')) {
  function isNilaiBakuMutuHtmlTable($value)
  {
    return is_string($value) && preg_match('/<table[\s>]/i', $value);
  }
}

if (!function_exists('encodeNilaiBakuMutuStorage')) {
  /**
   * Encode HTML tabel ke base64 agar aman disimpan di kolom latin1.
   * Prefix [[BMHTML]] menandakan nilai ter-encode.
   */
  function encodeNilaiBakuMutuStorage($value)
  {
    if (!is_string($value) || $value === '') {
      return $value;
    }
    if (strpos($value, '[[BMHTML]]') === 0) {
      return $value;
    }
    if (!isNilaiBakuMutuHtmlTable($value)) {
      return $value;
    }
    $value = trim($value);
    $value = preg_replace('/&nbsp;\s*$/', '', $value);
    return '[[BMHTML]]' . base64_encode($value);
  }
}

if (!function_exists('decodeNilaiBakuMutuStorage')) {
  /**
   * Decode nilai [[BMHTML]]... kembali ke HTML tabel.
   */
  function decodeNilaiBakuMutuStorage($value)
  {
    if (!is_string($value) || $value === '') {
      return $value;
    }
    $value = trim($value);
    if (strpos($value, '[[BMHTML]]') !== 0) {
      return $value;
    }
    $decoded = base64_decode(substr($value, 10), true);
    return ($decoded !== false && $decoded !== '') ? $decoded : $value;
  }
}

if (!function_exists('decodeNilaiBakuMutuValue')) {
  /**
   * Decode nilai baku mutu: satu atau beberapa segmen [[BMHTML]] yang digabung koma.
   */
  function decodeNilaiBakuMutuValue($value)
  {
    if (!is_string($value) || $value === '') {
      return $value;
    }

    $value = trim($value);
    if (strpos($value, '[[BMHTML]]') === false) {
      return $value;
    }

    $parts = preg_split('/,\s*(?=\[\[BMHTML\]\])/', $value);
    if (count($parts) <= 1) {
      return decodeNilaiBakuMutuStorage($value);
    }

    $decodedUnique = [];
    foreach ($parts as $part) {
      $part = trim($part);
      if ($part === '') {
        continue;
      }
      $decoded = decodeNilaiBakuMutuStorage($part);
      $key = is_string($decoded)
        ? preg_replace('/\s+/', ' ', trim(strip_tags($decoded)))
        : (string) $part;
      $decodedUnique[$key] = $decoded;
    }

    if (empty($decodedUnique)) {
      return decodeNilaiBakuMutuStorage($value);
    }

    return reset($decodedUnique);
  }
}

if (!function_exists('nilaiBakuMutuForDisplay')) {
  /**
   * Siapkan nilai baku mutu untuk ditampilkan di tabel/preview (decode, tanpa re-encode).
   */
  function nilaiBakuMutuForDisplay($value)
  {
    if ($value === null || $value === '') {
      return '';
    }

    $decoded = decodeNilaiBakuMutuValue($value);
    if (isNilaiBakuMutuHtmlTable($decoded)) {
      return '<div class="bmu-nilai-table-wrap">' . $decoded . '</div>';
    }

    if (is_string($value) && strpos(trim($value), '[[BMHTML]]') === 0) {
      return $decoded;
    }

    return rubahNilaikeHtml($value);
  }
}

if (!function_exists('nilaiNormalAlignClassFromHtml')) {
  /**
   * Kelas alignment kolom Nilai Normal: rata kiri jika tabel atau multi-baris,
   * rata tengah jika satu baris teks biasa (bukan tabel).
   */
  function nilaiNormalAlignClassFromHtml($html)
  {
    if ($html === null || $html === '' || $html === '-') {
      return 'nilai-normal-align-center';
    }

    if (!is_string($html)) {
      return 'nilai-normal-align-center';
    }

    if (isNilaiBakuMutuHtmlTable($html) || preg_match('/<table[\s>]/i', $html)) {
      return 'nilai-normal-align-left';
    }

    $withBreaks = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $plain = html_entity_decode(strip_tags($withBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\n/', $plain)), function ($line) {
      return $line !== '';
    }));

    if (count($lines) > 1) {
      return 'nilai-normal-align-left';
    }

    return 'nilai-normal-align-center';
  }
}

if (!function_exists('getNilaiRujukanLinesForParameter')) {
  /**
   * Bangun daftar baris nilai rujukan (fallback multiple baku mutu).
   */
  function getNilaiRujukanLinesForParameter(array $item_satuan_klinik)
  {
    $multipleBm = $item_satuan_klinik['multiple_baku_mutu'] ?? [];
    $showAllTiers = count($multipleBm) > 1;
    if ($showAllTiers) {
      $showAllTiers = false;
      foreach ($multipleBm as $bm) {
        if (!isset($bm['is_normal']) || (int) ($bm['is_normal'] ?? 0) !== 1) {
          $showAllTiers = true;
          break;
        }
      }
    }

    $nilaiRujukanLines = [];
    if ($showAllTiers) {
      $bakuMutuDisplayList = collect($multipleBm)
        ->sortBy(function ($bm) {
          if (isset($bm['min']) && $bm['min'] !== '' && $bm['min'] !== null) {
            return (float) $bm['min'];
          }
          if (isset($bm['max']) && $bm['max'] !== '' && $bm['max'] !== null) {
            return (float) $bm['max'];
          }
          return 999999;
        })
        ->values();

      foreach ($bakuMutuDisplayList as $bakuMutu) {
        $tierLine = null;
        if (!empty($bakuMutu['min']) && !empty($bakuMutu['max'])) {
          $tierLine = $bakuMutu['min'] . ' - ' . $bakuMutu['max'];
        } elseif (!empty($bakuMutu['min'])) {
          $tierLine = '≥ ' . $bakuMutu['min'];
        } elseif (!empty($bakuMutu['max'])) {
          if (!empty($bakuMutu['is_normal']) && (int) $bakuMutu['is_normal'] === 1) {
            $tierLine = '< ' . $bakuMutu['max'];
          } else {
            $tierLine = '≤ ' . $bakuMutu['max'];
          }
        } elseif (!empty($bakuMutu['equal'])) {
          $tierLine = '= ' . $bakuMutu['equal'];
        } elseif (!empty($bakuMutu['nilai_baku_mutu'])) {
          $tierLine = strip_tags($bakuMutu['nilai_baku_mutu']);
        }

        if ($tierLine !== null && $tierLine !== '') {
          if (!empty($bakuMutu['kesimpulan_baku_mutu'])) {
            $tierLine .= ' (' . strip_tags($bakuMutu['kesimpulan_baku_mutu']) . ')';
          }
          $nilaiRujukanLines[] = $tierLine;
        }
      }
    } else {
      $selMin = $item_satuan_klinik['min'] ?? null;
      $selMax = $item_satuan_klinik['max'] ?? null;
      $selEqual = $item_satuan_klinik['equal'] ?? null;
      $selNilai = $item_satuan_klinik['nilai_baku_mutu'] ?? null;
      $selKesimpulan = $item_satuan_klinik['kesimpulan_baku_mutu'] ?? null;
      $isNormal = (int) ($item_satuan_klinik['is_normal'] ?? 0);

      $tierLine = null;
      if (!empty($selMin) && !empty($selMax)) {
        $tierLine = $selMin . ' - ' . $selMax;
      } elseif (!empty($selMin)) {
        $tierLine = '≥ ' . $selMin;
      } elseif (!empty($selMax)) {
        if ($isNormal === 1) {
          $tierLine = '< ' . $selMax;
        } else {
          $tierLine = '≤ ' . $selMax;
        }
      } elseif (!empty($selEqual)) {
        $tierLine = '= ' . $selEqual;
      } elseif (!empty($selNilai)) {
        $tierLine = strip_tags($selNilai);
      }

      if ($tierLine !== null && $tierLine !== '') {
        if (!empty($selKesimpulan)) {
          $tierLine .= ' (' . strip_tags($selKesimpulan) . ')';
        }
        $nilaiRujukanLines[] = $tierLine;
      }
    }

    return $nilaiRujukanLines;
  }
}

if (!function_exists('nilaiNormalAlignClass')) {
  function nilaiNormalAlignClass(array $item_satuan_klinik)
  {
    $nilaiNormal = $item_satuan_klinik['keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik'] ?? null;
    if ($nilaiNormal === null || $nilaiNormal === '' || $nilaiNormal === '-') {
      $nilaiNormal = $item_satuan_klinik['nilai_baku_mutu'] ?? null;
    }
    $hasNilai = !empty($nilaiNormal) && $nilaiNormal !== '-';

    if ($hasNilai) {
      return nilaiNormalAlignClassFromHtml(rubahNilaikeForm($nilaiNormal));
    }

    $hasMultiple = isset($item_satuan_klinik['has_multiple_baku_mutu']) && $item_satuan_klinik['has_multiple_baku_mutu'];
    if ($hasMultiple) {
      $lines = getNilaiRujukanLinesForParameter($item_satuan_klinik);
      if (count($lines) > 1) {
        return 'nilai-normal-align-left';
      }
    }

    return 'nilai-normal-align-center';
  }
}

if (!function_exists('resolveMetodePermohonanForDisplay')) {
  /**
   * Metode untuk tampilan hasil: prioritas nilai permohonan, fallback master parameter.
   */
  function resolveMetodePermohonanForDisplay($item)
  {
    $method = trim(strip_tags((string) ($item['method_permohonan_uji_parameter_klinik'] ?? '')));
    if ($method !== '' && $method !== '-') {
      return $method;
    }

    $master = trim((string) ($item['metode_parameter_satuan_klinik'] ?? ''));
    if ($master === '' || $master === '-') {
      return '-';
    }

    $parts = array_values(array_filter(array_map('trim', explode(',', $master)), function ($p) {
      return $p !== '' && $p !== '-';
    }));

    return $parts[0] ?? $master;
  }
}



if (!function_exists('rubahNilaikeForm')) {
function rubahNilaikeForm($value, $numberFormat = null)
{
  //rubah pangkat dan subscript

  $value = decodeNilaiBakuMutuValue($value);
  if (isNilaiBakuMutuHtmlTable($value)) {
    return $value;
  }

  $value = str_replace("<br>","\n", $value);
  
  // PENTING: Convert placeholder lama (untuk backward compatibility dengan data lama)
  // Convert placeholder format lama ke format HTML dulu, baru ke format baru
  if (strpos($value, '[[PANGKAT_OPEN]]') !== false || strpos($value, '[[SUBSCRIPT_OPEN]]') !== false) {
      // Process character by character untuk handle placeholder lama
      $result = '';
      $tagStack = [];
      $i = 0;
      while ($i < strlen($value)) {
          if (substr($value, $i, 17) == '[[PANGKAT_OPEN]]') {
              $result .= '<sup>';
              array_push($tagStack, 'sup');
              $i += 17;
          } elseif (substr($value, $i, 19) == '[[SUBSCRIPT_OPEN]]') {
              $result .= '<sub>';
              array_push($tagStack, 'sub');
              $i += 19;
          } elseif (substr($value, $i, 9) == '[[CLOSE]]') {
              $tag = array_pop($tagStack);
              $result .= ($tag == 'sub') ? '</sub>' : '</sup>';
              $i += 9;
          } else {
              $result .= $value[$i];
              $i++;
          }
      }
      $value = $result;
  }
  
  // Replace HTML tags to ^() and _() format
  // IMPORTANT: Handle tags with attributes (e.g., <sup style="...">)
  // First, handle tags with attributes using regex
  $value = preg_replace('/<sup[^>]*>([^<]*)<\/sup>/i', '^($1)', $value);
  $value = preg_replace('/<sub[^>]*>([^<]*)<\/sub>/i', '_($1)', $value);
  // Then handle simple tags without attributes (for backward compatibility)
  $value = str_replace("<sup>", "^(", $value);
  $value = str_replace("</sup>", ")", $value);
  $value = str_replace("<sub>", "_(", $value);
  $value = str_replace("</sub>", ")", $value);
  
  // Replace HTML entities
  $value = str_replace("&#60;", "<", $value);
  $value = str_replace("&#62;", ">", $value);
  $value = str_replace("&#8804;","≤", $value);
  $value = str_replace("&#8805;", "≥", $value);
  $value = str_replace("&nbsp;", " ", $value);
  // Data lama kadang menyimpan operator "≥" menjadi "?" (encoding mismatch).
  // Normalisasi hanya untuk pola operator sebelum angka agar tidak mengubah teks biasa.
  $value = preg_replace('/(^|[\s,(;])\?\s*(?=\d)/u', '$1≥ ', $value);

  // Convert number format jika diperlukan (dari EN database format ke format yang diminta)
  if ($numberFormat === 'id' && !empty($value) && is_string($value)) {
      // Regex untuk detect angka dengan format EN (database format)
      // Pattern: angka dengan optional comma untuk ribuan dan titik untuk desimal
      // Contoh: 1234.56, 1,234.56, 4.0 - 6.5, <10, >=100
      $value = preg_replace_callback(
          '/(?<!\^)\b(\d{1,3}(?:,\d{3})*(?:\.\d+)?|\d+\.\d+|\d+)(?!\()\b/',
          function($matches) {
              $number = $matches[1];
              
              // Step 1: Remove ALL whitespace
              $cleanNumber = preg_replace('/\s+/', '', $number);
              // Step 2: Remove ALL comma thousands separator (EN format dari database)
              $cleanNumber = str_replace(',', '', $cleanNumber);
              // Step 3: Remove any remaining non-numeric except dot and minus
              $cleanNumber = preg_replace('/[^\d.-]/', '', $cleanNumber);
              
              // Skip jika bukan angka yang valid
              if (!is_numeric($cleanNumber)) {
                  return $matches[0];
              }
              
              // Detect jumlah desimal dari input
              $decimalPlaces = 0;
              if (strpos($cleanNumber, '.') !== false) {
                  $parts = explode('.', $cleanNumber);
                  $decimalPlaces = strlen($parts[1]);
              }
              
              // Convert to float
              $floatValue = (float) $cleanNumber;
              
              // Format dengan ID: decimal comma, thousands dot
              // Preserve jumlah desimal dari input asli
              return number_format($floatValue, $decimalPlaces, ',', '.');
          },
          $value
      );
  }

  return $value;
}
}

/**
 * Convert value for print format - converts ^(3) to Unicode superscript ³
 * This is used specifically for print/PDF output where we want Unicode superscripts
 * 
 * @param mixed $value - Value from database (can be HTML or ^() format)
 * @param string|null $numberFormat - Target number format: 'id' or 'en' (null = no conversion)
 * @return string - Converted value with Unicode superscripts
 */
if (!function_exists('rubahNilaikeFormForPrint')) {
function rubahNilaikeFormForPrint($value, $numberFormat = null)
{
  if (empty($value)) {
    return $value;
  }
  
  // Convert <br> to newline
  $value = str_replace("<br>", "\n", $value);
  
  // First, convert HTML sup/sub tags to ^() format if they exist
  // Handle tags with attributes
  $value = preg_replace('/<sup[^>]*>([^<]*)<\/sup>/i', '^($1)', $value);
  $value = preg_replace('/<sub[^>]*>([^<]*)<\/sub>/i', '_($1)', $value);
  // Handle simple tags without attributes
  $value = str_replace("<sup>", "^(", $value);
  $value = str_replace("</sup>", ")", $value);
  $value = str_replace("<sub>", "_(", $value);
  $value = str_replace("</sub>", ")", $value);
  
  // Now convert ^(digit) format to Unicode superscript
  // Map digits to Unicode superscript characters
  $superscriptMap = [
    '0' => '⁰',
    '1' => '¹',
    '2' => '²',
    '3' => '³',
    '4' => '⁴',
    '5' => '⁵',
    '6' => '⁶',
    '7' => '⁷',
    '8' => '⁸',
    '9' => '⁹',
  ];
  
  // Convert ^(single_digit) to Unicode superscript
  // Pattern: ^(3) or ^(12) - but for print we typically want single digit superscripts
  $value = preg_replace_callback('/\^\((\d+)\)/', function($matches) use ($superscriptMap) {
    $digits = $matches[1];
    $result = '';
    // Convert each digit to superscript
    for ($i = 0; $i < strlen($digits); $i++) {
      $digit = $digits[$i];
      if (isset($superscriptMap[$digit])) {
        $result .= $superscriptMap[$digit];
      } else {
        $result .= $digit; // Fallback if digit not in map
      }
    }
    return $result;
  }, $value);
  
  // Also handle format without parentheses: ^3 to ³
  $value = preg_replace_callback('/\^(\d+)/', function($matches) use ($superscriptMap) {
    $digits = $matches[1];
    $result = '';
    // Convert each digit to superscript
    for ($i = 0; $i < strlen($digits); $i++) {
      $digit = $digits[$i];
      if (isset($superscriptMap[$digit])) {
        $result .= $superscriptMap[$digit];
      } else {
        $result .= $digit; // Fallback if digit not in map
      }
    }
    return $result;
  }, $value);
  
  // Replace HTML entities
  $value = str_replace("&#60;", "<", $value);
  $value = str_replace("&#62;", ">", $value);
  $value = str_replace("&#8804;", "≤", $value);
  $value = str_replace("&#8805;", "≥", $value);
  $value = str_replace("&nbsp;", " ", $value);
  $value = str_replace("&le;", "≤", $value);
  $value = str_replace("&ge;", "≥", $value);
  $value = str_replace("&plusmn;", "±", $value);
  
  // Convert number format jika diperlukan (dari EN database format ke format yang diminta)
  if ($numberFormat === 'id' && !empty($value) && is_string($value)) {
    // Regex untuk detect angka dengan format EN (database format)
    // Pattern: angka dengan optional comma untuk ribuan dan titik untuk desimal
    // Contoh: 1234.56, 1,234.56, 4.0 - 6.5, <10, >=100
    $value = preg_replace_callback(
      '/(?<!\^)\b(\d{1,3}(?:,\d{3})*(?:\.\d+)?|\d+\.\d+|\d+)(?!\()\b/',
      function($matches) {
        $number = $matches[1];
        
        // Step 1: Remove ALL whitespace
        $cleanNumber = preg_replace('/\s+/', '', $number);
        // Step 2: Remove ALL comma thousands separator (EN format dari database)
        $cleanNumber = str_replace(',', '', $cleanNumber);
        // Step 3: Remove any remaining non-numeric except dot and minus
        $cleanNumber = preg_replace('/[^\d.-]/', '', $cleanNumber);
        
        // Skip jika bukan angka yang valid
        if (!is_numeric($cleanNumber)) {
          return $matches[0];
        }
        
        // Detect jumlah desimal dari input
        $decimalPlaces = 0;
        if (strpos($cleanNumber, '.') !== false) {
          $parts = explode('.', $cleanNumber);
          $decimalPlaces = strlen($parts[1]);
        }
        
        // Convert to float
        $floatValue = (float) $cleanNumber;
        
        // Format dengan ID: decimal comma, thousands dot
        // Preserve jumlah desimal dari input asli
        return number_format($floatValue, $decimalPlaces, ',', '.');
      },
      $value
    );
  }
  
  return $value;
}
}

/**
 * Extended version of rubahNilaikeForm with number format support
 * Converts value from database to form input format with number format conversion
 * 
 * @param mixed $value - Value from database
 * @param string|null $numberFormat - Target number format: 'id' or 'en' (null = no conversion)
 * @return string - Converted value
 */
if (!function_exists('rubahNilaikeFormWithNumber')) {
function rubahNilaikeFormWithNumber($value, $numberFormat = null)
{
  // First, do standard HTML entity and tag conversion
  $value = rubahNilaikeForm($value);
  
  // Then convert number format if requested
  if ($numberFormat === 'id' && !empty($value) && is_string($value)) {
      // Regex untuk detect angka dengan format EN (database format)
      // Pattern: angka dengan optional comma untuk ribuan dan titik untuk desimal
      // Contoh: 1234.56, 1,234.56, 4.0 - 6.5, <10, >=100
      $value = preg_replace_callback(
          '/(?<!\^)\b(\d{1,3}(?:,\d{3})*(?:\.\d+)?|\d+\.\d+|\d+)(?!\()\b/',
          function($matches) {
              $number = $matches[1];
              
              // Step 1: Remove ALL whitespace
              $cleanNumber = preg_replace('/\s+/', '', $number);
              // Step 2: Remove ALL comma thousands separator (EN format dari database)
              $cleanNumber = str_replace(',', '', $cleanNumber);
              // Step 3: Remove any remaining non-numeric except dot and minus
              $cleanNumber = preg_replace('/[^\d.-]/', '', $cleanNumber);
              
              // Skip jika bukan angka yang valid
              if (!is_numeric($cleanNumber)) {
                  return $matches[0];
              }
              
              // Detect jumlah desimal dari input
              $decimalPlaces = 0;
              if (strpos($cleanNumber, '.') !== false) {
                  $parts = explode('.', $cleanNumber);
                  $decimalPlaces = strlen($parts[1]);
              }
              
              // Convert to float
              $floatValue = (float) $cleanNumber;
              
              // Format dengan ID: decimal comma, thousands dot
              // Preserve jumlah desimal dari input asli
              return number_format($floatValue, $decimalPlaces, ',', '.');
          },
          $value
      );
  }

  return $value;
}
}

function getRomawi($bln){
  switch ($bln){
      case 1:
          return "I";
          break;
      case 2:
          return "II";
          break;
      case 3:
          return "III";
          break;
      case 4:
          return "IV";
          break;
      case 5:
          return "V";
          break;
      case 6:
          return "VI";
          break;
      case 7:
          return "VII";
          break;
      case 8:
          return "VIII";
          break;
      case 9:
          return "IX";
          break;
      case 10:
          return "X";
          break;
      case 11:
          return "XI";
          break;
      case 12:
          return "XII";
          break;
  }
}

function rubahNilaikeHtml($value, $inputNumberFormat = null)
{
  //rubah pangkat dan subscript - DIRECT CONVERSION (NO PLACEHOLDER)

  if (is_string($value) && strpos($value, '[[BMHTML]]') !== false) {
    return decodeNilaiBakuMutuValue($value);
  }
  
  // Convert number format if input is in ID format (BEFORE HTML processing)
  if ($inputNumberFormat === 'id' && !empty($value) && is_string($value)) {
      // Regex untuk detect angka dengan format ID (ribuan: titik, desimal: koma)
      // Contoh: 1.234,56 atau 1234,56 atau 4,0 - 6,5 atau dengan whitespace
      $value = preg_replace_callback(
          '/(?<!\^)\b(\d{1,3}(?:\.\d{3})*(?:,\d+)?|\d+,\d+|\d+)(?!\()\b/',
          function($matches) {
              $number = $matches[1];
              
              // Convert ID format to EN format (database format)
              // Step 1: Remove ALL whitespace
              $cleanNumber = preg_replace('/\s+/', '', $number);
              // Step 2: Remove ALL dot (thousands separator in ID)
              $cleanNumber = str_replace('.', '', $cleanNumber);
              // Step 3: Replace comma with dot (decimal separator)
              $cleanNumber = str_replace(',', '.', $cleanNumber);
              // Step 4: Remove any remaining non-numeric except dot and minus
              $cleanNumber = preg_replace('/[^\d.-]/', '', $cleanNumber);
              
              // Skip jika bukan angka yang valid
              if (!is_numeric($cleanNumber)) {
                  return $matches[0];
              }
              
              // Return in EN format (standard database format)
              // No thousands separator, dot for decimal
              return $cleanNumber;
          },
          $value
      );
  }
  
  // Check if value contains HTML tags (from TinyMCE) or HTML entities
  // More specific HTML tag detection - must start with letter or slash
  $hasHtmlTags = preg_match('/<\/?[a-zA-Z][^>]*>/', $value);
  $hasHtmlEntities = preg_match('/&lt;\/?[a-zA-Z][^&]*&gt;/', $value);
  
  // If this is HTML content from TinyMCE, clean up and return
  if ($hasHtmlTags) {
    // Remove all <p> and </p> tags but keep the content inside
    $value = preg_replace('/<p>/', '', $value);
    $value = preg_replace('/<\/p>/', '', $value);
    // Also remove empty paragraph tags (just in case)
    $value = preg_replace('/<p><\/p>/', '', $value);
    $value = preg_replace('/<p>\s*<\/p>/', '', $value);
    $value = trim($value);
    $value = preg_replace('/&nbsp;\s*$/', '', $value);
    return encodeNilaiBakuMutuStorage($value);
  }
  
  // If this is escaped HTML content (HTML entities), decode and clean up
  if ($hasHtmlEntities) {
    $decoded = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    // Remove all <p> and </p> tags but keep the content inside
    $decoded = preg_replace('/<p>/', '', $decoded);
    $decoded = preg_replace('/<\/p>/', '', $decoded);
    // Also remove empty paragraph tags (just in case)
    $decoded = preg_replace('/<p><\/p>/', '', $decoded);
    $decoded = preg_replace('/<p>\s*<\/p>/', '', $decoded);
    $decoded = trim($decoded);
    $decoded = preg_replace('/&nbsp;\s*$/', '', $decoded);
    return encodeNilaiBakuMutuStorage($decoded);
  }
  
  // Auto-close kurung yang tidak tertutup untuk pangkat
  $openSupCount = substr_count($value, '^(');
  $openSubCount = substr_count($value, '_(');
  $closeCount = substr_count($value, ')');
  
  // Jika ada ^( atau _( yang tidak tertutup, tambahkan ) di akhir
  $totalOpen = $openSupCount + $openSubCount;
  if ($totalOpen > $closeCount) {
      $value .= str_repeat(')', ($totalOpen - $closeCount));
  }
  
  // Step 1: Replace comparison operators FIRST (before < and >)
  $value = str_replace("<=", "&#8804;", $value);
  $value = str_replace(">=", "&#8805;", $value);
  $value = str_replace("≤", "&#8804;", $value);
  $value = str_replace("≥", "&#8805;", $value);
  
  // Step 2: Replace remaining < and > symbols (after operators replaced)
  $value = str_replace("<", "&#60;", $value);
  $value = str_replace(">", "&#62;", $value);
  
  // Step 2.5: Convert Unicode superscript characters to <sup> tags BEFORE processing ^() format
  // This handles characters like ³, ², ¹, etc. that might be in the data
  $value = str_replace('¹', '<sup>1</sup>', $value);
  $value = str_replace('²', '<sup>2</sup>', $value);
  $value = str_replace('³', '<sup>3</sup>', $value);
  $value = str_replace('⁴', '<sup>4</sup>', $value);
  $value = str_replace('⁵', '<sup>5</sup>', $value);
  $value = str_replace('⁶', '<sup>6</sup>', $value);
  $value = str_replace('⁷', '<sup>7</sup>', $value);
  $value = str_replace('⁸', '<sup>8</sup>', $value);
  $value = str_replace('⁹', '<sup>9</sup>', $value);
  $value = str_replace('⁰', '<sup>0</sup>', $value);
  
  // Step 3: Convert ^( and _( to HTML tags DIRECTLY (character by character)
  // Process with stack to handle nested/sequential sup/sub properly
  $result = '';
  $tagStack = [];
  $i = 0;
  $len = strlen($value);
  
  while ($i < $len) {
      // Check for superscript opening ^(
      if ($i < $len - 1 && $value[$i] == '^' && $value[$i + 1] == '(') {
          $result .= '<sup>';
          array_push($tagStack, 'sup');
          $i += 2; // Skip ^(
      }
      // Check for subscript opening _(
      elseif ($i < $len - 1 && $value[$i] == '_' && $value[$i + 1] == '(') {
          $result .= '<sub>';
          array_push($tagStack, 'sub');
          $i += 2; // Skip _(
      }
      // Check for closing )
      elseif ($value[$i] == ')' && count($tagStack) > 0) {
          $tag = array_pop($tagStack);
          $result .= ($tag == 'sub') ? '</sub>' : '</sup>';
          $i++;
      }
      // Regular character
      else {
          $result .= $value[$i];
          $i++;
      }
  }
  $value = $result;
  
  // Step 4: Handle line breaks and spaces for non-HTML content
  $value = str_replace("\n", '<br>', $value);
  $value = str_replace(" ", "&nbsp;", $value);
  
  return $value;
}

/**
 * Extended version of rubahNilaikeHtml with number format support
 * Converts value from form input to database HTML format with number format conversion
 * 
 * @param mixed $value - Value from form input
 * @param string|null $inputNumberFormat - Input number format from form: 'id' or 'en' (null = no conversion)
 * @return string - Converted value in HTML format
 */
function rubahNilaikeHtmlWithNumber($value, $inputNumberFormat = null)
{
  // First, convert number format if input is in ID format
  if ($inputNumberFormat === 'id' && !empty($value) && is_string($value)) {
      // Regex untuk detect angka dengan format ID (ribuan: titik, desimal: koma)
      // Contoh: 1.234,56 atau 1234,56 atau 4,0 - 6,5 atau dengan whitespace
      $value = preg_replace_callback(
          '/(?<!\^)\b(\d{1,3}(?:\.\d{3})*(?:,\d+)?|\d+,\d+|\d+)(?!\()\b/',
          function($matches) {
              $number = $matches[1];
              
              // Convert ID format to EN format (database format)
              // Step 1: Remove ALL whitespace
              $cleanNumber = preg_replace('/\s+/', '', $number);
              // Step 2: Remove ALL dot (thousands separator in ID)
              $cleanNumber = str_replace('.', '', $cleanNumber);
              // Step 3: Replace comma with dot (decimal separator)
              $cleanNumber = str_replace(',', '.', $cleanNumber);
              // Step 4: Remove any remaining non-numeric except dot and minus
              $cleanNumber = preg_replace('/[^\d.-]/', '', $cleanNumber);
              
              // Skip jika bukan angka yang valid
              if (!is_numeric($cleanNumber)) {
                  return $matches[0];
              }
              
              // Return in EN format (standard database format)
              // No thousands separator, dot for decimal
              return $cleanNumber;
          },
          $value
      );
  }
  
  // Then do standard HTML conversion
  return rubahNilaikeHtml($value);
}

/**
 * Nomor urut tampilan parameter di hasil cetak Kesmas (1, 2, 3, ...).
 * Urutan baris mengikuti orderlist_sample_type_detail di controller/view;
 * angka yang ditampilkan selalu berurutan, bukan nilai orderlist mentah.
 */
function kesmas_parameter_urut_number($method, &$fallbackCounter)
{
  $fallbackCounter++;

  return $fallbackCounter;
}

function kesmas_lhu_include_parameter($method)
{
  $hasil = isset($method->hasil) ? trim((string) $method->hasil) : '';

  return $hasil !== '' && $hasil !== '-';
}

function kesmas_lhu_normalize_jenis($method)
{
  $jenis = trim(strtolower((string) ($method->jenis_parameter_kimia ?? '')));

  if (in_array($jenis, ['fisika', 'kimiawi', 'kimia organik'], true)) {
    return $jenis;
  }

  // Parameter tambahan / master method tanpa jenis → tampilkan di KIMIA
  return 'kimiawi';
}

function kesmas_lhu_is_jenis($method, $jenis)
{
  return kesmas_lhu_normalize_jenis($method) === trim(strtolower((string) $jenis));
}

function kesmas_lhu_resolve_method_id($method)
{
  if (is_array($method)) {
    return $method['id_method'] ?? $method['method_id'] ?? null;
  }

  return $method->id_method ?? $method->method_id ?? null;
}

function kesmas_build_wajib_order_map($sampleTypeDetails)
{
  $map = [];

  foreach ($sampleTypeDetails as $detail) {
    $methodId = $detail->method_id ?? null;
    if (!$methodId) {
      continue;
    }

    $order = $detail->orderlist_sample_type_detail ?? null;
    // Tidak ada angka urutan di jenis sarana → biarkan fallback ke urutan method
    if ($order === null || $order === '') {
      continue;
    }

    // Ambil urutan pertama jika method muncul lebih dari sekali (wajib/tambahan)
    if (!isset($map[$methodId])) {
      $map[$methodId] = (int) $order;
    }
  }

  return $map;
}

function kesmas_resolve_method_orderlist($method, array $methodOrderLookup = [])
{
  if (is_array($method)) {
    $order = $method['orderlist_method'] ?? null;
  } else {
    $order = $method->orderlist_method ?? null;
  }

  if ($order === null || $order === '') {
    $methodId = kesmas_lhu_resolve_method_id($method);
    if ($methodId && array_key_exists($methodId, $methodOrderLookup) && $methodOrderLookup[$methodId] !== null && $methodOrderLookup[$methodId] !== '') {
      return (int) $methodOrderLookup[$methodId];
    }

    return null;
  }

  return (int) $order;
}

/**
 * Tuple urutan stabil:
 * 1) urutan detail jenis sarana (jika ada)
 * 2) fallback urutan master method (ms_method.orderlist_method)
 * 3) nama parameter
 */
function kesmas_lhu_sort_priority($method, array $sampleTypeOrderMap, array $methodOrderLookup = [])
{
  $methodId = kesmas_lhu_resolve_method_id($method);
  $name = strtolower(trim((string) (
    is_array($method)
      ? ($method['name_report'] ?? $method['params_method'] ?? '')
      : ($method->name_report ?? $method->params_method ?? '')
  )));

  // 1) Urutan dari detail jenis sarana
  if ($methodId && isset($sampleTypeOrderMap[$methodId])) {
    return [0, $sampleTypeOrderMap[$methodId], $name];
  }

  // 2) Fallback: urutan master method (ms_method.orderlist_method)
  $methodOrder = kesmas_resolve_method_orderlist($method, $methodOrderLookup);
  if ($methodOrder !== null) {
    return [1, $methodOrder, $name];
  }

  // 3) Paling bawah
  return [2, 99999, $name];
}

function kesmas_sort_laboratorium_methods($methods, $sampleTypeDetails)
{
  $sampleTypeOrderMap = kesmas_build_wajib_order_map($sampleTypeDetails);
  $collection = collect($methods)->values();

  // Ambil orderlist_method dari DB jika hilang karena join/select menimpa kolom
  $methodIds = $collection
    ->map(function ($method) {
      return kesmas_lhu_resolve_method_id($method);
    })
    ->filter()
    ->unique()
    ->values()
    ->all();

  $methodOrderLookup = [];
  if (count($methodIds) > 0) {
    $methodOrderLookup = \DB::table('ms_method')
      ->whereIn('id_method', $methodIds)
      ->whereNull('deleted_at')
      ->pluck('orderlist_method', 'id_method')
      ->toArray();
  }

  // Tempelkan orderlist_method ke objek agar print/view juga konsisten
  $collection = $collection->map(function ($method) use ($methodOrderLookup) {
    $methodId = kesmas_lhu_resolve_method_id($method);
    $resolved = kesmas_resolve_method_orderlist($method, $methodOrderLookup);
    if ($resolved !== null) {
      if (is_array($method)) {
        $method['orderlist_method'] = $resolved;
      } else {
        $method->orderlist_method = $resolved;
      }
    } elseif ($methodId && array_key_exists($methodId, $methodOrderLookup)) {
      $val = $methodOrderLookup[$methodId];
      if (is_array($method)) {
        $method['orderlist_method'] = $val;
      } else {
        $method->orderlist_method = $val;
      }
    }

    return $method;
  });

  return $collection
    ->sort(function ($a, $b) use ($sampleTypeOrderMap, $methodOrderLookup) {
      $ka = kesmas_lhu_sort_priority($a, $sampleTypeOrderMap, $methodOrderLookup);
      $kb = kesmas_lhu_sort_priority($b, $sampleTypeOrderMap, $methodOrderLookup);

      return $ka <=> $kb;
    })
    ->values();
}

function kesmas_lhu_sort_key($method)
{
  $orderlist = is_array($method)
    ? ($method['orderlist_sample_type_detail'] ?? null)
    : ($method->orderlist_sample_type_detail ?? null);
  $methodOrder = kesmas_resolve_method_orderlist($method);
  $name = strtolower(trim((string) (
    is_array($method)
      ? ($method['name_report'] ?? $method['params_method'] ?? '')
      : ($method->name_report ?? $method->params_method ?? '')
  )));

  // Sama seperti baca-hasil: jenis sarana dulu, lalu method
  if ($orderlist !== null && $orderlist !== '') {
    return [0, (int) $orderlist, $name];
  }

  if ($methodOrder !== null) {
    return [1, $methodOrder, $name];
  }

  return [2, 99999, $name];
}

function kesmas_lhu_resolve_hasil_for_print($method, $sampleResult = null)
{
  if ($sampleResult && isset($sampleResult->hasil)) {
    $hasil = trim((string) $sampleResult->hasil);
    if ($hasil !== '' && $hasil !== '-') {
      return $sampleResult->hasil;
    }
  }

  if (isset($method->hasil)) {
    $hasil = trim((string) $method->hasil);
    if ($hasil !== '' && $hasil !== '-') {
      return $method->hasil;
    }
  }

  return '-';
}

function kesmas_resolve_metode_for_print($method, $sampleResult = null)
{
  if ($sampleResult && isset($sampleResult->metode) && trim((string) $sampleResult->metode) !== '') {
    return $sampleResult->metode;
  }

  if (isset($method->metode) && trim((string) $method->metode) !== '') {
    return $method->metode;
  }

  return $method->name_method ?? null;
}

/**
 * Normalisasi teks kualitatif (Positif/Negatif, dll.) untuk perbandingan cetak LHU.
 */
function kesmas_normalize_qualitative_text($value): string
{
  if ($value === null) {
    return '';
  }

  $text = strip_tags((string) $value);
  $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

  return preg_replace('/\s+/', '', trim($text));
}

/**
 * Parse angka hasil/batas untuk cetak LHU (angka biasa, HTML, atau a×10^n).
 * Mendukung superscript Unicode (¹⁰⁵), HTML <sup>, dan notasi 10^5 / 10^{5}.
 */
function kesmas_parse_print_numeric($raw)
{
  if ($raw === null || $raw === '' || $raw === '-') {
    return null;
  }
  if (is_numeric($raw)) {
    return (float) $raw;
  }

  $s = (string) $raw;
  $s = preg_replace('/10\s*<sup>\s*([+\-]?\d+)\s*<\/sup>/iu', '10^$1', $s);
  $s = strip_tags($s);
  $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');

  // Superscript Unicode → ASCII (contoh: 10⁵ → 10^5)
  $superMap = [
    '⁰' => '0', '¹' => '1', '²' => '2', '³' => '3', '⁴' => '4',
    '⁵' => '5', '⁶' => '6', '⁷' => '7', '⁸' => '8', '⁹' => '9',
    '⁺' => '+', '⁻' => '-',
  ];
  // Ubah "10⁵" / "10⁻³" menjadi "10^5" / "10^-3"
  $s = preg_replace_callback('/10\s*([⁰¹²³⁴⁵⁶⁷⁸⁹⁺⁻]+)/u', function ($m) use ($superMap) {
    $digits = strtr($m[1], $superMap);

    return '10^' . $digits;
  }, $s);

  $s = str_replace(['×', '⋅', '·', "\xc3\x97"], 'x', $s);
  $s = preg_replace('/\s+/u', ' ', trim($s));

  if (preg_match('/([\d.]+)\s*[xX*]\s*10\s*\^?\s*\{?([+\-]?\d+)\}?/u', $s, $m)) {
    return (float) $m[1] * pow(10, (int) $m[2]);
  }
  if (preg_match('/([\d.]+)\s*\\\\?times\s*10\s*\^?\s*\{?([+\-]?\d+)\}?/iu', $s, $m)) {
    return (float) $m[1] * pow(10, (int) $m[2]);
  }
  if (preg_match('/^\s*([\d.]+)\s*$/', $s, $m)) {
    return (float) $m[1];
  }

  $parsed = parseNumberInput($s, 'en');

  return $parsed !== null ? (float) $parsed : null;
}

/**
 * Cek apakah hasil pemeriksaan melewati baku mutu pada template cetak Kesmas (makanan/minuman).
 */
function kesmas_hasil_melewati_baku_mutu_print($hasilVal, $minVal = null, $maxVal = null, $equalVal = null, $nilaiBakuMutu = null): bool
{
  $hasilPlain = kesmas_normalize_qualitative_text($hasilVal);
  if ($hasilPlain === '' || $hasilPlain === '-') {
    return false;
  }

  $hasilNumeric = kesmas_parse_print_numeric($hasilVal);
  $numMin = kesmas_parse_print_numeric($minVal);
  $numMax = kesmas_parse_print_numeric($maxVal);
  // Batas atas sering hanya di teks tampilan (nilai_baku_mutu / equal) bentuk A x 10^C
  if ($numMax === null && $nilaiBakuMutu !== null && $nilaiBakuMutu !== '') {
    $numMax = kesmas_parse_print_numeric($nilaiBakuMutu);
  }
  if ($numMax === null && $equalVal !== null && trim((string) $equalVal) !== '') {
    $numMaxFromEqual = kesmas_parse_print_numeric($equalVal);
    if ($numMaxFromEqual !== null) {
      $numMax = $numMaxFromEqual;
    }
  }

  if ($hasilNumeric !== null && ($numMin !== null || $numMax !== null)) {
    if ($numMin !== null && $hasilNumeric < $numMin) {
      return true;
    }
    if ($numMax !== null && $hasilNumeric > $numMax) {
      return true;
    }

    return false;
  }

  $reference = null;
  if ($equalVal !== null && trim((string) $equalVal) !== '' && kesmas_parse_print_numeric($equalVal) === null) {
    $reference = $equalVal;
  } elseif ($nilaiBakuMutu !== null && trim((string) $nilaiBakuMutu) !== '' && kesmas_parse_print_numeric($nilaiBakuMutu) === null) {
    $reference = $nilaiBakuMutu;
  }

  if ($reference !== null) {
    $refPlain = kesmas_normalize_qualitative_text($reference);
    if ($refPlain === '') {
      return false;
    }

    return strcasecmp($hasilPlain, $refPlain) !== 0;
  }

  return false;
}

/**
 * Format tampilan hasil cetak: tebal + bintang hanya jika melewati baku mutu.
 */
function kesmas_format_hasil_print_with_baku_mutu_marker($hasilVal, bool $isOver): string
{
  $hasilVal = (string) $hasilVal;
  if ($isOver) {
    return "<span style='font-weight:bold'>" . $hasilVal . '*</span>';
  }

  return $hasilVal;
}

function cek_hasil_color($hasil, $min, $max, $equal, $id, $offset_baku_mutu = "default", $numberFormat = 'en', $pengesahanMerahLewatBakuMutu = false, $nilai_baku_mutu = null)
{
  // Normalize offset_baku_mutu
  $offset_baku_mutu = trim(strtolower($offset_baku_mutu));

  $htmlMelewatiBakuMutu = function () use ($id, $hasil, $pengesahanMerahLewatBakuMutu) {
    if ($pengesahanMerahLewatBakuMutu) {
      return "<span id='" . $id . "' class='badge badge-danger' style='font-size: 14px; padding: 8px 12px;'><b>" . $hasil . "</b><span class='bintang-baku-mutu'> *</span></span>";
    }

    return "<span id='" . $id . "' class='d-inline-block font-weight-bold border border-dark rounded px-2 py-1' style='font-size: 14px; color: #212529; background: #fff;'><b>" . $hasil . "</b><span class='bintang-baku-mutu'> *</span></span>";
  };

  $delete_space = str_replace(" ", "", $hasil);

  if (isset($delete_space) && $delete_space != "" && $delete_space != "-") {
    // Manual override: false = tidak melewati, true = melewati
    if ($offset_baku_mutu == "false") {
      // Tidak melewati baku mutu (manual override) - badge success
      $hasil_last = "<span id='" . $id . "' class='badge badge-success' style='font-size: 14px; padding: 8px 12px;'><i class='fa fa-check-circle mr-1'></i>" . $hasil . "</span>";
    } elseif ($offset_baku_mutu == "true") {
      // Melewati baku mutu (manual override); di halaman pengesahan: badge merah
      $hasil_last = $htmlMelewatiBakuMutu();
    } else {
      // Automatic calculation — pakai parser yang mendukung A x 10^C (termasuk superscript Unicode)
      $melewati_baku_mutu = kesmas_hasil_melewati_baku_mutu_print($hasil, $min, $max, $equal, $nilai_baku_mutu);

      if ($melewati_baku_mutu) {
        $hasil_last = $htmlMelewatiBakuMutu();
      } else {
        // Tidak melewati baku mutu - badge success
        $hasil_last = "<span id='" . $id . "' class='badge badge-success' style='font-size: 14px; padding: 8px 12px;'><i class='fa fa-check-circle mr-1'></i>" . $hasil . "</span>";
      }
    }
  } else {
    $hasil_last = "<span id='" . $id . "' class='badge badge-secondary' style='font-size: 14px; padding: 8px 12px;'>-</span>";
  }
  return $hasil_last;
}

function cek_hasil_color_mikro($hasil, $min, $max, $equal, $id, $offset_baku_mutu = "default", $numberFormat = 'en', $nilai_baku_mutu = null)
{
  // Normalize offset_baku_mutu
  $offset_baku_mutu = trim(strtolower((string) $offset_baku_mutu));

  $delete_space = str_replace(" ", "", $hasil);

  if (isset($delete_space) && $delete_space != "" && $delete_space != "-") {
    if ($offset_baku_mutu == "false") {
      // Tidak melewati baku mutu (manual override) - badge success
      $hasil_last = "<span id='" . $id . "' class='badge badge-success' style='font-size: 14px; padding: 8px 12px;'><i class='fa fa-check-circle mr-1'></i>" . $hasil . "</span>";
    } elseif ($offset_baku_mutu == "true") {
      // Melewati baku mutu (manual override) — tebal, tanpa merah
      $hasil_last = "<span id='" . $id . "' class='d-inline-block font-weight-bold border border-dark rounded px-2 py-1' style='font-size: 14px; color: #212529; background: #fff;'><b>" . $hasil . "</b><span class='bintang-baku-mutu'> *</span></span>";
    } else {
      // Automatic — parser A x 10^C (Unicode superscript / 10^n)
      $melewati_baku_mutu = kesmas_hasil_melewati_baku_mutu_print($hasil, $min, $max, $equal, $nilai_baku_mutu);

      if ($melewati_baku_mutu) {
        $hasil_last = "<span id='" . $id . "' class='d-inline-block font-weight-bold border border-dark rounded px-2 py-1' style='font-size: 14px; color: #212529; background: #fff;'><b>" . $hasil . "</b><span class='bintang-baku-mutu'> *</span></span>";
      } else {
        $hasil_last = "<span id='" . $id . "' class='badge badge-success' style='font-size: 14px; padding: 8px 12px;'><i class='fa fa-check-circle mr-1'></i>" . $hasil . "</span>";
      }
    }
  } else {
    $hasil_last = "<span id='" . $id . "' class='badge badge-secondary' style='font-size: 14px; padding: 8px 12px;'>-</span>";
  }
  return $hasil_last;
}

function penyebut($nilai)
{
  $nilai = abs($nilai);
  $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
  $temp = "";
  if ($nilai < 12) {
    $temp = " " . $huruf[$nilai];
  } else if ($nilai < 20) {
    $temp = penyebut($nilai - 10) . " belas";
  } else if ($nilai < 100) {
    $temp = penyebut($nilai / 10) . " puluh" . penyebut($nilai % 10);
  } else if ($nilai < 200) {
    $temp = " seratus" . penyebut($nilai - 100);
  } else if ($nilai < 1000) {
    $temp = penyebut($nilai / 100) . " ratus" . penyebut($nilai % 100);
  } else if ($nilai < 2000) {
    $temp = " seribu" . penyebut($nilai - 1000);
  } else if ($nilai < 1000000) {
    $temp = penyebut($nilai / 1000) . " ribu" . penyebut($nilai % 1000);
  } else if ($nilai < 1000000000) {
    $temp = penyebut($nilai / 1000000) . " juta" . penyebut($nilai % 1000000);
  } else if ($nilai < 1000000000000) {
    $temp = penyebut($nilai / 1000000000) . " milyar" . penyebut(fmod($nilai, 1000000000));
  } else if ($nilai < 1000000000000000) {
    $temp = penyebut($nilai / 1000000000000) . " trilyun" . penyebut(fmod($nilai, 1000000000000));
  }
  return $temp;
}

function terbilang($nilai)
{
  if ($nilai < 0) {
    $hasil = "minus " . trim(penyebut($nilai));
  } else {
    $hasil = trim(penyebut($nilai));
  }
  return ucwords($hasil . " rupiah");
}
//end generator helper

function create_link($url)
{
  $url = strip_tags($url);
  $url = str_replace(" ", "-", $url);
  $url = str_replace("!", "", $url);
  $url = str_replace("@", "", $url);
  $url = str_replace("#", "", $url);
  $url = str_replace("$", "", $url);
  $url = str_replace("%", "", $url);
  $url = str_replace("^", "", $url);
  $url = str_replace("&", "", $url);
  $url = str_replace("*", "", $url);
  $url = str_replace("(", "", $url);
  $url = str_replace(")", "", $url);
  $url = str_replace("_", "", $url);
  $url = str_replace("+", "", $url);
  $url = str_replace("=", "", $url);
  $url = str_replace("{", "", $url);
  $url = str_replace("}", "", $url);
  $url = str_replace("[", "", $url);
  $url = str_replace("]", "", $url);
  $url = str_replace("|", "", $url);
  $url = str_replace('"', "", $url);
  $url = str_replace(";", "", $url);
  $url = str_replace(">", "", $url);
  $url = str_replace('<', "", $url);
  $url = str_replace("?", "", $url);
  $url = str_replace("/", "", $url);
  $url = str_replace('~', "", $url);
  $url = str_replace("`", "", $url);
  $url = str_replace(".", "", $url);
  $url = str_replace(",", "", $url);
  $url = str_replace(":", "", $url);
  $url = str_replace("'", "", $url);
  $url = addslashes($url);
  $url = strtolower($url);

  return $url;
}

//function fbulan
function fbulan($bulan)
{
  if ($bulan == "01") {
    $bln = "Januari";
  } else if ($bulan == "02") {
    $bln = "Februari";
  } else if ($bulan == "03") {
    $bln = "Maret";
  } else if ($bulan == "04") {
    $bln = "April";
  } else if ($bulan == "05") {
    $bln = "Mei";
  } else if ($bulan == "06") {
    $bln = "Juni";
  } else if ($bulan == "07") {
    $bln = "Juli";
  } else if ($bulan == "08") {
    $bln = "Agustus";
  } else if ($bulan == "09") {
    $bln = "September";
  } else if ($bulan == "10") {
    $bln = "Oktober";
  } else if ($bulan == "11") {
    $bln = "November";
  } else if ($bulan == "12") {
    $bln = "Desember";
  } else {
    $bln = "";
  }
  return $bln;
}

//function fdate
function fdate($value, $format)
{
  if ($value != "") {
    list($thn, $bln, $tgl) = explode("-", $value);

    switch ($format) {
      case "DDMMYYYY":
        $return = $tgl . " " . Smt::fbulan($bln) . " " . $thn;
        break;
        //new case
      case "DDMM":
        $return = $tgl . " " . Smt::fbulan($bln);
        break;
      case "DD":
        $return = $tgl;
        break;
      case "MM":
        $return = $bln;
        break;
      case "YYYYY":
        $return = $thn;
        break;
      case "mm":
        $return = Smt::fbulan($bln);
        break;
      case "HHDDMMYYYY":
        $jam = explode(" ", $value)[1];
        $tgl = explode(" ", $tgl)[0];
        list($H, $M, $S) = explode(":", $jam);
        $return = $tgl . " " . Smt::fbulan($bln) . " " . $thn . " | " . $H . ":" . $M;
        break;
    }
  } else {
    $return = "";
  }
  return $return;
}

function get_num_phone($nohp)
{
  // kadang ada penulisan no hp 0811 239 345
  $nohp = str_replace(" ", "", $nohp);
  // kadang ada penulisan no hp (0274) 778787
  $nohp = str_replace("(", "", $nohp);
  // kadang ada penulisan no hp (0274) 778787
  $nohp = str_replace(")", "", $nohp);
  // kadang ada penulisan no hp 0811.239.345
  $nohp = str_replace(".", "", $nohp);

  // cek apakah no hp mengandung karakter + dan 0-9
  if (!preg_match('/[^+0-9]/', trim($nohp))) {
    // cek apakah no hp karakter 1-3 adalah +62
    if (substr(trim($nohp), 0, 3) == '+62') {
      $hp = trim($nohp);
    }
    // cek apakah no hp karakter 1 adalah 0
    elseif (substr(trim($nohp), 0, 1) == '0') {
      $hp = '+62' . substr(trim($nohp), 1);
    }
  }
  return $hp;
}

function get_img($news_content = NULL)
{


  $dom = new \DOMDocument();





  if ($news_content == "") {


    return NULL;
  }





  libxml_use_internal_errors(true);


  $dom->loadHTML($news_content);


  libxml_use_internal_errors(false);


  $img_nodes = $dom->getElementsByTagName('img');





  $img_link = NULL;


  foreach ($img_nodes as $link) {


    $img_link = $link->getAttribute('src');
    break;
  }





  return $img_link;
}

function img_empty($asset, $value)
{
  if (empty($value)) {
    $img = asset('assets/public/images/intro/blank.png');
  } else {
    $img = asset($asset . $value);
  }
  return $img;
}
//get title
function name_url($url_segment)
{
  $get_name_control = DB::table('ms_menus')->where('link', '=', $url_segment)->first();
  $url = $get_name_control->name;
  return $url;
}

//get link controller
function name_link($url_segment)
{
  if ($url_segment == NULL) {
    $url_segment = '/';
  }
  $get_name_control = DB::table('ms_menus')->where('link', '=', $url_segment)->first();
  $url = $get_name_control->link;
  return $url;
}

function name_controller($value = null)
{
  $get_name = DB::table('ms_menus')->where('link', '=', Request::segment(1))->where('deleted_at', NULL)->first();
  $name = $get_name->type;
  return $name;
}

function get_type($value = null)
{
  if (Request::segment(1) == NULL) {
    return '1';
  }
  $get_name = DB::table('ms_menus')->where('link', '=', Request::segment(1))->where('deleted_at', NULL)->first();
  $name = $get_name->type;
  return $name;
}

function get_menuid($type = null)
{
  if ($type == NULL) {
    $get_name = DB::table('ms_menus')->where('link', '=', Request::segment(1))->where('deleted_at', NULL)->first();
  } else {
    $get_name = DB::table('ms_menus')->where('type', '=', $type)->where('deleted_at', NULL)->first();
  }
  $name = $get_name->id;
  return $name;
}

function getPaketKlinik($namePaket)
{
   $paketKlinik = \Illuminate\Support\Facades\DB::table('ms_parameter_paket_klinik')->where('name_parameter_paket_klinik', 'like', '%'.$namePaket.'%')->whereNull('deleted_at')->first();
   return $paketKlinik;
}


function cekValue($form_result, $params, $num)
{
  if (isset($form_result[$num][$params])) {
    if ($params == "date_test") {
      return Carbon\Carbon::createFromFormat('Y-m-d H:i:s',  $form_result[$num][$params])->format('d/m/Y');
    } else {
      return $form_result[$num][$params];
    }
  } else {
    if ($params == "date_test") {
      return Carbon\Carbon::now()->format('d/m/Y');
    } else {
      return '';
    }
  }
}

function get_linkmenu($type = null)
{
  if ($type == NULL) {
    $get_name_control = DB::table('ms_menus')->where('link', '=', Request::segment(1))->first();
  } else {
    $get_name_control = DB::table('ms_menus')->where('type', '=', $type)->first();
  }
  $url = $get_name_control->link;
  return $url;
}

function get_linkname()
{
  $url_segment = Request::segment(1);
  if ($url_segment == NULL) {
    $url_segment = '';
  }
  $get_name_control = DB::table('ms_menus')->where('link', '=', $url_segment)->where('deleted_at', NULL)->first();
  $url = $get_name_control->name;
  return $url;
}

function getLayout($type, $module)
{
  if ($type == "1") {
?>
Lorem, ipsum dolor sit amet consectetur adipisicing elit. Temporibus, commodi aut! Aperiam, alias? Cumque omnis
quibusdam nostrum maiores ipsum, quasi officia inventore doloremque accusamus quis doloribus sit quos quae dolorem?
<?php
  }
}

function GetLayoutModule($column, $modules)
{
  return view('masterweb::module.admin.layoutmodule.columns', compact('column', 'modules'));
}

function GetLayoutModulePublic($column, $modules)
{
  return view('masterweb::module.admin.layoutmodule.column_modules', compact('column', 'modules'));
}

function getModule($module)
{
  $getModule = DB::table('ms_module')->where('id', '=', $module)->first();
  ?>
<div class="card rounded border mb-2">
    <div class="card-body p-3 moduleId" data-id="<?= $module ?>">
        <div class="media">
            <i class="fa fa-news icon-sm text-primary align-self-center mr-3"></i>
            <div class="media-body">
                <h6 class="mb-1"><?= $getModule->name ?></h6>
                <p class="mb-0 text-muted">
                    <?= $getModule->module ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php
}

function smt_reference($kode, $value = null)
{

  switch ($kode) {
    case 'PUBLISH':
      $data = array(
        '1' => 'Aktif',
        '0' => 'Tidak Aktif'
      );
      break;

    case 'CONTENTREF':
      $data = array(
        '1' => 'Full',
        '0' => 'List'
      );
      break;

    case 'SEKS':
      $data = array(
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        'l' => 'Laki-laki',
        'p' => 'Perempuan'
      );
      break;

    case 'AGAMA':
      $data = array(
        '1' => 'Islam',
        '2' => 'Kristen Protestan',
        '3' => 'Katolik',
        '4' => 'Hindu',
        '5' => 'Budha',
        '6' => 'Konghuchu'
      );
      break;

    case 'JENJANG':
      $data = array(
        'SD' => 'SD/MI Sederajat',
        'SMP' => 'SMP/MTS Sederajat',
        'SMA' => 'SMA/SMK/MK Sederajat',
      );
    case 'TYPE_PRODUCT':
      $data = array(
        'A' => 'Alat',
        'B' => 'Bahan Habis Pakai'
      );

    case 'STATUS_SAMPLE':
      $data = array(
        '0' => 'Menunggu',
        'A' => 'Permintaan Pemeriksaan',
        'B' => 'Persiapan Sampel',
        'C' => 'Pengambilan Sampel	',
        'D' => 'Penerimaan Sampel',
        'E' => 'Penanganan Sampel',
        'F' => 'Persiapan Reagen',
        'G' => 'Pipetase / Inokulasi',
        'H' => 'Preparasi',
        'I' => 'Inkubasi',
        'J' => 'Pemeriksaan Alat',
        'K' => 'Baca Hasil',
        'L' => 'Pelaporan Hasil',
        'M' => 'Pengetikan Hasil',
        'N' => 'Verifikasi Hasil',
        'O' => 'Pengesahan Hasil',
        '1' => 'Pengesahan Hasil',
      );
  }

  if ($value == null) {
    return $data;
  } else {
    return $data[$value];
  }
}

function isSelected($a, $b)
{
  if ($a == $b) {
    return "selected";
  }
}

function Info_umum($value)
{
  # code...
  if ($value == 1) {
    return "Search Engine";
  } elseif ($value == 2) {
    return "Mailing Partner";
  } elseif ($value == 3) {
    return "News Letter";
  } elseif ($value == 4) {
    return "Facebook";
  } else {
    return "Twitter";
  }
}

function getAction($action)
{
  $user = Auth()->user();
  $level = $user->level;

  $role = \Smt\Masterweb\Models\AdminMenu::where('ms_menuadm.link', '=',  "/" . Request::segment(1))
    ->Orwhere('ms_menuadm.link', '=',  Request::segment(1))
    ->join('tb_role', function ($join) use ($level) {
      $join->on('tb_role.menu_id', '=', 'ms_menuadm.id')
        ->where('privilege_id', '=', $level)
        ->whereNull('ms_menuadm.deleted_at')
        ->whereNull('tb_role.deleted_at');
    })
    ->first();

  $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
  //  ;

  if ($action != "create" && $action != "read" && $action != "update" && $action != "delete") {
    if (preg_match($UUIDv4, $action) || !isset($action)) {

      $access = \Smt\Masterweb\Models\PrivilegeMenuRole::where(['tb_privilege_menu_role.privilege_id' => $level])
        ->join('tb_privilege_menu', function ($join) {
          $join->on('tb_privilege_menu.id_privilege_features', '=', 'tb_privilege_menu_role.id_privilege_menu')
            // ->where('tb_privilege_menu.sub_link', 'like', "%" . substr(Request::segment(1),0,strlen(Request::segment(1))))
            // ->orWhere('tb_privilege_menu.sub_link', 'like', "%" . substr(Request::segment(1),0,strlen(Request::segment(1))))
            ->whereNull('tb_privilege_menu.deleted_at')
            ->whereNull('tb_privilege_menu_role.deleted_at');
        })
        ->join('ms_menuadm', function ($join) {
          $join->on('tb_privilege_menu.menu_id', '=', 'ms_menuadm.id')
            ->whereNull('tb_privilege_menu.deleted_at')
            ->where('ms_menuadm.link', 'like', "%" . substr(Request::segment(1), 0, strlen(Request::segment(1)) - 1))
            ->orWhere('ms_menuadm.link', 'like', "%" . substr(Request::segment(1), 0, strlen(Request::segment(1))))
            ->whereNull('ms_menuadm.deleted_at');
        })

        // ->where('tb_privilege_menu.sub_link', 'like', "%".$action."%")
        // ->where('tb_privilege_menu.privilege_id', '=', $level)
        ->get();

      if (count($access) == 0) {

        $access = \Smt\Masterweb\Models\PrivilegeMenuRole::where(['tb_privilege_menu_role.privilege_id' => $level])
          ->join('tb_privilege_menu', function ($join) {
            $join->on('tb_privilege_menu.id_privilege_features', '=', 'tb_privilege_menu_role.id_privilege_menu')
              ->where('tb_privilege_menu.sub_link', 'like', "%" . substr(Request::segment(1), 0, strlen(Request::segment(1))))
              // ->orWhere('tb_privilege_menu.sub_link', 'like', "%" . substr(Request::segment(1),0,strlen(Request::segment(1))))
              ->whereNull('tb_privilege_menu.deleted_at')
              ->whereNull('tb_privilege_menu_role.deleted_at');
          })
          ->join('ms_menuadm', function ($join) {
            $join->on('tb_privilege_menu.menu_id', '=', 'ms_menuadm.id')
              ->whereNull('tb_privilege_menu.deleted_at')
              // ->where('ms_menuadm.link', 'like', "%" . substr(Request::segment(1),0,strlen(Request::segment(1))-1))
              // ->orWhere('ms_menuadm.link', 'like', "%" . substr(Request::segment(1),0,strlen(Request::segment(1))))
              ->whereNull('ms_menuadm.deleted_at');
          })

          // ->where('tb_privilege_menu.sub_link', 'like', "%".$action."%")
          // ->where('tb_privilege_menu.privilege_id', '=', $level)
          ->get();
      }
    } else {
      $access = \Smt\Masterweb\Models\PrivilegeMenuRole::where(['tb_privilege_menu_role.privilege_id' => $level])
        ->join('tb_privilege_menu', function ($join) {
          $join->on('tb_privilege_menu.id_privilege_features', '=', 'tb_privilege_menu_role.id_privilege_menu')

            ->whereNull('tb_privilege_menu.deleted_at')
            ->whereNull('tb_privilege_menu_role.deleted_at');
        })
        ->join('ms_menuadm', function ($join) {
          $join->on('tb_privilege_menu.menu_id', '=', 'ms_menuadm.id')
            ->whereNull('tb_privilege_menu.deleted_at')
            ->where('ms_menuadm.link', 'like', "%" . substr(Request::segment(1), 0, strlen(Request::segment(1)) - 1))
            ->orWhere('ms_menuadm.link', 'like', "%" . substr(Request::segment(1), 0, strlen(Request::segment(1))))
            ->whereNull('ms_menuadm.deleted_at');
        })

        ->where('tb_privilege_menu.sub_link', 'like', "%" . $action . "%")
        // ->where('tb_privilege_menu.privilege_id', '=', $level)
        ->get();
    }

    $user  = Auth()->user()->getlevel()->first();
    $level = $user->level;

    if (!isset($role)) {
      return true;
    } else {

      if (!isset($role[$action])) {
        if (($role['link'] == "/device-management" &&
            ($action == 'live' || $action == 'live2' || $action == 'activation' || $action == 'connect' || $action == 'report' || $action == 'edit_offset' ||  (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $action == 'setting-regresi')))
          || ($role['link'] == "/report-management" && $level != "DSB")
          || $role['link'] == "/biodata"
          || $role['link'] == "adm-password"

        ) {
          return true;
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $role['link'] == "/distributor-management") {
          return true;
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM" || $level == "DSB") && $role['link'] == "/client-management") {
          return true;
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM" || $level == "DSB" || $level == "CLI") && $role['link'] == "/user-client-management") {
          return true;
        } else {

          return false;
        }
      } else {

        if (($role['link'] == "/device-management" &&
            ($action == 'live' || $action == 'live2' || $action == 'activation' || (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $action == 'connect') || $action == 'report' || $action == 'edit_offset' || (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $action == 'setting-regresi')))
          || ($role['link'] == "/report-management" && $level != "DSB")
          || $role['link'] == "/biodata"
          || $role['link'] == "/adm-password"

        ) {


          return true;
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $role['link'] == "/distributor-management") {
          if ($action == "read") {
            return true;
          } else {
            if ($role[$action] == "1") {
              return true;
            } else {
              return false;
            }
          }
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM" || $level == "DSB") && $role['link'] == "/client-management") {
          if ($action == "read") {
            return true;
          } else {
            if ($role[$action] == "1") {
              return true;
            } else {
              return false;
            }
          }
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM" || $level == "DSB" || $level == "CLI") && $role['link'] == "/user-client-management") {
          if ($action == "read") {
            return true;
          } else {
            if ($role[$action] == "1") {
              return true;
            } else {
              return false;
            }
          }
        } else {

          if ($role[$action] == "1") {
            return true;
          } else {
            return false;
          }
        }
      }
    }
  } else {
    $access = \Smt\Masterweb\Models\PrivilegeMenuRole::where(['tb_privilege_menu_role.privilege_id' => $level])
      ->join('tb_privilege_menu', function ($join) {
        $join->on('tb_privilege_menu.id_privilege_features', '=', 'tb_privilege_menu_role.id_privilege_menu')

          ->whereNull('tb_privilege_menu.deleted_at')
          ->whereNull('tb_privilege_menu_role.deleted_at');
      })
      ->join('ms_menuadm', function ($join) {
        $join->on('tb_privilege_menu.menu_id', '=', 'ms_menuadm.id')
          ->whereNull('tb_privilege_menu.deleted_at')
          ->where('ms_menuadm.link', 'like', "%" . substr(Request::segment(1), 0, strlen(Request::segment(1)) - 1))
          ->orWhere('ms_menuadm.link', 'like', "%" . substr(Request::segment(1), 0, strlen(Request::segment(1))))
          ->whereNull('ms_menuadm.deleted_at');
      })

      ->where('tb_privilege_menu.sub_link', 'like', "%" . $action . "%")
      // ->where('tb_privilege_menu.privilege_id', '=', $level)
      ->get();



    if (!isset($role)) {
      return true;
    } else {

      if (!isset($role[$action])) {
        if (($role['link'] == "/device-management" &&
            ($action == 'live' || $action == 'live2' || $action == 'activation' || $action == 'connect' || $action == 'report' || $action == 'edit_offset' ||  (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $action == 'setting-regresi')))
          || ($role['link'] == "/report-management" && $level != "DSB")
          || $role['link'] == "/biodata"
          || $role['link'] == "adm-password"

        ) {
          return true;
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $role['link'] == "/distributor-management") {
          return true;
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM" || $level == "DSB") && $role['link'] == "/client-management") {
          return true;
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM" || $level == "DSB" || $level == "CLI") && $role['link'] == "/user-client-management") {
          return true;
        } else {

          return false;
        }
      } else {

        if (($role['link'] == "/device-management" &&
            ($action == 'live' || $action == 'live2' || $action == 'activation' || (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $action == 'connect') || $action == 'report' || $action == 'edit_offset' || (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $action == 'setting-regresi')))
          || ($role['link'] == "/report-management" && $level != "DSB")
          || $role['link'] == "/biodata"
          || $role['link'] == "/adm-password"

        ) {


          return true;
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM") && $role['link'] == "/distributor-management") {
          if ($action == "read") {
            return true;
          } else {
            if ($role[$action] == "1") {
              return true;
            } else {
              return false;
            }
          }
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM" || $level == "DSB") && $role['link'] == "/client-management") {
          if ($action == "read") {
            return true;
          } else {
            if ($role[$action] == "1") {
              return true;
            } else {
              return false;
            }
          }
        } elseif (($level == "ADMD" || $level == "DEV" || $level == "MANU" || $level == "MM" || $level == "DSB" || $level == "CLI") && $role['link'] == "/user-client-management") {
          if ($action == "read") {
            return true;
          } else {
            if ($role[$action] == "1") {
              return true;
            } else {
              return false;
            }
          }
        } else {

          if ($role[$action] == "1") {
            return true;
          } else {
            return false;
          }
        }
      }
    }
  }



  // return view('masterweb::module.admin.laboratorium.coba',compact('role'));
  // return Excel::download(new UsersExport, 'users.xlsx');
}

function arrayToKomma($array,$name=null,$name2=null)
{

  if(count($array)>1){
    $string="";
    $i=0;

    // foreach ($array as $arrayName) {
    //   # code...
    // }
    $array_temp =[];


    // array_unique($array);
    foreach ($array as $arrayName) {
      # code...
      // if($i<(count($array)-1)){

        if($i<(count($array)-2)){
          if(isset($name)){

            if(isset($name2)){
              array_push($array_temp,$arrayName[$name][$name2]);
              // $string=$string.$arrayName[$name][$name2].", ";
            }else{
              array_push($array_temp,$arrayName[$name]);
              // $string=$string.$arrayName[$name].", ";
            }

          }else{
            array_push($array_temp,$arrayName);
            // $string=$string.$arrayName.", ";
          }
        }else{
          if(isset($name)){

            if(isset($name2)){

              array_push($array_temp,$arrayName[$name][$name2]);
              // $string=$string.$arrayName[$name][$name2]." ";
            }else{
              array_push($array_temp,$arrayName[$name]);
              // $string=$string.$arrayName[$name]." ";
            }
          }else{
            array_push($array_temp,$arrayName);
            // $string=$string.$arrayName." ";
          }
        }

      // }else{
      //   if(isset($name)){
      //     if(isset($name2)){
      //       $string=$string."dan ".$arrayName[$name][$name2];
      //     }else{
      //       $string=$string."dan ".$arrayName[$name];
      //     }
      //   }else{
      //     $string=$string."dan ".$arrayName;
      //   }
      // }
      $i++;
    }


    // dd($array_temp);

    $array_temp=array_filter($array_temp, fn($value) => !is_null($value) && $value !== '');

    $array_temp=array_unique($array_temp);
    $i=0;
    if(count($array_temp)>1){
      # code...
      foreach ($array_temp as $arrayName) {
        # code...
        if($i<(count($array)-2)){
          if($i<(count($array_temp)-1)){
            $string=$string.$arrayName.", ";
          }else{
            $string=$string.$arrayName." ";
          }
        }else{
          $string=$string." dan ".$arrayName;
        }

        $i++;

      }

    }else{
      $string=$array_temp[0];
    }
    return $string;
  }elseif(count($array)==1){
    if(isset($name)){
      if(isset($name2)){
        return $array[0][$name][$name2];
      }else{
        return $array[0][$name];
      }
    }else{
      return $array[0];
    }
  }else{
    return "";
  }
}


function getSpesialAction($parent, $action, $id_device = null)
{
  $user = Auth()->user();
  $level = $user->level;

  $privilage = \Smt\Masterweb\Models\Privileges::where(['id' => $level])->first();

  $access = \Smt\Masterweb\Models\PrivilegeMenuRole::where(['privilege_id' => $privilage->id])
    ->join('tb_privilege_menu', function ($join) {
      $join->on('tb_privilege_menu.id_privilege_features', '=', 'tb_privilege_menu_role.id_privilege_menu')

        ->whereNull('tb_privilege_menu.deleted_at')
        ->whereNull('tb_privilege_menu_role.deleted_at');
    })
    ->where('sub_link', '=', $action)
    ->first();


  if ($access != null) {
    if ($access->value) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }





  // $role = \Smt\Masterweb\Models\Role::where(['privilege_id'=>$level])
  // ->join('ms_menuadm', function ($join) {
  //     $join->on('tb_role.menu_id', '=', 'ms_menuadm.id')
  //     ->where('ms_menuadm.link', 'like', "%".Request::segment(1))
  //     ->whereNull('ms_menuadm.deleted_at')
  //     ->whereNull('tb_role.deleted_at');
  // })
  // ->first();


  // if($role[$action]){
  //     return true;
  // }else{
  //     return false;
  // }


  // return view('masterweb::module.admin.laboratorium.coba',compact('role'));
  // return Excel::download(new UsersExport, 'users.xlsx');
}


function template_email($url, $nama_member, $status_member, $opt)
{
  $html = '

        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><!--[if IE]><html xmlns="http://www.w3.org/1999/xhtml" class="ie"><![endif]--><!--[if !IE]><!--><html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]--><head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title></title>
            <!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge" /><!--<![endif]-->
            <meta name="viewport" content="width=device-width" /><style type="text/css">
            @media only screen and (min-width: 620px){.wrapper{min-width:600px !important}.wrapper h1{}.wrapper h1{font-size:26px !important;line-height:34px !important}.wrapper h2{}.wrapper h2{font-size:20px !important;line-height:28px !important}.wrapper h3{}.column{}.wrapper .size-8{font-size:8px !important;line-height:14px !important}.wrapper .size-9{font-size:9px !important;line-height:16px !important}.wrapper .size-10{font-size:10px !important;line-height:18px !important}.wrapper .size-11{font-size:11px !important;line-height:19px !important}.wrapper .size-12{font-size:12px !important;line-height:19px !important}.wrapper .size-13{font-size:13px !important;line-height:21px !important}.wrapper .size-14{font-size:14px !important;line-height:21px !important}.wrapper .size-15{font-size:15px !important;line-height:23px !important}.wrapper .size-16{font-size:16px !important;line-height:24px
            !important}.wrapper .size-17{font-size:17px !important;line-height:26px !important}.wrapper .size-18{font-size:18px !important;line-height:26px !important}.wrapper .size-20{font-size:20px !important;line-height:28px !important}.wrapper .size-22{font-size:22px !important;line-height:31px !important}.wrapper .size-24{font-size:24px !important;line-height:32px !important}.wrapper .size-26{font-size:26px !important;line-height:34px !important}.wrapper .size-28{font-size:28px !important;line-height:36px !important}.wrapper .size-30{font-size:30px !important;line-height:38px !important}.wrapper .size-32{font-size:32px !important;line-height:40px !important}.wrapper .size-34{font-size:34px !important;line-height:43px !important}.wrapper .size-36{font-size:36px !important;line-height:43px !important}.wrapper .size-40{font-size:40px !important;line-height:47px !important}.wrapper
            .size-44{font-size:44px !important;line-height:50px !important}.wrapper .size-48{font-size:48px !important;line-height:54px !important}.wrapper .size-56{font-size:56px !important;line-height:60px !important}.wrapper .size-64{font-size:64px !important;line-height:63px !important}}
            </style>
                <meta name="x-apple-disable-message-reformatting" />
                <style type="text/css">
            body {
            margin: 0;
            padding: 0;
            }
            table {
            border-collapse: collapse;
            table-layout: fixed;
            }
            * {
            line-height: inherit;
            }
            [x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            }
            .wrapper .footer__share-button a:hover,
            .wrapper .footer__share-button a:focus {
            color: #ffffff !important;
            }
            .btn a:hover,
            .btn a:focus,
            .footer__share-button a:hover,
            .footer__share-button a:focus,
            .email-footer__links a:hover,
            .email-footer__links a:focus {
            opacity: 0.8;
            }
            .preheader,
            .header,
            .layout,
            .column {
            transition: width 0.25s ease-in-out, max-width 0.25s ease-in-out;
            }
            .preheader td {
            padding-bottom: 8px;
            }
            .layout,
            div.header {
            max-width: 400px !important;
            -fallback-width: 95% !important;
            width: calc(100% - 20px) !important;
            }
            div.preheader {
            max-width: 360px !important;
            -fallback-width: 90% !important;
            width: calc(100% - 60px) !important;
            }
            .snippet,
            .webversion {
            Float: none !important;
            }
            .stack .column {
            max-width: 400px !important;
            width: 100% !important;
            }
            .fixed-width.has-border {
            max-width: 402px !important;
            }
            .fixed-width.has-border .layout__inner {
            box-sizing: border-box;
            }
            .snippet,
            .webversion {
            width: 50% !important;
            }
            .ie .btn {
            width: 100%;
            }
            .ie .stack .column,
            .ie .stack .gutter {
            display: table-cell;
            float: none !important;
            }
            .ie div.preheader,
            .ie .email-footer {
            max-width: 560px !important;
            width: 560px !important;
            }
            .ie .snippet,
            .ie .webversion {
            width: 280px !important;
            }
            .ie div.header,
            .ie .layout {
            max-width: 600px !important;
            width: 600px !important;
            }
            .ie .two-col .column {
            max-width: 300px !important;
            width: 300px !important;
            }
            .ie .three-col .column,
            .ie .narrow {
            max-width: 200px !important;
            width: 200px !important;
            }
            .ie .wide {
            width: 400px !important;
            }
            .ie .stack.fixed-width.has-border,
            .ie .stack.has-gutter.has-border {
            max-width: 602px !important;
            width: 602px !important;
            }
            .ie .stack.two-col.has-gutter .column {
            max-width: 290px !important;
            width: 290px !important;
            }
            .ie .stack.three-col.has-gutter .column,
            .ie .stack.has-gutter .narrow {
            max-width: 188px !important;
            width: 188px !important;
            }
            .ie .stack.has-gutter .wide {
            max-width: 394px !important;
            width: 394px !important;
            }
            .ie .stack.two-col.has-gutter.has-border .column {
            max-width: 292px !important;
            width: 292px !important;
            }
            .ie .stack.three-col.has-gutter.has-border .column,
            .ie .stack.has-gutter.has-border .narrow {
            max-width: 190px !important;
            width: 190px !important;
            }
            .ie .stack.has-gutter.has-border .wide {
            max-width: 396px !important;
            width: 396px !important;
            }
            .ie .fixed-width .layout__inner {
            border-left: 0 none white !important;
            border-right: 0 none white !important;
            }
            .ie .layout__edges {
            display: none;
            }
            .mso .layout__edges {
            font-size: 0;
            }
            .layout-fixed-width,
            .mso .layout-full-width {
            background-color: #ffffff;
            }
            @media only screen and (min-width: 620px) {
            .column,
            .gutter {
                display: table-cell;
                Float: none !important;
                vertical-align: top;
            }
            div.preheader,
            .email-footer {
                max-width: 560px !important;
                width: 560px !important;
            }
            .snippet,
            .webversion {
                width: 280px !important;
            }
            div.header,
            .layout,
            .one-col .column {
                max-width: 600px !important;
                width: 600px !important;
            }
            .fixed-width.has-border,
            .fixed-width.x_has-border,
            .has-gutter.has-border,
            .has-gutter.x_has-border {
                max-width: 602px !important;
                width: 602px !important;
            }
            .two-col .column {
                max-width: 300px !important;
                width: 300px !important;
            }
            .three-col .column,
            .column.narrow,
            .column.x_narrow {
                max-width: 200px !important;
                width: 200px !important;
            }
            .column.wide,
            .column.x_wide {
                width: 400px !important;
            }
            .two-col.has-gutter .column,
            .two-col.x_has-gutter .column {
                max-width: 290px !important;
                width: 290px !important;
            }
            .three-col.has-gutter .column,
            .three-col.x_has-gutter .column,
            .has-gutter .narrow {
                max-width: 188px !important;
                width: 188px !important;
            }
            .has-gutter .wide {
                max-width: 394px !important;
                width: 394px !important;
            }
            .two-col.has-gutter.has-border .column,
            .two-col.x_has-gutter.x_has-border .column {
                max-width: 292px !important;
                width: 292px !important;
            }
            .three-col.has-gutter.has-border .column,
            .three-col.x_has-gutter.x_has-border .column,
            .has-gutter.has-border .narrow,
            .has-gutter.x_has-border .narrow {
                max-width: 190px !important;
                width: 190px !important;
            }
            .has-gutter.has-border .wide,
            .has-gutter.x_has-border .wide {
                max-width: 396px !important;
                width: 396px !important;
            }
            }
            @supports (display: flex) {
            @media only screen and (min-width: 620px) {
                .fixed-width.has-border .layout__inner {
                display: flex !important;
                }
            }
            }
            @media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2/1), only screen and (min-device-pixel-ratio: 2), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
            .fblike {
                background-image: url(https://i7.createsend1.com/static/eb/master/13-the-blueprint-3/images/fblike@2x.png) !important;
            }
            .tweet {
                background-image: url(https://i8.createsend1.com/static/eb/master/13-the-blueprint-3/images/tweet@2x.png) !important;
            }
            .linkedinshare {
                background-image: url(https://i9.createsend1.com/static/eb/master/13-the-blueprint-3/images/lishare@2x.png) !important;
            }
            .forwardtoafriend {
                background-image: url(https://i10.createsend1.com/static/eb/master/13-the-blueprint-3/images/forward@2x.png) !important;
            }
            }
            @media (max-width: 321px) {
            .fixed-width.has-border .layout__inner {
                border-width: 1px 0 !important;
            }
            .layout,
            .stack .column {
                min-width: 320px !important;
                width: 320px !important;
            }
            .border {
                display: none;
            }
            .has-gutter .border {
                display: table-cell;
            }
            }
            .mso div {
            border: 0 none white !important;
            }
            .mso .w560 .divider {
            Margin-left: 260px !important;
            Margin-right: 260px !important;
            }
            .mso .w360 .divider {
            Margin-left: 160px !important;
            Margin-right: 160px !important;
            }
            .mso .w260 .divider {
            Margin-left: 110px !important;
            Margin-right: 110px !important;
            }
            .mso .w160 .divider {
            Margin-left: 60px !important;
            Margin-right: 60px !important;
            }
            .mso .w354 .divider {
            Margin-left: 157px !important;
            Margin-right: 157px !important;
            }
            .mso .w250 .divider {
            Margin-left: 105px !important;
            Margin-right: 105px !important;
            }
            .mso .w148 .divider {
            Margin-left: 54px !important;
            Margin-right: 54px !important;
            }
            .mso .size-8,
            .ie .size-8 {
            font-size: 8px !important;
            line-height: 14px !important;
            }
            .mso .size-9,
            .ie .size-9 {
            font-size: 9px !important;
            line-height: 16px !important;
            }
            .mso .size-10,
            .ie .size-10 {
            font-size: 10px !important;
            line-height: 18px !important;
            }
            .mso .size-11,
            .ie .size-11 {
            font-size: 11px !important;
            line-height: 19px !important;
            }
            .mso .size-12,
            .ie .size-12 {
            font-size: 12px !important;
            line-height: 19px !important;
            }
            .mso .size-13,
            .ie .size-13 {
            font-size: 13px !important;
            line-height: 21px !important;
            }
            .mso .size-14,
            .ie .size-14 {
            font-size: 14px !important;
            line-height: 21px !important;
            }
            .mso .size-15,
            .ie .size-15 {
            font-size: 15px !important;
            line-height: 23px !important;
            }
            .mso .size-16,
            .ie .size-16 {
            font-size: 16px !important;
            line-height: 24px !important;
            }
            .mso .size-17,
            .ie .size-17 {
            font-size: 17px !important;
            line-height: 26px !important;
            }
            .mso .size-18,
            .ie .size-18 {
            font-size: 18px !important;
            line-height: 26px !important;
            }
            .mso .size-20,
            .ie .size-20 {
            font-size: 20px !important;
            line-height: 28px !important;
            }
            .mso .size-22,
            .ie .size-22 {
            font-size: 22px !important;
            line-height: 31px !important;
            }
            .mso .size-24,
            .ie .size-24 {
            font-size: 24px !important;
            line-height: 32px !important;
            }
            .mso .size-26,
            .ie .size-26 {
            font-size: 26px !important;
            line-height: 34px !important;
            }
            .mso .size-28,
            .ie .size-28 {
            font-size: 28px !important;
            line-height: 36px !important;
            }
            .mso .size-30,
            .ie .size-30 {
            font-size: 30px !important;
            line-height: 38px !important;
            }
            .mso .size-32,
            .ie .size-32 {
            font-size: 32px !important;
            line-height: 40px !important;
            }
            .mso .size-34,
            .ie .size-34 {
            font-size: 34px !important;
            line-height: 43px !important;
            }
            .mso .size-36,
            .ie .size-36 {
            font-size: 36px !important;
            line-height: 43px !important;
            }
            .mso .size-40,
            .ie .size-40 {
            font-size: 40px !important;
            line-height: 47px !important;
            }
            .mso .size-44,
            .ie .size-44 {
            font-size: 44px !important;
            line-height: 50px !important;
            }
            .mso .size-48,
            .ie .size-48 {
            font-size: 48px !important;
            line-height: 54px !important;
            }
            .mso .size-56,
            .ie .size-56 {
            font-size: 56px !important;
            line-height: 60px !important;
            }
            .mso .size-64,
            .ie .size-64 {
            font-size: 64px !important;
            line-height: 63px !important;
            }
            .btn {
                border-radius: 3px;
                box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
                color: #fff;
                display: inline-block;
                text-decoration: none;
                -webkit-text-size-adjust: none;
            }
            .btn-red,
            .btn-error {
                background-color: #e3342f;
                border-top: 10px solid #e3342f;
                border-right: 18px solid #e3342f;
                border-bottom: 10px solid #e3342f;
                border-left: 18px solid #e3342f;
            }
            </style>

            <!--[if !mso]><!--><style type="text/css">
            @import url(https://fonts.googleapis.com/css?family=Roboto:400,700,400italic,700italic);
            </style><link href="https://fonts.googleapis.com/css?family=Roboto:400,700,400italic,700italic" rel="stylesheet" type="text/css" /><!--<![endif]--><style type="text/css">
            body{background-color:#fbfbfb}.logo a:hover,.logo a:focus{color:#1e2e3b !important}.mso .layout-has-border{border-top:1px solid #c8c8c8;border-bottom:1px solid #c8c8c8}.mso .layout-has-bottom-border{border-bottom:1px solid #c8c8c8}.mso .border,.ie .border{background-color:#c8c8c8}.mso h1,.ie h1{}.mso h1,.ie h1{font-size:26px !important;line-height:34px !important}.mso h2,.ie h2{}.mso h2,.ie h2{font-size:20px !important;line-height:28px !important}.mso h3,.ie h3{}.mso .layout__inner,.ie .layout__inner{}.mso .footer__share-button p{}.mso .footer__share-button p{font-family:Georgia,serif}
            </style><meta name="robots" content="noindex,nofollow" />
            <meta property="og:title" content="My First Campaign" />
            </head>
            <!--[if mso]>
            <body class="mso">
            <![endif]-->
            <!--[if !mso]><!-->
            <body class="full-padding" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;">
            <!--<![endif]-->
                <table class="wrapper" style="border-collapse: collapse;table-layout: fixed;min-width: 320px;width: 100%;background-color: #fbfbfb;" cellpadding="0" cellspacing="0" role="presentation"><tbody><tr><td>
                <div role="banner">
                    <div class="preheader" style="Margin: 0 auto;max-width: 560px;min-width: 280px; width: 280px;width: calc(28000% - 167440px);">
                    <div style="border-collapse: collapse;display: table;width: 100%;">
                    <!--[if (mso)|(IE)]><table align="center" class="preheader" cellpadding="0" cellspacing="0" role="presentation"><tr><td style="width: 280px" valign="top"><![endif]-->
                        <div class="snippet" style="display: table-cell;Float: left;font-size: 12px;line-height: 19px;max-width: 280px;min-width: 140px; width: 140px;width: calc(14000% - 78120px);padding: 10px 0 5px 0;color: #999;font-family: Georgia,serif;">

                        </div>
                    <!--[if (mso)|(IE)]></td><td style="width: 280px" valign="top"><![endif]-->
                        <div class="webversion" style="display: table-cell;Float: left;font-size: 12px;line-height: 19px;max-width: 280px;min-width: 139px; width: 139px;width: calc(14100% - 78680px);padding: 10px 0 5px 0;text-align: right;color: #999;font-family: Georgia,serif;">
                        </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                    </div>
                    <div class="header" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);" id="emb-email-header-container">
                    <!--[if (mso)|(IE)]><table align="center" class="header" cellpadding="0" cellspacing="0" role="presentation"><tr><td style="width: 600px"><![endif]-->
                    <div class="logo emb-logo-margin-box" style="font-size: 26px;line-height: 32px;Margin-top: 6px;Margin-bottom: 20px;color: #41637e;font-family: Avenir,sans-serif;Margin-left: 20px;Margin-right: 20px;" align="center">
                    <div class="logo-center" align="center" id="emb-email-header"><img style="display: block;height: auto;width: 100%;border: 0;max-width: 254px;" src="" alt="" width="254" /></div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                </div>
                <div>
                <div class="layout one-col fixed-width stack" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
                    <div class="layout__inner" style="border-collapse: collapse;display: table;width: 100%;background-color: #ffffff;">
                    <!--[if (mso)|(IE)]><table align="center" cellpadding="0" cellspacing="0" role="presentation"><tr class="layout-fixed-width" style="background-color: #ffffff;"><td style="width: 600px" class="w560"><![endif]-->
                    <div class="column" style="text-align: left;color: #565656;font-size: 14px;line-height: 21px;font-family: Georgia,serif;">

                        <div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 24px;Margin-bottom: 24px;">
                <div style="mso-line-height-rule: exactly;mso-text-raise: 11px;vertical-align: middle;">
                    <h1 class="size-30" style="Margin-top: 0;Margin-bottom: 0;font-style: normal;font-weight: normal;color: #565656;font-size: 26px;line-height: 34px;font-family: Avenir,sans-serif;" lang="x-size-30">Hai ' . $nama_member . ' !</h1>
                    <p class="size-20" style="Margin-top: 20px;Margin-bottom: 0;font-family: roboto,tahoma,sans-serif;font-size: 17px;line-height: 26px;" lang="x-size-20"><span class="font-roboto">Terimakasih telah mendaftarkan diri di ' . $opt->title . '.</span></p>
                    <p class="size-20" style="Margin-top: 20px;Margin-bottom: 0;font-family: roboto,tahoma,sans-serif;font-size: 17px;line-height: 26px;" lang="x-size-20"><span class="font-roboto">Silahkan Klik tombol dibawah ini untuk memverifikasi akun Anda.</span></p>

                    <a href="' . $url . '" style="Margin-top: 20px;Margin-bottom: 0;font-family: roboto,tahoma,sans-serif;font-size: 17px;line-height: 26px;color:#000;  border-radius: 3px;
                    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);color: #fff;display: inline-block;text-decoration: none;-webkit-text-size-adjust: none;background-color: #e3342f;border-top: 10px solid #e3342f;border-right: 18px solid #e3342f;border-bottom: 10px solid #e3342f;border-left: 18px solid #e3342f;" target="_blank"><span class="font-roboto">  verifikasi </span></a>

                    <p class="size-20" style="Margin-top: 20px;Margin-bottom: 0;font-family: roboto,tahoma,sans-serif;font-size: 17px;line-height: 26px;" lang="x-size-20"><span class="font-roboto">Hormat kami,</span><br><br><span class="font-roboto">Tim ' . $opt->title . '.</span></p>
                </div>
                </div>

                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                </div>

                <div style="mso-line-height-rule: exactly;line-height: 20px;font-size: 20px;">&nbsp;</div>


                <div role="contentinfo">
                    <div class="layout email-footer stack" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
                    <div class="layout__inner" style="border-collapse: collapse;display: table;width: 100%;">
                    <!--[if (mso)|(IE)]><table align="center" cellpadding="0" cellspacing="0" role="presentation"><tr class="layout-email-footer"><td style="width: 400px;" valign="top" class="w360"><![endif]-->
                        <div class="column wide" style="text-align: left;font-size: 12px;line-height: 19px;color: #999;font-family: Georgia,serif;Float: left;max-width: 400px;min-width: 320px; width: 320px;width: calc(8000% - 47600px);">
                        <div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 10px;Margin-bottom: 10px;">
                            <table class="email-footer__links" style="border-collapse: collapse;table-layout: fixed;" role="presentation" emb-web-links><tbody><tr role="navigation">

                            </tr></tbody></table>
                            <div style="font-size: 12px;line-height: 19px;Margin-top: 20px;font-family: roboto,tahoma,sans-serif;">
                            <div class="font-roboto">Copyright SIMKRAF &#169; 2020 All rights reserved.</div>
                            </div>
                            <div style="font-size: 12px;line-height: 19px;Margin-top: 18px;">

                            </div>
                            <!--[if mso]>&nbsp;<![endif]-->
                        </div>
                        </div>
                    <!--[if (mso)|(IE)]></td><td style="width: 200px;" valign="top" class="w160"><![endif]-->
                        <div class="column narrow" style="text-align: left;font-size: 12px;line-height: 19px;color: #999;font-family: Georgia,serif;Float: left;max-width: 320px;min-width: 200px; width: 320px;width: calc(72200px - 12000%);">
                        <div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 10px;Margin-bottom: 10px;">

                        </div>
                        </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                    </div>
                    <div class="layout one-col email-footer" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
                    <div class="layout__inner" style="border-collapse: collapse;display: table;width: 100%;">
                    <!--[if (mso)|(IE)]><table align="center" cellpadding="0" cellspacing="0" role="presentation"><tr class="layout-email-footer"><td style="width: 600px;" class="w560"><![endif]-->
                        <div class="column" style="text-align: left;font-size: 12px;line-height: 19px;color: #999;font-family: Georgia,serif;">
                        <div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 10px;Margin-bottom: 10px;">
                            <div style="font-size: 12px;line-height: 19px;">

                            </div>
                        </div>
                        </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                    </div>
                </div>
                <div style="line-height:40px;font-size:40px;">&nbsp;</div>
                </div></td></tr></tbody></table>

        </body></html>

        ';

  return $html;
}

function takeIt($module)
{
  if (isset($module[0]) and $module[0] == "html") {
    echo $module[1];
  } else {
    $getModule = DB::table('ms_module')->where('id', '=', $module)->first();
    echo view('masterweb::' . $getModule->module);
  }
}

function integerToRoman($integer)
{
  // Convert the integer into an integer (just to make sure)
  $integer = intval($integer);
  $result = '';

  // Create a lookup array that contains all of the Roman numerals.
  $lookup = array(
    'M' => 1000,
    'CM' => 900,
    'D' => 500,
    'CD' => 400,
    'C' => 100,
    'XC' => 90,
    'L' => 50,
    'XL' => 40,
    'X' => 10,
    'IX' => 9,
    'V' => 5,
    'IV' => 4,
    'I' => 1
  );

  foreach ($lookup as $roman => $value) {
    // Determine the number of matches
    $matches = intval($integer / $value);

    // Add the same number of characters to the string
    $result .= str_repeat($roman, $matches);

    // Set the integer to be the remainder of the integer and the value
    $integer = $integer % $value;
  }

  // The Roman numeral should be built, return it
  return $result;
}

function number_to_alphabet($number)
{
  $number = intval($number);
  if ($number <= 0) {
    return '';
  }
  $alphabet = '';
  while ($number != 0) {
    $p = ($number - 1) % 26;
    $number = intval(($number - $p) / 26);
    $alphabet = chr(65 + $p) . $alphabet;
  }
  return $alphabet;
}

function alphabet_to_number($string)
{
  $string = strtoupper($string);
  $length = strlen($string);
  $number = 0;
  $level = 1;
  while ($length >= $level) {
    $char = $string[$length - $level];
    $c = ord($char) - 64;
    $number += $c * (26 ** ($level - 1));
    $level++;
  }
  return $number;
}

if (!function_exists('fdate_sas')) {

  function fdate_sas($value, $format)

  {

    $set_date = explode(" ", $value);

    $date = explode("-", $set_date[0]);

    $tgl = $date[2];

    $bln = $date[1];

    $thn = $date[0];

    $return = "";



    switch ($format) {

      case "DDMMYYYY":

        $return = $tgl . " " . fbulan($bln) . " " . $thn;

        break;

      case "DDMMMYYYY":

        $return = $tgl . " " . substr(fbulan($bln), 0, 3) . " " . $thn;

        break;

      case "DD":

        $return = $tgl;

        break;

      case "MM":

        $return = $bln;

        break;

      case "YYYYY":

        $return = $thn;

        break;

      case "MMYYYY":

        $return = fbulan($bln) . " " . $thn;

        break;

      case "mm":

        $return = fbulan($bln);

        break;

        // case "HHDDMMYYYY" :

        //     $eks = explode(" ", $tgl);

        //     $tgl = $eks[0];

        //     $jam = $eks[1];

        // list($H,$M,$S) = explode(":",$jam);

        //     $return = $tgl." ".fbulan($bln)." ".$thn." | ".$H.":".$M.":".$S;

        // break;

    }

    return $return;
  }
}

if (!function_exists('fdate_carbon_sas')) {
  function fdate_carbon_sas($value, $format)
  {
    if ($value != null) {
      $set_date = explode(" ", $value);
      $date = explode("-", $set_date[0]);
      $tgl = $date[2];
      $bln = $date[1];
      $thn = $date[0];
      $return = "";

      switch ($format) {
        case "DDMMYYYYHHMM":
          $return = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->isoFormat('D MMMM Y HH:mm');
          break;

        case "DDMMYYYY-HHMM":
          $return = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->isoFormat('D MMMM Y');
          break;

        case "DDMMYYYY":
          $return = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->isoFormat('D MMMM Y');
          break;

          // OUTPUT 20 Mar 2018
        case "DDMMMYYYY":
          $return = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('%d %b %Y');
          break;

        case "DD":
          $return = $return = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->isoFormat('D');
          break;

        case "MM":
          $return = $return = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->isoFormat('MMM');
          break;

        case "YYYYY":
          $return = $return = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->isoFormat('Y');
          break;

        case "MMYYYY":
          $return = $return = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->isoFormat('MMM Y');
          break;

        default:
          return "not found";
          break;
      }

      return $return;
    }
  }
}

if (!function_exists('reference_sas')) {

  function reference_sas($code, $value = null)
  {
    switch ($code) {
      case 'gender':
        (object) $data = [
          'L' => 'Laki-laki',
          'P' => 'Perempuan',
        ];
        return ($value == null) ? $data : $data[$value];
        break;

      case 'jenis_pemeriksaan_klinik':
        (object) $data = [
          '0' => 'Pemeriksaan Klinik',
          '1' => 'PCR',
          '2' => 'Rapid Antibody',
          '3' => 'Rapid Antigen',
        ];
        return ($value == null) ? $data : $data[$value];
        break;

      case 'jenis_program':
        (object) $data = [
          '0' => 'AB',
          '1' => 'AM',
          '2' => 'MAKMIN',
          '3' => 'KLB',
          '4' => 'SIDAK PASAR',
          '5' => 'TTU,tpm',
          '6' => 'SARKES',
          '7' => 'NON SARKES',
          '8' => 'PARASITOLOGI',
          '9' => 'KLINIK',
        ];
        return ($value == null) ? $data : $data[$value];
        break;

      default:
        return "not found";
        break;
    }
  }
}

function sortingNumberKlinik()
{

  $start_num=StartNum::where('code_lab_start_number','KLI')->first();

  if ( date('Y')==$start_num->year_start_number) {
    //UPDATE tb_number_klinik n
    DB::statement("
        SET @current_number = 2831;
    ");
    DB::statement("

        UPDATE tb_number_klinik n
        JOIN (
            SELECT
                id_number_klinik,
                @current_number AS new_number,
                CASE
                    WHEN id_permohonan_uji_klinik IS NOT NULL THEN @current_number
                    ELSE
                        CASE
                            WHEN id_prolanis IS NOT NULL THEN @current_number + v.kuota_prolanis - 1
                            WHEN id_haji IS NOT NULL THEN @current_number + h.kuota_haji - 1
                            WHEN id_prolanis_gula IS NOT NULL THEN @current_number + g.kuota_prolanis_gula - 1
                            WHEN id_prolanis_urine IS NOT NULL THEN @current_number + u.kuota_prolanis_urine - 1
                        END
                END AS last_number,
                @current_number :=
                    CASE
                      WHEN id_prolanis IS NOT NULL THEN @current_number + v.kuota_prolanis
                        WHEN id_haji IS NOT NULL THEN @current_number + h.kuota_haji
                        WHEN id_prolanis_gula IS NOT NULL THEN @current_number + g.kuota_prolanis_gula
                        WHEN id_prolanis_urine IS NOT NULL THEN @current_number + u.kuota_prolanis_urine
                        ELSE @current_number + 1
                    END AS updated_current_number

            FROM (
                SELECT *
                FROM tb_number_klinik
                WHERE deleted_at IS NULL
                ORDER BY created_at ASC
            ) sorted


            LEFT JOIN tb_permohonan_uji_klinik_haji h
                ON sorted.id_haji = h.id_permohonan_uji_klinik_haji AND h.deleted_at IS NULL
            LEFT JOIN tb_permohonan_uji_klinik_prolanis_gula g
                ON sorted.id_prolanis_gula = g.id_permohonan_uji_klinik_prolanis_gula AND g.deleted_at IS NULL
            LEFT JOIN tb_permohonan_uji_klinik_prolanis_urine u
                ON sorted.id_prolanis_urine = u.id_permohonan_uji_klinik_prolanis_urine AND u.deleted_at IS NULL
            LEFT JOIN tb_permohonan_uji_klinik_prolanis v
                ON sorted.id_prolanis = v.id_permohonan_uji_klinik_prolanis AND v.deleted_at IS NULL
        ) ordered
        ON n.id_number_klinik = ordered.id_number_klinik
        SET
            n.new_number = ordered.new_number,
            n.last_number = ordered.last_number;

    ");


    //NON PROLANIS DAN HAJI
    DB::statement("
        UPDATE
                    tb_permohonan_uji_klinik_2 p
                JOIN
                    tb_number_klinik n
                    ON p.id_permohonan_uji_klinik = n.id_permohonan_uji_klinik
                SET
                    p.nourut_permohonan_uji_klinik = n.new_number,
                    p.noregister_permohonan_uji_klinik = CONCAT(
                        n.new_number,
                        '/LK/',
                        CASE
                            WHEN MONTH(p.created_at) = 1 THEN 'I'
                            WHEN MONTH(p.created_at) = 2 THEN 'II'
                            WHEN MONTH(p.created_at) = 3 THEN 'III'
                            WHEN MONTH(p.created_at) = 4 THEN 'IV'
                            WHEN MONTH(p.created_at) = 5 THEN 'V'
                            WHEN MONTH(p.created_at) = 6 THEN 'VI'
                            WHEN MONTH(p.created_at) = 7 THEN 'VII'
                            WHEN MONTH(p.created_at) = 8 THEN 'VIII'
                            WHEN MONTH(p.created_at) = 9 THEN 'IX'
                            WHEN MONTH(p.created_at) = 10 THEN 'X'
                            WHEN MONTH(p.created_at) = 11 THEN 'XI'
                            WHEN MONTH(p.created_at) = 12 THEN 'XII'
                        END,
                        '/',
                        YEAR(p.created_at)
                    )
                WHERE
                    n.id_permohonan_uji_klinik IS NOT NULL
                    AND p.deleted_at IS NULL
                    AND n.deleted_at IS NULL;
        ");


    //UBAH PROLANIS
    DB::statement("

      UPDATE
            `tb_permohonan_uji_klinik_2` p
        JOIN (
          SELECT p.id_permohonan_uji_klinik, n.new_number, n.id_prolanis, ROW_NUMBER() OVER (PARTITION BY p.id_permohonan_uji_klinik_prolanis ORDER BY p.nourut_permohonan_uji_klinik) AS urutan FROM `tb_permohonan_uji_klinik_2` p LEFT JOIN tb_number_klinik n ON p.id_permohonan_uji_klinik_prolanis IN (n.id_prolanis, n.id_prolanis_gula, n.id_prolanis_urine) WHERE n.id_prolanis IS NOT NULL AND n.id_permohonan_uji_klinik IS NULL
        ) u
        ON
          p.id_permohonan_uji_klinik = u.id_permohonan_uji_klinik
        SET
            p.nourut_permohonan_uji_klinik = u.new_number + u.urutan - 1,
            p.noregister_permohonan_uji_klinik = CONCAT(
                        u.new_number + u.urutan - 1,
                        '/LK/',
                        CASE
                            WHEN MONTH(p.created_at) = 1 THEN 'I'
                            WHEN MONTH(p.created_at) = 2 THEN 'II'
                            WHEN MONTH(p.created_at) = 3 THEN 'III'
                            WHEN MONTH(p.created_at) = 4 THEN 'IV'
                            WHEN MONTH(p.created_at) = 5 THEN 'V'
                            WHEN MONTH(p.created_at) = 6 THEN 'VI'
                            WHEN MONTH(p.created_at) = 7 THEN 'VII'
                            WHEN MONTH(p.created_at) = 8 THEN 'VIII'
                            WHEN MONTH(p.created_at) = 9 THEN 'IX'
                            WHEN MONTH(p.created_at) = 10 THEN 'X'
                            WHEN MONTH(p.created_at) = 11 THEN 'XI'
                            WHEN MONTH(p.created_at) = 12 THEN 'XII'
                        END,
                        '/',
                        YEAR(p.created_at)
                    );

      ");

    // UBAH HAJI
    DB::statement("

              UPDATE
            `tb_permohonan_uji_klinik_2` p
        JOIN (
          SELECT p.id_permohonan_uji_klinik, n.new_number, n.id_haji, ROW_NUMBER() OVER (PARTITION BY p.id_permohonan_uji_klinik_haji ORDER BY p.nourut_permohonan_uji_klinik) AS urutan FROM `tb_permohonan_uji_klinik_2` p LEFT JOIN tb_number_klinik n ON p.id_permohonan_uji_klinik_haji = n.id_haji WHERE n.id_haji IS NOT NULL AND n.id_permohonan_uji_klinik IS NULL
        ) u
        ON p.id_permohonan_uji_klinik = u.id_permohonan_uji_klinik
        SET
            p.nourut_permohonan_uji_klinik = u.new_number + u.urutan - 1,
            p.noregister_permohonan_uji_klinik = CONCAT(
                        u.new_number + u.urutan - 1,
                        '/LK/',
                        CASE
                            WHEN MONTH(p.created_at) = 1 THEN 'I'
                            WHEN MONTH(p.created_at) = 2 THEN 'II'
                            WHEN MONTH(p.created_at) = 3 THEN 'III'
                            WHEN MONTH(p.created_at) = 4 THEN 'IV'
                            WHEN MONTH(p.created_at) = 5 THEN 'V'
                            WHEN MONTH(p.created_at) = 6 THEN 'VI'
                            WHEN MONTH(p.created_at) = 7 THEN 'VII'
                            WHEN MONTH(p.created_at) = 8 THEN 'VIII'
                            WHEN MONTH(p.created_at) = 9 THEN 'IX'
                            WHEN MONTH(p.created_at) = 10 THEN 'X'
                            WHEN MONTH(p.created_at) = 11 THEN 'XI'
                            WHEN MONTH(p.created_at) = 12 THEN 'XII'
                        END,
                        '/',
                        YEAR(p.created_at)
                    );
    ");

  }else{
       //UPDATE tb_number_klinik n
    DB::statement("
       SET @current_number = 1;
   ");
   DB::statement("

       UPDATE tb_number_klinik n
       JOIN (
           SELECT
               id_number_klinik,
               @current_number AS new_number,
               CASE
                   WHEN id_permohonan_uji_klinik IS NOT NULL THEN @current_number
                   ELSE
                       CASE
                           WHEN id_prolanis IS NOT NULL THEN @current_number + v.kuota_prolanis - 1
                           WHEN id_haji IS NOT NULL THEN @current_number + h.kuota_haji - 1
                           WHEN id_prolanis_gula IS NOT NULL THEN @current_number + g.kuota_prolanis_gula - 1
                           WHEN id_prolanis_urine IS NOT NULL THEN @current_number + u.kuota_prolanis_urine - 1
                       END
               END AS last_number,
               @current_number :=
                   CASE
                     WHEN id_prolanis IS NOT NULL THEN @current_number + v.kuota_prolanis
                       WHEN id_haji IS NOT NULL THEN @current_number + h.kuota_haji
                       WHEN id_prolanis_gula IS NOT NULL THEN @current_number + g.kuota_prolanis_gula
                       WHEN id_prolanis_urine IS NOT NULL THEN @current_number + u.kuota_prolanis_urine
                       ELSE @current_number + 1
                   END AS updated_current_number

           FROM (
               SELECT *
               FROM tb_number_klinik
               WHERE deleted_at IS NULL
               AND YEAR(tb_number_klinik.created_at) = ".date('Y')."
               ORDER BY created_at ASC
           ) sorted


           LEFT JOIN tb_permohonan_uji_klinik_haji h
               ON sorted.id_haji = h.id_permohonan_uji_klinik_haji AND h.deleted_at IS NULL
           LEFT JOIN tb_permohonan_uji_klinik_prolanis_gula g
               ON sorted.id_prolanis_gula = g.id_permohonan_uji_klinik_prolanis_gula AND g.deleted_at IS NULL
           LEFT JOIN tb_permohonan_uji_klinik_prolanis_urine u
               ON sorted.id_prolanis_urine = u.id_permohonan_uji_klinik_prolanis_urine AND u.deleted_at IS NULL
           LEFT JOIN tb_permohonan_uji_klinik_prolanis v
               ON sorted.id_prolanis = v.id_permohonan_uji_klinik_prolanis AND v.deleted_at IS NULL
       ) ordered
       ON n.id_number_klinik = ordered.id_number_klinik

       SET
           n.new_number = ordered.new_number,
           n.last_number = ordered.last_number
      WHERE YEAR(n.created_at) = ".date('Y').";
   ");


   //NON PROLANIS DAN HAJI
   DB::statement("
       UPDATE
                   tb_permohonan_uji_klinik_2 p
               JOIN
                   tb_number_klinik n
                   ON p.id_permohonan_uji_klinik = n.id_permohonan_uji_klinik
               SET
                   p.nourut_permohonan_uji_klinik = n.new_number,
                   p.noregister_permohonan_uji_klinik = CONCAT(
                        LPAD(n.new_number, 4, '0'),
                       '/LK/',
                       CASE
                           WHEN MONTH(p.created_at) = 1 THEN 'I'
                           WHEN MONTH(p.created_at) = 2 THEN 'II'
                           WHEN MONTH(p.created_at) = 3 THEN 'III'
                           WHEN MONTH(p.created_at) = 4 THEN 'IV'
                           WHEN MONTH(p.created_at) = 5 THEN 'V'
                           WHEN MONTH(p.created_at) = 6 THEN 'VI'
                           WHEN MONTH(p.created_at) = 7 THEN 'VII'
                           WHEN MONTH(p.created_at) = 8 THEN 'VIII'
                           WHEN MONTH(p.created_at) = 9 THEN 'IX'
                           WHEN MONTH(p.created_at) = 10 THEN 'X'
                           WHEN MONTH(p.created_at) = 11 THEN 'XI'
                           WHEN MONTH(p.created_at) = 12 THEN 'XII'
                       END,
                       '/',
                       YEAR(p.created_at)
                   )
               WHERE
                   n.id_permohonan_uji_klinik IS NOT NULL
                   AND p.deleted_at IS NULL
                    AND YEAR(p.created_at) = ".date('Y')."
                       AND YEAR( n.created_at) = ".date('Y')."
                   AND n.deleted_at IS NULL;
       ");


   //UBAH PROLANIS
   DB::statement("

     UPDATE
           `tb_permohonan_uji_klinik_2` p
       JOIN (
         SELECT p.id_permohonan_uji_klinik, n.new_number, n.id_prolanis, ROW_NUMBER() OVER (PARTITION BY p.id_permohonan_uji_klinik_prolanis ORDER BY p.nourut_permohonan_uji_klinik) AS urutan FROM `tb_permohonan_uji_klinik_2` p LEFT JOIN tb_number_klinik n ON p.id_permohonan_uji_klinik_prolanis IN (n.id_prolanis, n.id_prolanis_gula, n.id_prolanis_urine) WHERE n.id_prolanis IS NOT NULL AND n.id_permohonan_uji_klinik IS NULL AND YEAR(p.created_at) = ".date('Y')." AND YEAR(n.created_at) = ".date('Y')."
       ) u
       ON
         p.id_permohonan_uji_klinik = u.id_permohonan_uji_klinik
       SET
           p.nourut_permohonan_uji_klinik = u.new_number + u.urutan - 1,
           p.noregister_permohonan_uji_klinik = CONCAT(
                        LPAD(u.new_number + u.urutan - 1, 4, '0'),

                       '/LK/',
                       CASE
                           WHEN MONTH(p.created_at) = 1 THEN 'I'
                           WHEN MONTH(p.created_at) = 2 THEN 'II'
                           WHEN MONTH(p.created_at) = 3 THEN 'III'
                           WHEN MONTH(p.created_at) = 4 THEN 'IV'
                           WHEN MONTH(p.created_at) = 5 THEN 'V'
                           WHEN MONTH(p.created_at) = 6 THEN 'VI'
                           WHEN MONTH(p.created_at) = 7 THEN 'VII'
                           WHEN MONTH(p.created_at) = 8 THEN 'VIII'
                           WHEN MONTH(p.created_at) = 9 THEN 'IX'
                           WHEN MONTH(p.created_at) = 10 THEN 'X'
                           WHEN MONTH(p.created_at) = 11 THEN 'XI'
                           WHEN MONTH(p.created_at) = 12 THEN 'XII'
                       END,
                       '/',
                       YEAR(p.created_at)
                   )

               WHERE YEAR(p.created_at) = ".date('Y').";

     ");

   // UBAH HAJI
   DB::statement("

             UPDATE
           `tb_permohonan_uji_klinik_2` p
       JOIN (
         SELECT p.id_permohonan_uji_klinik, n.new_number, n.id_haji, ROW_NUMBER() OVER (PARTITION BY p.id_permohonan_uji_klinik_haji ORDER BY p.nourut_permohonan_uji_klinik) AS urutan FROM `tb_permohonan_uji_klinik_2` p LEFT JOIN tb_number_klinik n ON p.id_permohonan_uji_klinik_haji = n.id_haji WHERE n.id_haji IS NOT NULL AND n.id_permohonan_uji_klinik IS NULL
       ) u
       ON p.id_permohonan_uji_klinik = u.id_permohonan_uji_klinik
       SET
           p.nourut_permohonan_uji_klinik = u.new_number + u.urutan - 1,
           p.noregister_permohonan_uji_klinik = CONCAT(
                        LPAD(u.new_number + u.urutan - 1, 4, '0'),
                       '/LK/',
                       CASE
                           WHEN MONTH(p.created_at) = 1 THEN 'I'
                           WHEN MONTH(p.created_at) = 2 THEN 'II'
                           WHEN MONTH(p.created_at) = 3 THEN 'III'
                           WHEN MONTH(p.created_at) = 4 THEN 'IV'
                           WHEN MONTH(p.created_at) = 5 THEN 'V'
                           WHEN MONTH(p.created_at) = 6 THEN 'VI'
                           WHEN MONTH(p.created_at) = 7 THEN 'VII'
                           WHEN MONTH(p.created_at) = 8 THEN 'VIII'
                           WHEN MONTH(p.created_at) = 9 THEN 'IX'
                           WHEN MONTH(p.created_at) = 10 THEN 'X'
                           WHEN MONTH(p.created_at) = 11 THEN 'XI'
                           WHEN MONTH(p.created_at) = 12 THEN 'XII'
                       END,
                       '/',
                       YEAR(p.created_at)
                   )
                WHERE YEAR(p.created_at) = ".date('Y')."
                   ;


     ");
  }
}



function sortingNumber($lab_id, $plus_number=0){


  // $data = Sample::orderBy(DB::raw("CAST(SUBSTRING_INDEX(codesample_samples, '/', 1) AS UNSIGNED)"))
  // // ->where
  // ->leftjoin('tb_lab_num', function ($join) {
  //   $join->on('tb_lab_num.sample_id', '=', DB::raw('(SELECT sample_id FROM tb_lab_num WHERE tb_lab_num.sample_id = tb_samples.id_samples AND tb_lab_num.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

  //     // ->limit(1)
  //     ->whereNull('tb_lab_num.deleted_at')
  //     ->whereNull('tb_samples.deleted_at');
  // })
  // ->where('tb_lab_num.lab_id', $lab_id)
  // ->get();

  // $lab_id = 123; // Example lab_id

  DB::statement("
        DELETE tb_lab_num
      FROM tb_lab_num
      JOIN (
          SELECT id_lab_num
          FROM (
              SELECT id_lab_num,
                    ROW_NUMBER() OVER (PARTITION BY sample_id, permohonan_uji_id, deleted_at ORDER BY id_lab_num) AS rn
              FROM tb_lab_num
              WHERE YEAR(created_at) = YEAR(CURDATE())
          ) AS subquery
          WHERE rn > 1
      ) AS duplicates
      ON tb_lab_num.id_lab_num = duplicates.id_lab_num;
  ");


  if (isset($plus_number)) {
    # code...

      $start_num=$plus_number;
      DB::statement("DROP TEMPORARY TABLE IF EXISTS OrderedSamples");


      DB::statement("
            CREATE TEMPORARY TABLE OrderedSamples AS (
                SELECT DISTINCT tb_samples.id_samples,
                      ROW_NUMBER() OVER (ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)) + " . (int)$start_num . " AS row_num,
                      SUBSTRING(tb_samples.codesample_samples, LOCATE('/', tb_samples.codesample_samples)

                      ) AS suffix

                FROM tb_samples
                LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
                WHERE tb_lab_num.lab_id = :lab_id
                  AND tb_lab_num.is_makanan = 0
                  AND tb_lab_num.deleted_at IS NULL
                  AND tb_samples.deleted_at IS NULL
                  AND tb_lab_num.lab_number >= " . (int)$start_num . "
                  AND YEAR(tb_lab_num.created_at) = ".date('Y')."
                  AND YEAR(tb_samples.created_at) = ".date('Y')."

                  AND tb_lab_num.sample_id = (
                      SELECT sample_id FROM tb_lab_num
                      WHERE tb_lab_num.sample_id = tb_samples.id_samples
                        AND tb_lab_num.is_makanan = 0
                        AND tb_lab_num.deleted_at IS NULL
                        AND tb_samples.deleted_at IS NULL

                      LIMIT 1
                  )
            )
        ", ['lab_id' => $lab_id]);

            // Step 2: Update the main table based on the OrderedSamples table
            DB::statement("
                UPDATE tb_samples
                JOIN OrderedSamples ON tb_samples.id_samples = OrderedSamples.id_samples
                SET tb_samples.codesample_samples = CONCAT(LPAD(OrderedSamples.row_num, 4, '0'), OrderedSamples.suffix),
                    tb_samples.count_id = LPAD(OrderedSamples.row_num, 4, '0')
                WHERE YEAR(tb_samples.created_at) = ".date('Y').";

            ");
            DB::statement("DROP TEMPORARY TABLE IF EXISTS OrderedSamples");

            DB::statement("
            CREATE TEMPORARY TABLE OrderedSamples AS (
                SELECT DISTINCT tb_samples.id_samples,
                      ROW_NUMBER() OVER (
                          ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)
                      ) + " . (int)$start_num . " AS row_num,
                      SUBSTRING(tb_samples.codesample_samples, LOCATE('/', tb_samples.codesample_samples)



                      ) AS suffix
                FROM tb_samples
                LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
                WHERE tb_lab_num.lab_id = :lab_id
                  AND tb_lab_num.is_makanan = 0
                  AND tb_lab_num.deleted_at IS NULL
                  AND tb_samples.deleted_at IS NULL
                  AND tb_lab_num.lab_number >= " . (int)$start_num . "
                   AND YEAR(tb_lab_num.created_at) = ".date('Y')."
                  AND YEAR(tb_samples.created_at) = ".date('Y')."


                  AND tb_lab_num.sample_id = (
                      SELECT sample_id FROM tb_lab_num
                      WHERE tb_lab_num.sample_id = tb_samples.id_samples
                        AND tb_lab_num.is_makanan = 0
                        AND tb_lab_num.deleted_at IS NULL
                        AND tb_samples.deleted_at IS NULL


                      LIMIT 1
                  )
            )
        ", ['lab_id' => $lab_id]);
        DB::statement("
            UPDATE tb_lab_num
            JOIN OrderedSamples ON tb_lab_num.sample_id = OrderedSamples.id_samples
            SET tb_lab_num.lab_number = LPAD(OrderedSamples.row_num, 4, '0')
         WHERE YEAR(tb_lab_num.created_at) = ".date('Y').";
            ");


            //   $data=  DB::select("

            //         SELECT
            //       DISTINCT tb_samples.id_samples,
            //         tb_samples.codesample_samples,
            //               ROW_NUMBER() OVER (
            //                   ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)
            //               ) AS row_num
            //         FROM tb_samples
            //         LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
            //         WHERE tb_lab_num.lab_id = :lab_id
            //           AND  tb_lab_num.lab_number >= '". $start_num."'
            //           AND tb_lab_num.deleted_at IS NULL
            //           AND tb_samples.deleted_at IS NULL
            //           AND tb_lab_num.sample_id = (
            //               SELECT sample_id FROM tb_lab_num
            //               WHERE tb_lab_num.sample_id = tb_samples.id_samples
            //                 AND tb_lab_num.deleted_at IS NULL
            //                 AND tb_samples.deleted_at IS NULL
            //               LIMIT 1
            //           )

            // ", ['lab_id' => $lab_id]);

            // dd($data);
  }else{

    $start_num= StartNum::join('ms_laboratorium', function ($join) {
      $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
        ->whereNull('ms_laboratorium.deleted_at')
        ->whereNull('ms_start_number.deleted_at');
    })->where('id_laboratorium',$lab_id)->first();





    if (date('Y')==$start_num->year_start_number) {
      # code...
        $start_num=$start_num->count_start_number;
            // Step 1: Drop the temporary table if it exists
        DB::statement("DROP TEMPORARY TABLE IF EXISTS OrderedSamples");

        // Step 2: Create the OrderedSamples temporary table
        DB::statement("
            CREATE TEMPORARY TABLE OrderedSamples AS (
                SELECT DISTINCT tb_samples.id_samples,
                      ROW_NUMBER() OVER (
                          ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)
                      ) + " . (int)$start_num . " AS row_num,
                      SUBSTRING(tb_samples.codesample_samples, LOCATE('/', tb_samples.codesample_samples)

                      ) AS suffix
                FROM tb_samples
                LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
                WHERE tb_lab_num.lab_id = :lab_id
                  AND tb_lab_num.deleted_at IS NULL
                  AND tb_samples.deleted_at IS NULL
                  AND YEAR(tb_samples.created_at) = ".date('Y')."
                  AND YEAR(tb_lab_num.created_at) = ".date('Y')."
                    AND tb_lab_num.is_makanan = 0
                  AND tb_lab_num.sample_id = (
                      SELECT sample_id FROM tb_lab_num
                      WHERE tb_lab_num.sample_id = tb_samples.id_samples
                        AND tb_lab_num.deleted_at IS NULL
                          AND tb_lab_num.is_makanan = 0

                        AND tb_samples.deleted_at IS NULL
                      LIMIT 1
                  )
            )
        ", ['lab_id' => $lab_id]);



        DB::statement("
            UPDATE tb_samples
            JOIN OrderedSamples ON tb_samples.id_samples = OrderedSamples.id_samples
            SET tb_samples.codesample_samples = CONCAT(LPAD(OrderedSamples.row_num, 4, '0'), OrderedSamples.suffix)
            AND YEAR(tb_samples.created_at) = ".date('Y').";

        ");


        // Step 1: Drop the temporary table if it exists
        DB::statement("DROP TEMPORARY TABLE IF EXISTS OrderedSamples");

        // Step 2: Create the OrderedSamples temporary table
        DB::statement("
            CREATE TEMPORARY TABLE OrderedSamples AS (
                SELECT DISTINCT tb_samples.id_samples,
                      ROW_NUMBER() OVER (
                          ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)
                      ) + " . (int)$start_num . " AS row_num,
                      SUBSTRING(tb_samples.codesample_samples, LOCATE('/', tb_samples.codesample_samples)

                      ) AS suffix
                FROM tb_samples
                LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
                WHERE tb_lab_num.lab_id = :lab_id
                  AND tb_lab_num.deleted_at IS NULL
                  AND tb_samples.deleted_at IS NULL
                    AND YEAR(tb_samples.created_at) = ".date('Y')."
                  AND YEAR(tb_lab_num.created_at) = ".date('Y')."
                    AND tb_lab_num.is_makanan = 0
                  AND tb_lab_num.sample_id = (
                      SELECT sample_id FROM tb_lab_num
                      WHERE tb_lab_num.sample_id = tb_samples.id_samples
                        AND tb_lab_num.is_makanan = 0

                        AND tb_lab_num.deleted_at IS NULL
                        AND tb_samples.deleted_at IS NULL
                      LIMIT 1
                  )
            )
        ", ['lab_id' => $lab_id]);

        // Step 3: Update tb_lab_num using OrderedSamples
        DB::statement("
            UPDATE tb_lab_num
            JOIN OrderedSamples ON tb_lab_num.sample_id = OrderedSamples.id_samples
            SET tb_lab_num.lab_number = LPAD(OrderedSamples.row_num, 4, '0')
                AND YEAR(tb_lab_num.created_at) = ".date('Y').";
        ");
    }else{

      DB::statement("DROP TEMPORARY TABLE IF EXISTS OrderedSamples");

        // Step 2: Create the OrderedSamples temporary table
        DB::statement("
            CREATE TEMPORARY TABLE OrderedSamples AS (
                SELECT DISTINCT tb_samples.id_samples,
                      ROW_NUMBER() OVER (
                          ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)
                      )  AS row_num,
                      SUBSTRING(tb_samples.codesample_samples, LOCATE('/', tb_samples.codesample_samples)

                      ) AS suffix
                FROM tb_samples
                LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
                WHERE tb_lab_num.lab_id = :lab_id
                  AND tb_lab_num.deleted_at IS NULL
                  AND tb_samples.deleted_at IS NULL
                  AND YEAR(tb_samples.created_at) = ".date('Y')."
                  AND YEAR(tb_lab_num.created_at) = ".date('Y')."
                    AND tb_lab_num.is_makanan = 0
                  AND tb_lab_num.sample_id = (
                      SELECT sample_id FROM tb_lab_num
                      WHERE tb_lab_num.sample_id = tb_samples.id_samples
                        AND tb_lab_num.deleted_at IS NULL
                          AND tb_lab_num.is_makanan = 0

                        AND tb_samples.deleted_at IS NULL
                      LIMIT 1
                  )
            )
        ", ['lab_id' => $lab_id]);



        DB::statement("
            UPDATE tb_samples
            JOIN OrderedSamples ON tb_samples.id_samples = OrderedSamples.id_samples
            SET tb_samples.codesample_samples = CONCAT(LPAD(OrderedSamples.row_num, 4, '0'), OrderedSamples.suffix);

        ");


        // Step 1: Drop the temporary table if it exists
        DB::statement("DROP TEMPORARY TABLE IF EXISTS OrderedSamples");

        // Step 2: Create the OrderedSamples temporary table
        DB::statement("
            CREATE TEMPORARY TABLE OrderedSamples AS (
                SELECT DISTINCT tb_samples.id_samples,
                      ROW_NUMBER() OVER (
                          ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)
                      )  AS row_num,
                      SUBSTRING(tb_samples.codesample_samples, LOCATE('/', tb_samples.codesample_samples)

                      ) AS suffix
                FROM tb_samples
                LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
                WHERE tb_lab_num.lab_id = :lab_id
                  AND tb_lab_num.deleted_at IS NULL
                  AND tb_samples.deleted_at IS NULL
                    AND YEAR(tb_samples.created_at) = ".date('Y')."
                  AND YEAR(tb_lab_num.created_at) = ".date('Y')."
                    AND tb_lab_num.is_makanan = 0
                  AND tb_lab_num.sample_id = (
                      SELECT sample_id FROM tb_lab_num
                      WHERE tb_lab_num.sample_id = tb_samples.id_samples
                        AND tb_lab_num.is_makanan = 0

                        AND tb_lab_num.deleted_at IS NULL
                        AND tb_samples.deleted_at IS NULL
                      LIMIT 1
                  )
            )
        ", ['lab_id' => $lab_id]);

        // Step 3: Update tb_lab_num using OrderedSamples
        DB::statement("
            UPDATE tb_lab_num
            JOIN OrderedSamples ON tb_lab_num.sample_id = OrderedSamples.id_samples
            SET tb_lab_num.lab_number = LPAD(OrderedSamples.row_num, 4, '0');
        ");
    }



  }

  $start_num= StartNum::where('code_lab_start_number',"MAK-MIN")->first();

  // dd($start_num->count_start_number);

  $start_num=$start_num->count_start_number;



  // Step 1: Drop the temporary table if it exists
  DB::statement("DROP TEMPORARY TABLE IF EXISTS OrderedSamples");

  // Step 2: Create the OrderedSamples temporary table
  DB::statement("
      CREATE TEMPORARY TABLE OrderedSamples AS (
          SELECT DISTINCT tb_samples.id_samples,
                ROW_NUMBER() OVER (
                    ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)
                ) + " . (int)$start_num . " AS row_num,
                SUBSTRING(tb_samples.codesample_samples, LOCATE('/', tb_samples.codesample_samples)

                ) AS suffix
          FROM tb_samples
          LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
          WHERE tb_lab_num.lab_id = :lab_id
           AND  tb_lab_num.deleted_at IS NULL
            AND tb_samples.deleted_at IS NULL
                 AND YEAR(tb_samples.created_at) = ".date('Y')."
              AND YEAR(tb_lab_num.created_at) = ".date('Y')."
              AND tb_lab_num.is_makanan = 1
            AND tb_lab_num.sample_id = (
                SELECT sample_id FROM tb_lab_num
                WHERE tb_lab_num.sample_id = tb_samples.id_samples
                  AND tb_lab_num.deleted_at IS NULL
                    AND tb_lab_num.is_makanan = 1

                  AND tb_samples.deleted_at IS NULL
                LIMIT 1
            )
      )
  ", ['lab_id' => $lab_id]);



  DB::statement("
      UPDATE tb_samples
      JOIN OrderedSamples ON tb_samples.id_samples = OrderedSamples.id_samples
      SET tb_samples.codesample_samples = CONCAT(LPAD(OrderedSamples.row_num, 4, '0'), OrderedSamples.suffix);
  ");


  // Step 1: Drop the temporary table if it exists
  DB::statement("DROP TEMPORARY TABLE IF EXISTS OrderedSamples");

  // Step 2: Create the OrderedSamples temporary table
  DB::statement("
      CREATE TEMPORARY TABLE OrderedSamples AS (
          SELECT DISTINCT tb_samples.id_samples,
                ROW_NUMBER() OVER (
                    ORDER BY CAST(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 1) AS UNSIGNED)
                ) + " . (int)$start_num . " AS row_num,
                SUBSTRING(tb_samples.codesample_samples, LOCATE('/', tb_samples.codesample_samples)

                ) AS suffix
          FROM tb_samples

          LEFT JOIN tb_lab_num ON tb_lab_num.sample_id = tb_samples.id_samples
          WHERE tb_lab_num.lab_id = :lab_id
           AND tb_lab_num.deleted_at IS NULL
            AND tb_samples.deleted_at IS NULL
              AND YEAR(tb_samples.created_at) = ".date('Y')."
              AND YEAR(tb_lab_num.created_at) = ".date('Y')."
              AND tb_lab_num.is_makanan = 1
            AND tb_lab_num.sample_id = (
                SELECT sample_id FROM tb_lab_num
                WHERE tb_lab_num.sample_id = tb_samples.id_samples
                  AND tb_lab_num.is_makanan = 1
                  AND tb_lab_num.deleted_at IS NULL

                  AND tb_samples.deleted_at IS NULL
                LIMIT 1
            )
      )
   ", ['lab_id' => $lab_id]);


  // Step 3: Update tb_lab_num using OrderedSamples
  DB::statement("
      UPDATE tb_lab_num
      JOIN OrderedSamples ON tb_lab_num.sample_id = OrderedSamples.id_samples
      SET tb_lab_num.lab_number = LPAD(OrderedSamples.row_num, 4, '0');
  ");
}

/**
 * Fungsi untuk mengecek apakah hasil input user berada di luar range baku mutu normal
 * ketika ada multiple baku mutu dengan is_khusus_baku_mutu = 1
 * Mengecek apakah hasil masuk dalam SALAH SATU range dari semua baku mutu yang is_normal = 1
 */
function cek_hasil_multiple_baku_mutu($hasil, $parameter_jenis_klinik_id, $parameter_satuan_klinik_id, $pasien_gender, $pasien_umur)
{
  if (empty($hasil) || $hasil === '-') {
    return false;
  }

  // Cari semua baku mutu khusus untuk parameter ini
  $all_baku_mutu = \Smt\Masterweb\Models\BakuMutu::where('parameter_jenis_klinik_id', $parameter_jenis_klinik_id)
    ->where('parameter_satuan_klinik_id', $parameter_satuan_klinik_id)
    ->where('is_khusus_baku_mutu', '1')
    ->get();

  if ($all_baku_mutu->count() <= 1) {
    return false; // Tidak ada multiple baku mutu
  }

  // Cari semua baku mutu yang is_normal = 1
  $normal_baku_mutu_collection = $all_baku_mutu->where('is_normal', 1);
  
  if ($normal_baku_mutu_collection->count() === 0) {
    return false; // Tidak ada baku mutu normal
  }

  $hasil_numeric = is_numeric($hasil) ? floatval($hasil) : null;
  $is_within_any_normal_range = false;

  // Cek apakah hasil masuk dalam SALAH SATU range normal
  foreach ($normal_baku_mutu_collection as $normal_baku_mutu) {
    $is_within_this_range = false;
    
    if ($hasil_numeric !== null) {
      // Cek range numerik
      if (!empty($normal_baku_mutu->min) && !empty($normal_baku_mutu->max)) {
        $is_within_this_range = ($hasil_numeric >= $normal_baku_mutu->min && $hasil_numeric <= $normal_baku_mutu->max);
      } elseif (!empty($normal_baku_mutu->min)) {
        $is_within_this_range = ($hasil_numeric >= $normal_baku_mutu->min);
      } elseif (!empty($normal_baku_mutu->max)) {
        $is_within_this_range = ($hasil_numeric <= $normal_baku_mutu->max);
      }
    }
    
    // Cek equal value
    if (!empty($normal_baku_mutu->equal)) {
      $is_within_this_range = ($hasil === $normal_baku_mutu->equal);
    }
    
    // Jika masuk dalam salah satu range, maka dianggap normal
    if ($is_within_this_range) {
      $is_within_any_normal_range = true;
      break;
    }
  }

  // Return true jika TIDAK masuk dalam range normal manapun (berarti di luar range)
  return !$is_within_any_normal_range;
}

/**
 * Parse number dari input user (bisa format ID atau EN) ke float untuk database
 * 
 * @param string $value - Nilai angka dari input user
 * @param string $format - Format angka: 'id' = Indonesia, 'en' = International (default: 'en')
 * @return float|null - Nilai float atau null jika invalid
 */
function parseNumberInput($value, $format = 'en')
{
    // IMPORTANT: jangan gunakan empty() di sini karena 0 atau '0' akan dianggap kosong.
    // Perlakukan hanya NULL dan string kosong sebagai "tidak ada nilai".
    if ($value === null) {
        return null;
    }

    // Jika bukan string dan bukan numerik (array, objek, dsb), anggap tidak valid.
    if (!is_string($value) && !is_numeric($value)) {
        return null;
    }

    // Konversi ke string untuk pemrosesan lebih lanjut
    $value = (string) $value;

    // Jika benar‑benar string kosong (setelah trim), return null
    if (trim($value) === '') {
        return null;
    }
    
    // Trim whitespace
    $value = trim($value);
    
    // Remove ALL types of whitespace (space, tab, nbsp, etc.)
    // Match dengan JavaScript: value.replace(/\s+/g, '')
    $value = preg_replace('/\s+/', '', $value);
    
    // Jika sudah berupa number/float (no separators), return langsung
    if (is_numeric($value)) {
        return (float) $value;
    }
    
    if ($format === 'id') {
        // Format Indonesia: 1.234,56 -> 1234.56
        // Step 1: Remove ALL thousands separators (titik)
        $value = str_replace('.', '', $value);
        // Step 2: Replace decimal separator (koma to titik)
        $value = str_replace(',', '.', $value);
        // Step 3: Remove any remaining non-numeric except dot and minus
        $value = preg_replace('/[^\d.-]/', '', $value);
    } else {
        // Format International: 1,234.56 -> 1234.56
        // Step 1: Remove ALL thousands separators (koma)
        $value = str_replace(',', '', $value);
        // Step 2: Remove any remaining non-numeric except dot and minus
        $value = preg_replace('/[^\d.-]/', '', $value);
    }
    
    // Validate hasil parsing
    if (is_numeric($value)) {
        return (float) $value;
    }
    
    return null;
}

/**
 * Format angka dari database (float/string) ke format display sesuai parameter
 * 
 * @param mixed $value - Nilai dari database
 * @param string $format - Format yang diinginkan: 'id' atau 'en' (default: 'en')
 * @param int $decimals - Jumlah angka desimal (default: 2)
 * @return string - String terformat
 */
function formatNumberDisplay($value, $format = 'en', $decimals = 2)
{
    if (empty($value) && $value !== 0 && $value !== '0') {
        return '';
    }
    
    // Parse ke float dulu jika string
    if (is_string($value)) {
        // Coba parse dengan format EN dulu (default database format)
        $floatValue = parseNumberInput($value, 'en');
        if ($floatValue === null) {
            // Jika gagal, kembalikan nilai asli
            return $value;
        }
        $value = $floatValue;
    }
    
    if (!is_numeric($value)) {
        return $value;
    }
    
    if ($format === 'id') {
        // Format Indonesia: 1.234,56
        return number_format((float)$value, $decimals, ',', '.');
    } else {
        // Format International: 1,234.56
        return number_format((float)$value, $decimals, '.', ',');
    }
}

/**
 * Convert nilai dari format user input ke format database (selalu format EN)
 * 
 * @param string $value - Nilai dari input user
 * @param string $inputFormat - Format input dari user: 'id' atau 'en'
 * @return string - String format EN untuk database (contoh: "1234.56")
 */
function convertNumberToDatabase($value, $inputFormat = 'en')
{
    $parsed = parseNumberInput($value, $inputFormat);
    if ($parsed === null) {
        return $value; // Return original jika gagal parse
    }
    
    // Return as string dengan format EN (titik sebagai decimal separator, tanpa thousands separator)
    return number_format($parsed, 10, '.', '');  // 10 decimals to preserve precision
}

/**
 * Deteksi format angka dari string
 * Berguna untuk auto-detect format saat membaca nilai lama
 * 
 * @param string $value
 * @return string - 'id', 'en', atau 'unknown'
 */
function detectNumberFormat($value)
{
    if (empty($value) || !is_string($value)) {
        return 'unknown';
    }
    
    $value = trim($value);
    
    // Cek apakah ada koma dan titik
    $hasComma = strpos($value, ',') !== false;
    $hasDot = strpos($value, '.') !== false;
    
    if (!$hasComma && !$hasDot) {
        return 'unknown'; // Integer atau tidak ada separator
    }
    
    if ($hasComma && !$hasDot) {
        // Hanya ada koma: bisa ID (desimal) atau EN (ribuan)
        // Jika koma di posisi -3 dari belakang atau kelipatan 3, kemungkinan EN (ribuan)
        // Jika tidak, kemungkinan ID (desimal)
        $commaPos = strrpos($value, ',');
        $afterComma = strlen($value) - $commaPos - 1;
        
        if ($afterComma === 3 || $afterComma === 6) {
            return 'en'; // Kemungkinan ribuan
        } else {
            return 'id'; // Kemungkinan desimal
        }
    }
    
    if ($hasDot && !$hasComma) {
        // Hanya ada titik: bisa ID (ribuan) atau EN (desimal)
        $dotPos = strrpos($value, '.');
        $afterDot = strlen($value) - $dotPos - 1;
        
        if ($afterDot === 3 || $afterDot === 6) {
            return 'id'; // Kemungkinan ribuan
        } else {
            return 'en'; // Kemungkinan desimal
        }
    }
    
    if ($hasComma && $hasDot) {
        // Ada keduanya: cek mana yang terakhir
        $commaPos = strrpos($value, ',');
        $dotPos = strrpos($value, '.');
        
        if ($dotPos > $commaPos) {
            // Titik setelah koma: format EN (1,234.56)
            return 'en';
        } else {
            // Koma setelah titik: format ID (1.234,56)
            return 'id';
        }
    }
    
    return 'unknown';
}

?>