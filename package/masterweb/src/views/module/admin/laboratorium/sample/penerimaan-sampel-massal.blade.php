@extends('masterweb::template.admin.layout')

@section('title')
    Penerimaan Sampel - {{ $laboratorium->nama_laboratorium }}
@endsection

@section('styles')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Form Penerimaan Sampel - {{ $laboratorium->nama_laboratorium }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="window.history.back()">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('status') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Debug Info --}}
                            @if (config('app.debug'))
                                <div class="alert alert-warning">
                                    <strong>Debug Info:</strong><br>
                                    Lab Kode: {{ $laboratorium->kode_laboratorium ?? 'NULL' }}<br>
                                    Lab Nama: {{ $laboratorium->nama_laboratorium ?? 'NULL' }}<br>
                                    Penerima Sampel List ({{ count($penerima_sampel_list) }}):
                                    {{ implode(', ', $penerima_sampel_list) }}<br>
                                    Koordinator Kesmas List ({{ count($koordinator_kesmas_list) }}):
                                    {{ implode(', ', $koordinator_kesmas_list) }}
                                </div>
                            @endif

                            <div class="mb-3">
                                <h5>Informasi Permohonan Uji</h5>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td><strong>Nama Pelanggan</strong></td>
                                        <td>: {{ $permohonan_uji->customer->name_customer ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Laboratorium</strong></td>
                                        <td>: {{ $laboratorium->nama_laboratorium }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah Sampel</strong></td>
                                        <td>: {{ $samples->count() }} sampel</td>
                                    </tr>
                                </table>
                            </div>

                            @php
                                // Ambil existing data penerima dan disposisi dari sample pertama (jika ada)
                                $first_penerimaan =
                                    count($existing_penerimaan) > 0 ? reset($existing_penerimaan) : null;

                                // Format tanggal untuk Flatpickr (d/m/Y H:i)
                                $penerima_tanggal_default = '';
                                if ($first_penerimaan && $first_penerimaan->penerima_tanggal) {
                                    $penerima_tanggal_default = date(
                                        'd/m/Y H:i',
                                        strtotime($first_penerimaan->penerima_tanggal),
                                    );
                                }

                                $disposisi_tanggal_default = '';
                                if ($first_penerimaan && $first_penerimaan->disposisi_tanggal) {
                                    $disposisi_tanggal_default = date(
                                        'd/m/Y H:i',
                                        strtotime($first_penerimaan->disposisi_tanggal),
                                    );
                                }

                                $disposisi_analis_tanggal_default = '';
                                if ($first_penerimaan && $first_penerimaan->disposisi_analis_tanggal) {
                                    $disposisi_analis_tanggal_default = date(
                                        'd/m/Y H:i',
                                        strtotime($first_penerimaan->disposisi_analis_tanggal),
                                    );
                                }

                                $penerima_sampel_default = $first_penerimaan->penerima_sampel ?? '';
                                $disposisi_analis_default = $first_penerimaan->disposisi_analis ?? '';
                                $disposisi_koordinator_default = $first_penerimaan->disposisi_koordinator_kesmas ?? '';

                                // Cek step mana yang sudah selesai untuk menentukan form yang bisa diisi
                                // Periksa apakah masih ada sample yang belum memiliki data untuk step tertentu
                                $step_penerima_done = false;
                                $step_koordinator_done = false;
                                $step_analis_done = false;

                                // Hitung jumlah sample yang sudah memiliki data penerima DAN pengawetan
                                $samples_with_penerima = 0;
                                $samples_with_pengawetan = 0;
                                $total_samples = count($samples);

                                foreach ($samples as $sample) {
                                    $penerimaan = isset($existing_penerimaan[$sample->id_samples])
                                        ? $existing_penerimaan[$sample->id_samples]
                                        : null;

                                    // Cek data penerima
                                    if (
                                        $penerimaan &&
                                        !empty($penerimaan->penerima_sampel) &&
                                        !empty($penerimaan->penerima_tanggal)
                                    ) {
                                        $samples_with_penerima++;
                                    }

                                    // Cek data pengawetan
                                    if (
                                        $penerimaan &&
                                        (!empty($penerimaan->pengawetan_oleh) || !empty($penerimaan->pengawetan_dengan))
                                    ) {
                                        $samples_with_pengawetan++;
                                    }
                                }

                                // Step 1 selesai hanya jika semua sample sudah memiliki data penerima DAN semua sample sudah memiliki pengawetan
                                $step_penerima_done =
                                    $samples_with_penerima == $total_samples &&
                                    $samples_with_pengawetan == $total_samples &&
                                    $total_samples > 0;

                                // Hitung jumlah sample yang sudah memiliki disposisi koordinator
                                $samples_with_disposisi_koordinator = 0;
                                foreach ($samples as $sample) {
                                    $penerimaan = isset($existing_penerimaan[$sample->id_samples])
                                        ? $existing_penerimaan[$sample->id_samples]
                                        : null;
                                    if (
                                        $penerimaan &&
                                        !empty($penerimaan->disposisi_koordinator_kesmas) &&
                                        !empty($penerimaan->disposisi_tanggal)
                                    ) {
                                        $samples_with_disposisi_koordinator++;
                                    }
                                }

                                // Step 2 selesai hanya jika semua sample sudah memiliki disposisi koordinator
                                $step_koordinator_done =
                                    $samples_with_disposisi_koordinator == $total_samples &&
                                    $total_samples > 0 &&
                                    $step_penerima_done;

                                // Hitung jumlah sample yang sudah memiliki disposisi analis
                                $samples_with_disposisi_analis = 0;
                                foreach ($samples as $sample) {
                                    $penerimaan = isset($existing_penerimaan[$sample->id_samples])
                                        ? $existing_penerimaan[$sample->id_samples]
                                        : null;
                                    if (
                                        $penerimaan &&
                                        !empty($penerimaan->disposisi_analis) &&
                                        !empty($penerimaan->disposisi_analis_tanggal)
                                    ) {
                                        $samples_with_disposisi_analis++;
                                    }
                                }

                                // Step 3 selesai hanya jika semua sample sudah memiliki disposisi analis
                                $step_analis_done =
                                    $samples_with_disposisi_analis == $total_samples &&
                                    $total_samples > 0 &&
                                    $step_koordinator_done;
                            @endphp

                            {{-- Progress Indicator --}}
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h5 class="mb-0">Progress Penerimaan Sampel</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            <div
                                                class="badge badge-{{ $step_penerima_done ? 'success' : 'warning' }} badge-lg p-3 w-100">
                                                <i class="fas fa-{{ $step_penerima_done ? 'check-circle' : 'clock' }}"></i>
                                                <br>Step 1: Pengantar Sampel
                                                <br><small>{{ $step_penerima_done ? 'Selesai' : 'Belum Selesai' }}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div
                                                class="badge badge-{{ $step_koordinator_done ? 'success' : 'warning' }} badge-lg p-3 w-100">
                                                <i
                                                    class="fas fa-{{ $step_koordinator_done ? 'check-circle' : 'clock' }}"></i>
                                                <br>Step 2: Koordinator Kesmas
                                                <br><small>{{ $step_koordinator_done ? 'Selesai' : 'Belum Selesai' }}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div
                                                class="badge badge-{{ $step_analis_done ? 'success' : 'warning' }} badge-lg p-3 w-100">
                                                <i
                                                    class="fas fa-{{ $step_analis_done ? 'check-circle' : 'clock' }}"></i>
                                                <br>Step 3: Analis
                                                <br><small>{{ $step_analis_done ? 'Selesai' : 'Belum Selesai' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form
                                action="{{ route('elits-samples.penerimaan-sampel-store', [Request::segment(3), Request::segment(4), Request::segment(5)]) }}"
                                method="POST" id="form-penerimaan">
                                @csrf
                                <input type="hidden" name="current_step" id="current_step" value="">
                                <input type="hidden" name="save_all_steps" id="save_all_steps" value="0">
                                {{-- Hidden input untuk data verifikasi --}}
                                <input type="hidden" name="verifikasi_start_date" id="verifikasi_start_date_hidden" value="">
                                <input type="hidden" name="verifikasi_stop_date" id="verifikasi_stop_date_hidden" value="">
                                <input type="hidden" name="verifikasi_nama_petugas" id="verifikasi_nama_petugas_hidden" value="">
                                <input type="hidden" name="submit_verifikasi" id="submit_verifikasi" value="0">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th width="50">No</th>
                                                <th>No. Sampel</th>
                                                <th>Jenis Sampel</th>
                                                <th>Parameter</th>
                                                <th width="200">Pengawetan dilakukan oleh</th>
                                                <th width="250">Pendinginan</th>
                                                <th width="250">Kondisi Sampel</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($samples as $index => $sample)
                                                @php
                                                    $existing = isset($existing_penerimaan[$sample->id_samples])
                                                        ? $existing_penerimaan[$sample->id_samples]
                                                        : null;
                                                    $pengawetan_oleh = $existing ? $existing->pengawetan_oleh : null;

                                                    // Parse pengawetan dengan
                                                    $pengawetan_dengan = [];
                                                    if ($existing && $existing->pengawetan_dengan) {
                                                        $pengawetan_dengan = array_filter(
                                                            array_map(
                                                                'trim',
                                                                explode('; ', $existing->pengawetan_dengan),
                                                            ),
                                                        );
                                                    }

                                                    // Parse kondisi sample
                                                    $kondisi_sample = [];
                                                    if ($existing && $existing->kondisi_sample) {
                                                        $kondisi_sample = array_filter(
                                                            array_map('trim', explode('; ', $existing->kondisi_sample)),
                                                        );
                                                    }

                                                    // Extract kondisi lainnya text
                                                    $kondisi_lainnya_text = '';
                                                    $has_kondisi_lainnya = false;
                                                    foreach ($kondisi_sample as $item) {
                                                        if (stripos($item, 'lainnya:') === 0) {
                                                            $has_kondisi_lainnya = true;
                                                            $kondisi_lainnya_text = trim(substr($item, 8)); // Remove "lainnya:" prefix
                                                            break;
                                                        }
                                                    }

                                                    // Extract pengawetan lainnya text
                                                    $pengawetan_lainnya_text = '';
                                                    $has_pengawetan_lainnya = false;
                                                    foreach ($pengawetan_dengan as $item) {
                                                        if (stripos($item, 'lainnya:') === 0) {
                                                            $has_pengawetan_lainnya = true;
                                                            $pengawetan_lainnya_text = trim(substr($item, 8));
                                                            break;
                                                        }
                                                    }

                                                    // Tentukan default pengawetan_oleh berdasarkan is_sampling jika belum ada data
                                                    $pengawetan_oleh_default = $pengawetan_oleh;
                                                    if (empty($pengawetan_oleh_default)) {
                                                        // Jika permohonan uji adalah sampling (is_sampling = 1) → default Laboratorium
                                                        // Jika bukan sampling (is_sampling = 0) → default Pelanggan
                                                        if (
                                                            isset($permohonan_uji) &&
                                                            $permohonan_uji->is_sampling == 1
                                                        ) {
                                                            $pengawetan_oleh_default = 'Laboratorium';
                                                        } else {
                                                            $pengawetan_oleh_default = 'Pelanggan';
                                                        }
                                                    }

                                                    // Tentukan default pendinginan: jika belum ada data pengawetan_dengan, centang Pendinginan
                                                    $pendinginan_checked = in_array('Pendinginan', $pengawetan_dengan);
                                                    if (
                                                        !$existing ||
                                                        ($existing && empty($existing->pengawetan_dengan))
                                                    ) {
                                                        $pendinginan_checked = true;
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</strong>
                                                        <input type="hidden"
                                                            name="samples[{{ $sample->id_samples }}][sample_id]"
                                                            value="{{ $sample->id_samples }}">
                                                    </td>
                                                    <td>{{ $sample->name_sample_type }}</td>
                                                    <td>
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach ($sample->parameters as $param)
                                                                <li>☑ {{ $param->params_method }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="samples[{{ $sample->id_samples }}][pengawetan_oleh]"
                                                                id="pengawetan_pelanggan_{{ $sample->id_samples }}"
                                                                value="Pelanggan"
                                                                {{ $pengawetan_oleh_default == 'Pelanggan' ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="pengawetan_pelanggan_{{ $sample->id_samples }}">
                                                                Pelanggan
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="samples[{{ $sample->id_samples }}][pengawetan_oleh]"
                                                                id="pengawetan_lab_{{ $sample->id_samples }}"
                                                                value="Laboratorium"
                                                                {{ $pengawetan_oleh_default == 'Laboratorium' ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="pengawetan_lab_{{ $sample->id_samples }}">
                                                                Laboratorium
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="samples[{{ $sample->id_samples }}][pengawetan_pendinginan]"
                                                                id="pendinginan_{{ $sample->id_samples }}"
                                                                {{ $pendinginan_checked ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="pendinginan_{{ $sample->id_samples }}">
                                                                Pendinginan
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="samples[{{ $sample->id_samples }}][pengawetan_hno3]"
                                                                id="hno3_{{ $sample->id_samples }}"
                                                                {{ in_array('HNO3', $pengawetan_dengan) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="hno3_{{ $sample->id_samples }}">
                                                                HNO₃
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="samples[{{ $sample->id_samples }}][pengawetan_h2so4]"
                                                                id="h2so4_{{ $sample->id_samples }}"
                                                                {{ in_array('H2SO4', $pengawetan_dengan) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="h2so4_{{ $sample->id_samples }}">
                                                                H₂SO₄
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="samples[{{ $sample->id_samples }}][pengawetan_naoh]"
                                                                id="naoh_{{ $sample->id_samples }}"
                                                                {{ in_array('NaOH', $pengawetan_dengan) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="naoh_{{ $sample->id_samples }}">
                                                                NaOH
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input pengawetan-lainnya-checkbox"
                                                                type="checkbox"
                                                                name="samples[{{ $sample->id_samples }}][pengawetan_lainnya]"
                                                                id="pengawetan_lainnya_{{ $sample->id_samples }}"
                                                                data-target="pengawetan_lainnya_text_{{ $sample->id_samples }}"
                                                                {{ $has_pengawetan_lainnya ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="pengawetan_lainnya_{{ $sample->id_samples }}">
                                                                .....................
                                                            </label>
                                                        </div>
                                                        <input type="text"
                                                            class="form-control form-control-sm mt-1 pengawetan-lainnya-text"
                                                            name="samples[{{ $sample->id_samples }}][pengawetan_lainnya_text]"
                                                            id="pengawetan_lainnya_text_{{ $sample->id_samples }}"
                                                            placeholder="Sebutkan lainnya"
                                                            value="{{ $pengawetan_lainnya_text }}"
                                                            style="display: {{ $has_pengawetan_lainnya ? 'block' : 'none' }};">
                                                    </td>
                                                    <td>
                                                        <div class="mb-2">
                                                            <strong>1. TEMPAT / KEMASAN</strong>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input kelayakan-radio" type="radio"
                                                                name="samples[{{ $sample->id_samples }}][kelayakan]"
                                                                id="layak_{{ $sample->id_samples }}" value="1"
                                                                data-target="kondisi_tidak_layak_{{ $sample->id_samples }}"
                                                                {{ $existing && $existing->kelayakan_tempat_kemasan == 'layak' ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="layak_{{ $sample->id_samples }}">
                                                                LAYAK
                                                            </label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input kelayakan-radio" type="radio"
                                                                name="samples[{{ $sample->id_samples }}][kelayakan]"
                                                                id="tidak_layak_{{ $sample->id_samples }}" value="0"
                                                                data-target="kondisi_tidak_layak_{{ $sample->id_samples }}"
                                                                {{ $existing && $existing->kelayakan_tempat_kemasan == 'tidak layak' ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="tidak_layak_{{ $sample->id_samples }}">
                                                                TIDAK LAYAK
                                                            </label>
                                                        </div>

                                                        <div id="kondisi_tidak_layak_{{ $sample->id_samples }}"
                                                            class="kondisi-tidak-layak"
                                                            style="display: {{ $existing && $existing->kelayakan_tempat_kemasan == 'tidak layak' ? 'block' : 'none' }};">
                                                            <div class="mb-1"><strong>2. BERAT / VOL</strong></div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="samples[{{ $sample->id_samples }}][kondisi_tidak_diawetkan]"
                                                                    id="tidak_diawetkan_{{ $sample->id_samples }}"
                                                                    {{ in_array('tidak diawetkan di lapangan', $kondisi_sample) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="tidak_diawetkan_{{ $sample->id_samples }}">
                                                                    tidak diawetkan di lapangan
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="samples[{{ $sample->id_samples }}][kondisi_wadah_tidak_sesuai]"
                                                                    id="wadah_tidak_sesuai_{{ $sample->id_samples }}"
                                                                    {{ in_array('wadah sampel tidak sesuai', $kondisi_sample) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="wadah_tidak_sesuai_{{ $sample->id_samples }}">
                                                                    wadah sampel tidak sesuai
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="samples[{{ $sample->id_samples }}][kondisi_kadaluarsa]"
                                                                    id="kadaluarsa_{{ $sample->id_samples }}"
                                                                    {{ in_array('sampel kadaluarsa', $kondisi_sample) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="kadaluarsa_{{ $sample->id_samples }}">
                                                                    sampel kadaluarsa
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input kondisi-lainnya-checkbox"
                                                                    type="checkbox"
                                                                    name="samples[{{ $sample->id_samples }}][kondisi_lainnya]"
                                                                    id="kondisi_lainnya_{{ $sample->id_samples }}"
                                                                    data-target="kondisi_lainnya_text_{{ $sample->id_samples }}"
                                                                    {{ $has_kondisi_lainnya ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="kondisi_lainnya_{{ $sample->id_samples }}">
                                                                    lainnya, sebutkan
                                                                </label>
                                                            </div>
                                                            <textarea class="form-control form-control-sm mt-1 kondisi-lainnya-text"
                                                                name="samples[{{ $sample->id_samples }}][kondisi_lainnya_text]"
                                                                id="kondisi_lainnya_text_{{ $sample->id_samples }}" rows="2" placeholder="Catatan..."
                                                                style="display: {{ $has_kondisi_lainnya ? 'block' : 'none' }};">{{ $kondisi_lainnya_text }}</textarea>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="border p-3">
                                            <p class="mb-2"><strong>Catatan Tambahan:</strong></p>
                                            <p class="mb-0"><em>Kondisi sampel pada saat diterima termasuk abnormalitas
                                                    atau penyimpangan dari kondisi normal</em></p>
                                            <p class="mb-0 text-danger"><small>* Pastikan semua data telah diisi dengan
                                                    benar sebelum menyimpan</small></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Penerima Sampel --}}
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card border-{{ $step_penerima_done ? 'success' : 'info' }}">
                                            <div
                                                class="card-header bg-{{ $step_penerima_done ? 'success' : 'info' }} text-white">
                                                <h5 class="mb-0">
                                                    <i
                                                        class="fas fa-{{ $step_penerima_done ? 'check-circle' : 'user-check' }}"></i>
                                                    Step 1: Pengantar Sampel
                                                    @if ($step_penerima_done)
                                                        <span class="badge badge-light ml-2">Selesai</span>
                                                    @else
                                                        <span class="badge badge-warning ml-2">Wajib Diisi</span>
                                                    @endif
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                @if (!$step_penerima_done)
                                                    <div class="alert alert-info mb-3">
                                                        <i class="fas fa-info-circle"></i>
                                                        <strong>Catatan:</strong> Step 1 dianggap selesai jika:
                                                        <ul class="mb-0 mt-2">
                                                            <li>Semua sample sudah memiliki data penerima (Nama Penerima dan
                                                                Tanggal Penerimaan)</li>
                                                            <li><strong>DAN</strong> semua sample sudah memiliki data
                                                                pengawetan (Pengawetan dilakukan oleh dan/atau
                                                                Pendinginan/HNO₃/H₂SO₄/NaOH/Lainnya)</li>
                                                        </ul>
                                                    </div>
                                                @endif
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="penerima_sampel">Nama Penerima <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="penerima_sampel" id="penerima_sampel"
                                                                class="form-control"
                                                                {{ $step_penerima_done ? 'disabled' : '' }}>
                                                                <option value="">-- Pilih Pengantar Sampel --</option>
                                                                @foreach ($penerima_sampel_list as $penerima)
                                                                    <option value="{{ $penerima }}"
                                                                        {{ $penerima_sampel_default == $penerima ? 'selected' : '' }}>
                                                                        {{ $penerima }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="penerima_tanggal">Tanggal Penerimaan <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="penerima_tanggal"
                                                                id="penerima_tanggal" class="form-control"
                                                                placeholder="dd/mm/yyyy HH:mm"
                                                                {{ $step_penerima_done ? 'disabled' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Tanda Tangan / Paraf Pengantar</label>
                                                            @if ($use_tte)
                                                                <div class="alert alert-info">
                                                                    <i class="fas fa-info-circle"></i> Tanda tangan
                                                                    elektronik akan digunakan (TTE)
                                                                </div>
                                                                <input type="hidden" name="penerima_signature_type"
                                                                    value="tte">
                                                            @else
                                                                @if ($step_penerima_done && $first_penerimaan && $first_penerimaan->penerima_signature)
                                                                    {{-- Show saved signature as image when step is done --}}
                                                                    <div class="border rounded p-2 bg-light text-center">
                                                                        <img src="{{ $first_penerimaan->penerima_signature }}"
                                                                            alt="Tanda Tangan Penerima"
                                                                            style="max-height: 150px;">
                                                                        <p class="mb-0 mt-2 text-muted"><small><i
                                                                                    class="fas fa-check-circle text-success"></i>
                                                                                Tanda tangan tersimpan</small></p>
                                                                    </div>
                                                                @else
                                                                    <div class="border rounded"
                                                                        style="position: relative;">
                                                                        <canvas id="penerima-signature-pad"
                                                                            style="width: 100%; height: 150px; cursor: crosshair;"></canvas>
                                                                    </div>
                                                                    <div class="mt-2">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-warning"
                                                                            id="clear-penerima-signature">
                                                                            <i class="fas fa-eraser"></i> Bersihkan
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                                <input type="hidden" name="penerima_signature"
                                                                    id="penerima_signature"
                                                                    value="{{ $first_penerimaan->penerima_signature ?? '' }}">
                                                                <input type="hidden" name="penerima_signature_type"
                                                                    value="canvas">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Disposisi ke Koordinator Kesmas --}}
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div
                                            class="card border-{{ $step_koordinator_done ? 'success' : 'primary' }}">
                                            <div
                                                class="card-header bg-{{ $step_koordinator_done ? 'success' : 'primary' }} text-white">
                                                <h5 class="mb-0">
                                                    <i
                                                        class="fas fa-{{ $step_koordinator_done ? 'check-circle' : 'file-signature' }}"></i>
                                                    Step 2: Disposisi ke Koordinator Kesmas
                                                    @if ($step_koordinator_done)
                                                        <span class="badge badge-light ml-2">Selesai</span>
                                                    @else
                                                        <span class="badge badge-warning ml-2">Wajib Diisi</span>
                                                    @endif
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="disposisi_koordinator_kesmas">Koordinator
                                                                Kesmas <span class="text-danger">*</span></label>
                                                            <select name="disposisi_koordinator_kesmas"
                                                                id="disposisi_koordinator_kesmas" class="form-control"
                                                                {{ $step_koordinator_done ? 'disabled' : '' }}>
                                                                <option value="">-- Pilih Koordinator Kesmas --
                                                                </option>
                                                                @foreach ($koordinator_kesmas_list as $koordinator)
                                                                    <option value="{{ $koordinator }}"
                                                                        {{ $disposisi_koordinator_default == $koordinator ? 'selected' : '' }}>
                                                                        {{ $koordinator }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="disposisi_tanggal">Tanggal Disposisi <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="disposisi_tanggal"
                                                                id="disposisi_tanggal" class="form-control"
                                                                placeholder="dd/mm/yyyy HH:mm"
                                                                {{ $step_koordinator_done ? 'disabled' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Tanda Tangan / Paraf</label>
                                                            @if ($use_tte)
                                                                <div class="alert alert-info">
                                                                    <i class="fas fa-info-circle"></i> Tanda tangan
                                                                    elektronik akan digunakan (TTE)
                                                                </div>
                                                                <input type="hidden" name="disposisi_signature_type"
                                                                    value="tte">
                                                            @else
                                                                @if ($step_koordinator_done && $first_penerimaan && $first_penerimaan->disposisi_signature)
                                                                    {{-- Show saved signature as image when step is done --}}
                                                                    <div class="border rounded p-2 bg-light text-center">
                                                                        <img src="{{ $first_penerimaan->disposisi_signature }}"
                                                                            alt="Tanda Tangan Koordinator"
                                                                            style="max-height: 150px;">
                                                                        <p class="mb-0 mt-2 text-muted"><small><i
                                                                                    class="fas fa-check-circle text-success"></i>
                                                                                Tanda tangan tersimpan</small></p>
                                                                    </div>
                                                                @else
                                                                    <div class="border rounded"
                                                                        style="position: relative;">
                                                                        <canvas id="signature-pad"
                                                                            style="width: 100%; height: 150px; cursor: crosshair;"></canvas>
                                                                    </div>
                                                                    <div class="mt-2">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-warning"
                                                                            id="clear-signature">
                                                                            <i class="fas fa-eraser"></i> Bersihkan
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                                <input type="hidden" name="disposisi_signature"
                                                                    id="disposisi_signature"
                                                                    value="{{ $first_penerimaan->disposisi_signature ?? '' }}">
                                                                <input type="hidden" name="disposisi_signature_type"
                                                                    value="canvas">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Disposisi ke Analis --}}
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div
                                            class="card border-{{ $step_analis_done ? 'success' : 'warning' }}">
                                            <div
                                                class="card-header bg-{{ $step_analis_done ? 'success' : 'warning' }} text-white">
                                                <h5 class="mb-0">
                                                    <i
                                                        class="fas fa-{{ $step_analis_done ? 'check-circle' : 'flask' }}"></i>
                                                    Step 3: Disposisi ke Analis
                                                    @if ($step_analis_done)
                                                        <span class="badge badge-light ml-2">Selesai</span>
                                                    @else
                                                        <span class="badge badge-info ml-2">Wajib Diisi</span>
                                                    @endif
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="disposisi_analis">Nama Analis <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="disposisi_analis" id="disposisi_analis"
                                                                class="form-control"
                                                                {{ $step_analis_done ? 'disabled' : '' }}>
                                                                <option value="">-- Pilih Analis --</option>
                                                                @foreach ($analis_list as $analis)
                                                                    <option value="{{ $analis }}"
                                                                        {{ $disposisi_analis_default == $analis ? 'selected' : '' }}>
                                                                        {{ $analis }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="disposisi_analis_tanggal">Tanggal Disposisi <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="disposisi_analis_tanggal"
                                                                id="disposisi_analis_tanggal" class="form-control"
                                                                placeholder="dd/mm/yyyy HH:mm"
                                                                {{ $step_analis_done ? 'disabled' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Tanda Tangan / Paraf Analis</label>
                                                            @if ($use_tte)
                                                                <div class="alert alert-info">
                                                                    <i class="fas fa-info-circle"></i> Tanda tangan
                                                                    elektronik akan digunakan (TTE)
                                                                </div>
                                                                <input type="hidden"
                                                                    name="disposisi_analis_signature_type" value="tte">
                                                            @else
                                                                @if ($step_analis_done && $first_penerimaan && $first_penerimaan->disposisi_analis_signature)
                                                                    {{-- Show saved signature as image when step is done --}}
                                                                    <div class="border rounded p-2 bg-light text-center">
                                                                        <img src="{{ $first_penerimaan->disposisi_analis_signature }}"
                                                                            alt="Tanda Tangan Analis"
                                                                            style="max-height: 150px;">
                                                                        <p class="mb-0 mt-2 text-muted"><small><i
                                                                                    class="fas fa-check-circle text-success"></i>
                                                                                Tanda tangan tersimpan</small></p>
                                                                    </div>
                                                                @else
                                                                    <div class="border rounded"
                                                                        style="position: relative;">
                                                                        <canvas id="analis-signature-pad"
                                                                            style="width: 100%; height: 150px; cursor: crosshair;"></canvas>
                                                                    </div>
                                                                    <div class="mt-2">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-warning"
                                                                            id="clear-analis-signature">
                                                                            <i class="fas fa-eraser"></i> Bersihkan
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                                <input type="hidden" name="disposisi_analis_signature"
                                                                    id="disposisi_analis_signature"
                                                                    value="{{ $first_penerimaan->disposisi_analis_signature ?? '' }}">
                                                                <input type="hidden"
                                                                    name="disposisi_analis_signature_type" value="canvas">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Verifikasi Penerimaan Sampel --}}
                                @php
                                    // Ambil sample pertama untuk verifikasi
                                    $first_sample = $samples->first();
                                    
                                    // Cek apakah sudah ada verifikasi untuk Penerimaan Sampel (step 7)
                                    $verification_penerimaan = null;
                                    if ($first_sample) {
                                        $verification_penerimaan = \Smt\Masterweb\Models\VerificationActivitySample::where('id_sample', $first_sample->id_samples)
                                            ->where('id_verification_activity', 7)
                                            ->first();
                                    }
                                    
                                    // Ambil list petugas dari Master Petugas yang terkait dengan lab aktif
                                    $list_petugas_penerimaan = \Smt\Masterweb\Models\Petugas::query()
                                        ->get()
                                        ->filter(function ($petugas) use ($idlabs) {
                                            $nama = trim((string) ($petugas->nama ?? ''));
                                            if ($nama === '') {
                                                return false;
                                            }

                                            // Cek keterkaitan lab (field lab_id bertipe array/json)
                                            $labIds = $petugas->lab_id ?? [];
                                            if (!is_array($labIds)) {
                                                $decodedLab = json_decode((string) $labIds, true);
                                                $labIds = is_array($decodedLab) ? $decodedLab : [$labIds];
                                            }
                                            return in_array($idlabs, $labIds, true);
                                        })
                                        ->pluck('nama')
                                        ->map(function ($nama) {
                                            return trim((string) $nama);
                                        })
                                        ->filter(function ($nama) {
                                            return $nama !== '';
                                        })
                                        ->unique()
                                        ->sort()
                                        ->values()
                                        ->toArray();

                                    // Koordinator Step 2 bisa belum ada di master Petugas — tambahkan agar bisa dipilih
                                    $koordStep2 = trim((string) ($disposisi_koordinator_default ?? ''));
                                    if ($step_koordinator_done && $koordStep2 !== '') {
                                        $already = false;
                                        foreach ($list_petugas_penerimaan as $n) {
                                            if (strcasecmp(trim((string) $n), $koordStep2) === 0) {
                                                $already = true;
                                                break;
                                            }
                                        }
                                        if (!$already) {
                                            array_unshift($list_petugas_penerimaan, $koordStep2);
                                        }
                                    }

                                    // Format tanggal default
                                    $verifikasi_start_default = '';
                                    $verifikasi_stop_default = '';
                                    if ($verification_penerimaan && !empty($verification_penerimaan->start_date)) {
                                        $verifikasi_start_default = date('d/m/Y H:i', strtotime($verification_penerimaan->start_date));
                                    }
                                    if ($verification_penerimaan && !empty($verification_penerimaan->stop_date)) {
                                        $verifikasi_stop_default = date('d/m/Y H:i', strtotime($verification_penerimaan->stop_date));
                                    }
                                    // Belum ada verifikasi tersimpan: default dari Step 2 (tanggal disposisi koordinator)
                                    if (
                                        $step_koordinator_done
                                        && !empty($disposisi_tanggal_default)
                                    ) {
                                        if ($verifikasi_start_default === '') {
                                            $verifikasi_start_default = $disposisi_tanggal_default;
                                        }
                                        if ($verifikasi_stop_default === '') {
                                            $verifikasi_stop_default = $disposisi_tanggal_default;
                                        }
                                    }

                                    $verifikasi_nama_petugas_selected = '';
                                    if ($verification_penerimaan && trim((string) ($verification_penerimaan->nama_petugas ?? '')) !== '') {
                                        $verifikasi_nama_petugas_selected = trim((string) $verification_penerimaan->nama_petugas);
                                    } elseif ($step_koordinator_done && $koordStep2 !== '') {
                                        $verifikasi_nama_petugas_selected = $koordStep2;
                                    }
                                @endphp
                                
                                @if ($first_sample)
                                    {{-- UI disembunyikan: Nama Petugas verifikasi disamakan dengan Koordinator Kesmas (Step 2) via JS --}}
                                    <div class="row mt-4 d-none" id="section-verifikasi-penerimaan-sampel" aria-hidden="true">
                                        <div class="col-12">
                                            <div class="card border-info">
                                                <div class="card-header bg-info text-white">
                                                    <h5 class="mb-0">
                                                        <i class="fas fa-clipboard-check"></i>
                                                        Verifikasi Penerimaan Sampel
                                                        @if ($verification_penerimaan && $verification_penerimaan->is_done == 1)
                                                            <span class="badge badge-light ml-2">Selesai</span>
                                                        @else
                                                            <span class="badge badge-warning ml-2">Wajib Diisi</span>
                                                        @endif
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    @if ($verification_penerimaan && $verification_penerimaan->is_done == 1)
                                                        <div class="alert alert-success">
                                                            <i class="fas fa-check-circle"></i>
                                                            <strong>Verifikasi sudah dilakukan</strong><br>
                                                            Tanggal Mulai: {{ \Smt\Masterweb\Helpers\DateHelper::formatDate($verification_penerimaan->start_date) }}<br>
                                                            Tanggal Selesai: {{ \Smt\Masterweb\Helpers\DateHelper::formatDate($verification_penerimaan->stop_date) }}<br>
                                                            Nama Petugas: {{ $verification_penerimaan->nama_petugas }}
                                                        </div>
                                                    @else
                                                        <form action="{{ route('elits-samples.verification-analytic-2', [$first_sample->id_samples]) }}" 
                                                            method="post" class="formVerifikasiPenerimaan">
                                                            @csrf
                                                            <input type="hidden" name="verification_step" value="7">
                                                            
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="verifikasi_start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                                                                        <input type="text" 
                                                                            name="start_date" 
                                                                            id="verifikasi_start_date" 
                                                                            class="form-control" 
                                                                            placeholder="dd/mm/yyyy HH:mm"
                                                                            value="{{ $verifikasi_start_default }}"
                                                                            required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="verifikasi_stop_date">Tanggal Selesai <span class="text-danger">*</span></label>
                                                                        <input type="text" 
                                                                            name="stop_date" 
                                                                            id="verifikasi_stop_date" 
                                                                            class="form-control" 
                                                                            placeholder="dd/mm/yyyy HH:mm"
                                                                            value="{{ $verifikasi_stop_default }}"
                                                                            required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="verifikasi_nama_petugas">Nama Petugas <span class="text-danger">*</span></label>
                                                                        <select name="nama_petugas" 
                                                                            id="verifikasi_nama_petugas" 
                                                                            class="form-control" 
                                                                            required>
                                                                            <option value="">-- Pilih Petugas --</option>
                                                                            @foreach ($list_petugas_penerimaan as $petugas)
                                                                                <option value="{{ trim($petugas) }}"
                                                                                    {{ strcasecmp(trim((string) $verifikasi_nama_petugas_selected), trim((string) $petugas)) === 0 ? 'selected' : '' }}>
                                                                                    {{ trim($petugas) }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="row mt-3">
                                    <div class="col-12 text-right">
                                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                            <i class="fas fa-times"></i> Batal
                                        </button>

                                        {{-- Tombol Simpan Semua Step - menyimpan Step 1, 2, 3, dan Verifikasi --}}
                                        <button type="button" class="btn btn-success mr-2" id="btn-save-all">
                                            <i class="fas fa-save"></i> Simpan Semua Step (1-3 + Verifikasi)
                                        </button>
                                        
                                        @if ($step_analis_done)
                                            <a href="{{ route('elits-samples.verification-2', [$samples->first()->id_samples, $idlabs]) }}"
                                                class="btn btn-success">
                                                <i class="fas fa-arrow-left"></i> Kembali ke Verifikasi
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Flatpickr JS -->
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>

    <script>
        $(document).ready(function() {
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

            // Initialize Signature Pad (jika menggunakan canvas)
            @if (!$use_tte)
                // Signature Pad untuk Penerima Sampel
                var canvasPenerima = document.getElementById('penerima-signature-pad');
                var penerimaSignaturePad;
                if (canvasPenerima) {
                    penerimaSignaturePad = new SignaturePad(canvasPenerima, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)'
                    });

                    // Adjust canvas size
                    function resizeCanvasPenerima() {
                        var ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvasPenerima.width = canvasPenerima.offsetWidth * ratio;
                        canvasPenerima.height = canvasPenerima.offsetHeight * ratio;
                        canvasPenerima.getContext("2d").scale(ratio, ratio);
                        penerimaSignaturePad.clear();
                    }

                    window.addEventListener('resize', resizeCanvasPenerima);
                    resizeCanvasPenerima();

                    // Clear button
                    $('#clear-penerima-signature').on('click', function() {
                        penerimaSignaturePad.clear();
                        $('#penerima_signature').val('');
                    });
                }

                // Signature Pad untuk Analis
                var canvasAnalis = document.getElementById('analis-signature-pad');
                var analisSignaturePad;
                if (canvasAnalis) {
                    analisSignaturePad = new SignaturePad(canvasAnalis, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)'
                    });

                    // Adjust canvas size
                    function resizeCanvasAnalis() {
                        var ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvasAnalis.width = canvasAnalis.offsetWidth * ratio;
                        canvasAnalis.height = canvasAnalis.offsetHeight * ratio;
                        canvasAnalis.getContext("2d").scale(ratio, ratio);
                        analisSignaturePad.clear();
                    }

                    window.addEventListener('resize', resizeCanvasAnalis);
                    resizeCanvasAnalis();

                    // Clear button
                    $('#clear-analis-signature').on('click', function() {
                        analisSignaturePad.clear();
                        $('#disposisi_analis_signature').val('');
                    });
                }

                // Signature Pad untuk Disposisi (Koordinator Kesmas)
                var canvasDisposisi = document.getElementById('signature-pad');
                var disposisiSignaturePad;
                if (canvasDisposisi) {
                    disposisiSignaturePad = new SignaturePad(canvasDisposisi, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)'
                    });

                    // Adjust canvas size
                    function resizeCanvasDisposisi() {
                        var ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvasDisposisi.width = canvasDisposisi.offsetWidth * ratio;
                        canvasDisposisi.height = canvasDisposisi.offsetHeight * ratio;
                        canvasDisposisi.getContext("2d").scale(ratio, ratio);
                        disposisiSignaturePad.clear();
                    }

                    window.addEventListener('resize', resizeCanvasDisposisi);
                    resizeCanvasDisposisi();

                    // Clear button
                    $('#clear-signature').on('click', function() {
                        disposisiSignaturePad.clear();
                        $('#disposisi_signature').val('');
                    });
                }

                // Before submit, save all signatures to hidden inputs
                $('#form-penerimaan').on('submit', function(e) {
                    // Simpan semua signature sebelum submit
                    if (penerimaSignaturePad && !penerimaSignaturePad.isEmpty()) {
                        var dataURL = penerimaSignaturePad.toDataURL();
                        $('#penerima_signature').val(dataURL);
                    }
                    if (analisSignaturePad && !analisSignaturePad.isEmpty()) {
                        var dataURL = analisSignaturePad.toDataURL();
                        $('#disposisi_analis_signature').val(dataURL);
                    }
                    if (disposisiSignaturePad && !disposisiSignaturePad.isEmpty()) {
                        var dataURL = disposisiSignaturePad.toDataURL();
                        $('#disposisi_signature').val(dataURL);
                    }
                });
            @endif

            // Default jam kelipatan 10 menit (00, 10, 20, 30, 40, 50)
            function roundToTenMinutes(date) {
                var d = new Date(date.getTime());
                d.setSeconds(0, 0);
                var minutes = d.getMinutes();
                var rounded = Math.round(minutes / 10) * 10;
                if (rounded === 60) {
                    d.setHours(d.getHours() + 1);
                    d.setMinutes(0);
                } else {
                    d.setMinutes(rounded);
                }
                return d;
            }

            function parseFlatpickrDate(val) {
                if (!val) return null;
                // d/m/Y H:i
                var m = String(val).trim().match(/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})/);
                if (m) {
                    return new Date(+m[3], +m[2] - 1, +m[1], +m[4], +m[5], 0, 0);
                }
                var d = new Date(val);
                return isNaN(d.getTime()) ? null : d;
            }

            function defaultDateTenMinutes(storedVal) {
                var parsed = parseFlatpickrDate(storedVal);
                // Data tersimpan: pakai apa adanya; default baru: bulatkan ke 10 menit
                if (parsed) {
                    return parsed;
                }
                return roundToTenMinutes(new Date());
            }

            function addTenMinutes(date) {
                return new Date(date.getTime() + 10 * 60000);
            }

            var fpTimeOpts = {
                enableTime: true,
                dateFormat: "d/m/Y H:i",
                time_24hr: true,
                locale: "id",
                minuteIncrement: 10
            };

            // Default tanggal berantai antar step: tiap step berikutnya +10 menit
            var penerimaDefault = "{{ $penerima_tanggal_default }}";
            var disposisiDefault = "{{ $disposisi_tanggal_default }}";
            var analisDefault = "{{ $disposisi_analis_tanggal_default }}";
            var disposisiHasStored = !!parseFlatpickrDate(disposisiDefault);
            var analisHasStored = !!parseFlatpickrDate(analisDefault);

            var penerimaDate = defaultDateTenMinutes(penerimaDefault);
            // Step 2: data tersimpan, atau step 1 + 10 menit
            var disposisiDate = disposisiHasStored
                ? parseFlatpickrDate(disposisiDefault)
                : addTenMinutes(penerimaDate);
            // Step 3: data tersimpan, atau step 2 + 10 menit
            var analisDate = analisHasStored
                ? parseFlatpickrDate(analisDefault)
                : addTenMinutes(disposisiDate);

            // Initialize Flatpickr for penerima / disposisi tanggal
            var penerimaFlatpickr = null;
            var disposisiFlatpickr = null;
            var analisFlatpickr = null;

            if ($('#penerima_tanggal').length) {
                penerimaFlatpickr = $('#penerima_tanggal').flatpickr(Object.assign({}, fpTimeOpts, {
                    defaultDate: penerimaDate,
                    onChange: function(selectedDates) {
                        if (!selectedDates.length || disposisiHasStored) {
                            return;
                        }
                        var nextDisposisi = addTenMinutes(selectedDates[0]);
                        if (disposisiFlatpickr) {
                            disposisiFlatpickr.setDate(nextDisposisi, false);
                        }
                        if (!analisHasStored && analisFlatpickr) {
                            analisFlatpickr.setDate(addTenMinutes(nextDisposisi), false);
                        }
                    }
                }));
            }

            if ($('#disposisi_tanggal').length) {
                disposisiFlatpickr = $('#disposisi_tanggal').flatpickr(Object.assign({}, fpTimeOpts, {
                    defaultDate: disposisiDate,
                    onChange: function(selectedDates) {
                        if (!selectedDates.length || analisHasStored) {
                            return;
                        }
                        if (analisFlatpickr) {
                            analisFlatpickr.setDate(addTenMinutes(selectedDates[0]), false);
                        }
                    }
                }));
            }

            if ($('#disposisi_analis_tanggal').length) {
                analisFlatpickr = $('#disposisi_analis_tanggal').flatpickr(Object.assign({}, fpTimeOpts, {
                    defaultDate: analisDate
                }));
            }

            // Samakan Nama Petugas (verifikasi) dengan Koordinator Kesmas (Step 2), termasuk opsi yang belum ada di select petugas
            function ensureOptionValue($select, value) {
                if (!value) {
                    return;
                }
                var found = false;
                $select.find('option').each(function() {
                    if ($(this).val() === value) {
                        found = true;
                        return false;
                    }
                });
                if (!found) {
                    $select.append($('<option></option>').attr('value', value).text(value));
                }
            }

            function syncVerifikasiNamaPetugasFromKoordinator() {
                var $koord = $('#disposisi_koordinator_kesmas');
                var $petugas = $('#verifikasi_nama_petugas');
                if (!$koord.length || !$petugas.length) {
                    return;
                }
                var v = $koord.val() || '';
                ensureOptionValue($petugas, v);
                $petugas.val(v);
            }

            $('#disposisi_koordinator_kesmas').on('change', syncVerifikasiNamaPetugasFromKoordinator);
            syncVerifikasiNamaPetugasFromKoordinator();

            // Initialize Flatpickr for verifikasi penerimaan start/stop (default per 10 menit)
            var verifikasiStartDefault = "{{ $verifikasi_start_default }}";
            var verifikasiStopDefault = "{{ $verifikasi_stop_default }}";
            var verifikasiStartDate = defaultDateTenMinutes(verifikasiStartDefault);
            var verifikasiStopDate = defaultDateTenMinutes(verifikasiStopDefault);
            // Jika mulai & selesai sama, selesai = mulai + 10 menit
            if (verifikasiStartDate.getTime() === verifikasiStopDate.getTime()) {
                verifikasiStopDate = new Date(verifikasiStartDate.getTime() + 10 * 60000);
            }

            if ($('#verifikasi_start_date').length) {
                $('#verifikasi_start_date').flatpickr(Object.assign({}, fpTimeOpts, {
                    allowInput: true,
                    defaultDate: verifikasiStartDate
                }));
            }

            if ($('#verifikasi_stop_date').length) {
                $('#verifikasi_stop_date').flatpickr(Object.assign({}, fpTimeOpts, {
                    allowInput: true,
                    defaultDate: verifikasiStopDate
                }));
            }

            // Function to convert date format for form submission (same as verification-2.blade.php)
            function convertDateTimeFormat(dateTimeValue) {
                if (!dateTimeValue) return '';
                
                // If already in d/m/Y H:i format, return as is
                if (dateTimeValue.match(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/)) {
                    return dateTimeValue;
                }
                
                // Convert from YYYY-MM-DDTHH:mm to d/m/Y H:i
                if (dateTimeValue.includes('T')) {
                    const [datePart, timePart] = dateTimeValue.split('T');
                    const [year, month, day] = datePart.split('-');
                    const [hours, minutes] = timePart.split(':');
                    return `${day}/${month}/${year} ${hours}:${minutes}`;
                }
                
                return dateTimeValue;
            }

            // Function to convert form dates before submission
            function convertFormDates(form) {
                if (!form) return;
                
                const dateInputs = form.querySelectorAll('input[name="start_date"], input[name="stop_date"]');
                dateInputs.forEach(input => {
                    if (input.value) {
                        let convertedValue = '';
                        
                        // Get the flatpickr instance if available
                        const flatpickrInstance = input._flatpickr;
                        if (flatpickrInstance && flatpickrInstance.selectedDates && flatpickrInstance.selectedDates.length > 0) {
                            try {
                                convertedValue = flatpickrInstance.formatDate(flatpickrInstance.selectedDates[0], 'd/m/Y H:i');
                            } catch (e) {
                                convertedValue = convertDateTimeFormat(input.value);
                            }
                        } else {
                            convertedValue = convertDateTimeFormat(input.value);
                        }
                        
                        // Create hidden input with converted format
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name;
                        hiddenInput.value = convertedValue;
                        form.appendChild(hiddenInput);
                        
                        // Disable the original input so it won't be submitted
                        input.disabled = true;
                    }
                });
            }

            // Function checkNikAndPassword (simplified version)
            let namaPetugasValue = null;
            let formClassNameValue = null;
            const BSRE_USE = {{ config('app.bsre_use', false) ? 'true' : 'false' }};

            function checkNikAndPassword(namaPetugas, className) {
                namaPetugasValue = namaPetugas;
                formClassNameValue = className;
                event.preventDefault();
                
                const form = document.querySelector(`.${className}`);
                if (!form) {
                    console.error('Form not found:', className);
                    return;
                }
                
                // Convert date formats before submission
                convertFormDates(form);
                
                if (BSRE_USE === true || BSRE_USE === 'true') {
                    // Check NIK and Password via AJAX
                    $.ajax({
                        url: "{{ url('elits-samples/check-petugas') }}/" + encodeURIComponent(namaPetugas),
                        type: "GET",
                        success: function(response) {
                            if (response === "true") {
                                form.submit();
                            } else {
                                alert('NIK dan Password petugas belum diisi. Silakan hubungi administrator.');
                            }
                        },
                        error: function() {
                            alert('Terjadi kesalahan. Silakan coba lagi.');
                        }
                    });
                } else {
                    // Tidak pakai BSRE, langsung submit
                    form.submit();
                }
            }

            // Validasi: Jika belum ada pengawetan, maka step 1 harus diisi dulu
            function checkPengawetan() {
                var hasPengawetan = false;

                // Cek semua sample untuk pengawetan
                @foreach ($samples as $sample)
                    @php
                        $existing = isset($existing_penerimaan[$sample->id_samples]) ? $existing_penerimaan[$sample->id_samples] : null;
                        $hasExistingPengawetan = $existing && (!empty($existing->pengawetan_oleh) || !empty($existing->pengawetan_dengan));
                        // Replace dash dengan underscore untuk nama variabel JavaScript yang valid
                        $varSuffix = str_replace('-', '_', $sample->id_samples);
                    @endphp

                    // Cek data yang sudah tersimpan di database
                    @if ($hasExistingPengawetan)
                        hasPengawetan = true;
                    @endif

                    // Cek data di form (jika belum ada di database)
                    @if (!$hasExistingPengawetan)
                        var pengawetanOleh_{{ $varSuffix }} = $(
                            'input[name="samples[{{ $sample->id_samples }}][pengawetan_oleh]"]:checked').val();
                        var pengawetanPendinginan_{{ $varSuffix }} = $(
                            'input[name="samples[{{ $sample->id_samples }}][pengawetan_pendinginan]"]').is(
                            ':checked');
                        var pengawetanHno3_{{ $varSuffix }} = $(
                            'input[name="samples[{{ $sample->id_samples }}][pengawetan_hno3]"]').is(
                            ':checked');
                        var pengawetanH2so4_{{ $varSuffix }} = $(
                            'input[name="samples[{{ $sample->id_samples }}][pengawetan_h2so4]"]').is(
                            ':checked');
                        var pengawetanNaoh_{{ $varSuffix }} = $(
                            'input[name="samples[{{ $sample->id_samples }}][pengawetan_naoh]"]').is(
                            ':checked');
                        var pengawetanLainnya_{{ $varSuffix }} = $(
                            'input[name="samples[{{ $sample->id_samples }}][pengawetan_lainnya]"]').is(
                            ':checked');
                        var pengawetanLainnyaText_{{ $varSuffix }} = $(
                            'input[name="samples[{{ $sample->id_samples }}][pengawetan_lainnya_text]"]').val();

                        if (pengawetanOleh_{{ $varSuffix }} ||
                            pengawetanPendinginan_{{ $varSuffix }} ||
                            pengawetanHno3_{{ $varSuffix }} ||
                            pengawetanH2so4_{{ $varSuffix }} ||
                            pengawetanNaoh_{{ $varSuffix }} ||
                            (pengawetanLainnya_{{ $varSuffix }} && pengawetanLainnyaText_{{ $varSuffix }})
                        ) {
                            hasPengawetan = true;
                        }
                    @endif
                @endforeach

                return hasPengawetan;
            }


            // Handle tombol Simpan Semua Step
            $('#btn-save-all').on('click', function(e) {
                e.preventDefault();
                
                // Aktifkan semua field step 1-3 dengan menghapus atribut disabled
                $('#penerima_sampel').prop('disabled', false);
                $('#penerima_tanggal').prop('disabled', false);
                $('#disposisi_koordinator_kesmas').prop('disabled', false);
                $('#disposisi_tanggal').prop('disabled', false);
                $('#disposisi_analis').prop('disabled', false);
                $('#disposisi_analis_tanggal').prop('disabled', false);
                
                // Set flag untuk mode simpan semua (akan menyimpan Step 1, 2, dan 3 secara berurutan)
                $('#save_all_steps').val('1');
                $('#current_step').val('all');
                
                // Cek apakah ada form verifikasi yang perlu diisi
                var verifikasiForm = $('.formVerifikasiPenerimaan');
                var hasVerifikasi = verifikasiForm.length > 0;
                
                if (hasVerifikasi) {
                    syncVerifikasiNamaPetugasFromKoordinator();
                    var startDate = $('#verifikasi_start_date').val();
                    var stopDate = $('#verifikasi_stop_date').val();
                    var namaPetugas = $('#verifikasi_nama_petugas').val();
                    
                    if (startDate && stopDate && namaPetugas) {
                        // Get flatpickr instances untuk format tanggal
                        var startDateFormatted = startDate;
                        var stopDateFormatted = stopDate;
                        
                        var startDateFP = $('#verifikasi_start_date')[0]._flatpickr;
                        var stopDateFP = $('#verifikasi_stop_date')[0]._flatpickr;
                        
                        if (startDateFP && startDateFP.selectedDates && startDateFP.selectedDates.length > 0) {
                            startDateFormatted = startDateFP.formatDate(startDateFP.selectedDates[0], 'd/m/Y H:i');
                        }
                        
                        if (stopDateFP && stopDateFP.selectedDates && stopDateFP.selectedDates.length > 0) {
                            stopDateFormatted = stopDateFP.formatDate(stopDateFP.selectedDates[0], 'd/m/Y H:i');
                        }
                        
                        // Set data verifikasi ke hidden input
                        $('#verifikasi_start_date_hidden').val(startDateFormatted);
                        $('#verifikasi_stop_date_hidden').val(stopDateFormatted);
                        $('#verifikasi_nama_petugas_hidden').val(namaPetugas);
                        $('#submit_verifikasi').val('1');
                    }
                }
                
                // Submit form utama (akan redirect dengan flash message)
                // Controller akan handle verifikasi jika submit_verifikasi = 1
                $('#form-penerimaan').submit();
            });

            // Validasi form submit - hanya untuk fallback jika submit langsung (bukan dari tombol)
            $('#form-penerimaan').on('submit', function(e) {
                // Jika submit langsung (bukan dari tombol Simpan Semua Step), set mode simpan semua
                if ($('#save_all_steps').val() !== '1') {
                    $('#save_all_steps').val('1');
                    $('#current_step').val('all');
                }
                
                // Backend akan menyimpan Step 1, kemudian Step 2, kemudian Step 3 secara berurutan
                return true; // Lanjutkan submit
            });
        });
    </script>

    @if (!$use_tte)
        <!-- Include Signature Pad library -->
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    @endif
@endsection
