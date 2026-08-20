<html lang="">

<head>
  @include('masterweb::template.admin.metadata')
  @yield('css')
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>LHU.ELIT-2002006-AP </title>
  <link rel="shortcut icon" href="">
  <link rel="stylesheet" href="dist/css/bootstrap.min.css">
  <style>
    body {
      padding-top: 50px;
    }

    .starter-template {
      padding: 40px 15px;
      text-align: center;
    }

    .batas {
      padding-top: 10px;
      padding-bottom: 10px;
    }

    table>tr>td {
      cell-padding: 5px !important;
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
      font-size: 10px;
      text-align: center
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row text-center">
      <div class="col-md-12"> <img src="{{ asset('assets/admin/images/kopnya.png') }}" class="img-fluid">
      </div>
    </div>
    <div class="row batas">
      <div class="col-md-2"></div>
      <div class="col-md-8">
        <center>
          <u>LAPORAN HASIL PENGUJIAN</u><br>
          No. LHU.{{ $sample->codesample_samples }}
        </center>
        <br>
        <br><br>
        <table class="table table-condensed table-striped">
          <tbody>

            <tr>
              <td colspan="3"><strong>I. UMUM</strong></td>

            </tr>
            <tr>
              <td>1.</td>
              <td>Nomor Sample</td>
              <td>: {{ $sample->codesample_samples }}</td>
            </tr>
            <tr>
              <td>2.</td>
              <td>Nama Pelanggan</td>
              <td>: {{ $customer->name_customer }}</td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td>3.</td>
              <td>Alamat</td>
              <td>: {{ $customer->address_customer }}</td>
            </tr>
            <tr>
              <td>4.</td>
              <td>Jenis Industri/Kegiatan Usaha</td>
              <td>: {{ $category->name_industry }}</td>
            </tr>
            <tr>
              <td>5.</td>
              <td>Jenis Sample</td>
              <td>: {{ $sample->typesample_samples }}</td>
            </tr>
            <tr>
              <td>6.</td>
              <td>Rntang uji</td>
              <td>: 2020-02-03</td>
            </tr>

            <tr>
              <td colspan="3"></td>
            </tr>

            <tr>
              <td colspan="3"><strong>II. DATA CONTOH UJI</strong></td>
            </tr>

            <tr>
              <td>1.</td>
              <td>Nama Pelanggan/Instansi Pengirim</td>
              <td>: {{ $customer->name_customer }}</td>
            </tr>

            <tr>
              <td>2.</td>
              <td>Alamat</td>
              <td>: {{ $customer->address_customer }}</td>
            </tr>

            <tr>
              <td>3.</td>
              <td>Petugas</td>
              <td>: DIANTAR</td>
            </tr>
            <tr>
              <td>4.</td>
              <td>Deskripsi Sample</td>
              <td>: <br>
                a. Jumlah Sampel : 1 (satu)
                b. Wadah Sampel : sesuai sni<br>
                c. Volume Sampel : ± 2500<br>
              </td>
            </tr>


            <tr>
              <td>5.</td>
              <td>Tanggal/Jam Pengambilan</td>
              <td>: 2020-02-03</td>
            </tr>
            <tr>
              <td>6.</td>
              <td>Tanggal/Jam Penerimaan di Lab.</td>
              <td>: 2020-02-02 00:00:00</td>
            </tr>


            <tr>
              <td>7.</td>
              <td>Lokasi/Titik Pengambilan</td>
              <td>: bak outlet</td>
            </tr>

            <tr>
              <td>8.</td>
              <td>Metode Pengambilan</td>
              <td>: Sesaat</td>
            </tr>
          </tbody>

        </table>
        <hr>

        <p>&nbsp;</p>




        <div class="row batas">

        </div>
        <div class="row batas">
          <div class="col-md-8"></div>
          <div class="col-md-4 text-center">
            KEPALA UPTD LABORATORIUM LINGKUNGAN <br>
            DLH KABUPATEN CILACAP
            <img src="{{ asset('assets/admin/images/ttd.jpg') }}" class="img-fluid" width="80%">
            <strong><u>YUNIATI ERLINA, S.SI</u></strong><br>
            PENATA TK.I<br>
            NIP. 19800331 200312 2 2 007
          </div>
        </div>
        <div class="row batas">
          <em><small>
              *) : Berdasarkan Peraturan Daerah Kabupaten Cilacap No. 17 Tahun 2018 tentang Peraturan Kedua atau
              Peraturan Daerah Kabupaten Cilacap No. 4 Tahun 2012 tentang Retribusi Pemakaian Kekayaan Daerah di
              Kabupaten Cilacap<br>
              **) : Peraturan Bupati Cilacap No. 99 Tahun 2019 tentang Standar Satuan Harga di Lingkungan Pemerintahan
              Kabupaten Cilacap Tahun 2020.<br>
              ***) : Biaya Pengambilan sampel Kualitas Lingkungan dan Transportasi tim pengambil sampel ditanggung pihak
              pemohon
            </small> </em>
        </div>
      </div>
      <div class="col-md-2"></div>
    </div>
  </div>
  <button class="btn btn-outline-danger" id="cetak" onclick="myFunction()">Print / PDF Halaman ini</button>
  <script>
    function myFunction() {
      window.print();
    }
  </script>

  @include('masterweb::template.admin.scripts')
  @yield('scripts')

</body>

</html>
