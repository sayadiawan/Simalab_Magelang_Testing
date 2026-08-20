@extends('masterweb::template.admin.layout')

@section('title')
Baku Mutu Lab.{{ $lab }} Management
@endsection

@section('css')
@endsection

@section('content')
<div class="row">
  <div class="col-12 grid-margin stretch-card">
    <div class="card">
      <div class="">
        <div class="template-demo">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
              <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                  Beranda</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/elits-baku-mutu-' . $lab_link) }}"> Baku Mutu
                  Lab.{{ $lab }}</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex">
      <div class="mr-auto p-2">
        {{-- <div id="datepicker-popup" class="input-group date datepicker">
          <input type="text" class="form-control">
          <span class="input-group-addon input-group-append border-left">
            <span class="far fa-calendar input-group-text"></span>
          </span>
        </div> --}}
      </div>

      <div class="p-2">
        <a href="{{ url('/elits-baku-mutu-' . $lab_link . '/create') }}">

          <button type="button" class="btn btn-info btn-icon-text">
            Tambah Data
            <i class="fa fa-plus btn-icon-append"></i>
          </button>
        </a>
      </div>

    </div>

    <div class="row">
      <div class="col-2 mb-2">
        <select id="filter-jenis-sample" class="form-control mr-2 smt-select2">
          <option value="">-- Semua Jenis Sample --</option>
          @foreach (($sample_types_for_filter ?? collect()) as $st)
          <option value="{{ $st->name_sample_type }}">{{ $st->name_sample_type }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-3 mb-2">
        <select id="filter-parameter" class="form-control mr-2 smt-select2">
          <option value="">-- Semua Parameter --</option>
          @foreach (($sample_methods ?? collect()) as $sample_method)
          <option value="{{ $sample_method->params_method }}">{{ $sample_method->params_method }}</option>
          @endforeach
        </select>
      </div>
      @if (session('status'))
      <div class="alert alert-success">
        {{ session('status') }}
      </div>
      @endif

      <div class="col-12">
        <div class="table-responsive">
          <table id="table-baku-mutu" class="table" style="width: 100%">
            <thead>
              <tr>
                <th width="15">No</th>
                <th>Jenis Sample</th>
                <th>Jenis Makanan</th>
                <th>Parameter</th>
                <th>Acuan Baku Mutu</th>
                <th>Nilai Baku Mutu</th>
                <th width="200">Actions</th>
              </tr>
            </thead>

            <tbody class="table-border-bottom-0">
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


@section('scripts')
<script>
  window.bakuMutuParamsByJenis = @json($params_by_jenis_sample ?? []);
  window.bakuMutuParamsAll = @json($params_all ?? []);
</script>
<script>
  $(document).ready(function() {
            function rebuildParameterOptionsKimia() {
                var jenis = $('#filter-jenis-sample').val();
                var byJenis = window.bakuMutuParamsByJenis || {};
                var list = (!jenis) ? (window.bakuMutuParamsAll || []) : (byJenis[jenis] || []);
                var prev = $('#filter-parameter').val();
                var $sel = $('#filter-parameter');
                $sel.empty();
                $sel.append($('<option></option>').attr('value', '').text('-- Semua Parameter --'));
                list.forEach(function(p) {
                    $sel.append($('<option></option>').attr('value', p).text(p));
                });
                if (prev && list.indexOf(prev) !== -1) {
                    $sel.val(prev);
                } else {
                    $sel.val('');
                }
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.trigger('change.select2');
                } else {
                    $sel.trigger('change');
                }
            }

            var table = $('#table-baku-mutu').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                responsive: true,
                ajax: {
                    url: "{{ url()->current() }}",
                    type: "GET",
                    data: function(d) {
                        d.search = $('input[type="search"]').val();
                        d.jenis_sample = $('#filter-jenis-sample').val();
                        d.parameter = $('#filter-parameter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'jenis_sample',
                        name: 'jenis_sample'
                    },
                    {
                        data: 'jenis_makanan',
                        name: 'jenis_makanan',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'parameter',
                        name: 'parameter'
                    },
                    {
                        data: 'acuan_bakumutu',
                        name: 'acuan_bakumutu',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nilai_bakumutu',
                        name: 'nilai_bakumutu',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            rebuildParameterOptionsKimia();

            $('#filter-jenis-sample').on('change', function() {
                rebuildParameterOptionsKimia();
                table.draw();
            });
            $('#filter-parameter').on('change', function() {
                table.draw();
            });

            table.on('draw', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            // datatables responsive
            new $.fn.dataTable.FixedHeader(table);

            $('#table-baku-mutu').on('click', '.btn-hapus', function() {
                var kode = $(this).data('id');
                var nama = $(this).data('nama');

                swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk menghapus data : " + nama,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                type: 'ajax',
                                method: 'get',
                                url: '/elits-baku-mutu-{{ $lab_link }}-destroy/' + kode,
                                async: true,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status == true) {
                                        swal({
                                                title: "Success!",
                                                text: response.pesan,
                                                icon: "success"
                                            })
                                            .then(function() {
                                                table.ajax.reload();
                                            });
                                    } else {
                                        swal("Hapus Data Gagal!", {
                                            icon: "warning",
                                            title: "Failed!",
                                            text: response.pesan,
                                        });
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    var err = eval("(" + jqXHR.responseText + ")");
                                    swal("Error!", err.Message, "error");
                                }
                            });
                        } else {
                            swal("Cancelled", "Hapus data dibatalkan!", "error");
                        }
                    });
            });
        });
</script>
@endsection