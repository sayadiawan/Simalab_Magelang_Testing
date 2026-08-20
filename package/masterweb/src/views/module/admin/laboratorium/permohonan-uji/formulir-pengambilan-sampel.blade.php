<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Formulir Pengambilan Sampel</title>
    <style>
        @page {
            margin: 15mm 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-section {
            margin-bottom: 10px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }

        .info-label {
            display: table-cell;
            width: 150px;
            font-weight: bold;
        }

        .info-separator {
            display: table-cell;
            width: 10px;
        }

        .info-value {
            display: table-cell;
        }

        .sample-box {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .sample-header {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
            background: #f0f0f0;
            padding: 3px 5px;
        }

        .sample-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .sample-row {
            display: table-row;
        }

        .sample-col-left {
            display: table-cell;
            width: 48%;
            padding-right: 10px;
            vertical-align: top;
        }

        .sample-col-right {
            display: table-cell;
            width: 48%;
            padding-left: 10px;
            vertical-align: top;
            border-left: 1px solid #ccc;
        }

        .field {
            margin-bottom: 5px;
        }

        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .field-value {
            display: inline-block;
            border-bottom: 1px dotted #666;
            min-width: 150px;
            padding: 0 3px;
        }

        .field-value-block {
            border-bottom: 1px dotted #666;
            min-height: 15px;
            padding: 2px 3px;
        }

        .checkbox-group {
            margin-left: 10px;
        }

        .checkbox-item {
            margin-bottom: 3px;
        }

        .signature-section {
            margin-top: 25px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 50px;
        }

        .signature-line {
            border-top: 1px solid #000;
            display: inline-block;
            width: 120px;
            margin-top: 5px;
        }

        .signature-nip {
            font-size: 8pt;
            margin-top: 3px;
        }

        .data-pemeriksaan {
            margin-top: 8px;
        }

        .data-pemeriksaan-title {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .pemeriksaan-list {
            margin-left: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>FORMULIR PENGAMBILAN SAMPEL</h2>
    </div>

    <div class="info-section">
        @if ($wilayah_data)
            <div class="info-row">
                <div class="info-label">Wilayah</div>
                <div class="info-separator">:</div>
                <div class="info-value">
                    {{ $wilayah_data['kecamatan'] ?? '' }}
                    {{ $wilayah_data['desa'] ? '- ' . $wilayah_data['desa'] : '' }}
                    @if ($wilayah_data['detail_alamat'])
                        <br><small>{{ $wilayah_data['detail_alamat'] }}</small>
                    @endif
                </div>
            </div>
        @endif
        <div class="info-row">
            <div class="info-label">Pelanggan</div>
            <div class="info-separator">:</div>
            <div class="info-value">{{ $permohonan_uji->name_customer ?? '-' }}</div>
        </div>
    </div>

    @foreach ($samples as $index => $sample)
        <div class="sample-box">
            <div class="sample-header">{{ $index + 1 }}. SAMPEL</div>

            <div class="sample-grid">
                <div class="sample-row">
                    <!-- Left Column -->
                    <div class="sample-col-left">
                        <div class="field">
                            <span class="field-label">Nomor Sampel</span> :
                            <span class="field-value">{{ $sample->count_id ?? '-' }}</span>
                        </div>

                        <div class="field">
                            <span class="field-label">Kode Sampel</span> :
                            <span class="field-value">{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</span>
                        </div>

                        <div class="field">
                            <span class="field-label">Jenis Sampel</span> :
                            <span class="field-value">
                                {{ $sample->code_sample_type ?? '' }}
                                @if ($sample->name_sample_type)
                                    ({{ $sample->name_sample_type }})
                                @endif
                            </span>
                        </div>

                        <div class="field">
                            <span class="field-label" style="vertical-align: top;">Lokasi</span> :
                            <div class="field-value-block">

                            </div>
                        </div>

                        <div class="field">
                            <span class="field-label">Titik Sampel</span> :
                            <span class="field-value" style="width: 180px;">{!! $sample->titik_pengambilan ?? ($sample->name_customer_pdam ? $sample->name_customer_pdam . ' ' . ($sample->address_location_pdam ?? '') : '-') !!}</span>
                        </div>

                        <div class="field">
                            <span class="field-label">Pengambilan Tgl.</span> :
                            <span class="field-value" style="width: 80px;">
                                @if ($sample->datesampling_samples)
                                    {{ \Carbon\Carbon::parse($sample->datesampling_samples)->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>

                        <div class="field">
                            <span class="field-label">Jam</span> :
                            <span class="field-value" style="width: 80px;">
                                @if ($sample->datesampling_samples)
                                    {{ \Carbon\Carbon::parse($sample->datesampling_samples)->format('H:i') }}
                                @endif
                            </span>
                        </div>

                        <div class="field">
                            <span class="field-label">Pengiriman Tgl.</span> :
                            <span class="field-value" style="width: 80px;">
                                @if ($sample->date_sending)
                                    {{ \Carbon\Carbon::parse($sample->date_sending)->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>

                        <div class="field">
                            <span class="field-label">Jam</span> :
                            <span class="field-value" style="width: 80px;">
                                @if ($sample->date_sending)
                                    {{ \Carbon\Carbon::parse($sample->date_sending)->format('H:i') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="sample-col-right">
                        <div class="data-pemeriksaan">
                            <div class="data-pemeriksaan-title">Catatan</div>
                            <div style="border: 1px solid #ccc; padding: 5px; min-height: 80px; background: #f9f9f9;">
                                {{ $sample->note_samples ?? '-' }}
                            </div>
                        </div>

                        <div class="data-pemeriksaan" style="margin-top: 10px;">
                            <div style="font-weight: bold; margin: 5px 0;">Permintaan Pemeriksaan:</div>

                            @if ($sample->packet_name)
                                <!-- Jika menggunakan paket, tampilkan nama paket saja -->
                                <div
                                    style="padding: 8px; background: #e8f5e9; border-left: 3px solid #4caf50; margin-top: 5px;">
                                    <strong style="color: #2e7d32;">Paket:</strong> {{ $sample->packet_name }}
                                </div>
                            @else
                                <!-- Jika tidak pakai paket, tampilkan parameter list -->
                                <div style="font-size: 11px; line-height: 1.5;">
                                    @php
                                        $methodsByLab = $sample->methods_list->groupBy('kode_laboratorium');
                                    @endphp

                                    @foreach ($methodsByLab as $labCode => $labMethods)
                                        <div style="margin-bottom: 8px;">
                                            <strong style="text-decoration: underline;">
                                                @if (in_array($labCode, ['KMA', 'FKA', 'KIM']))
                                                    Kimia:
                                                @elseif ($labCode == 'MBI')
                                                    Mikrobiologi:
                                                @else
                                                    {{ $labCode }}:
                                                @endif
                                            </strong>
                                            <div style="margin-left: 10px; margin-top: 3px;">
                                                @foreach ($labMethods as $index => $method)
                                                    {{ $loop->iteration }}. {{ $method->params_method }}
                                                    @if (!$loop->last)
                                                        <br>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div style="margin-top: 15px; border-top: 1px solid #000; padding-top: 8px;">
        <div class="field">
            <span class="field-label" style="width: 100px; font-weight: bold;">Diterima Tgl.</span> :
            <span class="field-value" style="width: 200px;"></span>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">Penerima Sampel</div>
            <div style="margin-top: 60px;">
                <div class="signature-line"></div>
                <div class="signature-nip">NIP.</div>
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-title">Pengambil Sampel</div>
            <div style="margin-top: 60px;">
                <div class="signature-line"></div>
                <div class="signature-nip">NIP.</div>
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-title">Mengetahui :<br>{{ $permohonan_uji->jabatan_pelanggan ?? 'Pelanggan' }}</div>
            @if (!empty($permohonan_uji->signature_pelanggan))
                <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                    <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_pelanggan) }}" alt="TTD Pelanggan" style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                </div>
            @endif
            <div style="margin-top: 5px;">
                @if (!empty($permohonan_uji->nama_pelanggan))
                    <div class="signature-name">{{ $permohonan_uji->nama_pelanggan }}</div>
                    <div class="signature-line"></div>
                @else
                    <div class="signature-line"></div>
                @endif
                <div class="signature-nip">NIP. {{ $permohonan_uji->nip_pelanggan ?? '' }}</div>
            </div>
        </div>
    </div>
</body>

</html>
