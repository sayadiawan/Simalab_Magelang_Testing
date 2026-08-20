@extends('masterweb::template.admin.layout')
@section('title')
  Baku Mutu Lab.{{ $lab }} Management
@endsection

@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-baku-mutu-' . $lab_link) }}"> Baku Mutu
                    Lab.{{ $lab }}</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Edit</span></li>
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
        action="{{ route('elits-baku-mutu-klinik.update', $item->id_baku_mutu) }}" method="POST">

        @csrf
        @method('PUT')

        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
        <input type="hidden" class="form-control" name="lab_id" id="lab_id" value="{{ $item->lab_id }}" readonly>

        <div class="form-group">
          <label for="parameter_jenis_klinik_id">Parameter Jenis Klinik</label>

          <select class="form-control" name="parameter_jenis_klinik_id" id="parameter_jenis_klinik_id">
            <option value="{{ $item->parameter_jenis_klinik_id }}" selected>
              {{ $item->parameterjenisklinik->name_parameter_jenis_klinik }}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="parameter_satuan_klinik_id">Parameter Satuan Klinik</label>

          <select class="form-control" name="parameter_satuan_klinik_id" id="parameter_satuan_klinik_id">
            <option value="{{ $item->parameter_satuan_klinik_id }}" selected>
              {{ $item->parametersatuanklinik->name_parameter_satuan_klinik }}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="library_id">Acuan Baku Mutu</label>

          <select class="form-control" name="library_id" id="library_id">
            <option value="{{ $item->library_id }}" selected>{{ $item->library->title_library }}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="min">Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapan koma, maka
              menggunakan .
              (titik), apabila tidak ada kosongi)</b></label>
          <input type="text" class="form-control" id="min" name="min" value="{{ $item->min ?? 0 }}"
            placeholder="Kadar Min Baku Mutu">
        </div>

        <div class="form-group">
          <label for="max">Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka
              menggunakan .
              (titik), apabila tidak ada kosongi)</b></label>
          <input type="text" class="form-control" id="max" name="max" value="{{ $item->max ?? 0 }}"
            placeholder="Kadar Max Baku Mutu">
        </div>

        <div class="form-group">
          <label for="equal">Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan berupa range minimal
              maksimal
              misal (Negatif atau Positif) maka isi disini, apabila tidak maka kosongi)</b></label>
          <input type="text" class="form-control" id="equal" name="equal" value="{{ $item->equal ?? 0 }}"
            placeholder="Nilai Harus Sama Dengan">
        </div>


        <div class="form-group">
          <label for="nilai_baku_mutu">Nilai Baku Mutu di Laporan</label>
          <input type="text" class="form-control" id="nilai_baku_mutu" name="nilai_baku_mutu"
            placeholder="Nilai Baku Mutu" value="{{ $item->nilai_baku_mutu ?? old('nilai_baku_mutu') }}" required>
        </div>

        <br>

      </form>
      <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
      <button type="button" onclick="document.location='{{ url('/elits-baku-mutu-klinik') }}'"
        class="btn btn-light">Kembali</button>
    </div>
  </div>

  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script>
    function goBack() {
      window.history.back();
    }
    $(document).ready(function() {
      $(function() {
        var CSRF_TOKEN = $('#csrf-token').val();

        $("#parameter_jenis_klinik_id").select2({
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
            cache: true
          },
          allowClear: true
        });

        $("#parameter_satuan_klinik_id").select2({
          ajax: {
            url: "{{ route('getParameterSatuanKlinik') }}",
            type: "post",
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                _token: CSRF_TOKEN,
                search: params.term, // search term
                param: $("#parameter_jenis_klinik_id").val()
              };
            },
            processResults: function(response) {
              return {
                results: response
              };
            },
            cache: true
          },
          placeholder: 'Pilih parameter satuan',
          allowClear: true,
        });


        $("#parameter_jenis_klinik_id").change(function(e) {
          $("#parameter_satuan_klinik_id").val(0).trigger('change');

          $("#parameter_satuan_klinik_id").select2({
            ajax: {
              url: "{{ route('getParameterSatuanKlinik') }}",
              type: "post",
              dataType: 'json',
              delay: 250,
              data: function(params) {
                return {
                  _token: CSRF_TOKEN,
                  search: params.term, // search term
                  param: $("#parameter_jenis_klinik_id").val()
                };
              },
              processResults: function(response) {
                return {
                  results: response
                };
              },
              cache: true
            },
            placeholder: 'Pilih parameter jenis',
            allowClear: true,
          });
        })

        $("#library_id").select2({
          ajax: {
            url: "{{ route('getLibrary') }}",
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
            cache: true
          },
          allowClear: true
        });


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
                    document.location = '/elits-baku-mutu-klinik';
                  });
              } else {
                var pesan = "";

                jQuery.each(response.pesan, function(key, value) {
                  pesan += value + '. ';
                });

                swal({
                  title: "Error!",
                  text: pesan,
                  icon: "warning"
                });
              }
            },
            error: function() {
              swal("Error!", "System gagal menyimpan!", "error");
            }
          })
        })
      })
    })
  </script>
@endsection
