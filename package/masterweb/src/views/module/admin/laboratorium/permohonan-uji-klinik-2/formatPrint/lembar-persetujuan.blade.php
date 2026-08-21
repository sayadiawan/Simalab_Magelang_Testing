<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>LEMBAR PERSETUJUAN</title>
    <link rel="shortcut icon" href="">
    <style>
        .starter-template {
            text-align: center;
        }

        table>tr>td {
            /* cell-padding: 5px !important; */
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
            font-size: 5px;
            text-align: center
        }

        .result {
            border-collapse: collapse;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
        }

        @page {
            size: A4;
            margin: 15mm 20mm;
        }

        @font-face {
            font-family: "source_sans_proregular";
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            font-size: 12px;
        }

        body {
            font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
            font-size: 12px;
            text-align: justify;
            text-justify: inter-word;
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

        .border {
            border: 1.5px solid black;
        }

        .v-align-top {
            vertical-align: top;
        }

        .checkbox {
            height: 10px;
            position: relative;
            bottom: 5px;
        }

        .blue-header {
            background-color: #3a95b5;
            color: white;
            font-weight: bold;
            letter-spacing: 1px;
            padding-left: 4px;
            height: 10px;
        }

        .text-center {
            text-align: center;
        }

        .td-header {
            font-family: "Times New Roman", Times, serif !important;
            font-weight: bold;
            text-align: center;
        }

        .table-consent td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 4px 2px 4px 2px;
            font-size: 12.4px;
        }

        .table-clear td {
            border: 0px;
            padding: 0px;
        }

        .td-form-no {
            font-family: "Times New Roman", Times, serif !important;
            text-align: right;
        }

        /* Signature styles */
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding: 0 50px;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .signature-role {
            font-size: 10px;
            margin-bottom: 10px;
        }

        .signature-name {
            margin-top: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div style="margin-top: 0; padding: 0;">
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 20px; border: none;">
            <tr>
                <td
                    style="font-size: 11px; font-family: 'Times New Roman', Times, serif; text-align: left; border: none; padding: 0;">
                    449.5/ 9 /Form / {{ \Carbon\Carbon::now()->year }}
                </td>
            </tr>
        </table>

        <div style="font-family: 'Times New Roman', Times, serif; margin-bottom: 25px;">
            <div style="text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 8px;">
                SURAT PERSETUJUAN PENGIRIMAN HASIL LABORATORIUM MELALUI WHATSAPP
            </div>
        </div>

        <div style="font-size: 12px; font-family: 'Times New Roman', Times, serif;">
            <p style="margin: 0 0 15px 0;">Saya yang bertandatangan di bawah ini:</p>

            <table width="100%" cellspacing="0" cellpadding="4" style="margin-bottom: 18px; border: none;">
                @if ($item_permohonan_uji_klinik->nama_perwakilan_permohonan_uji_klinik)
                    @php
                        $umurPerwakilan = \Smt\Masterweb\Helpers\DateHelper::calcAge(
                            $item_permohonan_uji_klinik->tanggal_lahir_perwakilan_permohonan_uji_klinik,
                        );
                    @endphp
                    <tr>
                        <td width="28%" style="border: none; padding: 4px 8px 4px 0;">Nama</td>
                        <td width="2%" style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_permohonan_uji_klinik->nama_perwakilan_permohonan_uji_klinik ?? '.................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Jenis Kelamin</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_permohonan_uji_klinik->gender_perwakilan_permohonan_uji_klinik == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Umur/Tanggal Lahir</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">{{ $umurPerwakilan . ' Tahun' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Alamat</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_permohonan_uji_klinik->alamat_perwakilan_permohonan_uji_klinik ?? '.................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">No. WhatsApp</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_permohonan_uji_klinik->alamat_perwakilan_permohonan_uji_klinik ?? '.................................................' }}
                        </td>
                    </tr>
                @else
                    <tr>
                        <td width="28%" style="border: none; padding: 4px 8px 4px 0;">Nama</td>
                        <td width="2%" style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">{{ $item_pasien->nama_pasien }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Jenis Kelamin</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Umur/Tanggal Lahir</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik . ' Tahun' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Alamat</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_pasien) }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">No. WhatsApp</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_pasien->phone_pasien ?? '.................................................' }}
                        </td>
                    </tr>
                @endif
            </table>

            @if ($item_permohonan_uji_klinik->nama_perwakilan_permohonan_uji_klinik)
                <p style="margin: 15px 0; text-align: justify; line-height: 1.7;">
                    Menyatakan dengan sesungguhnya diri saya sendiri sebagai 
                    @if($item_permohonan_uji_klinik->status_hubungan_perwakilan_permohonan_uji_klinik && in_array($item_permohonan_uji_klinik->status_hubungan_perwakilan_permohonan_uji_klinik, ['Orang Tua', 'Suami', 'Istri', 'Anak', 'Wali']))
                        {{ $item_permohonan_uji_klinik->status_hubungan_perwakilan_permohonan_uji_klinik }}
                    @elseif($item_permohonan_uji_klinik->status_hubungan_perwakilan_permohonan_uji_klinik == 'Lainnya')
                        @if(!empty($item_permohonan_uji_klinik->status_hubungan_lainnya_permohonan_uji_klinik))
                            {{ $item_permohonan_uji_klinik->status_hubungan_lainnya_permohonan_uji_klinik }}
                        @else
                            ........................................
                        @endif
                    @else
                        *Orang Tua / *Suami / *Istri / *Anak / *Wali atau ........................................
                    @endif
                    dari,
                </p>

                <table width="100%" cellspacing="0" cellpadding="4" style="margin-bottom: 18px; border: none;">
                    <tr>
                        <td width="28%" style="border: none; padding: 4px 8px 4px 0;">Nama</td>
                        <td width="2%" style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">{{ $item_pasien->nama_pasien }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Jenis Kelamin</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Umur/Tanggal Lahir</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">
                            {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik . ' Tahun' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 4px 8px 4px 0;">Alamat</td>
                        <td style="border: none; padding: 4px 8px;">:</td>
                        <td style="border: none; padding: 4px 0;">{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_pasien) }}</td>
                    </tr>
                </table>
            @endif

            <p>
                Dengan ini menyatakan bahwa :
            </p>

            <ol>
                <li>
                    Saya meminta dan meyetujui hasil pemeriksaan laboratorium saya dikirimkan melalui aplikasi WhatsApp ke nomor yang telah saya cantumkan diatas.
                </li>
                <li>
                    Saya memahami bahwa pengiriman hasil melalui media elektronik (WhatsApp) memiliki potensi risiko, termasuk :
                    <ol type="a">
                        <li>Kebocoran data,</li>
                        <li>Akses oleh pihak yang tidak berwenang,</li>
                        <li>Kesalahan pengirian akibat nomor salah</li>
                    </ol>
                </li>
                <li>
                    Saya menyadari bahwa laboratorium akan berusaha menjaga kerahasiaan dan keamanan data hasil pemeriksaan, namun tidak bertanggung jawab atas risiko yang timbul setelah terkirim ke nomor WhatsApp yang saya cantumkan.
                </li>
                <li>
                    Saya tetap dapat mengambil hasil cetak (hardcopy) pemeriksaan laboratorium secara langsung di laboratorium.
                </li>
            </ol>
            <p>
                Dengan menandatangani dokumen ini, saya menyatakan telah membaca, memahami, dan menyetujui isi pernyataan diatas.
            </p>

            <!-- Signature Area -->
            <div style="display: table; width: 100%; margin-top: 50px; margin-bottom: 25px;">
                <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top; padding: 0 20px;">
                    <div style="font-size: 12px; margin-bottom: 15px; text-align: right; padding-right: 20px; color: white;">
                        --
                    </div>
                    <div style="font-weight: bold; font-size: 13px; margin-bottom: 6px;">Mengetahui</div>
                    <div style="font-size: 12px; margin-bottom: 12px;">Pasien/Wali</div>
                    @if (!empty($item_permohonan_uji_klinik->signature_persetujuan_pasien))
                        <div
                            style="height: 75px; display: flex; align-items: center; justify-content: center; margin: 12px 0;">
                            <img src="data:image/png;base64,{{ base64_encode($item_permohonan_uji_klinik->signature_persetujuan_pasien) }}"
                                alt="TTD Pasien" style="max-width: 160px; max-height: 65px; object-fit: contain;" />
                        </div>
                    @else
                        <div style="height: 75px;"></div>
                    @endif
                    <div style="font-weight: bold; font-size: 12px; margin-top: 12px;">
                        @if ($item_permohonan_uji_klinik->nama_perwakilan_permohonan_uji_klinik)
                            ({{ $item_permohonan_uji_klinik->nama_perwakilan_permohonan_uji_klinik }})
                        @else
                            ({{ $item_pasien->nama_pasien }})
                        @endif
                    </div>
                </div>

                <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top; padding: 0 20px;">
                    <div style="font-size: 12px; margin-bottom: 15px; text-align: right; padding-right: 20px;">
                        —, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}
                    </div>
                    <div style="font-weight: bold; font-size: 13px; margin-bottom: 6px;">Yang Menerangkan</div>
                    <div style="font-size: 12px; margin-bottom: 12px;">{{ $petugas_menerangkan ?? 'Petugas Registrasi' }}</div>
                    @php
                        $ttdPetugasConsent = !empty($use_pengambil_petugas)
                            ? ($item_permohonan_uji_klinik->signature_pengambil_sample_petugas ?? null)
                            : ($item_permohonan_uji_klinik->signature_persetujuan_petugas ?? null);
                    @endphp
                    @if (!empty($ttdPetugasConsent))
                        <div
                            style="height: 75px; display: flex; align-items: center; justify-content: center; margin: 12px 0;">
                            <img src="data:image/png;base64,{{ base64_encode($ttdPetugasConsent) }}"
                                alt="TTD Petugas" style="max-width: 160px; max-height: 65px; object-fit: contain;" />
                        </div>
                    @else
                        <div style="height: 75px;"></div>
                    @endif
                    <div style="font-weight: bold; font-size: 12px; margin-top: 12px;">
                        @if (!empty($petugas))
                            ({{ $petugas }})
                        @else
                            (..................................)
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
    @if (isset($signOption) and $signOption == 1)
        <div style="position: fixed; bottom: 0px; text-align: left;">
            <p style="font-size: 12px; margin: 0; padding: 0;"><i>Dokumen ini ditandatangani secara elektronik menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi Negara</i></p>
        </div>
    @endif
</body>

</html>
