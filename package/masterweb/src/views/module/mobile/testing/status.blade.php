<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Status Pengujian Sample</title>
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
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            text-align: center;
        }

        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 5px;
        }

        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.warning {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-badge.secondary {
            background: #e2e3e5;
            color: #383d41;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .step.active .step-circle {
            border-color: #0b3a5c;
            background: #0b3a5c;
            color: white;
        }

        .step.done .step-circle {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }

        .step.locked .step-circle {
            border-color: #e0e0e0;
            background: #f5f5f5;
            color: #999;
        }

        .step-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .step.active .step-label {
            color: #0b3a5c;
            font-weight: 600;
        }

        .step.done .step-label {
            color: #28a745;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group label .required {
            color: #dc3545;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }

        .form-control:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .sample-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #0b3a5c;
        }

        .sample-card h4 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }

        .sample-card .info-row {
            display: flex;
            margin-bottom: 5px;
            font-size: 13px;
        }

        .sample-card .info-row label {
            font-weight: 600;
            min-width: 100px;
            color: #666;
            margin-bottom: 0;
        }

        .sample-card .info-row span {
            color: #333;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"],
        .checkbox-item input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-item label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .signature-pad {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            margin: 10px auto;
            padding: 10px;
            max-width: 420px;
            width: 100%;
            display: flex;
            justify-content: center;
            background: #fafafa;
        }

        .signature-pad canvas {
            width: 100% !important;
            max-width: 400px !important;
            height: 200px !important;
            border-radius: 10px;
            cursor: crosshair !important;
            background: #fff;
        }

        .btn-clear {
            background: #ffc107;
            color: #333;
            padding: 8px 15px;
            font-size: 14px;
            margin-top: 10px;
        }

        .hidden {
            display: none;
        }

        .logout-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #dc3545;
            color: white;
            border: none;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Status Pengujian Sample</h1>
            <p>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!} - {{ $sample->sampletype->name_sample_type ?? '-' }}</p>
        </div>

        <!-- Progress Steps -->
        <div class="card">
            <div class="progress-steps">
                <div class="step {{ $step_penerima_done ? 'done' : (!$step_penerima_done ? 'active' : 'locked') }}">
                    <div class="step-circle">{{ $step_penerima_done ? '✓' : '1' }}</div>
                    <div class="step-label">Penerima<br>Sample</div>
                </div>
                <div class="step {{ $step_koordinator_done ? 'done' : ($step_penerima_done ? 'active' : 'locked') }}">
                    <div class="step-circle">{{ $step_koordinator_done ? '✓' : '2' }}</div>
                    <div class="step-label">Koordinator<br>Kesmas</div>
                </div>
                <div class="step {{ $step_analis_done ? 'done' : ($step_koordinator_done ? 'active' : 'locked') }}">
                    <div class="step-circle">{{ $step_analis_done ? '✓' : '3' }}</div>
                    <div class="step-label">Analis</div>
                </div>
            </div>
        </div>

        <!-- Sample Info -->
        <div class="card">
            <h3 style="margin-bottom: 15px; font-size: 16px;">📋 Informasi Sample ({{ count($group_samples) }} sample)
            </h3>
            @foreach ($group_samples as $s)
                <div class="sample-card">
                    <h4>{{ $s->codesample_samples }}</h4>
                    <div class="info-row">
                        <label>Jenis:</label>
                        <span>{{ $s->sampletype->name_sample_type ?? '-' }}</span>
                    </div>
                    @if ($s->titik_pengambilan)
                        <div class="info-row">
                            <label>Titik Pengambilan:</label>
                            <span>{{ $s->titik_pengambilan }}</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <label>Parameter:</label>
                        <span>
                            @foreach ($s->samplemethod as $sm)
                                {{ $sm->method->params_method ?? '-' }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Form Penerimaan Sample -->
        <form id="form-penerimaan" method="POST"
            action="{{ route('mobile.testing.storePenerimaan', $sample->id_samples) }}">
            @csrf
            <input type="hidden" name="current_step" id="current_step"
                value="{{ !$step_penerima_done ? 1 : (!$step_koordinator_done ? 2 : 3) }}">
            <input type="hidden" name="lab_type" id="lab_type" value="">

            <!-- STEP 1: Penerima Sampel -->
            @if (!$step_penerima_done)
                <!-- Single form for all labs (KIM and MBI) -->
                <div class="card" id="step-1">
                    <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 1: Penerima Sampel</h3>

                    <div class="form-group">
                        <label for="penerima_sampel">Nama Penerima <span class="required">*</span></label>
                        <select name="penerima_sampel" id="penerima_sampel" class="form-control" required>
                            <option value="">-- Pilih Penerima Sampel --</option>
                            @foreach ($penerima_sampel_list as $penerima)
                                <option value="{{ $penerima }}">{{ $penerima }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="penerima_tanggal">Tanggal Penerimaan <span class="required">*</span></label>
                        <input type="text" name="penerima_tanggal" id="penerima_tanggal" class="form-control"
                            placeholder="dd/mm/yyyy HH:mm" value="{{ $penerima_tanggal_default ?? '' }}" required>
                    </div>

                    @if (!$use_tte)
                        <div class="form-group">
                            <label>Tanda Tangan Penerima</label>
                            <div class="signature-pad">
                                <canvas id="penerima-signature-pad"
                                    style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                            </div>
                            <button type="button" class="btn btn-clear" id="clear-penerima-signature">🗑️
                                Bersihkan</button>
                            <input type="hidden" name="penerima_signature" id="penerima_signature" value="">
                            <input type="hidden" name="penerima_signature_type" value="canvas">
                        </div>
                    @endif

                    <!-- Sample Details for ALL samples (KIM and MBI) -->
                    @foreach ($group_samples_all as $s)
                        @php
                            // Get existing penerimaan for this sample - check for any lab (KIM or MBI)
                            $existing = null;
                            $s_lab = null;
                            if ($s->samplemethod && $s->samplemethod->count() > 0) {
                                foreach ($s->samplemethod as $sm) {
                                    if (
                                        $sm->laboratorium &&
                                        in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])
                                    ) {
                                        $s_lab = $sm->laboratorium;
                                        $existing = \Smt\Masterweb\Models\PenerimaanSample::where(
                                            'sample_id',
                                            $s->id_samples,
                                        )
                                            ->where('laboratorium_id', $s_lab->id_laboratorium)
                                            ->first();
                                        if ($existing) {
                                            break;
                                        }
                                    }
                                }
                            }

                            // Default pengawetan_oleh berdasarkan is_sampling dari permohonan uji
                            if ($existing && $existing->pengawetan_oleh) {
                                $pengawetan_oleh = $existing->pengawetan_oleh;
                            } else {
                                // Default: jika is_sampling = 1 maka Laboratorium, jika 0 maka Pelanggan
                                $is_sampling = $s->permohonanuji
                                    ? $s->permohonanuji->is_sampling
                                    : ($sample->permohonanuji
                                        ? $sample->permohonanuji->is_sampling
                                        : 0);
                                $pengawetan_oleh = $is_sampling == 1 ? 'Laboratorium' : 'Pelanggan';
                            }

                            $pengawetan_dengan = [];
                            if ($existing && $existing->pengawetan_dengan) {
                                $pengawetan_dengan = array_filter(
                                    array_map('trim', explode('; ', $existing->pengawetan_dengan)),
                                );
                            } else {
                                // Default: Pendinginan checked
                                $pengawetan_dengan = ['Pendinginan'];
                            }

                            $kondisi_sample = [];
                            if ($existing && $existing->kondisi_sample) {
                                $kondisi_sample = array_filter(
                                    array_map('trim', explode('; ', $existing->kondisi_sample)),
                                );
                            }

                            // Default kelayakan: LAYAK jika belum ada data
                            $kelayakan_default = 'layak';
                            if ($existing && $existing->kelayakan_tempat_kemasan) {
                                $kelayakan_default = $existing->kelayakan_tempat_kemasan;
                            }

                            $pengawetan_lainnya_text = '';
                            $has_pengawetan_lainnya = false;
                            foreach ($pengawetan_dengan as $item) {
                                if (stripos($item, 'lainnya:') === 0) {
                                    $has_pengawetan_lainnya = true;
                                    $pengawetan_lainnya_text = trim(substr($item, 8));
                                    break;
                                }
                            }
                            $kondisi_lainnya_text = '';
                            $has_kondisi_lainnya = false;
                            foreach ($kondisi_sample as $item) {
                                if (stripos($item, 'lainnya:') === 0) {
                                    $has_kondisi_lainnya = true;
                                    $kondisi_lainnya_text = trim(substr($item, 8));
                                    break;
                                }
                            }
                        @endphp
                        <div class="sample-card">
                            <h4>{{ $s->codesample_samples }}</h4>

                            <div class="form-group">
                                <label>Pengawetan dilakukan oleh</label>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="radio" name="samples[{{ $s->id_samples }}][pengawetan_oleh]"
                                            id="pengawetan_pelanggan_{{ $s->id_samples }}" value="Pelanggan"
                                            {{ $pengawetan_oleh == 'Pelanggan' ? 'checked' : '' }}>
                                        <label for="pengawetan_pelanggan_{{ $s->id_samples }}">Pelanggan</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="radio" name="samples[{{ $s->id_samples }}][pengawetan_oleh]"
                                            id="pengawetan_lab_{{ $s->id_samples }}" value="Laboratorium"
                                            {{ $pengawetan_oleh == 'Laboratorium' ? 'checked' : '' }}>
                                        <label for="pengawetan_lab_{{ $s->id_samples }}">Laboratorium</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Pendinginan</label>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="checkbox"
                                            name="samples[{{ $s->id_samples }}][pengawetan_pendinginan]"
                                            id="pendinginan_{{ $s->id_samples }}"
                                            {{ in_array('Pendinginan', $pengawetan_dengan) ? 'checked' : '' }}>
                                        <label for="pendinginan_{{ $s->id_samples }}">Pendinginan</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="samples[{{ $s->id_samples }}][pengawetan_hno3]"
                                            id="hno3_{{ $s->id_samples }}"
                                            {{ in_array('HNO3', $pengawetan_dengan) ? 'checked' : '' }}>
                                        <label for="hno3_{{ $s->id_samples }}">HNO₃</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="samples[{{ $s->id_samples }}][pengawetan_h2so4]"
                                            id="h2so4_{{ $s->id_samples }}"
                                            {{ in_array('H2SO4', $pengawetan_dengan) ? 'checked' : '' }}>
                                        <label for="h2so4_{{ $s->id_samples }}">H₂SO₄</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="samples[{{ $s->id_samples }}][pengawetan_naoh]"
                                            id="naoh_{{ $s->id_samples }}"
                                            {{ in_array('NaOH', $pengawetan_dengan) ? 'checked' : '' }}>
                                        <label for="naoh_{{ $s->id_samples }}">NaOH</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox"
                                            name="samples[{{ $s->id_samples }}][pengawetan_lainnya]"
                                            id="pengawetan_lainnya_{{ $s->id_samples }}"
                                            class="pengawetan-lainnya-checkbox"
                                            data-target="pengawetan_lainnya_text_{{ $s->id_samples }}"
                                            {{ $has_pengawetan_lainnya ? 'checked' : '' }}>
                                        <label for="pengawetan_lainnya_{{ $s->id_samples }}">Lainnya</label>
                                    </div>
                                </div>
                                <input type="text" class="form-control mt-2 pengawetan-lainnya-text"
                                    name="samples[{{ $s->id_samples }}][pengawetan_lainnya_text]"
                                    id="pengawetan_lainnya_text_{{ $s->id_samples }}" placeholder="Sebutkan lainnya"
                                    value="{{ $pengawetan_lainnya_text }}"
                                    style="display: {{ $has_pengawetan_lainnya ? 'block' : 'none' }};">
                            </div>

                            <div class="form-group">
                                <label>Kondisi Sampel</label>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="radio" name="samples[{{ $s->id_samples }}][kelayakan]"
                                            id="layak_{{ $s->id_samples }}" value="1"
                                            data-target="kondisi_tidak_layak_{{ $s->id_samples }}"
                                            class="kelayakan-radio"
                                            {{ $kelayakan_default == 'layak' ? 'checked' : '' }}>
                                        <label for="layak_{{ $s->id_samples }}">LAYAK</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="radio" name="samples[{{ $s->id_samples }}][kelayakan]"
                                            id="tidak_layak_{{ $s->id_samples }}" value="0"
                                            data-target="kondisi_tidak_layak_{{ $s->id_samples }}"
                                            class="kelayakan-radio"
                                            {{ $kelayakan_default == 'tidak layak' ? 'checked' : '' }}>
                                        <label for="tidak_layak_{{ $s->id_samples }}">TIDAK LAYAK</label>
                                    </div>
                                </div>
                                <div id="kondisi_tidak_layak_{{ $s->id_samples }}" class="kondisi-tidak-layak mt-2"
                                    style="display: {{ $kelayakan_default == 'tidak layak' ? 'block' : 'none' }};">
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][kondisi_tidak_diawetkan]"
                                                id="tidak_diawetkan_{{ $s->id_samples }}"
                                                {{ in_array('tidak diawetkan di lapangan', $kondisi_sample) ? 'checked' : '' }}>
                                            <label for="tidak_diawetkan_{{ $s->id_samples }}">tidak diawetkan di
                                                lapangan</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][kondisi_wadah_tidak_sesuai]"
                                                id="wadah_tidak_sesuai_{{ $s->id_samples }}"
                                                {{ in_array('wadah sampel tidak sesuai', $kondisi_sample) ? 'checked' : '' }}>
                                            <label for="wadah_tidak_sesuai_{{ $s->id_samples }}">wadah sampel tidak
                                                sesuai</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][kondisi_kadaluarsa]"
                                                id="kadaluarsa_{{ $s->id_samples }}"
                                                {{ in_array('sampel kadaluarsa', $kondisi_sample) ? 'checked' : '' }}>
                                            <label for="kadaluarsa_{{ $s->id_samples }}">sampel kadaluarsa</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][kondisi_lainnya]"
                                                id="kondisi_lainnya_{{ $s->id_samples }}"
                                                class="kondisi-lainnya-checkbox"
                                                data-target="kondisi_lainnya_text_{{ $s->id_samples }}"
                                                {{ $has_kondisi_lainnya ? 'checked' : '' }}>
                                            <label for="kondisi_lainnya_{{ $s->id_samples }}">lainnya,
                                                sebutkan</label>
                                        </div>
                                    </div>
                                    <textarea class="form-control mt-2 kondisi-lainnya-text" name="samples[{{ $s->id_samples }}][kondisi_lainnya_text]"
                                        id="kondisi_lainnya_text_{{ $s->id_samples }}" rows="2" placeholder="Catatan..."
                                        style="display: {{ $has_kondisi_lainnya ? 'block' : 'none' }};">{{ $kondisi_lainnya_text }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary"
                        onclick="document.getElementById('current_step').value='1';">💾 Simpan Step 1: Penerima
                        Sampel</button>
                </div>
            @endif
            @if (false)
                <!-- OLD CODE - Separate forms for KIM and MBI - DISABLED -->
                @if (false && $can_manage_kim && !$step_penerima_done_kim)
                    <div class="card" id="step-1-kim">
                        <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 1: Penerima Sampel -
                            Lab Kimia</h3>

                        <div class="form-group">
                            <label for="penerima_sampel_kim">Nama Penerima <span class="required">*</span></label>
                            <select name="penerima_sampel" id="penerima_sampel_kim" class="form-control" required>
                                <option value="">-- Pilih Penerima Sampel --</option>
                                @foreach ($penerima_sampel_list as $penerima)
                                    <option value="{{ $penerima }}">{{ $penerima }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="penerima_tanggal_kim">Tanggal Penerimaan <span
                                    class="required">*</span></label>
                            <input type="text" name="penerima_tanggal" id="penerima_tanggal_kim"
                                class="form-control" placeholder="dd/mm/yyyy HH:mm"
                                value="{{ $penerima_tanggal_default ?? '' }}" required>
                        </div>

                        @if (!$use_tte)
                            <div class="form-group">
                                <label>Tanda Tangan Penerima</label>
                                <div class="signature-pad">
                                    <canvas id="penerima-signature-pad-kim"
                                        style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                </div>
                                <button type="button" class="btn btn-clear" id="clear-penerima-signature-kim">
                                    🗑️ Bersihkan
                                </button>
                                <input type="hidden" name="penerima_signature" id="penerima_signature_kim"
                                    value="">
                                <input type="hidden" name="penerima_signature_type" value="canvas">
                            </div>
                        @endif

                        <!-- Sample Details for KIM -->
                        @foreach ($group_samples_kim as $s)
                            @php
                                // Get existing penerimaan for this sample with KIM lab
                                $s_lab_kim = null;
                                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                                    foreach ($s->samplemethod as $sm) {
                                        if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                                            $s_lab_kim = $sm->laboratorium;
                                            break;
                                        }
                                    }
                                }
                                $existing = null;
                                if ($s_lab_kim) {
                                    $existing = \Smt\Masterweb\Models\PenerimaanSample::where(
                                        'sample_id',
                                        $s->id_samples,
                                    )
                                        ->where('laboratorium_id', $s_lab_kim->id_laboratorium)
                                        ->first();
                                }

                                // Default pengawetan_oleh berdasarkan is_sampling dari permohonan uji
                                if ($existing && $existing->pengawetan_oleh) {
                                    $pengawetan_oleh = $existing->pengawetan_oleh;
                                } else {
                                    // Default: jika is_sampling = 1 maka Laboratorium, jika 0 maka Pelanggan
                                    $is_sampling = $s->permohonanuji
                                        ? $s->permohonanuji->is_sampling
                                        : ($sample->permohonanuji
                                            ? $sample->permohonanuji->is_sampling
                                            : 0);
                                    $pengawetan_oleh = $is_sampling == 1 ? 'Laboratorium' : 'Pelanggan';
                                }

                                $pengawetan_dengan = [];
                                if ($existing && $existing->pengawetan_dengan) {
                                    $pengawetan_dengan = array_filter(
                                        array_map('trim', explode('; ', $existing->pengawetan_dengan)),
                                    );
                                } else {
                                    // Default: Pendinginan checked
                                    $pengawetan_dengan = ['Pendinginan'];
                                }

                                $kondisi_sample = [];
                                if ($existing && $existing->kondisi_sample) {
                                    $kondisi_sample = array_filter(
                                        array_map('trim', explode('; ', $existing->kondisi_sample)),
                                    );
                                }

                                // Default kelayakan: LAYAK jika belum ada data
                                $kelayakan_default = 'layak';
                                if ($existing && $existing->kelayakan_tempat_kemasan) {
                                    $kelayakan_default = $existing->kelayakan_tempat_kemasan;
                                }

                                $pengawetan_lainnya_text = '';
                                $has_pengawetan_lainnya = false;
                                foreach ($pengawetan_dengan as $item) {
                                    if (stripos($item, 'lainnya:') === 0) {
                                        $has_pengawetan_lainnya = true;
                                        $pengawetan_lainnya_text = trim(substr($item, 8));
                                        break;
                                    }
                                }
                                $kondisi_lainnya_text = '';
                                $has_kondisi_lainnya = false;
                                foreach ($kondisi_sample as $item) {
                                    if (stripos($item, 'lainnya:') === 0) {
                                        $has_kondisi_lainnya = true;
                                        $kondisi_lainnya_text = trim(substr($item, 8));
                                        break;
                                    }
                                }
                            @endphp
                            <div class="sample-card">
                                <h4>{{ $s->codesample_samples }}</h4>

                                <div class="form-group">
                                    <label>Pengawetan dilakukan oleh</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="radio"
                                                name="samples[{{ $s->id_samples }}][pengawetan_oleh]"
                                                id="pengawetan_pelanggan_kim_{{ $s->id_samples }}" value="Pelanggan"
                                                {{ $pengawetan_oleh == 'Pelanggan' ? 'checked' : '' }}>
                                            <label
                                                for="pengawetan_pelanggan_kim_{{ $s->id_samples }}">Pelanggan</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="radio"
                                                name="samples[{{ $s->id_samples }}][pengawetan_oleh]"
                                                id="pengawetan_lab_kim_{{ $s->id_samples }}" value="Laboratorium"
                                                {{ $pengawetan_oleh == 'Laboratorium' ? 'checked' : '' }}>
                                            <label for="pengawetan_lab_kim_{{ $s->id_samples }}">Laboratorium</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Pendinginan</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_pendinginan]"
                                                id="pendinginan_kim_{{ $s->id_samples }}"
                                                {{ in_array('Pendinginan', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="pendinginan_kim_{{ $s->id_samples }}">Pendinginan</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_hno3]"
                                                id="hno3_kim_{{ $s->id_samples }}"
                                                {{ in_array('HNO3', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="hno3_kim_{{ $s->id_samples }}">HNO₃</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_h2so4]"
                                                id="h2so4_kim_{{ $s->id_samples }}"
                                                {{ in_array('H2SO4', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="h2so4_kim_{{ $s->id_samples }}">H₂SO₄</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_naoh]"
                                                id="naoh_kim_{{ $s->id_samples }}"
                                                {{ in_array('NaOH', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="naoh_kim_{{ $s->id_samples }}">NaOH</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_lainnya]"
                                                id="pengawetan_lainnya_kim_{{ $s->id_samples }}"
                                                class="pengawetan-lainnya-checkbox"
                                                data-target="pengawetan_lainnya_text_kim_{{ $s->id_samples }}"
                                                {{ $has_pengawetan_lainnya ? 'checked' : '' }}>
                                            <label for="pengawetan_lainnya_kim_{{ $s->id_samples }}">Lainnya</label>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mt-2 pengawetan-lainnya-text"
                                        name="samples[{{ $s->id_samples }}][pengawetan_lainnya_text]"
                                        id="pengawetan_lainnya_text_kim_{{ $s->id_samples }}"
                                        placeholder="Sebutkan lainnya" value="{{ $pengawetan_lainnya_text }}"
                                        style="display: {{ $has_pengawetan_lainnya ? 'block' : 'none' }};">
                                </div>

                                <div class="form-group">
                                    <label>Kondisi Sampel</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="radio" name="samples[{{ $s->id_samples }}][kelayakan]"
                                                id="layak_kim_{{ $s->id_samples }}" value="1"
                                                data-target="kondisi_tidak_layak_kim_{{ $s->id_samples }}"
                                                {{ $kelayakan_default == 'layak' ? 'checked' : '' }}>
                                            <label for="layak_kim_{{ $s->id_samples }}">LAYAK</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="radio" name="samples[{{ $s->id_samples }}][kelayakan]"
                                                id="tidak_layak_kim_{{ $s->id_samples }}" value="0"
                                                data-target="kondisi_tidak_layak_kim_{{ $s->id_samples }}"
                                                {{ $kelayakan_default == 'tidak layak' ? 'checked' : '' }}>
                                            <label for="tidak_layak_kim_{{ $s->id_samples }}">TIDAK LAYAK</label>
                                        </div>
                                    </div>
                                    <div id="kondisi_tidak_layak_kim_{{ $s->id_samples }}"
                                        class="kondisi-tidak-layak mt-2"
                                        style="display: {{ $kelayakan_default == 'tidak layak' ? 'block' : 'none' }};">
                                        <div class="checkbox-group">
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_tidak_diawetkan]"
                                                    id="tidak_diawetkan_kim_{{ $s->id_samples }}"
                                                    {{ in_array('tidak diawetkan di lapangan', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="tidak_diawetkan_kim_{{ $s->id_samples }}">tidak
                                                    diawetkan di
                                                    lapangan</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_wadah_tidak_sesuai]"
                                                    id="wadah_tidak_sesuai_kim_{{ $s->id_samples }}"
                                                    {{ in_array('wadah sampel tidak sesuai', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="wadah_tidak_sesuai_kim_{{ $s->id_samples }}">wadah
                                                    sampel tidak
                                                    sesuai</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_kadaluarsa]"
                                                    id="kadaluarsa_kim_{{ $s->id_samples }}"
                                                    {{ in_array('sampel kadaluarsa', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="kadaluarsa_kim_{{ $s->id_samples }}">sampel
                                                    kadaluarsa</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_lainnya]"
                                                    id="kondisi_lainnya_kim_{{ $s->id_samples }}"
                                                    class="kondisi-lainnya-checkbox"
                                                    data-target="kondisi_lainnya_text_kim_{{ $s->id_samples }}"
                                                    {{ $has_kondisi_lainnya ? 'checked' : '' }}>
                                                <label for="kondisi_lainnya_kim_{{ $s->id_samples }}">lainnya,
                                                    sebutkan</label>
                                            </div>
                                        </div>
                                        <textarea class="form-control mt-2 kondisi-lainnya-text" name="samples[{{ $s->id_samples }}][kondisi_lainnya_text]"
                                            id="kondisi_lainnya_text_kim_{{ $s->id_samples }}" rows="2" placeholder="Catatan..."
                                            style="display: {{ $has_kondisi_lainnya ? 'block' : 'none' }};">{{ $kondisi_lainnya_text }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary"
                            onclick="document.getElementById('current_step').value='1'; document.getElementById('lab_type').value='KIM';">💾
                            Simpan Step 1: Penerima Sampel - Lab Kimia</button>
                    </div>
                @endif

                <!-- Form untuk Lab Mikro -->
                @if ($can_manage_mbi && !$step_penerima_done_mbi)
                    <div class="card" id="step-1-mbi">
                        <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 1: Penerima Sampel -
                            Lab Mikro</h3>

                        <div class="form-group">
                            <label for="penerima_sampel_mbi">Nama Penerima <span class="required">*</span></label>
                            <select name="penerima_sampel" id="penerima_sampel_mbi" class="form-control" required>
                                <option value="">-- Pilih Penerima Sampel --</option>
                                @foreach ($penerima_sampel_list as $penerima)
                                    <option value="{{ $penerima }}">{{ $penerima }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="penerima_tanggal_mbi">Tanggal Penerimaan <span
                                    class="required">*</span></label>
                            <input type="text" name="penerima_tanggal" id="penerima_tanggal_mbi"
                                class="form-control" placeholder="dd/mm/yyyy HH:mm"
                                value="{{ $penerima_tanggal_default ?? '' }}" required>
                        </div>

                        @if (!$use_tte)
                            <div class="form-group">
                                <label>Tanda Tangan Penerima</label>
                                <div class="signature-pad">
                                    <canvas id="penerima-signature-pad-mbi"
                                        style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                </div>
                                <button type="button" class="btn btn-clear" id="clear-penerima-signature-mbi">
                                    🗑️ Bersihkan
                                </button>
                                <input type="hidden" name="penerima_signature" id="penerima_signature_mbi"
                                    value="">
                                <input type="hidden" name="penerima_signature_type" value="canvas">
                            </div>
                        @endif

                        <!-- Sample Details for MBI -->
                        @foreach ($group_samples_mbi as $s)
                            @php
                                // Get existing penerimaan for this sample with MBI lab
                                $s_lab_mbi = null;
                                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                                    foreach ($s->samplemethod as $sm) {
                                        if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                                            $s_lab_mbi = $sm->laboratorium;
                                            break;
                                        }
                                    }
                                }
                                $existing = null;
                                if ($s_lab_mbi) {
                                    $existing = \Smt\Masterweb\Models\PenerimaanSample::where(
                                        'sample_id',
                                        $s->id_samples,
                                    )
                                        ->where('laboratorium_id', $s_lab_mbi->id_laboratorium)
                                        ->first();
                                }

                                // Default pengawetan_oleh berdasarkan is_sampling dari permohonan uji
                                if ($existing && $existing->pengawetan_oleh) {
                                    $pengawetan_oleh = $existing->pengawetan_oleh;
                                } else {
                                    // Default: jika is_sampling = 1 maka Laboratorium, jika 0 maka Pelanggan
                                    $is_sampling = $s->permohonanuji
                                        ? $s->permohonanuji->is_sampling
                                        : ($sample->permohonanuji
                                            ? $sample->permohonanuji->is_sampling
                                            : 0);
                                    $pengawetan_oleh = $is_sampling == 1 ? 'Laboratorium' : 'Pelanggan';
                                }

                                $pengawetan_dengan = [];
                                if ($existing && $existing->pengawetan_dengan) {
                                    $pengawetan_dengan = array_filter(
                                        array_map('trim', explode('; ', $existing->pengawetan_dengan)),
                                    );
                                } else {
                                    // Default: Pendinginan checked
                                    $pengawetan_dengan = ['Pendinginan'];
                                }

                                $kondisi_sample = [];
                                if ($existing && $existing->kondisi_sample) {
                                    $kondisi_sample = array_filter(
                                        array_map('trim', explode('; ', $existing->kondisi_sample)),
                                    );
                                }

                                // Default kelayakan: LAYAK jika belum ada data
                                $kelayakan_default = 'layak';
                                if ($existing && $existing->kelayakan_tempat_kemasan) {
                                    $kelayakan_default = $existing->kelayakan_tempat_kemasan;
                                }

                                $pengawetan_lainnya_text = '';
                                $has_pengawetan_lainnya = false;
                                foreach ($pengawetan_dengan as $item) {
                                    if (stripos($item, 'lainnya:') === 0) {
                                        $has_pengawetan_lainnya = true;
                                        $pengawetan_lainnya_text = trim(substr($item, 8));
                                        break;
                                    }
                                }
                                $kondisi_lainnya_text = '';
                                $has_kondisi_lainnya = false;
                                foreach ($kondisi_sample as $item) {
                                    if (stripos($item, 'lainnya:') === 0) {
                                        $has_kondisi_lainnya = true;
                                        $kondisi_lainnya_text = trim(substr($item, 8));
                                        break;
                                    }
                                }
                            @endphp
                            <div class="sample-card">
                                <h4>{{ $s->codesample_samples }}</h4>

                                <div class="form-group">
                                    <label>Pengawetan dilakukan oleh</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="radio"
                                                name="samples[{{ $s->id_samples }}][pengawetan_oleh]"
                                                id="pengawetan_pelanggan_mbi_{{ $s->id_samples }}" value="Pelanggan"
                                                {{ $pengawetan_oleh == 'Pelanggan' ? 'checked' : '' }}>
                                            <label
                                                for="pengawetan_pelanggan_mbi_{{ $s->id_samples }}">Pelanggan</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="radio"
                                                name="samples[{{ $s->id_samples }}][pengawetan_oleh]"
                                                id="pengawetan_lab_mbi_{{ $s->id_samples }}" value="Laboratorium"
                                                {{ $pengawetan_oleh == 'Laboratorium' ? 'checked' : '' }}>
                                            <label for="pengawetan_lab_mbi_{{ $s->id_samples }}">Laboratorium</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Pendinginan</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_pendinginan]"
                                                id="pendinginan_mbi_{{ $s->id_samples }}"
                                                {{ in_array('Pendinginan', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="pendinginan_mbi_{{ $s->id_samples }}">Pendinginan</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_hno3]"
                                                id="hno3_mbi_{{ $s->id_samples }}"
                                                {{ in_array('HNO3', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="hno3_mbi_{{ $s->id_samples }}">HNO₃</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_h2so4]"
                                                id="h2so4_mbi_{{ $s->id_samples }}"
                                                {{ in_array('H2SO4', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="h2so4_mbi_{{ $s->id_samples }}">H₂SO₄</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_naoh]"
                                                id="naoh_mbi_{{ $s->id_samples }}"
                                                {{ in_array('NaOH', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="naoh_mbi_{{ $s->id_samples }}">NaOH</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_lainnya]"
                                                id="pengawetan_lainnya_mbi_{{ $s->id_samples }}"
                                                class="pengawetan-lainnya-checkbox"
                                                data-target="pengawetan_lainnya_text_mbi_{{ $s->id_samples }}"
                                                {{ $has_pengawetan_lainnya ? 'checked' : '' }}>
                                            <label for="pengawetan_lainnya_mbi_{{ $s->id_samples }}">Lainnya</label>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mt-2 pengawetan-lainnya-text"
                                        name="samples[{{ $s->id_samples }}][pengawetan_lainnya_text]"
                                        id="pengawetan_lainnya_text_mbi_{{ $s->id_samples }}"
                                        placeholder="Sebutkan lainnya" value="{{ $pengawetan_lainnya_text }}"
                                        style="display: {{ $has_pengawetan_lainnya ? 'block' : 'none' }};">
                                </div>

                                <div class="form-group">
                                    <label>Kondisi Sampel</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="radio" name="samples[{{ $s->id_samples }}][kelayakan]"
                                                id="layak_mbi_{{ $s->id_samples }}" value="1"
                                                data-target="kondisi_tidak_layak_mbi_{{ $s->id_samples }}"
                                                {{ $kelayakan_default == 'layak' ? 'checked' : '' }}>
                                            <label for="layak_mbi_{{ $s->id_samples }}">LAYAK</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="radio" name="samples[{{ $s->id_samples }}][kelayakan]"
                                                id="tidak_layak_mbi_{{ $s->id_samples }}" value="0"
                                                data-target="kondisi_tidak_layak_mbi_{{ $s->id_samples }}"
                                                {{ $kelayakan_default == 'tidak layak' ? 'checked' : '' }}>
                                            <label for="tidak_layak_mbi_{{ $s->id_samples }}">TIDAK LAYAK</label>
                                        </div>
                                    </div>
                                    <div id="kondisi_tidak_layak_mbi_{{ $s->id_samples }}"
                                        class="kondisi-tidak-layak mt-2"
                                        style="display: {{ $kelayakan_default == 'tidak layak' ? 'block' : 'none' }};">
                                        <div class="checkbox-group">
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_tidak_diawetkan]"
                                                    id="tidak_diawetkan_mbi_{{ $s->id_samples }}"
                                                    {{ in_array('tidak diawetkan di lapangan', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="tidak_diawetkan_mbi_{{ $s->id_samples }}">tidak
                                                    diawetkan di
                                                    lapangan</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_wadah_tidak_sesuai]"
                                                    id="wadah_tidak_sesuai_mbi_{{ $s->id_samples }}"
                                                    {{ in_array('wadah sampel tidak sesuai', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="wadah_tidak_sesuai_mbi_{{ $s->id_samples }}">wadah
                                                    sampel tidak
                                                    sesuai</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_kadaluarsa]"
                                                    id="kadaluarsa_mbi_{{ $s->id_samples }}"
                                                    {{ in_array('sampel kadaluarsa', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="kadaluarsa_mbi_{{ $s->id_samples }}">sampel
                                                    kadaluarsa</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_lainnya]"
                                                    id="kondisi_lainnya_mbi_{{ $s->id_samples }}"
                                                    class="kondisi-lainnya-checkbox"
                                                    data-target="kondisi_lainnya_text_mbi_{{ $s->id_samples }}"
                                                    {{ $has_kondisi_lainnya ? 'checked' : '' }}>
                                                <label for="kondisi_lainnya_mbi_{{ $s->id_samples }}">lainnya,
                                                    sebutkan</label>
                                            </div>
                                        </div>
                                        <textarea class="form-control mt-2 kondisi-lainnya-text" name="samples[{{ $s->id_samples }}][kondisi_lainnya_text]"
                                            id="kondisi_lainnya_text_mbi_{{ $s->id_samples }}" rows="2" placeholder="Catatan..."
                                            style="display: {{ $has_kondisi_lainnya ? 'block' : 'none' }};">{{ $kondisi_lainnya_text }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary"
                            onclick="document.getElementById('current_step').value='1'; document.getElementById('lab_type').value='MBI';">💾
                            Simpan Step 1: Penerima Sampel - Lab Mikro</button>
                    </div>
                @endif
            @endif
            @if (false)
                <!-- OLD CODE - Form tunggal untuk analis - DISABLED -->
                @if (false && $single_lab_type && $can_manage_single && !$step_penerima_done)
                    <script>
                        document.getElementById('lab_type').value = '{{ $single_lab_type }}';
                    </script>
                    <div class="card" id="step-1">
                        <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 1: Penerima Sampel
                        </h3>

                        <div class="form-group">
                            <label for="penerima_sampel">Nama Penerima <span class="required">*</span></label>
                            <select name="penerima_sampel" id="penerima_sampel" class="form-control" required>
                                <option value="">-- Pilih Penerima Sampel --</option>
                                @foreach ($penerima_sampel_list as $penerima)
                                    <option value="{{ $penerima }}">{{ $penerima }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="penerima_tanggal">Tanggal Penerimaan <span class="required">*</span></label>
                            <input type="text" name="penerima_tanggal" id="penerima_tanggal" class="form-control"
                                placeholder="dd/mm/yyyy HH:mm" value="{{ $penerima_tanggal_default ?? '' }}"
                                required>
                        </div>

                        @if (!$use_tte)
                            <div class="form-group">
                                <label>Tanda Tangan Penerima</label>
                                <div class="signature-pad">
                                    <canvas id="penerima-signature-pad"
                                        style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                </div>
                                <button type="button" class="btn btn-clear" id="clear-penerima-signature">
                                    🗑️ Bersihkan
                                </button>
                                <input type="hidden" name="penerima_signature" id="penerima_signature"
                                    value="">
                                <input type="hidden" name="penerima_signature_type" value="canvas">
                            </div>
                        @endif

                        <!-- Sample Details -->
                        @foreach ($group_samples as $s)
                            @php
                                $existing = isset($existing_penerimaan[$s->id_samples])
                                    ? $existing_penerimaan[$s->id_samples]
                                    : null;

                                // Default pengawetan_oleh berdasarkan is_sampling dari permohonan uji
                                if ($existing && $existing->pengawetan_oleh) {
                                    $pengawetan_oleh = $existing->pengawetan_oleh;
                                } else {
                                    // Default: jika is_sampling = 1 maka Laboratorium, jika 0 maka Pelanggan
                                    $is_sampling = $s->permohonanuji
                                        ? $s->permohonanuji->is_sampling
                                        : ($sample->permohonanuji
                                            ? $sample->permohonanuji->is_sampling
                                            : 0);
                                    $pengawetan_oleh = $is_sampling == 1 ? 'Laboratorium' : 'Pelanggan';
                                }

                                $pengawetan_dengan = [];
                                if ($existing && $existing->pengawetan_dengan) {
                                    $pengawetan_dengan = array_filter(
                                        array_map('trim', explode('; ', $existing->pengawetan_dengan)),
                                    );
                                } else {
                                    // Default: Pendinginan checked
                                    $pengawetan_dengan = ['Pendinginan'];
                                }

                                $kondisi_sample = [];
                                if ($existing && $existing->kondisi_sample) {
                                    $kondisi_sample = array_filter(
                                        array_map('trim', explode('; ', $existing->kondisi_sample)),
                                    );
                                }

                                // Default kelayakan: LAYAK jika belum ada data
                                $kelayakan_default = 'layak';
                                if ($existing && $existing->kelayakan_tempat_kemasan) {
                                    $kelayakan_default = $existing->kelayakan_tempat_kemasan;
                                }

                                $pengawetan_lainnya_text = '';
                                $has_pengawetan_lainnya = false;
                                foreach ($pengawetan_dengan as $item) {
                                    if (stripos($item, 'lainnya:') === 0) {
                                        $has_pengawetan_lainnya = true;
                                        $pengawetan_lainnya_text = trim(substr($item, 8));
                                        break;
                                    }
                                }
                                $kondisi_lainnya_text = '';
                                $has_kondisi_lainnya = false;
                                foreach ($kondisi_sample as $item) {
                                    if (stripos($item, 'lainnya:') === 0) {
                                        $has_kondisi_lainnya = true;
                                        $kondisi_lainnya_text = trim(substr($item, 8));
                                        break;
                                    }
                                }
                            @endphp
                            <div class="sample-card">
                                <h4>{{ $s->codesample_samples }}</h4>

                                <div class="form-group">
                                    <label>Pengawetan dilakukan oleh</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="radio"
                                                name="samples[{{ $s->id_samples }}][pengawetan_oleh]"
                                                id="pengawetan_pelanggan_{{ $s->id_samples }}" value="Pelanggan"
                                                {{ $pengawetan_oleh == 'Pelanggan' ? 'checked' : '' }}>
                                            <label for="pengawetan_pelanggan_{{ $s->id_samples }}">Pelanggan</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="radio"
                                                name="samples[{{ $s->id_samples }}][pengawetan_oleh]"
                                                id="pengawetan_lab_{{ $s->id_samples }}" value="Laboratorium"
                                                {{ $pengawetan_oleh == 'Laboratorium' ? 'checked' : '' }}>
                                            <label for="pengawetan_lab_{{ $s->id_samples }}">Laboratorium</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Pendinginan</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_pendinginan]"
                                                id="pendinginan_{{ $s->id_samples }}"
                                                {{ in_array('Pendinginan', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="pendinginan_{{ $s->id_samples }}">Pendinginan</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_hno3]"
                                                id="hno3_{{ $s->id_samples }}"
                                                {{ in_array('HNO3', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="hno3_{{ $s->id_samples }}">HNO₃</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_h2so4]"
                                                id="h2so4_{{ $s->id_samples }}"
                                                {{ in_array('H2SO4', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="h2so4_{{ $s->id_samples }}">H₂SO₄</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_naoh]"
                                                id="naoh_{{ $s->id_samples }}"
                                                {{ in_array('NaOH', $pengawetan_dengan) ? 'checked' : '' }}>
                                            <label for="naoh_{{ $s->id_samples }}">NaOH</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox"
                                                name="samples[{{ $s->id_samples }}][pengawetan_lainnya]"
                                                id="pengawetan_lainnya_{{ $s->id_samples }}"
                                                class="pengawetan-lainnya-checkbox"
                                                data-target="pengawetan_lainnya_text_{{ $s->id_samples }}"
                                                {{ $has_pengawetan_lainnya ? 'checked' : '' }}>
                                            <label for="pengawetan_lainnya_{{ $s->id_samples }}">Lainnya</label>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mt-2 pengawetan-lainnya-text"
                                        name="samples[{{ $s->id_samples }}][pengawetan_lainnya_text]"
                                        id="pengawetan_lainnya_text_{{ $s->id_samples }}"
                                        placeholder="Sebutkan lainnya" value="{{ $pengawetan_lainnya_text }}"
                                        style="display: {{ $has_pengawetan_lainnya ? 'block' : 'none' }};">
                                </div>

                                <div class="form-group">
                                    <label>Kondisi Sampel</label>
                                    <div class="checkbox-group">
                                        <div class="checkbox-item">
                                            <input type="radio" name="samples[{{ $s->id_samples }}][kelayakan]"
                                                id="layak_{{ $s->id_samples }}" value="1"
                                                data-target="kondisi_tidak_layak_{{ $s->id_samples }}"
                                                {{ $kelayakan_default == 'layak' ? 'checked' : '' }}>
                                            <label for="layak_{{ $s->id_samples }}">LAYAK</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="radio" name="samples[{{ $s->id_samples }}][kelayakan]"
                                                id="tidak_layak_{{ $s->id_samples }}" value="0"
                                                data-target="kondisi_tidak_layak_{{ $s->id_samples }}"
                                                {{ $kelayakan_default == 'tidak layak' ? 'checked' : '' }}>
                                            <label for="tidak_layak_{{ $s->id_samples }}">TIDAK LAYAK</label>
                                        </div>
                                    </div>
                                    <div id="kondisi_tidak_layak_{{ $s->id_samples }}"
                                        class="kondisi-tidak-layak mt-2"
                                        style="display: {{ $kelayakan_default == 'tidak layak' ? 'block' : 'none' }};">
                                        <div class="checkbox-group">
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_tidak_diawetkan]"
                                                    id="tidak_diawetkan_{{ $s->id_samples }}"
                                                    {{ in_array('tidak diawetkan di lapangan', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="tidak_diawetkan_{{ $s->id_samples }}">tidak diawetkan
                                                    di
                                                    lapangan</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_wadah_tidak_sesuai]"
                                                    id="wadah_tidak_sesuai_{{ $s->id_samples }}"
                                                    {{ in_array('wadah sampel tidak sesuai', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="wadah_tidak_sesuai_{{ $s->id_samples }}">wadah sampel
                                                    tidak
                                                    sesuai</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_kadaluarsa]"
                                                    id="kadaluarsa_{{ $s->id_samples }}"
                                                    {{ in_array('sampel kadaluarsa', $kondisi_sample) ? 'checked' : '' }}>
                                                <label for="kadaluarsa_{{ $s->id_samples }}">sampel
                                                    kadaluarsa</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox"
                                                    name="samples[{{ $s->id_samples }}][kondisi_lainnya]"
                                                    id="kondisi_lainnya_{{ $s->id_samples }}"
                                                    class="kondisi-lainnya-checkbox"
                                                    data-target="kondisi_lainnya_text_{{ $s->id_samples }}"
                                                    {{ $has_kondisi_lainnya ? 'checked' : '' }}>
                                                <label for="kondisi_lainnya_{{ $s->id_samples }}">lainnya,
                                                    sebutkan</label>
                                            </div>
                                        </div>
                                        <textarea class="form-control mt-2 kondisi-lainnya-text" name="samples[{{ $s->id_samples }}][kondisi_lainnya_text]"
                                            id="kondisi_lainnya_text_{{ $s->id_samples }}" rows="2" placeholder="Catatan..."
                                            style="display: {{ $has_kondisi_lainnya ? 'block' : 'none' }};">{{ $kondisi_lainnya_text }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary">💾 Simpan Step 1: Penerima Sampel</button>
                    </div>
                @endif
            @endif

            <!-- STEP 2: Disposisi Koordinator Kesmas -->
            @php
                $has_kim = isset($group_samples_kim) && $group_samples_kim->count() > 0;
                $has_mbi = isset($group_samples_mbi) && $group_samples_mbi->count() > 0;
                $multiple_labs = $has_kim && $has_mbi && $isAdmin;
                $normalizedLabCode = strtoupper($userLabCode ?? '');
                $can_manage_kim = $has_kim && ($isAdmin || ($isAnalis && $normalizedLabCode === 'KIM'));
                $can_manage_mbi = $has_mbi && ($isAdmin || ($isAnalis && $normalizedLabCode === 'MBI'));
                // Step 2 muncul jika Step 1 untuk lab tersebut sudah selesai
                $show_step2_kim = $can_manage_kim && $step_penerima_done_kim && !$step_koordinator_done_kim;
                $show_step2_mbi = $can_manage_mbi && $step_penerima_done_mbi && !$step_koordinator_done_mbi;
            @endphp

            @if ($show_step2_kim || $show_step2_mbi)
                @if ($multiple_labs)
                    <!-- Form untuk Lab Kimia -->
                    @if ($show_step2_kim)
                        <div class="card" id="step-2-kim">
                            <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 2: Disposisi ke
                                Koordinator Kesmas - Lab Kimia</h3>

                            <div class="form-group">
                                <label for="disposisi_koordinator_kesmas_kim">Koordinator Kesmas <span
                                        class="required">*</span></label>
                                <select name="disposisi_koordinator_kesmas" id="disposisi_koordinator_kesmas_kim"
                                    class="form-control" required>
                                    <option value="">-- Pilih Koordinator Kesmas --</option>
                                    @foreach ($koordinator_kesmas_list_kim as $koordinator)
                                        <option value="{{ $koordinator }}">{{ $koordinator }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="disposisi_tanggal_kim">Tanggal Disposisi <span
                                        class="required">*</span></label>
                                <input type="text" name="disposisi_tanggal" id="disposisi_tanggal_kim"
                                    class="form-control" placeholder="dd/mm/yyyy HH:mm" required>
                            </div>

                            @if (!$use_tte)
                                <div class="form-group">
                                    <label>Tanda Tangan Koordinator</label>
                                    <div class="signature-pad">
                                        <canvas id="disposisi-signature-pad-kim"
                                            style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-clear"
                                        id="clear-disposisi-signature-kim">🗑️ Bersihkan</button>
                                    <input type="hidden" name="disposisi_signature" id="disposisi_signature_kim"
                                        value="">
                                    <input type="hidden" name="disposisi_signature_type" value="canvas">
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary"
                                onclick="document.getElementById('current_step').value='2'; document.getElementById('lab_type').value='KIM';">💾
                                Simpan Step 2: Koordinator Kesmas - Lab Kimia</button>
                        </div>
                    @endif

                    <!-- Form untuk Lab Mikro -->
                    @if ($show_step2_mbi)

                        <div class="card" id="step-2-mbi">
                            <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 2: Disposisi ke
                                Koordinator Kesmas - Lab Mikro</h3>

                            <div class="form-group">
                                <label for="disposisi_koordinator_kesmas_mbi">Koordinator Kesmas <span
                                        class="required">*</span></label>
                                <select name="disposisi_koordinator_kesmas" id="disposisi_koordinator_kesmas_mbi"
                                    class="form-control" required>
                                    <option value="">-- Pilih Koordinator Kesmas --</option>
                                    @foreach ($koordinator_kesmas_list_mbi as $koordinator)
                                        <option value="{{ $koordinator }}">{{ $koordinator }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="disposisi_tanggal_mbi">Tanggal Disposisi <span
                                        class="required">*</span></label>
                                <input type="text" name="disposisi_tanggal" id="disposisi_tanggal_mbi"
                                    class="form-control" placeholder="dd/mm/yyyy HH:mm"
                                    value="{{ $disposisi_tanggal_default ?? '' }}" required>
                            </div>

                            @if (!$use_tte)
                                <div class="form-group">
                                    <label>Tanda Tangan Koordinator</label>
                                    <div class="signature-pad">
                                        <canvas id="disposisi-signature-pad-mbi"
                                            style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-clear"
                                        id="clear-disposisi-signature-mbi">🗑️ Bersihkan</button>
                                    <input type="hidden" name="disposisi_signature" id="disposisi_signature_mbi"
                                        value="">
                                    <input type="hidden" name="disposisi_signature_type" value="canvas">
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary"
                                onclick="document.getElementById('current_step').value='2'; document.getElementById('lab_type').value='MBI';">💾
                                Simpan Step 2: Koordinator Kesmas - Lab Mikro</button>
                        </div>
                    @endif
                @else
                    <!-- Form tunggal untuk analis atau jika hanya satu lab -->
                    @php
                        $single_lab_type = $has_kim ? 'KIM' : ($has_mbi ? 'MBI' : null);
                        $can_manage_single = $single_lab_type
                            ? $isAdmin || ($isAnalis && $normalizedLabCode === $single_lab_type)
                            : false;

                        // Step 2 muncul jika Step 1 untuk lab tersebut sudah selesai
                        $show_step2_single = false;
                        if ($single_lab_type == 'KIM') {
                            $show_step2_single =
                                $can_manage_single && $step_penerima_done_kim && !$step_koordinator_done_kim;
                        } elseif ($single_lab_type == 'MBI') {
                            $show_step2_single =
                                $can_manage_single && $step_penerima_done_mbi && !$step_koordinator_done_mbi;
                        }
                    @endphp
                    @if ($single_lab_type && $can_manage_single && $show_step2_single)
                        <script>
                            document.getElementById('lab_type').value = '{{ $single_lab_type }}';
                        </script>
                        <div class="card" id="step-2">
                            <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 2: Disposisi ke
                                Koordinator Kesmas</h3>

                            <div class="form-group">
                                <label for="disposisi_koordinator_kesmas">Koordinator Kesmas <span
                                        class="required">*</span></label>
                                <select name="disposisi_koordinator_kesmas" id="disposisi_koordinator_kesmas"
                                    class="form-control" required>
                                    <option value="">-- Pilih Koordinator Kesmas --</option>
                                    @foreach ($koordinator_kesmas_list as $koordinator)
                                        <option value="{{ $koordinator }}">{{ $koordinator }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="disposisi_tanggal">Tanggal Disposisi <span
                                        class="required">*</span></label>
                                <input type="text" name="disposisi_tanggal" id="disposisi_tanggal"
                                    class="form-control" placeholder="dd/mm/yyyy HH:mm"
                                    value="{{ $disposisi_tanggal_default ?? '' }}" required>
                            </div>

                            @if (!$use_tte)
                                <div class="form-group">
                                    <label>Tanda Tangan Koordinator</label>
                                    <div class="signature-pad">
                                        <canvas id="disposisi-signature-pad"
                                            style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-clear" id="clear-disposisi-signature">🗑️
                                        Bersihkan</button>
                                    <input type="hidden" name="disposisi_signature" id="disposisi_signature"
                                        value="">
                                    <input type="hidden" name="disposisi_signature_type" value="canvas">
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary"
                                onclick="var labType = '{{ $single_lab_type ?? '' }}'; if (labType) { document.getElementById('current_step').value='2'; document.getElementById('lab_type').value=labType; }">💾
                                Simpan Step 2: Koordinator
                                Kesmas</button>
                        </div>
                    @endif
                @endif
            @endif

            <!-- STEP 3: Disposisi Analis -->
            @php
                $has_kim = isset($group_samples_kim) && $group_samples_kim->count() > 0;
                $has_mbi = isset($group_samples_mbi) && $group_samples_mbi->count() > 0;
                $multiple_labs = $has_kim && $has_mbi && $isAdmin;
                // Show separate forms if admin has both labs, or if analis has access to both labs
                $normalizedLabCode = strtoupper($userLabCode ?? '');
                $can_analis_kim = $has_kim && ($isAdmin || ($isAnalis && $normalizedLabCode === 'KIM'));
                $can_analis_mbi = $has_mbi && ($isAdmin || ($isAnalis && $normalizedLabCode === 'MBI'));
                // Step 3 muncul jika Step 2 untuk lab tersebut sudah selesai
                $show_step3_kim = $can_analis_kim && $step_koordinator_done_kim && !$step_analis_done_kim;
                $show_step3_mbi = $can_analis_mbi && $step_koordinator_done_mbi && !$step_analis_done_mbi;
            @endphp

            @if ($show_step3_kim || $show_step3_mbi)
                @if ($multiple_labs)
                    <!-- Form untuk Lab Kimia -->
                    @if ($show_step3_kim)
                        <div class="card" id="step-3-kim">
                            <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 3: Disposisi ke
                                Analis - Lab Kimia</h3>


                            <div class="form-group">
                                <label for="disposisi_analis_kim">Nama Analis <span class="required">*</span></label>
                                <select name="disposisi_analis" id="disposisi_analis_kim" class="form-control"
                                    required>
                                    <option value="">-- Pilih Analis --</option>
                                    @foreach ($analis_list_kim as $analis)
                                        <option value="{{ $analis }}">{{ $analis }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="disposisi_analis_tanggal_kim">Tanggal Disposisi <span
                                        class="required">*</span></label>
                                <input type="text" name="disposisi_analis_tanggal"
                                    id="disposisi_analis_tanggal_kim" class="form-control"
                                    placeholder="dd/mm/yyyy HH:mm"
                                    value="{{ $disposisi_analis_tanggal_default ?? '' }}" required>
                            </div>

                            @if (!$use_tte)
                                <div class="form-group">
                                    <label>Tanda Tangan Analis</label>
                                    <div class="signature-pad">
                                        <canvas id="analis-signature-pad-kim"
                                            style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-clear" id="clear-analis-signature-kim">🗑️
                                        Bersihkan</button>
                                    <input type="hidden" name="disposisi_analis_signature"
                                        id="disposisi_analis_signature_kim" value="">
                                    <input type="hidden" name="disposisi_analis_signature_type" value="canvas">
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary"
                                onclick="document.getElementById('current_step').value='3'; document.getElementById('lab_type').value='KIM'; return true;">💾
                                Simpan Step 3: Analis - Lab Kimia</button>
                        </div>
                    @endif

                    <!-- Form untuk Lab Mikro -->
                    @if ($show_step3_mbi)
                        <div class="card" id="step-3-mbi">
                            <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 3: Disposisi ke
                                Analis - Lab Mikro</h3>


                            <div class="form-group">
                                <label for="disposisi_analis_mbi">Nama Analis <span class="required">*</span></label>
                                <select name="disposisi_analis" id="disposisi_analis_mbi" class="form-control"
                                    required>
                                    <option value="">-- Pilih Analis --</option>
                                    @foreach ($analis_list_mbi as $analis)
                                        <option value="{{ $analis }}">{{ $analis }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="disposisi_analis_tanggal_mbi">Tanggal Disposisi <span
                                        class="required">*</span></label>
                                <input type="text" name="disposisi_analis_tanggal"
                                    id="disposisi_analis_tanggal_mbi" class="form-control"
                                    placeholder="dd/mm/yyyy HH:mm"
                                    value="{{ $disposisi_analis_tanggal_default ?? '' }}" required>
                            </div>

                            @if (!$use_tte)
                                <div class="form-group">
                                    <label>Tanda Tangan Analis</label>
                                    <div class="signature-pad">
                                        <canvas id="analis-signature-pad-mbi"
                                            style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-clear" id="clear-analis-signature-mbi">🗑️
                                        Bersihkan</button>
                                    <input type="hidden" name="disposisi_analis_signature"
                                        id="disposisi_analis_signature_mbi" value="">
                                    <input type="hidden" name="disposisi_analis_signature_type" value="canvas">
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary"
                                onclick="document.getElementById('current_step').value='3'; document.getElementById('lab_type').value='MBI'; return true;">💾
                                Simpan Step 3: Analis - Lab Mikro</button>
                        </div>
                    @endif
                @else
                    <!-- Form tunggal untuk analis atau jika hanya satu lab -->
                    @php
                        $single_lab_type_analis = $has_kim ? 'KIM' : ($has_mbi ? 'MBI' : null);
                        $can_manage_single_analis = $single_lab_type_analis
                            ? $isAdmin || ($isAnalis && $normalizedLabCode === $single_lab_type_analis)
                            : false;
                        // Step 3 muncul jika Step 2 untuk lab tersebut sudah selesai
                        $show_step3_single = false;
                        if ($single_lab_type_analis == 'KIM') {
                            $show_step3_single =
                                $can_manage_single_analis && $step_koordinator_done_kim && !$step_analis_done_kim;
                        } elseif ($single_lab_type_analis == 'MBI') {
                            $show_step3_single =
                                $can_manage_single_analis && $step_koordinator_done_mbi && !$step_analis_done_mbi;
                        }
                    @endphp
                    @if ($single_lab_type_analis && $can_manage_single_analis && $show_step3_single)
                        <script>
                            document.getElementById('lab_type').value = '{{ $single_lab_type_analis }}';
                        </script>
                        <div class="card" id="step-3">
                            <h3 style="margin-bottom: 15px; font-size: 16px; color: #0b3a5c;">Step 3: Disposisi ke
                                Analis
                            </h3>

                            <div class="form-group">
                                <label for="disposisi_analis">Nama Analis <span class="required">*</span></label>
                                <select name="disposisi_analis" id="disposisi_analis" class="form-control" required>
                                    <option value="">-- Pilih Analis --</option>
                                    @foreach ($analis_list as $analis)
                                        <option value="{{ $analis }}">{{ $analis }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="disposisi_analis_tanggal">Tanggal Disposisi <span
                                        class="required">*</span></label>
                                <input type="text" name="disposisi_analis_tanggal" id="disposisi_analis_tanggal"
                                    class="form-control" placeholder="dd/mm/yyyy HH:mm"
                                    value="{{ $disposisi_analis_tanggal_default ?? '' }}" required>
                            </div>

                            @if (!$use_tte)
                                <div class="form-group">
                                    <label>Tanda Tangan Analis</label>
                                    <div class="signature-pad">
                                        <canvas id="analis-signature-pad"
                                            style="width: 100%; max-width: 400px; height: 200px; cursor: crosshair;"></canvas>
                                    </div>
                                    <button type="button" class="btn btn-clear" id="clear-analis-signature">🗑️
                                        Bersihkan</button>
                                    <input type="hidden" name="disposisi_analis_signature"
                                        id="disposisi_analis_signature" value="">
                                    <input type="hidden" name="disposisi_analis_signature_type" value="canvas">
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary"
                                onclick="var labType = '{{ $single_lab_type_analis ?? '' }}'; if (labType) { document.getElementById('current_step').value='3'; document.getElementById('lab_type').value=labType; }">💾
                                Simpan Step 3: Analis</button>
                        </div>
                    @endif
                @endif
            @endif

            @if ($step_analis_done)
                <div class="card">
                    <div class="alert alert-info">
                        <strong>✓ Selesai!</strong> Semua step telah diselesaikan.
                    </div>
                </div>
            @endif
        </form>
    </div>

    <form method="POST" action="{{ route('mobile.testing.logout') }}"
        style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        @csrf
        <button type="submit" class="logout-btn" title="Logout">🚪</button>
    </form>

    <!-- jQuery -->
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>

    <!-- Flatpickr JS -->
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>

    @if (!$use_tte)
        <!-- Signature Pad -->
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    @endif

    <script>
        // Wait for jQuery to be ready
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded! Please refresh the page.');
        } else {
            jQuery(document).ready(function($) {
                // Wait for Flatpickr to be loaded
                if (typeof flatpickr === 'undefined') {
                    console.error('Flatpickr is not loaded!');
                    return;
                }

                // Initialize Flatpickr
                @if (!$step_penerima_done)
                    if ($('#penerima_tanggal').length) {
                        var penerimaDefault = "{{ $penerima_tanggal_default ?? '' }}";
                        var defaultDate = new Date();

                        // If there's existing value, try to parse it
                        if (penerimaDefault) {
                            try {
                                // Parse d/m/Y H:i format
                                var parts = penerimaDefault.split(' ');
                                if (parts.length === 2) {
                                    var dateParts = parts[0].split('/');
                                    var timeParts = parts[1].split(':');
                                    if (dateParts.length === 3 && timeParts.length >= 2) {
                                        defaultDate = new Date(
                                            parseInt(dateParts[2]), // year
                                            parseInt(dateParts[1]) - 1, // month (0-indexed)
                                            parseInt(dateParts[0]), // day
                                            parseInt(timeParts[0]), // hour
                                            parseInt(timeParts[1]) // minute
                                        );
                                    }
                                }
                            } catch (e) {
                                console.log('Error parsing existing date:', e);
                            }
                        }

                        var fpPenerima = flatpickr("#penerima_tanggal", {
                            enableTime: true,
                            noCalendar: false,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true,
                            locale: "id",
                            defaultDate: defaultDate,
                            minuteIncrement: 1,
                            allowInput: true,
                            clickOpens: true,
                            animate: true,
                            timePicker: true,
                            onChange: function(selectedDates, dateStr, instance) {
                                // Ensure time is always set
                                if (selectedDates.length > 0) {
                                    var selectedDate = selectedDates[0];
                                    var day = String(selectedDate.getDate()).padStart(2, '0');
                                    var month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                                    var year = selectedDate.getFullYear();
                                    var hours = String(selectedDate.getHours()).padStart(2, '0');
                                    var minutes = String(selectedDate.getMinutes()).padStart(2, '0');
                                    var formattedDate = day + '/' + month + '/' + year + ' ' + hours +
                                        ':' + minutes;

                                    // Update input value to ensure format is correct
                                    $('#penerima_tanggal').val(formattedDate);
                                }
                            }
                        });

                        // Set value if exists
                        if (penerimaDefault) {
                            fpPenerima.setDate(defaultDate, false);
                        } else {
                            // If no default, ensure current time is set
                            var now = new Date();
                            fpPenerima.setDate(now, false);
                            var day = String(now.getDate()).padStart(2, '0');
                            var month = String(now.getMonth() + 1).padStart(2, '0');
                            var year = now.getFullYear();
                            var hours = String(now.getHours()).padStart(2, '0');
                            var minutes = String(now.getMinutes()).padStart(2, '0');
                            $('#penerima_tanggal').val(day + '/' + month + '/' + year + ' ' + hours + ':' +
                                minutes);
                        }
                    }
                @endif

                @if ($step_penerima_done && !$step_koordinator_done)
                    // Initialize Flatpickr for disposisi tanggal (single or separate forms)
                    var disposisiDefault = "{{ $disposisi_tanggal_default ?? '' }}";

                    if ($('#disposisi_tanggal').length) {
                        var defaultDateDisposisi = new Date();
                        if (disposisiDefault) {
                            try {
                                var parts = disposisiDefault.split(' ');
                                if (parts.length === 2) {
                                    var dateParts = parts[0].split('/');
                                    var timeParts = parts[1].split(':');
                                    if (dateParts.length === 3 && timeParts.length >= 2) {
                                        defaultDateDisposisi = new Date(
                                            parseInt(dateParts[2]),
                                            parseInt(dateParts[1]) - 1,
                                            parseInt(dateParts[0]),
                                            parseInt(timeParts[0]),
                                            parseInt(timeParts[1])
                                        );
                                    }
                                }
                            } catch (e) {
                                console.log('Error parsing disposisi date:', e);
                            }
                        }

                        var fpDisposisi = flatpickr("#disposisi_tanggal", {
                            enableTime: true,
                            noCalendar: false,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true,
                            locale: "id",
                            defaultDate: defaultDateDisposisi,
                            minuteIncrement: 1,
                            allowInput: true,
                            clickOpens: true,
                            animate: true,
                            timePicker: true
                        });

                        if (disposisiDefault) {
                            fpDisposisi.setDate(defaultDateDisposisi, false);
                        }
                    }
                    // For separate forms (kimia and mikro)
                    if ($('#disposisi_tanggal_kim').length) {
                        var defaultDateDisposisiKim = new Date();
                        if (disposisiDefault) {
                            try {
                                var parts = disposisiDefault.split(' ');
                                if (parts.length === 2) {
                                    var dateParts = parts[0].split('/');
                                    var timeParts = parts[1].split(':');
                                    if (dateParts.length === 3 && timeParts.length >= 2) {
                                        defaultDateDisposisiKim = new Date(
                                            parseInt(dateParts[2]),
                                            parseInt(dateParts[1]) - 1,
                                            parseInt(dateParts[0]),
                                            parseInt(timeParts[0]),
                                            parseInt(timeParts[1])
                                        );
                                    }
                                }
                            } catch (e) {
                                console.log('Error parsing disposisi date:', e);
                            }
                        }

                        var fpDisposisiKim = flatpickr("#disposisi_tanggal_kim", {
                            enableTime: true,
                            noCalendar: false,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true,
                            locale: "id",
                            defaultDate: defaultDateDisposisiKim,
                            minuteIncrement: 1,
                            allowInput: true,
                            clickOpens: true,
                            animate: true,
                            timePicker: true
                        });

                        if (disposisiDefault) {
                            fpDisposisiKim.setDate(defaultDateDisposisiKim, false);
                        }
                    }
                    if ($('#disposisi_tanggal_mbi').length) {
                        var defaultDateDisposisiMbi = new Date();
                        if (disposisiDefault) {
                            try {
                                var parts = disposisiDefault.split(' ');
                                if (parts.length === 2) {
                                    var dateParts = parts[0].split('/');
                                    var timeParts = parts[1].split(':');
                                    if (dateParts.length === 3 && timeParts.length >= 2) {
                                        defaultDateDisposisiMbi = new Date(
                                            parseInt(dateParts[2]),
                                            parseInt(dateParts[1]) - 1,
                                            parseInt(dateParts[0]),
                                            parseInt(timeParts[0]),
                                            parseInt(timeParts[1])
                                        );
                                    }
                                }
                            } catch (e) {
                                console.log('Error parsing disposisi date:', e);
                            }
                        }

                        var fpDisposisiMbi = flatpickr("#disposisi_tanggal_mbi", {
                            enableTime: true,
                            noCalendar: false,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true,
                            locale: "id",
                            defaultDate: defaultDateDisposisiMbi,
                            minuteIncrement: 1,
                            allowInput: true,
                            clickOpens: true,
                            animate: true,
                            timePicker: true
                        });

                        if (disposisiDefault) {
                            fpDisposisiMbi.setDate(defaultDateDisposisiMbi, false);
                        }
                    }
                @endif

                @if ($step_koordinator_done && !$step_analis_done)
                    // Initialize Flatpickr for disposisi analis tanggal (single or separate forms)
                    var analisDefault = "{{ $disposisi_analis_tanggal_default ?? '' }}";

                    if ($('#disposisi_analis_tanggal').length) {
                        var defaultDateAnalis = new Date();
                        if (analisDefault) {
                            try {
                                var parts = analisDefault.split(' ');
                                if (parts.length === 2) {
                                    var dateParts = parts[0].split('/');
                                    var timeParts = parts[1].split(':');
                                    if (dateParts.length === 3 && timeParts.length >= 2) {
                                        defaultDateAnalis = new Date(
                                            parseInt(dateParts[2]),
                                            parseInt(dateParts[1]) - 1,
                                            parseInt(dateParts[0]),
                                            parseInt(timeParts[0]),
                                            parseInt(timeParts[1])
                                        );
                                    }
                                }
                            } catch (e) {
                                console.log('Error parsing analis date:', e);
                            }
                        }

                        var fpAnalis = flatpickr("#disposisi_analis_tanggal", {
                            enableTime: true,
                            noCalendar: false,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true,
                            locale: "id",
                            defaultDate: defaultDateAnalis,
                            minuteIncrement: 1,
                            allowInput: true,
                            clickOpens: true,
                            animate: true,
                            timePicker: true
                        });

                        if (analisDefault) {
                            fpAnalis.setDate(defaultDateAnalis, false);
                        }
                    }
                    // For separate forms (kimia and mikro)
                    if ($('#disposisi_analis_tanggal_kim').length) {
                        var defaultDateAnalisKim = new Date();
                        if (analisDefault) {
                            try {
                                var parts = analisDefault.split(' ');
                                if (parts.length === 2) {
                                    var dateParts = parts[0].split('/');
                                    var timeParts = parts[1].split(':');
                                    if (dateParts.length === 3 && timeParts.length >= 2) {
                                        defaultDateAnalisKim = new Date(
                                            parseInt(dateParts[2]),
                                            parseInt(dateParts[1]) - 1,
                                            parseInt(dateParts[0]),
                                            parseInt(timeParts[0]),
                                            parseInt(timeParts[1])
                                        );
                                    }
                                }
                            } catch (e) {
                                console.log('Error parsing analis date:', e);
                            }
                        }

                        var fpAnalisKim = flatpickr("#disposisi_analis_tanggal_kim", {
                            enableTime: true,
                            noCalendar: false,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true,
                            locale: "id",
                            defaultDate: defaultDateAnalisKim,
                            minuteIncrement: 1,
                            allowInput: true,
                            clickOpens: true,
                            animate: true,
                            timePicker: true
                        });

                        if (analisDefault) {
                            fpAnalisKim.setDate(defaultDateAnalisKim, false);
                        }
                    }
                    if ($('#disposisi_analis_tanggal_mbi').length) {
                        var defaultDateAnalisMbi = new Date();
                        if (analisDefault) {
                            try {
                                var parts = analisDefault.split(' ');
                                if (parts.length === 2) {
                                    var dateParts = parts[0].split('/');
                                    var timeParts = parts[1].split(':');
                                    if (dateParts.length === 3 && timeParts.length >= 2) {
                                        defaultDateAnalisMbi = new Date(
                                            parseInt(dateParts[2]),
                                            parseInt(dateParts[1]) - 1,
                                            parseInt(dateParts[0]),
                                            parseInt(timeParts[0]),
                                            parseInt(timeParts[1])
                                        );
                                    }
                                }
                            } catch (e) {
                                console.log('Error parsing analis date:', e);
                            }
                        }

                        var fpAnalisMbi = flatpickr("#disposisi_analis_tanggal_mbi", {
                            enableTime: true,
                            noCalendar: false,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true,
                            locale: "id",
                            defaultDate: defaultDateAnalisMbi,
                            minuteIncrement: 1,
                            allowInput: true,
                            clickOpens: true,
                            animate: true,
                            timePicker: true
                        });

                        if (analisDefault) {
                            fpAnalisMbi.setDate(defaultDateAnalisMbi, false);
                        }
                    }
                @endif

                // Handle kelayakan radio change
                $('.kelayakan-radio').on('change', function() {
                    var target = $(this).data('target');
                    var value = $(this).val();
                    if (value == '0') {
                        $('#' + target).slideDown();
                    } else {
                        $('#' + target).slideUp();
                    }
                });

                // Handle kondisi lainnya checkbox
                $('.kondisi-lainnya-checkbox').on('change', function() {
                    var target = $(this).data('target');
                    if ($(this).is(':checked')) {
                        $('#' + target).slideDown();
                    } else {
                        $('#' + target).slideUp().val('');
                    }
                });

                // Handle pengawetan lainnya checkbox
                $('.pengawetan-lainnya-checkbox').on('change', function() {
                    var target = $(this).data('target');
                    if ($(this).is(':checked')) {
                        $('#' + target).slideDown();
                    } else {
                        $('#' + target).slideUp().val('');
                    }
                });

                @if (!$use_tte)
                    // Initialize all signature hidden inputs to empty on first load
                    $('#penerima_signature').val('');
                    $('#penerima_signature_kim').val('');
                    $('#penerima_signature_mbi').val('');
                    $('#disposisi_signature').val('');
                    $('#disposisi_signature_kim').val('');
                    $('#disposisi_signature_mbi').val('');
                    $('#disposisi_analis_signature').val('');
                    $('#disposisi_analis_signature_kim').val('');
                    $('#disposisi_analis_signature_mbi').val('');

                    // Initialize Signature Pads
                    @if (!$step_penerima_done)
                        // Single form signature pad
                        var canvasPenerima = document.getElementById('penerima-signature-pad');
                        var penerimaSignaturePad;
                        if (canvasPenerima) {
                            penerimaSignaturePad = new SignaturePad(canvasPenerima, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasPenerima() {
                                // Use higher ratio for better quality when printing
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasPenerima.width = canvasPenerima.offsetWidth * ratio;
                                canvasPenerima.height = canvasPenerima.offsetHeight * ratio;
                                canvasPenerima.getContext("2d").scale(ratio, ratio);
                                penerimaSignaturePad.clear();
                            }

                            window.addEventListener('resize', resizeCanvasPenerima);
                            resizeCanvasPenerima();

                            $('#clear-penerima-signature').on('click', function() {
                                penerimaSignaturePad.clear();
                                $('#penerima_signature').val('');
                            });
                        }

                        // KIM form signature pad
                        var canvasPenerimaKim = document.getElementById('penerima-signature-pad-kim');
                        var penerimaSignaturePadKim;
                        if (canvasPenerimaKim) {
                            penerimaSignaturePadKim = new SignaturePad(canvasPenerimaKim, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasPenerimaKim() {
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasPenerimaKim.width = canvasPenerimaKim.offsetWidth * ratio;
                                canvasPenerimaKim.height = canvasPenerimaKim.offsetHeight * ratio;
                                canvasPenerimaKim.getContext("2d").scale(ratio, ratio);
                                penerimaSignaturePadKim.clear();
                            }

                            window.addEventListener('resize', resizeCanvasPenerimaKim);
                            resizeCanvasPenerimaKim();

                            $('#clear-penerima-signature-kim').on('click', function() {
                                penerimaSignaturePadKim.clear();
                                $('#penerima_signature_kim').val('');
                            });
                        }

                        // MBI form signature pad
                        var canvasPenerimaMbi = document.getElementById('penerima-signature-pad-mbi');
                        var penerimaSignaturePadMbi;
                        if (canvasPenerimaMbi) {
                            penerimaSignaturePadMbi = new SignaturePad(canvasPenerimaMbi, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasPenerimaMbi() {
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasPenerimaMbi.width = canvasPenerimaMbi.offsetWidth * ratio;
                                canvasPenerimaMbi.height = canvasPenerimaMbi.offsetHeight * ratio;
                                canvasPenerimaMbi.getContext("2d").scale(ratio, ratio);
                                penerimaSignaturePadMbi.clear();
                            }

                            window.addEventListener('resize', resizeCanvasPenerimaMbi);
                            resizeCanvasPenerimaMbi();

                            $('#clear-penerima-signature-mbi').on('click', function() {
                                penerimaSignaturePadMbi.clear();
                                $('#penerima_signature_mbi').val('');
                            });
                        }
                    @endif

                    @if ($step_penerima_done && !$step_koordinator_done)
                        // Initialize signature pad for disposisi (single or separate forms)
                        var canvasDisposisi = document.getElementById('disposisi-signature-pad');
                        var disposisiSignaturePad;
                        if (canvasDisposisi) {
                            disposisiSignaturePad = new SignaturePad(canvasDisposisi, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasDisposisi() {
                                // Use higher ratio for better quality when printing
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasDisposisi.width = canvasDisposisi.offsetWidth * ratio;
                                canvasDisposisi.height = canvasDisposisi.offsetHeight * ratio;
                                canvasDisposisi.getContext("2d").scale(ratio, ratio);
                                disposisiSignaturePad.clear();
                            }

                            window.addEventListener('resize', resizeCanvasDisposisi);
                            resizeCanvasDisposisi();

                            $('#clear-disposisi-signature').on('click', function() {
                                disposisiSignaturePad.clear();
                                $('#disposisi_signature').val('');
                            });
                        }

                        // For separate forms (kimia)
                        var canvasDisposisiKim = document.getElementById('disposisi-signature-pad-kim');
                        var disposisiSignaturePadKim;
                        if (canvasDisposisiKim) {
                            disposisiSignaturePadKim = new SignaturePad(canvasDisposisiKim, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasDisposisiKim() {
                                // Use higher ratio for better quality when printing
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasDisposisiKim.width = canvasDisposisiKim.offsetWidth * ratio;
                                canvasDisposisiKim.height = canvasDisposisiKim.offsetHeight * ratio;
                                canvasDisposisiKim.getContext("2d").scale(ratio, ratio);
                                disposisiSignaturePadKim.clear();
                            }

                            window.addEventListener('resize', resizeCanvasDisposisiKim);
                            resizeCanvasDisposisiKim();

                            $('#clear-disposisi-signature-kim').on('click', function() {
                                disposisiSignaturePadKim.clear();
                                $('#disposisi_signature_kim').val('');
                            });
                        }

                        // For separate forms (mikro)
                        var canvasDisposisiMbi = document.getElementById('disposisi-signature-pad-mbi');
                        var disposisiSignaturePadMbi;
                        if (canvasDisposisiMbi) {
                            disposisiSignaturePadMbi = new SignaturePad(canvasDisposisiMbi, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasDisposisiMbi() {
                                // Use higher ratio for better quality when printing
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasDisposisiMbi.width = canvasDisposisiMbi.offsetWidth * ratio;
                                canvasDisposisiMbi.height = canvasDisposisiMbi.offsetHeight * ratio;
                                canvasDisposisiMbi.getContext("2d").scale(ratio, ratio);
                                disposisiSignaturePadMbi.clear();
                            }

                            window.addEventListener('resize', resizeCanvasDisposisiMbi);
                            resizeCanvasDisposisiMbi();

                            $('#clear-disposisi-signature-mbi').on('click', function() {
                                disposisiSignaturePadMbi.clear();
                                $('#disposisi_signature_mbi').val('');
                            });
                        }
                    @endif

                    @if ($step_koordinator_done && !$step_analis_done)
                        // Initialize signature pad for analis (single or separate forms)
                        var canvasAnalis = document.getElementById('analis-signature-pad');
                        var analisSignaturePad;
                        if (canvasAnalis) {
                            analisSignaturePad = new SignaturePad(canvasAnalis, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasAnalis() {
                                // Use higher ratio for better quality when printing
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasAnalis.width = canvasAnalis.offsetWidth * ratio;
                                canvasAnalis.height = canvasAnalis.offsetHeight * ratio;
                                canvasAnalis.getContext("2d").scale(ratio, ratio);
                                analisSignaturePad.clear();
                            }

                            window.addEventListener('resize', resizeCanvasAnalis);
                            resizeCanvasAnalis();

                            $('#clear-analis-signature').on('click', function() {
                                analisSignaturePad.clear();
                                $('#disposisi_analis_signature').val('');
                            });
                        }

                        // For separate forms (kimia)
                        var canvasAnalisKim = document.getElementById('analis-signature-pad-kim');
                        var analisSignaturePadKim;
                        if (canvasAnalisKim) {
                            analisSignaturePadKim = new SignaturePad(canvasAnalisKim, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasAnalisKim() {
                                // Use higher ratio for better quality when printing
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasAnalisKim.width = canvasAnalisKim.offsetWidth * ratio;
                                canvasAnalisKim.height = canvasAnalisKim.offsetHeight * ratio;
                                canvasAnalisKim.getContext("2d").scale(ratio, ratio);
                                analisSignaturePadKim.clear();
                            }

                            window.addEventListener('resize', resizeCanvasAnalisKim);
                            resizeCanvasAnalisKim();

                            $('#clear-analis-signature-kim').on('click', function() {
                                analisSignaturePadKim.clear();
                                $('#disposisi_analis_signature_kim').val('');
                            });
                        }

                        // For separate forms (mikro)
                        var canvasAnalisMbi = document.getElementById('analis-signature-pad-mbi');
                        var analisSignaturePadMbi;
                        if (canvasAnalisMbi) {
                            analisSignaturePadMbi = new SignaturePad(canvasAnalisMbi, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });

                            function resizeCanvasAnalisMbi() {
                                // Use higher ratio for better quality when printing
                                var ratio = Math.max(window.devicePixelRatio || 1, 2);
                                canvasAnalisMbi.width = canvasAnalisMbi.offsetWidth * ratio;
                                canvasAnalisMbi.height = canvasAnalisMbi.offsetHeight * ratio;
                                canvasAnalisMbi.getContext("2d").scale(ratio, ratio);
                                analisSignaturePadMbi.clear();
                            }

                            window.addEventListener('resize', resizeCanvasAnalisMbi);
                            resizeCanvasAnalisMbi();

                            $('#clear-analis-signature-mbi').on('click', function() {
                                analisSignaturePadMbi.clear();
                                $('#disposisi_analis_signature_mbi').val('');
                            });
                        }
                    @endif
                @endif

                // Form submission with AJAX
                $('#form-penerimaan').on('submit', function(e) {
                    e.preventDefault();

                    // Before submit, save all signatures and ensure date format is correct




                    // Before submit, save all signatures
                    @if (!$use_tte)
                        @if (!$step_penerima_done)
                            // Save signature based on which form is being submitted
                            var labType = $('input[name="lab_type"]').val();
                            if (labType === 'KIM') {
                                // Form kimia
                                if (typeof penerimaSignaturePadKim !== 'undefined' &&
                                    penerimaSignaturePadKim &&
                                    !penerimaSignaturePadKim.isEmpty()) {
                                    var dataURL = penerimaSignaturePadKim.toDataURL();
                                    $('#penerima_signature_kim').val(dataURL);
                                    // Also set main signature field
                                    $('#penerima_signature').val(dataURL);
                                }
                            } else if (labType === 'MBI') {
                                // Form mikro
                                if (typeof penerimaSignaturePadMbi !== 'undefined' &&
                                    penerimaSignaturePadMbi &&
                                    !penerimaSignaturePadMbi.isEmpty()) {
                                    var dataURL = penerimaSignaturePadMbi.toDataURL();
                                    $('#penerima_signature_mbi').val(dataURL);
                                    // Also set main signature field
                                    $('#penerima_signature').val(dataURL);
                                }
                            } else {
                                // Single form - check all possible signature pads
                                if (typeof penerimaSignaturePad !== 'undefined' && penerimaSignaturePad &&
                                    !penerimaSignaturePad.isEmpty()) {
                                    var dataURL = penerimaSignaturePad.toDataURL();
                                    $('#penerima_signature').val(dataURL);
                                } else if (typeof penerimaSignaturePadKim !== 'undefined' &&
                                    penerimaSignaturePadKim &&
                                    !penerimaSignaturePadKim.isEmpty()) {
                                    var dataURL = penerimaSignaturePadKim.toDataURL();
                                    $('#penerima_signature').val(dataURL);
                                } else if (typeof penerimaSignaturePadMbi !== 'undefined' &&
                                    penerimaSignaturePadMbi &&
                                    !penerimaSignaturePadMbi.isEmpty()) {
                                    var dataURL = penerimaSignaturePadMbi.toDataURL();
                                    $('#penerima_signature').val(dataURL);
                                }
                            }
                        @endif
                        @if ($step_penerima_done && !$step_koordinator_done)
                            // Save signature for disposisi based on which form is being submitted
                            var labType = $('input[name="lab_type"]').val();
                            if (labType === 'KIM') {
                                // Form kimia
                                if (typeof disposisiSignaturePadKim !== 'undefined' &&
                                    disposisiSignaturePadKim && !disposisiSignaturePadKim.isEmpty()) {
                                    var dataURL = disposisiSignaturePadKim.toDataURL();
                                    $('#disposisi_signature_kim').val(dataURL);
                                    // Also set main signature field
                                    $('#disposisi_signature').val(dataURL);
                                }
                            } else if (labType === 'MBI') {
                                // Form mikro
                                if (typeof disposisiSignaturePadMbi !== 'undefined' &&
                                    disposisiSignaturePadMbi && !disposisiSignaturePadMbi.isEmpty()) {
                                    var dataURL = disposisiSignaturePadMbi.toDataURL();
                                    $('#disposisi_signature_mbi').val(dataURL);
                                    // Also set main signature field
                                    $('#disposisi_signature').val(dataURL);
                                }
                            } else {
                                // Single form
                                if (typeof disposisiSignaturePad !== 'undefined' && disposisiSignaturePad &&
                                    !disposisiSignaturePad.isEmpty()) {
                                    var dataURL = disposisiSignaturePad.toDataURL();
                                    $('#disposisi_signature').val(dataURL);
                                }
                            }
                        @endif
                        @if ($step_koordinator_done && !$step_analis_done)
                            // Save signature for analis based on which form is being submitted
                            var labType = $('input[name="lab_type"]').val();
                            // If labType is not set, try to determine from available forms
                            if (!labType) {
                                if ($('#disposisi_analis_kim').length) {
                                    labType = 'KIM';
                                } else if ($('#disposisi_analis_mbi').length) {
                                    labType = 'MBI';
                                }
                            }

                            if (labType === 'KIM') {
                                // Form kimia
                                if (typeof analisSignaturePadKim !== 'undefined' && analisSignaturePadKim &&
                                    !analisSignaturePadKim.isEmpty()) {
                                    var dataURL = analisSignaturePadKim.toDataURL();
                                    $('#disposisi_analis_signature_kim').val(dataURL);
                                    // Also set main signature field
                                    $('#disposisi_analis_signature').val(dataURL);
                                }
                            } else if (labType === 'MBI') {
                                // Form mikro
                                if (typeof analisSignaturePadMbi !== 'undefined' && analisSignaturePadMbi &&
                                    !analisSignaturePadMbi.isEmpty()) {
                                    var dataURL = analisSignaturePadMbi.toDataURL();
                                    $('#disposisi_analis_signature_mbi').val(dataURL);
                                    // Also set main signature field
                                    $('#disposisi_analis_signature').val(dataURL);
                                }
                            } else {
                                // Single form - check all possible signature pads
                                if (typeof analisSignaturePad !== 'undefined' && analisSignaturePad &&
                                    !analisSignaturePad.isEmpty()) {
                                    var dataURL = analisSignaturePad.toDataURL();
                                    $('#disposisi_analis_signature').val(dataURL);
                                } else if (typeof analisSignaturePadKim !== 'undefined' &&
                                    analisSignaturePadKim &&
                                    !analisSignaturePadKim.isEmpty()) {
                                    var dataURL = analisSignaturePadKim.toDataURL();
                                    $('#disposisi_analis_signature').val(dataURL);
                                } else if (typeof analisSignaturePadMbi !== 'undefined' &&
                                    analisSignaturePadMbi &&
                                    !analisSignaturePadMbi.isEmpty()) {
                                    var dataURL = analisSignaturePadMbi.toDataURL();
                                    $('#disposisi_analis_signature').val(dataURL);
                                }
                            }
                        @endif
                    @endif

                    // Copy values from separate form fields to main fields if using separate forms
                    var labType = $('#lab_type').val();
                    var currentStep = $('#current_step').val();
                    if (labType === 'KIM' && $('#disposisi_koordinator_kesmas_kim').length) {
                        // Copy from kimia form fields
                        $('#disposisi_koordinator_kesmas').val($('#disposisi_koordinator_kesmas_kim')
                            .val());
                        $('#disposisi_tanggal').val($('#disposisi_tanggal_kim').val());
                        $('#disposisi_analis').val($('#disposisi_analis_kim').val());
                        $('#disposisi_analis_tanggal').val($('#disposisi_analis_tanggal_kim').val());
                        if ($('#disposisi_signature_kim').val()) {
                            $('#disposisi_signature').val($('#disposisi_signature_kim').val());
                        }
                        if ($('#disposisi_analis_signature_kim').val()) {
                            $('#disposisi_analis_signature').val($('#disposisi_analis_signature_kim')
                                .val());
                        }
                    } else if (labType === 'MBI' && $('#disposisi_koordinator_kesmas_mbi').length) {
                        // Copy from mikro form fields
                        $('#disposisi_koordinator_kesmas').val($('#disposisi_koordinator_kesmas_mbi')
                            .val());
                        $('#disposisi_tanggal').val($('#disposisi_tanggal_mbi').val());
                        $('#disposisi_analis').val($('#disposisi_analis_mbi').val());
                        $('#disposisi_analis_tanggal').val($('#disposisi_analis_tanggal_mbi').val());
                        if ($('#disposisi_signature_mbi').val()) {
                            $('#disposisi_signature').val($('#disposisi_signature_mbi').val());
                        }
                        if ($('#disposisi_analis_signature_mbi').val()) {
                            $('#disposisi_analis_signature').val($('#disposisi_analis_signature_mbi')
                                .val());
                        }
                    } else {
                        // Single form: ensure lab_type reflects the available lab but keep existing values
                        if (!labType) {
                            if ($('#disposisi_koordinator_kesmas_kim').length) {
                                $('#lab_type').val('KIM');
                            } else if ($('#disposisi_koordinator_kesmas_mbi').length) {
                                $('#lab_type').val('MBI');
                            } else if ($('#disposisi_koordinator_kesmas').length) {
                                // Single form for analis - determine lab from available form
                                // Check if there's a KIM or MBI form available
                                if ($('#step-2-kim').length) {
                                    $('#lab_type').val('KIM');
                                } else if ($('#step-2-mbi').length) {
                                    $('#lab_type').val('MBI');
                                } else {
                                    // Try to get from hidden input or determine from context
                                    var currentStep = $('#current_step').val();
                                    if (currentStep == '2') {
                                        // For Step 2, check which koordinator list is available
                                        if ($('#disposisi_koordinator_kesmas option').length > 1) {
                                            // Try to determine from the first option or form context
                                            // This is a fallback - ideally lab_type should be set by onclick
                                        }
                                    }
                                }
                            }
                        }
                        // For single form, ensure signature is copied from the correct pad
                        // This is already handled in the signature saving section above
                        // But make sure signature is set if it exists in the single form
                        if (currentStep == '2' && !$('#disposisi_signature').val()) {
                            // If main signature field is empty, try to get from any available pad
                            if (typeof disposisiSignaturePad !== 'undefined' && disposisiSignaturePad &&
                                !disposisiSignaturePad.isEmpty()) {
                                var dataURL = disposisiSignaturePad.toDataURL();
                                $('#disposisi_signature').val(dataURL);
                            }
                        }
                        if (currentStep == '3' && !$('#disposisi_analis_signature').val()) {
                            // If main signature field is empty, try to get from any available pad
                            if (typeof analisSignaturePad !== 'undefined' && analisSignaturePad &&
                                !analisSignaturePad.isEmpty()) {
                                var dataURL = analisSignaturePad.toDataURL();
                                $('#disposisi_analis_signature').val(dataURL);
                            }
                        }
                    }

                    var formData = new FormData(this);
                    var submitBtn = $(this).find('button[type="submit"]');
                    var originalText = submitBtn.html();

                    submitBtn.prop('disabled', true).html('⏳ Menyimpan...');

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                // Check if all steps are completed
                                if (response.all_steps_completed && response.lab_id) {
                                    // Show sweet alert
                                    var labName = '';
                                    if (response.is_analis && response.completed_lab) {
                                        labName = response.completed_lab == 'KIM' ? 'Kimia' :
                                            'Mikrobiologi';
                                    }

                                    var message = 'Proses penerimaan sample';
                                    if (labName) {
                                        message += ' lab ' + labName;
                                    }
                                    message += ' telah selesai!';

                                    // Use sweetalert2 if available, otherwise use regular alert
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil!',
                                            text: message,
                                            confirmButtonText: 'Lanjut ke Pemeriksaan',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false
                                        }).then(function() {
                                            window.location.href =
                                                '{{ url('/mobile/testing') }}/' +
                                                '{{ $sample->id_samples }}' +
                                                '/pemeriksaan/' + response.lab_id;
                                        });
                                    } else {
                                        if (confirm(message +
                                                '\n\nKlik OK untuk melanjutkan ke form pemeriksaan.'
                                            )) {
                                            window.location.href =
                                                '{{ url('/mobile/testing') }}/' +
                                                '{{ $sample->id_samples }}' +
                                                '/pemeriksaan/' + response.lab_id;
                                        } else {
                                            location.reload();
                                        }
                                    }
                                } else {
                                    alert(response.message);
                                    location.reload();
                                }
                            } else {
                                alert('Error: ' + (response.error || 'Terjadi kesalahan'));
                                submitBtn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            alert('Error: ' + errorMsg);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                });
            });
        }
    </script>
</body>

</html>
