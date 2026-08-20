@extends('masterweb::template.admin.layout')
@section('title')
  Baku Mutu Lab.{{ $lab }} Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-baku-mutu-' . $lab_link) }}"> Baku Mutu
                    Lab.{{ $lab }}</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Detail</span></li>
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
        <div class="card-body">
          <div class="table-responsive">
            <table class="table" width="100%">
              <tr>
                <th width="400px">Parameter Jenis Parameter</th>
                <td>{{ $item->parameterjenisklinik->name_parameter_jenis_klinik }}</td>
              </tr>

              <tr>
                <th width="400px">Parameter Satuan Parameter</th>
                <td>{{ $item->parametersatuanklinik->name_parameter_satuan_klinik }}</td>
              </tr>

              <tr>
                <th width="400px">Acuan Baku Mutu</th>
                <td>
                  {{ $item->library->title_library }} <br>
                  {!! $item->library->link_library !== '-'
                      ? '<a href="' . $item->library->link_library . '" target="__blank">' . $item->library->link_library . '</a>'
                      : '' !!}
                </td>
              </tr>


            </table>

            <table class="table" width="100%">
              @if ($item->is_sub_parameter_satuan_baku_mutu == '0')
                <tr>
                  <td width="400px">
                    Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapan koma, maka menggunakan
                      .
                      (titik), apabila tidak ada kosongi)</b>
                  </td>
                  <td>{{ $item->min }}</td>
                </tr>

                <tr>
                  <td width="400px">
                    Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan
                      .
                      (titik), apabila tidak ada kosongi)</b>
                  </td>
                  <td>{{ $item->max }}</td>
                </tr>

                <tr>
                  <td width="400px">
                    Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan berupa range minimal maksimal
                      misal (Negatif atau Positif) maka isi disini, apabila tidak maka kosongi)</b>
                  </td>
                  <td>{{ $item->equal }}</td>
                </tr>

                <tr>
                  <td width="400px">
                    Nilai Baku Mutu di Laporan
                  </td>
                  <td>{{ $item->nilai_baku_mutu }}</td>
                </tr>
              @endif

              @if ($item->is_sub_parameter_satuan_baku_mutu == '1')
                @if ($item->bakumutudetailparameterklinik !== null)
                  @foreach ($item->bakumutudetailparameterklinik as $key_bakumutudetail => $item_bakumutudetail)
                    <tr>
                      <th colspan="2">
                        {{ $item_bakumutudetail->parametersubsatuanklinik->name_parameter_sub_satuan_klinik }} ~
                      </th>
                    </tr>

                    <tr>
                      <td width="400px">
                        Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapan koma, maka menggunakan
                          .
                          (titik)
                          , apabila tidak ada kosongi)</b>
                      </td>
                      <td>{{ $item_bakumutudetail->min_baku_mutu_detail_parameter_klinik }}</td>
                    </tr>

                    <tr>
                      <td width="400px">
                        Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan
                          .
                          (titik), apabila tidak ada kosongi)</b>
                      </td>
                      <td>{{ $item_bakumutudetail->max_baku_mutu_detail_parameter_klinik }}</td>
                    </tr>

                    <tr>
                      <td width="400px">
                        Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan berupa range minimal maksimal
                          misal (Negatif atau Positif) maka isi disini, apabila tidak maka kosongi)</b>
                      </td>
                      <td>{{ $item_bakumutudetail->equal_baku_mutu_detail_parameter_klinik }}</td>
                    </tr>

                    <tr>
                      <td width="400px">
                        Nilai Baku Mutu di Laporan
                      </td>
                      <td>{{ $item_bakumutudetail->nilai_baku_mutu_detail_parameter_klinik }}</td>
                    </tr>
                  @endforeach
                @endif
              @endif
            </table>
          </div>

          <br>

          <button type="button" onclick="document.location='{{ url('/elits-baku-mutu-klinik') }}'"
            class="btn btn-light">Kembali</button>
        </div>
      </div>
    </div>
  </div>
@endsection
