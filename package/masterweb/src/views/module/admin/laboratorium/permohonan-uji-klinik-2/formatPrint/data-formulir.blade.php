<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Permintaan Pemeriksaan - {{ $item_permohonan_uji_klinik->getDisplayNoregister() }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            background-color: white;
            padding: 0;
            margin: 0;
        }

        .container {
            width: 180mm;
            max-width: 180mm;
            margin: 0 auto;
            background-color: white;
            padding: 10mm;
            box-sizing: border-box;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .logo {
            display: table-cell;
            width: 20px;
            vertical-align: middle;
            overflow: visible;
        }

        .logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: 100%;
        }

        .header-text h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 3px 0;
            color: #000;
            text-transform: uppercase;
        }

        .header-text h3 {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #000;
            text-transform: uppercase;
        }

        .header-text p {
            font-size: 10px;
            margin: 1px 0;
            line-height: 1.3;
            color: #000;
        }

        .section-title {
            /* background-color: #2d5016; */
            color: rgb(0, 0, 0);
            padding: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin: 0px 0 0px 0;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #333;
        }

        th,
        td {
            padding: 2px 2px 1px 2px;
            text-align: left;
            font-size: 10px;
            line-height: 1.2;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 28%;
        }

        .header-number-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .header-number-table td {
            border: none;
            padding: 1px 2px;
        }

        .header-number-table tr + tr td {
            border-top: 0.6px solid #666;
        }

        .form-table tr:first-child td:nth-child(1) {
            width: 50%;
        }

        .form-table tr:first-child td:nth-child(2) {
            width: 50%;
        }

        .form-table tr:not(:first-child) td:nth-child(1) {
            width: 40%;
        }

        .form-table tr:not(:first-child) td:nth-child(2) {
            width: 20%;
        }

        .form-table tr:not(:first-child) td:nth-child(3) {
            width: 20%;
        }

        .form-table tr:not(:first-child) td:nth-child(4) {
            width: 20%;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3px;
            font-size: 9px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 3px;
        }

        .result-columns {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            text-align: center;
        }

        .result-columns div {
            border-left: 1px solid #333;
            padding: 3px;
        }

        .result-columns div:first-child {
            border-left: none;
        }

        .exam-items {
            border: 1px solid #333;
            padding: 8px;
            background-color: white;
        }

        .exam-items {
            border: 1px solid #333;
            padding: 8px;
            background-color: white;
        }

        .exam-items table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .exam-items table td {
            border: none;
            padding: 2px 5px;
            font-size: 9px;
            line-height: 1.2;
            width: 25%;
            vertical-align: top;
            text-indent: -6px;
            padding-left: 9px;
            word-wrap: break-word;
            white-space: normal;
        }



        .consent-section {
            margin-top: 15px;
        }

        .consent-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .consent-subtitle {
            text-align: center;
            font-style: italic;
            font-size: 9px;
            margin-bottom: 8px;
        }

        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            width: 100%;
        }

        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
        }

        .signature-title {
            font-size: 10px;
            margin-bottom: 3px;
            line-height: 1.2;
        }

        .signature-role {
            font-size: 10px;
            margin-bottom: 30px;
            line-height: 1.2;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 120px;
            margin: 0 auto 10px auto;
        }

        .signature-name {
            font-size: 9px;
            margin-top: 5px;
        }

        input[type="text"],
        input[type="date"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 2px;
            border: none;
            border-bottom: 1px solid #333;
            background-color: transparent;
            font-family: inherit;
            font-size: 10px;
        }

        textarea {
            border: 1px solid #333;
            resize: vertical;
            min-height: 25px;
        }

        label {
            font-size: 10px;
            margin-right: 8px;
        }

        .label-align {
            display: inline-block;
            width: 140px;
        }

        .form-table td {
            word-wrap: break-word;
            white-space: normal;
            vertical-align: top;
            line-height: 1.4;
        }

        .form-table td span:not(.label-align) {
            word-break: break-word;
            overflow-wrap: break-word;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 1mm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html,
            body {
                width: 210mm !important;
                height: auto !important;
                background-color: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 8mm !important;
                border: none !important;
                box-shadow: none !important;
                background-color: white !important;
                box-sizing: border-box !important;
            }

            .header {
                margin-bottom: 10px;
                padding-bottom: 8px;
            }

            .logo {
                width: 15px;
            }

            .logo img {
                width: 40px !important;
                height: 40px !important;
                object-fit: contain !important;
            }

            .header-text h2 {
                font-size: 13px;
                margin-bottom: 2px;
            }

            .header-text h3 {
                font-size: 12px;
                margin-bottom: 3px;
            }

            .header-text p {
                font-size: 9px;
                margin: 1px 0;
            }

            .section-title {
                padding: 3px;
                font-size: 10px;
                margin: 5px 0 3px 0;
            }

            table {
                width: 100% !important;
                margin-bottom: 5px;
            }

            th,
            td {
                padding: 1px 2px 0px 2px;
                font-size: 9px;
            }

            .exam-items {
                background-color: white !important;
                padding: 5px;
            }

            .exam-items table td {
                font-size: 8px;
                padding: 1px 3px;
                padding-left: 7px;
                text-indent: -4px;
                line-height: 1.2;
            }

            .consent-section {
                margin-top: 8px;
            }

            .signature-box p {
                font-size: 9px;
                margin-bottom: 25px;
            }

            .signature-line {
                width: 100px;
            }

            p {
                font-size: 9px !important;
                margin-bottom: 5px !important;
            }
        }
    </style>
</head>


@php
    \Carbon\Carbon::setLocale('id');

    // Hitung tindakan medis khusus menggunakan helper function
    $tindakan_medis_khusus_display = \Smt\Masterweb\Helpers\Smt::getTindakanMedisKhususFromParameter(
        $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
        $item_permohonan_uji_klinik->tindakan_medis_khusus ?? null,
    );

    // Normalisasi gender agar tidak salah tampil (sumber data bisa M/F, L/P, atau teks lengkap)
    $rawGender = strtoupper(trim((string) ($item_pasien->jeniskelamin_pasien ?? $item_pasien->gender_pasien ?? '')));
    if (in_array($rawGender, ['M', 'L', 'MALE', 'LAKI-LAKI', 'LAKI LAKI', 'PRIA'], true)) {
        $jenis_kelamin_display = 'Laki-laki';
    } elseif (in_array($rawGender, ['F', 'P', 'FEMALE', 'PEREMPUAN', 'WANITA'], true)) {
        $jenis_kelamin_display = 'Perempuan';
    } else {
        $jenis_kelamin_display = '-';
    }
@endphp

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" alt="Logo"
                style="width: 100%;  object-fit: contain;">
        </div>

        <!-- Title -->
        <div class="section-title">FORMULIR PERMINTAAN PEMERIKSAAN</div>

        <!-- Form Table -->
        <table class="form-table">
            <tr>
                <td>
                    <table class="header-number-table">
                        <tr>
                            <td style="width: 35%;"><strong>Tanggal Pendaftaran</strong></td>
                            <td style="width: 5%;">:</td>
                            <td style="width: 60%;">
                                @php
                                    $tglRegister = $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik ?? null;
                                @endphp
                                {{ $tglRegister ? \Carbon\Carbon::parse($tglRegister)->isoFormat('D MMMM Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 35%;"><strong>No. Laboratorium</strong></td>
                            <td style="width: 5%;">:</td>
                            <td style="width: 60%;">
                                {{ $item_permohonan_uji_klinik->getLabNumber() }}</td>
                        </tr>
                        <tr>
                            <td style="width: 35%;"><strong>No. Spesimen</strong></td>
                            <td style="width: 5%;">:</td>
                            <td style="width: 60%;">
                                {{ $item_permohonan_uji_klinik->getSpesimenNumber() }}</td>
                        </tr>
                    </table>
                </td>
                <td colspan="3" style="background-color: #f0f0f0; font-weight: bold;">Dokter Pengirim :
                    @if ($item_permohonan_uji_klinik->doctor_type == 'lab')
                        dr. Sunantyo, M.P.H.
                    @else
                        {{ $item_permohonan_uji_klinik->nama_dokter_pengirim_permohonan_uji_klinik ?? '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 35%; padding: 1px;"><strong>No. Rekam Medis</strong></td>
                            <td style="border: none; width: 5%; padding: 1px;">:</td>
                            <td style="border: none; width: 60%; padding: 1px;">{{ $item_permohonan_uji_klinik->getNoRekamMedis() }}</td>
                        </tr>
                    </table>
                </td>
                <td colspan="3" style="background-color: #f0f0f0; font-weight: bold;">Diagnosis :
                    {{ $item_permohonan_uji_klinik->diagnosa_permohonan_uji_klinik ?? '' }}</td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 35%; padding: 1px;"><strong>Nama</strong></td>
                            <td style="border: none; width: 5%; padding: 1px;">:</td>
                            <td style="border: none; width: 60%; padding: 1px;">{{ mb_strtoupper($item_pasien->nama_pasien, 'UTF-8') }}</td>
                        </tr>
                    </table>
                </td>
                <td colspan="3" rowspan="5"
                    style="background-color: #f0f0f0; font-weight: bold; align-items: center; text-align: center;">
                    <img src="data:image/png;base64,{{ $qr_code }}" alt="QR Code" width="50%">
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 35%; padding: 1px;"><strong>Tgl. Lahir / Umur</strong></td>
                            <td style="border: none; width: 5%; padding: 1px;">:</td>
                            <td style="border: none; width: 60%; padding: 1px;">
                                {{ Carbon\Carbon::parse($item_pasien->tgllahir_pasien)->isoFormat('D MMMM Y') }} /
                                {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik }} thn</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 35%; padding: 1px;"><strong>Jenis Kelamin</strong></td>
                            <td style="border: none; width: 5%; padding: 1px;">:</td>
                            <td style="border: none; width: 60%; padding: 1px;">
                                {{ $jenis_kelamin_display }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 35%; padding: 1px;"><strong>Telepon/No. HP</strong></td>
                            <td style="border: none; width: 5%; padding: 1px;">:</td>
                            <td style="border: none; width: 60%; padding: 1px;">
                                {{ $item_pasien->phone_pasien }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 35%; padding: 1px;"><strong>Alamat</strong></td>
                            <td style="border: none; width: 5%; padding: 1px;">:</td>
                            <td style="border: none; width: 60%; padding: 1px;">
                               {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_pasien) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Item Pemeriksaan -->
        <div class="exam-items">
            <div class="section-title">ITEM PEMERIKSAAN</div>
            <table>
                @php
                    $allItems = [];
                    
                    // Jika ada categoryLayouts, gunakan struktur kategori
                    if(isset($categoryLayouts) && count($categoryLayouts) > 0)
                    {
                        // Get selected items from permohonan
                        $selectedItemsIds = $item_permohonan_uji_klinik->permohonanujipaketklinik->pluck('parameter_paket_klinik')->toArray();
                        
                        // Collect items by category order
                        foreach($categoryLayouts as $category)
                        {
                            if($category->categoryItems && count($category->categoryItems) > 0)
                            {
                                foreach($category->categoryItems as $categoryItem)
                                {
                                    // Hanya tambahkan jika item dipilih dalam permohonan
                                    if(in_array($categoryItem->parameterPaketKlinik->id_parameter_paket_klinik, $selectedItemsIds))
                                    {
                                        $allItems[] = [
                                            'name' => $categoryItem->parameterPaketKlinik->name_parameter_paket_klinik,
                                            'category' => $category->category_code . '. ' . $category->category_name
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        // Fallback: gunakan sorting lama
                        $items = $item_permohonan_uji_klinik->permohonanujipaketklinik;
                        $itemsArray = [];
                        foreach ($items as $item) {
                            $itemsArray[] = $item;
                        }
                        // Urutkan berdasarkan sort dari parameterpaketklinik
                        usort($itemsArray, function($a, $b) {
                            $sortA = $a->parameterpaketklinik->sort ?? 0;
                            $sortB = $b->parameterpaketklinik->sort ?? 0;
                            return $sortA <=> $sortB;
                        });
                        
                        foreach($itemsArray as $item)
                        {
                            $allItems[] = [
                                'name' => $item->parameterpaketklinik->name_parameter_paket_klinik,
                                'category' => null
                            ];
                        }
                    }
                    
                    $chunks = array_chunk($allItems, 5); // Bagi items menjadi grup 5
                @endphp
                @foreach ($chunks as $chunk)
                    <tr>
                        @foreach ($chunk as $item)
                            <td>• {{ $item['name'] }}</td>
                        @endforeach
                        @for ($i = count($chunk); $i < 5; $i++)
                            <td>&nbsp;</td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        </div>

        <!-- Informed consent tindakan medis: sembunyikan jika urin/feses saja (tanpa tindakan medis) -->
        @php
            $jenisSampleResolved = is_array($jenis_sample ?? null) ? $jenis_sample : [];
            if ($jenisSampleResolved === []) {
                $jenisSampleResolved = \Smt\Masterweb\Helpers\Smt::resolveJenisSampleForPermohonan(
                    $item_permohonan_uji_klinik->id_permohonan_uji_klinik
                );
            }
            $spesimenTindakanMedis = \Smt\Masterweb\Helpers\Smt::jenisSpesimenTindakanMedisInformedConsent(
                $jenisSampleResolved
            );
            $spesimenTindakanMedisDisplay = !empty($spesimenTindakanMedis)
                ? implode(', ', $spesimenTindakanMedis)
                : '......';
            $petugasConsentDisplay = '..................................';
            $petugasNama = trim((string) ($petugas ?? ''));
            if ($petugasNama !== '' && $petugasNama !== '...................') {
                $petugasConsentDisplay = $petugasNama;
            }
            $showTindakanMedisConsent = \Smt\Masterweb\Helpers\Smt::shouldShowTindakanMedisInformedConsent(
                $jenisSampleResolved,
                $item_permohonan_uji_klinik->mode_pengambilan_sampel ?? null
            );
        @endphp
        @if ($showTindakanMedisConsent)
            <div class="consent-section">
                <div class="section-title">SURAT PERSETUJUAN TINDAKAN MEDIS (INFORM CONSENT)</div>
                <p class="consent-subtitle">(diisi petugas lab)</p>

                <p style="font-size: 10px; margin-bottom: 10px;">
                    Dengan ini menyatakan SETUJU/MENOLAK untuk dilakukan tindakan medis berupa pengambilan spesimen : <b>{{ $spesimenTindakanMedisDisplay }}</b>. Dari penjelasan yang diberikan saya telah mengerti dan memahami segala hal yang berhubungan dengan tindakan medis yang akan dilakukan dan kemungkinan pasca tindakan yang mungkin terjadi.
                </p>

                <div class="signature-area">
                    <div class="signature-box">
                        <div class="signature-title">Mengetahui</div>
                        <div class="signature-role">Pasien/Wali</div>
                        @if (!empty($item_permohonan_uji_klinik->signature_pengambil_sample_pasien))
                            <div
                                style="height:60px; display:flex; align-items:center; justify-content:center; margin-top: -10px; margin-bottom: -10px;">
                                <img src="{{ $item_permohonan_uji_klinik->signature_pengambil_sample_pasien }}"
                                    alt="TTD Pasien" style="max-width: 160px; max-height: 60px; object-fit: contain;" />
                            </div>
                        @else
                            <br>
                            <br>
                        @endif
                        <br>
                        <br>
                        <div class="signature-name">
                            @if ($item_permohonan_uji_klinik->nama_perwakilan_permohonan_uji_klinik)
                                ({{ $item_permohonan_uji_klinik->nama_perwakilan_permohonan_uji_klinik }})
                            @else
                                ({{ mb_strtoupper($item_pasien->nama_pasien, 'UTF-8') }})
                            @endif
                        </div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-title">Yang Menerangkan</div>
                        <div class="signature-role">{{ $petugas_menerangkan ?? 'Petugas Pengambil Sampel' }}</div>
                        @php
                            $ttdPetugasConsent = !empty($use_pengambil_petugas)
                                ? ($item_permohonan_uji_klinik->signature_pengambil_sample_petugas ?? null)
                                : ($item_permohonan_uji_klinik->signature_persetujuan_petugas ?? null);
                        @endphp
                        @if (!empty($ttdPetugasConsent))
                            <div
                                style="height:60px; display:flex; align-items:center; justify-content:center; margin-left: 80px; margin-top: -10px; margin-bottom: -10px;">
                                <img src="{{ $ttdPetugasConsent }}"
                                    alt="TTD Petugas"
                                    style="max-width: 160px; max-height: 60px; object-fit: contain;" />
                            </div>
                        @else
                            <br>
                            <br>
                        @endif
                        <br>
                        <br>
                        <div class="signature-name">
                            ({{ $petugasConsentDisplay }})
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>

</html>

</html>
