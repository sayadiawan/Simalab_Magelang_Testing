@extends('masterweb::template.admin.layout')
@section('title')
    Input Diagnosis - Permohonan Uji Klinik
@endsection


@section('content')
    <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>
    
    <style>
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0d6efd;
        }

        .form-section-title i {
            margin-right: 10px;
            color: #0d6efd;
        }

        .patient-info-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .patient-info-card table {
            width: 100%;
        }

        .patient-info-card th {
            font-weight: 600;
            color: #495057;
            padding: 10px 0;
        }

        .patient-info-card td {
            color: #212529;
            padding: 10px 0;
        }
    </style>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik/registrasi') }}">Registrasi
                                        Permohonan Uji Klinik</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Input Diagnosis</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fa fa-stethoscope"></i> Input Diagnosis Dokter</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('elits-permohonan-uji-klinik-2.store-diagnosis', $id) }}" method="POST"
                enctype="multipart/form-data" id="form">

                @csrf

                <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

                <!-- Alert Info -->
                <div class="alert alert-info" role="alert">
                    <i class="fa fa-info-circle"></i> Silakan lengkapi informasi diagnosis untuk pasien ini sebelum melanjutkan ke pemilihan parameter pemeriksaan.
                </div>

                <!-- Patient Info Card -->
                <div class="patient-info-card">
                    <h5 class="mb-3"><i class="fa fa-user"></i> Informasi Pasien</h5>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="250px">No. Registrasi</th>
                            <td><strong class="text-primary">{{ $code }}</strong></td>
                            <input type="hidden" name="permohonan_uji_klinik" id="permohonan_uji_klinik"
                                value="{{ $id }}" readonly>
                        </tr>

                        @if($pasien)
                        <tr>
                            <th width="250px">No. Rekam Medis</th>
                            <td>
                                {{ Carbon\Carbon::createFromFormat('Y-m-d', $pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
                            </td>
                        </tr>

                        <tr>
                            <th width="250px">Nama Pasien</th>
                            <td><strong>{{ $pasien->nama_pasien }}</strong></td>
                        </tr>

                        <tr>
                            <th width="250px">Umur/Jenis Kelamin</th>
                            <td>
                                {{ $umur_string }}
                                /
                                {{ $pasien->gender_pasien == 'L' || $pasien->gender_pasien == 'male' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                        </tr>

                        <tr>
                            <th width="250px">Alamat</th>
                            <td>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($pasien) }}</td>
                        </tr>

                        <tr>
                            <th width="250px">No. Telepon</th>
                            <td>{{ $pasien->phone_pasien ?? '-' }}</td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="2">
                                <div class="alert alert-warning mb-0" role="alert">
                                    <i class="fa fa-exclamation-triangle"></i> Data pasien tidak ditemukan
                                </div>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>

                <!-- Request/Keluhan Pasien Section -->
                @if($item->request_pasien_permohonan_uji_klinik)
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-comment-alt"></i>
                        Request / Keluhan Pasien
                    </div>

                    <div class="form-group">
                        <label>REQUEST PASIEN / KELUHAN</label>
                        <div class="patient-info-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                            {!! nl2br(e($item->request_pasien_permohonan_uji_klinik)) !!}
                        </div>
                        <small class="form-text text-muted">Request atau keluhan pasien yang telah diinput sebelumnya</small>
                    </div>
                </div>
                @endif

                <!-- Diagnosis Form Section (Field yang disembunyikan untuk doctor_type = 'lab') -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-hospital"></i>
                        Informasi Diagnosis Dokter
                    </div>

                    <div class="form-group">
                        <label for="diagnosa_permohonan_uji_klinik">
                            DIAGNOSA <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="diagnosa_permohonan_uji_klinik"
                            id="diagnosa_permohonan_uji_klinik" placeholder="Masukkan diagnosa pasien" 
                            rows="5" required>{{ $item->diagnosa_permohonan_uji_klinik ?? '' }}</textarea>
                        <small class="form-text text-muted">Jelaskan diagnosis pasien dengan lengkap untuk menentukan pemeriksaan laboratorium yang sesuai</small>
                    </div>
                </div>

                <hr>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnSave">
                        <i class="fa fa-save"></i> Simpan & Lanjut ke Parameter
                    </button>
                    <a href="{{ route('elits-permohonan-uji-klinik.registrasi') }}" class="btn btn-secondary btn-lg">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script src="{{asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js')}}"></script>

    <script>
        $(document).ready(function() {
            // Form submit handler
            $('#form').on('submit', function(e) {
                e.preventDefault();

                // Validasi form
                var diagnosa = $('#diagnosa_permohonan_uji_klinik').val().trim();

                if (!diagnosa) {
                    swal({
                        icon: "warning",
                        title: "Perhatian!",
                        text: "Mohon isi field diagnosa!"
                    });
                    return false;
                }

                var formData = new FormData(this);

                $('#btnSave').text('Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        $('#btnSave').text('Simpan & Lanjut ke Parameter').prop('disabled', false);
                        
                        if (response.status == true) {
                            swal({
                                icon: "success",
                                title: "Berhasil!",
                                text: response.pesan,
                                buttons: false,
                                timer: 1500
                            }).then(() => {
                                // Redirect to parameter page
                                window.location.href = response.urlNextStep;
                            });
                        } else {
                            swal({
                                icon: "error",
                                title: "Gagal!",
                                text: response.pesan
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#btnSave').text('Simpan & Lanjut ke Parameter').prop('disabled', false);
                        
                        var errorMsg = 'Terjadi kesalahan saat menyimpan data';
                        
                        if (xhr.responseJSON && xhr.responseJSON.pesan) {
                            errorMsg = xhr.responseJSON.pesan;
                        } else if (xhr.responseText) {
                            try {
                                var errorResponse = JSON.parse(xhr.responseText);
                                errorMsg = errorResponse.message || errorMsg;
                            } catch(e) {
                                console.error('Error parsing response:', e);
                            }
                        }
                        
                        swal({
                            icon: "error",
                            title: "Error!",
                            text: errorMsg
                        });
                    }
                });
            });
        });
    </script>
@endsection
