@extends('masterweb::template.admin.layout')
@section('title')
    Urutkan Parameter Jenis Klinik
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0"><i class="fa fa-sort mr-2"></i>Urutkan Parameter Jenis Klinik</h4>
                        <a href="{{ url('/elits-parameter-jenis-klinik') }}" class="btn btn-light">Kembali</a>
                    </div>

                    <p class="text-muted">Seret untuk mengubah urutan atau edit angka urutan lalu klik "Simpan Urutan".</p>

                    <ul id="sortable-params" class="list-group">
                        @foreach ($data as $row)
                            <li class="list-group-item d-flex align-items-center"
                                data-id="{{ $row->id_parameter_jenis_klinik }}">
                                <span class="handle mr-3" style="cursor: move"><i class="fa fa-bars"></i></span>
                                <input type="number" class="form-control form-control-sm mr-2" min="1"
                                    step="1" style="width:80px" value="{{ $row->sort_parameter_jenis_klinik ?? 0 }}"
                                    aria-label="Urutan" />
                                <div>
                                    <div>
                                        <strong>{{ $row->name_parameter_jenis_klinik }}</strong>
                                        <span class="badge badge-{{ $row->level == 0 ? 'primary' : 'info' }} ml-2">
                                            {{ $row->level == 0 ? 'Parent' : 'Sub' }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        {{ $row->code_parameter_jenis_klinik }}
                                        @if($row->parent)
                                            | Parent: {{ $row->parent->name_parameter_jenis_klinik }}
                                        @endif
                                    </small>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <button type="button" id="save-order" class="btn btn-primary mt-3"><i
                            class="fa fa-save mr-1"></i>Simpan Urutan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
    <script>
        (function() {
            function init() {
                if (!$.fn.sortable) return;
                $('#sortable-params').sortable({
                    handle: '.handle',
                    update: function(event, ui) {
                        // Update input values berdasarkan posisi baru setelah drag & drop
                        $('#sortable-params li').each(function(index) {
                            $(this).find('input[type=number]').val(index + 1);
                        });
                    }
                });
                $('#save-order').on('click', function() {
                    var orders = [];
                    // Urutan array sudah mencerminkan urutan drag & drop (DOM order)
                    // Gunakan posisi di list sebagai urutan untuk memastikan konsistensi
                    $('#sortable-params li').each(function(index) {
                        var id = $(this).data('id');
                        // Urutan berdasarkan posisi di list (index + 1)
                        // Ini memastikan urutan sesuai dengan drag & drop yang sudah dilakukan
                        var sort = index + 1;
                        orders.push({
                            id: id,
                            sort: sort
                        });
                    });
                    $.post("{{ route('parameter-jenis-klinik.reorder') }}", {
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
                    })
                })
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
