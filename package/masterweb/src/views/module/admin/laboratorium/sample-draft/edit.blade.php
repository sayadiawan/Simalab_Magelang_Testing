@extends('masterweb::template.admin.layout')
@section('title')
    Edit Draft Sample
@endsection

@section('content')
    <style>
        .form-section {
            background: #ffffff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }

        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            color: #4b6cb7;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .info-card {
            background: linear-gradient(135deg, #f9fafb 0%, #edf2f7 100%);
            border-left: 4px solid #4b6cb7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .parameter-badge {
            background: #8ea3e9;
            color: #1a202c;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            margin: 3px;
            display: inline-block;
        }

        .btn-save {
            background: linear-gradient(135deg, #8ea3e9 0%, #8b9bcf 100%);
            color: #1a202c;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(142, 163, 233, 0.45);
        }
    </style>

    <!-- Breadcrumb -->
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card" style="border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                <div class="card-body" style="padding: 15px 20px;">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/home') }}">
                                    <i class="fa fa-home menu-icon mr-1"></i> Beranda
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('elits-permohonan-uji.index') }}">Permohonan Uji</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('elits-sample-draft.index', $draft->permohonan_uji_id) }}">Draft
                                    Sample</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Draft</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Header -->
    <div class="info-card">
        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-2">
                    <i class="fa fa-edit text-primary"></i>
                    <strong>Edit Draft Sample</strong>
                </h4>
                <p class="mb-0">
                    <strong>Permohonan Uji</strong>
                    @if (optional($permohonan_uji->customer)->name_customer)
                        — {{ $permohonan_uji->customer->name_customer }}
                    @elseif (!empty($permohonan_uji->name_customer))
                        — {{ $permohonan_uji->name_customer }}
                    @endif
                </p>
            </div>
            <div class="col-md-6 text-right">
                <span class="badge badge-warning" style="font-size: 14px; padding: 8px 15px;">
                    <i class="fa fa-clock"></i> DRAFT
                </span>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form id="editDraftForm" method="POST" action="{{ route('elits-sample-draft.update', $draft->id_sample_draft) }}">
        @csrf
        @method('PUT')

        <!-- Informasi Sample (Read Only) -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="fa fa-info-circle"></i> Informasi Sample (Tidak dapat diubah)
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Jenis Sampel:</strong></label>
                        <input type="text" class="form-control" readonly
                            value="{{ $draft->sampletype ? $draft->sampletype->code_sample_type . ' - ' . $draft->sampletype->name_sample_type : '-' }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Paket:</strong></label>
                        <input type="text" class="form-control" readonly
                            value="{{ $draft->packet ? $draft->packet->name_packet : 'Tanpa Paket' }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><strong>Parameter Pengujian:</strong></label>
                <div>
                    @if ($draft->samplemethoddraft && $draft->samplemethoddraft->count() > 0)
                        @foreach ($draft->samplemethoddraft as $method_draft)
                            @if ($method_draft->method)
                                <span class="parameter-badge">
                                    <i class="fa fa-flask"></i> {{ $method_draft->method->params_method }}
                                    @if ($method_draft->laboratorium)
                                        ({{ $method_draft->laboratorium->nama_laboratorium }})
                                    @endif
                                </span>
                            @endif
                        @endforeach
                    @else
                        <span class="text-muted">Tidak ada parameter</span>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Biaya Pengujian:</strong></label>
                        <input type="text" class="form-control" readonly
                            value="Rp {{ number_format($draft->cost_samples, 0, ',', '.') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Dibuat:</strong></label>
                        <input type="text" class="form-control" readonly
                            value="{{ $draft->created_at->format('d/m/Y H:i') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Data yang Dapat Diubah -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="fa fa-edit"></i> Data yang Dapat Diubah
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="titik_pengambilan">
                            <i class="fa fa-map-marker-alt"></i> Titik Lokasi Pengambilan
                        </label>
                        <textarea class="form-control" name="titik_pengambilan" id="titik_pengambilan" rows="3"
                            placeholder="Masukkan titik lokasi pengambilan sampel">{{ old('titik_pengambilan', $draft->titik_pengambilan) }}</textarea>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Contoh: Jl. Sudirman No. 123, Kota Magelang
                        </small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cost_sampling_samples">
                            <i class="fa fa-money-bill-wave"></i> Biaya Sampling
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control" name="cost_sampling_samples"
                                id="cost_sampling_samples" min="0" step="1000"
                                value="{{ old('cost_sampling_samples', $draft->cost_sampling_samples) }}"
                                placeholder="Masukkan biaya sampling">
                        </div>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Biaya untuk pengambilan sampel
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="note_samples">
                    <i class="fa fa-sticky-note"></i> Catatan
                </label>
                <textarea class="form-control" name="note_samples" id="note_samples" rows="3"
                    placeholder="Tambahkan catatan jika diperlukan">{{ old('note_samples', $draft->note_samples) }}</textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-section">
            <div class="row">
                <div class="col-md-12 text-right">
                    <a href="{{ route('elits-sample-draft.index', $draft->permohonan_uji_id) }}"
                        class="btn btn-secondary">
                        <i class="fa fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#editDraftForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');
                var submitBtn = form.find('button[type="submit"]');

                // Disable submit button
                submitBtn.prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

                $.ajax({
                    url: url,
                    type: 'PUT',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                title: "Berhasil!",
                                text: response.pesan,
                                icon: "success",
                                buttons: false,
                                timer: 1500
                            }).then(function() {
                                window.location.href =
                                    "{{ route('elits-sample-draft.index', $draft->permohonan_uji_id) }}";
                            });
                        } else {
                            swal({
                                title: "Error!",
                                text: response.pesan,
                                icon: "error"
                            });
                            submitBtn.prop('disabled', false).html(
                                '<i class="fa fa-save"></i> Simpan Perubahan');
                        }
                    },
                    error: function(xhr) {
                        var message = "Gagal mengupdate draft sample!";
                        if (xhr.responseJSON && xhr.responseJSON.pesan) {
                            message = xhr.responseJSON.pesan;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            message = Object.values(errors).flat().join('\n');
                        }
                        swal({
                            title: "Error!",
                            text: message,
                            icon: "error"
                        });
                        submitBtn.prop('disabled', false).html(
                            '<i class="fa fa-save"></i> Simpan Perubahan');
                    }
                });
            });
        });
    </script>
@endsection

