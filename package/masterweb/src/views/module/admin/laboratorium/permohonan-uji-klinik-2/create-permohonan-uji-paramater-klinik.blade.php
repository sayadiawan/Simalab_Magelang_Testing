@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Klinik
@endsection


@section('content')
  <style>
    .select2-container {
      min-width: 10em !important;
    }

    .paper-container {
        background-color: #f5f5dc;
        border: 3px solid #4caf50;
        border-radius: 8px;
        padding: 30px;
        margin: 20px 0;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .paper-container::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #4caf50;
    }

    .paper-container::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #4caf50;
    }

    .category-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 15px;
        font-weight: bold;
        font-size: 16px;
        letter-spacing: 1px;
        margin: 20px 0 15px 0;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .parameter-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px 15px;
        margin-bottom: 25px;
        align-items: start;
    }

    .parameter-item {
        display: flex;
        align-items: flex-start;
        padding: 10px 12px;
        background: white;
        border-radius: 4px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
        min-height: 44px;
        box-sizing: border-box;
    }

    .parameter-item.parameter-empty {
        background: transparent !important;
        border: none !important;
        padding: 10px 12px !important;
        min-height: 44px !important;
        visibility: hidden !important;
        /* Force the cell to take up space in grid */
        content: '' !important;
    }

    .parameter-item.parameter-empty:hover {
        background: transparent !important;
        border: none !important;
        transform: none !important;
    }

    .parameter-item:hover {
        background: #f0f0f0;
        border-color: #4caf50;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .parameter-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 12px;
        margin-top: 2px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .parameter-item label {
        margin: 0;
        cursor: pointer;
        flex: 1;
        font-size: 14px;
        color: #333;
    }

    .info-section {
        background: white;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .info-row {
        display: grid;
        grid-template-columns: 200px 1fr;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .info-label {
        font-weight: 600;
        color: #555;
    }

    .info-value {
        color: #333;
    }
  </style>
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan Uji Klinik
                    Management</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>create permohonan uji paket klinik</span>
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h4>Tambah Permohonan Uji Paket Klinik
      </h4>
    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <form action="{{ route('elits-permohonan-uji-klinik.store-permohonan-uji-parameter') }}" method="POST"
          enctype="multipart/form-data" id="form">

          @csrf

          <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

          <input type="hidden" name="permohonan_uji_klinik" id="permohonan_uji_klinik" value="{{ $id }}" readonly>

          <div class="paper-container">
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">No. Sample:</div>
                    <div class="info-value">{{ $item->noregister_permohonan_uji_klinik }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. Rekam Medis:</div>
                    <div class="info-value">
                        {{ $item->getNoRekamMedis() }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tgl. Register:</div>
                    <div class="info-value">{{ $tgl_register }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nama Pasien:</div>
                    <div class="info-value">{{ $item->pasien->nama_pasien }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Umur/Jenis Kelamin:</div>
                    <div class="info-value">
                        {{ $item->umurtahun_pasien_permohonan_uji_klinik . ' tahun ' . $item->umurbulan_pasien_permohonan_uji_klinik . ' bulan ' . $item->umurhari_pasien_permohonan_uji_klinik . ' hari' }}
                        / {{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </div>
                </div>
            </div>

            {{-- Dynamic Category Layout from Database with Grid Support --}}
            @if(isset($categoryLayouts) && count($categoryLayouts) > 0)
                @foreach($categoryLayouts as $category)
                    <div class="category-header">
                        {{ $category->category_code }}. {{ $category->category_name }}
                    </div>
                    @php
                        $emptyPosition = $category->empty_column_position ?? 'none';
                        $gridRows = $category->grid_rows ?? 0;
                        $gridColumns = $category->grid_columns ?? 3;
                        $items = $category->categoryItems ?? collect();
                        
                        // Check if using grid positioning
                        $useGrid = $items->where('row_position', '!=', null)->count() > 0;
                        
                        if ($useGrid) {
                            // Build grid array
                            $grid = [];
                            $maxRow = 0;
                            foreach ($items as $item) {
                                if ($item->parameterPaketKlinik && $item->row_position && $item->column_position) {
                                    $row = (int)$item->row_position;
                                    $col = (int)$item->column_position;
                                    
                                    if (!isset($grid[$row])) {
                                        $grid[$row] = [];
                                    }
                                    $grid[$row][$col] = $item;
                                    
                                    if ($row > $maxRow) {
                                        $maxRow = $row;
                                    }
                                }
                            }
                            ksort($grid);
                            // Always use gridRows if set, otherwise use maxRow
                            $actualRows = $gridRows > 0 ? (int)$gridRows : max($maxRow, 1);
                        }
                    @endphp
                    
                    @if($useGrid)
                        {{-- Grid-based rendering --}}
                        <!-- DEBUG: Grid Rows={{ $actualRows }}, Columns={{ $gridColumns }}, UseGrid={{ $useGrid ? 'YES' : 'NO' }} -->
                        <div class="parameter-list" style="grid-template-columns: repeat({{ $gridColumns }}, 1fr);">
                            @for($r = 1; $r <= $actualRows; $r++)
                                @for($c = 1; $c <= $gridColumns; $c++)
                                    @if(isset($grid[$r][$c]) && $grid[$r][$c]->parameterPaketKlinik)
                                        @php $item = $grid[$r][$c]; @endphp
                                        <!-- DEBUG: Row={{ $r }}, Col={{ $c }}, Param={{ $item->parameterPaketKlinik->name_parameter_paket_klinik }} -->
                                        <div class="parameter-item">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}_{{ $item->parameterPaketKlinik->harga_parameter_paket_klinik }}"
                                                id="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                            <label for="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                                {{ $item->parameterPaketKlinik->name_parameter_paket_klinik }}
                                            </label>
                                        </div>
                                    @else
                                        <!-- DEBUG: Row={{ $r }}, Col={{ $c }} - EMPTY CELL -->
                                        <div class="parameter-item parameter-empty">
                                            <span style="opacity: 0;">&nbsp;</span>
                                        </div>
                                    @endif
                                @endfor
                            @endfor
                        </div>
                    @else
                        {{-- Legacy list rendering --}}
                        <div class="parameter-list">
                            @if($emptyPosition == 'left')
                                <div class="parameter-item" style="visibility: hidden; pointer-events: none;">
                                    <span>&nbsp;</span>
                                </div>
                            @endif
                            
                            @if($items && count($items) > 0)
                                @foreach($items as $item)
                                    @if($item->parameterPaketKlinik)
                                        <div class="parameter-item">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}_{{ $item->parameterPaketKlinik->harga_parameter_paket_klinik }}"
                                                id="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                            <label for="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                                {{ $item->parameterPaketKlinik->name_parameter_paket_klinik }}
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                            
                            @if($emptyPosition == 'right')
                                <div class="parameter-item" style="visibility: hidden; pointer-events: none;">
                                    <span>&nbsp;</span>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            @else
                <p class="text-center text-muted">Silakan atur layout kategori terlebih dahulu di <a href="{{ route('elits-parameter-paket-klinik.categoryLayout') }}">Penataan Layout</a></p>
            @endif
          </div>

          <div class="table-responsive" style="display: none;">
            <table id="table-parameter-old" class="table">
              <thead>
                <tr>
                  <th style="width: 5%">No</th>
                  <th style="width: 15%">Paket</th>
                  <th style="width: 15%">Jenis Parameter</th>
                  <th style="width: 25%">Parameter</th>
                  <th style="width: 20%">Harga</th>
                  <th style="width: 15%">Aksi</th>
                </tr>
              </thead>

              <tbody>
                <tr id="row_1" class="tr_row">
                  <td style="width: 5%">
                    <h5>1</h5>
                  </td>

                  <td style="width: 15%">
                    <select class="form-control type_parameter" name="type_parameter[1]" id="type_parameter_1"
                      onchange="setDataPaketParameter(1)">
                      <option value="P" selected>Paket</option>
                      <option value="C">Custom</option>
                    </select>
                  </td>

                  <td style="width: 15%">
                    <select class="form-control jenis_parameter" name="jenis_parameter[1]" id="jenis_parameter_1"
                      onchange="setDataJenisParameter(1)" style="display: none; width:100pt">
                      <option value=""></option>
                    </select>
                  </td>


                  <td style="width: 35%">
                    <select class="form-control satuan_parameter" name="satuan_parameter[1]" id="satuan_parameter_1"
                      onchange="setDataSatuanParameter(1)">
                      <option value=""></option>
                    </select>
                  </td>

                  <td style="width: 15%">
                    <input type="number" class="form-control harga_parameter" name="harga_parameter[1]"
                      id="harga_parameter_1" value="0" readonly>
                  </td>

                  <td style="width: 15%">
                    <button type="button" class="btn btn-primary btn-add-row" data-row="1" onclick="addRow(1)">
                      <i class="fas fa-plus"></i>
                    </button>

                    <button type="button" class="btn btn-danger btn-remove-row" data-row="1" onclick="removeRow(1)">
                      <i class="fas fa-minus"></i>
                    </button>
                  </td>
                </tr>
              </tbody>

              <tr>
                <th style="width: 250px" colspan="4" class="text-right">Total Harga</th>
                <td>
                  <input type="text" class="form-control" name="subamount_harga_parameter"
                    id="subamount_harga_parameter" readonly>
                </td>
              </tr>
            </table>
          </div>

          {{-- Total Harga Display --}}
          <div class="info-section mt-3">
            <div class="info-row">
              <div class="info-label">Total Harga:</div>
              <div class="info-value">
                <input type="text" class="form-control" name="subamount_harga_parameter"
                  id="subamount_harga_parameter" readonly style="font-weight: bold;">
              </div>
            </div>
          </div>

        </form>
        @if (Request::get('complete-step') == true)
          <input type="hidden" name="complete_step" id="complete_step" value="1" readonly>

          <a href="{{ route('elits-permohonan-uji-klinik.bukti-daftar-permohonan-uji-parameter', $item->id_permohonan_uji_klinik) }}"
            class="btn btn-primary mr-2 btn-lanjutkan">Lanjutkan</a>

          <button type="submit" class="btn btn-primary mr-2 btn-simpan" id="btn-simpan-lanjutkan"
            style="display: none">Simpan</button>
        @else
          <input type="hidden" name="complete_step" id="complete_step" value="0" readonly>

          <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>

          <button type="button" class="btn btn-light"
            onclick="document.location='{{ url('/elits-permohonan-uji-klinik') }}'">Kembali</button>
        @endif
      </li>
    </ul>
  </div>
@endsection

@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script>
    var CSRF_TOKEN = $('#csrf-token').val();
    getSubAmount()

    function setDataJenisParameter(row) {
      $("#satuan_parameter_" + row).val([]);
      var val_type_parameter = $('#type_parameter_' + row + ' option:selected').val();
      $("#harga_parameter_" + row).val(0);

      getSubAmount()
      setDataSatuanParameter(row);

      $("#satuan_parameter_" + row).select2({
        ajax: {
          url: "{{ route('elits-permohonan-uji-klinik.get-parameter-dan-harga') }}",
          type: "POST",
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              _token: CSRF_TOKEN,
              jenis_parameter: $('#jenis_parameter_' + row).val(),
              type_parameter: val_type_parameter,
              search: params.term // search term
            };
          },
          processResults: function(response) {
            /* return {
              results: response
            }; */

            return {
              results: $.map(response, function(obj) {
                return {
                  id: obj.id,
                  text: obj.text,
                  harga: obj.harga
                };
              })
            };

            getSubAmount()
          },
          cache: true,
        },
        placeholder: 'Pilih parameter',
        theme: 'classic',
        allowClear: true
      });
    }

    function setDataPaketParameter(row) {
      $("#satuan_parameter_" + row).val([]);
      var val_type_parameter = $('#type_parameter_' + row + ' option:selected').val();
      $("#harga_parameter_" + row).val(0);
      getSubAmount()
      setDataSatuanParameter(row);

      if (val_type_parameter == "P") {
        $('#jenis_parameter_' + row).css('display', 'none');
        $('#satuan_parameter_' + row).removeAttr('multiple');
        $('#jenis_parameter_' + row).select2('destroy');
        $("#satuan_parameter_" + row).select2({
          ajax: {
            url: "{{ route('elits-permohonan-uji-klinik.get-parameter-dan-harga') }}",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                _token: CSRF_TOKEN,
                type_parameter: val_type_parameter,
                search: params.term // search term
              };
            },
            processResults: function(response) {
              /* return {
                results: response
              }; */

              return {
                results: $.map(response, function(obj) {
                  return {
                    id: obj.id,
                    text: obj.text,
                    harga: obj.harga
                  };
                })
              };

              getSubAmount()
            },
            cache: true,
          },
          placeholder: 'Pilih parameter',
          theme: 'classic',
          allowClear: true
        });
        $("#satuan_parameter_" + row).prop("name", "satuan_parameter[" + (row) + "]");
      } else {
        $('#jenis_parameter_' + row).css('display', 'block');
        $('#satuan_parameter_' + row).attr('multiple', 'multiple');

        $('#jenis_parameter_' + row).select2({
          ajax: {
            url: "{{ route('getParameterJenisKlinik') }}",
            type: "post",
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                _token: CSRF_TOKEN,
                search: params.term // search term
              };
            },
            processResults: function(response) {
              return {
                results: response
              };
            },
            cache: true,
          },
          placeholder: 'Pilih jenis parameter',
          theme: 'classic',
          allowClear: true
        });
        $("#satuan_parameter_" + row).select2({
          ajax: {
            url: "{{ route('elits-permohonan-uji-klinik.get-parameter-dan-harga') }}",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                _token: CSRF_TOKEN,
                jenis_parameter: $('#jenis_parameter_' + row).val(),
                type_parameter: val_type_parameter,
                search: params.term // search term
              };
            },
            processResults: function(response) {
              /* return {
                results: response
              }; */

              return {
                results: $.map(response, function(obj) {
                  return {
                    id: obj.id,
                    text: obj.text,
                    harga: obj.harga
                  };
                })
              };

              getSubAmount()
            },
            cache: true,
          },
          placeholder: 'Pilih parameter',
          theme: 'classic',
          allowClear: true
        });

        $("#satuan_parameter_" + row).prop("name", "satuan_parameter[" + (row) + "][]");
      }



    }

    function setDataSatuanParameter(row) {
      var val_jenis_parameter = $('#jenis_parameter_' + row + ' option:selected').val();
      var val_type_parameter = $('#type_parameter_' + row + ' option:selected').val();

      if (val_type_parameter == "P") {
        var val_satuan_parameter = $('#satuan_parameter_' + row + ' option:selected').val();


      } else {
        var val_satuan_parameter = [];

        val_satuan_parameter = $("#satuan_parameter_" + row).val();
      }

      if (val_jenis_parameter !== "" || val_type_parameter !== "" || val_type_parameter !== "") {
        $.ajax({
          url: "{{ route('elits-permohonan-uji-klinik.count-parameter-dan-harga') }}",
          type: "POST",
          data: {
            _token: CSRF_TOKEN,
            jenis_parameter: val_jenis_parameter,
            type_parameter: val_type_parameter,
            satuan_parameter: val_satuan_parameter
          },
          dataType: "JSON",
          success: function(response) {

            if (response[0].harga > 0) {
              $('#harga_parameter_' + row).val(response[0].harga);
            } else {
              $('#harga_parameter_' + row).val(0);
            }

            getSubAmount()
          },
          error: function(e) {
            swal("Error!", "System gagal mendapatkan harga parameter!", "error");
          }
        });
      } else {
        $('#harga_parameter_' + row).val(0);
      }

    }



    function addRow(row) {
      var tableParameterLength = $("#table-parameter tbody .tr_row").length;


      for (x = 0; x < tableParameterLength; x++) {
        var tr = $("#table-parameter tbody tr")[x];
        var count = $(tr).attr('id');

        count = Number(count.substring(4));

      } // /for

      var count_table_tbody_tr = $("#table-parameter tbody .tr_row").length;
      id_html = count + 1;

      var dom_html = `<tr id="row_${id_html}" class="tr_row">
                        <td style="width: 5%">
                            <h5>${id_html}</h5>
                        </td>

                        <td style="width: 15%">
                            <select class="form-control type_parameter" name="type_parameter[${id_html}]" id="type_parameter_${id_html}" onchange="setDataPaketParameter(${id_html})">
                                <option value="P" selected>Paket</option>
                                <option value="C">Custom</option>
                            </select>
                        </td>
                        <td style="width: 15%">
                            <select class="form-control jenis_parameter" style="width:100pt" name="jenis_parameter[${id_html}]" id="jenis_parameter_${id_html}" onchange="setDataJenisParameter(${id_html})" >
                                <option value=""></option>
                            </select>
                        </td>


                        <td style="width: 35%">
                            <select class="form-control satuan_parameter" name="satuan_parameter[${id_html}]" id="satuan_parameter_${id_html}" onchange="setDataSatuanParameter(${id_html})">
                                <option value=""></option>
                            </select>
                        </td>

                        <td style="width: 15%">
                            <input type="number" class="form-control harga_parameter" name="harga_parameter[${id_html}]" id="harga_parameter_${id_html}" readonly>
                        </td>

                        <td style="width: 15%">
                            <button type="button" class="btn btn-primary btn-add-row" data-row="${id_html}" onclick="addRow(${id_html})">
                                <i class="fas fa-plus"></i>
                            </button>

                            <button type="button" class="btn btn-danger btn-remove-row" data-row="${id_html}" onclick="removeRow(${id_html})">
                                <i class="fas fa-minus"></i>
                            </button>
                        </td>
                    </tr>`;

      // $(document.getElementById('main-bdy')).append(dom_html);

      if (count_table_tbody_tr >= 1) {
        $("#table-parameter tbody .tr_row:last").after(dom_html);
      } else {
        $("#table-parameter tbody").html(dom_html);
      }

      $('#jenis_parameter_' + id_html).select2({
        ajax: {
          url: "{{ route('getParameterJenisKlinik') }}",
          type: "post",
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              _token: CSRF_TOKEN,
              search: params.term // search term
            };
          },
          processResults: function(response) {
            return {
              results: response
            };
          },
          cache: true,
        },
        placeholder: 'Pilih jenis parameter',
        theme: 'classic',
        allowClear: true
      });

      sorting()
      setDataJenisParameter(id_html);
      setDataPaketParameter(id_html);
      setDataSatuanParameter(id_html);
    }

    function removeRow(row) {
      var count_table_tbody_tr = $("#table-parameter tbody .tr_row").length;

      // console.log(count_table_tbody_tr-1);



      if (count_table_tbody_tr > 1) {
        $("#table-parameter tbody .tr_row#row_" + row).remove();

        getSubAmount()
      }
      sorting()
    }

    function sorting() {

      $("#table-parameter tbody .tr_row").each(function(i, element) {
        console.log($(element).find('h5'));
        $(element).find('h5').html((i + 1));
        $(element).find('.type_parameter').prop("id", "type_parameter_" + (i + 1));
        $(element).find('.type_parameter').prop("name", "type_parameter[" + (i + 1) + "]");
        $(element).find('.type_parameter').attr("onchange", "setDataPaketParameter(" + (i + 1) + ")");

        $(element).find('.jenis_parameter').prop("id", "jenis_parameter_" + (i + 1));
        $(element).find('.jenis_parameter').prop("name", "jenis_parameter[" + (i + 1) + "]");
        $(element).find('.jenis_parameter').attr("onchange", "setDataJenisParameter(" + (i + 1) + ")");

        $(element).find('.satuan_parameter').prop("id", "satuan_parameter_" + (i + 1));
        if ($(element).find('.satuan_parameter').attr("multiple") != undefined) {

          $(element).find('.satuan_parameter').prop("name", "satuan_parameter[" + (i + 1) + "][]");
        } else {
          $(element).find('.satuan_parameter').prop("name", "satuan_parameter[" + (i + 1) + "]");
        }
        $(element).find('.satuan_parameter').attr("onchange", "setDataSatuanParameter(" + (i + 1) + ")");

        $(element).find('.harga_parameter').prop("id", "harga_parameter_" + (i + 1));
        $(element).find('.harga_parameter').prop("name", "harga_parameter[" + (i + 1) + "]");


        $(element).find('.btn-add-row').attr("data-row", (i + 1));
        $(element).find('.btn-add-row').attr("onclick", "addRow(" + (i + 1) + ")");


        $(element).find('.btn-remove-row').attr("data-row", (i + 1));
        $(element).find('.btn-remove-row').attr("onclick", "removeRow(" + (i + 1) + ")");

        $(element).prop("id", "row_" + (i + 1));
      })
    }

    function getSubAmount() {
      var tableParameterLength = $("#table-parameter tbody .tr_row").length;
      var totalSubAmount = 0;

      for (x = 0; x < tableParameterLength; x++) {
        var tr = $("#table-parameter tbody .tr_row")[x];
        var count = $(tr).attr('id');

        count = Number(count.substring(4));


        totalSubAmount = Number(totalSubAmount) + Number($("#harga_parameter_" + count).val());
      } // /for

      totalSubAmount = Number(totalSubAmount);


      $("#subamount_harga_parameter").val(totalSubAmount);

      if ($('#subamount_harga_parameter').val() != null && $('#subamount_harga_parameter').val() != 0 && $(
          '#subamount_harga_parameter').val() != '') {
        $('#btn-simpan-lanjutkan').show();
        $('.btn-lanjutkan').hide();
      } else {
        $('#btn-simpan-lanjutkan').hide();
        $('.btn-lanjutkan').show();
      }
    }
    $(document).ready(function() {
      $('.btn-simpan').on('click', function() {
        $('#form').ajaxSubmit({
          success: function(response) {
            if (response.status == true) {
              swal({
                  title: "Success!",
                  text: response.pesan,
                  icon: "success"
                })
                .then(function() {
                  document.location = response.urlNextStep;
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
        })
      })
    })

    $("#satuan_parameter_1").select2({
      ajax: {
        url: "{{ route('elits-permohonan-uji-klinik.get-parameter-dan-harga') }}",
        type: "POST",
        dataType: 'json',
        delay: 250,
        data: function(params) {
          return {
            _token: CSRF_TOKEN,
            type_parameter: "P",
            search: params.term // search term
          };
        },
        processResults: function(response) {
          /* return {
            results: response
          }; */

          return {
            results: $.map(response, function(obj) {
              return {
                id: obj.id,
                text: obj.text,
                harga: obj.harga
              };
            })
          };

          // getSubAmount()
        },
        cache: true,
      },
      placeholder: 'Pilih parameter',
      theme: 'classic',
      allowClear: true
    });

    // ==================== NEW CHECKBOX SYSTEM ====================
    $(document).ready(function() {
      // Track selected parameters to prevent duplicates
      const selectedParameters = new Set();

      // Handle checkbox selection
      $('.form-check-input').on('change', function() {
        const paramId = $(this).attr('id').replace('param_', '');
        const paramLabel = $('label[for="' + $(this).attr('id') + '"]').text().trim();

        if ($(this).is(':checked')) {
          // Check if already selected in another category
          if (selectedParameters.has(paramId)) {
            alert('Parameter "' + paramLabel + '" sudah dipilih di kategori lain.');
            $(this).prop('checked', false);
            return;
          }
          selectedParameters.add(paramId);
        } else {
          selectedParameters.delete(paramId);
        }

        updateParameterOrder();
      });

      // Update hidden input for parameter order
      function updateParameterOrder() {
        // Remove existing parameter_order inputs
        $('input[name="parameter_order[]"]').remove();

        // Add parameter_order inputs based on checkbox order in DOM
        $('.form-check-input:checked').each(function() {
          const paramValue = $(this).val(); // Format: id_harga
          const paramId = paramValue.split('_')[0];
          $('<input>').attr({
            type: 'hidden',
            name: 'parameter_order[]',
            value: paramId
          }).appendTo('#form-permohonan-uji-parameter');
        });
      }

      // Show/hide old table system based on selection
      $('.form-check-input').on('change', function() {
        const anyChecked = $('.form-check-input:checked').length > 0;
        if (anyChecked) {
          $('#table-parameter-old').closest('.table-responsive').hide();
          updateTotalHarga();
        } else {
          $('#table-parameter-old').closest('.table-responsive').show();
        }
      });

      // Calculate and display total price
      function updateTotalHarga() {
        let totalHarga = 0;
        $('.form-check-input:checked').each(function() {
          const value = $(this).val();
          const harga = parseFloat(value.split('_')[1]) || 0;
          totalHarga += harga;
        });
        $('#subamount_harga_parameter').val(totalHarga);

        // Show/hide simpan button based on selection
        if (totalHarga > 0) {
          $('#btn-simpan-lanjutkan').show();
          $('.btn-simpan').show();
        } else {
          $('#btn-simpan-lanjutkan').hide();
        }
      }

      // Update total on checkbox change
      $('.form-check-input').on('change', updateTotalHarga);

      // Initial update
      updateTotalHarga();
    });
  </script>
@endsection
