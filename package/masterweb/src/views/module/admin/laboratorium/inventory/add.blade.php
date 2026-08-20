@extends('masterweb::template.admin.layout')
@section('title')
  Inventory Management
@endsection

@section('content')
  <link href="{{asset('assets/admin/cdn-local/css/select2.min.css')}}" rel="stylesheet" />







  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-inventories') }}">Inventory Management</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>create</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="card">
    <div class="card-body">
      <form enctype="multipart/form-data" class="forms-sample" action="{{ route('elits-inventories.store') }}"
        method="POST">
        @csrf
        <div class="form-group">
          <label for="params_method">Product</label>
          <select id="productAttributes" name="productAttributes" class="js-product-basic-multiple js-states form-control"
            required style="width: 100%">

          </select>
        </div>

        <div class="form-group">
          <div class="form-row align-items-center col-md-12" id="fields-relay-1">
            <div class="col-md-6" id="condition">
              <input type="radio" id="is_send" class="is_send" name="type_stock" value="tambah" data-toggle="toggle">
              Tambah

            </div>
            <div class="col-md-6" id="condition">
              <input type="radio" id="is_send" class="is_send" name="type_stock" value="dipakai" data-toggle="toggle">
              Dipakai

            </div>

          </div>
        </div>



        <div class="form-group">
          <label for="qty_stock">Jumlah</label>
          <input type="number" class="form-control" id="qty_stock" name="qty_stock" placeholder="Maukkan Jumlah">
        </div>


        <button type="submit" class="btn btn-primary mr-2">Simpan</button>
        <button onclick="goBack()" class="btn btn-light">Kembali</button>
      </form>
    </div>
  </div>


  <script>
    function goBack() {
      window.history.back();
    }


    // $('#dynamicAttributes').select2({
    //       allowClear: true,
    //       minimumResultsForSearch: -1,
    //       width: 600
    //     });
    // });

    $(document).ready(function() {

      $.fn.select2.defaults.set("theme", "classic");
      $('.js-product-basic-multiple').select2({
        placeholder: "Pilih Product",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/products/') }}",
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
    })
  </script>
@endsection
