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
                <td>{{ $item->pasien->nama_pasien }}</td>
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
                      <th width="250px">Detail Parameter</th>
                      <td>
                        @php
                          $prefix = $parameterList = '';
                          
                          foreach ($arr_parameter_satuan as $mytable) {
                              $parameterList .= $prefix . $mytable['nama_parameter_satuan'];
                              $prefix = ', ';
                          }
                          
                          echo $parameterList;
                        @endphp
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
                        {{-- @foreach ($arr_parameter_satuan as $key => $item)
                          @php
                            $data_item = $key;
                          @endphp
                        @endforeach

                        @php

                          for ($i = 0; $i <= $data_item; $i++) {
                              echo $arr_parameter_satuan[$i]['nama_parameter_satuan'] . ' - ' . rupiah($arr_parameter_satuan[$i]['harga_parameter_satuan']) . '<br>';
                          }
                        @endphp --}}

                        <ol class="list-group list-group-flush">
                          @foreach ($arr_parameter_satuan as $item)
                            <li class="list-group-item">{{ $item['nama_parameter_satuan'] }} -
                              {{ $item['harga_parameter_satuan'] ? rupiah($item['harga_parameter_satuan']) : 'Rp. 0' }}
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
