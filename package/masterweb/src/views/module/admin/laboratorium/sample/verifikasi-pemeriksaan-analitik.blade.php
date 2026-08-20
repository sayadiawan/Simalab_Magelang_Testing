@extends('masterweb::template.admin.layout')

@section('title')
    Verifikasi Pemeriksaan / Analitik - {{ $laboratorium->nama_laboratorium }}
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
                            <h3 class="card-title">Verifikasi Pemeriksaan / Analitik - {{ $laboratorium->nama_laboratorium }}</h3>
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

                            <div class="mb-3">
                                <h5>Informasi Permohonan Uji</h5>
                                @php
                                    $currentSampleId = Request::segment(3);
                                    $currentSample = collect($samples)->firstWhere('id_samples', $currentSampleId);
                                    $sampleIndex = collect($samples)->search(function ($s) use ($currentSampleId) {
                                        return $s->id_samples === $currentSampleId;
                                    });
                                    $sampleUrutan = $sampleIndex !== false ? ($sampleIndex + 1) : null;
                                    $sampleDisplay = $currentSample->codesample_samples ?? ($currentSample->code_sample_customer ?? null);

                                    $parameterDiuji = \Smt\Masterweb\Models\SampleMethod::with('method')
                                        ->where('sample_id', $currentSampleId)
                                        ->where('laboratorium_id', $idlabs)
                                        ->whereNull('deleted_at')
                                        ->get()
                                        ->pluck('method.name_method')
                                        ->filter()
                                        ->unique()
                                        ->values();

                                    $yangDiujikan = [];
                                    if (!empty($currentSample->name_sample_type)) {
                                        $yangDiujikan[] = $currentSample->name_sample_type;
                                    }
                                    if (!empty($currentSample->titik_pengambilan)) {
                                        $yangDiujikan[] = 'Titik: ' . $currentSample->titik_pengambilan;
                                    }
                                @endphp
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
                                    <tr>
                                        <td><strong>Nomor Sampel Aktif</strong></td>
                                        <td>: {{ $sampleDisplay ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sampel yang Diganti</strong></td>
                                        <td>:
                                            @if ($sampleUrutan)
                                                Sampel ke-{{ $sampleUrutan }} dari {{ $samples->count() }}
                                            @else
                                                Tidak terdeteksi
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Parameter Diuji</strong></td>
                                        <td>:
                                            @if ($parameterDiuji->count() > 0)
                                                {{ $parameterDiuji->implode(', ') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Apa Saja yang Diujikan</strong></td>
                                        <td>:
                                            @if (count($yangDiujikan) > 0)
                                                {{ implode(' | ', $yangDiujikan) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <hr>

                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h4 class="card-title mb-0 text-white">Form Verifikasi Pemeriksaan / Analitik</h4>
                                </div>
                                <div class="card-body">
                                    @php
                                        $first_sample = $samples->first();
                                        
                                        // Format default dates untuk Flatpickr (d/m/Y)
                                        $default_start_date_str = '';
                                        $default_stop_date_str = '';
                                        
                                        if ($default_start_date) {
                                            $default_start_date_str = $default_start_date->format('d/m/Y');
                                        }
                                        
                                        if ($default_stop_date) {
                                            $default_stop_date_str = $default_stop_date->format('d/m/Y');
                                        }
                                    @endphp

                                    <form action="{{ route('elits-samples.verification-analytic-2', [Request::segment(3)]) }}" 
                                          method="post" 
                                          id="formVerifikasiPemeriksaan" 
                                          class="formVerifikasiPemeriksaan">
                                        @csrf
                                        <input type="hidden" name="verification_step" value="2">
                                        <input type="hidden" name="id_laboratorium" value="{{ $idlabs }}">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="start_date_pemeriksaan">
                                                        <strong>Start Date <span class="text-danger">*</span></strong>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="start_date" 
                                                           id="start_date_pemeriksaan" 
                                                           placeholder="dd/mm/yyyy" 
                                                           required>
                                                    <small class="form-text text-muted">
                                                        Format: dd/mm/yyyy (contoh: 08/01/2026)
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="stop_date_pemeriksaan">
                                                        <strong>Stop Date <span class="text-danger">*</span></strong>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="stop_date" 
                                                           id="stop_date_pemeriksaan" 
                                                           placeholder="dd/mm/yyyy" 
                                                           required>
                                                    <small class="form-text text-muted">
                                                        Format: dd/mm/yyyy (contoh: 10/01/2026)
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="nama_petugas_pemeriksaan">
                                                        <strong>Nama Petugas <span class="text-danger">*</span></strong>
                                                    </label>
                                                    <select name="nama_petugas" 
                                                            id="nama_petugas_pemeriksaan" 
                                                            class="form-control" 
                                                            required>
                                                        <option value="">-- Pilih Nama Petugas --</option>
                                                        @foreach ($analis_list as $analis)
                                                            <option value="{{ $analis }}" 
                                                                    {{ $default_analis == $analis ? 'selected' : '' }}>
                                                                {{ $analis }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <button type="submit" 
                                                        class="btn btn-primary btn-lg" 
                                                        id="btn-simpan-verifikasi">
                                                    <i class="fas fa-save"></i> Simpan Verifikasi
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-secondary btn-lg" 
                                                        onclick="window.history.back()">
                                                    <i class="fas fa-times"></i> Batal
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Input NIK dan Password -->
    <div class="modal fade" id="inputNikAndPasword" tabindex="-1" aria-labelledby="inputNikAndPassword"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inputNikAndPassword">Input NIK dan Password BSRE</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nikPetugas">NIK</label>
                            <input type="text" class="form-control" name="nik" id="nikPetugas"
                                placeholder="Nomor Induk Kependudukan" required>
                        </div>
                        <div class="form-group mt-2">
                            <label for="passwordPetugas">Password</label>
                            <input type="text" class="form-control" name="password" id="passwordPetugas"
                                placeholder="Password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="submitNikAndPassword()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Flatpickr JS -->
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    
    <script>
        // BSRE Configuration
        const BSRE_USE = {{ config('app.bsre_use', false) ? 'true' : 'false' }};
        
        // Global variables untuk menyimpan nama petugas dan form class name
        var namaPetugasValue = null;
        var formClassNameValue = null;

        // Function to convert datetime-local format (YYYY-MM-DDTHH:mm) to d/m/Y
        function convertDateTimeFormat(dateTimeValue) {
            if (!dateTimeValue) return '';
            
            // If already in d/m/Y format, return as is
            if (dateTimeValue.match(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/)) {
                return dateTimeValue;
            }
            
            // Convert from YYYY-MM-DDTHH:mm to d/m/Y
            if (dateTimeValue.includes('T')) {
                const [datePart, timePart] = dateTimeValue.split('T');
                const [year, month, day] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }
            
            // If format is YYYY-MM-DD HH:mm:ss, convert it
            if (dateTimeValue.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/)) {
                const [datePart, timePart] = dateTimeValue.split(' ');
                const [year, month, day] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }
            
            return dateTimeValue;
        }

        // Function to convert date inputs in form before submission
        function convertFormDates(form) {
            if (!form) return;
            
            // For text inputs with flatpickr, get the formatted value directly
            const startDateInput = form.querySelector('#start_date_pemeriksaan');
            const stopDateInput = form.querySelector('#stop_date_pemeriksaan');
            
            if (startDateInput) {
                const flatpickrInstance = startDateInput._flatpickr;
                if (flatpickrInstance && flatpickrInstance.selectedDates && flatpickrInstance.selectedDates.length > 0) {
                    try {
                        const formattedValue = flatpickrInstance.formatDate(flatpickrInstance.selectedDates[0], 'd/m/Y');
                        // Update the input value directly instead of creating hidden input
                        startDateInput.value = formattedValue;
                    } catch (e) {
                        console.error('Error formatting start date:', e);
                    }
                }
            }
            
            if (stopDateInput) {
                const flatpickrInstance = stopDateInput._flatpickr;
                if (flatpickrInstance && flatpickrInstance.selectedDates && flatpickrInstance.selectedDates.length > 0) {
                    try {
                        const formattedValue = flatpickrInstance.formatDate(flatpickrInstance.selectedDates[0], 'd/m/Y');
                        // Update the input value directly instead of creating hidden input
                        stopDateInput.value = formattedValue;
                    } catch (e) {
                        console.error('Error formatting stop date:', e);
                    }
                }
            }
        }

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
                // Wajib input popup
                $('#inputNikAndPasword').modal('show');
            } else {
                // Tidak pakai BSRE, langsung submit
                form.submit();
            }
        }

        function submitNikAndPassword() {
            event.preventDefault();

            if (namaPetugasValue != null) {
                // Jangan simpan DB, kirim ke server via endpoint session sekali-pakai
                const formData = {
                    nik: document.getElementById("nikPetugas").value,
                    password: document.getElementById("passwordPetugas").value,
                    _token: '{{ csrf_token() }}'
                };
                $.ajax({
                    url: "{{ url('elits-samples/update-petugas') }}/" + encodeURIComponent(namaPetugasValue),
                    type: "PUT",
                    data: formData,
                    success: function(response) {
                        if (response === "true") {
                            $('#inputNikAndPasword').modal('hide');
                            // submit form yang diminta sebelumnya
                            if (formClassNameValue) {
                                const form = document.querySelector(`.${formClassNameValue}`);
                                if (form) {
                                    // Convert dates again before submitting
                                    convertFormDates(form);
                                    form.submit();
                                }
                            } else {
                                // Fallback: submit form aktif terakhir di halaman
                                const forms = document.querySelectorAll('form');
                                if (forms && forms.length) {
                                    const lastForm = forms[forms.length - 1];
                                    convertFormDates(lastForm);
                                    lastForm.submit();
                                }
                            }
                        } else {
                            alert('Gagal mengirim kredensial BSRE.');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan jaringan.');
                    }
                })
            }
        }

        $(document).ready(function() {
            @php
                // Convert default dates to JavaScript Date objects for flatpickr
                $js_start_date = $default_start_date ? $default_start_date->format('Y-m-d') : '';
                $js_stop_date = $default_stop_date ? $default_stop_date->format('Y-m-d') : '';
            @endphp

            // Initialize Flatpickr for start date
            var startDatePicker = flatpickr("#start_date_pemeriksaan", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                },
                @if ($default_start_date)
                defaultDate: new Date("{{ $js_start_date }}"),
                @endif
                onChange: function(selectedDates, dateStr, instance) {
                    // Auto-adjust to work hours if needed
                    if (selectedDates.length > 0) {
                        var date = selectedDates[0];
                        var hour = date.getHours();
                        if (hour < 8) {
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        } else if (hour >= 15) {
                            date.setDate(date.getDate() + 1);
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        }
                    }
                }
            });

            // Initialize Flatpickr for stop date
            var stopDatePicker = flatpickr("#stop_date_pemeriksaan", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                },
                @if ($default_stop_date)
                defaultDate: new Date("{{ $js_stop_date }}"),
                @endif
                onChange: function(selectedDates, dateStr, instance) {
                    // Auto-adjust to work hours if needed
                    if (selectedDates.length > 0) {
                        var date = selectedDates[0];
                        var hour = date.getHours();
                        if (hour < 8) {
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        } else if (hour >= 15) {
                            date.setDate(date.getDate() + 1);
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        }
                    }
                }
            });

            // Handle form submission (tanpa BSRE popup khusus untuk halaman ini)
            $('#formVerifikasiPemeriksaan').on('submit', function(e) {
                var namaPetugas = $('#nama_petugas_pemeriksaan').val();
                
                if (!namaPetugas) {
                    e.preventDefault();
                    alert('Nama Petugas harus diisi!');
                    return false;
                }

                // Konversi tanggal sebelum submit
                convertFormDates(this);
                
                // Biarkan form submit secara normal (tidak preventDefault)
                return true;
            });
        });
    </script>
@endsection

