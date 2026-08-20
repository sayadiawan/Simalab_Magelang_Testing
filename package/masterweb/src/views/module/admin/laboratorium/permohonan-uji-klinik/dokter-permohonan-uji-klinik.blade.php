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
                <li class="breadcrumb-item active" aria-current="page"><span>rekomendasi dokter permohonan uji paket
                    klinik</span>
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
      <h4>Rekomendasi Dokter Permohonan Uji Klinik
      </h4>
    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <form
          action="{{ route('elits-permohonan-uji-klinik.store-permohonan-uji-rekomendasi-dokter', $item->id_permohonan_uji_klinik) }}"
          method="POST" enctype="multipart/form-data" id="form">

          @csrf
          @method('POST')

          <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

          <div class="table-responsive">
            <table class="table table-borderless">
              <tr>
                <th width="250px">No. Register</th>
                <td>{{ $item->noregister_permohonan_uji_klinik }}</td>
              </tr>

              <tr>
                <th width="250px">No. Rekam Medis</th>
                <td>
                  {{ Carbon\Carbon::createFromFormat('Y-m-d', $item->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
                </td>
              </tr>

              <tr>
                <th width="250px">Tgl. Register</th>
                <td>{{ $tgl_register_permohonan_uji_klinik }}</td>
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

          <div class="table-responsive">
            <table class="table table-borderless">
              <thead>
                <tr>
                  <th width="250px">Dokter Perujuk</th>
                  <td>{{ $item->dokter_permohonan_uji_klinik }}</td>
                </tr>

                <tr>
                  <th width="250px">Analis</th>
                  <td>{{ $item->name_analis_permohonan_uji_klinik }}

                    @if ($item->nip_analis_permohonan_uji_klinik)
                      - <strong>NIP {{ $item->nip_analis_permohonan_uji_klinik }}</strong>
                    @endif
                  </td>
                </tr>

                <tr>
                  <th width="250px">Tanggal Pengujian</th>
                  <td>{{ $tgl_pengujian }}</td>
                </tr>

                <tr>
                  <th width="250px">Waktu Pengambilan Spesimen Darah</th>
                  <td>{{ $tgl_spesimen_darah }}</td>
                </tr>

                <tr>
                  <th width="250px">Waktu Pengambilan Spesimen Urine</th>
                  <td>{{ $tgl_spesimen_urine }}</td>
                </tr>
              </thead>
            </table>

            <table class="table table-striped">
              <thead>
                <tr>
                  <th style="width: 20%">NAMA TEST</th>
                  <th style="width: 15%" style="text-align: center">NILAI RUJUKAN</th>
                  <th style="width: 15%" style="text-align: center">SATUAN</th>
                  <th style="width: 15%" style="text-align: center">HASIL</th>
                  <th style="width: 15%" style="text-align: center">FLAG</th>
                  <th style="width: 20%" style="text-align: center">KETERANGAN</th>
                </tr>
              </thead>

              <tbody>
                @php
                  $no = 0;
                @endphp
                @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                  <tr>
                    <th colspan="6" style="text-align: left">
                      <strong>{{ $item_parameter_jenis_klinik['name_parameter_jenis_klinik'] }}</strong>
                    </th>
                  </tr>
                  @foreach ($item_parameter_jenis_klinik['item_permohonan_parameter_satuan'] as $key_satuan_klinik => $item_satuan_klinik)
                    @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                      <tr>
                        <td colspan="6" style="text-align: left">
                          {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                        </td>
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
                            {!! $item_satuan_klinik['flag_permohonan_uji_parameter_klinik']
                                ? '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>'
                                : '' !!}

                          </td>

                          <td style="width: 20%; text-align: center">
                            {{ $item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] ?? '' }}
                          </td>
                        </tr>
                      @endforeach
                    @else
                      <tr>
                        <td style="width: 20%; text-align: left">
                          {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                        </td>

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
                          {!! $item_satuan_klinik['flag_permohonan_uji_parameter_klinik']
                              ? '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>'
                              : '' !!}
                        </td>

                        <td style="width: 20%; text-align: center">
                          {{ $item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik'] ?? '' }}
                        </td>
                      </tr>
                    @endif
                  @endforeach
                @endforeach
              </tbody>
            </table>
          </div>

          {{-- HASIL ANALIS DAN KESIMPULAN --}}
          <div class="form-group">
            <label for="kesimpulan_permohonan_uji_analis_klinik">Kesimpulan</label>

            <textarea class="form-control" name="kesimpulan_permohonan_uji_analis_klinik"
              id="kesimpulan_permohonan_uji_analis_klinik" cols="30" rows="10">{{ $item->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik ?? old('kesimpulan_permohonan_uji_analis_klinik') }}</textarea>

            <script>
              $(document).ready(function() {
                tinymce.init({
                  selector: 'textarea#kesimpulan_permohonan_uji_analis_klinik',
                  height: 200,
                  menubar: false,
                  plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                  ],
                  toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
                });
              });
            </script>
          </div>

          <div class="form-group">
            <label for="kategori_permohonan_uji_analis_klinik">Kategori</label>

            <textarea class="form-control" name="kategori_permohonan_uji_analis_klinik" id="kategori_permohonan_uji_analis_klinik"
              cols="30" rows="10">{{ $item->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik ?? old('kategori_permohonan_uji_analis_klinik') }}</textarea>

            <script>
              $(document).ready(function() {
                tinymce.init({
                  selector: 'textarea#kategori_permohonan_uji_analis_klinik',
                  height: 200,
                  menubar: false,
                  plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                  ],
                  toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
                });
              });
            </script>
          </div>

          <div class="form-group">
            <label for="saran_permohonan_uji_analis_klinik">Saran</label>

            <textarea class="form-control" name="saran_permohonan_uji_analis_klinik" id="saran_permohonan_uji_analis_klinik"
              cols="30" rows="10">{{ $item->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik ?? old('saran_permohonan_uji_analis_klinik') }}</textarea>

            <script>
              $(document).ready(function() {
                tinymce.init({
                  selector: 'textarea#saran_permohonan_uji_analis_klinik',
                  height: 200,
                  menubar: false,
                  plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                  ],
                  toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
                });
              });
            </script>
          </div>

        </form>
        <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
        <button type="button" class="btn btn-light"
          onclick="document.location='{{ url('/elits-permohonan-uji-klinik') }}'">Kembali</button>
      </li>
    </ul>
  </div>
@endsection

@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script>
    $(document).ready(function() {
      $('.btn-simpan').on('click', function() {
        $('#form').ajaxSubmit({
          success: function(response) {
            if (response.status == true) {
              swal({
                  title: "Success!",
                  text: response.pesan,
                  icon: "success"
                })
                .then(function() {
                  document.location = '/elits-permohonan-uji-klinik';
                });
            } else {
              var pesan = "";
              var data_pesan = response.pesan;
              const wrapper = document.createElement('div');

              if (typeof(data_pesan) == 'object') {
                jQuery.each(data_pesan, function(key, value) {
                  console.log(value);
                  pesan += value + '. <br>';
                  wrapper.innerHTML = pesan;
                });

                swal({
                  title: "Error!",
                  content: wrapper,
                  icon: "warning"
                });
              } else {
                swal({
                  title: "Error!",
                  text: response.pesan,
                  icon: "warning"
                });
              }
            }
          },
          error: function() {
            swal("Error!", "System gagal menyimpan!", "error");
          }
        })
      })
    })
  </script>
@endsection
