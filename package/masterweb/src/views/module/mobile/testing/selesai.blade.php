<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sampel Selesai</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 12px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
            color: white;
            padding: 22px 20px 26px;
            border-radius: 18px;
            margin-bottom: 18px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 6px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .logout-btn {
            position: absolute;
            top: 18px;
            right: 20px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.18);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.28);
        }

        .card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 10px 35px rgba(29, 37, 71, 0.07);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #222;
            text-align: right;
            max-width: 55%;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .alert-success {
            background: #e6fffa;
            border: 1px solid #b2f5ea;
            color: #0c6c62;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th,
        td {
            padding: 10px 8px;
            border: 1px solid #f0f0f0;
            text-align: left;
        }

        th {
            background: #fafafa;
            font-size: 13px;
            letter-spacing: 0.2px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            margin-bottom: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
            color: white;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #444;
        }

        .note {
            font-size: 13px;
            color: #555;
            margin-top: 8px;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <form method="POST" action="{{ route('mobile.testing.logout') }}"
                style="position: absolute; top: 18px; right: 20px;">
                @csrf
                <button type="submit" class="logout-btn">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </form>
            <h1>Pengujian Selesai</h1>
            <p>Laboratorium {{ $lab->kode_laboratorium == 'KIM' ? 'Kimia' : 'Mikrobiologi' }}</p>
        </div>

        <div class="card">
            <div class="alert alert-success">
                🎉 Seluruh tahapan pengujian untuk sampel ini telah selesai. Untuk melakukan perubahan,
                silakan gunakan platform website (desktop) agar seluruh data tetap terjaga konsistensinya.
            </div>
            <div class="info-row">
                <span class="info-label">ID Sample</span>
                <span class="info-value">{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Permohonan</span>
                <span class="info-value">{{ $sample->permohonan_uji_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jenis Sample</span>
                <span
                    class="info-value">{{ $sample->name_sample_type ?? ($sample->sampletype->name_sample_type ?? '-') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Laboratorium</span>
                <span class="info-value">{{ $lab->nama_laboratorium }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Validasi</span>
                <span class="info-value">
                    {{ $validationRecord && $validationRecord->stop_date ? \Carbon\Carbon::parse($validationRecord->stop_date)->isoFormat('D MMMM Y') : '-' }}
                </span>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 12px;">Ringkasan Parameter</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Parameter</th>
                            <th>Hasil</th>
                            <th>Batas Syarat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($laboratoriummethods as $laboratoriummethod)
                            @if (count($laboratoriummethod['detail']) == 0)
                                <tr>
                                    <td>{{ $no }}</td>
                                    <td>{!! rubahNilaikeForm($laboratoriummethod->name_report) !!}</td>
                                    <td>
                                        {!! cek_hasil_color(
                                            isset($laboratoriummethod->hasil)
                                                ? $laboratoriummethod->hasil
                                                : (isset($laboratoriummethod->equal)
                                                    ? $laboratoriummethod->equal
                                                    : '-'),
                                            $laboratoriummethod->min,
                                            $laboratoriummethod->max,
                                            $laboratoriummethod->equal,
                                            'result_output_method_' . $laboratoriummethod->method_id,
                                            $laboratoriummethod->offset_baku_mutu,
                                            $laboratoriummethod->number_format ?? 'en'
                                        ) !!}
                                    </td>
                                    <td>{!! rubahNilaikeHtml($laboratoriummethod->nilai_baku_mutu) ?? '-' !!}</td>
                                </tr>
                            @else
                                <tr>
                                    <td rowspan="{{ count($laboratoriummethod['detail']) + 1 }}"
                                        style="vertical-align: top;">{{ $no }}</td>
                                    <td colspan="3"><strong>{!! rubahNilaikeHtml($laboratoriummethod->name_report) !!}</strong></td>
                                </tr>
                                @foreach ($laboratoriummethod['detail'] as $detail)
                                    <tr>
                                        <td>{!! rubahNilaikeHtml($detail->name_sample_result_detail) !!}</td>
                                        <td>
                                            {!! cek_hasil_color(
                                                isset($detail->hasil)
                                                    ? $detail->hasil
                                                    : (isset($detail->equal_sample_result_detail)
                                                        ? $detail->equal_sample_result_detail
                                                        : '-'),
                                                $detail->min_sample_result_detail,
                                                $detail->max_sample_result_detail,
                                                $detail->equal_sample_result_detail,
                                                'result_output_method_' . $detail->id_sample_result_detail,
                                                $detail->offset_baku_mutu,
                                                $detail->number_format ?? 'en'
                                            ) !!}
                                        </td>
                                        <td>{!! rubahNilaikeHtml($detail->nilai_sample_result_detail) !!}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @php $no++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="note">* Data hasil bersumber langsung dari tahap pengesahan & validasi terakhir.</p>
        </div>

        @php
            $isKimia = $lab->kode_laboratorium == 'KIM';
            
            // Cek apakah jenis sample adalah "Makanan/Minuman/Lainnya"
            $isMakananMinumanLainnya = isset($sample->name_sample_type) && 
                $sample->name_sample_type === 'Makanan/Minuman/Lainnya';

            // Jika jenis sample adalah "Makanan/Minuman/Lainnya", gunakan format print-kimia
            if ($isMakananMinumanLainnya) {
                // URL print-kimia untuk format makanan/minuman/lainnya
                $previewUrl = url(
                    'elits-release/print-kimia/' .
                        $sample->permohonan_uji_id .
                        '/' .
                        $sample->typesample_samples .
                        '?agenda=&signOption=0',
                );
            } else {
                $kimiaUrl = url(
                    'elits-release/printLHU/' . $sample->id_samples . '/' . $lab->id_laboratorium . '?agenda=&signOption=0',
                );
                $mikroBase = url(
                    'elits-release/print-mikro/' . $sample->permohonan_uji_id . '/' . $sample->typesample_samples,
                );
                $mikroQuery = '?agenda=&signOption=0&printall=on';
                if (!empty($sample->jenis_makanan_id)) {
                    $mikroQuery .= '&jenis_makanan_id=' . $sample->jenis_makanan_id;
                }
                try {
                    $mikroSampleIds = \Smt\Masterweb\Models\Sample::query()
                        ->where('tb_samples.permohonan_uji_id', $sample->permohonan_uji_id)
                        ->join('tb_sample_method', function ($join) {
                            $join
                                ->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                                ->whereNull('tb_sample_method.deleted_at');
                        })
                        ->join('ms_laboratorium', function ($join) {
                            $join
                                ->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                                ->whereNull('ms_laboratorium.deleted_at');
                        })
                        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
                        ->pluck('tb_samples.id_samples')
                        ->unique()
                        ->toArray();
                    foreach ($mikroSampleIds as $sid) {
                        $mikroQuery .= '&printSamples[]=' . $sid;
                    }
                } catch (\Throwable $e) {
                    $mikroQuery .= '&printSamples[]=' . $sample->id_samples;
                }
                $mikroUrl = $mikroBase . $mikroQuery;
                $previewUrl = $isKimia ? $kimiaUrl : $mikroUrl;
            }
        @endphp

        <div class="card">
            <h3 style="margin-bottom: 12px;">View Hasil / PDF</h3>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <span style="font-weight:600;color:#555;">Pratinjau PDF Hasil Uji</span>
                <a href="{{ $previewUrl }}" target="_blank" class="btn btn-primary"
                    style="width:auto;padding:10px 18px;">
                    <span>📄</span>
                    <span>Buka PDF</span>
                </a>
            </div>
            <div style="width:100%;height:360px;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden;">
                <iframe src="{{ $previewUrl }}" loading="lazy" style="width:100%;height:100%;border:none;"></iframe>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 12px;">Tindakan Selanjutnya</h3>
            <a href="{{ $web_link }}" target="_blank" class="btn btn-primary">
                <span>🌐</span>
                <span>Buka Versi Website</span>
            </a>
            <a href="{{ url('/mobile/testing') }}" class="btn btn-secondary">
                <span>🏠</span>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Selesai!',
                html: 'Seluruh proses untuk sample <strong>{{ $sample->codesample_samples }}</strong> sudah selesai. ' +
                    'Edit lanjutan hanya tersedia di platform website.',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#0072ff'
            });
        });
    </script>
</body>

</html>
