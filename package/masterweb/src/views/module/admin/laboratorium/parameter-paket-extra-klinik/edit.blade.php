@extends('masterweb::template.admin.layout')
@section('title')
  Parameter Paket Klinik
@endsection

@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ url('/home') }}">
                    <i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a>
                </li>
                <li class="breadcrumb-item">
                  <a href="{{ url('/elits-parameter-paket-extra') }}">Parameter Paket Extra</a>
                </li>
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
              action="{{ route('elits-parameter-paket-extra.update', $item->id_parameter_paket_extra) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="nama_parameter_paket_extra">Nama Parameter Paket Extra</label>
                <input type="text" class="form-control" id="nama_parameter_paket_extra" name="nama_parameter_paket_extra"
                       placeholder="Nama parameter paket extra.." value="{{ old('nama_parameter_paket_extra', $item->nama_parameter_paket_extra) }}">
            </div>

            <div class="form-group">
                <label for="parameter_paket_klinik">Pilih Parameter Paket Klinik:</label>
                <select class="select2-multiple" name="parameter_paket_klinik[]" multiple="multiple" style="width: 100%;">
                    @foreach($parameter_paket as $paket)
                        <option value="{{ $paket->id_parameter_paket_klinik }}"
                                {{ in_array($paket->id_parameter_paket_klinik, $selected_paket) ? 'selected' : '' }}>
                            {{ $paket->name_parameter_paket_klinik }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="harga_parameter_paket_extra">Harga Parameter Paket (Rupiah)</label>
                <input type="number" class="form-control" id="harga_parameter_paket_extra" name="harga_parameter_paket_extra"
                       placeholder="Harga parameter paket klinik.." value="{{ old('harga_parameter_paket_extra', $item->harga_parameter_paket_extra) }}">
            </div>

            <button type="submit" class="btn btn-primary btn-simpan">Update</button>
            <button type="button" onclick="document.location='{{ url('/elits-parameter-paket-extra') }}'" class="btn btn-light">Kembali</button>
        </form>
    </div>
  </div>

  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script>
      $(document).ready(function() {
          var CSRF_TOKEN = "{{ csrf_token() }}";

          // Inisialisasi Select2
          $('.select2-multiple').select2({
              placeholder: "Pilih Paket..",
              allowClear: true,
              theme: "bootstrap4",
          });

          // Handler untuk tombol simpan
          $('.btn-simpan').on('click', function(event) {
              event.preventDefault(); // Mencegah form submission standar

              $.ajax({
                  url: $('#form').attr('action'),
                  type: 'POST',
                  data: $('#form').serialize(), // Serialize form data
                  headers: {
                      'X-CSRF-TOKEN': CSRF_TOKEN // Menambahkan token CSRF
                  },
                  success: function(response) {
                      if (response.status === true) {
                          swal({
                              title: "Success!",
                              text: response.pesan,
                              icon: "success"
                          }).then(function() {
                              // Pastikan response.redirect_url tersedia dan tidak kosong
                              if (response.redirect_url) {
                                  window.location.href = response.redirect_url;
                              } else {
                                  console.error('Redirect URL tidak tersedia');
                              }
                          });
                      } else {
                          var pesan = "";
                          $.each(response.pesan, function(key, value) {
                              pesan += value + '. ';
                          });

                          swal({
                              title: "Error!",
                              text: pesan,
                              icon: "warning"
                          });
                      }
                  },
                  error: function(xhr, status, error) {
                      swal({
                          title: "Error!",
                          text: "Sistem gagal menyimpan! " + error,
                          icon: "error"
                      });
                  }
              });
          });
      });
  </script>
@endsection
