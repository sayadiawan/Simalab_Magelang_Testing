<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemeriksaan Narkoba</title>
    <style>
        @page {
            /* size: A4; */
            /* margin: 40mm 5mm; */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @php
            $fs = 12;
            $lh = isset($lineHeightHasil) ? (float)$lineHeightHasil : 1.4;
            $pd = isset($paddingHasil) ? (float)$paddingHasil : 4;
            $pasienCetak = $item_permohonan_uji_klinik->pasien ?? null;
            $pageMarginBottom = 18; // mm
            $bsreFooterReserve = 0;
            $showKopVal = isset($showKop) ? (int) $showKop : 1;
            $kopPageMargin = '5.5cm';
            $mgLeft = isset($marginLeftHasil) ? (float) $marginLeftHasil : 32;
            $mgRight = isset($marginRightHasil) ? (float) $marginRightHasil : 32;
            if ($mgLeft === 20.0) { $mgLeft = 32; }
            if ($mgRight === 20.0) { $mgRight = 32; }
        @endphp
        @page {
            margin-top: {{ $kopPageMargin }};
            margin-right: {{ $mgRight }}px;
            margin-bottom: {{ $pageMarginBottom }}mm;
            margin-left: {{ $mgLeft }}px;
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
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ $fs }}pt;
            line-height: {{ $lh }};
            color: #000;
            padding: 20px 0;
        }
        td, th {
            padding-top: {{ $pd }}pt;
            padding-bottom: {{ $pd }}pt;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
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

    <div style="text-align: center">
        <u>HASIL PEMERIKSAAN NARKOBA</u><br>
        NO : {!! $no_LHU !!}
    </div>

    <div style="margin-top: 20px;">
        <p>Dokter Penanggung Jawab Klinik : dr. Dummy Pengirim</p>
    </div>

    <table width="100%" cellspacing="0" cellpadding="5" border="0" style="border-collapse: collapse; margin-top: 10px;">
        <tr>
            <td style="width: 30%">
                <u>A. Identitas Pasien</u>
            </td>
            <td style="width: 1%">

            </td>
            <td style="width: 69%">

            </td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">
                1. Nama
            </td>
            <td>
                :
            </td>
            <td>
                {{ mb_strtoupper($pasienCetak->nama_pasien ?? '-', 'UTF-8') }}
            </td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">
                2. Tempat/Tanggal Lahir
            </td>
            <td>
                :
            </td>
            <td>
                {{ \Smt\Masterweb\Helpers\Smt::tempatLahirPasienCetak($pasienCetak) }}/
                @php
                    $tgllahir = $pasienCetak->tgllahir_pasien ?? null;
                    $tgllahir_formatted = $tgllahir
                        ? \Carbon\Carbon::parse($tgllahir)->isoFormat('D MMMM Y')
                        : '';
                @endphp
                {{ $tgllahir_formatted }}
            </td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">
                3. Jenis Kelamin
            </td>
            <td>
                :
            </td>
            <td>
                {{ ($pasienCetak->gender_pasien ?? '') == 'L' ? 'Laki-laki' : 'Perempuan' }}
            </td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">
                4. Pekerjaan
            </td>
            <td>
                :
            </td>
            <td>
                {{ \Smt\Masterweb\Helpers\Smt::pekerjaanPasienCetak($pasienCetak) }}
            </td>
        </tr>
        <tr style="vertical-align: top">
            <td style="padding-left: 20px;">
                5. Alamat
            </td>
            <td>
                :
            </td>
            <td>
                {{ \Smt\Masterweb\Helpers\Smt::alamatPasienNarkobaCetak($pasienCetak) }}
            </td>
        </tr>
        <tr>
            <td>
                <u>B. Pemeriksaan</u>
            </td>
            <td>

            </td>
            <td>

            </td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">
                1. Tgl Pemeriksaan
            </td>
            <td>
                :
            </td>
            <td>
                {{ $tanggal_pemeriksaan_sample ?? '' }}
            </td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">
                2. Jam
            </td>
            <td>
                :
            </td>
            <td>
                {{ $jam_pemeriksaan_sample }} WIB
            </td>
        </tr>
    </table>
    <br>
    <u>C. Hasil tes pemeriksaan urine secara kualitatif atas zat</u>
    <br>
    @php
        $rows = array_chunk($dataNarkoba, 2);
        $formatHasilNarkoba = function ($hasil) {
            if ($hasil === null || $hasil === '' || $hasil === '-') {
                return '..................';
            }
            if (strcasecmp(trim($hasil), 'Positif') === 0) {
                return '<b>Positif</b>';
            }
            return e($hasil);
        };
        $isDiperiksaNarkoba = function ($hasil) {
            return $hasil !== null && $hasil !== '' && $hasil !== '-';
        };
    @endphp
    <table width="100%" cellspacing="0" cellpadding="5" border="0" style="border-collapse: collapse; margin-top: 10px; text-align: left;">
        @foreach($rows as $row)
        <tr>
            {{-- KOLOM KIRI --}}
            <td style="padding-left: 20px;">
                <input type="checkbox"
                       @if($isDiperiksaNarkoba($row[0]['hasil_permohonan_uji_parameter_klinik'] ?? null)) checked @endif>
            </td>
            <td>
                {{ $mapping[$row[0]['nama_parameter_satuan_klinik']] ?? $row[0]['nama_parameter_satuan_klinik'] }}
            </td>
            <td>
                : {!! $formatHasilNarkoba($row[0]['hasil_permohonan_uji_parameter_klinik'] ?? null) !!}
            </td>

            {{-- KOLOM KANAN --}}
            @if(isset($row[1]))
                <td>
                    <input type="checkbox"
                           @if($isDiperiksaNarkoba($row[1]['hasil_permohonan_uji_parameter_klinik'] ?? null)) checked @endif>
                </td>
                <td>
                    {{ $mapping[$row[1]['nama_parameter_satuan_klinik']] ?? $row[1]['nama_parameter_satuan_klinik'] }}
                </td>
                <td>
                    : {!! $formatHasilNarkoba($row[1]['hasil_permohonan_uji_parameter_klinik'] ?? null) !!}
                </td>
            @else
                <td></td><td></td><td></td>
            @endif
        </tr>

        {{-- SPACER --}}
        <tr>
            <td style="padding-left: 20px; padding-top: {{ round($pd * 3, 1) }}pt;"></td>
            <td></td><td></td>
            <td></td><td></td><td></td>
        </tr>
    @endforeach
    </table>
    <u>D. Kesimpulan</u>
    <br><br>
    @php
        if (!function_exists('sanitizePrintHtml')) {
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
        function printPlainFooterHtml($html)
        {
            $html = sanitizePrintHtml($html);
            $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));

            return $text === '' ? '' : $text;
        }
        }

        $kesimpulanNarkobaHtml = \Smt\Masterweb\Helpers\Smt::composeKesimpulanNarkobaCetak($dataNarkoba, $mapping);

    @endphp
    <p style="margin-left: 20px">
        {!! $kesimpulanNarkobaHtml !!}
    </p>
    @php
        $kesimpulanFooterCetak = printPlainFooterHtml(
            \Smt\Masterweb\Helpers\Smt::resolveCatatanFooterNarkobaCetak($item_permohonan_uji_klinik)
        );
    @endphp
    @if($kesimpulanFooterCetak !== '')
        <p style="margin-left: 20px; margin-top: 4px;">{{ $kesimpulanFooterCetak }}</p>
    @endif

    <div style="width: 600px !important; margin-top: 20px;">
        <table cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse;">
            <tr>
                <td width="40%" style="padding: 3px; border: none;">Diperiksa oleh</td>
                <td width="2%" style="padding: 3px; border: none; text-align: center;">:</td>
                <td width="58%" style="padding: 3px; border: none;">{{ $nama_petugas_pemeriksa ?? '...................' }}</td>
            </tr>
            <tr>
                <td width="40%" style="padding: 3px; border: none;">Diverifikasi oleh</td>
                <td width="2%" style="padding: 3px; border: none; text-align: center;">:</td>
                <td width="58%" style="padding: 3px; border: none;">{{ $nama_petugas_verifikator ?? '...................' }}</td>
            </tr>
        </table>
    </div>

    @php
        $validasi = \Smt\Masterweb\Models\VerificationActivitySample::where(
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
    <div style="margin-top: 40px; padding-bottom: {{ $bsreFooterReserve }}pt; margin-bottom: 4pt;">
        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint._ttd-validator-klinik', [
            'tglTtdLabel' => $tgl_ttd,
            'fs' => $fs,
            'lh' => $lh,
            'validasi' => $validasi,
            'nama_petugas_validasi' => $nama_petugas_validasi,
            'signOption' => $signOption ?? 0,
        ])
    </div>

    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint._bsre-footer-klinik', [
        'fs' => $fs,
    ])
</body>

</html>
