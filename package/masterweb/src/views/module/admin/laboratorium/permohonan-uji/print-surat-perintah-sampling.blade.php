<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Sampling</title>
    <style>
        @page {
            margin: 1.5cm 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }

        .kop-surat {
            width: 100%;
            margin-bottom: 15px;
        }

        .kop-surat img {
            width: 100%;
            height: auto;
        }

        .document-title {
            text-align: center;
            margin: 20px 0 15px 0;
        }

        .document-title h1 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 8px;
        }

        .document-number {
            font-size: 11pt;
            margin-top: 5px;
        }

        .content {
            text-align: justify;
            margin: 12px 0;
            font-size: 11pt;
        }

        .content p {
            margin-bottom: 10px;
        }

        .details-table {
            width: 100%;
            margin: 12px 0;
            font-size: 11pt;
        }

        .details-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .details-table .label {
            width: 180px;
        }

        .details-table .separator {
            width: 15px;
        }

        .sampling-location {
            background: #f9f9f9;
            border: 2px solid #000;
            padding: 10px;
            margin: 12px 0;
        }

        .sampling-location h3 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }

        .qr-code {
            text-align: center;
            margin: 15px 0;
            padding: 12px;
            border: 1px dashed #000;
        }

        .qr-code .qr-label {
            margin-top: 8px;
            font-size: 9pt;
            font-style: italic;
        }

        .petunjuk {
            margin: 12px 0;
        }

        .petunjuk ol {
            margin-left: 18px;
            margin-top: 6px;
        }

        .petunjuk li {
            margin-bottom: 4px;
        }

        .signature-section {
            margin-top: 30px;
        }

        .signature-table {
            width: 100%;
        }

        .signature-box {
            text-align: center;
            vertical-align: top;
        }

        .signature-box .title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .signature-box .name {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-box .nip {
            margin-top: 2px;
            font-size: 10pt;
        }

        .footer {
            margin-top: 15px;
            font-size: 9pt;
            font-style: italic;
            color: #666;
        }
    </style>
</head>

<body>
    <!-- Kop Surat -->
    <div class="kop-surat">
        <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" alt="Kop Surat">
    </div>

    <!-- Judul Surat -->
    <div class="document-title">
        <h1>SURAT PERINTAH PENGAMBILAN SAMPEL</h1>
        <div class="document-number">
            Nomor: SPPS/{{ date('Y') }}
        </div>
    </div>

    <!-- Isi Surat -->
    <div class="content">
        <p>Berdasarkan Permohonan Uji yang telah diterima, dengan ini kami menugaskan petugas laboratorium untuk
            melakukan pengambilan sampel dengan rincian sebagai berikut:</p>
    </div>

    <!-- Detail Permohonan Uji -->
    <table class="details-table">
        <tr>
            <td class="label">Tanggal Permohonan</td>
            <td class="separator">:</td>
            <td class="value">
                {{ \Carbon\Carbon::parse($permohonan_uji->date_permohonan_uji)->isoFormat('DD MMMM YYYY') }}</td>
        </tr>
        <tr>
            <td class="label">Nama Pelanggan/Instansi</td>
            <td class="separator">:</td>
            <td class="value"><strong>{{ $permohonan_uji->customer->name_customer ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Alamat Pelanggan</td>
            <td class="separator">:</td>
            <td class="value">{{ $permohonan_uji->customer->address_customer ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Contact Person</td>
            <td class="separator">:</td>
            <td class="value">{{ $permohonan_uji->customer->cp_customer ?? '-' }}</td>
        </tr>
    </table>

    <!-- Lokasi Sampling -->
    @if (!empty($wilayah_data))
        <div class="sampling-location">
            <h3>LOKASI PENGAMBILAN SAMPEL</h3>
            <table class="details-table">
                @if (!empty($wilayah_data['detail_alamat']))
                    <tr>
                        <td class="label">Alamat Detail</td>
                        <td class="separator">:</td>
                        <td class="value"><strong>{{ $wilayah_data['detail_alamat'] }}</strong></td>
                    </tr>
                @endif
                @if (!empty($wilayah_data['desa']))
                    <tr>
                        <td class="label">Desa/Kelurahan</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $wilayah_data['desa'] }}</td>
                    </tr>
                @endif
                @if (!empty($wilayah_data['kecamatan']))
                    <tr>
                        <td class="label">Kecamatan</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $wilayah_data['kecamatan'] }}</td>
                    </tr>
                @endif
                @if (!empty($wilayah_data['kabupaten']))
                    <tr>
                        <td class="label">Kabupaten/Kota</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $wilayah_data['kabupaten'] }}</td>
                    </tr>
                @endif
                @if (!empty($wilayah_data['provinsi']))
                    <tr>
                        <td class="label">Provinsi</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $wilayah_data['provinsi'] }}</td>
                    </tr>
                @endif
            </table>
        </div>
    @endif

    <!-- Catatan -->
    @if ($permohonan_uji->catatan)
        <div class="content">
            <p><strong>Catatan:</strong><br>{{ $permohonan_uji->catatan }}</p>
        </div>
    @endif

    <!-- QR Code -->
    @if ($qrCodeBase64)
        <div class="qr-code">
            <img src="{{ $qrCodeBase64 }}" alt="QR Code" style="width: 120px; height: 120px;">
            <div class="qr-label">Scan QR Code untuk verifikasi</div>
        </div>
    @else
        <div class="qr-code">
            <div style="padding: 20px; border: 2px solid #000; display: inline-block;">
                <strong>Verifikasi</strong>
            </div>
            <div class="qr-label">Referensi dokumen</div>
        </div>
    @endif

    <!-- Petunjuk Pelaksanaan -->
    <div class="content petunjuk">
        <p><strong>Petunjuk Pelaksanaan:</strong></p>
        <ol>
            <li>Petugas wajib membawa surat perintah ini saat melakukan pengambilan sampel</li>
            <li>Pengambilan sampel dilakukan sesuai dengan SOP yang berlaku</li>
            <li>Sampel yang telah diambil segera dibawa ke laboratorium untuk diproses</li>
            <li>Petugas wajib melaporkan hasil pengambilan sampel</li>
        </ol>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td width="50%" class="signature-box">
                    <div class="title">Petugas Sampling</div>
                    <div class="name">
                        (......................................)
                    </div>
                    <div class="nip">NIP. ....................................</div>
                </td>
                <td width="50%" class="signature-box">
                    <div class="title">Kepala Kesmas</div>
                    <div class="name">
                        (......................................)
                    </div>
                    <div class="nip">NIP. ....................................</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>* Surat ini dicetak secara otomatis dari Sistem Informasi Laboratorium Kesehatan Daerah SIMLAB Testing
        </p>
        <p>* Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('DD MMMM YYYY, HH:mm') }} WIB</p>
    </div>
</body>

</html>
