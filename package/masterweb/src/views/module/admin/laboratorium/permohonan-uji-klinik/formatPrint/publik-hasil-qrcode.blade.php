<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik - {{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    <style>
        .starter-template {
            text-align: center;
        }


        table>tr>td {
            /* cell-padding: 5px !important; */
        }

        @media print {
            #cetak {
                display: none;
            }
        }

        .garis {
            border: 1px solid
        }

        .table2 {
            font-size: 5px;
            text-align: center
        }

        .result {
            border-collapse: collapse;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
        }

        @page {
            size: 794px 1248px;
            margin: 20px 20px 20px 20px;
        }

        body {
            font-size: 12px;
        }

        .page_break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td><img src="{{ asset('assets/admin/images/logo/kop_perusahaan.png') }}" width="730px"></td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="10%">
                Nomor
            </td>
            <td width="1%">
                :
            </td>
            <td>
                -
            </td>
            <td align="right">
                Magelang,
                {{ isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)
                    ? \Carbon\Carbon::createFromFormat(
                        'Y-m-d',
                        $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
                    )->isoFormat('D MMMM Y')
                    : '-' }}

            </td>
        </tr>
        <tr>
            <td width="10%">
                Hal
            </td>
            <td width="1%">
                :
            </td>
            <td>
                Hasil Klinik
            </td>
            <td align="right">

            </td>
        </tr>
    </table>

    <br>
    <table width="40%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="2">
                Yang Terhormat :
            </td>

        </tr>
        <tr>
            <td colspan="2">
                {{ $item_permohonan_uji_klinik->namapasien_permohonan_uji_klinik }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{ $item_permohonan_uji_klinik->alamat_permohonan_uji_klinik }}
            </td>

        </tr>
    </table>
    <br>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>Disampaikan dengan hormat hasil laboratorium klinik kami adalah sebagai berikut:</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="30%">
                No. Register
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Tanggal Register
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)
                    ? \Carbon\Carbon::createFromFormat(
                        'Y-m-d',
                        $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
                    )->isoFormat('D MMMM Y')
                    : '-' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Nama Pasien
            </td>
            <td width="1%">
                :
            </td>
            <td>
                <strong>{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</strong>
            </td>
        </tr>

        <tr>
            <td width="30%">
                Jenis Kelamin
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Tanggal Lahir
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien)
                    ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat(
                        'D MMMM Y',
                    )
                    : '' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Usia
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-' }} Tahun
                {{ $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik ?? '-' }} Bulan
                {{ $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik ?? '-' }} Hari
            </td>
        </tr>

        <tr>
            <td width="30%">
                No. Rekam Medis
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item_permohonan_uji_klinik->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                No. Telepon
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->pasien->phone_pasien ?? '-' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Alamat Pasien
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                No. KTP
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->pasien->nik_pasien ?? '-' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Pengirim
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->namapengirim_permohonan_uji_klinik ?? '-' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Dokter Perujuk
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->dokter_permohonan_uji_klinik ?? '-' }}
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td><strong>Hasil Pemeriksaan</strong></td>
        </tr>
        <tr>
            <td>
                <i>(<span style="color: red">*</span>) Hasil diluar nilai normal yang diperbolehkan</i>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead>
            <tr>
                <th style="width: 20%">Nama Test</th>
                <th style="width: 15%">Hasil</th>
                <th style="width: 15%">Flag</th>
                <th style="width: 15%">Satuan</th>
                <th style="width: 15%">Nilai Rujukan</th>
                <th style="width: 20%">Keterangan</th>
            </tr>
        </thead>

        <tbody>
            @php
                $no = 0;
            @endphp
            @foreach ($data_permohonan_uji_parameter_klinik as $key_pupk => $item_pupk)
                @if (count($item_pupk->parametersatuanklinik->parametersubsatuanklinik) < 1)
                    <tr>
                        <td style="width: 20%; text-align: left">
                            <strong>{{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}</strong>
                        </td>

                        <td style="width: 15%; text-align: center">
                            {{ $item_pupk->hasil_permohonan_uji_parameter_klinik ?? '-' }}
                        </td>

                        <td style="width: 15%; text-align: center">
                            <p id="flag_permohonan_uji_parameter_klinik_text_{{ $no }}">
                                {!! $item_pupk->flag_permohonan_uji_parameter_klinik ?? '-' !!}</p>
                        </td>

                        <td style="width: 15%; text-align: center">
                            {{ $item_pupk->unit->name_unit }}
                        </td>

                        <td style="width: 15%; text-align: center">
                            {{ ($item_pupk->baku_mutu_permohonan_uji_parameter_klinik != null
                                    ? $item_pupk->baku_mutu_permohonan_uji_parameter_klinik
                                    : $item_pupk->bakumutu != null)
                                ? $item_pupk->bakumutu->nilai_baku_mutu
                                : '-' }}
                        </td>

                        <td style="width: 20%; text-align: center">
                            {{ $item_pupk->keterangan_permohonan_uji_parameter_klinik ?? '-' }}
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="6" style="text-align: left">
                            <strong>{{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}: ~</strong>
                        </td>
                    </tr>

                    @php
                        $no_sub = 0;
                    @endphp
                    @foreach ($item_pupk->parametersatuanklinik->parametersubsatuanklinik as $key_pssk => $item_pssk)
                        <tr>
                            <td style="width: 20%; text-align: center">
                                <p style="padding-left: 30px">{{ $item_pssk->name_parameter_sub_satuan_klinik }} ~</p>
                            </td>

                            <td style="width: 15%; text-align: center">
                                {{ $item_pssk->permohonanujisubparameterklinik->hasil_permohonan_uji_sub_parameter_klinik ?? '-' }}
                            </td>

                            <td style="width: 15%; text-align: center">
                                <p id="flag_permohonan_uji_sub_parameter_klinik_text_{{ $no_sub }}">
                                    {!! $item_pssk->permohonanujisubparameterklinik->flag_permohonan_uji_sub_parameter_klinik ?? '-' !!}
                                </p>
                            </td>

                            <td style="width: 15%; text-align: center">
                                @if ($item_pssk->permohonanujisubparameterklinik !== null)
                                    {{ $item_pssk->permohonanujisubparameterklinik->unit->name_unit }}
                                @endif
                            </td>

                            <td style="width: 15%; text-align: center">
                                {{ $item_pssk->permohonanujisubparameterklinik != null
                                    ? $item_pssk->permohonanujisubparameterklinik->baku_mutu_permohonan_uji_sub_parameter_klinik
                                    : ($item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik != null
                                        ? $item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik
                                        : '-') }}
                            </td>

                            <td style="width: 20%; text-align: center">
                                {{ $item_pssk->permohonanujisubparameterklinik->keterangan_permohonan_uji_sub_parameter_klinik ?? '-' }}
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

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 10px">
        <tr>
            <td>Waktu pengambilan spesimen:</td>
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

    @if ($item_permohonan_uji_klinik->permohonanujianalisklinik)
        @if ($item_permohonan_uji_klinik->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik)
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-size: 30px; text-decoration: underline" colspan="6">
                        <strong>KESIMPULAN</strong>
                    </td>
                </tr>

                <tr>
                    <td class="align-top" colspan="6">
                        {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik !!}
                    </td>
                </tr>
            </table>
        @endif

        @if ($item_permohonan_uji_klinik->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik)
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-size: 30px; text-decoration: underline">
                        <strong>KATEGORI</strong>
                    </td>
                </tr>

                <tr>
                    <td class="align-top">
                        {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik !!}
                    </td>
                </tr>
            </table>
        @endif

        @if ($item_permohonan_uji_klinik->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik)
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-size: 30px; text-decoration: underline">
                        <strong>SARAN</strong>
                    </td>
                </tr>

                <tr>
                    <td class="align-top">
                        {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik !!}
                    </td>
                </tr>
            </table>
        @endif
    @endif

    {{-- pagebreak next pemeriksaan PCR --}}
    @if (count($data_permohonan_uji_parameter_klinik_pcr) > 0)
        <div class="page_break"></div>
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding-right: 20px">
                    Telah dilakukan pemeriksaan laboratorium dengan metode Pemeriksaan RT-PCR di Laboratorium rujukan
                    dengan
                    hasil sebagai berikut :
                </td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
            <thead>
                <tr>
                    <th style="width: 20%">JENIS PEMERIKSAAN</th>
                    <th style="width: 20%">TANGGAL DIPERIKSA</th>
                    <th style="width: 20%">HASIL</th>
                    <th style="width: 20%">NILAI RUJUKAN</th>
                    <th style="width: 20%">LABORATORIUM PEMERIKSAAN</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data_permohonan_uji_parameter_klinik_pcr as $key_pupk => $item_pupk)
                    @if (count($item_pupk->parametersatuanklinik->parametersubsatuanklinik) < 1)
                        <tr>
                            <th style="width: 20%">
                                {{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}
                            </th>

                            <td style="width: 15%">
                                {{ $item_pupk->hasil_permohonan_uji_parameter_klinik ?? '-' }}
                            </td>

                            <td style="width: 15%">
                                <p id="flag_permohonan_uji_parameter_klinik_text_{{ $no }}">
                                    {!! $item_pupk->flag_permohonan_uji_parameter_klinik ?? '-' !!}</p>
                            </td>

                            <td style="width: 15%">
                                {{ $item_pupk->unit->name_unit }}
                            </td>

                            <td style="width: 15%">
                                {{ ($item_pupk->baku_mutu_permohonan_uji_parameter_klinik != null
                                        ? $item_pupk->baku_mutu_permohonan_uji_parameter_klinik
                                        : $item_pupk->bakumutu != null)
                                    ? $item_pupk->bakumutu->nilai_baku_mutu
                                    : '-' }}
                            </td>

                            <td style="width: 20%">
                                {{ $item_pupk->keterangan_permohonan_uji_parameter_klinik ?? '-' }}
                            </td>
                        </tr>
                    @else
                        <tr>
                            <th colspan="6">{{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}:
                                ~</th>
                        </tr>
                        @foreach ($item_pupk->parametersatuanklinik->parametersubsatuanklinik as $key_pssk => $item_pssk)
                            <tr>
                                <td style="width: 20%">
                                    <p style="padding-left: 30px">{{ $item_pssk->name_parameter_sub_satuan_klinik }} ~
                                    </p>
                                </td>

                                <td style="width: 15%">
                                    {{ $item_pssk->permohonanujisubparameterklinik->hasil_permohonan_uji_sub_parameter_klinik ?? '-' }}
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
                                    {{ $item_pssk->permohonanujisubparameterklinik != null
                                        ? $item_pssk->permohonanujisubparameterklinik->baku_mutu_permohonan_uji_sub_parameter_klinik
                                        : ($item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik != null
                                            ? $item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik
                                            : '-') }}
                                </td>

                                <td style="width: 20%">
                                    {{ $item_pssk->permohonanujisubparameterklinik->keterangan_permohonan_uji_sub_parameter_klinik ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach

            </tbody>
        </table>
    @endif

    {{-- pagebreak next pemeriksaan ANTIGEN --}}
    @if (count($data_permohonan_uji_parameter_klinik_antigen) > 0)
        <div class="page_break"></div>
        <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
            <thead>
                <tr>
                    <th style="width: 20%">JENIS PEMERIKSAAN</th>
                    <th style="width: 15%">HASIL</th>
                    <th style="width: 15%">NILAI RUJUKAN</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data_permohonan_uji_parameter_klinik_antigen as $key_pupk => $item_pupk)
                    @if (count($item_pupk->parametersatuanklinik->parametersubsatuanklinik) < 1)
                        <tr>
                            <th style="width: 20%">
                                {{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}
                            </th>

                            <td style="width: 15%; text-align: center">
                                {{ $item_pupk->hasil_permohonan_uji_parameter_klinik ?? '-' }}
                            </td>

                            <td style="width: 15%; text-align: center">
                                {{ ($item_pupk->baku_mutu_permohonan_uji_parameter_klinik != null
                                        ? $item_pupk->baku_mutu_permohonan_uji_parameter_klinik
                                        : $item_pupk->bakumutu != null)
                                    ? $item_pupk->bakumutu->nilai_baku_mutu
                                    : '-' }}
                            </td>
                        </tr>
                    @else
                        <tr>
                            <th colspan="6">{{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}:
                                ~</th>
                        </tr>
                        @foreach ($item_pupk->parametersatuanklinik->parametersubsatuanklinik as $key_pssk => $item_pssk)
                            <tr>
                                <td style="width: 20%">
                                    <p style="padding-left: 30px">{{ $item_pssk->name_parameter_sub_satuan_klinik }} ~
                                    </p>
                                </td>

                                <td style="width: 15%; text-align: center">
                                    {{ $item_pssk->permohonanujisubparameterklinik->hasil_permohonan_uji_sub_parameter_klinik ?? '-' }}
                                </td>

                                <td style="width: 15%; text-align: center">
                                    {{ $item_pssk->permohonanujisubparameterklinik != null
                                        ? $item_pssk->permohonanujisubparameterklinik->baku_mutu_permohonan_uji_sub_parameter_klinik
                                        : ($item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik != null
                                            ? $item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik
                                            : '-') }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td>Catatan</td>
            </tr>

            <tr>
                <td style="width: 10px; vertical-align: top">A.</td>
                <td style="text-align: left; padding-right: 20px">
                    Apabila rapid test Antigen SARS-CoV-2 Reaktif <br>
                    Kemungkinan: <br>
                    &bull; Terinfeksi SARS CoV-2
                    <br>
                </td>
            </tr>

            <tr>
                <td style="width: 10px"></td>
                <td style="text-align: left; padding-right: 20px">
                    Saran: <br>
                    &bull; Segera melakukan pemeriksaan konfirmasi dengan RT-PCR <br>
                    &bull; Melakukan karantina atau isolasi sesuai dengan kriteria <br>
                    &bull; Menerapkan PHBS (perilaku hidup bersih dan sehat, mencuci tangan, menerapkan etika batuk,
                    menggunakan
                    masker,
                    menjaga stamina) dan physical distancing
                </td>
            </tr>

            <br>

            <tr>
                <td style="width: 10px; vertical-align: top">B.</td>
                <td style="text-align: left; padding-right: 20px">
                    Apabila rapid test Antigen SARS-CoV-2 Non Reaktif <br>
                    Kemungkinan <br>
                    &bull; Tidak terinfeksi SARS CoV-2<br>
                    &bull; Jumlah antigen pada spesimen lendir saluran nafas di bawah ambang deteksi alat <br>
                </td>
            </tr>

            <tr>
                <td style="width: 10px"></td>
                <td style="text-align: left; padding-right: 20px">
                    Saran: <br>
                    &bull; Apabila pasien memiliki gejala pernafasan , atau gejala tipikal Covid-19 lainnya dan
                    berkontak erat
                    dengan
                    pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR,pasien tetap harus melakukan karantina mandiri
                    dan
                    melakukan pemeriksaan antigen SARS-CoV-2 ulang dalam 5-7 hari, atau pemeriksaan RT-PCR. <br>
                    &bull; Apabila pasien tidak memiliki gejala pernafasan , atau gejala tipikal Covid-19 lainnya namun
                    berkontak
                    erat
                    dengan pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR, pasien disarankan melakukan karantina
                    mandiri, dan melakukan pemeriksaan antigen SARS-CoV-2 ulang atau pemeriksaan RT-PCR apabila timbul
                    gejala
                    pernafasan, demam,ataupun tidak bisa membau. <br>
                    &bull; Apabila pasien tidak memiliki gejala pernafasan atau gejala tipikal Covid-19 lainnya dan
                    tidak
                    berkontak
                    erat
                    dengan pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR, pasien tidak perlu melakukan karantina
                    mandiri,
                    kemungkinan besar pasien tidak terinfeksi SARS-CoV-2.
                </td>
            </tr>
        </table>
    @endif

    {{-- pagebreak next pemeriksaan ANTIBODY --}}
    @if (count($data_permohonan_uji_parameter_klinik_antibody) > 0)
        <div class="page_break"></div>
        <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
            <thead>
                <tr>
                    <th style="width: 20%">JENIS PEMERIKSAAN</th>
                    <th style="width: 15%">HASIL</th>
                    <th style="width: 15%">NILAI RUJUKAN</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data_permohonan_uji_parameter_klinik_antibody as $key_pupk => $item_pupk)
                    @if (count($item_pupk->parametersatuanklinik->parametersubsatuanklinik) < 1)
                        <tr>
                            <th style="width: 20%">
                                {{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}
                            </th>

                            <td style="width: 15%; text-align: center">
                                {{ $item_pupk->hasil_permohonan_uji_parameter_klinik ?? '-' }}
                            </td>

                            <td style="width: 15%; text-align: center">
                                {{ ($item_pupk->baku_mutu_permohonan_uji_parameter_klinik != null
                                        ? $item_pupk->baku_mutu_permohonan_uji_parameter_klinik
                                        : $item_pupk->bakumutu != null)
                                    ? $item_pupk->bakumutu->nilai_baku_mutu
                                    : '-' }}
                            </td>
                        </tr>
                    @else
                        <tr>
                            <th colspan="6">{{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}:
                                ~</th>
                        </tr>

                        @foreach ($item_pupk->parametersatuanklinik->parametersubsatuanklinik as $key_pssk => $item_pssk)
                            <tr>
                                <td style="width: 20%">
                                    <p style="padding-left: 30px">{{ $item_pssk->name_parameter_sub_satuan_klinik }} ~
                                    </p>
                                </td>

                                <td style="width: 15%; text-align: center">
                                    {{ $item_pssk->permohonanujisubparameterklinik->hasil_permohonan_uji_sub_parameter_klinik ?? '-' }}
                                </td>

                                <td style="width: 15%; text-align: center">
                                    {{ $item_pssk->permohonanujisubparameterklinik != null
                                        ? $item_pssk->permohonanujisubparameterklinik->baku_mutu_permohonan_uji_sub_parameter_klinik
                                        : ($item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik != null
                                            ? $item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik
                                            : '-') }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td>Catatan</td>
            </tr>

            <tr>
                <td style="width: 10px; vertical-align: top">A.</td>
                <td style="text-align: left; padding-right: 20px">
                    Apabila rapid test Antibodi SARS-CoV-2 Reaktif <br>
                    Hasil IgM Positif <br>
                    &bull; Kemungkinan Terinfeksi SARS CoV-2 jika disertai gejala infeksi virus acut atau bisa juga
                    reasi virus
                    lain
                    <br>
                    Hasil IgM dan IgG Positif <br>
                    &bull; Menandakan keberadaan antibodi IgG dan IgM dari SARS C-Cov-2 dapat menunjukan Covid 19 atau
                    positif
                    palsu
                </td>
            </tr>

            <tr>
                <td style="width: 10px"></td>
                <td style="text-align: left; padding-right: 20px">
                    Saran: <br>
                    &bull; Segera melakukan pemeriksaan konfirmasi dengan RT-PCR <br>
                    &bull; Melakukan karantina atau isolasi sesuai dengan kriteria <br>
                    &bull; Menerapkan PHBS (perilaku hidup bersih dan sehat, mencuci tangan, menerapkan etika batuk,
                    menggunakan
                    masker,
                    menjaga stamina) dan physical distancing
                </td>
            </tr>

            <br>

            <tr>
                <td style="width: 10px; vertical-align: top">B.</td>
                <td style="text-align: left; padding-right: 20px">
                    Apabila rapid test Antibodi SARS-CoV-2 Non Reaktif <br>
                    Kemungkinan <br>
                    &bull; Tidak terinfeksi SARS CoV-2<br>
                    &bull; Antibodi tidak terbentuk <br>
                </td>
            </tr>

            <tr>
                <td style="width: 10px"></td>
                <td style="text-align: left; padding-right: 20px">
                    Saran: <br>
                    &bull; Apabila pasien memiliki gejala pernafasan , atau gejala tipikal Covid-19 lainnya dan
                    berkontak erat
                    dengan
                    pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR,pasien tetap harus melakukan karantina mandiri
                    dan
                    melakukan pemeriksaan antigen SARS-CoV-2 ulang dalam 5-7 hari, atau pemeriksaan RT-PCR. <br>
                    &bull; Apabila pasien tidak memiliki gejala pernafasan , atau gejala tipikal Covid-19 lainnya namun
                    berkontak
                    erat
                    dengan pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR, pasien disarankan melakukan karantina
                    mandiri, dan melakukan pemeriksaan antigen SARS-CoV-2 ulang atau pemeriksaan RT-PCR apabila timbul
                    gejala
                    pernafasan, demam,ataupun tidak bisa membau. <br>
                    &bull; Apabila pasien tidak memiliki gejala pernafasan atau gejala tipikal Covid-19 lainnya dan
                    tidak
                    berkontak
                    erat
                    dengan pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR, pasien tidak perlu melakukan karantina
                    mandiri,
                    kemungkinan besar pasien tidak terinfeksi SARS-CoV-2.
                </td>
            </tr>
        </table>
    @endif

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px">
        <tr>
            <td style="width: 35%" style="text-align: left">Mengetahui:</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>PENANGGUNGJAWAB <br> LAB. KLINIK</td>
            <td style="text-align: right">PETUGAS PEMERIKSA</td>
        </tr>

        <tr>
            <td style="height: 50px; vertical-align: bottom">........................................</td>
            <td style="height: 50px; vertical-align: bottom; text-align: right">
                {{ $item_permohonan_uji_klinik->analis->name ?? '........................................' }}
                <br> NIP. {{ $item_permohonan_uji_klinik->analis->nip_users ?? '-' }}
            </td>
        </tr>
    </table>
</body>

</html>
