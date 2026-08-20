@extends('masterweb::template.admin.layout')
@section('title')
  Parameter Satuan Klinik
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-parameter-satuan-klinik') }}">Parameter Satuan
                    Klinik</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Detail</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="card">
    <div class="card-body">
      <div class="table-responseve">
        <table class="table table-stripped">
          <tr>
            <th width="250px">Parameter Jenis</th>
            <td>{{ $item->parameterjenisklinik->name_parameter_jenis_klinik }}</td>
          </tr>

          <tr>
            <th width="250px">Nama Parameter Satuan</th>
            <td>{{ $item->name_parameter_satuan_klinik }}</td>
          </tr>

          <tr>
            <th width="250px">Metode Parameter Satuan</th>
            <td>{{ $item->metode_parameter_satuan_klinik }}</td>
          </tr>

          @if (($item->is_haji ?? 0) == 1)
            <tr>
              <th width="250px">Metode Parameter Satuan (Haji)</th>
              <td>{{ $item->metode_parameter_satuan_klinik_haji ?: '-' }}</td>
            </tr>
          @endif

          <tr>
            <th width="250px">Loinc Parameter Satuan</th>
            <td>{{ $item->loinc_parameter_satuan_klinik }}</td>
          </tr>

          @if (($item->is_haji ?? 0) == 1)
            <tr>
              <th width="250px">Loinc Parameter Satuan (Haji)</th>
              <td>{{ $item->loinc_parameter_satuan_klinik_haji ?: '-' }}</td>
            </tr>
          @endif

          @if ($data_subitem)
            <tr>
              <th width="250px">Sub Parameter Satuan</th>

              @php
                $prefix = $subParameterList = '';

                foreach ($data_subitem as $key_ds => $value_ds) {
                    $subParameterList .= $prefix . $value_ds->name_parameter_sub_satuan_klinik;
                    $prefix = ', ';
                }

                echo '<td>' . $subParameterList . '</td>';
              @endphp
            </tr>
          @endif

          <tr>
            <th width="250px">Harga Parameter Satuan</th>
            <td>{{ rupiah($item->harga_satuan_parameter_satuan_klinik) }}</td>
          </tr>

          <tr>
            <th width="250px">Ket. Default Parameter Satuan</th>
            <td>{!! nilaiBakuMutuForDisplay($item->ket_default_parameter_satuan_klinik ?? '') !!}</td>
          </tr>

        </table>

        <br>
        <button type="button" onclick="document.location='{{ url('/elits-parameter-satuan-klinik') }}'"
          class="btn btn-light">Kembali</button>
      </div>

    </div>
  </div>
@endsection
