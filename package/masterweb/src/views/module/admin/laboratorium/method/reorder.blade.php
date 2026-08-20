@extends('masterweb::template.admin.layout')
@section('title')
    Urutkan Method
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
                                            class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-methods') }}">Method Management</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Urutkan</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 8px;">
                <h4 class="card-title mb-0"><i class="fa fa-sort mr-2"></i>Urutkan Method</h4>
                <a href="{{ route('elits-methods.index') }}" class="btn btn-light">Kembali</a>
            </div>

            <div class="alert alert-info">
                <i class="fa fa-info-circle mr-1"></i>
                Default urutan diambil dari detail jenis sarana
                <strong>{{ $sampleType->name_sample_type ?? 'Air Higiene' }}</strong>
                (<a href="{{ url('/elits-sampletypes/' . $defaultSampleTypeId . '/edit') }}" target="_blank"
                    class="ml-1">buka edit</a>).
                Method yang belum ada di jenis sarana bisa digeser / diatur di bawah.
            </div>

            <div class="mb-3 d-flex flex-wrap" style="gap: 8px;">
                <button type="button" id="btn-sync-empty" class="btn btn-outline-info btn-sm">
                    <i class="fa fa-copy mr-1"></i>Salin dari Jenis Sarana (hanya yang kosong)
                </button>
                <button type="button" id="btn-sync-all" class="btn btn-outline-warning btn-sm">
                    <i class="fa fa-sync mr-1"></i>Reset &amp; Salin Ulang dari Jenis Sarana
                </button>
            </div>

            <p class="text-muted">Seret untuk mengubah urutan lalu klik "Simpan Urutan".</p>

            <ul id="sortable-methods" class="list-group">
                @foreach ($methods as $row)
                    @php
                        $fromSt = $sampleTypeOrderMap[$row->id_method] ?? null;
                    @endphp
                    <li class="list-group-item d-flex align-items-center"
                        data-id="{{ $row->id_method }}">
                        <span class="handle mr-3" style="cursor: move"><i class="fa fa-bars"></i></span>
                        <input type="number" class="form-control form-control-sm mr-2" min="1" step="1"
                            style="width:80px" value="{{ $row->orderlist_method ?? '' }}" aria-label="Urutan"
                            readonly />
                        <div class="flex-grow-1">
                            <div>
                                <strong>{{ $row->params_method }}</strong>
                                @if ($fromSt)
                                    <span class="badge badge-success ml-2"
                                        title="Ada di detail jenis sarana (urutan {{ $fromSt['detail_order'] }})">
                                        Dari jenis sarana #{{ $fromSt['order'] }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary ml-2"
                                        title="Belum ada di detail jenis sarana default">
                                        Belum di jenis sarana
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $row->name_method }}</small>
                        </div>
                    </li>
                @endforeach
            </ul>

            <button type="button" id="save-order" class="btn btn-primary mt-3">
                <i class="fa fa-save mr-1"></i>Simpan Urutan
            </button>
        </div>
    </div>

    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
    <script>
        (function() {
            var syncUrl = "{{ route('elits-methods.reorder-sync') }}";
            var sampleTypeId = "{{ $defaultSampleTypeId }}";

            function renumberInputs() {
                $('#sortable-methods li').each(function(index) {
                    $(this).find('input[type=number]').val(index + 1);
                });
            }

            function init() {
                if (!$.fn.sortable) return;
                $('#sortable-methods').sortable({
                    handle: '.handle',
                    update: function() {
                        renumberInputs();
                    }
                });

                $('#save-order').on('click', function() {
                    var orders = [];
                    $('#sortable-methods li').each(function(index) {
                        orders.push({
                            id: $(this).data('id'),
                            sort: index + 1
                        });
                    });
                    $.post("{{ route('elits-methods.reorder') }}", {
                        _token: '{{ csrf_token() }}',
                        orders: orders
                    }, function(resp) {
                        if (resp.status) {
                            swal('Success', resp.pesan, 'success').then(function() {
                                window.location.reload();
                            });
                        } else {
                            swal('Error', resp.pesan || 'Gagal', 'error');
                        }
                    });
                });

                function syncFromSampleType(fillOnlyEmpty) {
                    var confirmText = fillOnlyEmpty
                        ? 'Salin urutan dari jenis sarana hanya untuk method yang belum punya urutan?'
                        : 'Reset urutan semua method mengikuti jenis sarana? Urutan manual akan diganti.';
                    swal({
                        title: 'Konfirmasi',
                        text: confirmText,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: !fillOnlyEmpty
                    }).then(function(ok) {
                        if (!ok) return;
                        $.post(syncUrl, {
                            _token: '{{ csrf_token() }}',
                            sample_type_id: sampleTypeId,
                            fill_only_empty: fillOnlyEmpty ? '1' : '0'
                        }, function(resp) {
                            if (resp.status) {
                                swal('Success', resp.pesan, 'success').then(function() {
                                    window.location.reload();
                                });
                            } else {
                                swal('Error', resp.pesan || 'Gagal', 'error');
                            }
                        });
                    });
                }

                $('#btn-sync-empty').on('click', function() {
                    syncFromSampleType(true);
                });
                $('#btn-sync-all').on('click', function() {
                    syncFromSampleType(false);
                });
            }

            function ensureUi(cb) {
                if ($.fn.sortable) {
                    cb();
                    return;
                }
                var srcs = [
                    'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js',
                    'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js'
                ];
                var i = 0;

                function loadNext() {
                    var s = document.createElement('script');
                    s.src = srcs[i++];
                    s.onload = cb;
                    s.onerror = function() {
                        if (i < srcs.length) loadNext();
                    };
                    document.head.appendChild(s);
                }
                loadNext();
            }
            $(function() {
                ensureUi(init);
            });
        })();
    </script>
@endsection
