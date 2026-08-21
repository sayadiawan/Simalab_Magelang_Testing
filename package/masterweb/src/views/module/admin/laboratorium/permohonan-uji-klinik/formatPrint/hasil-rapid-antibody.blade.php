<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik - {!! $no_LHU !!}</title>
    <link rel="shortcut icon" href="">
    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint._head_style')
</head>

<body>
    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint._head_kop')

    <div class="header" style="text-align: center; line-height: 1.5">
        <h1 class="nospace" style="font-size: 1.6em; text-decoration: underline;">
            SURAT KETERANGAN DOKTER
        </h1>
        <p class="nospace"> No: <b> {!! $no_LHU !!} </b> </p>
    </div>
    <br>

    <div style="margin-left: 3em">
        <p class="nospace"> Yang bertanda tangan dibawah ini adalah saya : </p>
        <table style="margin-left: 3em">
            <tr>
                <td> Nama </td>
                <td> : </td>
                <td> dr. MUHARYATI </td>
            </tr>
            <tr>
                <td> NIP </td>
                <td> : </td>
                <td> 197211062002122003 </td>
            </tr>
            <tr>
                <td> Pekerjaan </td>
                <td> : </td>
                <td> Dokter umum di Laboratorium Kesehatan Kabupaten Boyolali </td>
            </tr>
        </table>
    </div>
    <br>

    <div style="margin-left: 3em">
        <p class="nospace"> Dengan ini menyatakan bahwa pada hari Jumat, 2 Februari 2024 telah memeriksa : </p>
        <table style="margin-left: 3em">
            <tr>
                <td> Nama </td>
                <td> : </td>
                <td> Tn. RADITYA ALFARA </td>
            </tr>
            <tr>
                <td> Tempat/Tgl lahir </td>
                <td> : </td>
                <td> Bagansiapiapi, 3 Januari 2001 </td>
            </tr>
            <tr>
                <td> Alamat </td>
                <td> : </td>
                <td> Mulyorejo RT 007 RW 003, Doplang, Teras, Boyolali </td>
            </tr>
            <tr>
                <td> Tgl pemeriksaan </td>
                <td> : </td>
                <td> 2 Februari 2024 </td>
            </tr>
        </table>
    </div>


    <div class="">
        <p style="text-indent: 3em"> Telah dilakukan pemeriksaan <strong>Zat Adiktif/Narkoba</strong> pada
            <strong>Urine</strong> yang
            bersangkutandengan metode <strong>Rapid Test Drug Screen Panel MD 6 in 1</strong> dengan hasil sebagai
            berikut :
        </p>
        <table style="table-layout:auto">
            @foreach (range(1, 6) as $range)
                <tr>
                    <td style="width: 2em"> {{ $range }}. </td>
                    <td><b> AMPHETAMINE </b></td>
                    <td style="width: 2em; text-align: center"> : </td>
                    <td> NEGATIF </td>
                </tr>
            @endforeach
        </table>
    </div>

    <p> Kesimpulan : Yang bersangkutan <strong>BEBAS DARI ZAT ADIKTIF</strong> tersebut. </p>

    <p> Demikian surat keterangan ini dibuat dengan sebenar-benarnya agar dapat dipergunakan sebagaimana mestinya. </p>

    <div class="" style="float: right; text-align: center; margin-right: 3em">
        <p> —, 2 Februari 2024 </p>
        <br>
        <br>
        <br>
        <p><u> dr. MUHARYATI </u></p>
    </div>
    <div class="clearfix"></div>

    <div class="" style="display: none">

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="4"> </td>
            </tr>

            <tr>
                <td width="2%">1.</td>
                <td style="width: 20%">Tanggal dan Waktu</td>
                <td width="1%">:</td>
                <td>{{ $tgl_pengujian }}</td>
            </tr>

            <tr>
                <td width="2%">2.</td>
                <td style="width: 20%">Tempat</td>
                <td width="1%">:</td>
                <td>UPT Laboratorium Kesehatan BOYOLALI</td>
            </tr>
        </table>
        <br>
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="4">Telah dilakukan pemeriksaan dengan metode Rapid test Covid-19 Antibodi SARS-CoV-2:
                </td>
            </tr>

            <tr>
                <td width="2%">1.</td>
                <td style="width: 20%">Nama</td>
                <td width="1%">:</td>
                <td>{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
            </tr>

            <tr>
                <td width="2%">2.</td>
                <td style="width: 20%">Jenis Kelamin</td>
                <td width="1%">:</td>
                <td>{{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>

            <tr>
                <td width="2%">3.</td>
                <td style="width: 20%">Umur</td>
                <td width="1%">:</td>
                <td>{{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-' }} Tahun
                    {{ $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik ?? '-' }} Bulan
                    {{ $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik ?? '-' }} Hari</td>
            </tr>

            <tr>
                <td width="2%">4.</td>
                <td style="width: 20%">Alamat</td>
                <td width="1%">:</td>
                <td>{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}</td>
            </tr>

            <tr>
                <td width="2%">5.</td>
                <td style="width: 20%">NIK</td>
                <td width="1%">:</td>
                <td>{{ $item_permohonan_uji_klinik->pasien->nik_pasien ?? '-' }}</td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 5px">
            <tr>
                <td style="text-align: center">
                    <h2>HASIL PEMERIKSAAN</h2>
                </td>
            </tr>
            <tr>
                <td>
                    <i>(<span style="color: red">*</span>) Hasil diluar nilai rujukan</i>
                </td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
            <thead>
                <tr>
                    <th style="width: 20%">JENIS PEMERIKSAAN</th>
                    <th style="width: 15%; text-align: center;">HASIL</th>
                    <th style="width: 15%; text-align: center;">NILAI RUJUKAN</th>
                </tr>
            </thead>

            <tbody>
                {{-- panggil semua paket yang ada untuk mengeluarkan parameter satuan dan parameter sub satuan --}}
                @if (count($arr_permohonan_parameter) > 0)
                    {{-- mengeluarkan data semua permhonanuji yang ada di tb_permohonan_uji_paket_klinik --}}
                    @foreach ($arr_permohonan_parameter as $key_paket => $item_paket)
                        {{-- pada analis tidak memerlukan kondisi untuk mengeluarkan tipe paket atau custom --}}
                        {{-- kondisi apkah ada data paket yang memiliki parameter satuan --}}
                        @if (count($item_paket['data_permohonan_uji_satuan_klinik']) > 0)
                            {{-- mapping data parameter satuan dari paket yang dipilih --}}
                            @foreach ($item_paket['data_permohonan_uji_satuan_klinik'] as $key_satuan_klinik => $item_satuan_klinik)
                                {{-- pada analis hanya mengeluarkan kondisi apakah parameter satuan memiliki data subparameter atau hanya
        parameter satuan saja --}}
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

                                    {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan
        --}}
                                    @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                                        <tr>
                                            <td style="width: 20%; text-align: center">
                                                <p style="padding-left: 30px">
                                                    {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }}
                                                    ~
                                                </p>
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

        @if ($item_permohonan_uji_klinik->permohonanujianalisklinik)
            @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik))
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

            @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik))
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

            @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik))
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
                    Apabila rapid test Antibodi SARS-CoV-2 Reaktif <br>
                    Hasil IgM Positif <br>
                    &bull; Kemungkinan Terinfeksi SARS CoV-2 jika disertai gejala infeksi virus acut atau bisa juga
                    reasi
                    virus lain
                    <br>
                    Hasil IgM dan IgG Positif <br>
                    &bull; Menandakan keberadaan antibodi IgG dan IgM dari SARS C-Cov-2 dapat menunjukan Covid 19 atau
                    positif palsu
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
                    berkontak
                    erat
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

        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 5px">
            <tr>
                <td style="text-align: left">Demikian surat keterangan ini dibuat untuk dapat digunakan sebagaimana
                    mestinya</td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 5px">
            <tr>
                <td style="text-align: right">—, {{ fdate_sas(date('Y-m-d'), 'DDMMYYYY') }}</td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 5px">
            <tr>
                <td style="width: 35%" style="text-align: left">Mengetahui:</td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td style="text-align: left">PENANGGUNGJAWAB LAB. KLINIK <br> UPT Estu Lentera Indo Teknologi</td>
                <td style="text-align: right">PETUGAS PEMERIKSA</td>
            </tr>

            <tr>
                <td style="height: 80px; vertical-align: bottom; text-align: left">
                    Estu Lentera Indo Teknologi <br> Pembina<br>
                    NIP. 19721106 200212 2 001
                </td>
                <td style="height: 80px; vertical-align: bottom; text-align: right">
                    {{ $item_permohonan_uji_klinik->name_analis_permohonan_uji_klinik ?? '' }} <br>
                    {{ 'NIP.' . ($item_permohonan_uji_klinik->nip_analis_permohonan_uji_klinik ?? '') }}
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
