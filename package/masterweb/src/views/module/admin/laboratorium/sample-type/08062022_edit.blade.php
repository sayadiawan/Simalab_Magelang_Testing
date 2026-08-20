@extends('masterweb::template.admin.layout')
@section('title')
  Sample Type Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-sampletypes') }}">Sample Type Management</a></li>
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

      <form enctype="multipart/form-data" class="forms-sample" action="{{ route('elits-sampletypes.update', [$id]) }}"
        method="POST">
        @csrf
        <input type="hidden" value="PUT" name="_method">

        <div class="form-group">
          <label for="name_sampletype">Nama Jenis Sarana</label>
          <input type="text" class="form-control" id="name_sampletype" name="name_sampletype"
            value="{{ $sampletype->name_sample_type }}" placeholder="Name Sample Type" required>
        </div>
        <!-- <div class="form-group">
                      <label for="code_sampletype">Kode Jenis Saran</label>
                      <input type="text" class="form-control" id="code_sampletype" name="code_sampletype" value="{{ $sampletype->code_sample_type }}" placeholder="Code Sample Type" required >
                  </div> -->

        <div class="form-group">
          <label for="code_sampletype">Parameter Wajib</label>
          <select id="methodAttributes" name="methodAttributes[]" class="js-example-basic-multiple" style="display:none"
            multiple="multiple">
            @foreach ($sampletype_details as $sampletype_detail)
              <option value="{{ $sampletype_detail->id_method }}" selected>{{ $sampletype_detail->params_method }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="code_sampletype">Parameter Tambahan</label>
          <select id="methodPlusAttributes" name="methodPlusAttributes[]" class="js-example-basic-multiple"
            style="display:none" multiple="multiple">
            @foreach ($sampletype_plus_details as $sampletype_plus_detail)
              <option value="{{ $sampletype_plus_detail->id_method }}" selected>
                {{ $sampletype_plus_detail->params_method }}</option>
            @endforeach
          </select>
        </div>

        {{-- <div class="form-group">
                <label for="cost_samples">Harga</label>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">
                        Rp.
                        </span>
                    </div>
                    <input type="number" class="form-control" id="price_sample_type" name="price_sample_type"  value="{{$sampletype->price_sample_type}}" type="number"  placeholder="Isikan Harga" required>

                </div>
            </div> --}}





        <button type="submit" class="btn btn-primary mr-2">Simpan</button>
        <button onclick="goBack()" class="btn btn-light">Kembali</button>
      </form>
    </div>
  </div>

  <script>
    function goBack() {
      window.history.back();
    }


    $(document).ready(function() {

      $.fn.select2.defaults.set("theme", "classic");
      $('#methodPlusAttributes').select2({
        placeholder: "Pilih Metode",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/method/') }}",
          method: "post",
          dataType: 'json',

          params: { // extra parameters that will be passed to ajax
            contentType: "application/json;",
          },
          data: function(term) {
            return {
              term: term.term || '',
              page: term.page || 1
            };
          },
          cache: true
        }
      });

      $('#methodAttributes').select2({
        placeholder: "Pilih Metode",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/method/') }}",
          method: "post",
          dataType: 'json',

          params: { // extra parameters that will be passed to ajax
            contentType: "application/json;",
          },
          data: function(term) {
            return {
              term: term.term || '',
              page: term.page || 1
            };
          },
          cache: true
        }
      });



    });
  </script>
@endsection
