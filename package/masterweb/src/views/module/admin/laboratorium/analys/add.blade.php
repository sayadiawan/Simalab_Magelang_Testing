@extends('masterweb::template.admin.layout')
@section('title')
  Analisys Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-samples') }}">Analisys Management</a></li>
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
      <!-- <form enctype="multipart/form-data" class="forms-sample" action="{{ route('elits-samples.store') }}"  method="POST"> -->
      @csrf
      <div class="form-group">
        <label for="code_samples">Kode</label>
        <input type="text" class="form-control" id="code_samples" name="code_samples" value='{{ $code }}'
          placeholder="Isikan Kode" readonly>
      </div>

      <div class="form-group">
        <label for="customer_samples">Customer</label>
        <select id="customerAttributes" class="js-customer-basic-multiple js-states form-control" style="width: 100%"
          required>

        </select>
      </div>

      <div class="form-group">
        <label for="name_customer">Method</label>

        <select id="methodAttributes" class="form-control js-example-basic-multiple" style="width: 100%; display:none"
          multiple="multiple" required>
        </select>
      </div>


      <div class="form-group">
        <div class="form-row align-items-center col-md-12" id="fields-relay-1">
          <div class="col-md-2" id="condition">
            <input type="checkbox" id="issend_samples" class="issend_samples" name="issend_samples" data-toggle="toggle">
            DIANTAR

          </div>
          <div class="col-md-10" id="name_sampling_field">
            <label id="label-sender_samples" for="sender_samples">Petugas Sampling</label>
            <input type="text" class="form-control" id="sender_samples" name="sender_samples"
              placeholder="Isikan Petugas Sampling">
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="poinsample_samples">Titik Sampling</label>
        <input type="text" class="form-control" id="poinsample_samples" name="poinsample_samples"
          placeholder="Isikan Titik Sampling" required>
      </div>

      <div class="form-group">
        <label for="name_customer">Tanggal Sampling</label>
        <div class="input-group date">
          <input type="text" class="form-control datepicker" name="datesampling_samples" id="datesampling_samples"
            placeholder="Isikan Tanggal Sampling" data-date-format="dd/mm/yyyy" required>
          <div class="input-group-append">
            <span class="input-group-text">
              <i class="fas fa-calendar-alt"></i>
            </span>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="typesample_samples">Jenis Sampling</label>
        <input type="text" class="form-control" id="typesample_samples" name="typesample_samples"
          placeholder="Isikan Jenis Sampling" required>
      </div>

      <div class="form-group">
        <label for="wadah_samples">Wadah</label>
        <input type="text" class="form-control" id="wadah_samples" name="wadah_samples" placeholder="Isikan Wadah"
          required>
      </div>


      <div class="form-group">
        <div class="form-row align-items-center col-md-12" id="fields-relay-1">
          <div class="col-md-6" id="condition">
            <label for="amount_samples">Berat/Volume</label>
            <input type="number" class="form-control" id="amount_samples" name="amount_samples"
              placeholder="Isikan Berat/Volume" required>
          </div>
          <div class="col-md-6" id="name_sampling_field">
            <br>
            <select id="unitAttributes" class="js-unit-basic-multiple js-states form-control" style="width: 100%">

            </select>

          </div>
        </div>
      </div>


      <div class="form-group">
        <label for="datelab_samples">Tanggal Masuk Lab</label>
        <div class="input-group date">
          <input type="text" class="form-control datelab_samples" name="datelab_samples" id="datelab_samples"
            placeholder="Isikan Tanggal Masuk Lab" data-date-format="dd/mm/yyyy" required>
          <div class="input-group-append">
            <span class="input-group-text">
              <i class="fas fa-calendar-alt"></i>
            </span>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="cost_samples">Harga</label>
        <div class="input-group">
          <div class="input-group-append">
            <span class="input-group-text">
              Rp.
            </span>
          </div>
          <input type="number" class="form-control" id="cost_samples" name="cost_samples" placeholder="Isikan Harga"
            required>
        </div>
      </div>





      <!-- <div class="input-group date">
                      <div class="input-group date" data-provide="datepicker">
                      <input class="datepicker" class="form-control" data-date-format="mm/dd/yyyy">
                      <div class="input-group-addon">
                          <span class="glyphicon glyphicon-th"></span>
                      </div>
                  </div> -->




      <button type="submit" class="btn btn-primary mr-2" id="submit">Simpan</button>
      <button onclick="goBack()" class="btn btn-light">Kembali</button>
      <!-- </form> -->
    </div>
  </div>


  <script>
    $('#methodAttributes').select2({
    allowClear: true,
    minimumResultsForSearch: -1,
    width: 600
    });
    });
  </script>
@endsection

@section('scripts')
  <script>
    function goBack() {
      window.history.back();
    }



    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.datepicker').datepicker('update', new Date());

    $('.datelab_samples').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.datelab_samples').datepicker('update', new Date());



    $("#submit").click(function() {
      var methodAttributes = $("#methodAttributes").val()
      var customerAttributes = $("#customerAttributes").val()
      var code_samples = $("#code_samples").val()
      var datelab_samples = $("#datelab_samples").val()
      var datesampling_samples = $("#datesampling_samples").val()
      var poinsample_samples = $("#poinsample_samples").val()
      var cost_samples = $("#cost_samples").val()
      var wadah_samples = $("#wadah_samples").val()
      var unitAttributes = $("#unitAttributes").val()
      var typesample_samples = $("#typesample_samples").val()
      var issend_samples = $("#issend_samples").val()
      var sender_samples = $("#sender_samples").val()
      var amount_samples = $("#amount_samples").val()

      let _token = "{{ csrf_token() }}"

      $.ajax({
        url: "{{ route('elits-samples.store') }}",
        type: "POST",
        data: {
          methodAttributes: methodAttributes,
          customerAttributes: customerAttributes,
          code_samples: code_samples,
          datelab_samples: datelab_samples,
          datesampling_samples: datesampling_samples,
          poinsample_samples: poinsample_samples,
          cost_samples: cost_samples,
          wadah_samples: wadah_samples,
          unitAttributes: unitAttributes,
          typesample_samples: typesample_samples,
          issend_samples: issend_samples,
          sender_samples: sender_samples,
          amount_samples: amount_samples,
          _token: _token
        },
        success: function(response) {

          var url = "{{ route('elits-samples.index') }}";
          window.location.href = url;

        },
      });


    });


    $('#issend_samples').on('change', function() {
      if ($('input[name=issend_samples]:checked').val()) {
        $('#sender_samples').hide();
        $('#label-sender_samples').hide();
      } else {
        $('#sender_samples').show();
        $('#label-sender_samples').show();
      }
    });


    $(document).ready(function() {

      $.fn.select2.defaults.set("theme", "classic");




      // var selectedValues = ["5c8b7dd9-ff36-4291-ae41-425e637ebe6d","89e359c4-a874-43d1-bc7d-aba263c63ecb","aa5eba43-7038-4f60-820b-c891a550af8b"]

      // $(".js-example-basic-multiple").select2().val(selectedValues).trigger('change')


      $('.js-customer-basic-multiple').select2({
        placeholder: "Pilih Customer",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/customer/') }}",
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



      $('.js-unit-basic-multiple').select2({
        placeholder: "Pilih Unit",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/unit/') }}",
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




      $('.js-example-basic-multiple').select2({
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
