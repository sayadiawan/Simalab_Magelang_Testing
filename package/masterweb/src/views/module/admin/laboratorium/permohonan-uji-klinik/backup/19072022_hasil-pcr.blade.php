<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik - {!! $no_LHU !!}</title>
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
            <td style="text-align: center">
                <h3><strong>SURAT KETERANGAN</strong></h3>
            </td>
        </tr>

        <tr>
            <td style="text-align: center">
                Nomor: {!! $no_LHU !!}
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="4">Yang bertanda tangan di bawah ini:</td>
        </tr>

        <tr>
            <td width="5%">1.</td>
            <td style="width: 20%">Nama</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->dokter->name }}</td>
        </tr>

        <tr>
            <td width="5%">2.</td>
            <td style="width: 20%">NIP</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->dokter->nip_users }}</td>
        </tr>

        <tr>
            <td width="5%">3.</td>
            <td style="width: 20%">Jabatan</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->div_dept_permohonan_uji_klinik }}</td>
        </tr>
    </table>

    <br>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>Dengan ini memberi keterangan bahwa:</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="5%">1.</td>
            <td style="width: 20%">Nama</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
        </tr>

        <tr>
            <td width="5%">2.</td>
            <td style="width: 20%">Jenis Kelamin</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>

        <tr>
            <td width="5%">3.</td>
            <td style="width: 20%">Umur</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-' }} Tahun
                {{ $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik ?? '-' }} Bulan
                {{ $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik ?? '-' }} Hari</td>
        </tr>

        <tr>
            <td width="5%">4.</td>
            <td style="width: 20%">Alamat</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->pasien->alamat_pasien ?? '-' }}</td>
        </tr>

        <tr>
            <td width="5%">5.</td>
            <td style="width: 20%">NIK</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->pasien->nik_pasien ?? '-' }}</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="padding-right: 20px">
                Telah dilakukan pemeriksaan laboratorium dengan metode Pemeriksaan RT-PCR di Laboratorium rujukan dengan
                hasil sebagai berikut :
            </td>
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
                <th style="width: 20%">JENIS PEMERIKSAAN</th>
                <th style="width: 20%">TANGGAL DIPERIKSA</th>
                <th style="width: 20%">HASIL</th>
                <th style="width: 20%">NILAI RUJUKAN</th>
                <th style="width: 20%">LABORATORIUM PEMERIKSAAN</th>
            </tr>
        </thead>

        <tbody>
            @php
                $no = 0;
            @endphp
            @foreach ($data_permohonan_uji_parameter_klinik as $key_pupk => $item_pupk)
                @if (count($item_pupk->parametersatuanklinik->parametersubsatuanklinik) < 1)
                    <tr>
                        <th style="width: 20%; text-align: center">
                            {{ $item_pupk->parametersatuanklinik->name_parameter_satuan_klinik }}
                        </th>

                        <td style="width: 20%; text-align: center">
                            {{ $tgl_pengujian ?? '-' }}
                        </td>

                        <td style="width: 20%; text-align: center">
                            {{ $item_pupk->hasil_permohonan_uji_parameter_klinik ?? '-' }}
                        </td>

                        <td style="width: 20%; text-align: center">
                            {{ ($item_pupk->baku_mutu_permohonan_uji_parameter_klinik != null
                                    ? $item_pupk->baku_mutu_permohonan_uji_parameter_klinik
                                    : $item_pupk->bakumutu != null)
                                ? $item_pupk->bakumutu->nilai_baku_mutu
                                : '-' }}
                        </td>

                        <td style="width: 20%">
                            -
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
                            <td style="width: 20%; text-align: center">
                                <p style="padding-left: 30px">{{ $item_pssk->name_parameter_sub_satuan_klinik }} ~</p>
                            </td>

                            <td style="width: 20%; text-align: center">
                                {{ $tgl_pengujian ?? '-' }}
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

                            <td style="width: 20%; text-align: center">
                                -
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

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead>
            <tr>
                <th style="width: 20%">CT</th>
                <th style="width: 20%">HASIL</th>
                <th style="width: 20%">RUJUKAN</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Gen 1 : ORF1ab</td>
                <td></td>
                <td rowspan="2" style="text-align: center">>36</td>
            </tr>
            <tr>
                <td>Gen 2 : E</td>
                <td></td>
            </tr>
        </tbody>
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

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>Yang bersangkutan dianjurkan untuk tetap menerapkan protokol kesehatan.</td>
        </tr>

        <tr>
            <td>Demikian Surat Keterangan ini dibuat untuk dapat digunakan sebagaimana mestinya</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px">
        <tr>
            <td style="text-align: right">—, {{ fdate_sas(date('Y-m-d'), 'DDMMYYYY') }}</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px">
        <tr>
            <td style="width: 35%" style="text-align: left">Mengetahui:</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>KEPALA DINAS KESEHATAN <br> KABUPATEN BOYOLALI</td>
            <td style="text-align: right">PENANGGUNG JAWAB LAB KLINIK <br> UPT. LABKES DINKES KAB. BOYOLALI</td>
        </tr>

        <tr>
            <td style="height: 100px; vertical-align: bottom">
                PURWATI, SKM, M.KES <br> Pembina <br> NIP. 19730723 199303 2 005
            </td>
            <td style="height: 100px; vertical-align: bottom; text-align: right">
                dr.EMA NUR FITRIANA, M.Biomed <br> Penata Muda Tk.I <br> NIP. 19910323 201903 2 021
            </td>
        </tr>
    </table>
</body>

</html>
