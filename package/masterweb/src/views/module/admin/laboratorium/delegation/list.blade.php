@extends('masterweb::template.admin.layout')

@section('title')
  Delegation Management
@endsection


@section('content')
  <style>
    .right {
      float: right;
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-samples') }}">Laboraturium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Delegation Management</span></li>
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

      </div>

      <div class="row">
        @if (session('status_delegation'))
          @php
            alert()->success('Berhasil', session('status_delegation'));
          @endphp
        @endif
        <div class="col-12">
          <div class="row">
            <div class="col-md-3">
              <label for="codesample_samples">Kode Sample</label>
            </div>
            <div class="col-md-0">
              <label for="codesample_samples">:</label>
            </div>
            <div class="col-md-3">
              <label for="codesample_samples">{{ $samples->codesample_samples }}</label>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3">
              <label for="blanko_titration1">Customer</label>
            </div>
            <div class="col-md-0">
              <label for="blanko_titration1">:</label>
            </div>
            <div class="col-md-3">
              <label for="blanko_titration1" style="align:left">{{ $samples->name_customer }}</label>
            </div>
          </div>



          <br>

          <br>
          @php
            $no = 1;
          @endphp
          <div class="table-responsive">
            <form enctype="multipart/form-data" id="forms-delegation">
              @csrf
              <table class="table table-bordered ">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Method</th>
                    <th>Analis</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($method as $mymethod)
                    <tr>
                      <td>{{ $no }}</td>
                      <td>{{ $mymethod->params_method }}</td>
                      <td>
                        <div class="form-group">
                          <select name="office_{{ $mymethod->id_method }}" id="office_{{ $mymethod->id_method }}"
                            class=" js-lab-officer-basic-multiple js-states form-control" required>
                            @foreach ($delegation as $mydelegation)
                              @if ($mydelegation->method_id == $mymethod->id_method)
                                <option value="{{ $mydelegation->id_user }}" selected>{{ $mydelegation->user_name }}
                                </option>
                              @endif
                            @endforeach
                          </select>
                        </div>
                      </td>
                    </tr>
                    @php
                      $no++;
                    @endphp
                  @endforeach
                </tbody>
              </table>
              <br>
              <br>
              <div class="right">
                <button type="submit" class="btn btn-primary mr-2" id="start">Mulai Analisa</button>

              </div>
            </form>
            <div class="right">
              <button class="btn btn-primary mr-2" id="save">Simpan</button>
            </div>
            <button class="btn btn-light" onclick="goBack()">Kembali</button>

          </div>
        </div>
      </div>


    </div>
  </div>
@endsection

@section('scripts')
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <script>
    $(document).on('submit', '[id^=forms-delegation]', function(e) {
      console.log("woy")

      var data = $(this).serialize();
      Swal.fire({
        title: "Apakah Proses Analisa dimulai?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Mulai!",
        cancelButtonText: "Batal",
        closeOnCancel: true
      }).then(function(value) {
        if (value.value) {
          var method = @json($method, JSON_PRETTY_PRINT);

          var data = {}

          method.forEach(method => {
            if ($("#office_" + method.id_method).val() != undefined && $("#office_" + method.id_method)
              .val() != '') {
              data["office_" + method.id_method] = $("#office_" + method.id_method).val();
            }

          })

          data["_token"] = "{{ csrf_token() }}";

          $.ajax({
            /* the route pointing to the post function */
            url: "{{ route('elits-deligations.start', ['id' => Request::segment(2)]) }}",
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: data,
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function(data) {
              window.location.href = '/elits-samples'
              Swal.fire({
                title: 'Berhasil!',
                text: data.success,
                icon: 'success',
                confirmButtonText: 'Ok'
              })

            }
          });

        } else if (value.dismiss === Swal.DismissReason.cancel) {

        }
      });

      return false;
    });


    $(document).ready(function() {


      $.fn.select2.defaults.set("theme", "classic");

      $('.js-lab-officer-basic-multiple').select2({
        placeholder: "Pilih Lab Officer",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/lab-officer/') }}",
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



      $("#save").click(function() {


        var method = @json($method, JSON_PRETTY_PRINT);

        var data = {}

        method.forEach(method => {
          if ($("#office_" + method.id_method).val() != undefined && $("#office_" + method.id_method).val() !=
            '') {
            data["office_" + method.id_method] = $("#office_" + method.id_method).val();
          }

        })

        data["_token"] = "{{ csrf_token() }}";

        $.ajax({
          /* the route pointing to the post function */
          url: "{{ route('elits-deligations.save', ['id' => Request::segment(2)]) }}",
          type: 'POST',
          /* send the csrf-token and the input to the controller */
          data: data,
          dataType: 'JSON',
          /* remind that 'data' is the response of the AjaxController */
          success: function(data) {

            Swal.fire({
              title: 'Berhasil!',
              text: data.success,
              icon: 'success',
              confirmButtonText: 'Ok'
            })
          }
        });
      });




    })

    function goBack() {
      window.history.back();
    }
  </script>

  @include('sweetalert::alert')
@endsection
