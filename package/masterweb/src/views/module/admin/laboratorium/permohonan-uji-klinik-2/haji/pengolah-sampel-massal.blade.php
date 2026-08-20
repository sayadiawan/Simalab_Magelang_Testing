@extends('masterweb::template.admin.layout')

@section('title')
  Pengolah Sampel Massal Haji
@endsection

@section('content')
  <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">

  <style>
    .info-card {
      background: linear-gradient(135deg, #b5651d 0%, #d4915a 100%);
      border-radius: 12px;
      padding: 22px;
      color: white;
      margin-bottom: 22px;
    }
    .info-card h4 { color: white; margin-bottom: 8px; font-weight: 600; }
    .data-card, .form-section {
      background: #fff;
      border-radius: 12px;
      padding: 22px;
      margin-bottom: 18px;
      border: 1px solid #e9ecef;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .form-section h5, .data-card h5 {
      color: #495057;
      font-weight: 600;
      margin-bottom: 18px;
      padding-bottom: 10px;
      border-bottom: 2px solid #d4915a;
    }
  </style>

  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('elits-permohonan-uji-klinik-2.haji') }}">Permohonan Uji Klinik Haji</a></li>
                <li class="breadcrumb-item"><a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}">Daftar Pasien</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Pengolah Massal</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="info-card">
    <h4><i class="fa fa-microscope mr-2"></i>Pengolah Sampel Massal — {{ $haji->nama_haji }}</h4>
    <p class="mb-0" style="opacity:.92;">
      Isi jam &amp; petugas sekali, lalu diterapkan ke <strong>{{ count($selectedIds) }} pasien</strong> status Pemeriksaan.
    </p>
  </div>

  <form
    action="{{ route('elits-permohonan-uji-klinik-2.haji.store-pengolah-sampel-massal', $haji->id_permohonan_uji_klinik_haji) }}"
    method="POST"
    id="form-pengolah-massal">
    @csrf
    @method('PUT')

    @foreach ($selectedIds as $sid)
      <input type="hidden" name="selected_ids[]" value="{{ $sid }}">
    @endforeach

    <div class="data-card">
      <h5><i class="fa fa-list mr-2"></i>Pasien yang akan diolah ({{ $pasienList->count() }})</h5>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th style="width:50px;">No</th>
              <th>No. Lab / Spesimen</th>
              <th>Nama Pasien</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pasienList as $i => $item)
              @php
                $displayNoLab = ($klinikNumberSettings->is_nomor_lab_manual && $klinikNumberSettings->is_nomor_spesimen_manual
                  && !empty($item->nomor_lab_manual) && !empty($item->nomor_spesimen_manual))
                  ? $item->nomor_spesimen_manual . ' / ' . $item->nomor_lab_manual
                  : ($item->noregister_permohonan_uji_klinik ?? '-');
                $statusUi = $statusPengujianMap[$item->id_permohonan_uji_klinik]
                  ?? ['label' => 'Pemeriksaan', 'class' => 'badge-warning'];
              @endphp
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $displayNoLab }}</td>
                <td style="text-transform: uppercase;">{{ $item->pasien->nama_pasien ?? '-' }}</td>
                <td><label class="badge {{ $statusUi['class'] }} badge-pill">{{ $statusUi['label'] }}</label></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="form-section">
      <h5><i class="fa fa-clock mr-2"></i>Data Pengolah Sampel (berlaku untuk semua)</h5>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="jam_pengolah">JAM PENGOLAH SAMPEL <span style="color:red">*</span></label>
            <input type="text" class="form-control" name="jam_pengolah" id="jam_pengolah" required
              placeholder="HH:mm" autocomplete="off" value="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="nama_petugas_pengolah">NAMA PETUGAS PENGOLAH <span style="color:red">*</span></label>
            <select class="form-control" name="nama_petugas_pengolah" id="nama_petugas_pengolah" required>
              <option value="">-- Pilih Petugas --</option>
              @foreach ($petugas_pengolah_sampel as $nama)
                <option value="{{ $nama }}">{{ $nama }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-12 text-right">
        <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}"
          class="btn btn-light mr-2">
          <i class="fa fa-arrow-left mr-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-success btn-selesai-pengolah-massal">
          <i class="fa fa-check-circle mr-1"></i> Selesai Massal
        </button>
      </div>
    </div>
  </form>
@endsection

@section('scripts')
  <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
  <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.js') }}"></script>
  <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"></script>
  <script>
    $(function () {
      flatpickr('#jam_pengolah', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        defaultHour: new Date().getHours(),
        defaultMinute: new Date().getMinutes(),
      });

      if (!$('#jam_pengolah').val()) {
        var now = new Date();
        $('#jam_pengolah').val(
          String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0')
        );
      }

      $('.btn-selesai-pengolah-massal').on('click', function () {
        if (!$('#jam_pengolah').val() || !$('#nama_petugas_pengolah').val()) {
          swal('Perhatian', 'Jam dan petugas pengolah wajib diisi.', 'warning');
          return;
        }

        var count = {{ count($selectedIds) }};
        var redirectUrl = @json(route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji));

        swal({
          title: 'Konfirmasi',
          text: 'Tandai pengolah sampel selesai untuk ' + count + ' pasien?',
          icon: 'warning',
          buttons: true,
        }).then(function (ok) {
          if (!ok) return;

          var $btn = $('.btn-selesai-pengolah-massal').prop('disabled', true);

          $.ajax({
            url: $('#form-pengolah-massal').attr('action'),
            type: 'POST',
            data: $('#form-pengolah-massal').serialize(),
            dataType: 'json',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              $btn.prop('disabled', false);
              if (response.status == true) {
                swal({
                  title: 'Berhasil!',
                  text: response.pesan,
                  icon: 'success',
                }).then(function () {
                  document.location = response.redirect || redirectUrl;
                });
              } else {
                swal('Gagal', response.pesan || 'Pengolah massal gagal.', 'warning');
              }
            },
            error: function (xhr) {
              $btn.prop('disabled', false);
              var msg = 'Sistem gagal menyimpan pengolah massal.';
              if (xhr.responseJSON && xhr.responseJSON.pesan) {
                msg = xhr.responseJSON.pesan;
              }
              swal('Error', msg, 'error');
            }
          });
        });
      });
    });
  </script>
@endsection
