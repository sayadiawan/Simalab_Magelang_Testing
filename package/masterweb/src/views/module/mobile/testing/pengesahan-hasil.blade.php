<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pengesahan Hasil</title>
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
            padding: 10px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            text-align: center;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ececec;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .parameter-card {
            background: #fafafa;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #ff9966;
        }

        .parameter-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }

        .result-text {
            font-size: 14px;
            color: #444;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff7167;
            box-shadow: 0 0 0 3px rgba(255, 113, 103, 0.15);
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead {
            background: #fff1ed;
        }

        thead th {
            padding: 10px;
            text-align: left;
            color: #ff5e62;
            font-weight: 600;
        }

        tbody td {
            padding: 10px;
            border-top: 1px solid #f1f1f1;
            vertical-align: top;
        }

        .pdf-preview {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e3f2fd;
            padding: 15px;
        }

        .pdf-preview iframe {
            width: 100%;
            height: 400px;
            border: none;
            border-radius: 10px;
            background: #f5f5f5;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <div class="header" style="position: relative;">
            <form method="POST" action="{{ route('mobile.testing.logout') }}"
                style="position: absolute; top: 20px; right: 20px;">
                @csrf
                <button type="submit" class="logout-btn">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </form>
            <h1>PENGESAHAN HASIL</h1>
            <p>Laboratorium {{ $lab->kode_laboratorium == 'KIM' ? 'Kimia' : 'Mikrobiologi' }}</p>
        </div>

        <div class="card">
            <h3 class="card-title">📋 Informasi Sampel</h3>
            <div class="info-row">
                <span>ID Sample</span>
                <span>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</span>
            </div>
            <div class="info-row">
                <span>Jenis Sampel</span>
                <span>{{ $sample->jenisSampelDisplay() }}</span>
            </div>
            <div class="info-row">
                <span>Laboratorium</span>
                <span>{{ $lab->nama_laboratorium }}</span>
            </div>
            <div class="info-row">
                <span>Tanggal Pengambilan</span>
                <span>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y HH:mm') }}</span>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">🔬 Ringkasan Parameter</h3>
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
                                    <td>{!! $laboratoriummethod->name_report !!}</td>
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
                                    <td>{!! rubahNilaikeForm($laboratoriummethod->nilai_baku_mutu) ?? '-' !!}</td>
                                </tr>
                            @else
                                <tr>
                                    <td style="vertical-align: top;"
                                        rowspan="{{ count($laboratoriummethod['detail']) + 1 }}">
                                        {{ $no }}
                                    </td>
                                    <td colspan="3"><strong>{!! $laboratoriummethod->name_report !!}</strong></td>
                                </tr>
                                @foreach ($laboratoriummethod['detail'] as $detail)
                                    <tr>
                                        <td>{!! $detail->name_sample_result_detail !!}</td>
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
                                        <td>{!! rubahNilaikeForm($detail->nilai_sample_result_detail) !!}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @php $no++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
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
            <h3 class="card-title">🗂️ View Hasil / PDF</h3>
            <div class="pdf-preview">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-weight: 600; color: #555;">Pratinjau PDF Hasil Uji</span>
                    <a href="{{ $previewUrl }}" target="_blank" class="btn btn-primary"
                        style="width: auto; padding: 10px 18px;">
                        <span>📄</span>
                        <span>Buka PDF</span>
                    </a>
                </div>
                <iframe src="{{ $previewUrl }}" loading="lazy"></iframe>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">✅ Validasi Pengesahan</h3>

            @if ($pengesahan_hasil)
                <div class="alert alert-success">
                    Pengesahan sudah dilakukan pada
                    <strong>{{ \Carbon\Carbon::parse($pengesahan_hasil->pengesahan_hasil_date)->isoFormat('D MMMM Y') }}</strong>.
                    Anda dapat memperbarui tanggal jika dibutuhkan.
                </div>
            @endif

            <form id="pengesahan-form" action="{{ route('mobile.testing.storePengesahanHasil', $sample->id_samples) }}"
                method="POST">
                @csrf
                <input type="hidden" name="lab_id" value="{{ $lab_id }}">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="pengesahan_hasil" style="font-weight: 600; color: #333; font-size: 14px;">Tanggal
                        Pengesahan</label>
                    <input type="text" class="form-control" id="pengesahan_hasil" name="pengesahan_hasil"
                        value="{{ $default_pengesahan_date }}">
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>✔</span>
                    <span>Validasi Hasil</span>
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-form@4.3.0/dist/jquery.form.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            flatpickr("#pengesahan_hasil", {
                dateFormat: "d/m/Y",
                allowInput: true,
                defaultDate: "{{ $default_pengesahan_date }}"
            });

            $('#pengesahan-form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message ||
                                    'Pengesahan hasil berhasil disimpan.',
                                icon: "success"
                            }).then(function() {
                                if (response.url_redirect) {
                                    window.location.href = response.url_redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.message ||
                                    'Terjadi kesalahan saat menyimpan data.',
                                icon: "warning"
                            });
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON || {};
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: err.message ||
                                'Terjadi kesalahan saat menyimpan data.'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
