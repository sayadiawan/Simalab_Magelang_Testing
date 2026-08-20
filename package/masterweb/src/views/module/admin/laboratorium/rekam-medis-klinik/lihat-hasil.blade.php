@extends('masterweb::template.admin.layout')
@section('title')
    Detail Hasil Rekam Medis Klinik
@endsection


@section('content')




    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-rekam-medis') }}">Rekam Medis Klinik</a>
                                </li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('elits-rekam-medis-show', $item->pasien_permohonan_uji_klinik) }}">Detail
                                        Rekam Medis
                                        Klinik</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page"><span>detail hasil rekam medis
                                        klinik</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>
                Detail Hasil Rekam Medis Klinik
            </h4>
        </div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tr>
                            <th width="250px">Nama Pasien</th>
                            <td>{{ $item->pasien->nama_pasien }}</td>
                        </tr>

                        <tr>
                            <th width="250px">No. Register</th>
                            <td>{{ $item->noregister_permohonan_uji_klinik }}</td>
                        </tr>

                        <tr>
                            <th width="250px">No. Rekam Medis</th>
                            <td>
                                {{ $item->getNoRekamMedis() }}
                            </td>
                        </tr>

                        <tr>
                            <th width="250px">Tanggal Lahir Pasien</th>
                            <td>
                                @php
                                    try {
                                        if (isset($item->pasien->tgllahir_pasien) && $item->pasien->tgllahir_pasien) {
                                            $dateOnly = explode(' ', $item->pasien->tgllahir_pasien)[0];
                                            echo \Carbon\Carbon::parse($dateOnly)->isoFormat('D MMMM Y');
                                        } else {
                                            echo '';
                                        }
                                    } catch (\Exception $e) {
                                        echo $item->pasien->tgllahir_pasien ?? '';
                                    }
                                @endphp
                            </td>
                        </tr>

                        <tr>
                            <th width="250px">Umur Pasien</th>
                            <td>
                                {{ $item->umurtahun_pasien_permohonan_uji_klinik . ' tahun ' . $item->umurbulan_pasien_permohonan_uji_klinik . ' bulan ' . $item->umurhari_pasien_permohonan_uji_klinik . ' hari' }}
                            </td>
                        </tr>

                        <tr>
                            <th width="250px">Jenis Kelamin</th>
                            <td>{{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>

                        <tr>
                            <th width="250px">Phone Pasien</th>
                            <td>{{ $item->pasien->phone_pasien }}</td>
                        </tr>

                        <tr>
                            <th width="250px">Alamat Pasien</th>
                            <td>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item->pasien) }}</td>
                        </tr>

                        <tr>
                            <th width="250px">Tanggal Register</th>
                            <td>
                                @php
                                    try {
                                        if (
                                            isset($item->tglregister_permohonan_uji_klinik) &&
                                            $item->tglregister_permohonan_uji_klinik
                                        ) {
                                            $dateOnly = explode(' ', $item->tglregister_permohonan_uji_klinik)[0];
                                            echo \Carbon\Carbon::parse($dateOnly)->isoFormat('D MMMM Y');
                                        } else {
                                            echo '-';
                                        }
                                    } catch (\Exception $e) {
                                        echo $item->tglregister_permohonan_uji_klinik ?? '-';
                                    }
                                @endphp
                            </td>
                        </tr>

                        <tr>
                            <th width="250px">Tanggal Pengambilan</th>
                            <td>
                                @php
                                    try {
                                        if (
                                            isset($item->tglpengambilan_permohonan_uji_klinik) &&
                                            $item->tglpengambilan_permohonan_uji_klinik
                                        ) {
                                            echo \Carbon\Carbon::parse(
                                                $item->tglpengambilan_permohonan_uji_klinik,
                                            )->isoFormat('D MMMM Y HH:mm');
                                        } else {
                                            echo '-';
                                        }
                                    } catch (\Exception $e) {
                                        echo $item->tglpengambilan_permohonan_uji_klinik ?? '-';
                                    }
                                @endphp
                            </td>
                        </tr>

                        <tr>
                            <th width="250px">Nama Pengirim</th>
                            <td>{{ $item->namapengirim_permohonan_uji_klinik }}</td>
                        </tr>

                        <tr>
                            <th width="250px">Diagnosa</th>
                            <td>{!! $item->diagnosa_permohonan_uji_klinik !!}</td>
                        </tr>
                    </table>
                </div>

                {{-- hasil paket yang diambil --}}
                @if (count($data_permohonan_uji_paket_klinik) > 0)
                    <div class="card" style="margin-bottom: 10px">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table-hasil-detail-rekam-medis" class="table" width="100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%">PAKET/CUSTOM</th>
                                            <th style="width: 15%">JUMLAH PARAMETER</th>
                                            <th style="width: 15%">HARGA SATUAN</th>
                                            <th style="width: 25%">JUMLAH</th>
                                        </tr>
                                    </thead>

                                    <tbody id="table-body">
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
                                                {{ number_format($item->total_harga_permohonan_uji_klinik, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- hasil paket yang diambil dan yang sudah dianalis --}}
                @if (
                    $item->tglpengujian_permohonan_uji_klinik !== null ||
                        $item->spesimen_darah_permohonan_uji_klinik !== null ||
                        $item->spesimen_urine_permohonan_uji_klinik !== null)
                    <div class="card" style="margin-bottom: 10px">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <thead>
                                        <tr>
                                            <th width="250px">Dokter</th>
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

                                        @if ($item->permohonanujianalisklinik)
                                            @if ($item->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik)
                                                <tr>
                                                    <th width="250px" class="align-top">Kesimpulan</th>
                                                    <td class="align-top">
                                                        {!! $item->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik !!}
                                                    </td>
                                                </tr>
                                            @endif

                                            @if ($item->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik)
                                                <tr>
                                                    <th width="250px" class="align-top">Kategori</th>
                                                    <td class="align-top">
                                                        {!! $item->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik !!}
                                                    </td>
                                                </tr>
                                            @endif

                                            @if ($item->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik)
                                                <tr>
                                                    <th width="250px" class="align-top">Saran</th>
                                                    <td class="align-top">
                                                        {!! $item->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik !!}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    </thead>
                                </table>

                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%">Nama Test</th>
                                            <th style="width: 15%">Nilai Rujukan</th>
                                            <th style="width: 15%">Satuan</th>
                                            <th style="width: 15%">Hasil</th>
                                            <th style="width: 15%">Flag</th>
                                            <th style="width: 20%">Keterangan</th>
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
                                                                    {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }}
                                                                    ~</p>
                                                            </td>

                                                            <td style="width: 15%; text-align: center">
                                                                {!! $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] !!}
                                                            </td>

                                                            <td style="width: 15%; text-align: center">
                                                                @if ($item_subsatuan_klinik['satuan_permohonan_uji_sub_parameter_klinik'] != null)
                                                                    {{ $item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik'] }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>

                                                            <td style="width: 15%; text-align: center">
                                                                {!! $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? '-' !!}
                                                            </td>

                                                            <td style="width: 15%; text-align: center">
                                                                {!! $item_satuan_klinik['flag_permohonan_uji_parameter_klinik']
                                                                    ? '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>'
                                                                    : '' !!}
                                                            </td>

                                                            <td style="width: 20%; text-align: center">
                                                                {!! $item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] ?? '' !!}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td style="width: 20%; text-align: left">
                                                            {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                                        </td>

                                                        <td style="width: 15%; text-align: center">
                                                            {!! $item_satuan_klinik['nilai_baku_mutu'] !!}
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
                                                            {!! $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '-' !!}
                                                        </td>

                                                        <td style="width: 15%; text-align: center">
                                                            {!! $item_satuan_klinik['flag_permohonan_uji_parameter_klinik']
                                                                ? '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>'
                                                                : '' !!}
                                                        </td>

                                                        <td style="width: 20%; text-align: center">
                                                            {!! $item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik'] ?? '' !!}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <button type="button" class="btn btn-light"
                    onclick="document.location='{{ route('elits-rekam-medis-show', $item->pasien_permohonan_uji_klinik) }}'">Kembali</button>
            </li>
        </ul>
    </div>
@endsection
