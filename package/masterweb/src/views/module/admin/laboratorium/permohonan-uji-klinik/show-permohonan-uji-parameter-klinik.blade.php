@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Klinik
@endsection


@section('content')





  <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/gijgo.min.js')}}" type="text/javascript"></script>
  <link href="{{asset('assets/admin/cdn-local/css/gijgo.min.css')}}" rel="stylesheet" type="text/css" />
  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCvkw3POs-1ZAeDNh83LazabKECKU8i024&libraries=places"></script>


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
                <li class="breadcrumb-item active" aria-current="page"><span>detail permohonan uji paket klinik</span>
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-header">
          <h4>Detail Permohonan Uji Paket Klinik</h4>
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table">
              <tr>
                <th width="250px">No. Register</th>
                <td>{{ $item->noregister_permohonan_uji_klinik }}</td>
              </tr>

              <tr>
                <th width="250px">No. Rekam Medis</th>
                <td>{{ $item->nopasien_permohonan_uji_klinik }}</td>
              </tr>

              <tr>
                <th width="250px">Tgl. Register</th>
                <td>{{ $tgl_register }}</td>
              </tr>

              <tr>
                <th width="250px">Nama Pasien</th>
                <td>{{ $item->namapasien_permohonan_uji_klinik }}</td>
              </tr>

              <tr>
                <th width="250px">Umur/Jenis Kelamin</th>
                <td>
                  {{ $item->umurtahun_pasien_permohonan_uji_klinik . ' tahun ' . $item->umurbulan_pasien_permohonan_uji_klinik . ' bulan ' . $item->umurhari_pasien_permohonan_uji_klinik . ' hari' }}
                  / {{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
              </tr>
            </table>

            <table id="table-parameter" class="table">
              <thead>
                <tr>
                  <th style="width: 5%">No</th>
                  <th style="width: 15%">Jenis Parameter</th>
                  <th style="width: 15%">Paket</th>
                  <th style="width: 25%">Parameter</th>
                  <th style="width: 20%">Harga</th>
                </tr>
              </thead>

              <tbody>
                @if ($data_detail_uji_paket)
                  @php
                    $x = 1;
                  @endphp

                  @foreach ($data_detail_uji_paket as $key => $value)
                    <tr id="row_{{ $x }}" class="tr_row">
                      <td style="width: 5%">
                        <h5>{{ $x }}</h5>
                      </td>

                      <td style="width: 15%">
                        {{ $value->parameterjenisklinik->name_parameter_jenis_klinik }}
                      </td>

                      <td style="width: 15%">
                        {!! $value->type_permohonan_uji_paket_klinik == 'P' ? 'Paket' : 'Custom' !!}
                      </td>

                      <td style="width: 35%">
                        @if ($value->type_permohonan_uji_paket_klinik == 'P')
                          {{ $value->parameterpaketklinik->name_parameter_paket_klinik }}
                        @elseif ($value->type_permohonan_uji_paket_klinik == 'C')
                          @php
                            $item_custom_satuan_parameter = Smt\Masterweb\Models\PermohonanUjiParameterKlinik::where('permohonan_uji_paket_klinik', $value->id_permohonan_uji_paket_klinik)
                                ->where('permohonan_uji_klinik', $item->id_permohonan_uji_klinik)
                                ->get();

                            $prefix = $sampleListCustomSatuan = '';

                            foreach ($item_custom_satuan_parameter as $mytable) {
                                $sampleListCustomSatuan .= $prefix . $mytable->parametersatuanklinik->name_parameter_satuan_klinik;
                                $prefix = ', ';
                            }

                            echo $sampleListCustomSatuan;
                          @endphp
                        @else
                          -
                        @endif

                      </td>

                      <td style="width: 15%">
                        {{ rupiah($value->harga_permohonan_uji_paket_klinik) }}
                      </td>
                    </tr>

                    @php
                      $x++;
                    @endphp
                  @endforeach
                @else
                  <tr id="row_1" class="tr_row">
                    <td colspan="5">
                      Tidak ada data parameter yang tersimpan.
                    </td>
                  </tr>
                @endif

              </tbody>

              <tr>
                <th style="width: 250px" colspan="4" class="text-right">Total Harga</th>
                <td>
                  {{ rupiah($item->total_harga_permohonan_uji_klinik) }}
                </td>
              </tr>
            </table>
          </div>

          <button type="button" class="btn btn-light"
            onclick="document.location='{{ url('/elits-permohonan-uji-klinik') }}'">Kembali</button>
        </div>
      </div>
    </div>
  </div>
@endsection
