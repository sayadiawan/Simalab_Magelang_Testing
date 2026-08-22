<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik - {{ $item_permohonan_uji_klinik->getDisplayNoregister() }}</title>
    <link rel="shortcut icon" href="">
    @php

        // dd(count($value_items));
        // dd($arr_permohonan_parameter);

        $value_items = 0;
        foreach ($arr_permohonan_parameter as $item) {
            # code...

            foreach ($item['item_permohonan_parameter_satuan'] as $key => $parameter) {
                # code...
                $value_items++;
            }
            // dd($item);
        }

        if ($value_items < 3) {
            # code...
            $size_table = 9;
            $padding_table = 5;
        } elseif ($value_items >= 4 && $value_items <= 16) {
            # code...
            $size_table = 9;
        } else {
            $size_table = 8;
        }

        // Fungsi untuk menormalkan simbol operator baku mutu agar bisa dirender oleh DomPDF.
        // Font "dejavu sans" sudah di-bundle di DomPDF dan mendukung karakter ≤ ≥ ≠.
        // Nama font harus lowercase persis sesuai installed-fonts.dist.json: "dejavu sans"
        if (!function_exists('normalizeBakuMutuSymbols')) {
        function normalizeBakuMutuSymbols($text)
        {
            if ($text === null || $text === '') {
                return $text;
            }

            $text = (string) $text;
            $dv = "font-family: 'dejavu sans', sans-serif;";
            $wrap = function ($entity) use ($dv) {
                return '<span style="' . $dv . '">' . $entity . '</span>';
            };

            // Data lama: operator ≥ tersimpan sebagai "?" (encoding mismatch)
            $text = preg_replace_callback(
                '/(^|[\s,(;])\?\s*(?=\d)/u',
                function ($m) use ($wrap) {
                    return $m[1] . $wrap('&#8805;') . ' ';
                },
                $text
            );

            // ASCII dari data lama / input JS
            $text = str_replace('>= ', $wrap('&#8805;') . ' ', $text);
            $text = str_replace('<= ', $wrap('&#8804;') . ' ', $text);

            // Named HTML entities
            $text = str_replace('&ge;', $wrap('&#8805;'), $text);
            $text = str_replace('&le;', $wrap('&#8804;'), $text);
            $text = str_replace('&ne;', $wrap('&#8800;'), $text);

            // Decimal entities
            $text = str_replace('&#8805;', $wrap('&#8805;'), $text);
            $text = str_replace('&#8804;', $wrap('&#8804;'), $text);
            $text = str_replace('&#8800;', $wrap('&#8800;'), $text);

            // Unicode langsung
            $text = str_replace('≥', $wrap('&#8805;'), $text);
            $text = str_replace('≤', $wrap('&#8804;'), $text);
            $text = str_replace('≠', $wrap('&#8800;'), $text);

            return $text;
        }
        }

        if (!function_exists('preparePrintText')) {
        /** Normalisasi hyphen + simbol ≥ ≤ untuk cetak DomPDF (DejaVu Sans). */
        function preparePrintText($text)
        {
            if ($text === null || $text === '') {
                return $text;
            }

            $text = (string) $text;
            $text = str_replace(['‑', '–', '—', '&#8209;'], '-', $text);
            $text = preg_replace('/(\d)\s*-\s*(\d)/', '$1-$2', $text);
            // Escape operator baku mutu "<30" / ">200" saja — jangan sentuh penutup tag HTML
            // (bug lama: ">51" di dalam <strong ...>51 merusak tag sehingga nilai hilang di DomPDF)
            $text = preg_replace('/(?<![A-Za-z0-9"\'\/])<\s*(?=\d)/u', '&#60; ', $text);
            $text = preg_replace('/(?<![A-Za-z0-9"\'\/])>\s*(?=\d)/u', '&#62; ', $text);

            return normalizeBakuMutuSymbols($text);
        }
        }

        if (!function_exists('sanitizePrintHtml')) {
        /** Bersihkan HTML cetak: decode entity, hapus paragraf kosong. */
        function sanitizePrintHtml($html)
        {
            if ($html === null || trim((string) $html) === '') {
                return '';
            }

            $html = (string) $html;
            $prev = null;
            while ($prev !== $html) {
                $prev = $html;
                $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $html = preg_replace('/<p[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/iu', '', $html);
            $html = preg_replace('/<div[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/div>/iu', '', $html);

            return trim($html);
        }
        }

        if (!function_exists('printPlainFooterHtml')) {
        /** Teks footer cetak tanpa tag HTML mentah. */
        function printPlainFooterHtml($html)
        {
            $html = sanitizePrintHtml($html);
            $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));

            return $text === '' ? '' : $text;
        }
        }

        if (!function_exists('decodeBakuMutuCellText')) {
        /** Decode isi sel tabel baku mutu tanpa menghapus operator "< 150". */
        function decodeBakuMutuCellText($cellHtml)
        {
            $text = html_entity_decode((string) $cellHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = str_replace("\xc2\xa0", ' ', $text);
            $placeholder = '___BM_LT_CMP___';
            $text = preg_replace('/<(\d)/', $placeholder . '$1', $text);

            if (preg_match('/<[^>]+>/', $text)) {
                $text = strip_tags($text);
            }

            $text = str_replace($placeholder, '<', $text);

            return trim(preg_replace('/\s+/', ' ', $text));
        }
        }

        if (!function_exists('plainTextNilaiNormal')) {
        /** Ubah nilai normal (termasuk HTML tabel dari DB) menjadi teks polos. */
        function plainTextNilaiNormal($text)
        {
            if ($text === null || $text === '' || $text === '-') {
                return $text ?? '-';
            }

            $text = (string) $text;
            if (preg_match('/<table|<tr|<td|<th|<div/i', $text)) {
                if (preg_match('/<table/i', $text) && preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $text, $rowMatches)) {
                    $lines = [];
                    $pendingBullet = false;

                    foreach ($rowMatches[1] as $rowHtml) {
                        $cells = [];
                        if (preg_match_all('/<t[dh][^>]*>(.*?)<\/t[dh]>/is', $rowHtml, $cellMatches)) {
                            foreach ($cellMatches[1] as $cellHtml) {
                                $cellText = decodeBakuMutuCellText($cellHtml);
                                if ($cellText !== '') {
                                    $cells[] = $cellText;
                                }
                            }
                        } else {
                            $cellText = decodeBakuMutuCellText($rowHtml);
                            if ($cellText !== '') {
                                $cells[] = $cellText;
                            }
                        }

                        if (count($cells) === 0) {
                            continue;
                        }

                        if (count($cells) === 1 && preg_match('/^-+$/', $cells[0])) {
                            $pendingBullet = true;
                            continue;
                        }

                        if (count($cells) === 2 && preg_match('/^[-–—•]\s*$/u', $cells[0])) {
                            $lines[] = '- ' . $cells[1];
                            $pendingBullet = false;
                            continue;
                        }

                        if (count($cells) === 2) {
                            $valueCell = ltrim($cells[1], ': ');
                            $lines[] = $cells[0] . ' : ' . $valueCell;
                            $pendingBullet = false;
                            continue;
                        }

                        $line = implode(' ', $cells);
                        if ($pendingBullet) {
                            $lines[] = '- ' . $line;
                            $pendingBullet = false;
                        } else {
                            $lines[] = $line;
                        }
                    }

                    if (!empty($lines)) {
                        $genderFromLines = buildMassalGenderPairFromLines($lines);
                        if ($genderFromLines !== null) {
                            return $genderFromLines;
                        }
                        if (count($lines) === 1) {
                            $genderTable = buildMassalGenderPairTableHtml($lines[0]);
                            if ($genderTable !== null) {
                                return $genderTable;
                            }
                        }
                        return implode('<br>', $lines);
                    }
                }

                $text = str_replace(['</tr>', '</p>', '</li>', '</div>', '<br>', '<br/>', '<br />'], "\n", $text);
                $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $placeholder = '___BM_LT_CMP___';
                $decoded = preg_replace('/<\s+(\d)/', $placeholder . '$1', $decoded);
                $text = strip_tags($decoded);
                $text = str_replace($placeholder, '< ', $text);
                $text = preg_replace("/[ \t]+/", ' ', $text);
                $text = preg_replace("/\n\s*\n/", "\n", trim($text));

                $rawLines = array_values(array_filter(array_map('trim', explode("\n", $text)), function ($line) {
                    return $line !== '';
                }));

                $lines = [];
                for ($i = 0; $i < count($rawLines); $i++) {
                    if ($rawLines[$i] === '-' && isset($rawLines[$i + 1])) {
                        $lines[] = '- ' . $rawLines[$i + 1];
                        $i++;
                        continue;
                    }
                    if ($rawLines[$i] !== '-') {
                        $lines[] = $rawLines[$i];
                    }
                }

                $text = implode('<br>', $lines);
                return $text;
            }

            $genderTable = buildMassalGenderPairTableHtml($text);
            if ($genderTable !== null) {
                return $genderTable;
            }

            return decodeBakuMutuCellText($text);
        }
        }

        if (!function_exists('formatBakuMutuNumberForPrint')) {
        /** Rapikan angka baku mutu (float 29.9999 → 30). */
        function formatBakuMutuNumberForPrint($n): string
        {
            if ($n === null || $n === '') {
                return '';
            }
            if (!is_numeric($n)) {
                return trim((string) $n);
            }

            $f = (float) $n;
            if (abs($f - round($f)) < 0.00015) {
                return (string) (int) round($f);
            }

            $s = rtrim(rtrim(sprintf('%.4F', $f), '0'), '.');

            return $s === '' ? '0' : $s;
        }
        }

        if (!function_exists('formatBakuMutuTierLine')) {
        function formatBakuMutuTierLine(array $bm): ?string
        {
            $rangePart = null;

            // Utamakan teks nilai (mis. "<30") agar tidak jadi float 29.9999 + simbol rusak
            $nilaiRaw = $bm['nilai_baku_mutu'] ?? null;
            if ($nilaiRaw !== null && $nilaiRaw !== '') {
                $nilai = trim(strip_tags(html_entity_decode((string) $nilaiRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                $nilai = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $nilai);
                $nilai = trim(preg_replace('/\s+/u', ' ', $nilai));
                if ($nilai !== '' && $nilai !== '-' && preg_match('/^[<>≤≥=!]=?\s*-?\d/u', $nilai)) {
                    $nilai = preg_replace('/^<=\s*/u', '&#8804; ', $nilai);
                    $nilai = preg_replace('/^>=\s*/u', '&#8805; ', $nilai);
                    $nilai = preg_replace('/^≤\s*/u', '&#8804; ', $nilai);
                    $nilai = preg_replace('/^≥\s*/u', '&#8805; ', $nilai);
                    $nilai = preg_replace('/^<\s*/u', '&#60; ', $nilai);
                    $nilai = preg_replace('/^>\s*/u', '&#62; ', $nilai);
                    $rangePart = $nilai;
                }
            }

            if ($rangePart === null) {
                $hasMin = isset($bm['min']) && $bm['min'] !== '' && $bm['min'] !== null;
                $hasMax = isset($bm['max']) && $bm['max'] !== '' && $bm['max'] !== null;

                if ($hasMin && $hasMax) {
                    $rangePart = formatBakuMutuNumberForPrint($bm['min']) . ' - ' . formatBakuMutuNumberForPrint($bm['max']);
                } elseif ($hasMin) {
                    $rangePart = '&#8805; ' . formatBakuMutuNumberForPrint($bm['min']);
                } elseif ($hasMax) {
                    if (!empty($bm['is_normal']) && (int) $bm['is_normal'] === 1) {
                        $rangePart = '&#60; ' . formatBakuMutuNumberForPrint($bm['max']);
                    } else {
                        $rangePart = '&#8804; ' . formatBakuMutuNumberForPrint($bm['max']);
                    }
                } elseif (!empty($bm['equal'])) {
                    $rangePart = '= ' . $bm['equal'];
                } elseif (!empty($bm['nilai_baku_mutu'])) {
                    $rangePart = plainTextNilaiNormal($bm['nilai_baku_mutu']);
                }
            }

            if ($rangePart === null || $rangePart === '' || $rangePart === '-') {
                return null;
            }

            $kesimpulan = trim(strip_tags($bm['kesimpulan_baku_mutu'] ?? ''));
            if ($kesimpulan !== '') {
                return normalizeBakuMutuSymbols($kesimpulan . ' : ' . $rangePart);
            }

            return normalizeBakuMutuSymbols($rangePart);
        }
        }

        if (!function_exists('formatBakuMutuTierValueOnly')) {
        function formatBakuMutuTierValueOnly(array $bm): ?string
        {
            $line = formatBakuMutuTierLine($bm);
            if ($line === null || $line === '') {
                return null;
            }
            $kesimpulan = trim(strip_tags($bm['kesimpulan_baku_mutu'] ?? ''));
            if ($kesimpulan !== '' && strpos($line, $kesimpulan . ' : ') === 0) {
                return substr($line, strlen($kesimpulan) + 3);
            }

            return $line;
        }
        }

        if (!function_exists('splitLabelValueHtmlLine')) {
        /** Pisah "Label : nilai" dari satu baris HTML/teks. */
        function splitLabelValueHtmlLine($html): array
        {
            $html = trim((string) $html);
            if ($html === '' || !preg_match('/^(.+?)\s*:\s*(.+)$/us', $html, $m)) {
                return [null, null];
            }

            return [trim($m[1]), trim($m[2])];
        }
        }

        if (!function_exists('isGenderPairLabel')) {
        function isGenderPairLabel($label): bool
        {
            return (bool) preg_match('/^[LP]$/iu', trim(strip_tags((string) $label)));
        }
        }

        if (!function_exists('parseMassalGenderPairHtml')) {
        /** Parse teks/HTML "L : ... P : ..." menjadi [nilaiL, nilaiP]. */
        function parseMassalGenderPairHtml($html): ?array
        {
            $html = trim((string) $html);
            if ($html === '') {
                return null;
            }

            $plain = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $plain = trim(str_replace("\xc2\xa0", ' ', $plain));

            $patterns = [
                '/^L\s*:\s*(.+?)\s+P\s*:\s*(.+)$/iu',
                '/^L\s+(.+?)\s+P\s+(.+)$/iu',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $plain, $m)) {
                    return [trim($m[1]), trim($m[2])];
                }
            }

            if (preg_match('/^L\s*:\s*(.+?)\s+P\s*:\s*(.+)$/ius', $html, $m)) {
                return [trim($m[1]), trim($m[2])];
            }

            return null;
        }
        }

        if (!function_exists('extractGenderTableWidthPercent')) {
        /** Ambil lebar tabel (%) dari HTML TinyMCE bila ada. */
        function extractGenderTableWidthPercent($html): ?int
        {
            $html = (string) $html;
            if (preg_match('/<table[^>]*\bstyle=["\'][^"\']*width\s*:\s*(\d+(?:\.\d+)?)%/i', $html, $m)) {
                return min(100, max(40, (int) round((float) $m[1])));
            }
            if (preg_match('/<table[^>]*\bwidth\s*=\s*["\']?(\d+)%?/i', $html, $m)) {
                $w = (int) $m[1];

                return ($w > 0 && $w <= 100) ? $w : null;
            }

            return null;
        }
        }

        if (!function_exists('openGenderPairTableHtml')) {
        function openGenderPairTableHtml(?int $widthPercent = null): string
        {
            if ($widthPercent !== null && $widthPercent > 0) {
                $widthCss = 'width:' . $widthPercent . '%;max-width:' . $widthPercent . '%;';
            } else {
                $widthCss = 'width:auto;max-width:72%;';
            }

            $tableStyle = 'border:none;border-collapse:collapse;margin:0 auto;font-size:inherit;'
                . 'table-layout:auto;text-align:left;' . $widthCss;

            return '<div class="bm-gender-pair-wrap">'
                . '<table class="bm-label-value-table bm-nilai-normal-compact bm-gender-pair-table" style="' . $tableStyle . '">'
                . '<tbody>';
        }
        }

        if (!function_exists('closeGenderPairTableHtml')) {
        function closeGenderPairTableHtml(): string
        {
            return '</tbody></table></div>';
        }
        }

        if (!function_exists('normalizeGenderPairCellHtml')) {
        function normalizeGenderPairCellHtml($cell): string
        {
            $cell = trim((string) $cell);

            return preg_replace('/^([LP])\s*:\s*/iu', '$1 : ', $cell);
        }
        }

        if (!function_exists('buildGenderPairRowFromCellsHtml')) {
        /** Satu baris 2 sel — sama seperti tabel TinyMCE (L : ... | P : ...). */
        function buildGenderPairRowFromCellsHtml($leftCell, $rightCell): string
        {
            $cellStyle = 'border:none;padding:1px 4px;vertical-align:top;text-align:left;white-space:nowrap;';

            return '<tr>'
                . '<td class="bm-gender-pair-cell bm-gender-pair-left" style="' . $cellStyle . 'padding-right:10px;">'
                . normalizeGenderPairCellHtml($leftCell)
                . '</td>'
                . '<td class="bm-gender-pair-cell bm-gender-pair-right" style="' . $cellStyle . '">'
                . normalizeGenderPairCellHtml($rightCell)
                . '</td>'
                . '</tr>';
        }
        }

        if (!function_exists('buildGenderLabelValueRowHtml')) {
        /** Satu gender saja (fallback). */
        function buildGenderLabelValueRowHtml($label, $value): string
        {
            $cellStyle = 'border:none;padding:1px 4px;vertical-align:top;text-align:left;white-space:nowrap;';

            return '<tr><td colspan="2" class="bm-gender-pair-cell" style="' . $cellStyle . '">'
                . htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') . ' : ' . $value
                . '</td></tr>';
        }
        }

        if (!function_exists('buildGenderPairValuesRowHtml')) {
        /** Horizontal: L : nilai  |  P : nilai — format sel TinyMCE. */
        function buildGenderPairValuesRowHtml($lValue, $pValue): string
        {
            return buildGenderPairRowFromCellsHtml('L : ' . $lValue, 'P : ' . $pValue);
        }
        }

        if (!function_exists('buildMassalGenderPairTableHtml')) {
        function buildMassalGenderPairTableHtml($text, ?int $widthPercent = null): ?string
        {
            $pairs = parseMassalGenderPairHtml($text);
            if ($pairs === null) {
                return null;
            }

            return openGenderPairTableHtml($widthPercent)
                . buildGenderPairValuesRowHtml($pairs[0], $pairs[1])
                . closeGenderPairTableHtml();
        }
        }

        if (!function_exists('buildMassalGenderPairFromLines')) {
        /** Dua baris terpisah "L : ..." dan "P : ..." → tabel gender. */
        function buildMassalGenderPairFromLines(array $lines): ?string
        {
            if (count($lines) !== 2) {
                return null;
            }

            [$l1, $v1] = splitLabelValueHtmlLine($lines[0]);
            [$l2, $v2] = splitLabelValueHtmlLine($lines[1]);
            if (!isGenderPairLabel($l1) || !isGenderPairLabel($l2)) {
                return null;
            }

            return openGenderPairTableHtml()
                . buildGenderPairValuesRowHtml($v1, $v2)
                . closeGenderPairTableHtml();
        }
        }

        if (!function_exists('tryReformatTinyMceGenderPairTable')) {
        /** Tabel TinyMCE 1 baris 2 sel (L|P) → cetak rapi dengan lebar mengikuti editor. */
        function tryReformatTinyMceGenderPairTable($html): ?string
        {
            if ($html === null || stripos((string) $html, '<table') === false) {
                return null;
            }

            if (!preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', (string) $html, $rowMatches)) {
                return null;
            }

            $widthPercent = extractGenderTableWidthPercent($html);
            $rows = '';

            foreach ($rowMatches[1] as $rowHtml) {
                $cells = [];
                if (preg_match_all('/<t[dh][^>]*>(.*?)<\/t[dh]>/is', $rowHtml, $cellMatches)) {
                    foreach ($cellMatches[1] as $cellHtml) {
                        $cells[] = trim((string) $cellHtml);
                    }
                }

                if (count($cells) !== 2 || !isSideBySideCompleteValueRow($cells)) {
                    return null;
                }

                [$sideL1] = splitLabelValueHtmlLine($cells[0]);
                [$sideL2] = splitLabelValueHtmlLine($cells[1]);
                if (!isGenderPairLabel($sideL1) || !isGenderPairLabel($sideL2)) {
                    return null;
                }

                $rows .= buildGenderPairRowFromCellsHtml($cells[0], $cells[1]);
            }

            if ($rows === '') {
                return null;
            }

            return openGenderPairTableHtml($widthPercent) . $rows . closeGenderPairTableHtml();
        }
        }

        if (!function_exists('buildAlignedNilaiNormalRowHtml')) {
        /** Baris label : nilai — semua rata kiri, titik dua di kolom tengah lurus vertikal. */
        function buildAlignedNilaiNormalRowHtml($label, $value): string
        {
            $label = trim((string) $label);
            $value = trim((string) $value);
            $labelStyle = 'border:none;padding:0;padding-right:1px;vertical-align:top;text-align:left;white-space:normal;';
            $colonStyle = 'border:none;padding:0;padding-right:2px;vertical-align:top;text-align:left;white-space:nowrap;width:1%;';
            $valueStyle = 'border:none;padding:0;vertical-align:top;text-align:left;white-space:normal;';

            if ($label === '') {
                return '';
            }

            if ($value === '') {
                return '<tr><td colspan="3" class="bm-nilai-normal-row" style="' . $labelStyle . '">'
                    . $label . '</td></tr>';
            }

            return '<tr>'
                . '<td class="bm-label-col" style="' . $labelStyle . '">' . $label . '</td>'
                . '<td class="bm-colon-col" style="' . $colonStyle . '">:</td>'
                . '<td class="bm-value-col" style="' . $valueStyle . '">' . $value . '</td>'
                . '</tr>';
        }
        }

        if (!function_exists('buildAlignedSideBySideRowHtml')) {
        /** Baris ganda L/P dalam satu baris, masing-masing label : nilai rata kiri. */
        function buildAlignedSideBySideRowHtml($cell1, $cell2): string
        {
            [$l1, $v1] = splitLabelValueHtmlLine($cell1);
            [$l2, $v2] = splitLabelValueHtmlLine($cell2);
            $labelStyle = 'border:none;padding:0;padding-right:1px;vertical-align:top;text-align:left;white-space:normal;';
            $colonStyle = 'border:none;padding:0;padding-right:2px;vertical-align:top;text-align:left;white-space:nowrap;width:1%;';
            $valueStyle = 'border:none;padding:0;vertical-align:top;text-align:left;white-space:normal;';
            $gapStyle = 'border:none;padding:0;padding-left:6px;padding-right:1px;vertical-align:top;text-align:left;white-space:normal;';

            if ($l1 === null || $l2 === null) {
                return buildAlignedNilaiNormalRowHtml($cell1, '') !== ''
                    ? buildAlignedNilaiNormalRowHtml($cell1, '') . buildAlignedNilaiNormalRowHtml($cell2, '')
                    : '<tr><td colspan="3" class="bm-nilai-normal-row" style="' . $labelStyle . '">'
                        . $cell1 . ' &nbsp; ' . $cell2 . '</td></tr>';
            }

            if (isGenderPairLabel($l1) && isGenderPairLabel($l2)) {
                return buildGenderPairValuesRowHtml($v1, $v2);
            }

            return '<tr>'
                . '<td class="bm-label-col" style="' . $labelStyle . '">' . $l1 . '</td>'
                . '<td class="bm-colon-col" style="' . $colonStyle . '">:</td>'
                . '<td class="bm-value-col" style="' . $valueStyle . '">' . $v1 . '</td>'
                . '<td class="bm-label-col" style="' . $gapStyle . '">' . $l2 . '</td>'
                . '<td class="bm-colon-col" style="' . $colonStyle . '">:</td>'
                . '<td class="bm-value-col" style="' . $valueStyle . '">' . $v2 . '</td>'
                . '</tr>';
        }
        }

        if (!function_exists('openNilaiNormalCompactTableHtml')) {
        function openNilaiNormalCompactTableHtml(int $colCount = 3): string
        {
            $tableStyle = 'border:none;border-collapse:collapse;width:100%;margin:0;font-size:inherit;'
                . 'table-layout:fixed;text-align:left;';
            $colgroup = $colCount === 6
                ? '<colgroup>'
                    . '<col class="bm-col-label" style="width:16%">'
                    . '<col class="bm-col-colon" style="width:2%">'
                    . '<col class="bm-col-value" style="width:32%">'
                    . '<col class="bm-col-label" style="width:16%">'
                    . '<col class="bm-col-colon" style="width:2%">'
                    . '<col class="bm-col-value" style="width:32%">'
                    . '</colgroup>'
                : '<colgroup>'
                    . '<col class="bm-col-label" style="width:42%">'
                    . '<col class="bm-col-colon" style="width:3%">'
                    . '<col class="bm-col-value" style="width:55%">'
                    . '</colgroup>';

            return '<table class="bm-label-value-table bm-nilai-normal-compact" style="' . $tableStyle . '">'
                . $colgroup . '<tbody>';
        }
        }

        if (!function_exists('buildClassificationBakuMutuTableHtml')) {
        function buildClassificationBakuMutuTableHtml(array $multiple): string
        {
            $sorted = $multiple;
            usort($sorted, function ($a, $b) {
                $sortKey = function ($bm) {
                    if (isset($bm['min']) && $bm['min'] !== '' && $bm['min'] !== null) {
                        return (float) $bm['min'];
                    }
                    if (isset($bm['max']) && $bm['max'] !== '' && $bm['max'] !== null) {
                        return (float) $bm['max'];
                    }
                    return 999999;
                };
                return $sortKey($a) <=> $sortKey($b);
            });

            $rows = '';
            foreach ($sorted as $bm) {
                $label = trim(strip_tags($bm['kesimpulan_baku_mutu'] ?? ''));
                $value = formatBakuMutuTierValueOnly($bm);
                if ($label === '' || $value === null || $value === '') {
                    continue;
                }
                $rows .= buildAlignedNilaiNormalRowHtml(
                    htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
                    $value
                );
            }

            if ($rows === '') {
                return '';
            }

            return openNilaiNormalCompactTableHtml(3) . $rows . '</tbody></table>';
        }
        }

        if (!function_exists('isClassificationBakuMutuSet')) {
        /**
         * Klasifikasi bertingkat (Trigliserida, LDL): tier tanpa gender/umur.
         * Demografi (Ureum L/P): tiap tier punya gender atau range umur.
         */
        function isClassificationBakuMutuSet(array $multiple): bool
        {
            if (count($multiple) <= 1) {
                return false;
            }

            foreach ($multiple as $bm) {
                $hasGender = !empty($bm['gender_baku_mutu']);
                $hasUmur = isset($bm['minimal_umur_baku_mutu'], $bm['maksimal_umur_baku_mutu'])
                    && $bm['minimal_umur_baku_mutu'] !== '' && $bm['maksimal_umur_baku_mutu'] !== '';
                if (!$hasGender && !$hasUmur) {
                    return true;
                }
            }

            return false;
        }
        }

        if (!function_exists('shouldShowAllBakuMutuTiers')) {
        /**
         * Trigliserida: ada tier non-normal (batas tinggi, tinggi, sangat tinggi) → tampilkan semua.
         * Ureum/demografi / LED gender: baris beda L/P atau umur → jangan dump semua tanpa label.
         */
        function shouldShowAllBakuMutuTiers(array $multiple): bool
        {
            if (count($multiple) <= 1) {
                return false;
            }

            if (isClassificationBakuMutuSet($multiple)) {
                return true;
            }

            // Pasangan gender / rentang umur: bukan klasifikasi bertingkat
            $allDemographic = true;
            foreach ($multiple as $bm) {
                $hasGender = !empty($bm['gender_baku_mutu']);
                $hasUmur = isset($bm['minimal_umur_baku_mutu'], $bm['maksimal_umur_baku_mutu'])
                    && $bm['minimal_umur_baku_mutu'] !== ''
                    && $bm['maksimal_umur_baku_mutu'] !== '';
                if (!$hasGender && !$hasUmur) {
                    $allDemographic = false;
                    break;
                }
            }
            if ($allDemographic) {
                return false;
            }

            foreach ($multiple as $bm) {
                if (!isset($bm['is_normal']) || (int) $bm['is_normal'] !== 1) {
                    return true;
                }
            }

            return false;
        }
        }

        if (!function_exists('buildGenderPairFromMultipleBakuMutu')) {
        /** Bangun tabel L/P dari multiple baku mutu gender (mis. Laju Endap Darah). */
        function buildGenderPairFromMultipleBakuMutu(array $multiple): ?string
        {
            $byGender = [];
            foreach ($multiple as $bm) {
                $g = $bm['gender_baku_mutu'] ?? null;
                if ($g !== 'L' && $g !== 'P') {
                    continue;
                }
                $val = formatBakuMutuTierValueOnly($bm);
                if ($val === null || $val === '' || $val === '-') {
                    continue;
                }
                $byGender[$g] = $val;
            }

            if (!isset($byGender['L'], $byGender['P'])) {
                return null;
            }

            return openGenderPairTableHtml()
                . buildGenderPairValuesRowHtml($byGender['L'], $byGender['P'])
                . closeGenderPairTableHtml();
        }
        }

        if (!function_exists('findBakuMutuTableHtml')) {
        function findBakuMutuTableHtml($item_satuan_klinik)
        {
            $candidates = [];
            foreach (['nilai_baku_mutu', 'ket_default_parameter_satuan_klinik'] as $key) {
                if (!empty($item_satuan_klinik[$key]) && is_string($item_satuan_klinik[$key])) {
                    $candidates[] = $item_satuan_klinik[$key];
                }
            }
            if (!empty($item_satuan_klinik['multiple_baku_mutu']) && is_array($item_satuan_klinik['multiple_baku_mutu'])) {
                foreach ($item_satuan_klinik['multiple_baku_mutu'] as $bm) {
                    if (!empty($bm['nilai_baku_mutu']) && is_string($bm['nilai_baku_mutu'])) {
                        $candidates[] = $bm['nilai_baku_mutu'];
                    }
                }
            }
            foreach ($candidates as $candidate) {
                $decoded = decodeNilaiBakuMutuValue($candidate);
                if (stripos($decoded, '<table') !== false) {
                    return $decoded;
                }
            }

            return null;
        }
        }

        if (!function_exists('isBulletListBakuMutuTable')) {
        /** Tabel baku mutu berbentuk daftar (kolom "-" + teks panjang), bukan klasifikasi label:nilai. */
        function isBulletListBakuMutuTable($html): bool
        {
            if ($html === null || $html === '' || stripos($html, '<table') === false) {
                return false;
            }

            if (!preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rowMatches)) {
                return false;
            }

            $bulletRows = 0;
            $totalRows = 0;

            foreach ($rowMatches[1] as $rowHtml) {
                if (!preg_match_all('/<t[dh][^>]*>(.*?)<\/t[dh]>/is', $rowHtml, $cellMatches) || count($cellMatches[1]) < 2) {
                    continue;
                }

                $totalRows++;
                $first = trim(preg_replace(
                    '/\s+/',
                    ' ',
                    strip_tags(html_entity_decode($cellMatches[1][0], ENT_QUOTES | ENT_HTML5, 'UTF-8'))
                ));

                if ($first === '' || preg_match('/^[-–—•]\s*$/u', $first)) {
                    $bulletRows++;
                }
            }

            return $totalRows > 0 && $bulletRows >= (int) ceil($totalRows * 0.6);
        }
        }

        if (!function_exists('protectBakuMutuPhrasesForPrint')) {
        /** Lindungi satuan/pola umum dari pemisahan kata di tengah (DomPDF). */
        function protectBakuMutuPhrasesForPrint($text)
        {
            if ($text === null || $text === '') {
                return $text;
            }

            $text = (string) $text;
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = preg_replace('/1\.73m(?:&sup2;|<sup>2<\/sup>|²)/iu', '1.73m²', $text);
            $text = preg_replace('/nowrap;\s*">/iu', '', $text);
            $text = preg_replace('/<span[^>]*white-space\s*:\s*nowrap[^>]*>/iu', '', $text);
            $text = preg_replace('/<\/span>/iu', '', $text);

            $phrases = [
                'mL/menit/1.73m²',
                'mg/dL',
                'mg/dl',
                'U/L',
            ];

            foreach ($phrases as $phrase) {
                if (strpos($text, $phrase) === false) {
                    continue;
                }

                $safePhrase = htmlspecialchars($phrase, ENT_QUOTES, 'UTF-8');
                $text = str_replace(
                    $phrase,
                    '<span class="bm-nowrap-unit">' . $safePhrase . '</span>',
                    $text
                );
            }

            return $text;
        }
        }

        if (!function_exists('wrapNilaiNormalPrintContent')) {
        /** Bungkus konten nilai normal ber-tabel (e-GFR, L/P, klasifikasi). */
        function wrapNilaiNormalPrintContent($html)
        {
            if ($html === null || $html === '' || $html === '-') {
                return $html ?? '-';
            }

            return '<div class="nilai-normal-print-wrap">' . $html . '</div>';
        }
        }

        if (!function_exists('isStructuredNilaiNormalHtml')) {
        /** Nilai normal ber-tabel / multi-baris kompleks — tetap rata kiri. */
        function isStructuredNilaiNormalHtml($html): bool
        {
            $html = (string) $html;

            return stripos($html, '<table') !== false
                || stripos($html, 'bm-bullet-list') !== false
                || stripos($html, 'bm-gender-pair') !== false
                || stripos($html, 'bm-label-value-table') !== false
                || stripos($html, 'bm-nilai-normal-compact') !== false
                || stripos($html, 'nilai-normal-print-wrap') !== false;
        }
        }

        if (!function_exists('isPlainNilaiNormalForPrint')) {
        /** Nilai normal teks biasa — rata tengah horizontal & vertikal di sel. */
        function isPlainNilaiNormalForPrint($itemOrHtml, $pasienGender = null): bool
        {
            if (is_array($itemOrHtml)) {
                $resolved = resolveNilaiNormalKlinik($itemOrHtml, $pasienGender);
            } else {
                $resolved = (string) $itemOrHtml;
            }

            return !isStructuredNilaiNormalHtml($resolved);
        }
        }

        if (!function_exists('wrapNilaiNormalPlainCenter')) {
        /** Nilai normal teks biasa tanpa tabel — rata tengah. */
        function wrapNilaiNormalPlainCenter($html)
        {
            if ($html === null || $html === '' || $html === '-') {
                return $html ?? '-';
            }

            // Jangan double-wrap HTML terstruktur (sudah punya span/table)
            if (stripos((string) $html, '<table') !== false || stripos((string) $html, 'bm-') !== false) {
                return '<div class="nilai-normal-plain-center">' . $html . '</div>';
            }

            return '<div class="nilai-normal-plain-center">' . preparePrintText($html) . '</div>';
        }
        }

        if (!function_exists('cleanBulletListCellHtml')) {
        function cleanBulletListCellHtml($cellHtml): string
        {
            $text = html_entity_decode((string) $cellHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = str_replace("\xc2\xa0", ' ', $text);
            $text = preg_replace('/nowrap;\s*">/iu', '', $text);
            $text = preg_replace('/<span[^>]*white-space\s*:\s*nowrap[^>]*>/iu', '', $text);
            $text = preg_replace('/<\/?span[^>]*>/iu', '', $text);
            $placeholder = '___BM_LT_CMP___';
            $text = preg_replace('/<(\d)/', $placeholder . '$1', $text);
            $text = strip_tags($text);
            $text = str_replace($placeholder, '<', $text);
            $text = trim(preg_replace('/\s+/u', ' ', $text));

            return protectBakuMutuPhrasesForPrint($text);
        }
        }

        if (!function_exists('openBulletListTableHtml')) {
        function openBulletListTableHtml(?int $widthPercent = null): string
        {
            if ($widthPercent !== null && $widthPercent > 0) {
                $widthCss = 'width:' . $widthPercent . '%;max-width:100%;';
            } else {
                $widthCss = 'width:100%;max-width:100%;';
            }

            return '<div class="bm-bullet-list-wrap">'
                . '<table class="bm-bullet-list-table" style="border:none;border-collapse:collapse;'
                . $widthCss . 'table-layout:auto;margin:0;text-align:left;font-size:inherit;">'
                . '<colgroup><col style="width:5pt"><col style="width:auto"></colgroup>'
                . '<tbody>';
        }
        }

        if (!function_exists('closeBulletListTableHtml')) {
        function closeBulletListTableHtml(): string
        {
            return '</tbody></table></div>';
        }
        }

        if (!function_exists('buildBulletListRowHtml')) {
        /** Satu baris: kolom "-" sempit + teks penuh (seperti TinyMCE). */
        function buildBulletListRowHtml($bullet, $content): string
        {
            $markStyle = 'border:none;padding:0 2px 0 0;vertical-align:top;text-align:left;white-space:nowrap;width:5pt;';
            $textStyle = 'border:none;padding:0;vertical-align:top;text-align:left;white-space:normal;word-wrap:break-word;overflow-wrap:break-word;width:auto;';

            return '<tr>'
                . '<td class="bm-bullet-mark" style="' . $markStyle . '">' . htmlspecialchars((string) $bullet, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td class="bm-bullet-text" style="' . $textStyle . '">' . $content . '</td>'
                . '</tr>';
        }
        }

        if (!function_exists('formatBulletListLineForPrint')) {
        /** Satu baris daftar e-GFR: "- ≥ 90 : keterangan panjang" */
        function formatBulletListLineForPrint($line): string
        {
            $line = trim((string) $line);
            if ($line === '') {
                return '';
            }

            $plain = html_entity_decode(strip_tags($line), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $plain = preg_replace('/nowrap;\s*">/iu', '', $plain);
            $plain = trim(preg_replace('/\s+/u', ' ', $plain));

            if (preg_match('/^-\s*(.+?)\s*:\s*(.+)$/us', $plain, $m)) {
                $range = trim($m[1]);
                $desc = trim($m[2]);

                return '<div class="bm-bullet-line">'
                    . '<span class="bm-bullet-range">- ' . htmlspecialchars($range, ENT_QUOTES, 'UTF-8') . ' :</span> '
                    . '<span class="bm-bullet-desc">' . protectBakuMutuPhrasesForPrint($desc) . '</span>'
                    . '</div>';
            }

            return '<div class="bm-bullet-line">' . protectBakuMutuPhrasesForPrint($plain) . '</div>';
        }
        }

        if (!function_exists('formatBulletListBakuMutuForPrint')) {
        /** Tabel daftar (e-GFR): pertahankan layout 2 kolom TinyMCE (- | teks penuh). */
        function formatBulletListBakuMutuForPrint($html)
        {
            $html = (string) $html;
            if (stripos($html, '<table') !== false && preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rowMatches)) {
                $widthPercent = extractGenderTableWidthPercent($html);
                $rows = '';
                $hasRows = false;

                foreach ($rowMatches[1] as $rowHtml) {
                    $cells = [];
                    if (preg_match_all('/<t[dh][^>]*>(.*?)<\/t[dh]>/is', $rowHtml, $cellMatches)) {
                        foreach ($cellMatches[1] as $cellHtml) {
                            $cells[] = trim((string) $cellHtml);
                        }
                    }

                    if (count($cells) === 0) {
                        continue;
                    }

                    if (count($cells) >= 2) {
                        $bulletPlain = trim(strip_tags(html_entity_decode($cells[0], ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                        $bullet = ($bulletPlain === '' || preg_match('/^[-–—•]\s*$/u', $bulletPlain))
                            ? '-'
                            : $bulletPlain;
                        $rows .= buildBulletListRowHtml($bullet, cleanBulletListCellHtml($cells[1]));
                        $hasRows = true;
                        continue;
                    }

                    $line = cleanBulletListCellHtml($cells[0]);
                    if (preg_match('/^-\s*(.+)$/us', html_entity_decode(strip_tags($line), ENT_QUOTES | ENT_HTML5, 'UTF-8'), $m)) {
                        $rows .= buildBulletListRowHtml('-', cleanBulletListCellHtml($m[1]));
                    } else {
                        $rows .= buildBulletListRowHtml('-', $line);
                    }
                    $hasRows = true;
                }

                if ($hasRows) {
                    return normalizeBakuMutuSymbols(
                        openBulletListTableHtml($widthPercent) . $rows . closeBulletListTableHtml()
                    );
                }
            }

            $plain = plainTextNilaiNormal($html);
            if ($plain === null || $plain === '' || $plain === '-') {
                return '-';
            }

            $lines = preg_split('/<br\s*\/?>/i', $plain);
            $rows = '';

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $plainLine = html_entity_decode(strip_tags($line), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (preg_match('/^-\s*(.+)$/us', $plainLine, $m)) {
                    $rows .= buildBulletListRowHtml('-', cleanBulletListCellHtml($m[1]));
                } else {
                    $rows .= buildBulletListRowHtml('-', cleanBulletListCellHtml($plainLine));
                }
            }

            if ($rows === '') {
                return '-';
            }

            return normalizeBakuMutuSymbols(
                openBulletListTableHtml() . $rows . closeBulletListTableHtml()
            );
        }
        }

        if (!function_exists('bakuMutuCellContainsColon')) {
        function bakuMutuCellContainsColon($cell): bool
        {
            $decoded = html_entity_decode((string) $cell, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return strpos($decoded, ':') !== false;
        }
        }

        if (!function_exists('isGenderQuadCellRow')) {
        /** Baris 4 sel: L | : nilai | P | : nilai */
        function isGenderQuadCellRow(array $cells): bool
        {
            if (count($cells) !== 4) {
                return false;
            }

            return isGenderPairLabel($cells[0]) && isGenderPairLabel($cells[2]);
        }
        }

        if (!function_exists('extractGenderQuadCellValues')) {
        function extractGenderQuadCellValues(array $cells): array
        {
            $lValue = preg_replace('/^:&nbsp;?/i', '', (string) $cells[1]);
            $lValue = preg_replace('/^:\s*/u', '', $lValue);
            $pValue = preg_replace('/^:&nbsp;?/i', '', (string) $cells[3]);
            $pValue = preg_replace('/^:\s*/u', '', $pValue);

            return [trim($lValue), trim($pValue)];
        }
        }

        if (!function_exists('isSideBySideCompleteValueRow')) {
        /**
         * Baris 2 sel yang masing-masing sudah lengkap (mis. SGPT: L : ≤ 45 | P : < 34).
         * Jangan ubah ke 3 kolom agar tidak muncul titik dua tambahan di tengah.
         */
        function isSideBySideCompleteValueRow(array $cells): bool
        {
            if (count($cells) !== 2) {
                return false;
            }

            return bakuMutuCellContainsColon($cells[0]) && bakuMutuCellContainsColon($cells[1]);
        }
        }

        if (!function_exists('normalizeLabelValueTableForPrint')) {
        /**
         * Tabel label:nilai untuk cetak — satu kolom per baris (Label : nilai),
         * rata kiri tanpa jarak besar sebelum titik dua.
         */
        function normalizeLabelValueTableForPrint($html)
        {
            if ($html === null || $html === '' || stripos($html, '<table') === false) {
                return $html;
            }

            $tinyMceGenderTable = tryReformatTinyMceGenderPairTable($html);
            if ($tinyMceGenderTable !== null) {
                return $tinyMceGenderTable;
            }

            if (!preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rowMatches)) {
                return $html;
            }

            $genderTableWidth = extractGenderTableWidthPercent($html);
            $rows = '';
            $colCount = 3;
            $isGenderPairTable = false;
            $pendingLValue = null;
            $flushPendingGenderL = function () use (&$pendingLValue, &$rows, &$isGenderPairTable) {
                if ($pendingLValue !== null) {
                    $rows .= buildGenderLabelValueRowHtml('L', $pendingLValue);
                    $isGenderPairTable = true;
                    $pendingLValue = null;
                }
            };
            $appendGenderLabelValue = function ($label, $value) use (&$pendingLValue, &$rows, &$isGenderPairTable, $flushPendingGenderL) {
                $genderLabel = strtoupper(trim(strip_tags((string) $label)));
                if ($genderLabel === 'L') {
                    $flushPendingGenderL();
                    $pendingLValue = $value;
                    return;
                }
                if ($genderLabel === 'P' && $pendingLValue !== null) {
                    $rows .= buildGenderPairValuesRowHtml($pendingLValue, $value);
                    $pendingLValue = null;
                    $isGenderPairTable = true;
                    return;
                }
                $flushPendingGenderL();
                $rows .= buildGenderLabelValueRowHtml($label, $value);
                $isGenderPairTable = true;
            };

            foreach ($rowMatches[1] as $rowHtml) {
                $cells = [];
                if (preg_match_all('/<t[dh][^>]*>(.*?)<\/t[dh]>/is', $rowHtml, $cellMatches)) {
                    foreach ($cellMatches[1] as $cellHtml) {
                        $cells[] = trim((string) $cellHtml);
                    }
                }

                if (count($cells) === 0) {
                    continue;
                }

                if (count($cells) === 1) {
                    $genderTable = buildMassalGenderPairTableHtml($cells[0]);
                    if ($genderTable !== null) {
                        return $genderTable;
                    }

                    [$label, $value] = splitLabelValueHtmlLine($cells[0]);
                    if ($label !== null) {
                        $rows .= buildAlignedNilaiNormalRowHtml($label, $value);
                    } else {
                        $cellStyle = 'border:none;padding:0;vertical-align:top;text-align:left;white-space:normal;';
                        $rows .= '<tr><td colspan="3" class="bm-nilai-normal-row" style="' . $cellStyle . '">' . $cells[0] . '</td></tr>';
                    }
                    continue;
                }

                if (isGenderQuadCellRow($cells)) {
                    $flushPendingGenderL();
                    [$quadL, $quadP] = extractGenderQuadCellValues($cells);
                    $rows .= buildGenderPairValuesRowHtml($quadL, $quadP);
                    $isGenderPairTable = true;
                    continue;
                }

                if (count($cells) === 2 && isGenderPairLabel($cells[0])) {
                    $genderValue = preg_replace('/^:&nbsp;?/i', '', (string) $cells[1]);
                    $genderValue = preg_replace('/^:\s*/u', '', $genderValue);
                    $appendGenderLabelValue($cells[0], $genderValue);
                    continue;
                }

                if (isSideBySideCompleteValueRow($cells)) {
                    $flushPendingGenderL();
                    [$sideL1, $sideV1] = splitLabelValueHtmlLine($cells[0]);
                    [$sideL2, $sideV2] = splitLabelValueHtmlLine($cells[1]);
                    if (isGenderPairLabel($sideL1) && isGenderPairLabel($sideL2)) {
                        $rows .= buildGenderPairRowFromCellsHtml($cells[0], $cells[1]);
                        $isGenderPairTable = true;
                    } else {
                        $rows .= buildAlignedSideBySideRowHtml($cells[0], $cells[1]);
                        $colCount = 6;
                    }
                    continue;
                }

                $label = $cells[0];
                $value = '';

                if (count($cells) >= 3) {
                    $value = $cells[count($cells) - 1];
                } elseif (count($cells) === 2) {
                    $value = preg_replace('/^:&nbsp;?/i', '', $cells[1]);
                    $value = preg_replace('/^:\s*/u', '', $value);
                }

                if (isGenderPairLabel($label)) {
                    $appendGenderLabelValue($label, $value);
                    continue;
                }

                $flushPendingGenderL();
                $rows .= buildAlignedNilaiNormalRowHtml($label, $value);
            }

            $flushPendingGenderL();

            if ($rows === '') {
                return $html;
            }

            return ($isGenderPairTable ? openGenderPairTableHtml($genderTableWidth) : openNilaiNormalCompactTableHtml($colCount))
                . $rows . ($isGenderPairTable ? closeGenderPairTableHtml() : '</tbody></table>');
        }
        }

        if (!function_exists('prepareBakuMutuHtmlForPrint')) {
        /** Siapkan tabel HTML baku mutu untuk cetak DomPDF (pertahankan &lt; pada nilai). */
        function prepareBakuMutuHtmlForPrint($html)
        {
            if ($html === null || $html === '') {
                return '-';
            }

            $html = (string) $html;
            $html = preg_replace('/<\/?p>/i', '', $html);

            if (preg_match('/<table/i', $html)) {
                if (isBulletListBakuMutuTable($html)) {
                    return formatBulletListBakuMutuForPrint($html);
                }

                return normalizeBakuMutuSymbols(normalizeLabelValueTableForPrint($html));
            }

            return normalizeBakuMutuSymbols($html);
        }
        }

        if (!function_exists('renderNilaiRujukanKlinikPrint')) {
        function renderNilaiRujukanKlinikPrint($item_satuan_klinik, $pasienGender = null)
        {
            $resolved = resolveNilaiNormalKlinik($item_satuan_klinik, $pasienGender);

            if (isStructuredNilaiNormalHtml($resolved)) {
                return wrapNilaiNormalPrintContent($resolved);
            }

            if (is_string($resolved) && (
                stripos($resolved, 'bm-print-line') !== false
                || stripos($resolved, 'nilai-normal-plain-lines') !== false
            )) {
                return wrapNilaiNormalPlainCenter($resolved);
            }

            return wrapNilaiNormalPlainCenter(preparePrintText($resolved));
        }
        }

        if (!function_exists('formatKeteranganDilaporanSnapshotForPrint')) {
        /**
         * Format snapshot keterangan_dilaporan dari tb_permohonan_uji_parameter_klinik untuk cetak.
         * Selaras dengan halaman analis (rubahNilaikeForm): tabel HTML dipertahankan.
         */
        function formatKeteranganDilaporanSnapshotForPrint($snapshot)
        {
            $snapshot = trim((string) $snapshot);
            if ($snapshot === '' || $snapshot === '-') {
                return '-';
            }

            if (function_exists('decodeNilaiBakuMutuValue')) {
                $snapshot = decodeNilaiBakuMutuValue($snapshot);
            }

            if (function_exists('rubahNilaikeForm')) {
                $snapshot = rubahNilaikeForm($snapshot);
            } elseif (function_exists('decodeNilaiBakuMutuStorage')) {
                $snapshot = decodeNilaiBakuMutuStorage($snapshot);
            }

            if ($snapshot === null || trim((string) $snapshot) === '' || trim((string) $snapshot) === '-') {
                return '-';
            }

            if (function_exists('isNilaiBakuMutuHtmlTable') && isNilaiBakuMutuHtmlTable($snapshot)) {
                $html = preg_replace('/<\/?p>/i', '', (string) $snapshot);

                return prepareBakuMutuHtmlForPrint($html);
            }

            if (stripos((string) $snapshot, '<table') !== false) {
                $html = preg_replace('/<\/?p>/i', '', (string) $snapshot);

                return prepareBakuMutuHtmlForPrint($html);
            }

            $genderTable = buildMassalGenderPairTableHtml($snapshot);
            if ($genderTable !== null) {
                return $genderTable;
            }

            return plainTextNilaiNormal($snapshot);
        }
        }

        if (!function_exists('resolveIsNormalForItemSatuanKlinik')) {
        function resolveIsNormalForItemSatuanKlinik(array $item_satuan_klinik): int
        {
            $idBm = $item_satuan_klinik['id_baku_mutu'] ?? null;
            $multiple = $item_satuan_klinik['multiple_baku_mutu'] ?? [];
            $min = $item_satuan_klinik['min'] ?? null;
            $max = $item_satuan_klinik['max'] ?? null;

            if ($idBm && is_array($multiple)) {
                foreach ($multiple as $bm) {
                    if (($bm['id_baku_mutu'] ?? null) == $idBm) {
                        return (int) ($bm['is_normal'] ?? 0);
                    }
                }
            }

            if (is_array($multiple)) {
                foreach ($multiple as $bm) {
                    $bmMin = $bm['min'] ?? null;
                    $bmMax = $bm['max'] ?? null;
                    if ($min !== null && $min !== '' && $bmMin == $min && $bmMax == $max) {
                        return (int) ($bm['is_normal'] ?? 0);
                    }
                    if (($min === null || $min === '') && $max !== null && $max !== '' && $bmMax == $max && ($bmMin === null || $bmMin === '')) {
                        return (int) ($bm['is_normal'] ?? 0);
                    }
                }

                if (count($multiple) === 1) {
                    return (int) ($multiple[0]['is_normal'] ?? 0);
                }
            }

            return 0;
        }
        }

        if (!function_exists('resolveNilaiNormalKlinik')) {
        /**
         * NILAI NORMAL: prioritas snapshot keterangan_dilaporan per permohonan; fallback ke logika
         * gender/klasifikasi/ket_default jika snapshot kosong.
         */
        function resolveNilaiNormalKlinik($item_satuan_klinik, $pasienGender = null)
        {
            $snapshot = $item_satuan_klinik['keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik'] ?? null;
            if ($snapshot !== null && $snapshot !== '' && $snapshot !== '-') {
                return formatKeteranganDilaporanSnapshotForPrint($snapshot);
            }

            $min = $item_satuan_klinik['min'] ?? null;
            $max = $item_satuan_klinik['max'] ?? null;
            $equal = $item_satuan_klinik['equal'] ?? null;
            $isNormal = resolveIsNormalForItemSatuanKlinik($item_satuan_klinik);

            $isGenderBased = !empty($item_satuan_klinik['is_baku_mutu_berdasarkan_gender']);
            $nilaiBakuMutu = $item_satuan_klinik['nilai_baku_mutu'] ?? null;
            $ketDefault = $item_satuan_klinik['ket_default_parameter_satuan_klinik'] ?? null;

            // e-GFR dan parameter serupa: ket_default berisi tabel daftar panjang
            if ($ketDefault && stripos($ketDefault, '<table') !== false && isBulletListBakuMutuTable($ketDefault)) {
                return formatBulletListBakuMutuForPrint($ketDefault);
            }
            $multiple = $item_satuan_klinik['multiple_baku_mutu'] ?? [];
            $isClassification = is_array($multiple)
                && count($multiple) > 1
                && shouldShowAllBakuMutuTiers($multiple);

            // LED dll: pasangan L/P → tampilkan tabel gender, bukan dua baris "? 29.9999"
            if (is_array($multiple) && count($multiple) > 1 && !$isClassification) {
                $genderPair = buildGenderPairFromMultipleBakuMutu($multiple);
                if ($genderPair !== null) {
                    return $genderPair;
                }
            }

            // Klasifikasi (e-GFR, LDL): jika snapshot kosong, pakai nilai_baku_mutu master (sama isi snapshot)
            if ($isClassification) {
                foreach ($multiple as $bm) {
                    if (!empty($bm['nilai_baku_mutu']) && is_string($bm['nilai_baku_mutu'])) {
                        $decodedTierNilai = function_exists('decodeNilaiBakuMutuValue')
                            ? decodeNilaiBakuMutuValue($bm['nilai_baku_mutu'])
                            : $bm['nilai_baku_mutu'];
                        if (is_string($decodedTierNilai) && stripos($decodedTierNilai, '<table') !== false) {
                            return formatKeteranganDilaporanSnapshotForPrint($bm['nilai_baku_mutu']);
                        }
                    }
                }
            }

            // Klasifikasi bertingkat: bangun tabel HTML dari min/max/kesimpulan (paling akurat)
            if ($isClassification) {
                $builtTable = buildClassificationBakuMutuTableHtml($multiple);
                if ($builtTable !== '') {
                    return prepareBakuMutuHtmlForPrint($builtTable);
                }
            }

            // Fallback: tabel HTML tersimpan di master baku mutu
            $tableHtml = findBakuMutuTableHtml($item_satuan_klinik);
            if ($tableHtml !== null && ($isClassification || !$isGenderBased)) {
                if (isBulletListBakuMutuTable($tableHtml)) {
                    return formatBulletListBakuMutuForPrint($tableHtml);
                }

                return prepareBakuMutuHtmlForPrint($tableHtml);
            }

            if (!empty($item_satuan_klinik['has_multiple_baku_mutu'])) {
                if (is_array($multiple) && count($multiple) > 1 && $isClassification) {
                    $sorted = $multiple;
                    usort($sorted, function ($a, $b) {
                        $sortKey = function ($bm) {
                            if (isset($bm['min']) && $bm['min'] !== '' && $bm['min'] !== null) {
                                return (float) $bm['min'];
                            }
                            if (isset($bm['max']) && $bm['max'] !== '' && $bm['max'] !== null) {
                                return (float) $bm['max'];
                            }
                            return 999999;
                        };
                        return $sortKey($a) <=> $sortKey($b);
                    });

                    $lines = [];
                    foreach ($sorted as $bm) {
                        $tierLine = formatBakuMutuTierLine($bm);
                        if ($tierLine !== null) {
                            $lines[] = $tierLine;
                        }
                    }

                    if (!empty($lines)) {
                        return '<span class="nilai-normal-plain-lines">' . implode('<br>', $lines) . '</span>';
                    }
                }
            }

            if ($isGenderBased) {
                if ($nilaiBakuMutu !== null && $nilaiBakuMutu !== '') {
                    return plainTextNilaiNormal($nilaiBakuMutu);
                }
                $multiple = $item_satuan_klinik['multiple_baku_mutu'] ?? [];
                if ($pasienGender && is_array($multiple)) {
                    foreach ($multiple as $bm) {
                        if (!empty($bm['gender_baku_mutu']) && ($bm['gender_baku_mutu'] ?? null) === $pasienGender) {
                            $tierLine = formatBakuMutuTierLine($bm);
                            if ($tierLine !== null) {
                                return $tierLine;
                            }
                        }
                    }
                }

                return plainTextNilaiNormal($nilaiBakuMutu ?? '-');
            }

            if ($ketDefault !== null && $ketDefault !== '') {
                if (stripos($ketDefault, '<table') !== false && isBulletListBakuMutuTable($ketDefault)) {
                    return formatBulletListBakuMutuForPrint($ketDefault);
                }

                return plainTextNilaiNormal($ketDefault);
            }

            // Range terpilih controller (Ureum/demografi: satu baris sesuai gender/umur pasien)
            $selectedLine = formatBakuMutuTierLine([
                'min' => $min,
                'max' => $max,
                'equal' => $equal,
                'nilai_baku_mutu' => $nilaiBakuMutu,
                'kesimpulan_baku_mutu' => $item_satuan_klinik['kesimpulan_baku_mutu'] ?? null,
                'is_normal' => $isNormal,
            ]);
            if ($selectedLine !== null) {
                return $selectedLine;
            }

            if ($nilaiBakuMutu !== null && $nilaiBakuMutu !== '') {
                return plainTextNilaiNormal($nilaiBakuMutu);
            }

            return '-';
        }
        }

        // Fungsi untuk mengecek apakah hasil abnormal dan format hasil dengan bold dan bintang
        if (!function_exists('isHasilKlinikKosong')) {
        function isHasilKlinikKosong($hasil)
        {
            return $hasil === null || $hasil === '' || $hasil === '-';
        }
        }

        if (!function_exists('formatHasilAbnormal')) {
        function formatHasilAbnormal($hasil, $nilai_baku_mutu, $numberFormat = 'en')
        {
            if (isHasilKlinikKosong($hasil)) {
                return '';
            }

            // Cek apakah ada flag yang menunjukkan abnormal
            // Jika flag_permohonan_uji_parameter_klinik = 1, berarti abnormal
            if (isset($nilai_baku_mutu['flag']) && $nilai_baku_mutu['flag'] == 1) {
                return '<strong>' . $hasil . '</strong> *';
            }

            // Cek berdasarkan nilai baku mutu
            $min = isset($nilai_baku_mutu['min']) ? $nilai_baku_mutu['min'] : null;
            $max = isset($nilai_baku_mutu['max']) ? $nilai_baku_mutu['max'] : null;
            $equal = isset($nilai_baku_mutu['equal']) ? $nilai_baku_mutu['equal'] : null;

            // Parse hasil dengan number format yang sesuai
            $hasil_numeric = parseNumberInput($hasil, $numberFormat);

            if ($hasil_numeric === null) {
                return $hasil;
            }

            // Cek dengan equal
            if ($equal !== null && $equal !== '' && $equal !== '0') {
                $equal_numeric = parseNumberInput($equal, $numberFormat);
                if ($equal_numeric !== null && $hasil_numeric != $equal_numeric) {
                    return '<strong>' . $hasil . '</strong> *';
                }
            }

            // Cek dengan min dan max
            if ($min !== null && $max !== null && $min !== '' && $max !== '') {
                $dbFormat = $numberFormat ?: 'en'; // Use numberFormat or fallback to 'en'
                $min_numeric = parseNumberInput($min, $dbFormat);
                $max_numeric = parseNumberInput($max, $dbFormat);
                if ($min_numeric !== null && $max_numeric !== null) {
                    if ($hasil_numeric < $min_numeric || $hasil_numeric > $max_numeric) {
                        return '<strong>' . $hasil . '</strong> *';
                    }
                }
            }

            return $hasil;
        }
        }

        // Fungsi untuk mengecek multiple baku mutu — selaras dengan Smt::checkBakuMutu (halaman analis)
        if (!function_exists('formatHasilMultipleBakuMutu')) {
        function formatHasilMultipleBakuMutu($hasil, $item_satuan_klinik, $item_permohonan_uji_klinik = null)
        {
            $context = is_array($item_satuan_klinik) ? $item_satuan_klinik : [];
            if ($item_permohonan_uji_klinik) {
                if (!array_key_exists('pasien_umur', $context) || $context['pasien_umur'] === null || $context['pasien_umur'] === '') {
                    $context['pasien_umur'] = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? null;
                }
                if (!array_key_exists('pasien_gender', $context) || $context['pasien_gender'] === null || $context['pasien_gender'] === '') {
                    $context['pasien_gender'] = optional($item_permohonan_uji_klinik->pasien)->gender_pasien ?? null;
                }
            }

            $formatted = \Smt\Masterweb\Helpers\Smt::formatHasilForKlinikPrint($hasil, $context);

            // Pastikan ≥/≤ (termasuk data lama "? 500") di-render dengan DejaVu di DomPDF
            return preparePrintText($formatted);
        }
        }

        // Fungsi untuk sub-parameter
        if (!function_exists('formatHasilSubAbnormal')) {
        function formatHasilSubAbnormal($hasil, $min, $max, $equal, $flag = null, $numberFormat = 'en')
        {
            if (isHasilKlinikKosong($hasil)) {
                return '';
            }

            // Cek flag terlebih dahulu
            if ($flag !== null && $flag != '' && $flag != '0' && $flag != 0) {
                return '<strong>' . $hasil . '</strong> *';
            }

            $hasil_numeric = parseNumberInput($hasil, $numberFormat);

            if ($hasil_numeric === null) {
                return $hasil;
            }

            // Cek dengan equal
            if ($equal !== null && $equal !== '' && $equal !== '0') {
                $equal_numeric = parseNumberInput($equal, $numberFormat);
                if ($equal_numeric !== null && $hasil_numeric != $equal_numeric) {
                    return '<strong>' . $hasil . '</strong> *';
                }
            }

            // Cek dengan min dan max
            if ($min !== null && $max !== null && $min !== '' && $max !== '') {
                $dbFormat = $numberFormat ?: 'en'; // Use numberFormat or fallback to 'en'
                $min_numeric = parseNumberInput($min, $dbFormat);
                $max_numeric = parseNumberInput($max, $dbFormat);
                if ($min_numeric !== null && $max_numeric !== null) {
                    if ($hasil_numeric < $min_numeric || $hasil_numeric > $max_numeric) {
                        return '<strong>' . $hasil . '</strong> *';
                    }
                }
            }

            return $hasil;
        }
        }

        // Fungsi untuk sub-parameter — selaras dengan Smt::checkBakuMutu (termasuk offset_baku_mutu)
        if (!function_exists('formatHasilSubMultipleBakuMutu')) {
        function formatHasilSubMultipleBakuMutu($hasil, $item_subsatuan_klinik, $item_satuan_klinik = [], $item_permohonan_uji_klinik = null)
        {
            $parentContext = [
                'nama_parameter_satuan_klinik' => $item_satuan_klinik['nama_parameter_satuan_klinik'] ?? null,
                'number_format' => $item_satuan_klinik['number_format'] ?? 'en',
                'kesimpulan_baku_mutu' => $item_satuan_klinik['kesimpulan_baku_mutu'] ?? '',
                'is_normal' => $item_satuan_klinik['is_normal'] ?? null,
                'pasien_umur' => $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? null,
                'pasien_gender' => $item_permohonan_uji_klinik->pasien->gender_pasien ?? null,
            ];

            $formatted = \Smt\Masterweb\Helpers\Smt::formatHasilSubForKlinikPrint(
                $hasil,
                $item_subsatuan_klinik,
                $parentContext
            );

            return preparePrintText($formatted);
        }
        }

    @endphp
    @php
        $fs = isset($fontsizeHasil) ? (float)$fontsizeHasil : 12;
        $lh = isset($lineHeightHasil) ? (float)$lineHeightHasil : 1.5;
        $pd = isset($paddingHasil) ? (float)$paddingHasil : 4;
        $pdTop = isset($paddingTopHasil) ? (float)$paddingTopHasil : $pd;
        $pdBottom = isset($paddingBottomHasil) ? (float)$paddingBottomHasil : $pd;
        $mgLeft = isset($marginLeftHasil) ? (float)$marginLeftHasil : 32;
        $mgRight = isset($marginRightHasil) ? (float)$marginRightHasil : 32;
        if ($mgLeft === 20.0) {
            $mgLeft = 32;
        }
        if ($mgRight === 20.0) {
            $mgRight = 32;
        }
        $lebarKolom = $lebarKolomHasil ?? [
            'pemeriksaan' => 24,
            'hasil' => 10,
            'satuan' => 14,
            'metode' => 12,
            'nilai_normal' => 26,
        ];
        $lkPemeriksaan = (float) ($lebarKolom['pemeriksaan'] ?? 24);
        $lkHasil = (float) ($lebarKolom['hasil'] ?? 10);
        $lkSatuan = (float) ($lebarKolom['satuan'] ?? 14);
        $lkMetode = (float) ($lebarKolom['metode'] ?? 12);
        $lkNilaiNormal = (float) ($lebarKolom['nilai_normal'] ?? 26);
        $adjustedHasilSatuan = \Smt\Masterweb\Helpers\Smt::adjustKlinikPrintHasilSatuanWidths(
            ['hasil' => $lkHasil, 'satuan' => $lkSatuan, 'nilai_normal' => $lkNilaiNormal],
            $arr_permohonan_parameter ?? [],
            $fs
        );
        $lkHasil = $adjustedHasilSatuan['hasil'];
        $lkSatuan = $adjustedHasilSatuan['satuan'];
        $lkNilaiNormal = $adjustedHasilSatuan['nilai_normal'];
        $fsNilaiNormal = max(7, $fs - 1);
        $pageMarginBottom = 18; // mm — ruang aman footer BSrE halaman terakhir (canvas)
        $bsreFooterReserve = 0;
        $showKopVal = isset($showKop) ? (int) $showKop : 1;
        // Tinggi area kop mengikuti kop_magelang.png (≈5.5cm lebar penuh A4); tanpa kop tetap 5.5cm (kertas berkop)
        $kopPageMargin = '5.5cm';
    @endphp
    <style>
    .starter-template {
        text-align: center;
    }

    table {
        width: 100%;
    }

    .table-with-signature {
        table-layout: fixed;
    }

    td,
    th {
        white-space: normal;
    }

    .table-with-signature td,
    .table-with-signature th {
        word-wrap: break-word;
    }

    .table-with-signature td:nth-child(5),
    .table-with-signature td:nth-child(5) table,
    .table-with-signature td:nth-child(5) table td {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        word-break: normal !important;
    }

    .nilai-normal-print-wrap {
        display: block;
        width: 100%;
        max-width: 100%;
        text-align: left !important;
        text-justify: auto !important;
        overflow: hidden;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .nilai-normal-print-wrap span,
    .nilai-normal-print-wrap div,
    .nilai-normal-print-wrap p {
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        margin: 0 !important;
        padding: 0 !important;
        text-align: left !important;
    }

    .nilai-normal-plain-lines {
        display: inline;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        text-align: left !important;
    }

    .nilai-normal-plain-center {
        display: block;
        width: 100%;
        text-align: center !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .table-with-signature td.nilai-normal-cell .nilai-normal-plain-center {
        text-align: center !important;
    }

    .bm-bullet-list-wrap {
        display: block;
        width: 100% !important;
        max-width: 100% !important;
        text-align: left !important;
    }

    .bm-bullet-list-table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: auto !important;
        border-collapse: collapse !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        text-align: left !important;
    }

    .bm-bullet-list-table .bm-bullet-mark {
        vertical-align: top !important;
        padding: 0 2px 0 0 !important;
        border: none !important;
        text-align: left !important;
        white-space: nowrap !important;
        width: 5pt !important;
        max-width: 5pt !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
    }

    .bm-bullet-list-table .bm-bullet-text {
        vertical-align: top !important;
        padding: 0 !important;
        border: none !important;
        text-align: left !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        width: auto !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
    }

    .bm-bullet-list-table tr + tr .bm-bullet-mark,
    .bm-bullet-list-table tr + tr .bm-bullet-text {
        padding-top: {{ max(0, $pdBottom * 0.35) }}pt !important;
    }

    .table-with-signature td:nth-child(5) .bm-bullet-list-wrap,
    .table-with-signature td:nth-child(5) .bm-bullet-list-table,
    .table-with-signature td:nth-child(5) .bm-bullet-list-table td {
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
    }

    .bm-nowrap-unit {
        white-space: nowrap !important;
    }

    .table-with-signature td.nilai-normal-cell {
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        text-align: left !important;
        vertical-align: top !important;
        padding-top: {{ $pdTop }}pt !important;
        padding-bottom: {{ $pdBottom }}pt !important;
        padding-left: 5px !important;
        padding-right: 5px !important;
    }

    .table-with-signature td.nilai-normal-cell.nilai-normal-cell--middle {
        text-align: center !important;
        vertical-align: middle !important;
    }

    .nilai-normal-print-wrap .bm-bullet-list-table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: auto !important;
        text-align: left !important;
    }

    .nilai-normal-print-wrap .bm-label-value-table,
    .nilai-normal-print-wrap .bm-nilai-normal-compact {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        text-align: left !important;
    }

    .nilai-normal-print-wrap table:not(.bm-bullet-list-table) {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        text-align: left !important;
    }

    .nilai-normal-print-wrap .bm-bullet-list-table td {
        text-align: left !important;
    }

    .nilai-normal-print-wrap table td,
    .nilai-normal-print-wrap .bm-label-value-table td,
    .nilai-normal-print-wrap .bm-nilai-normal-compact td,
    .nilai-normal-print-wrap .bm-nilai-normal-row {
        text-align: left !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        vertical-align: top !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .nilai-normal-print-wrap .bm-nilai-normal-compact tr + tr .bm-nilai-normal-row {
        padding-top: {{ max(0, $pdBottom * 0.25) }}pt !important;
    }

    .bm-nilai-normal-compact .bm-label-col,
    .bm-nilai-normal-compact col.bm-col-label {
        width: 42% !important;
        text-align: left !important;
        padding-right: 1px !important;
    }

    .bm-nilai-normal-compact .bm-colon-col,
    .bm-nilai-normal-compact col.bm-col-colon {
        width: 3% !important;
        text-align: left !important;
        white-space: nowrap !important;
        padding: 0 2px 0 0 !important;
    }

    .bm-nilai-normal-compact .bm-value-col,
    .bm-nilai-normal-compact col.bm-col-value {
        width: 55% !important;
        text-align: left !important;
    }

    .table-with-signature td:nth-child(5) .bm-nilai-normal-compact .bm-value-col {
        text-align: left !important;
    }

    .bm-gender-pair-wrap {
        display: block;
        width: 100%;
        text-align: center !important;
        margin: 0 auto !important;
        padding-left: 2px !important;
        padding-right: 2px !important;
    }

    .bm-gender-pair-table {
        table-layout: auto !important;
        margin: 0 auto !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
    }

    .bm-gender-pair-table > tbody > tr > td.bm-gender-pair-cell {
        vertical-align: top !important;
        border: none !important;
        text-align: left !important;
        width: auto !important;
        white-space: nowrap !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        padding: 1px 4px !important;
    }

    .bm-gender-pair-table .bm-gender-pair-left {
        padding-right: 10px !important;
    }

    .table-with-signature td:nth-child(5) .bm-gender-pair-wrap {
        text-align: center !important;
    }

    @media print {
        #cetak {
            display: none;
        }
    }

    .garis {
        border: 1px solid
    }

    .table2 {
        text-align: center
    }

    .result {
        border-collapse: collapse;
    }

    .result td {
        border: 1px solid black;
        text-align: center;
    }

    /* Gunakan ukuran kertas A4 potrait — margin atas untuk kop di setiap halaman */
    @page {
        size: A4;
        margin: {{ $kopPageMargin }} {{ $mgRight }}px {{ $pageMarginBottom }}mm {{ $mgLeft }}px;
        orphans: 3;
        widows: 3;
    }

    .kop-repeat {
        position: fixed;
        top: -{{ $kopPageMargin }};
        left: 0;
        right: 0;
        height: {{ $kopPageMargin }};
        overflow: hidden;
    }
    .kop-repeat table,
    .kop-repeat td {
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
    }
    .kop-repeat img {
        width: 100%;
        max-height: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        object-position: top center;
    }

    @font-face {
        font-family: "source_sans_proregular";
        src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
        font-weight: normal;
        font-style: normal;
    }

    /* Global typography: gunakan Arial {{ $fs }}pt, line-height {{ $lh }} */
    body {
        font-family: Arial, Calibri, Candara, Segoe, "Segoe UI", Optima, sans-serif;
        font-size: {{ $fs }}pt;
        line-height: {{ $lh }};
        text-align: justify;
        text-justify: inter-word;
    }

    /* Samakan ukuran font & line-height untuk elemen umum */
    body, table, td, th, p, span, div {
        font-size: {{ $fs }}pt;
        line-height: {{ $lh }};
    }

    .page_break {
        page-break-before: always;
    }

    .flex-container {
        display: flex !important;
        flex-wrap: nowrap !important;
    }

    .flex-container>div {
        width: 100px !important;
        margin: 10px !important;
    }

    th {
        margin-top: 2px;
        font-size: {{ $fs }}pt !important;
        padding-top: {{ $pdTop }}pt !important;
        padding-bottom: {{ $pdBottom }}pt !important;
    }

    td {
        margin-top: 2px;
        font-size: {{ $fs }}pt !important;
        padding-top: {{ $pdTop }}pt !important;
        padding-bottom: {{ $pdBottom }}pt !important;
    }


    .table-with-signature + .signature-section {
        margin-top: 10px !important;
    }

    .table-with-signature:empty {
        display: none !important;
    }

    .empty-group {
        display: none !important;
    }

    .keterangan-table table,
    .keterangan-table tr,
    .keterangan-table td,
    .keterangan-table th {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .patient-info-table {
        table-layout: fixed !important;
        width: 100% !important;
    }

    .patient-info-table td {
        padding-top: 1px !important;
        padding-bottom: 1px !important;
        padding-left: 3px !important;
        padding-right: 3px !important;
        line-height: 1.2 !important;
    }

    .patient-info-table td.patient-info-label {
        font-weight: bold !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
        text-align: left !important;
        padding-right: 16px !important;
    }

    .patient-info-table td.patient-info-label-right {
        font-weight: bold !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
        text-align: left !important;
        padding-left: 3px !important;
        padding-right: 16px !important;
        line-height: 1.15 !important;
    }

    .patient-info-table tr.patient-info-row-alamat td.patient-info-label,
    .patient-info-table tr.patient-info-row-alamat td.patient-info-colon,
    .patient-info-table tr.patient-info-row-alamat td.patient-info-alamat-value {
        vertical-align: top !important;
    }

    .patient-info-table tr.patient-info-row-alamat td.patient-info-label-right,
    .patient-info-table tr.patient-info-row-alamat td.patient-info-colon-right,
    .patient-info-table tr.patient-info-row-alamat td.patient-info-value-right,
    .patient-info-table tr.patient-info-row-tanggal td.patient-info-label-right,
    .patient-info-table tr.patient-info-row-tanggal td.patient-info-colon-right,
    .patient-info-table tr.patient-info-row-tanggal td.patient-info-value-right {
        vertical-align: top !important;
        line-height: 1.1 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .patient-info-table tr.patient-info-row-tanggal td {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .patient-info-table tr.patient-info-row-tanggal {
        height: 1px !important;
    }

    .patient-info-table td.patient-info-colon {
        white-space: nowrap !important;
        vertical-align: middle !important;
        text-align: left !important;
        padding-left: 6px !important;
        padding-right: 8px !important;
        width: 2% !important;
    }

    .patient-info-table td.patient-info-colon-right {
        white-space: nowrap !important;
        vertical-align: middle !important;
        text-align: left !important;
        padding-left: 6px !important;
        padding-right: 8px !important;
        width: 2% !important;
        line-height: 1.15 !important;
        font-weight: normal !important;
    }

    .patient-info-table td.patient-info-value,
    .patient-info-table td.patient-info-alamat-value,
    .patient-info-table td.patient-info-value-right {
        padding-left: 0 !important;
        padding-right: 0 !important;
        text-align: left !important;
    }

    .patient-info-table td.patient-info-value,
    .patient-info-table td.patient-info-value-right {
        white-space: nowrap !important;
        vertical-align: middle !important;
        line-height: 1.15 !important;
        font-weight: normal !important;
    }

    .patient-info-table td.patient-info-alamat-value {
        vertical-align: top !important;
        white-space: normal !important;
    }

    .patient-info-table td.patient-info-spacer {
        padding: 0 !important;
    }

    /* Ensure hasil column is properly aligned */
    .table-with-signature td:nth-child(2),
    .table-with-signature th:nth-child(2) {
        text-align: center !important;
        vertical-align: middle !important;
    }

    /* Ensure strong tags and asterisks align properly in hasil column */
    .table-with-signature td:nth-child(2) strong {
        display: inline;
        text-align: center;
    }

    /* Ensure all content in hasil column is centered */
    .table-with-signature td:nth-child(2) {
        text-align: center !important;
    }

    .table-with-signature td:nth-child(2) sup,
    .table-with-signature td:nth-child(2) sub,
    .table-with-signature td:nth-child(2) span:not(.hasil-multi-line):not(.urinalisa-multi-hasil),
    .table-with-signature td:nth-child(2) strong {
        text-align: center !important;
        display: inline;
    }

    /* Font size setting untuk kolom hasil (mengikuti $fs) */
    .table-with-signature td:nth-child(2) {
        font-size: {{ $fs }}pt !important;
        font-weight: normal;
    }

    .table-with-signature td:nth-child(2) .hasil-multi-line,
    .table-with-signature td:nth-child(2) .urinalisa-multi-hasil {
        display: inline-block !important;
        text-align: left !important;
        white-space: normal !important;
        line-height: 1.3;
        vertical-align: middle;
    }

    .table-with-signature td:nth-child(2) .urinalisa-multi-hasil {
        white-space: nowrap !important;
    }

    .table-with-signature td:nth-child(2) .urinalisa-first-jenis {
        white-space: nowrap !important;
        display: inline !important;
    }

    .table-with-signature td:nth-child(2) .hasil-multi-line br,
    .table-with-signature td:nth-child(2) .urinalisa-multi-hasil br,
    .table-with-signature td:nth-child(2) br {
        display: block !important;
        content: '' !important;
        line-height: 1.3 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .table-with-signature td:nth-child(2) table.hasil-multi-line {
        display: table !important;
        margin: 0 auto !important;
        padding: 0 !important;
        border: none !important;
        border-collapse: collapse !important;
        width: auto !important;
    }

    .table-with-signature td:nth-child(2) table.hasil-multi-line td,
    .table-with-signature td:nth-child(2) table.hasil-multi-line tr {
        display: table-row !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        text-align: center !important;
        vertical-align: middle !important;
    }

    .table-with-signature td:nth-child(2) table.hasil-multi-line td {
        display: table-cell !important;
        line-height: 1.3 !important;
    }

    .table-with-signature td:nth-child(2) strong {
        font-size: {{ $fs }}pt !important;
        font-weight: bold;
    }

    /* Lebar kolom tabel hasil */
    .table-with-signature th:nth-child(1),
    .table-with-signature td:nth-child(1) {
        width: {{ $lkPemeriksaan }}%;
    }

    .table-with-signature th:nth-child(2),
    .table-with-signature td:nth-child(2) {
        width: {{ $lkHasil }}%;
        white-space: normal;
        padding-left: 3px !important;
        padding-right: 3px !important;
        word-wrap: normal !important;
        overflow-wrap: normal !important;
    }

    .table-with-signature th:nth-child(3),
    .table-with-signature td:nth-child(3) {
        width: {{ $lkSatuan }}%;
        white-space: {{ $lkSatuan < 10 ? 'normal' : 'nowrap' }};
        padding-left: 2px !important;
        padding-right: 2px !important;
        font-size: {{ max(7, $fs - 1) }}pt !important;
    }

    .table-with-signature th:nth-child(4),
    .table-with-signature td:nth-child(4) {
        width: {{ $lkMetode }}%;
    }

    .table-with-signature th:nth-child(5),
    .table-with-signature td:nth-child(5) {
        width: {{ $lkNilaiNormal }}%;
        max-width: 0;
        text-align: left !important;
        vertical-align: top !important;
        word-break: normal !important;
        overflow-wrap: break-word !important;
        hyphens: none !important;
    }

    .table-with-signature th:nth-child(2),
    .table-with-signature th:nth-child(3),
    .table-with-signature th:nth-child(4) {
        white-space: nowrap;
        font-size: {{ max(7, $fs - 1) }}pt !important;
    }

    .table-with-signature th:nth-child(5) {
        white-space: normal !important;
        text-align: left !important;
    }

    .table-with-signature td:nth-child(5) .bm-bullet-list-wrap {
        text-align: left !important;
        max-width: none !important;
        width: 100% !important;
    }

    .table-with-signature td:nth-child(5) .bm-bullet-list-table {
        text-align: left !important;
        max-width: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }

    .table-with-signature td:nth-child(5) .bm-bullet-list-table .bm-bullet-mark {
        text-align: left !important;
        width: 5pt !important;
        max-width: 5pt !important;
        padding-right: 2px !important;
    }

    .table-with-signature td:nth-child(5) .bm-bullet-list-table .bm-bullet-text {
        text-align: left !important;
        width: auto !important;
    }

    .table-with-signature td:nth-child(5) .bm-print-line,
    .table-with-signature td:nth-child(5) .nilai-normal-plain-lines {
        display: inline;
        text-align: left !important;
        word-break: normal !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .table-with-signature td:nth-child(5) table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
    }

    .table-with-signature td:nth-child(5) .bm-label-value-table td,
    .table-with-signature td:nth-child(5) .bm-nilai-normal-compact td,
    .table-with-signature td:nth-child(5) .bm-nilai-normal-row {
        text-align: left !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        font-size: {{ $fsNilaiNormal }}pt !important;
        line-height: {{ $lh }} !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Spacing/padding untuk pemisah antar baris tabel */
    .table-with-signature tr {
        border-spacing: 0;
    }

    .table-with-signature td,
    .table-with-signature th {
        padding-top: {{ $pdTop }}pt !important;
        padding-bottom: {{ $pdBottom }}pt !important;
        padding-left: 5px !important;
        padding-right: 5px !important;
        line-height: {{ $lh }} !important;
    }

    /* Spacing khusus untuk baris header (sedikit lebih besar) */
    .table-with-signature th {
        padding-top: {{ round($pdTop * 1.5, 1) }}pt !important;
        padding-bottom: {{ round($pdBottom * 1.5, 1) }}pt !important;
    }

    /* Line-height dinamis untuk seluruh section tanda tangan & pertanggungjawaban */
    .signature-section,
    .signature-section p,
    .signature-section td,
    .signature-section th,
    .signature-section span,
    .signature-section div {
        line-height: {{ $lh }} !important;
    }

    .signature-section {
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }

    .signature-section .no-break {
        margin-bottom: 2pt !important;
    }
    </style>
</head>


<body>
    @if ($showKopVal)
    <div class="kop-repeat">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td><img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="100%"></td>
            </tr>
        </table>
    </div>
    @endif
    <div class="content-wrapper">
        <div class="header-section">
            <H1 style="text-align: center; font-size: 16px;"><u>HASIL PEMERIKSAAN LABORATORIUM</u></H1>

            @php
                $fmtTanggalHasilKlinik = function ($tgl) {
                    if (!$tgl || $tgl === '-') {
                        return '-';
                    }
                    try {
                        return \Carbon\Carbon::parse($tgl)->locale('id')->isoFormat('D MMMM Y');
                    } catch (\Exception $e) {
                        return preg_replace('/\s+\d{1,2}:\d{2}$/', '', $tgl);
                    }
                };
                $tglDiambilCetak = $fmtTanggalHasilKlinik($tanggal_pengambilan_sample ?? '-');
                $tglDiperiksaCetak = $fmtTanggalHasilKlinik($tanggal_pemeriksaan_sample ?? '-');
                $usiaCetak = ($item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-') . ' Tahun';
                $tglLahirPasien = $item_permohonan_uji_klinik->pasien->tgllahir_pasien ?? null;
                if (!empty($tglLahirPasien)) {
                    try {
                        $umurAcuan = !empty($tanggal_pemeriksaan_sample)
                            ? \Carbon\Carbon::parse($tanggal_pemeriksaan_sample)
                            : now();
                        $umurDiff = \Carbon\Carbon::parse($tglLahirPasien)->diff($umurAcuan);

                        $usiaParts = [];
                        if ($umurDiff->y > 0) {
                            $usiaParts[] = $umurDiff->y . ' Tahun';
                        }
                        if ($umurDiff->m > 0) {
                            $usiaParts[] = $umurDiff->m . ' Bulan';
                        }
                        if ($umurDiff->d > 0 || empty($usiaParts)) {
                            $usiaParts[] = $umurDiff->d . ' Hari';
                        }

                        $usiaCetak = implode(' ', $usiaParts);
                    } catch (\Exception $e) {
                        // fallback ke umur tahun tersimpan saat parsing tanggal gagal
                    }
                }

                $jenisKelaminUsia = ($item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan')
                    . '/' . $usiaCetak;
            @endphp
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="patient-info-table"
                style="border-collapse: collapse; margin-top: 10px; margin-bottom: 0;">
                <colgroup>
                    <col style="width:34%">
                    <col style="width:2%">
                    <col style="width:13%">
                    <col style="width:3%">
                    <col style="width:27%">
                    <col style="width:2%">
                    <col style="width:19%">
                </colgroup>
                <tr>
                    <td class="patient-info-label">
                        Dokter Pengirim
                    </td>
                    <td class="patient-info-colon">
                        :
                    </td>
                    <td colspan="2" class="patient-info-value">
                        @if ($item_permohonan_uji_klinik->doctor_type == 'rujukan' || $item_permohonan_uji_klinik->is_haji == 1)
                            {{ $item_permohonan_uji_klinik->nama_dokter_pengirim_permohonan_uji_klinik ?? '-' }}
                        @elseif (isset($item_permohonan_uji_klinik->nama_dokter_pengirim_permohonan_uji_klinik))
                            {{ $item_permohonan_uji_klinik->nama_dokter_pengirim_permohonan_uji_klinik ?? '-' }}
                        @else
                            dr. Sunantyo, M.P.H.
                        @endif
                    </td>
                    <td class="patient-info-label-right">
                        No. Rekam Medis
                    </td>
                    <td class="patient-info-colon-right">
                        :
                    </td>
                    <td class="patient-info-value-right">
                        {{ $item_permohonan_uji_klinik->getNoRekamMedis() }}
                    </td>
                </tr>
                <tr>
                    <td class="patient-info-label">
                        Nama
                    </td>
                    <td class="patient-info-colon">
                        :
                    </td>
                    <td colspan="2" class="patient-info-value">
                        {{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien ?? '-', 'UTF-8') }}
                    </td>
                    <td class="patient-info-label-right">
                        No. Spesimen
                    </td>
                    <td class="patient-info-colon-right">
                        :
                    </td>
                    <td class="patient-info-value-right">
                        {{ $item_permohonan_uji_klinik->getSpesimenNumber() }}
                    </td>
                </tr>
                <tr>
                    <td class="patient-info-label">
                        Jenis Kelamin/Usia
                    </td>
                    <td class="patient-info-colon">
                        :
                    </td>
                    <td colspan="2" class="patient-info-value">
                        {{ $jenisKelaminUsia }}
                    </td>
                    <td class="patient-info-label-right">
                        No. Laboratorium
                    </td>
                    <td class="patient-info-colon-right">
                        :
                    </td>
                    <td class="patient-info-value-right">
                        {{ $item_permohonan_uji_klinik->getLabNumber() }}
                    </td>
                </tr>
                <tr class="patient-info-row-alamat">
                    <td rowspan="2" class="patient-info-label">
                        Alamat
                    </td>
                    <td rowspan="2" class="patient-info-colon">
                        :
                    </td>
                    <td rowspan="2" colspan="2" class="patient-info-value patient-info-alamat-value">
                        {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
                    </td>
                    <td class="patient-info-label-right">
                        Tanggal Diambil
                    </td>
                    <td class="patient-info-colon-right">
                        :
                    </td>
                    <td class="patient-info-value-right">
                        {{ $tglDiambilCetak }}
                    </td>
                </tr>
                <tr class="patient-info-row-tanggal">
                    <td class="patient-info-label-right">
                        Tanggal Diperiksa
                    </td>
                    <td class="patient-info-colon-right">
                        :
                    </td>
                    <td class="patient-info-value-right">
                        {{ $tglDiperiksaCetak }}
                    </td>
                </tr>
            </table>
        </div>
        {{-- End header-section --}}

        <table cellspacing="0" cellpadding="0" border="1" class="table-with-signature"
            style="margin-top: 4px; margin-bottom: 0px; border-collapse: collapse; width: 100%; table-layout: fixed;">
            <colgroup>
                <col style="width:{{ $lkPemeriksaan }}%">
                <col style="width:{{ $lkHasil }}%">
                <col style="width:{{ $lkSatuan }}%">
                <col style="width:{{ $lkMetode }}%">
                <col style="width:{{ $lkNilaiNormal }}%">
            </colgroup>
            <thead>
                <tr>
                    <th style="border: 1px solid black; padding: 5px; text-align: left; background-color: #f0f0f0; white-space: nowrap;">
                        PEMERIKSAAN</th>
                    <th style="border: 1px solid black; padding: 3px; text-align: center; background-color: #f0f0f0; white-space: nowrap;">
                        HASIL</th>
                    <th style="border: 1px solid black; padding: 3px; text-align: center; background-color: #f0f0f0; white-space: nowrap;">
                        SATUAN</th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center; background-color: #f0f0f0; white-space: nowrap;">
                        METODE</th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center; background-color: #f0f0f0; white-space: nowrap;">
                        NILAI NORMAL</th>
                </tr>
            </thead>


            <tbody>
                @php
                    $item_permohonan_parameter_satuan = 0; // Variabel untuk menyimpan total jumlah elemen
                    $total_rows = 0; // Hitung total baris yang akan ditampilkan (termasuk header grup)
                    $all_params_with_results = []; // Simpan semua parameter yang punya hasil untuk tracking

                    // Hitung total baris yang akan ditampilkan
                    foreach ($arr_permohonan_parameter as $group_key => $item) {
                        // Pastikan item_permohonan_parameter_satuan ada dan adalah array
                        if (
                            isset($item['item_permohonan_parameter_satuan']) &&
                            is_array($item['item_permohonan_parameter_satuan'])
                        ) {
                            // Hitung elemen di dalam setiap array item_permohonan_parameter_satuan
                            $item_permohonan_parameter_satuan += count($item['item_permohonan_parameter_satuan']);

                            // Tambah 1 untuk header grup
                            $total_rows++;

                            // Hitung baris untuk setiap parameter (termasuk yang hasilnya kosong)
                            foreach ($item['item_permohonan_parameter_satuan'] as $param_key => $param) {
                                $param_row_count = 0;

                                if (count($param['data_permohonan_uji_subsatuan_klinik'] ?? []) > 0) {
                                    $param_row_count++;
                                    foreach ($param['data_permohonan_uji_subsatuan_klinik'] as $sub) {
                                        $param_row_count++;
                                    }
                                } else {
                                    $param_row_count = 1;
                                }

                                $total_rows += $param_row_count;
                            }
                        }
                    }

                    // Semua parameter harus tercetak (mis. Lain-lain di Sedimen).
                    // DomPDF memecah halaman otomatis; tanda tangan mengikuti di halaman berikutnya bila perlu.
                    $max_rows_before_cut = 18;
                    $should_cut_last_param = false;

                    // Menampilkan total jumlah elemen
                    // dd ($arr_permohonan_parameter);
                    $count = 0;
                    $current_row_count = 0;
                @endphp


                @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                    @php
                        $nama_param_jenis = str_replace(
                            '<br>',
                            '',
                            $item_parameter_jenis_klinik['name_parameter_jenis_klinik'],
                        );
                        $nama_param_jenis = html_entity_decode(
                            $item_parameter_jenis_klinik['name_parameter_jenis_klinik'],
                        );

                        // Tampilkan grup jika memiliki parameter (hasil boleh kosong)
                        $has_parameters = !empty($item_parameter_jenis_klinik['item_permohonan_parameter_satuan'])
                            && is_array($item_parameter_jenis_klinik['item_permohonan_parameter_satuan']);
                    @endphp
                    @if ($has_parameters)
                        @php
                            $isChild = !empty($item_parameter_jenis_klinik['id_parameter_jenis_klinik_parent']);
                        @endphp
                        @if ($isChild)
                            {{-- Child: tampilkan sebagai bold text (hanya huruf pertama kapital) di baris pertama parameter satuan --}}
                            @php $current_row_count++; @endphp
                            <tr>
                                <td colspan="5"
                                    style="text-align: left; padding: 3px 5px; border: 1px solid black; font-weight: bold;">
                                    {!! ucwords(strtolower($nama_param_jenis)) !!}
                                </td>
                            </tr>
                        @else
                            {{-- Parent: tampilkan sebagai header dengan background --}}
                            @if ($item_permohonan_uji_klinik->is_prolanis_gula == 1 || $item_permohonan_uji_klinik->is_prolanis_gula == 1)
                                @php $current_row_count++; @endphp
                                <tr>
                                    <th colspan="5"
                                        style="text-align: center; padding: 5px 3px; border: 1px solid black; background-color: #e8e8e8;">
                                        <strong>{!! strtoupper($nama_param_jenis) !!}</strong>
                                    </th>
                                </tr>
                            @else
                                @php $current_row_count++; @endphp
                                <tr>
                                    <th colspan="5"
                                        style="text-align: center; padding-left: 3px; border: 1px solid black;">
                                        <strong>{!! strtoupper($nama_param_jenis) !!}</strong>
                                    </th>
                                </tr>
                            @endif
                        @endif
                    @endif

                    {{-- @for ($j = 0; $j < count($item_parameter_jenis_klinik['item_permohonan_parameter_satuan']) - 1; $j++)

                @endfor --}}

                    @php
                        $count_parameter = 0;
                        $items_sorted = $item_parameter_jenis_klinik['item_permohonan_parameter_satuan'] ?? [];
                        usort($items_sorted, function ($a, $b) {
                            $resolveSort = function ($item) {
                                if (isset($item['sort_parameter_satuan_klinik']) && $item['sort_parameter_satuan_klinik'] !== null && $item['sort_parameter_satuan_klinik'] !== '') {
                                    return (int) $item['sort_parameter_satuan_klinik'];
                                }

                                return PHP_INT_MAX;
                            };

                            $sortA = $resolveSort($a);
                            $sortB = $resolveSort($b);

                            if ($sortA !== $sortB) {
                                return $sortA <=> $sortB;
                            }

                            return strcmp(
                                (string) ($a['parameter_satuan_klinik'] ?? ''),
                                (string) ($b['parameter_satuan_klinik'] ?? '')
                            );
                        });

                        $last_param_key = !empty($items_sorted) ? array_key_last($items_sorted) : null;
                    @endphp
                    @foreach ($items_sorted as $key_satuan_klinik => $item_satuan_klinik)
                        @php
                            $is_last_param_in_group = $key_satuan_klinik == $last_param_key;
                            $should_skip =
                                $should_cut_last_param &&
                                $is_last_param_in_group &&
                                $current_row_count >= $max_rows_before_cut;
                        @endphp
                        @if (!$should_skip)
                            @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] ?? []) > 0)
                                {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan --}}
                                @php
                                    // Urutkan subsatuan jika ada berdasarkan sort_parameter_sub_satuan_klinik
                                    $subs_sorted = $item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] ?? [];
                                    if (isset($subs_sorted['id_permohonan_uji_sub_parameter_klinik'])) {
                                        $subs_sorted = [$subs_sorted];
                                    }
                                    usort($subs_sorted, function ($a, $b) {
                                        $sa = isset($a['sort_parameter_sub_satuan_klinik'])
                                            ? (int) $a['sort_parameter_sub_satuan_klinik']
                                            : PHP_INT_MAX;
                                        $sb = isset($b['sort_parameter_sub_satuan_klinik'])
                                            ? (int) $b['sort_parameter_sub_satuan_klinik']
                                            : PHP_INT_MAX;
                                        return $sa <=> $sb;
                                    });

                                    $last_sub_key = !empty($subs_sorted) ? array_key_last($subs_sorted) : null;
                                    $has_subs = !empty($subs_sorted);
                                @endphp

                                @if ($has_subs)
                                    @php $current_row_count++; @endphp
                                    {{-- Header parent: colspan agar tanpa garis vertikal antar kolom --}}
                                    <tr>
                                        <td colspan="5"
                                            style="text-align: left; padding: 5px 3px; border: 1px solid black; font-weight: bold; background-color: #f5f5f5; vertical-align: middle;">
                                            {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                        </td>
                                    </tr>

                                    @foreach ($subs_sorted as $key_subsatuan_klinik => $item_subsatuan_klinik)
                                        @php
                                            // Cek apakah ini sub-parameter terakhir dan perlu dipotong
                                            $is_last_sub = $key_subsatuan_klinik == $last_sub_key;
                                            $should_skip_sub =
                                                $should_cut_last_param &&
                                                $is_last_param_in_group &&
                                                $is_last_sub &&
                                                $current_row_count >= $max_rows_before_cut;
                                        @endphp
                                        @if (!$should_skip_sub)
                                            @php $current_row_count++; @endphp
                                            <tr>
                                                {{-- nama test: padding sama seperti parameter biasa --}}
                                                <td style="text-align: left; padding: 5px 3px; border: 1px solid black; vertical-align: middle;">
                                                    {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }}
                                                </td>

                                                {{-- hasil --}}
                                                <td style="text-align: center; padding: 3px; border: 1px solid black; vertical-align: middle;">
                                                    {!! formatHasilSubMultipleBakuMutu(
                                                        $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? null,
                                                        $item_subsatuan_klinik,
                                                        $item_satuan_klinik,
                                                        $item_permohonan_uji_klinik ?? null,
                                                    ) !!}
                                                </td>

                                                {{-- satuan --}}
                                                <td style="text-align: center; padding: 5px; border: 1px solid black; vertical-align: middle;">
                                                    @if ($item_subsatuan_klinik['satuan_permohonan_uji_sub_parameter_klinik'] != null)
                                                        {!! protectBakuMutuPhrasesForPrint($item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik']) !!}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                {{-- metode --}}
                                                <td style="text-align: center; padding: 5px; border: 1px solid black; vertical-align: middle;">
                                                    {{ resolveMetodePermohonanForDisplay($item_satuan_klinik) }}
                                                </td>

                                                {{-- nilai rujukan --}}
                                                <td class="nilai-normal-cell{{ isPlainNilaiNormalForPrint($item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] ?? '-') ? ' nilai-normal-cell--middle' : '' }}" style="border: 1px solid black; vertical-align: middle;">
                                                    {!! normalizeBakuMutuSymbols($item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] ?? '-') !!}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @else
                                @php $current_row_count++; @endphp
                                <tr>
                                    {{-- nama test --}}
                                    <td style="text-align: left; padding: 5px 3px; border: 1px solid black;">
                                        {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                    </td>

                                    {{-- hasil --}}
                                    <td style="text-align: center; padding: 3px; border: 1px solid black; vertical-align: middle;">
                                        {!! formatHasilMultipleBakuMutu(
                                            $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? null,
                                            $item_satuan_klinik,
                                            $item_permohonan_uji_klinik,
                                        ) !!}
                                    </td>

                                        {{-- satuan --}}
                                        <td style="text-align: center; padding: 5px; border: 1px solid black;">
                                            @if ($item_satuan_klinik['satuan_permohonan_uji_parameter_klinik'] != null)
                                                {!! protectBakuMutuPhrasesForPrint($item_satuan_klinik['nama_satuan_permohonan_uji_parameter_klinik']) !!}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- metode: dari permohonan (baca hasil), fallback master parameter --}}
                                        <td style="text-align: center; padding: 5px; border: 1px solid black;">
                                            {{ resolveMetodePermohonanForDisplay($item_satuan_klinik) }}
                                        </td>

                                        {{-- nilai rujukan --}}
                                        <td class="nilai-normal-cell{{ isPlainNilaiNormalForPrint($item_satuan_klinik, $item_permohonan_uji_klinik->pasien->gender_pasien ?? null) ? ' nilai-normal-cell--middle' : '' }}" style="border: 1px solid black;">
                                            {!! renderNilaiRujukanKlinikPrint($item_satuan_klinik, $item_permohonan_uji_klinik->pasien->gender_pasien ?? null) !!}
                                        </td>
                                    </tr>
                            @endif
                        @endif
                        @php
                            $count_parameter++;
                        @endphp
                    @endforeach
                    @php
                        $count++;
                    @endphp
                @endforeach




            </tbody>

        </table>



        {{-- TTD selalu di bawah tabel, tidak perlu page break karena sudah dipotong jika perlu --}}
        <div class="signature-section" style="page-break-inside: auto;">
            <p style="font-size: {{ $fs }}pt !important; line-height: {{ $lh }} !important;">Dokter Penanggungjawab: dr. Sunantyo, M.P.H. <span
                    style="font-size: {{ $fs }}pt !important; line-height: {{ $lh }} !important; padding-left: 20px; display: none;" hidden="true">(dokter yang melakukan validasi)</span></p>
            <style>
                /* Samakan font Catatan dengan font dinamis */
                .note-container p,
                .note-container ul,
                .note-container li {
                    font-size: {{ $fs }}pt;
                    line-height: {{ $lh }};
                    margin: 0;
                    margin-bottom: 5px;
                    padding: 0;
                }

                .note-container ul {
                    list-style: none;
                }
            </style>

            @php
                $catatanHasilCetak = \Smt\Masterweb\Helpers\Smt::dedupeStadiumCatatanHasil(
                    (string) ($item_permohonan_uji_klinik->catatan_hasil ?? '')
                );
                $hasCatatanHasil = strlen(trim(strip_tags($catatanHasilCetak))) > 0;
            @endphp
            <div class="note-container">
                <p>Catatan</p>
                <p>(*) = Hasil Abnormal</p>
                @if ($hasCatatanHasil)
                    {!! preparePrintText($catatanHasilCetak) !!}
                @endif
            </div>

            <div style="width: 600px !important;">
                <table cellspacing="0" cellpadding="0" border="0"
                    style="margin-top: 10px; border-collapse: collapse;">
                    <tr>
                        <td width="40%" style="padding: 3px; border: none;">
                            Diperiksa oleh
                        </td>
                        <td width="2%"
                            style="padding: 3px; border: none; text-align: center;">
                            :
                        </td>
                        <td width="58%" style="padding: 3px; border: none;">
                            {{ $nama_petugas_pemeriksa }}
                        </td>
                    </tr>
                    <tr>
                        <td width="40%" style="padding: 3px; border: none;">
                            Diverifikasi oleh
                        </td>
                        <td width="2%"
                            style="padding: 3px; border: none; text-align: center;">
                            :
                        </td>
                        <td width="58%" style="padding: 3px; border: none;">
                            {{ $nama_petugas_verifikator }}
                        </td>
                    </tr>
                </table>
            </div>
            <br>

            @php
                $validasi = Smt\Masterweb\Models\VerificationActivitySample::where(
                    'is_klinik',
                    $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
                )
                    ->where('id_verification_activity', 5)
                    ->first();

                if (isset($validasi)) {
                    $tanggal_validasi = $validasi->stop_date;
                    $nama_petugas_validasi = $validasi->nama_petugas;
                } else {
                    $tanggal_validasi = null;
                    $nama_petugas_validasi = null;
                }

                $tgl_ttd_raw = $tanggal_validasi
                    ?? $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik
                    ?? $item_permohonan_uji_klinik->updated_at
                    ?? null;
                $tgl_ttd = $tgl_ttd_raw
                    ? \Carbon\Carbon::parse($tgl_ttd_raw)->isoFormat('D MMMM Y')
                    : \Carbon\Carbon::now()->isoFormat('D MMMM Y');
            @endphp
            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint._ttd-validator-klinik', [
                'tglTtdLabel' => $tgl_ttd,
                'fs' => $fs,
                'lh' => $lh,
                'validasi' => $validasi,
                'nama_petugas_validasi' => $nama_petugas_validasi,
                'signOption' => $signOption ?? 0,
            ])
        </div>
        {{-- End signature-section --}}

        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint._bsre-footer-klinik', [
            'fs' => $fs,
        ])
    </div>
    {{-- End content-wrapper --}}

    {{-- =============================================
         HALAMAN KEDUA: TES NARKOBA (jika ada)
         ============================================= --}}
    @if (isset($dataNarkoba) && count($dataNarkoba) > 0)
    @php
        $narkoba_mapping   = $mapping_narkoba ?? [];
        $tgl_narkoba       = $tanggal_pemeriksaan_narkoba ?? '';
        $jam_narkoba       = $jam_pemeriksaan_narkoba ?? '';

        // Hitung kesimpulan narkoba
        $narkoba_rows = array_chunk($dataNarkoba, 2);
        $kesimpulanNarkobaHtml = \Smt\Masterweb\Helpers\Smt::composeKesimpulanNarkobaCetak($dataNarkoba, $narkoba_mapping);
    @endphp

    <div style="page-break-before: always; font-family: Arial, Helvetica, sans-serif; font-size: 12pt; line-height: {{ $lh }}; color: #000;">

        {{-- Kop halaman ke-2+ dari .kop-repeat (position:fixed) --}}

        {{-- Judul --}}
        <div style="text-align: center; margin-top: 8px;">
            <u>HASIL PEMERIKSAAN NARKOBA</u><br>
            NO : {!! $no_LHU !!}
        </div>

        <div style="margin-top: 16px;">
            <p>Dokter Penanggung Jawab Klinik : dr. Sunantyo, M.P.H
                <span style="font-size: 9pt; font-style: italic;">(dokter yang melakukan validasi)</span></p>
        </div>

        {{-- Identitas Pasien & Pemeriksaan --}}
        <table width="100%" cellspacing="0" cellpadding="5" border="0"
               style="border-collapse: collapse; margin-top: 10px;">
            <tr>
                <td style="width:30%"><u>A. Identitas Pasien</u></td>
                <td style="width:1%"></td>
                <td style="width:69%"></td>
            </tr>
            <tr>
                <td style="padding-left:20px">1. Nama</td>
                <td>:</td>
                <td>{{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien ?? '-', 'UTF-8') }}</td>
            </tr>
            <tr>
                <td style="padding-left:20px">2. Tempat/Tanggal Lahir</td>
                <td>:</td>
                <td>
                    {{ \Smt\Masterweb\Helpers\Smt::tempatLahirPasienCetak($item_permohonan_uji_klinik->pasien ?? null) }}/
                    @php
                        $tgl_lahir_narkoba = $item_permohonan_uji_klinik->pasien->tgllahir_pasien ?? null;
                        echo $tgl_lahir_narkoba
                            ? \Carbon\Carbon::parse($tgl_lahir_narkoba)->isoFormat('D MMMM Y')
                            : '';
                    @endphp
                </td>
            </tr>
            <tr>
                <td style="padding-left:20px">3. Jenis Kelamin</td>
                <td>:</td>
                <td>{{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td style="padding-left:20px">4. Pekerjaan</td>
                <td>:</td>
                <td>{{ \Smt\Masterweb\Helpers\Smt::pekerjaanPasienCetak($item_permohonan_uji_klinik->pasien ?? null) }}</td>
            </tr>
            <tr style="vertical-align:top">
                <td style="padding-left:20px">5. Alamat</td>
                <td>:</td>
                <td>{{ \Smt\Masterweb\Helpers\Smt::alamatPasienNarkobaCetak($item_permohonan_uji_klinik->pasien ?? null) }}</td>
            </tr>
            <tr>
                <td><u>B. Pemeriksaan</u></td>
                <td></td><td></td>
            </tr>
            <tr>
                <td style="padding-left:20px">1. Tgl Pemeriksaan</td>
                <td>:</td>
                <td>{{ $tgl_narkoba }}</td>
            </tr>
            <tr>
                <td style="padding-left:20px">2. Jam</td>
                <td>:</td>
                <td>{{ $jam_narkoba }} WIB</td>
            </tr>
        </table>

        <br>
        <u>C. HASIL tes pemeriksaan urine secara kualitatif atas zat</u>
        <br>

        {{-- Tabel Hasil Narkoba (2 kolom) --}}
        <table width="100%" cellspacing="0" cellpadding="6" border="0"
               style="border-collapse: collapse; margin-top: 10px; text-align: left;">
            @foreach($narkoba_rows as $row)
            <tr>
                {{-- Kolom Kiri --}}
                <td style="width:4%; padding-left:20px; white-space:nowrap;">
                    <input type="checkbox"
                        @if($row[0]['hasil_permohonan_uji_parameter_klinik'] != 'Negatif') checked @endif>
                </td>
                <td style="width:34%;">
                    {{ $narkoba_mapping[$row[0]['nama_parameter_satuan_klinik']] ?? $row[0]['nama_parameter_satuan_klinik'] }}
                </td>
                <td style="width:12%; white-space:nowrap;">
                    : {{ $row[0]['hasil_permohonan_uji_parameter_klinik'] ?? '..................' }}
                </td>
                {{-- Kolom Kanan --}}
                @if(isset($row[1]))
                    <td style="width:4%; padding-left:20px; white-space:nowrap;">
                        <input type="checkbox"
                            @if($row[1]['hasil_permohonan_uji_parameter_klinik'] != 'Negatif') checked @endif>
                    </td>
                    <td style="width:34%;">
                        {{ $narkoba_mapping[$row[1]['nama_parameter_satuan_klinik']] ?? $row[1]['nama_parameter_satuan_klinik'] }}
                    </td>
                    <td style="width:12%; white-space:nowrap;">
                        : {{ $row[1]['hasil_permohonan_uji_parameter_klinik'] ?? '..................' }}
                    </td>
                @else
                    <td style="width:4%;"></td>
                    <td style="width:34%;"></td>
                    <td style="width:12%;"></td>
                @endif
            </tr>
            @endforeach
        </table>

        {{-- D. Kesimpulan --}}
        <u>D. Kesimpulan</u>
        <br><br>
        <p style="margin-left:20px">
            {!! $kesimpulanNarkobaHtml !!}
        </p>
        @php
            $catatanFooterNarkobaCetak = \Smt\Masterweb\Helpers\Smt::resolveCatatanFooterNarkobaCetak($item_permohonan_uji_klinik);
        @endphp
        @if($catatanFooterNarkobaCetak !== '')
            <p style="margin-left:20px; margin-top: 4px;">{{ $catatanFooterNarkobaCetak }}</p>
        @endif

        {{-- Tanda Tangan (sama dengan halaman hasil klinik utama) --}}
        @php
            $validasi_narkoba_halaman = \Smt\Masterweb\Models\VerificationActivitySample::where(
                'is_klinik',
                $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
            )
                ->where('id_verification_activity', 5)
                ->first();

            if (isset($validasi_narkoba_halaman)) {
                $tanggal_validasi_narkoba = $validasi_narkoba_halaman->stop_date;
                $nama_petugas_validasi_narkoba = $validasi_narkoba_halaman->nama_petugas;
            } else {
                $tanggal_validasi_narkoba = null;
                $nama_petugas_validasi_narkoba = null;
            }

            $tgl_ttd_narkoba_raw = $tanggal_validasi_narkoba
                ?? $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik
                ?? $item_permohonan_uji_klinik->updated_at
                ?? null;
            $tgl_ttd_narkoba_halaman = $tgl_ttd_narkoba_raw
                ? \Carbon\Carbon::parse($tgl_ttd_narkoba_raw)->isoFormat('D MMMM Y')
                : \Carbon\Carbon::now()->isoFormat('D MMMM Y');
        @endphp
        <div class="signature-section" style="margin-top: 10px; padding-bottom: {{ $bsreFooterReserve }}pt; margin-bottom: 4pt;">
            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint._ttd-validator-klinik', [
                'tglTtdLabel' => $tgl_ttd_narkoba_halaman,
                'fs' => $fs,
                'lh' => $lh,
                'validasi' => $validasi_narkoba_halaman,
                'nama_petugas_validasi' => $nama_petugas_validasi_narkoba,
                'signOption' => $signOption ?? 0,
            ])
        </div>

        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint._bsre-footer-klinik', [
            'fs' => $fs,
        ])

        <!-- @if (isset($signOption) && $signOption == 1) -->
            
        <!-- @endif -->

    </div>
    @endif

</body>

</html>
