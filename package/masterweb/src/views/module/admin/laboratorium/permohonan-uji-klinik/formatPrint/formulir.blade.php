<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>FORMULIR - {{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</title>
    <link rel="shortcut icon" href="">
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
            margin: 20px 75px 20px 75px;
        }

        @font-face {
            font-family: "source_sans_proregular";
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            font-size: 11px;
        }

        body {
            font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
            font-size: 11px;
            text-align: justify;
            text-justify: inter-word;
        }

        .page_break {
            page-break-before: always;
        }

        .flex-container {
            display: flex !important;
            flex-wrap: nowrap !important;
        }

        .flex-container>div {
            width: 100px !important;
            margin: 10px !important;
        }

        .border {
            border: 1.5px solid black;
        }

        .v-align-top {
            vertical-align: top;
        }

        .checkbox {
            height: 10px;
            position: relative;
            bottom: 5px;
        }

        .blue-header {
            background-color: #3a95b5;
            color: white;
            font-weight: bold;
            letter-spacing: 1px;
            padding-left: 4px;
            height: 15px;
        }

        .text-center {
            text-align: center;
        }

        .td-header {
            font-family: "Times New Roman", Times, serif !important;
            font-weight: bold;
            text-align: center;
        }

        .table-consent td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 4px 2px 4px 2px;
            font-size: 12.4px;
        }

        .table-clear td {
            border: 0px;
            padding: 0px;
        }
    </style>
</head>

<body>

    <div style="margin-top: 60px;padding: 0px 55px 0px 55px;">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td class="td-header">
                    <span style="font-size: 15px;">
                        <u>INFORMED CONSENT</u>
                    </span>
                </td>
            </tr>
        </table>

        <table style="margin: 5px 0px 25px 0px;" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td class="td-header">
                    <span style="font-size: 15px;">
                        (PERSETUJUAN TINDAKAN MEDIK)
                    </span>
                </td>
            </tr>
        </table>

        <table style="font-size: 14px;font-family: 'Times New Roman', Times, serif !important;" width="100%"
            cellspacing="0" cellpadding="0">
            <tr>
                <td>Saya yang bertandatangan di bawah ini :</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="28%">Nama</td>
                            <td width="2%">:</td>
                            <td>......................................................</td>
                        </tr>
                        <tr>
                            <td>Umur/ Jenis Kelamin</td>
                            <td>:</td>
                            <td>......................................................</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>......................................................</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td style="text-align: justify;">
                    Menyatakan dengan sesungguhnya telah memberikan PERSETUJUAN untuk dilakukan
                    tindakan medis berupa pengambilan darah sebanyak 3cc/5cc/10cc di lengan tangan kanan/ kiri
                    terhadap diri saya sendiri*/ anak*/ Istri*/ Suami*/ Ayah*/ Ibu*/ Kakak*/ Adik*/ Cucu* saya
                    dengan identitas :
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="28%">Nama</td>
                            <td width="2%">:</td>
                            <td>......................................................</td>
                        </tr>
                        <tr>
                            <td>Umur/ Jenis Kelamin</td>
                            <td>:</td>
                            <td>......................................................</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>......................................................</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td style="text-align: justify;">
                    Yang tujuan, sifat dan perlunya tindakan medis tersebut di atas, serta resiko yang dapat
                    ditimbulkannya dan upaya mengatasinya telah cukup dijelaskan oleh tenaga medis dan telah
                    saya mengerti sepenuhnya.
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    Demikian persetujuan ini saya buat dengan penuh kesadaran dan tanpa paksaan.
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="50%">&nbsp;</td>
                            <td width="50%" class="text-center">
                                Magelang, ................................2024
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                Petugas,
                            </td>
                            <td class="text-center">
                                Yang Membuat Pernyataan,
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                (.....................................................)
                            </td>
                            <td class="text-center">
                                (.....................................................)
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="margin-top: 70px;" class="table-consent" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="5">
                    <table class="table-clear" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="5%"></td>
                            <td width="25%" style="border: 1px solid black;" class="text-center">LOGO</td>
                            <td class="text-center">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td
                                            style="
                      text-align: center;
                      font-weight: bold;
                      font-size: 13px;
                    ">
                                            PEMERINTAH KABUPATEN BOYOLALI
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="
                      text-align: center;
                      font-weight: bold;
                      font-size: 13px;
                    ">
                                            DINAS KESEHATAN
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="
                      text-align: center;
                      font-weight: bold;
                      font-size: 13px;
                    ">
                                            LABORATORIUM KESEHATAN
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="
                      text-align: center;
                      font-size: 12px;
                    ">
                                            Komplek Perkantoran Terpadu Kabupaten Boyolali
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="
                      text-align: center;
                      font-size: 12px;
                    ">
                                            Jalan Ahmad Yani No. 1, Siswodipuran, Boyolali 57311
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="5%"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    Hari/ Tanggal :
                </td>
                <td colspan="3">
                    No. Reg :
                </td>
            </tr>
            <tr>
                <td class="text-center">
                    Jenis kegiatan Lab PK
                </td>
                <td width="16%" class="text-center">
                    Jam Mulai
                </td>
                <td width="16%" class="text-center">
                    Jam Selesai
                </td>
                <td width="18%" class="text-center">
                    Nama Petugas
                </td>
                <td width="17%" class="text-center">
                    TTD
                </td>
            </tr>
            <tr>
                <td>
                    Pendaftaran/Registrasi
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    Pengambilan sampel
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    Pemeriksaan/Analitik
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    Input/Output Hasil Px
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    Verifikasi
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    Validasi
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <br><br><br><br><br>
    <br><br><br><br><br>
    <br><br><br><br><br>

    <div style="padding-top: 60px;">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="20%" class="border text-center">
                    <span>LOGO</span>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="td-header">
                                <span style="font-size: 15px;">
                                    PEMERINTAH KABUPATEN BOYOLALI
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-header">
                                <span style="font-size: 17px;">
                                    DINAS KESEHATAN
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-header">
                                <span style="font-size: 17px;">
                                    LABORATORIUM KESEHATAN
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-header">
                                <span style="font-size: 11px;">
                                    Komplek Perkantoran Terpadu Kabupaten Boyolali
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-header">
                                <span style="font-size: 11px;">
                                    Jl. Ahmad Yani No.1 Siswodipuran, Telp. (0276) 320855 Boyolali 57311
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-header">
                                <span style="font-size: 11px;">
                                    Provinsi Jawa Tengah, E-mail : labkes@boyolali.go.id
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="20%"></td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <hr style="border-top: 4px solid;">
                </td>
            </tr>
        </table>

        <table style="margin: 10px 0px 10px 0px;" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="
          font-size: 13px;
          font-weight: bold;
          text-align: center;
        ">
                    <u>FORMULIR PERMINTAAN PEMERIKSAAN KLINIK</u>
                </td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="40%" class="border">
                    <table width="100%" cellspacing="2" cellpadding="0">
                        <tr>
                            <td width="22%">
                                No. Lab
                            </td>
                            <td width="3%">:</td>
                            <td>
                                REG-12399-814791
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Diagnosa
                            </td>
                            <td>:</td>
                            <td>
                                Sakit biasa
                            </td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                DOKTER PENGIRIM
                            </td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                ( Fardannu Bimantara )
                            </td>
                            {{-- <td>
                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
              </td> --}}
                        </tr>

                        <tr>
                            <td>
                                No. HP
                            </td>
                            <td>:</td>
                            <td>
                                0839-3326-9018
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="60%">
                    <table width="100%" cellspacing="2" cellpadding="0">
                        <tr>
                            <td width="27%" style="padding-left: 10px;">
                                Nama Lengkap
                                <br>(Sesuai KTP)
                            </td>
                            <td width="3%">:<br>&nbsp;</td>
                            <td class="border" style="padding-left: 2px;">
                                @php
                                    $nama_lengkap = 'Tri Nugraha';
                                @endphp
                                {{ $nama_lengkap }}
                                @if (strlen($nama_lengkap) < 44)
                                    <br>&nbsp;
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Tanggal Lahir
                            </td>
                            <td>:</td>
                            <td class="border" style="padding-left: 2px;">
                                27 November 1998
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Jenis Kelamin
                            </td>
                            <td>:</td>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="10%">
                                            <input type="checkbox" class="checkbox" checked />
                                        </td>
                                        <td width="40%">Laki-laki</td>
                                        <td width="10%">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td width="40%">Perempuan</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                No. Tep
                            </td>
                            <td>:</td>
                            <td class="border" style="padding-left: 2px;">
                                0822-3456-2234
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Alamat
                                <br>&nbsp;
                                <br>&nbsp;
                            </td>
                            <td>:<br>&nbsp;<br>&nbsp;</td>
                            <td class="border" style="padding-left: 2px;">
                                @php
                                    $alamat = 'Semarang';
                                @endphp
                                {{ $alamat }}
                                @for ($i = 1; $i < 3; $i++)
                                    @if (strlen($alamat) < $i * 44)
                                        <br>&nbsp;
                                    @endif
                                @endfor
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>

        <br>
        <br>

        <table width="100%" cellspacing="10" cellpadding="0">
            <tr>
                <td class="blue-header">HEMATOLOGI</td>
                <td width="25%" class="blue-header">IMUNOSEROLOGI</td>
                <td width="25%" class="blue-header">URINALISA</td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Widal</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Urine Rutin</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Differensial Count (Hitung Jenis Lekosit)</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>HBsAg Strip</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Sedimen</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Laju Endap Darah (LED)</td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Test Kehamilan</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Golongan Darah A, B, O dan Rhesus</td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Mikro Albumin</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <br>

        <table width="100%" cellspacing="10" cellpadding="0">
            <tr>
                <td class="blue-header">KIMIA KLINIK</td>
                <td width="25%" class="blue-header">LAIN-LAIN</td>
                <td width="25%"></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <b>DIABETES</b>
                            </td>
                            <td>
                                <b>FAAL HATI</b>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Narkoba</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>Gula Darah Puasa *</td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>SGOT (AST)</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>........................</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>Gula Darah 2 Jam PP</td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>SGPT (ALT)</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>........................</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>Gula Darah Sewaktu</td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>........................</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>HbA1C</td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </td>
                <td class="blue-header">SPESIMEN</td>
                <td style="padding-left: 4px;">
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="26px">
                                <b>(*)</b>
                            </td>
                            <td>
                                <b>Puasa 8-12 Jam</b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <b>LEMAK</b>
                            </td>
                            <td>
                                <b>GINJAL</b>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Darah Beku</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" checked />
                                        </td>
                                        <td>Kolesterol Total</td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>Ureum</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>Darah Beku</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>HDL Kolesterol</td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" checked />
                                        </td>
                                        <td>Kreatinin</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>NaF</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>LDL Kolesterol</td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" checked />
                                        </td>
                                        <td>Asam Urat *</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" />
                            </td>
                            <td>EDTA</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

            <tr>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="60%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>Trigliserida</td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="30px">
                                <input type="checkbox" class="checkbox" checked />
                            </td>
                            <td>Urine</td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>
        </table>

        <br>
        <br>
        <br>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="30%">
                    <table width="100%" cellspacing="2" cellpadding="0">
                        <tr>
                            <td class="border">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="text-align: center">Catatan</td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="
                      padding-left: 2px;
                      padding-right: 2px;
                      padding-top: 5px;
                      font-size: 9px;
                    ">
                                            @php
                                                $catatan = 'Detak jantung tidak stabil';
                                            @endphp
                                            {{ $catatan }}
                                            @for ($i = 1; $i < 6; $i++)
                                                @if (strlen($catatan) < $i * 37)
                                                    <br>&nbsp;
                                                @endif
                                            @endfor
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="border">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="text-align: center">Riwayat Penggunaan Obat</td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="
                      padding-left: 2px;
                      padding-right: 2px;
                      padding-top: 5px;
                      font-size: 9px;
                    ">
                                            @php
                                                $riwayat_obat = '';
                                            @endphp
                                            {{ $riwayat_obat }}
                                            @for ($i = 1; $i < 5; $i++)
                                                @if (strlen($riwayat_obat) < $i * 37)
                                                    <br>&nbsp;
                                                @endif
                                            @endfor
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="70%">
                    <table width="100%" cellspacing="2" cellpadding="0">
                        <tr>
                            <td width="32%" style="padding-left: 10px;">
                                Tanggal Sampling
                            </td>
                            <td width="3%">:</td>
                            <td class="border" style="padding-left: 2px;">
                                23 Juni 2024
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Jam Sampling
                            </td>
                            <td>:</td>
                            <td class="border" style="padding-left: 2px;">
                                14:20
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Plebotomist
                            </td>
                            <td>:</td>
                            <td class="border" style="padding-left: 2px;">
                                -
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Vena Lengan Kanan / Kiri
                            </td>
                            <td>:</td>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="10%">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td width="40%">Kanan</td>
                                        <td width="10%">
                                            <input type="checkbox" class="checkbox" checked />
                                        </td>
                                        <td width="40%">Kiri</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Lokasi Lain
                            </td>
                            <td>:</td>
                            <td class="border" style="padding-left: 2px;">
                                -
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Paraf
                                <br>&nbsp;
                            </td>
                            <td>:<br>&nbsp;</td>
                            <td class="border" style="padding-left: 2px;">
                                <br>&nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">
                                Penanganan Hasil
                                <br>&nbsp;
                            </td>
                            <td>:<br>&nbsp;</td>
                            <td>
                                <table width="100%" cellspacing="2" cellpadding="0">
                                    <tr>
                                        <td width="10%">
                                            <input type="checkbox" class="checkbox" checked />
                                        </td>
                                        <td width="40%">Diambil Pasien</td>
                                        <td width="10%">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td width="40%">Dikirim ke Pasien</td>
                                    </tr>
                                    <tr>
                                        <td width="10%">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td width="40%">Diambil Dokter</td>
                                        <td width="10%">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td width="40%">Via Internet</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    </div>
</body>

</html>
