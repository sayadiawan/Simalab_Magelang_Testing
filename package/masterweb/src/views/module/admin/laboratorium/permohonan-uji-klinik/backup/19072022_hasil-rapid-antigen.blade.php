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
            <td colspan="4">Berdasarkan hasil pemeriksaan yang dilaksanakan pada:</td>
        </tr>

        <tr>
            <td width="5%">1.</td>
            <td style="width: 20%">Tanggal dan Waktu</td>
            <td width="1%">:</td>
            <td>{{ $tgl_pengujian }}</td>
        </tr>

        <tr>
            <td width="5%">2.</td>
            <td style="width: 20%">Tempat</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
    </table>

    <br>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>Telah dilakukan pemeriksaan dengan metode Rapid test Covid-19 Antigen SARS-CoV-2:</td>
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
            <td style="text-align: center">
                <h3><strong>HASIL PEMERIKSAAN</strong></h3>
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
                <th style="width: 15%">HASIL</th>
                <th style="width: 15%">NILAI RUJUKAN</th>
            </tr>
        </thead>

        <tbody>
            @php
                $no = 0;
            @endphp
            @foreach ($data_permohonan_uji_parameter_klinik as $key_pupk => $item_pupk)
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
                &bull; Apabila pasien memiliki gejala pernafasan , atau gejala tipikal Covid-19 lainnya dan berkontak
                erat
                dengan
                pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR,pasien tetap harus melakukan karantina mandiri dan
                melakukan pemeriksaan antigen SARS-CoV-2 ulang dalam 5-7 hari, atau pemeriksaan RT-PCR. <br>
                &bull; Apabila pasien tidak memiliki gejala pernafasan , atau gejala tipikal Covid-19 lainnya namun
                berkontak
                erat
                dengan pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR, pasien disarankan melakukan karantina
                mandiri, dan melakukan pemeriksaan antigen SARS-CoV-2 ulang atau pemeriksaan RT-PCR apabila timbul
                gejala
                pernafasan, demam,ataupun tidak bisa membau. <br>
                &bull; Apabila pasien tidak memiliki gejala pernafasan atau gejala tipikal Covid-19 lainnya dan tidak
                berkontak
                erat
                dengan pasien yang terkonfirmasi Covid-19 berdasarkan RT-PCR, pasien tidak perlu melakukan karantina
                mandiri,
                kemungkinan besar pasien tidak terinfeksi SARS-CoV-2.
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px">
        <tr>
            <td style="text-align: left">Demikian surat keterangan ini dibuat untuk dapat digunakan sebagaimana
                mestinya</td>
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
