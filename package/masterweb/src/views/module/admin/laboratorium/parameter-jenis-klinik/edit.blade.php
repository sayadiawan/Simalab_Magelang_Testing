@extends('masterweb::template.admin.layout')
@section('title')
  Parameter Jenis Klinik
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-parameter-jenis-klinik') }}">Parameter Jenis
                    Klinik</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>edit</span></li>
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
        action="{{ route('elits-parameter-jenis-klinik.update', $item->id_parameter_jenis_klinik) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="form-group">
          <label for="name_parameter_jenis_klinik">Nama Parameter Jenis</label>

          <input type="text" class="form-control" id="name_parameter_jenis_klinik" name="name_parameter_jenis_klinik"
            placeholder="Name parameter jenis klinik.."
            value="{{ $item->name_parameter_jenis_klinik ?? old('name_parameter_jenis_klinik') }}" required>
        </div>

        <div class="form-group">
          <label for="code_parameter_jenis_klinik">Kode Parameter Jenis</label>

          <input type="text" class="form-control" id="code_parameter_jenis_klinik" name="code_parameter_jenis_klinik"
            placeholder="Kode parameter jenis klinik.."
            value="{{ $item->code_parameter_jenis_klinik ?? old('code_parameter_jenis_klinik') }}" required>
          </select>
        </div>

        <div class="form-group">
          <label for="id_parameter_jenis_klinik_parent">Parent Parameter Jenis</label>
          <small class="text-muted">(Kosongkan jika ini adalah parameter utama)</small>

          <select class="form-control" id="id_parameter_jenis_klinik_parent" name="id_parameter_jenis_klinik_parent">
            <option value="">-- Tidak Ada Parent (Parameter Utama) --</option>
            @if(isset($parentOptions))
              @foreach($parentOptions as $parent)
                <option value="{{ $parent->id_parameter_jenis_klinik }}" 
                  {{ ($item->id_parameter_jenis_klinik_parent == $parent->id_parameter_jenis_klinik || old('id_parameter_jenis_klinik_parent') == $parent->id_parameter_jenis_klinik) ? 'selected' : '' }}>
                  {{ $parent->name_parameter_jenis_klinik }}
                </option>
              @endforeach
            @endif
          </select>
        </div>

        <div class="form-group">
          <label for="sort_order">Urutan Tampilan</label>
          <small class="text-muted">(Urutan tampilan dalam parent yang sama)</small>

          <input type="number" class="form-control" id="sort_order" name="sort_order"
            placeholder="Urutan tampilan.." value="{{ $item->sort_order ?? old('sort_order', 0) }}">
        </div>

        <div class="form-group">
          <label for="sort_parameter_jenis_klinik">Urutan Parameter Jenis (Legacy)</label>

          <!-- Input Urutan Parameter jenis Klinik -->
          <input type="number" class="form-control" id="sort_parameter_jenis_klinik"
              name="sort_parameter_jenis_klinik" placeholder="Urutan parameter jenis klinik.."
              value="{{ $item->sort_parameter_jenis_klinik ?? old('sort_parameter_jenis_klinik') }}" required readonly>

          <!-- Tombol Pindah Urutan -->
          <button class="btn btn-primary mt-2" type="button" id="btn-show-dropdown">Pindahkan Urutan</button>

          <!-- Dropdown yang akan disembunyikan sampai tombol ditekan -->
          <div id="dropdown-move-order" style="display: none; margin-top: 10px;">
              <label for="after_sort_parameter_jenis_klinik">Pindahkan Setelah</label>
              <select name="after_sort_parameter_jenis_klinik" id="after_sort_parameter_jenis_klinik" class="selec2">
                  <option value="">-- Pilih Nomor Urut --</option>
                  @foreach($existing_sorts as $sort => $name)
                      {{-- @if($sort > 0) Jangan tampilkan jika urutan adalah 0 --}}
                          <option value="{{ $sort }}">
                              Setelah urutan {{ $sort }} - {{ $name }}
                          </option>
                      {{-- @endif --}}
                  @endforeach
              </select>
          </div>
        </div>

        <br>

      </form>
      <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
      <button type="button" onclick="document.location='{{ url('/elits-parameter-jenis-klinik') }}'"
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
                    document.location = '/elits-parameter-jenis-klinik';
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

    $(document).ready(function() {
        // Saat tombol "Pindahkan Setelah" ditekan, tampilkan dropdown
        $('#btn-show-dropdown').on('click', function() {
            $('#dropdown-move-order').toggle(); // Mengubah visibilitas dropdown
        });

          // Inisialisasi Select2 pada dropdown
        $('#after_sort_parameter_jenis_klinik').select2({
            placeholder: 'Pilih urutan setelah...',
            allowClear: true
        });
    });

    $(document).ready(function() {
        $('#after_sort_parameter_jenis_klinik').on('change', function() {
            const selectedSort = parseInt($(this).val());
            $('#sort_parameter_jenis_klinik').val(selectedSort + 1);
        });
    });
  </script>
@endsection
