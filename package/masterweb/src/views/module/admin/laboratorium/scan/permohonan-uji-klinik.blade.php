@extends('masterweb::template.admin.layout_scan')
@section('title')
  Detail Permohonan Uji Klinik
@endsection

@section('content')

  <div class="card" style="margin-bottom: 20px">
    <div class="card-header">

      <H4>Detail Permohonan Uji Klinik</H4>
    </div>
    <div class="card-body">
      <div class="col-md-12">

        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Nomor Register</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label
                  for="codesample_samples">{{ $item_permohonan_uji_klinik->getDisplayNoregister() }}</label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Tanggal Register</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label
                  for="codesample_samples">{{ isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik) ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y') : '-' }}</label>
              </div>
            </div>

            @if ($tgl_pengujian)
              <div class="row">
                <div class="col-md-3">
                  <label for="codesample_samples">Tanggal Pengujian</label>
                </div>
                <div class="col-md-0">
                  <label for="codesample_samples">:</label>
                </div>
                <div class="col-md-8">
                  <label for="codesample_samples">{{ $tgl_pengujian }}</label>
                </div>
              </div>
            @endif

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Nama Pasien</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label
                  for="codesample_samples">{{ $item_permohonan_uji_klinik->namapasien_permohonan_uji_klinik }}</label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">No. KTP</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {{ $item_permohonan_uji_klinik->nikpasien_permohonan_uji_klinik ?? '-' }}
                </label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Tanggal Lahir</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {{ isset($item_permohonan_uji_klinik->tgllahir_permohonan_uji_klinik) ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->tgllahir_permohonan_uji_klinik)->isoFormat('D MMMM Y') : '' }}
                </label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Usia</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {{ $item_permohonan_uji_klinik->umurtahun_permohonan_uji_klinik ?? '-' }}
                  Tahun
                  {{ $item_permohonan_uji_klinik->umurbulan_permohonan_uji_klinik ?? '-' }} Bulan
                  {{ $item_permohonan_uji_klinik->umurhari_permohonan_uji_klinik ?? '-' }} Hari
                </label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Jenis Kelamin</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {{ $item_permohonan_uji_klinik->gender_permohonan_uji_klinik == 'L' ? 'Laki-laki' : 'Perempuan' }}
                </label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">No. Telepon</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {{ $item_permohonan_uji_klinik->phone_permohonan_uji_klinik ?? '-' }}
                </label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Alamat Pasien</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {{ $item_permohonan_uji_klinik->alamat_permohonan_uji_klinik ?? '-' }}
                </label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Tanggal Pengambilan</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {{ $tgl_pengambilan }}
                </label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Nama Pengirim</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {{ $item_permohonan_uji_klinik->namapengirim_permohonan_uji_klinik }}
                </label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Diagnosa</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">
                  {!! $item_permohonan_uji_klinik->diagnosa_permohonan_uji_klinik !!}
                </label>
              </div>
            </div>

            @if ($item_permohonan_uji_klinik->permohonanujianalisklinik)
              @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik))
                <div class="row">
                  <div class="col-md-3">
                    <label for="codesample_samples">Kesimpulan</label>
                  </div>
                  <div class="col-md-0">
                    <label for="codesample_samples">:</label>
                  </div>
                  <div class="col-md-8">
                    <label for="codesample_samples">
                      {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik !!}
                    </label>
                  </div>
                </div>
              @endif

              @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik))
                <div class="row">
                  <div class="col-md-3">
                    <label for="codesample_samples">Kategori</label>
                  </div>
                  <div class="col-md-0">
                    <label for="codesample_samples">:</label>
                  </div>
                  <div class="col-md-8">
                    <label for="codesample_samples">
                      {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik !!}
                    </label>
                  </div>
                </div>
              @endif

              @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik))
                <div class="row">
                  <div class="col-md-3">
                    <label for="codesample_samples">Saran</label>
                  </div>
                  <div class="col-md-0">
                    <label for="codesample_samples">:</label>
                  </div>
                  <div class="col-md-8">
                    <label for="codesample_samples">
                      {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik !!}
                    </label>
                  </div>
                </div>
              @endif
            @endif
          </li>
        </ul>
      </div>
    </div>
  </div>

  @if (count($item_permohonan_uji_klinik->permohonanujipaketklinik) > 0)
    <div class="card" style="margin-bottom: 20px">
      <div class="card-header" style="background-color: #17a2b8">
        <H6 style="color: white"><b>Detail Pemeriksaan</b></H6>
      </div>

      <div class="card-body">
        {{-- Inputan Parameter --}}

        <div class="row" style="margin-top: 20px">
          <div class="col-md-12">
            <h3>Parameter yang Dipilih</h3>
          </div>

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
                  <td>Rp.
                    {{ number_format($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik, 2, ',', '.') }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>


        {{-- Detail Analisa Pemeriksaan Klinik --}}
        @if (count($arr_permohonan_parameter_klinik) > 0)
          <div class="row" style="margin-top: 20px">
            <div class="col-md-12">
              <h3>Hasil Pemeriksaan Klinik</h3>
            </div>

            <div class="table-responsive">
              <table width="100%" class="table table-bordered">
                <thead>
                  <tr>
                    <th style="width: 20%; text-align: center">Nama Test</th>
                    <th style="width: 15%; text-align: center">Nilai Rujukan</th>
                    <th style="width: 15%; text-align: center">Satuan</th>
                    <th style="width: 15%; text-align: center">Hasil</th>
                    <th style="width: 15%; text-align: center">Flag</th>
                    <th style="width: 20%; text-align: center">Keterangan</th>
                  </tr>
                </thead>

                <tbody>
                  {{-- panggil semua paket yang ada untuk mengeluarkan parameter satuan dan parameter sub satuan --}}
                  @if (count($arr_permohonan_parameter_klinik) > 0)
                    {{-- mengeluarkan data semua permhonanuji yang ada di tb_permohonan_uji_paket_klinik --}}
                    @foreach ($arr_permohonan_parameter_klinik as $key_paket => $item_paket)
                      {{-- pada analis tidak memerlukan kondisi untuk mengeluarkan tipe paket atau custom --}}
                      {{-- kondisi apkah ada data paket yang memiliki parameter satuan --}}
                      @if (count($item_paket['data_permohonan_uji_satuan_klinik']) > 0)
                        {{-- mapping data parameter satuan dari paket yang dipilih --}}
                        @foreach ($item_paket['data_permohonan_uji_satuan_klinik'] as $key_satuan_klinik => $item_satuan_klinik)
                          {{-- pada analis hanya mengeluarkan kondisi apakah parameter satuan memiliki data subparameter atau hanya parameter satuan saja --}}
                          @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                            <tr>
                              <th colspan="6" style="text-align: left">
                                @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                  <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                @else
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                @endif
                              </th>
                            </tr>

                            {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan --}}
                            @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                              <tr>
                                <td style="width: 20%; text-align: center">
                                  <p style="padding-left: 30px">
                                    {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }} ~</p>
                                </td>

                                <td style="width: 15%; text-align: center">
                                  {{ $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] }}
                                </td>

                                <td style="width: 15%; text-align: center">
                                  @if ($item_subsatuan_klinik['satuan_permohonan_uji_sub_parameter_klinik'] != null)
                                    {{ $item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik'] }}
                                  @else
                                    -
                                  @endif
                                </td>

                                <td style="width: 15%; text-align: center">
                                  {{ $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? '-' }}
                                </td>

                                <td style="width: 15%; text-align: center">
                                  {!! $item_subsatuan_klinik['flag_permohonan_uji_sub_parameter_klinik'] ?? '-' !!}
                                </td>

                                <td style="width: 20%; text-align: center">
                                  {{ $item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] ?? '-' }}
                                </td>
                              </tr>
                            @endforeach
                          @else
                            <tr>
                              <th style="width: 20%; text-align: left">
                                @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                  <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                @else
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                @endif
                              </th>

                              <td style="width: 15%; text-align: center">
                                {{ $item_satuan_klinik['nilai_baku_mutu'] }}
                              </td>

                              {{-- kondisi jika data satuan pada permohonan uji parameter klinik belum terpilih dan suda terpilih --}}
                              <td style="width: 15%; text-align: center">

                                @if ($item_satuan_klinik['satuan_permohonan_uji_parameter_klinik'] != null)
                                  {{ $item_satuan_klinik['nama_satuan_permohonan_uji_parameter_klinik'] }}
                                @else
                                  -
                                @endif

                              </td>

                              <td style="width: 15%; text-align: center">
                                {{ $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '-' }}
                              </td>

                              <td style="width: 15%; text-align: center">
                                {!! $item_satuan_klinik['flag_permohonan_uji_parameter_klinik'] ?? '-' !!}
                              </td>

                              <td style="width: 20%; text-align: center">
                                {{ $item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik'] ?? '-' }}
                              </td>
                            </tr>
                          @endif
                        @endforeach
                      @endif
                    @endforeach
                  @endif
                </tbody>
              </table>

              <table width="100%" class="table">
                <tr>
                  <td colspan="6">Waktu pengambilan spesimen:</td>
                </tr>

                <tr>
                  <td style="width: 100px">Darah</td>
                  <td>{{ $tgl_spesimen_darah }}</td>
                </tr>

                <tr>
                  <td style="width: 100px">Urine</td>
                  <td>{{ $tgl_spesimen_urine }}</td>
                </tr>
              </table>
            </div>
          </div>
        @endif

        {{-- Detail Analisa Pemeriksaan PCR --}}
        @if (count($arr_permohonan_parameter_klinik_pcr) > 0)
          <div class="row" style="margin-top: 20px">
            <div class="col-md-12">
              <h3>Hasil Pemeriksaan PCR</h3>
            </div>

            <div class="table-responsive">
              <table width="100%" class="table table-bordered">
                <thead>
                  <tr>
                    <th style="width: 20%; text-align: center">JENIS PEMERIKSAAN</th>
                    <th style="width: 20%; text-align: center">NILAI RUJUKAN</th>
                    <th style="width: 20%; text-align: center">HASIL</th>
                    <th style="width: 20%; text-align: center">TANGGAL DIPERIKSA</th>
                    <th style="width: 20%; text-align: center">LABORATORIUM PEMERIKSAAN</th>
                  </tr>
                </thead>

                <tbody>
                  {{-- panggil semua paket yang ada untuk mengeluarkan parameter satuan dan parameter sub satuan --}}
                  @if (count($arr_permohonan_parameter_klinik_pcr) > 0)
                    {{-- mengeluarkan data semua permhonanuji yang ada di tb_permohonan_uji_paket_klinik --}}
                    @foreach ($arr_permohonan_parameter_klinik_pcr as $key_paket => $item_paket)
                      {{-- pada analis tidak memerlukan kondisi untuk mengeluarkan tipe paket atau custom --}}
                      {{-- kondisi apkah ada data paket yang memiliki parameter satuan --}}
                      @if (count($item_paket['data_permohonan_uji_satuan_klinik']) > 0)
                        {{-- mapping data parameter satuan dari paket yang dipilih --}}
                        @foreach ($item_paket['data_permohonan_uji_satuan_klinik'] as $key_satuan_klinik => $item_satuan_klinik)
                          {{-- pada analis hanya mengeluarkan kondisi apakah parameter satuan memiliki data subparameter atau hanya parameter satuan saja --}}
                          @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                            <tr>
                              <th colspan="6" style="text-align: left">
                                @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                  <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                @else
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                @endif
                              </th>
                            </tr>

                            {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan --}}
                            @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                              <tr>
                                <td style="width: 20%; text-align: center">
                                  <p style="padding-left: 30px">
                                    {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }} ~</p>
                                </td>

                                <td style="width: 20%; text-align: center">
                                  {{ $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] }}
                                </td>

                                <td style="width: 20%; text-align: center">
                                  {{ $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? '-' }}
                                </td>

                                <td style="width: 20%; text-align: center">
                                  {{ $tgl_pengujian }}
                                </td>

                                <td style="width: 20%; text-align: center">
                                  -
                                </td>
                              </tr>
                            @endforeach
                          @else
                            <tr>
                              <th style="width: 20%; text-align: left">
                                @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                  <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                @else
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                @endif
                              </th>

                              <td style="width: 20%; text-align: center">
                                {{ $item_satuan_klinik['nilai_baku_mutu'] }}
                              </td>

                              <td style="width: 20%; text-align: center">
                                {{ $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '-' }}
                              </td>

                              <td style="width: 20%; text-align: center">
                                {{ $tgl_pengujian }}
                              </td>

                              <td style="width: 20%; text-align: center">
                                -
                              </td>
                            </tr>
                          @endif
                        @endforeach
                      @endif
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>

          <div class="row">
            <div class="table-responsive mt-10">
              <table width="100%" class="table table-bordered">
                <thead>
                  <tr>
                    <th style="width: 20%; text-align: center">CT</th>
                    <th style="width: 20%; text-align: center">HASIL</th>
                    <th style="width: 20%; text-align: center">RUJUKAN</th>
                  </tr>
                </thead>

                <tbody>
                  <tr>
                    <td>Gen 1 : ORF1ab</td>
                    <td></td>
                    <td rowspan="2" style="text-align: center"><strong>>36</strong></td>
                  </tr>

                  <tr>
                    <td>Gen 2 : E</td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        @endif

        {{-- Detail Analisa Pemeriksaan Antigen --}}
        @if (count($arr_permohonan_parameter_klinik_antigen) > 0)
          <div class="row" style="margin-top: 20px">
            <div class="col-md-12">
              <h3>Hasil Pemeriksaan Antigen</h3>
            </div>

            <div class="table-responsive">
              <table width="100%" class="table table-bordered">
                <thead>
                  <tr>
                    <th style="width: 20%; text-align: center">JENIS PEMERIKSAAN</th>
                    <th style="width: 15%; text-align: center">HASIL</th>
                    <th style="width: 15%; text-align: center">NILAI RUJUKAN</th>
                  </tr>
                </thead>

                <tbody>
                  {{-- panggil semua paket yang ada untuk mengeluarkan parameter satuan dan parameter sub satuan --}}
                  @if (count($arr_permohonan_parameter_klinik_antigen) > 0)
                    {{-- mengeluarkan data semua permhonanuji yang ada di tb_permohonan_uji_paket_klinik --}}
                    @foreach ($arr_permohonan_parameter_klinik_antigen as $key_paket => $item_paket)
                      {{-- pada analis tidak memerlukan kondisi untuk mengeluarkan tipe paket atau custom --}}
                      {{-- kondisi apkah ada data paket yang memiliki parameter satuan --}}
                      @if (count($item_paket['data_permohonan_uji_satuan_klinik']) > 0)
                        {{-- mapping data parameter satuan dari paket yang dipilih --}}
                        @foreach ($item_paket['data_permohonan_uji_satuan_klinik'] as $key_satuan_klinik => $item_satuan_klinik)
                          {{-- pada analis hanya mengeluarkan kondisi apakah parameter satuan memiliki data subparameter atau hanya parameter satuan saja --}}
                          @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                            <tr>
                              <th colspan="6" style="text-align: left">
                                @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                  <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                @else
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                @endif
                              </th>
                            </tr>

                            {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan --}}
                            @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                              <tr>
                                <td style="width: 20%; text-align: center">
                                  <p style="padding-left: 30px">
                                    {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }} ~</p>
                                </td>

                                <td style="width: 15%; text-align: center">
                                  {{ $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? '-' }}
                                </td>

                                <td style="width: 15%; text-align: center">
                                  {{ $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] }}
                                </td>
                              </tr>
                            @endforeach
                          @else
                            <tr>
                              <th style="width: 20%; text-align: left">
                                @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                  <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                @else
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                @endif
                              </th>

                              <td style="width: 15%; text-align: center">
                                {{ $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '-' }}
                              </td>

                              <td style="width: 15%; text-align: center">
                                {{ $item_satuan_klinik['nilai_baku_mutu'] }}
                              </td>
                            </tr>
                          @endif
                        @endforeach
                      @endif
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        @endif

        {{-- Detail Analisa Pemeriksaan Antibody --}}
        @if (count($arr_permohonan_parameter_klinik_antibody) > 0)
          <div class="row" style="margin-top: 20px">
            <div class="col-md-12">
              <h3>Hasil Pemeriksaan Antibody</h3>
            </div>

            <div class="table-responsive">
              <table width="100%" class="table table-bordered">
                <thead>
                  <tr>
                    <th style="width: 20%; text-align: center">JENIS PEMERIKSAAN</th>
                    <th style="width: 15%; text-align: center">HASIL</th>
                    <th style="width: 15%; text-align: center">NILAI RUJUKAN</th>
                  </tr>
                </thead>

                <tbody>
                  {{-- panggil semua paket yang ada untuk mengeluarkan parameter satuan dan parameter sub satuan --}}
                  @if (count($arr_permohonan_parameter_klinik_antibody) > 0)
                    {{-- mengeluarkan data semua permhonanuji yang ada di tb_permohonan_uji_paket_klinik --}}
                    @foreach ($arr_permohonan_parameter_klinik_antibody as $key_paket => $item_paket)
                      {{-- pada analis tidak memerlukan kondisi untuk mengeluarkan tipe paket atau custom --}}
                      {{-- kondisi apkah ada data paket yang memiliki parameter satuan --}}
                      @if (count($item_paket['data_permohonan_uji_satuan_klinik']) > 0)
                        {{-- mapping data parameter satuan dari paket yang dipilih --}}
                        @foreach ($item_paket['data_permohonan_uji_satuan_klinik'] as $key_satuan_klinik => $item_satuan_klinik)
                          {{-- pada analis hanya mengeluarkan kondisi apakah parameter satuan memiliki data subparameter atau hanya parameter satuan saja --}}
                          @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                            <tr>
                              <th colspan="6" style="text-align: left">
                                @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                  <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                @else
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                @endif
                              </th>
                            </tr>

                            {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan --}}
                            @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                              <tr>
                                <td style="width: 20%; text-align: center">
                                  <p style="padding-left: 30px">
                                    {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }} ~</p>
                                </td>

                                <td style="width: 15%; text-align: center">
                                  {{ $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? '-' }}
                                </td>

                                <td style="width: 15%; text-align: center">
                                  {{ $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] }}
                                </td>
                              </tr>
                            @endforeach
                          @else
                            <tr>
                              <th style="width: 20%; text-align: left">
                                @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                  <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                @else
                                  {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                @endif
                              </th>

                              <td style="width: 15%; text-align: center">
                                {{ $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '-' }}
                              </td>

                              <td style="width: 15%; text-align: center">
                                {{ $item_satuan_klinik['nilai_baku_mutu'] }}
                              </td>
                            </tr>
                          @endif
                        @endforeach
                      @endif
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        @endif
      </div>
    </div>
  @endif



@endsection
