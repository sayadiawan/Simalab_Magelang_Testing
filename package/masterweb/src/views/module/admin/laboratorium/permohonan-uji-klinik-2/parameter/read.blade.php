@extends('masterweb::template.admin.layout')
@section('title')
  Detail Permohonan Uji Klinik Parameter
@endsection


@section('content')



  <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/gijgo.min.js')}}" type="text/javascript"></script>
  <link href="{{asset('assets/admin/cdn-local/css/gijgo.min.css')}}" rel="stylesheet" type="text/css" />


  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a>
                </li>

                <li class="breadcrumb-item">
                  <a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan Uji Klinik
                    Management</a>
                </li>

                <li class="breadcrumb-item">
                  <a
                    href="{{ route('elits-permohonan-uji-klinik.permohonan-uji-klinik-parameter', $item->id_permohonan_uji_klinik) }}">Permohonan
                    Uji
                    Klinik Parameter
                    Management</a>
                </li>

                <li class="breadcrumb-item active" aria-current="page">
                  <span>detail</span>
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
      <h4>Detail Permohonan Uji Klinik Parameter</h4>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="col-md-12">
          <div class="mb-2 float-right">
            <a
              href="{{ route('elits-permohonan-uji-klinik.permohonan-uji-klinik-parameter', $item->id_permohonan_uji_klinik) }}">

              <button type="button" class="btn btn-default btn-icon-text">
                <i class="fa fa-arrow-left btn-icon-append"></i>
                Kembali
              </button>
            </a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-borderless">
              <tr>
                <th width="250px">No. Register</th>
                <td>{{ $item->noregister_permohonan_uji_klinik }}</td>

                <input type="hidden" name="permohonan_uji_klinik" id="permohonan_uji_klinik"
                  value="{{ $item->id_permohonan_uji_klinik }}" readonly>
              </tr>

              <tr>
                <th width="250px">No. Rekam Medis</th>
                <td>
                  {{ Carbon\Carbon::createFromFormat('Y-m-d', $item->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
                </td>
              </tr>

              <tr>
                <th width="250px">Tgl. Register</th>
                <td>{{ $tgl_register }}</td>
              </tr>

              <tr>
                <th width="250px">Nama Pasien</th>
                <td style="text-transform: uppercase;">{{ $item->pasien->nama_pasien }}</td>
              </tr>

              <tr>
                <th width="250px">Umur/Jenis Kelamin</th>
                <td>
                  {{ $item->umurtahun_pasien_permohonan_uji_klinik . ' tahun ' . $item->umurbulan_pasien_permohonan_uji_klinik . ' bulan ' . $item->umurhari_pasien_permohonan_uji_klinik . ' hari' }}
                  / {{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Detail Parameter</h5>

          <div class="row">
            <div class="col-12">
              <div class="table-responsive">
                <table class="table table-borderless">
                  <tr>
                    <th width="250px">Jenis Parameter</th>
                    <td>{{ $item_parameter_paket->parameterjenisklinik->name_parameter_jenis_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="250px">Tipe Paket</th>
                    <td>{{ $item_parameter_paket->type_permohonan_uji_paket_klinik == 'P' ? 'Paket' : 'Custom' }}</td>
                  </tr>

                  {{-- Cek dulu paket atau custom --}}
                  @if ($item_parameter_paket->type_permohonan_uji_paket_klinik == 'P')
                    <tr>
                      <th width="250px">Nama Paket</th>
                      <td>{{ $item_parameter_paket->parameterpaketklinik->name_parameter_paket_klinik }}</td>
                    </tr>

                    <tr>
                      <th width="250px" style="vertical-align: top">Detail Jenis Parameter Paket</th>
                      <td>
                        <ol class="list-group list-group-flush">
                          @foreach ($data_parameter_satuan as $item)
                            <li>
                              {{ $item->jenisparameterklinik->name_parameter_jenis_klinik . ' - ' . $item->jenisparameterklinik->code_parameter_jenis_klinik }}

                              <ul>
                                @php
                                  /* $data_parameter_satuan_paket = \Smt\Masterweb\Models\PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $item->permohonan_uji_klinik)
                                      ->where('permohonan_uji_paket_klinik', $item->permohonan_uji_paket_klinik)
                                      ->join('ms_parameter_paket_jenis_klinik', function ($q) use ($item) {
                                          $q->on('ms_parameter_paket_jenis_klinik.parameter_paket_klinik_id', 'tb_permohonan_uji_parameter_klinik.parameter_paket_klinik')
                                              ->orderBy('ms_parameter_paket_jenis_klinik.sort', 'asc')
                                              ->groupBy('tb_permohonan_uji_parameter_klinik.parameter_paket_jenis_klinik')
                                              ->join('ms_parameter_satuan_paket_klinik', function ($q) use ($item) {
                                                  $q->on('ms_parameter_satuan_paket_klinik.parameter_paket_jenis_klinik', 'ms_parameter_paket_jenis_klinik.id_parameter_paket_jenis_klinik')
                                                      ->where('ms_parameter_satuan_paket_klinik.parameter_paket_jenis_klinik', $item->parameter_paket_jenis_klinik)
                                                      ->where('ms_parameter_satuan_paket_klinik.parameter_satuan_klinik', $item->parameter_satuan_klinik)
                                                      ->orderBy('ms_parameter_satuan_paket_klinik.sorting', 'asc')
                                                      ->join('ms_parameter_satuan_klinik', function ($q) {
                                                          $q->on('ms_parameter_satuan_klinik.id_parameter_satuan_klinik', 'ms_parameter_satuan_paket_klinik.parameter_satuan_klinik');
                                                      });
                                              });
                                      })
                                      ->select('tb_permohonan_uji_parameter_klinik.harga_permohonan_uji_parameter_klinik', 'ms_parameter_satuan_klinik.name_parameter_satuan_klinik')
                                      ->orderBy('sorting_permohonan_uji_parameter_klinik', 'asc')
                                      ->get(); */
                                  
                                  $data_parameter_satuan_paket = \Smt\Masterweb\Models\PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $item->permohonan_uji_klinik)
                                      ->where('permohonan_uji_paket_klinik', $item->permohonan_uji_paket_klinik)
                                      ->orderBy('sorting_permohonan_uji_parameter_klinik', 'asc')
                                      ->get();
                                @endphp

                                @foreach ($data_parameter_satuan_paket as $value)
                                  <li>{{ $value->parametersatuanklinik->name_parameter_satuan_klinik }} -
                                    {{ $value->harga_permohonan_uji_parameter_klinik ? rupiah($value->harga_permohonan_uji_parameter_klinik) : 'Rp. 0' }}
                                  </li>
                                @endforeach
                              </ul>
                            </li>
                          @endforeach
                        </ol>
                      </td>
                    </tr>

                    <tr>
                      <th width="250px">Harga Total</th>
                      <td>
                        {{ $item_parameter_paket->harga_permohonan_uji_paket_klinik ? rupiah($item_parameter_paket->harga_permohonan_uji_paket_klinik) : 'Rp. 0' }}
                      </td>
                    </tr>
                  @endif

                  @if ($item_parameter_paket->type_permohonan_uji_paket_klinik == 'C')
                    <tr>
                      <th width="250px">Detail Parameter</th>
                      <td>
                        <ol class="list-group list-group-flush">
                          @foreach ($data_parameter_satuan as $item)
                            <li class="list-group-item">{{ $item->parametersatuanklinik->name_parameter_satuan_klinik }}
                              -
                              {{ $item->harga_permohonan_uji_parameter_klinik ? rupiah($item->harga_permohonan_uji_parameter_klinik) : 'Rp. 0' }}
                            </li>
                          @endforeach
                        </ol>
                      </td>
                    </tr>

                    <tr>
                      <th width="250px">Harga Total</th>
                      <td>
                        {{ $item_parameter_paket->harga_permohonan_uji_paket_klinik ? rupiah($item_parameter_paket->harga_permohonan_uji_paket_klinik) : 'Rp. 0' }}
                      </td>
                    </tr>
                  @endif
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endsection
