@extends('masterweb::template.admin.layout')
@section('title')
    Baku Mutu Haji Lab.{{ $lab }} Management
@endsection

@section('content')
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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-baku-mutu-' . $lab_link) }}"> Baku Mutu
                                        Lab.{{ $lab }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Create Haji</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form enctype="multipart/form-data" class="forms-sample" id="form"
                action="{{ route('elits-baku-mutu-klinik.store') }}" method="POST" novalidate>

                @csrf

                <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
                <input type="hidden" class="form-control" name="lab_id" id="lab_id"
                    value="{{ $get_lab->id_laboratorium }}" readonly>
                <input type="hidden" name="is_haji" value="1">
                <input type="hidden" name="is_sub_parameter_satuan_baku_mutu" value="0">
                <input type="hidden" name="parameter_jenis_klinik_id" id="parameter_jenis_klinik_id" value="">

                <!-- Informasi Dasar Parameter -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fa fa-info-circle mr-2"></i>Informasi Dasar Parameter - Baku Mutu Haji</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="fa fa-info-circle mr-2"></i>
                            <strong>Baku Mutu Haji:</strong> Pilih parameter satuan klinik yang merupakan parameter haji. Baku mutu haji dapat menggunakan tipe General (tidak spesifik terhadap jenis kelamin) atau Specific (spesifik terhadap jenis kelamin, tidak spesifik terhadap usia).
                        </div>

                        <div class="form-group">
                            <label for="parameter_satuan_klinik_id">
                                <i class="fa fa-list mr-1"></i>Parameter Satuan Klinik
                                <span class="badge badge-danger ml-1">Wajib</span>
                            </label>
                            <select class="form-control" name="parameter_satuan_klinik_id" id="parameter_satuan_klinik_id">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="library_id">
                                <i class="fa fa-book mr-1"></i>Acuan Baku Mutu
                                <span class="badge badge-danger ml-1">Wajib</span>
                            </label>
                            <select class="form-control" name="library_id" id="library_id">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="unit_id">
                                <i class="fa fa-ruler mr-1"></i>Satuan
                            </label>
                            <select class="form-control unit_id" name="unit_id" id="unit_id">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Konfigurasi Baku Mutu -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fa fa-chart-line mr-2"></i>Nilai Baku Mutu</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="is_khusus_baku_mutu">
                                <i class="fa fa-filter mr-1"></i>Tipe Data
                            </label>
                            <select class="form-control" name="is_khusus_baku_mutu" id="is_khusus_baku_mutu">
                                <option value="0">General (Tidak spesifik terhadap jenis kelamin)</option>
                                <option value="1">Specific (Spesifik terhadap jenis kelamin)</option>
                            </select>
                            <small class="form-text text-muted">General: berlaku untuk semua jenis kelamin | Specific: dapat berbeda untuk laki-laki dan perempuan</small>
                        </div>

                        <!-- Form untuk Specific (Gender-based) -->
                        <div class="card border-info display-data-khusus mb-3" id="specific-form" style="display: none;">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fa fa-sliders mr-2"></i>Data Specific - Jenis Kelamin</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="gender_baku_mutu">
                                        <i class="fa fa-venus-mars mr-1"></i>Jenis Kelamin
                                        <span class="badge badge-danger ml-1">Wajib</span>
                                    </label>
                                    <select class="form-control" name="gender_baku_mutu" id="gender_baku_mutu">
                                        <option value="A">Semua gender sama (akan dibuat untuk Laki-laki dan Perempuan)</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Form untuk General -->
                        <div id="general-form">
                            <div class="form-group">
                                <label for="min">
                                    <i class="fa fa-arrow-down mr-1"></i>Min (Minimum)
                                    <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                        title="Gunakan titik (.) untuk desimal. Contoh: 4.0 atau kosongkan jika tidak ada"></i>
                                </label>
                                <input type="text" class="form-control" id="min" name="min"
                                    placeholder="Contoh: 4.0">
                            </div>

                            <div class="form-group">
                                <label for="max">
                                    <i class="fa fa-arrow-up mr-1"></i>Max (Maksimum)
                                    <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                        title="Gunakan titik (.) untuk desimal. Contoh: 6.5 atau kosongkan jika tidak ada"></i>
                                </label>
                                <input type="text" class="form-control" id="max" name="max"
                                    placeholder="Contoh: 6.5">
                            </div>

                            <div class="form-group">
                                <label for="equal">
                                    <i class="fa fa-equals mr-1"></i>Nilai Sama Dengan
                                    <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                        title="Untuk nilai non-range seperti Positif/Negatif. Kosongkan jika menggunakan range Min-Max"></i>
                                </label>
                                <input type="text" class="form-control" id="equal" name="equal"
                                    placeholder="Contoh: Positif">
                            </div>

                            <div class="form-group">
                                <label for="nilai_baku_mutu">
                                    <i class="fa fa-file-alt mr-1"></i>Nilai Baku Mutu di Laporan
                                </label>
                                <input type="text" class="form-control" id="nilai_baku_mutu"
                                    name="nilai_baku_mutu" placeholder="Contoh: 4.0 - 6.5 atau Negatif">
                                <small class="form-text text-muted">Teks yang akan muncul di laporan hasil</small>
                            </div>
                        </div>

                        <!-- Form untuk Specific - Laki-laki -->
                        <div id="specific-l-form" style="display: none;">
                            <h6 class="mb-3"><i class="fa fa-mars mr-2"></i>Baku Mutu untuk Laki-laki</h6>
                            <div class="form-group">
                                <label for="min_l">
                                    <i class="fa fa-arrow-down mr-1"></i>Min (Minimum)
                                </label>
                                <input type="text" class="form-control" id="min_l" name="min_l"
                                    placeholder="Contoh: 4.0">
                            </div>
                            <div class="form-group">
                                <label for="max_l">
                                    <i class="fa fa-arrow-up mr-1"></i>Max (Maksimum)
                                </label>
                                <input type="text" class="form-control" id="max_l" name="max_l"
                                    placeholder="Contoh: 6.5">
                            </div>
                            <div class="form-group">
                                <label for="equal_l">
                                    <i class="fa fa-equals mr-1"></i>Nilai Sama Dengan
                                </label>
                                <input type="text" class="form-control" id="equal_l" name="equal_l"
                                    placeholder="Contoh: Positif">
                            </div>
                            <div class="form-group">
                                <label for="nilai_baku_mutu_l">
                                    <i class="fa fa-file-alt mr-1"></i>Nilai Baku Mutu di Laporan
                                </label>
                                <input type="text" class="form-control" id="nilai_baku_mutu_l"
                                    name="nilai_baku_mutu_l" placeholder="Contoh: 4.0 - 6.5">
                            </div>
                        </div>

                        <!-- Form untuk Specific - Perempuan -->
                        <div id="specific-p-form" style="display: none;">
                            <h6 class="mb-3"><i class="fa fa-venus mr-2"></i>Baku Mutu untuk Perempuan</h6>
                            <div class="form-group">
                                <label for="min_p">
                                    <i class="fa fa-arrow-down mr-1"></i>Min (Minimum)
                                </label>
                                <input type="text" class="form-control" id="min_p" name="min_p"
                                    placeholder="Contoh: 4.0">
                            </div>
                            <div class="form-group">
                                <label for="max_p">
                                    <i class="fa fa-arrow-up mr-1"></i>Max (Maksimum)
                                </label>
                                <input type="text" class="form-control" id="max_p" name="max_p"
                                    placeholder="Contoh: 6.5">
                            </div>
                            <div class="form-group">
                                <label for="equal_p">
                                    <i class="fa fa-equals mr-1"></i>Nilai Sama Dengan
                                </label>
                                <input type="text" class="form-control" id="equal_p" name="equal_p"
                                    placeholder="Contoh: Positif">
                            </div>
                            <div class="form-group">
                                <label for="nilai_baku_mutu_p">
                                    <i class="fa fa-file-alt mr-1"></i>Nilai Baku Mutu di Laporan
                                </label>
                                <input type="text" class="form-control" id="nilai_baku_mutu_p"
                                    name="nilai_baku_mutu_p" placeholder="Contoh: 4.0 - 6.5">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                <button type="button" onclick="document.location='{{ url('/elits-baku-mutu-klinik') }}'"
                    class="btn btn-light">Kembali</button>
            </form>
        </div>
    </div>

    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"></script>

    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        $(document).ready(function() {
            var CSRF_TOKEN = $('#csrf-token').val();

            // Handler untuk toggle General/Specific
            $('#is_khusus_baku_mutu').on('change', function() {
                var isKhusus = $(this).val();
                if (isKhusus == '1') {
                    // Specific
                    $('#specific-form').show();
                    $('#general-form').hide();
                    updateSpecificForm();
                } else {
                    // General
                    $('#specific-form').hide();
                    $('#general-form').show();
                    $('#specific-l-form').hide();
                    $('#specific-p-form').hide();
                }
            });

            // Handler untuk gender change
            $('#gender_baku_mutu').on('change', function() {
                updateSpecificForm();
            });

            function updateSpecificForm() {
                var gender = $('#gender_baku_mutu').val();
                if (gender == 'A') {
                    // Tampilkan form untuk L dan P
                    $('#specific-l-form').show();
                    $('#specific-p-form').show();
                } else if (gender == 'L') {
                    // Tampilkan form untuk L saja
                    $('#specific-l-form').show();
                    $('#specific-p-form').hide();
                } else if (gender == 'P') {
                    // Tampilkan form untuk P saja
                    $('#specific-l-form').hide();
                    $('#specific-p-form').show();
                }
            }

            // Inisialisasi Select2 untuk Parameter Satuan Klinik (Haji)
            $("#parameter_satuan_klinik_id").select2({
                ajax: {
                    url: "{{ route('getParameterSatuanKlinik') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term || '',
                            is_haji: 1, // Filter hanya parameter haji
                            exclude_existing_haji: 1, // Exclude parameter yang sudah ada baku mutu haji
                            lab_id: $('#lab_id').val() // Filter berdasarkan lab_id
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: $.map(response, function(obj) {
                                return {
                                    id: obj.id,
                                    text: obj.text
                                };
                            })
                        };
                    },
                    cache: true
                },
                placeholder: 'Pilih parameter satuan haji',
                allowClear: true,
                minimumInputLength: 0
            });

            // Set parameter_jenis_klinik_id saat parameter satuan dipilih
            $('#parameter_satuan_klinik_id').on('select2:select', function(e) {
                var paramSatuanId = $(this).val();
                if (paramSatuanId) {
                    $.ajax({
                        url: "{{ route('getParameterSatuanKlinikDetail') }}",
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            id: paramSatuanId
                        },
                        success: function(response) {
                            if (response.status && response.data && response.data.parameter_jenis_klinik) {
                                $('#parameter_jenis_klinik_id').val(response.data.parameter_jenis_klinik);
                            }
                        }
                    });
                }
            });

            // Inisialisasi Select2 untuk Library
            $("#library_id").select2({
                ajax: {
                    url: "{{ route('getLibrary') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: $.map(response, function(obj) {
                                return {
                                    id: obj.id,
                                    text: obj.text
                                };
                            })
                        };
                    },
                    cache: true
                },
                placeholder: 'Pilih library',
                allowClear: true
            });

            // Inisialisasi Select2 untuk Unit
            $(".unit_id").select2({
                ajax: {
                    url: "{{ route('getDataUnitBySelect') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: $.map(response, function(obj) {
                                return {
                                    id: obj.id,
                                    text: obj.text
                                };
                            })
                        };
                    },
                    cache: true
                },
                placeholder: 'Pilih satuan',
                allowClear: true
            });

            // Submit form
            $('#form').on('submit', function(e) {
                e.preventDefault();
                
                // Validasi
                if (!$('#parameter_satuan_klinik_id').val()) {
                    swal("Error!", "Parameter Satuan Klinik harus dipilih!", "error");
                    return;
                }
                if (!$('#library_id').val()) {
                    swal("Error!", "Acuan Baku Mutu harus dipilih!", "error");
                    return;
                }
                
                // Pastikan parameter_jenis_klinik_id sudah di-set
                if (!$('#parameter_jenis_klinik_id').val() && $('#parameter_satuan_klinik_id').val()) {
                    // Jika belum di-set, ambil dari parameter satuan
                    var paramSatuanId = $('#parameter_satuan_klinik_id').val();
                    $.ajax({
                        url: "{{ route('getParameterSatuanKlinikDetail') }}",
                        type: 'POST',
                        async: false, // Synchronous untuk memastikan nilai ter-set sebelum submit
                        data: {
                            _token: CSRF_TOKEN,
                            id: paramSatuanId
                        },
                        success: function(response) {
                            if (response.status && response.data && response.data.parameter_jenis_klinik) {
                                $('#parameter_jenis_klinik_id').val(response.data.parameter_jenis_klinik);
                            }
                        }
                    });
                }

                // Handle data untuk Specific dengan gender
                var isKhusus = $('#is_khusus_baku_mutu').val();
                if (isKhusus == '1') {
                    var gender = $('#gender_baku_mutu').val();
                    
                    // Untuk haji specific, tidak perlu umur (set null)
                    // Hapus dulu jika sudah ada
                    $('#form input[name="minimal_umur_baku_mutu"]').remove();
                    $('#form input[name="maksimal_umur_baku_mutu"]').remove();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'minimal_umur_baku_mutu',
                        value: ''
                    }).appendTo('#form');
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'maksimal_umur_baku_mutu',
                        value: ''
                    }).appendTo('#form');
                    
                    if (gender == 'A') {
                        // Semua gender sama - kirim data L dan P secara terpisah menggunakan bulk_rows
                        // Hapus field min, max, equal, nilai_baku_mutu yang ada
                        $('#form input[name="min"]').remove();
                        $('#form input[name="max"]').remove();
                        $('#form input[name="equal"]').remove();
                        $('#form input[name="nilai_baku_mutu"]').remove();
                        
                        // Buat array untuk bulk_rows
                        var bulkRows = [
                            {
                                gender_baku_mutu: 'L',
                                min: $('#min_l').val(),
                                max: $('#max_l').val(),
                                equal: $('#equal_l').val(),
                                nilai_baku_mutu: $('#nilai_baku_mutu_l').val(),
                                is_khusus_baku_mutu: '1',
                                is_haji: '1'
                            },
                            {
                                gender_baku_mutu: 'P',
                                min: $('#min_p').val(),
                                max: $('#max_p').val(),
                                equal: $('#equal_p').val(),
                                nilai_baku_mutu: $('#nilai_baku_mutu_p').val(),
                                is_khusus_baku_mutu: '1',
                                is_haji: '1'
                            }
                        ];
                        
                        // Tambahkan hidden input untuk bulk_rows
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'bulk_rows',
                            value: JSON.stringify(bulkRows)
                        }).appendTo('#form');
                    } else if (gender == 'L') {
                        // Hanya Laki-laki
                        $('#min').val($('#min_l').val());
                        $('#max').val($('#max_l').val());
                        $('#equal').val($('#equal_l').val());
                        $('#nilai_baku_mutu').val($('#nilai_baku_mutu_l').val());
                    } else if (gender == 'P') {
                        // Hanya Perempuan
                        $('#min').val($('#min_p').val());
                        $('#max').val($('#max_p').val());
                        $('#equal').val($('#equal_p').val());
                        $('#nilai_baku_mutu').val($('#nilai_baku_mutu_p').val());
                    }
                } else {
                    // General - hapus hidden input umur jika ada
                    $('#form input[name="minimal_umur_baku_mutu"]').remove();
                    $('#form input[name="maksimal_umur_baku_mutu"]').remove();
                }

                $(this).ajaxSubmit({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                title: "Success!",
                                text: response.pesan,
                                icon: "success"
                            }).then(function() {
                                document.location = '/elits-baku-mutu-klinik';
                            });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    pesan += value + '. <br>';
                                    wrapper.innerHTML = pesan;
                                });

                                swal({
                                    title: "Error!",
                                    content: wrapper,
                                    icon: "warning"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: response.pesan,
                                    icon: "warning"
                                });
                            }
                        }
                    },
                    error: function() {
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });
            });
        });
    </script>
@endsection
