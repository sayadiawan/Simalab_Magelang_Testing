@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Klinik
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan Uji Klinik
                    Management</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>detail</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row align-items-center mb-4">
        <div class="col">
          <h3 class="page-title">
            Detail Permohonan Uji Klinik Management
          </h3>
        </div>
        <!-- Page title actions -->
        <div class="col-auto ms-auto d-print-none">
          <div class="d-flex">
            <div class="template-demo">

              <a href="/elits-permohonan-uji-klinik">
                <button type="button" class="btn btn-secondary btn-icon-text">
                  <i class="fa fa-chevron-left btn-icon-prepend"></i>
                  Kembali
                </button>
              </a>
            </div>
          </div>
        </div>
      </div>

      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="permohonan-uji-klinik-tab" data-toggle="tab" href="#permohonan-uji-klinik-1"
            role="tab" aria-controls="permohonan-uji-klinik-1" aria-selected="false">Permohonan Uji Klinik</a>
        </li>

        @if (count($data_permohonan_uji_paket_klinik) > 0)
          <li class="nav-item">
            <a class="nav-link" id="data-parameter-tab" data-toggle="tab" href="#data-parameter-1" role="tab"
              aria-controls="data-parameter-1" aria-selected="true">Data Parameter</a>
          </li>
        @endif

        @if (
            $item->tglpengujian_permohonan_uji_klinik !== null ||
                $item->spesimen_darah_permohonan_uji_klinik !== null ||
                $item->spesimen_urine_permohonan_uji_klinik !== null)
          <li class="nav-item">
            <a class="nav-link" id="data-analis-tab" data-toggle="tab" href="#data-analis-1" role="tab"
              aria-controls="data-analis-1" aria-selected="false">Data Analis</a>
          </li>
        @endif
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade active show" id="permohonan-uji-klinik-1" role="tabpanel"
          aria-labelledby="permohonan-uji-klinik-tab">
          <table class="table table-stripped">
            <tr>
              <th width="250px">No. REGISTER</th>
              <td>{{ $item->noregister_permohonan_uji_klinik }}</td>
            </tr>

            <tr>
              <th width="250px">No. REKAM MEDIS</th>
              <td>
                {{ Carbon\Carbon::createFromFormat('Y-m-d', $item->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
              </td>
            </tr>

            <tr>
              <th width="250px">TGL.REGISTER</th>
              <td>{{ $tgl_register }}</td>
            </tr>

            <tr>
              <th width="250px">NIK PASIEN</th>
              <td>{{ $item->pasien->nik_pasien }}</td>
            </tr>

            <tr>
              <th width="250px">NAMA PASIEN</th>
              <td>{{ $item->pasien->nama_pasien }}</td>
            </tr>

            <tr>
              <th width="250px">JENIS KELAMIN</th>
              <td>{{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>

            <tr>
              <th width="250px">TANGGAL LAHIR</th>
              <td>
                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $item->pasien->tgllahir_pasien)->isoFormat('dddd, D MMMM Y') ?? '-' }}
              </td>
            </tr>

            <tr>
              <th width="250px">UMUR (Tahun)</th>
              <td>{{ $item->umurtahun_pasien_permohonan_uji_klinik }}</td>
            </tr>

            <tr>
              <th width="250px">UMUR (Bulan)</th>
              <td>{{ $item->umurbulan_pasien_permohonan_uji_klinik }}</td>
            </tr>

            <tr>
              <th width="250px">UMUR (Hari)</th>
              <td>{{ $item->umurhari_pasien_permohonan_uji_klinik }}</td>
            </tr>

            <tr>
              <th width="250px">ALAMAT</th>
              <td>{{ $item->pasien->alamat_pasien }}</td>
            </tr>

            <tr>
              <th width="250px">NO TELP/HP</th>
              <td>{{ $item->pasien->phone_pasien }}</td>
            </tr>

            <tr>
              <th width="250px">TANGGAL PENGAMBILAN</th>
              <td>{{ $tgl_pengambilan }}</td>
            </tr>

            <tr>
              <th width="250px">NAMA PENGIRIM</th>
              <td>{{ $item->namapengirim_permohonan_uji_klinik }}</td>
            </tr>

            <tr>
              <th width="250px">DIAGNOSA</th>
              <td>{{ $item->diagnosa_permohonan_uji_klinik }}</td>
            </tr>
          </table>
        </div>

        @if (count($data_permohonan_uji_paket_klinik) > 0)
          <div class="tab-pane fade" id="data-parameter-1" role="tabpanel" aria-labelledby="data-parameter-tab">
            {{-- DETAIL PARAMETER --}}
            <div class="table-responsive">
              <table class="table table-stripped">
                <thead>
                  <tr>
                    <th style="width: 35%">PAKET/CUSTOM</th>
                    <th style="width: 15%">JUMLAH PARAMETER</th>
                    <th style="width: 15%">HARGA SATUAN</th>
                    <th style="width: 25%">JUMLAH</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach ($value_items as $key => $value)
                    {{-- KONDISI MENCARI PAKET --}}
                    <tr>
                      <td width="35%">

                        {{ $value['name_item'] }}
                      </td>

                      <td width="15%">

                        {{ $value['count_item'] }}
                      </td>

                      <td width="15%">
                        {{ rupiah($value['price_item']) }}
                      </td>

                      <td width="25%">
                        {{ rupiah($value['total']) }}
                      </td>
                    </tr>
                  @endforeach

                  <tr>
                    <th colspan="3" style="text-align: right">TOTAL</th>
                    <td>Rp. {{ number_format($item->total_harga_permohonan_uji_klinik, 2, ',', '.') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        @endif

        @if (
            $item->tglpengujian_permohonan_uji_klinik !== null ||
                $item->spesimen_darah_permohonan_uji_klinik !== null ||
                $item->spesimen_urine_permohonan_uji_klinik !== null)
          <div class="tab-pane fade" id="data-analis-1" role="tabpanel" aria-labelledby="data-analis-tab">
            <div class="table-responsive">
              <table class="table table-borderless">
                <thead>
                  <tr>
                    <th width="400px">NRP</th>
                    <td>{{ $item->nrp_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="400px">DIVISI/DEPT</th>
                    <td>{{ $item->div_dept_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="400px">DOKTER</th>
                    <td>{{ $item->dokter->name }}</td>
                  </tr>

                  <tr>
                    <th width="250px">ANALIS</th>
                    <td>{{ $item->analis->name != null ? $item->analis->name : '-' }}

                      @if ($item->analis->nip_users)
                        - <strong>NIP {{ $item->analis->nip_users }}</strong>
                      @endif
                    </td>
                  </tr>

                  <tr>
                    <th width="400px">TANGGAL PENGUJIAN</th>
                    <td>{{ $tgl_pengujian }}</td>
                  </tr>

                  <tr>
                    <th width="400px">WAKTU PENGAMBILAN SPESIMEN DARAH</th>
                    <td>{{ $tgl_spesimen_darah }}</td>
                  </tr>

                  <tr>
                    <th width="250px">WAKTU PENGAMBILAN SPESIMEN URINE</th>
                    <td>{{ $tgl_spesimen_urine }}</td>
                  </tr>

                  @if (isset($item->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik))
                    <tr>
                      <th width="250px" class="align-top">KESIMPULAN</th>
                      <td class="align-top">
                        {!! $item->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik !!}
                      </td>
                    </tr>
                  @endif

                  @if (isset($item->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik))
                    <tr>
                      <th width="250px" class="align-top">KATEGORI</th>
                      <td class="align-top">
                        {!! $item->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik !!}
                      </td>
                    </tr>
                  @endif

                  @if (isset($item->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik))
                    <tr>
                      <th width="250px" class="align-top">SARAN</th>
                      <td class="align-top">
                        {!! $item->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik !!}
                      </td>
                    </tr>
                  @endif
                </thead>
              </table>

              <table class="table table-striped">
                <thead>
                  <tr>
                    <th style="width: 20%">NAMA TEST</th>
                    <th style="width: 15%">HASIL</th>
                    <th style="width: 15%">FLAG</th>
                    <th style="width: 15%">SATUAN</th>
                    <th style="width: 15%">NILAI RUJUKAN</th>
                    <th style="width: 20%">KETERANGAN</th>
                  </tr>
                </thead>

                <tbody>
                  @php
                    $no = 0;
                  @endphp
                  @foreach ($item->permohonanujiparameterklinik as $key_pupk => $item_pupk)
                    @if (count($item_pupk->parametersatuanklinik->parametersubsatuanklinik) < 1)
                      <tr>
                        <th style="width: 20%">
                          {{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}
                        </th>

                        <td style="width: 15%">
                          {{ $item_pupk->hasil_permohonan_uji_parameter_klinik ?? old('hasil_permohonan_uji_parameter_klinik') }}
                        </td>

                        <td style="width: 15%">
                          <p id="flag_permohonan_uji_parameter_klinik_text_{{ $no }}">
                            {!! $item_pupk->flag_permohonan_uji_parameter_klinik ?? '-' !!}</p>
                        </td>

                        <td style="width: 15%">
                          {{ $item_pupk->unit->name_unit }}
                        </td>

                        <td style="width: 15%">
                          {{ ($item_pupk->baku_mutu_permohonan_uji_parameter_klinik != null ? $item_pupk->baku_mutu_permohonan_uji_parameter_klinik : $item_pupk->bakumutu->nilai_baku_mutu != null) ? $item_pupk->bakumutu->nilai_baku_mutu : '-' }}
                        </td>

                        <td style="width: 20%">
                          {{ $item_pupk->keterangan_permohonan_uji_parameter_klinik ?? old('keterangan_permohonan_uji_parameter_klinik') }}
                        </td>
                      </tr>
                    @else
                      <tr>
                        <th colspan="6">{{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}: ~
                        </th>
                      </tr>

                      @php
                        $no_sub = 0;
                      @endphp
                      @foreach ($item_pupk->parametersatuanklinik->parametersubsatuanklinik as $key_pssk => $item_pssk)
                        <tr>
                          <td style="width: 20%">
                            <p style="padding-left: 30px">{{ $item_pssk->name_parameter_sub_satuan_klinik }} ~</p>
                          </td>

                          <td style="width: 15%">
                            {{ $item_pssk->permohonanujisubparameterklinik->hasil_permohonan_uji_sub_parameter_klinik ?? old('hasil_permohonan_uji_sub_parameter_klinik') }}
                          </td>

                          <td style="width: 15%">
                            <p id="flag_permohonan_uji_sub_parameter_klinik_text_{{ $no_sub }}">
                              {!! $item_pssk->permohonanujisubparameterklinik->flag_permohonan_uji_sub_parameter_klinik ?? '-' !!}
                            </p>
                          </td>

                          <td style="width: 15%">
                            @if ($item_pssk->permohonanujisubparameterklinik !== null)
                              {{ $item_pssk->permohonanujisubparameterklinik->unit->name_unit }}
                            @endif
                          </td>

                          <td style="width: 15%">
                            {{ $item_pssk->permohonanujisubparameterklinik != null ? $item_pssk->permohonanujisubparameterklinik->baku_mutu_permohonan_uji_sub_parameter_klinik : ($item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik != null ? $item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik : '-') }}
                          </td>

                          <td style="width: 20%">
                            {{ $item_pssk->permohonanujisubparameterklinik->keterangan_permohonan_uji_sub_parameter_klinik ?? old('keterangan_permohonan_uji_sub_parameter_klinik') }}
                          </td>
                        </tr>

                        @php
                          $no_sub++;
                        @endphp
                      @endforeach
                    @endif

                    @php
                      $no++;
                    @endphp
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif
      </div>
    </div>

  </div>
@endsection
