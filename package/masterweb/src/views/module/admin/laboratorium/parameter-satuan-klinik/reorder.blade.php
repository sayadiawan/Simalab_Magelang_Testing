@extends('masterweb::template.admin.layout')
@section('title')
    Urutkan Parameter Satuan Klinik
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0"><i class="fa fa-sort mr-2"></i>Urutkan Parameter Satuan Klinik</h4>
                        <a href="{{ url('/elits-parameter-satuan-klinik') }}" class="btn btn-light">Kembali</a>
                    </div>

                    <p class="text-muted">Seret untuk mengubah urutan atau edit angka urutan lalu klik "Simpan Urutan".</p>

                    <ul id="sortable-params" class="list-group">
                        @foreach ($data as $row)
                            <li class="list-group-item d-flex align-items-center"
                                data-id="{{ $row->id_parameter_satuan_klinik }}">
                                <span class="handle mr-3" style="cursor: move"><i class="fa fa-bars"></i></span>
                                <input type="number" class="form-control form-control-sm mr-2" min="1"
                                    step="1" style="width:80px" value="{{ $row->sort_parameter_satuan_klinik }}"
                                    aria-label="Urutan" />
                                <div>
                                    <div><strong>{{ $row->name_parameter_satuan_klinik }}</strong></div>
                                    <small
                                        class="text-muted">{{ $row->parameterjenisklinik->name_parameter_jenis_klinik }}</small>
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
                    handle: '.handle'
                });
                $('#save-order').on('click', function() {
                    var orders = [];
                    $('#sortable-params li').each(function() {
                        var id = $(this).data('id');
                        var sort = parseInt($(this).find('input[type=number]').val(), 10) || 0;
                        orders.push({
                            id: id,
                            sort: sort
                        });
                    });
                    $.post("{{ route('parameter-satuan-klinik.reorder') }}", {
                        _token: '{{ csrf_token() }}',
                        orders: orders
                    }, function(resp) {
                        if (resp.status) {
                            swal('Success', resp.pesan, 'success');
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
