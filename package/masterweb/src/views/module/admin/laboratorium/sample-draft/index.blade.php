@extends('masterweb::template.admin.layout')
@section('title')
    Draft Sample (Sementara)
@endsection

@section('content')
    <style>
        .table-draft {
            font-size: 13px;
        }

        .table-draft th {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            font-weight: 600;
            border: none;
        }

        .badge-draft {
            background: #ffc107;
            color: #000;
            font-weight: 600;
        }

        .badge-group {
            background: #17a2b8;
            color: white;
            font-weight: 600;
            padding: 5px 10px;
        }

        .group-badge-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .duplicate-group-btn {
            padding: 2px 6px;
            font-size: 11px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .duplicate-group-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        .btn-action {
            padding: 4px 8px;
            font-size: 12px;
            margin: 2px;
        }

        .draft-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .parameter-list {
            max-height: 100px;
            overflow-y: auto;
            font-size: 11px;
        }

        .parameter-list::-webkit-scrollbar {
            width: 4px;
        }

        .parameter-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        #confirmDraftModal .flatpickr-calendar {
            z-index: 1065;
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
                                <a href="{{ route('elits-samples.index', $permohonan_uji->id_permohonan_uji) }}">Sample</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Draft Sample</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Header -->
    <div class="draft-info">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2">
                    <i class="fa fa-file-text text-warning"></i>
                    <strong>Draft Sample (Sementara)</strong>
                </h4>
                <p class="mb-1">
                    <strong>Permohonan Uji</strong>
                    @if (optional($permohonan_uji->customer)->name_customer)
                        — {{ $permohonan_uji->customer->name_customer }}
                    @elseif (!empty($permohonan_uji->name_customer))
                        — {{ $permohonan_uji->name_customer }}
                    @endif
                </p>
                <p class="mb-0">
                    <strong>Customer:</strong> {{ $permohonan_uji->name_customer ?? '-' }}
                </p>
            </div>
            <div class="col-md-4 text-right">
                <h3 class="mb-0 text-warning">
                    <i class="fa fa-layer-group"></i> {{ $drafts->count() }} Draft
                </h3>
                @if ($drafts->count() > 0)
                    @php
                        $groups = $drafts->groupBy('draft_group_id')->count();
                    @endphp
                    <small class="text-muted">dalam {{ $groups }} grup inputan</small>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('elits-sample-draft.create', $permohonan_uji->id_permohonan_uji) }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Tambah Draft Sample
            </a>
            @if ($drafts->count() > 0)
            <a href="{{ route('elits-sample-draft.print-nota', $permohonan_uji->id_permohonan_uji) }}" class="btn btn-success" target="_blank">
                <i class="fa fa-print"></i> Cetak Nota Draft
            </a>
            <button type="button" class="btn btn-warning confirm-all-draft" data-id="{{ $permohonan_uji->id_permohonan_uji }}" data-count="{{ $drafts->count() }}">
                <i class="fa fa-check-double"></i> Konfirmasi All ({{ $drafts->count() }})
            </button>
            @endif
            <a href="{{ route('elits-samples.index', $permohonan_uji->id_permohonan_uji) }}" class="btn btn-info">
                <i class="fa fa-list"></i> Lihat Sample Final
            </a>
            <a href="{{ route('elits-permohonan-uji.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Draft Table -->
    <div class="card">
        <div class="card-body">
            @if ($drafts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-draft" id="draftTable">
                        <thead>
                            <tr>
                                <th width="3%">#</th>
                                <th width="5%">Grup</th>
                                <th width="15%">Jenis Sampel</th>
                                <th width="10%">Paket</th>
                                <th width="15%">Parameter</th>
                                <th width="10%">Titik Lokasi</th>
                                <th width="8%">Biaya</th>
                                <th width="8%">Status</th>
                                <th width="12%">Dibuat</th>
                                <th width="14%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($drafts as $draft)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($draft->draft_group_id)
                                            @php
                                                $groupNumber =
                                                    $drafts
                                                        ->groupBy('draft_group_id')
                                                        ->keys()
                                                        ->search($draft->draft_group_id) + 1;
                                                $groupCount = $drafts
                                                    ->where('draft_group_id', $draft->draft_group_id)
                                                    ->count();
                                            @endphp
                                            <div class="group-badge-container">
                                                <span class="badge badge-group"
                                                    title="Grup Inputan #{{ $groupNumber }} ({{ $groupCount }} draft)">
                                                    G{{ $groupNumber }}
                                                </span>
                                                @if ($loop->first || $drafts[$loop->index - 1]->draft_group_id != $draft->draft_group_id)
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary duplicate-group-btn"
                                                        data-group-id="{{ $draft->draft_group_id }}"
                                                        data-group-number="{{ $groupNumber }}"
                                                        data-group-count="{{ $groupCount }}"
                                                        title="Duplicate seluruh grup ({{ $groupCount }} draft)">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $draft->sampletype ? $draft->sampletype->code_sample_type : '-' }}</strong>
                                        <br>
                                        <small
                                            class="text-muted">{{ $draft->sampletype ? $draft->sampletype->name_sample_type : '-' }}</small>
                                    </td>
                                    <td>
                                        @if ($draft->packet)
                                            <span class="badge badge-success">
                                                <i class="fa fa-box"></i> {{ $draft->packet->name_packet }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($draft->samplemethoddraft && $draft->samplemethoddraft->count() > 0)
                                            <div class="parameter-list">
                                                @foreach ($draft->samplemethoddraft as $method_draft)
                                                    @if ($method_draft->method)
                                                        <span class="badge badge-secondary mb-1">
                                                            {{ $method_draft->method->params_method }}
                                                        </span>
                                                        <br>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <small class="text-info">
                                                <i class="fa fa-flask"></i> {{ $draft->samplemethoddraft->count() }}
                                                parameter
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($draft->titik_pengambilan && trim($draft->titik_pengambilan) != '')
                                            <small title="{{ $draft->titik_pengambilan }}" style="cursor: help;">
                                                <i class="fa fa-map-marker-alt text-success"></i>
                                                {{ Str::limit($draft->titik_pengambilan, 30) }}
                                            </small>
                                        @else
                                            <span class="text-muted" title="Titik lokasi belum diisi">
                                                <i class="fa fa-map-marker-alt"></i> Belum diisi
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>Rp {{ number_format($draft->cost_samples, 0, ',', '.') }}</strong>
                                        @if ($draft->cost_sampling_samples > 0)
                                            <br>
                                            <small class="text-success">
                                                +Rp {{ number_format($draft->cost_sampling_samples, 0, ',', '.') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-draft">
                                            <i class="fa fa-clock"></i> DRAFT
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $draft->created_at->format('d/m/Y') }}<br>
                                            {{ $draft->created_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-action confirm-draft"
                                            data-id="{{ $draft->id_sample_draft }}" title="Konfirmasi">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-action edit-draft"
                                            data-id="{{ $draft->id_sample_draft }}" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-info btn-action duplicate-draft"
                                            data-id="{{ $draft->id_sample_draft }}" title="Duplicate">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-action delete-draft"
                                            data-id="{{ $draft->id_sample_draft }}" title="Hapus">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fa fa-info-circle fa-3x mb-3"></i>
                    <h5>Belum ada draft sample</h5>
                    <p>Klik tombol di bawah untuk membuat draft sample baru.</p>
                    <a href="{{ route('elits-sample-draft.create', $permohonan_uji->id_permohonan_uji) }}"
                        class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Draft Sample
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal konfirmasi: jam & petugas pengambil sampel (sumber petugas sama halaman verifikasi) --}}
    <div class="modal fade" id="confirmDraftModal" tabindex="-1" role="dialog" aria-labelledby="confirmDraftModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="confirmDraftModalLabel">
                        <i class="fa fa-check-circle"></i> Konfirmasi Draft Sample
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" id="confirmDraftModalDesc">
                        Isi jam dan petugas pengambilan sampel sebelum dikonfirmasi menjadi sample final.
                    </p>
                    <div class="form-group">
                        <label for="confirm_jam_pengambilan">
                            Jam Pengambilan Sampel <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="confirm_jam_pengambilan"
                            placeholder="dd/mm/yyyy hh:mm" autocomplete="off" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="confirm_pengambil_sampel">
                            Petugas Pengambil Sampel <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="confirm_pengambil_sampel" required>
                            <option value="">-- Pilih Petugas --</option>
                            @foreach ($list_name_petugas as $nama_petugas)
                                <option value="{{ trim($nama_petugas) }}"
                                    {{ trim((string) ($user->name ?? '')) === trim((string) $nama_petugas) ? 'selected' : '' }}>
                                    {{ trim($nama_petugas) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Daftar petugas sama dengan halaman verifikasi sampel.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="confirmDraftModalSubmit">
                        <i class="fa fa-check"></i> Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var confirmDraftState = {
                mode: null,
                url: null,
                button: null,
                count: 0
            };
            var confirmJamFlatpickr = null;

            function waitForFlatpickr(callback, maxAttempts) {
                maxAttempts = maxAttempts || 50;
                if (typeof flatpickr !== 'undefined') {
                    callback();
                    return;
                }
                if (maxAttempts <= 0) {
                    return;
                }
                setTimeout(function() {
                    waitForFlatpickr(callback, maxAttempts - 1);
                }, 100);
            }

            function initConfirmJamFlatpickr() {
                waitForFlatpickr(function() {
                    if (confirmJamFlatpickr || !document.getElementById('confirm_jam_pengambilan')) {
                        return;
                    }

                    confirmJamFlatpickr = flatpickr('#confirm_jam_pengambilan', {
                        enableTime: true,
                        allowInput: true,
                        locale: 'id',
                        dateFormat: 'd/m/Y H:i',
                        time_24hr: true,
                        defaultDate: new Date(),
                        appendTo: document.body
                    });
                });
            }

            function setDefaultJamPengambilan() {
                if (confirmJamFlatpickr) {
                    confirmJamFlatpickr.setDate(new Date(), true);
                    return;
                }

                var now = new Date();
                var pad = function(n) {
                    return String(n).padStart(2, '0');
                };
                $('#confirm_jam_pengambilan').val(
                    pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear() +
                    ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes())
                );
            }

            function getJamPengambilanForSubmit() {
                var el = document.getElementById('confirm_jam_pengambilan');
                if (el && el._flatpickr && el._flatpickr.selectedDates.length) {
                    return el._flatpickr.formatDate(el._flatpickr.selectedDates[0], 'Y-m-d H:i:s');
                }

                return $('#confirm_jam_pengambilan').val();
            }

            function openConfirmDraftModal(mode, url, button, count) {
                confirmDraftState.mode = mode;
                confirmDraftState.url = url;
                confirmDraftState.button = button;
                confirmDraftState.count = count || 0;

                setDefaultJamPengambilan();

                if (mode === 'all') {
                    $('#confirmDraftModalDesc').text(
                        'Konfirmasi ' + count + ' draft sample. Isi jam dan petugas pengambilan sampel yang berlaku untuk semua draft.'
                    );
                } else {
                    $('#confirmDraftModalDesc').text(
                        'Draft sample akan menjadi sample final. Isi jam dan petugas pengambilan sampel terlebih dahulu.'
                    );
                }

                $('#confirmDraftModal').modal('show');
            }

            function resetConfirmButton() {
                var button = confirmDraftState.button;
                if (!button || !button.length) {
                    return;
                }

                button.prop('disabled', false);
                if (confirmDraftState.mode === 'all') {
                    button.html('<i class="fa fa-check-double"></i> Konfirmasi All (' + confirmDraftState.count + ')');
                } else {
                    button.html('<i class="fa fa-check"></i>');
                }
            }

            function submitDraftConfirmation() {
                var jam = getJamPengambilanForSubmit();
                var petugas = $('#confirm_pengambil_sampel').val();

                if (!jam) {
                    swal({ title: 'Perhatian', text: 'Jam pengambilan sampel wajib diisi.', icon: 'warning' });
                    return;
                }
                if (!petugas) {
                    swal({ title: 'Perhatian', text: 'Petugas pengambil sampel wajib dipilih.', icon: 'warning' });
                    return;
                }

                var button = confirmDraftState.button;
                button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                $('#confirmDraftModalSubmit').prop('disabled', true);

                $.ajax({
                    url: confirmDraftState.url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        jam_pengambilan: jam,
                        pengambil_sampel: petugas
                    },
                    success: function(response) {
                        $('#confirmDraftModal').modal('hide');
                        if (response.status == true) {
                            var message = response.pesan;
                            if (response.success_count && response.error_count) {
                                message += "\n\nBerhasil: " + response.success_count + " draft\nGagal: " + response.error_count + " draft";
                            }
                            swal({
                                title: "Berhasil!",
                                text: message,
                                icon: "success",
                                buttons: false,
                                timer: confirmDraftState.mode === 'all' ? 2000 : 1500
                            }).then(function() {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            swal({ title: "Error!", text: response.pesan, icon: "error" });
                            resetConfirmButton();
                        }
                    },
                    error: function(xhr) {
                        $('#confirmDraftModal').modal('hide');
                        var message = "Gagal mengonfirmasi draft sample!";
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.pesan) {
                                message = xhr.responseJSON.pesan;
                            } else if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                var errors = xhr.responseJSON.errors;
                                message = Object.keys(errors).map(function(key) {
                                    return errors[key][0];
                                }).join('\n');
                            }
                        }
                        swal({ title: "Error!", text: message, icon: "error" });
                        resetConfirmButton();
                    },
                    complete: function() {
                        $('#confirmDraftModalSubmit').prop('disabled', false);
                    }
                });
            }

            $('#confirmDraftModal').on('hidden.bs.modal', function() {
                if (confirmDraftState.button && confirmDraftState.button.prop('disabled')) {
                    // tetap disabled jika sedang proses ajax
                } else {
                    resetConfirmButton();
                }
            });

            $('#confirmDraftModalSubmit').on('click', function() {
                submitDraftConfirmation();
            });

            initConfirmJamFlatpickr();

            // Initialize DataTable
            var table = $('#draftTable').DataTable({
                "order": [
                    [8, "desc"]
                ], // Sort by created date
                "pageLength": 25,
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                    "infoEmpty": "Tidak ada data tersedia",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });

            // Confirm draft
            $('.confirm-draft').on('click', function() {
                var id = $(this).data('id');
                var button = $(this);
                openConfirmDraftModal(
                    'single',
                    "{{ url('elits-sample-draft/confirm') }}/" + id,
                    button,
                    0
                );
            });

            // Confirm all drafts
            $('.confirm-all-draft').on('click', function() {
                var id = $(this).data('id');
                var count = $(this).data('count');
                var button = $(this);
                openConfirmDraftModal(
                    'all',
                    "{{ url('elits-sample-draft/confirm-all') }}/" + id,
                    button,
                    count
                );
            });

            // Edit draft
            $('.edit-draft').on('click', function() {
                var id = $(this).data('id');
                window.location.href = "{{ url('elits-sample-draft/edit') }}/" + id;
            });

            // Duplicate draft
            $('.duplicate-draft').on('click', function() {
                var id = $(this).data('id');
                var button = $(this);

                swal({
                    title: "Duplicate Draft Sample?",
                    text: "Draft akan diduplikasi dengan data yang sama.",
                    icon: "info",
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: false,
                            visible: true,
                            className: "btn-secondary"
                        },
                        confirm: {
                            text: "Ya, Duplicate",
                            value: true,
                            visible: true,
                            className: "btn-info"
                        }
                    }
                }).then((confirm) => {
                    if (confirm) {
                        button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                        $.ajax({
                            url: "{{ url('elits-sample-draft/duplicate') }}/" + id,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    swal({
                                        title: "Berhasil!",
                                        text: response.pesan,
                                        icon: "success",
                                        buttons: false,
                                        timer: 1500
                                    }).then(function() {
                                        location.reload();
                                    });
                                } else {
                                    swal({
                                        title: "Error!",
                                        text: response.pesan,
                                        icon: "error"
                                    });
                                    button.prop('disabled', false).html(
                                        '<i class="fa fa-copy"></i>');
                                }
                            },
                            error: function(xhr) {
                                var message = "Gagal menduplikasi draft sample!";
                                if (xhr.responseJSON && xhr.responseJSON.pesan) {
                                    message = xhr.responseJSON.pesan;
                                }
                                swal({
                                    title: "Error!",
                                    text: message,
                                    icon: "error"
                                });
                                button.prop('disabled', false).html(
                                    '<i class="fa fa-copy"></i>');
                            }
                        });
                    }
                });
            });

            // Duplicate entire group
            $('.duplicate-group-btn').on('click', function() {
                var groupId = $(this).data('group-id');
                var groupNumber = $(this).data('group-number');
                var groupCount = $(this).data('group-count');
                var button = $(this);

                swal({
                    title: "Duplicate Grup G" + groupNumber + "?",
                    text: "Semua " + groupCount +
                        " draft dalam grup ini akan diduplikasi sebagai grup baru.",
                    icon: "info",
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: false,
                            visible: true,
                            className: "btn-secondary"
                        },
                        confirm: {
                            text: "Ya, Duplicate Grup",
                            value: true,
                            visible: true,
                            className: "btn-primary"
                        }
                    }
                }).then((confirm) => {
                    if (confirm) {
                        button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                        $.ajax({
                            url: "{{ url('elits-sample-draft/duplicate-group') }}/" +
                                groupId,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    swal({
                                        title: "Berhasil!",
                                        text: response.pesan,
                                        icon: "success",
                                        buttons: false,
                                        timer: 2000
                                    }).then(function() {
                                        location.reload();
                                    });
                                } else {
                                    swal({
                                        title: "Error!",
                                        text: response.pesan,
                                        icon: "error"
                                    });
                                    button.prop('disabled', false).html(
                                        '<i class="fa fa-copy"></i>');
                                }
                            },
                            error: function(xhr) {
                                var message = "Gagal menduplikasi grup draft!";
                                if (xhr.responseJSON && xhr.responseJSON.pesan) {
                                    message = xhr.responseJSON.pesan;
                                } else if (xhr.responseJSON && xhr.responseJSON
                                    .message) {
                                    message = xhr.responseJSON.message;
                                }
                                swal({
                                    title: "Error!",
                                    text: message,
                                    icon: "error"
                                });
                                button.prop('disabled', false).html(
                                    '<i class="fa fa-copy"></i>');
                            }
                        });
                    }
                });
            });

            // Delete draft
            $('.delete-draft').on('click', function() {
                var id = $(this).data('id');
                var button = $(this);

                swal({
                    title: "Hapus Draft Sample?",
                    text: "Draft sample akan dihapus secara permanen.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: false,
                            visible: true,
                            className: "btn-secondary"
                        },
                        confirm: {
                            text: "Ya, Hapus",
                            value: true,
                            visible: true,
                            className: "btn-danger"
                        }
                    },
                    dangerMode: true
                }).then((confirm) => {
                    if (confirm) {
                        button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                        $.ajax({
                            url: "{{ url('elits-sample-draft') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    swal({
                                        title: "Berhasil!",
                                        text: response.pesan,
                                        icon: "success",
                                        buttons: false,
                                        timer: 1500
                                    }).then(function() {
                                        location.reload();
                                    });
                                } else {
                                    swal({
                                        title: "Error!",
                                        text: response.pesan,
                                        icon: "error"
                                    });
                                    button.prop('disabled', false).html(
                                        '<i class="fa fa-trash"></i>');
                                }
                            },
                            error: function(xhr) {
                                var message = "Gagal menghapus draft sample!";
                                if (xhr.responseJSON && xhr.responseJSON.pesan) {
                                    message = xhr.responseJSON.pesan;
                                }
                                swal({
                                    title: "Error!",
                                    text: message,
                                    icon: "error"
                                });
                                button.prop('disabled', false).html(
                                    '<i class="fa fa-trash"></i>');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
