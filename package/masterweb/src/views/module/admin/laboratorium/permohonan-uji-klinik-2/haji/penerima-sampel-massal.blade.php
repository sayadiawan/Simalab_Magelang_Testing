@extends('masterweb::template.admin.layout')

@section('title')
  Penerimaan Sampel Massal Haji
@endsection

@section('content')
  <link href="{{ asset('assets/admin/cdn-local/css/select2.min.css') }}" rel="stylesheet" />
  <script src="{{ asset('assets/admin/cdn-local/js/select2.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">

  <style>
    .info-card {
      background: linear-gradient(135deg, #0d7377 0%, #14919b 100%);
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
      border-bottom: 2px solid #14919b;
    }
    .badge-custom {
      background: #14919b;
      color: white;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
    }
    .quality-checkbox-group {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px 24px;
      background: #f8f9fa;
      border-radius: 10px;
      padding: 16px 20px;
      margin-top: 10px;
    }
    .quality-checkbox-col { display: flex; flex-direction: column; gap: 12px; }
    .quality-checkbox-group .form-check {
      display: flex;
      align-items: center;
      margin: 0;
      padding: 0;
    }
    .quality-checkbox-group .form-check-input {
      position: static;
      float: none;
      width: 18px;
      height: 18px;
      margin: 0 8px 0 0;
      cursor: pointer;
    }
    .pasien-chip {
      display: inline-block;
      background: #e8f6f6;
      border: 1px solid #b8e0e0;
      border-radius: 6px;
      padding: 4px 10px;
      margin: 0 6px 6px 0;
      font-size: 13px;
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
                <li class="breadcrumb-item active" aria-current="page"><span>Penerimaan Massal</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="info-card">
    <h4><i class="fa fa-users mr-2"></i>Penerimaan Sampel Massal — {{ $haji->nama_haji }}</h4>
    <p class="mb-0" style="opacity:.92;">
      Isi data penerimaan sekali, lalu diterapkan ke <strong>{{ count($selectedIds) }} pasien</strong> yang dipilih.
      Pastikan hanya pasien dengan status <em>Penerimaan Sample</em>.
    </p>
  </div>

  <form
    action="{{ route('elits-permohonan-uji-klinik-2.haji.store-penerima-sampel-massal', $haji->id_permohonan_uji_klinik_haji) }}"
    method="POST"
    id="form-penerima-massal">
    @csrf
    @method('PUT')

    @foreach ($selectedIds as $sid)
      <input type="hidden" name="selected_ids[]" value="{{ $sid }}">
    @endforeach

    <div class="data-card">
      <h5><i class="fa fa-list mr-2"></i>Pasien yang akan diterima ({{ $pasienList->count() }})</h5>
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
                  ?? ['label' => 'Penerimaan Sample', 'class' => 'badge-dark'];
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
      <h5><i class="fa fa-clipboard-check mr-2"></i>Data Penerimaan Sampel (berlaku untuk semua)</h5>

      @if (empty($jenis_sampel_array))
        <div class="alert alert-warning">
          <i class="fa fa-exclamation-triangle mr-1"></i>
          Jenis sampel belum terdeteksi dari pengambilan sample. Pastikan sampling sudah dilakukan, atau isi jenis secara manual di bawah.
        </div>
        @php $jenis_sampel_array = ['Darah']; @endphp
      @endif

      @foreach ($jenis_sampel_array as $index => $sampel_type)
        <div style="background: #f0fafb; border-radius: 10px; padding: 18px; margin-bottom: 16px; border-left: 4px solid #14919b;">
          <h6 style="color: #0d7377; font-weight: 600; margin-bottom: 16px;">
            <i class="fa fa-vial mr-2"></i>
            Sampel: <span class="badge-custom">{{ $sampel_type }}</span>
          </h6>

          <div class="form-group">
            <label>PENERIMAAN SAMPEL <span style="color:red">*</span></label>
            <textarea class="form-control" name="penerimaan_sampel[{{ $sampel_type }}]" rows="3" required
              placeholder="Catatan penerimaan untuk {{ $sampel_type }} (berlaku semua pasien terpilih)"></textarea>
          </div>

          <div class="form-group">
            <label>VOLUME SAMPEL <span style="color:red">*</span></label>
            <input type="text" class="form-control" name="volume_sampel[{{ $sampel_type }}]" required
              placeholder="Contoh: 5 ml">
          </div>

          @if (strtolower(trim($sampel_type)) != 'urine')
            <div class="form-group mb-0">
              <label>KUALITAS SAMPEL <span style="color:red">*</span></label>
              <div class="quality-checkbox-group">
                <div class="quality-checkbox-col">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                      id="kualitas_lisis_{{ $index }}" value="Lisis">
                    <label class="form-check-label" for="kualitas_lisis_{{ $index }}">Lisis</label>
                  </div>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                      id="kualitas_ikterik_{{ $index }}" value="Ikterik">
                    <label class="form-check-label" for="kualitas_ikterik_{{ $index }}">Ikterik</label>
                  </div>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                      id="kualitas_lipemik_{{ $index }}" value="Lipemik">
                    <label class="form-check-label" for="kualitas_lipemik_{{ $index }}">Lipemik</label>
                  </div>
                </div>
                <div class="quality-checkbox-col">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                      id="kualitas_cukup_{{ $index }}" value="Cukup" checked>
                    <label class="form-check-label" for="kualitas_cukup_{{ $index }}">Cukup</label>
                  </div>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                      id="kualitas_beku_{{ $index }}" value="Beku">
                    <label class="form-check-label" for="kualitas_beku_{{ $index }}">Beku</label>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      @endforeach
    </div>

    <div class="form-section">
      <h5><i class="fa fa-clock mr-2"></i>Data Penerima Sampel</h5>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="jam_penerima">JAM PENERIMAAN SAMPEL <span style="color:red">*</span></label>
            <input type="text" class="form-control" name="jam_penerima" id="jam_penerima" required
              placeholder="HH:mm" autocomplete="off" value="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="nama_petugas_penerima">NAMA PETUGAS PENERIMA <span style="color:red">*</span></label>
            <select class="form-control" name="nama_petugas_penerima" id="nama_petugas_penerima" required>
              <option value="">-- Pilih Petugas --</option>
              @foreach ($petugas_penerima_sampel as $nama)
                <option value="{{ $nama }}" {{ request('petugas') == $nama ? 'selected' : '' }}>{{ $nama }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    <input type="hidden" name="is_selesai" id="is_selesai_penerima" value="0">

    <div class="row mb-4">
      <div class="col-12 text-right">
        <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}"
          class="btn btn-light mr-2">
          <i class="fa fa-arrow-left mr-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-primary mr-2 btn-simpan-massal">
          <i class="fa fa-save mr-1"></i> Simpan Massal
        </button>
        <button type="button" class="btn btn-success btn-selesai-massal">
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
      flatpickr('#jam_penerima', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        defaultHour: new Date().getHours(),
        defaultMinute: new Date().getMinutes(),
      });

      if (!$('#jam_penerima').val()) {
        var now = new Date();
        $('#jam_penerima').val(
          String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0')
        );
      }

      function submitMassal(isSelesai) {
        if (!$('#jam_penerima').val() || !$('#nama_petugas_penerima').val()) {
          swal('Perhatian', 'Jam dan petugas penerima wajib diisi.', 'warning');
          return;
        }

        var count = {{ count($selectedIds) }};
        var label = isSelesai ? 'selesai (tandai tahap penerimaan selesai)' : 'simpan';
        var redirectUrl = @json(route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji));

        swal({
          title: 'Konfirmasi',
          text: 'Terapkan penerimaan ke ' + count + ' pasien dan ' + label + '?',
          icon: 'warning',
          buttons: true,
        }).then(function (ok) {
          if (!ok) return;

          $('#is_selesai_penerima').val(isSelesai ? '1' : '0');

          var $btn = $('.btn-simpan-massal, .btn-selesai-massal').prop('disabled', true);

          $.ajax({
            url: $('#form-penerima-massal').attr('action'),
            type: 'POST',
            data: $('#form-penerima-massal').serialize(),
            dataType: 'json',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              $('#is_selesai_penerima').val('0');
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
                swal('Gagal', response.pesan || 'Penerimaan massal gagal.', 'warning');
              }
            },
            error: function (xhr) {
              $('#is_selesai_penerima').val('0');
              $btn.prop('disabled', false);
              var msg = 'Sistem gagal menyimpan penerimaan massal.';
              if (xhr.responseJSON && xhr.responseJSON.pesan) {
                msg = xhr.responseJSON.pesan;
              } else if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
              }
              swal('Error', msg, 'error');
            }
          });
        });
      }

      $('.btn-simpan-massal').on('click', function () { submitMassal(false); });
      $('.btn-selesai-massal').on('click', function () { submitMassal(true); });
    });
  </script>
@endsection
