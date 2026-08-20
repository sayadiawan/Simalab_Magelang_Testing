@extends('masterweb::template.admin.layout')

@section('title')
  Permohonan Uji Klinik Haji
@endsection

@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                <li class="breadcrumb-item"><a href="">Laboratorium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Permohonan Uji Klinik Haji</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
      <div class="col-12">
          <div class="alert alert-success">
              {{ session('success') }}
          </div>
      </div>
  @endif
  @if (session('error'))
      <div class="col-12">
          <div class="alert alert-danger">
              {{ session('error') }}
          </div>
      </div>
  @endif

  <div class="card">
    <div class="card-body">
      <div class="d-flex">
        <div class="mr-auto p-2"></div>
        <div class="p-2">
          <a href="{{ route('elits-permohonan-uji-klinik-2.haji.create-new') }}">
            <button type="button" class="btn btn-info btn-icon-text">
              Tambah Data
              <i class="fa fa-plus btn-icon-append"></i>
            </button>
          </a>
        </div>
      </div>

      <form method="get" action="{{ route('elits-permohonan-uji-klinik-2.haji') }}" class="mb-3">
        <div class="row align-items-end">
          <div class="col-md-3 col-sm-6 mb-2">
            <label class="form-label mb-1" for="date_start">Tanggal Pendaftaran Mulai</label>
            <input type="date" name="date_start" id="date_start" class="form-control form-control-sm"
              value="{{ $date_start ?? '' }}">
          </div>
          <div class="col-md-3 col-sm-6 mb-2">
            <label class="form-label mb-1" for="date_end">Tanggal Pendaftaran Akhir</label>
            <input type="date" name="date_end" id="date_end" class="form-control form-control-sm"
              value="{{ $date_end ?? '' }}">
          </div>
          <div class="col-md-6 col-sm-12 mb-2">
            <button type="submit" class="btn btn-primary btn-sm mr-1">
              <i class="fa fa-filter"></i> Terapkan
            </button>
            <a href="{{ route('elits-permohonan-uji-klinik-2.haji') }}" class="btn btn-outline-secondary btn-sm">
              <i class="fa fa-times"></i> Reset
            </a>
            <small class="text-muted d-block d-md-inline ml-md-2">Filter berdasarkan tanggal pendaftaran (tgl haji)</small>
          </div>
        </div>
      </form>

      <div class="row">
        @if (session('status'))
          <div class="alert alert-success">
            {{ session('status') }}
          </div>
        @endif

        <div class="col-12">
          <div class="table-responsive">
            <table id="order-listing" class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Pelanggan/Puskesmas</th>
                  <th>Tanggal Pendaftaran</th>
                  <th>Jumlah Pasien</th>
                  <th>Status Pembayaran</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $no = 1;
                @endphp

                @foreach ($data as $item)
                  <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->nama_haji }}</td>
                    <td>
                      @if (!empty($item->tgl_haji))
                        {{ \Carbon\Carbon::parse($item->tgl_haji)->isoFormat('D MMMM Y') }}
                      @elseif (!empty($item->created_at))
                        {{ \Carbon\Carbon::parse($item->created_at)->isoFormat('D MMMM Y') }}
                      @else
                        -
                      @endif
                    </td>
                    <td>{{ $item->jumlah_pasien ?? 0 }}</td>
                    <td>
                      @if(isset($item->status_pembayaran) && $item->status_pembayaran == 'Lunas')
                          <label class="badge badge-success badge-pill">Lunas</label>
                      @else
                          <label class="badge badge-danger badge-pill">Belum Lunas</label>
                      @endif
                    </td>
                    <td>
                      <div class="btn-group-vertical" role="group">
                        <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $item->id_permohonan_uji_klinik_haji) }}" class="btn btn-info btn-sm mb-1">
                          <i class="fa fa-list"></i> Daftar Pasien
                        </a>
                        <a href="{{ route('elits-permohonan-uji-klinik-2.haji.edit-customer-dokter', $item->id_permohonan_uji_klinik_haji) }}" class="btn btn-secondary btn-sm mb-1">
                          <i class="fa fa-edit"></i> Edit Customer & Dokter
                        </a>
                        <a href="{{ route('elits-permohonan-uji-klinik-2.haji.cetak-nota', $item->id_permohonan_uji_klinik_haji) }}" class="btn btn-primary btn-sm mb-1 btn-cetak-nota"
                            data-name="{{ $item->nama_haji }}"
                            data-url-instansi="{{ route('elits-permohonan-uji-klinik-2.haji.cetak-nota', $item->id_permohonan_uji_klinik_haji) }}"
                            data-url-satuan="{{ route('elits-permohonan-uji-klinik-2.haji.cetak-nota-per-pasien', $item->id_permohonan_uji_klinik_haji) }}">
                          <i class="fa fa-file-invoice"></i> Cetak Nota
                        </a>
                        <div class="btn-group mb-1">
                          <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-file-excel"></i> Export
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.haji.export-pasien-haji', $item->id_permohonan_uji_klinik_haji) }}">
                              <i class="fa fa-users"></i> Daftar Pasien (Nama, Usia, Alamat)
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.haji.export-rekap-haji', $item->id_permohonan_uji_klinik_haji) }}">
                              <i class="fa fa-file-excel"></i> Rekap Hasil — Kimia Darah
                            </a>
                            <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.haji.export-rekap-haji-urin-rutin', $item->id_permohonan_uji_klinik_haji) }}">
                              <i class="fa fa-file-excel"></i> Rekap Hasil — Urin Rutin
                            </a>
                          </div>
                        </div>
                        <a href="#hapus" type="button" class="btn btn-danger btn-sm btn-hapus"
                            data-id="{{ $item->id_permohonan_uji_klinik_haji }}"
                            data-name="{{ $item->nama_haji }}">
                            <i class="fa fa-trash"></i> Hapus
                        </a>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SweetAlert JS -->
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <!-- Bootstrap JS -->
  <script src="{{asset('assets/admin/cdn-local/js/jquery-3.5.1.slim.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/popper.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/bootstrap4.min.js')}}"></script>

  <script>
    $(function() {
      $('#order-listing').on('click', '.btn-cetak-nota', function(e) {
        e.preventDefault();

        var name = $(this).data('name');
        var urlInstansi = $(this).data('url-instansi');
        var urlSatuan = $(this).data('url-satuan');

        swal({
          title: "Cetak Nota",
          text: "Pilih format nota untuk rombongan : " + name + "\n\nSatuan = nota per pasien (2 pasien per lembar), Satu Instansi = 1 nota gabungan.",
          icon: "info",
          buttons: {
            cancel: "Batal",
            satuan: {
              text: "Satuan (per pasien)",
              value: "satuan",
              className: "btn btn-info"
            },
            instansi: {
              text: "Satu Instansi",
              value: "instansi",
              className: "btn btn-primary"
            }
          }
        }).then(function(pilihan) {
          if (pilihan === 'satuan') {
            window.open(urlSatuan, '_blank');
          } else if (pilihan === 'instansi') {
            window.open(urlInstansi, '_blank');
          }
        });
      });

      $('#order-listing').on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        var kode = $btn.data('id');
        var name = $btn.data('name');
        var $row = $btn.closest('tr');

        swal({
            title: "Apakah anda yakin?",
            text: "Untuk menghapus data : " + name,
            icon: "warning",
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              $.ajax({
                type: 'GET',
                url: '{{ url('/elits-permohonan-uji-klinik-2/destroy-haji') }}/' + kode,
                dataType: 'json',
                cache: false,
                timeout: 120000,
                success: function(response) {
                  if (response && response.status == true) {
                    try {
                      if ($.fn.DataTable && $.fn.DataTable.isDataTable('#order-listing')) {
                        $('#order-listing').DataTable().row($row).remove().draw(false);
                      } else {
                        $row.remove();
                      }
                    } catch (err) {
                      $row.remove();
                    }

                    swal({
                        title: "Success!",
                        text: response.pesan,
                        icon: "success"
                      })
                      .then(function() {
                        document.location = '{{ url('/elits-permohonan-uji-klinik-2/haji') }}?_ts=' + Date.now();
                      });
                  } else {
                    swal("Hapus Data Gagal!", {
                      icon: "warning",
                      title: "Failed!",
                      text: (response && response.pesan) ? response.pesan : "Gagal menghapus data",
                    });
                  }
                },
                error: function(xhr) {
                  var msg = (xhr.responseJSON && xhr.responseJSON.pesan)
                    ? xhr.responseJSON.pesan
                    : "System tidak dapat menghapus data!";
                  swal("ERROR", msg, "error");
                }
              });
            } else {
              swal("Cancelled", "Hapus data dibatalkan!", "error");
            }
          });
      });
      
    })
  </script>
@endsection
