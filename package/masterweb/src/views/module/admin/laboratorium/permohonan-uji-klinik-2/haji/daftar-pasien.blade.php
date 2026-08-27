@extends('masterweb::template.admin.layout')

@section('title')
  Daftar Pasien Haji
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
                <li class="breadcrumb-item"><a href="{{ route('elits-permohonan-uji-klinik-2.haji') }}">Permohonan Uji Klinik Haji</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Daftar Pasien</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="card-title mb-0">Daftar Pasien - {{ $haji->nama_haji }}</h4>
        <div class="d-flex flex-wrap" style="gap: 8px;">
          @if ($canKlinikVerifikasi)
          <button type="button" id="btn-pengambilan-sample" class="btn btn-secondary" title="Pintasan untuk status Pengambilan Sample">
            <i class="fa fa-hand-paper"></i> Pengambilan Sample (<span id="count-pengambilan-sample">0</span>)
          </button>
          <button type="button" id="btn-penerimaan-massal" class="btn btn-info" title="Massal untuk status Penerimaan Sample">
            <i class="fa fa-flask"></i> Penerimaan Massal (<span id="count-penerimaan-massal">0</span>)
          </button>
          <button type="button" id="btn-pengolah-massal" class="btn btn-warning" title="Massal untuk status Pemeriksaan">
            <i class="fa fa-microscope"></i> Pengolah Massal (<span id="count-pengolah-massal">0</span>)
          </button>
          @endif
          <a href="{{ route('elits-permohonan-uji-klinik-2.haji.edit-customer-dokter', $haji->id_permohonan_uji_klinik_haji) }}" class="btn btn-secondary" title="Ubah nama customer & dokter pengirim untuk semua pasien">
            <i class="fa fa-edit"></i> Edit Customer & Dokter
          </a>
          <a href="{{ route('elits-permohonan-uji-klinik-2.haji.riwayat-nomor', $haji->id_permohonan_uji_klinik_haji) }}" class="btn btn-outline-secondary" title="Riwayat penggantian nomor spesimen/lab">
            <i class="fa fa-history"></i> Riwayat Nomor
          </a>
          <a href="{{ route('elits-permohonan-uji-klinik-2.haji.export-pasien-haji', $haji->id_permohonan_uji_klinik_haji) }}" class="btn btn-success">
            <i class="fa fa-file-excel"></i> Export Semua Pasien
          </a>
          <a href="{{ route('elits-permohonan-uji-klinik-2.haji.tambah-pasien', $haji->id_permohonan_uji_klinik_haji) }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Tambah Pasien
          </a>
        </div>
      </div>

      @if ($canKlinikVerifikasi)
      <div class="alert alert-info py-2 mb-3">
        <i class="fa fa-info-circle mr-1"></i>
        Centang pasien lalu klik aksi:
        <label class="badge badge-secondary badge-pill mb-0">Pengambilan Sample</label> → <strong>Pengambilan Sample</strong>
        (abu-abu),
        <label class="badge badge-dark badge-pill mb-0">Penerimaan Sample</label> → <strong>Penerimaan Massal</strong>
        (hijau muda),
        <label class="badge badge-warning badge-pill mb-0">Pemeriksaan</label> → <strong>Pengolah Massal</strong>
        (kuning muda). Atau klik tombol langsung untuk pilih semua yang siap.
      </div>
      @endif

      <form id="form-penerimaan-massal"
        action="{{ route('elits-permohonan-uji-klinik-2.haji.create-penerima-sampel-massal', $haji->id_permohonan_uji_klinik_haji) }}"
        method="POST" class="d-none">
        @csrf
        <div id="selected-ids-container"></div>
      </form>

      <form id="form-pengolah-massal"
        action="{{ route('elits-permohonan-uji-klinik-2.haji.create-pengolah-sampel-massal', $haji->id_permohonan_uji_klinik_haji) }}"
        method="POST" class="d-none">
        @csrf
        <div id="selected-ids-pengolah-container"></div>
      </form>

      <style>
        #order-listing .check-massal,
        #order-listing #check-all-massal {
          width: 18px;
          height: 18px;
          cursor: pointer;
        }
        #order-listing tr.row-eligible-pengambilan {
          background-color: #eef1f5 !important;
        }
        #order-listing tr.row-eligible-penerimaan {
          background-color: #e8f8f0 !important;
        }
        #order-listing tr.row-eligible-pengolah {
          background-color: #fff8e6 !important;
        }
      </style>

      @if (!empty($haji->tgl_haji))
        <p class="text-muted mb-3">
          Tanggal pemeriksaan:
          <strong>{{ \Carbon\Carbon::parse($haji->tgl_haji)->isoFormat('DD MMMM YYYY') }}</strong>
          &mdash; export pasien hanya untuk jamaah yang didaftarkan pada tanggal tertentu bisa dipilih di bawah.
        </p>
        <form method="get" action="{{ route('elits-permohonan-uji-klinik-2.haji.export-pasien-haji', $haji->id_permohonan_uji_klinik_haji) }}" class="form-inline mb-4">
          <label for="tanggal_export" class="mr-2">Export per tanggal daftar:</label>
          <input type="date" class="form-control mr-2" name="tanggal" id="tanggal_export"
            value="{{ request('tanggal', \Carbon\Carbon::parse($haji->tgl_haji)->format('Y-m-d')) }}">
          <button type="submit" class="btn btn-outline-success">
            <i class="fa fa-download"></i> Export
          </button>
        </form>
      @endif
      
      <div class="table-responsive">
        <table id="order-listing" class="table">
          <thead>
            <tr>
              @if ($canKlinikVerifikasi)
              <th style="width:40px;">
                <input type="checkbox" id="check-all-massal" title="Pilih semua yang siap massal">
              </th>
              @endif
              <th>No</th>
              <th>No. Spesimen / No. Lab</th>
              <th>Nama Pasien</th>
              <th>Tanggal Lahir</th>
              <th>Usia</th>
              <th>Jenis Kelamin</th>
              <th>Alamat</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp

            @foreach ($data as $item)
              @php
                $statusUi = $statusPengujianMap[$item->id_permohonan_uji_klinik]
                  ?? ['label' => ($item->status_permohonan_uji_klinik ?: 'Belum Dimulai'), 'class' => 'badge-warning'];
                $statusLabel = $statusUi['label'] ?? '';
                $isEligiblePengambilan = $statusLabel === 'Pengambilan Sample';
                $isEligiblePenerimaan = $statusLabel === 'Penerimaan Sample';
                $isEligiblePengolah = $statusLabel === 'Pemeriksaan';
                $isEligibleMassal = $isEligiblePengambilan || $isEligiblePenerimaan || $isEligiblePengolah;
                $rowClass = $isEligiblePengambilan
                  ? 'row-eligible-pengambilan'
                  : ($isEligiblePenerimaan ? 'row-eligible-penerimaan' : ($isEligiblePengolah ? 'row-eligible-pengolah' : ''));
                $jenisMassal = $isEligiblePengambilan
                  ? 'pengambilan'
                  : ($isEligiblePenerimaan ? 'penerimaan' : ($isEligiblePengolah ? 'pengolah' : ''));
                $skipPengambilan = ($item->mode_pengambilan_sampel ?? '') === 'dibawa_pelanggan';
                $sampleUrl = route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik);
                $penerimaUrl = route('elits-permohonan-uji-klinik-2.create-penerima-sampel', $item->id_permohonan_uji_klinik);
                $checkSignatureUrl = route('elits-permohonan-uji-klinik-2.check-signature-status', $item->id_permohonan_uji_klinik);
                $verificationUrl = route('elits-permohonan-uji-klinik-2.verification', $item->id_permohonan_uji_klinik);
              @endphp
              <tr class="{{ $rowClass }}">
                @if ($canKlinikVerifikasi)
                <td>
                  @if ($isEligibleMassal)
                    <input type="checkbox" class="check-massal"
                      value="{{ $item->id_permohonan_uji_klinik }}"
                      data-jenis="{{ $jenisMassal }}"
                      data-nama="{{ $item->pasien->nama_pasien ?? '-' }}"
                      data-sample-url="{{ $sampleUrl }}"
                      data-check-signature-url="{{ $checkSignatureUrl }}"
                      data-verification-url="{{ $verificationUrl }}">
                  @else
                    <input type="checkbox" disabled title="Belum siap aksi massal">
                  @endif
                </td>
                @endif
                <td>{{ $no++ }}</td>
                <td>
                  @php
                    $spesimenUrut = $item->resolveSpesimenUrut();
                    $labUrut = $item->resolveLabUrut();
                    $tahunReg = $item->tglregister_permohonan_uji_klinik
                      ? \Carbon\Carbon::parse($item->tglregister_permohonan_uji_klinik)->format('Y')
                      : date('Y');
                  @endphp
                  <div class="small text-muted mb-0">Spesimen</div>
                  <div class="font-weight-bold">
                    {{ $spesimenUrut !== '' ? ('03/' . $spesimenUrut . '/' . $tahunReg) : '—' }}
                  </div>
                  <div class="small text-muted mb-0 mt-1">Lab</div>
                  <div class="font-weight-bold">
                    {{ $labUrut !== '' ? ('449.5/03/' . $labUrut . '/' . $tahunReg) : '—' }}
                  </div>
                </td>
                <td>{{ $item->pasien->nama_pasien ?? '-' }}</td>
                <td>{{ $item->pasien->tgllahir_pasien ? \Carbon\Carbon::parse($item->pasien->tgllahir_pasien)->isoFormat('DD MMMM YYYY') : '-' }}</td>
                <td>
                  @php
                    $usiaTahun = $item->umurtahun_pasien_permohonan_uji_klinik;
                    if ($usiaTahun === null || $usiaTahun === '') {
                      $usiaTahun = null;
                      if (!empty($item->pasien->tgllahir_pasien)) {
                        try {
                          $refDate = $item->tglregister_permohonan_uji_klinik
                            ?? $item->created_at
                            ?? now();
                          $usiaTahun = \Carbon\Carbon::parse($item->pasien->tgllahir_pasien)
                            ->diffInYears(\Carbon\Carbon::parse($refDate));
                        } catch (\Throwable $e) {
                          $usiaTahun = null;
                        }
                      }
                    }
                  @endphp
                  {{ $usiaTahun !== null && $usiaTahun !== '' ? $usiaTahun . ' tahun' : '-' }}
                </td>
                <td>{{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : ($item->pasien->gender_pasien == 'P' ? 'Perempuan' : '-') }}</td>
                <td>
                  {{-- Ambil dari alamat pasien (bukan tanggal lahir) --}}
                  {{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item->pasien) }}
                </td>
                <td>
                  <label class="badge {{ $statusUi['class'] }} badge-pill">{{ $statusUi['label'] }}</label>
                </td>
                <td>
                  @php
                    // Check if parameter exists
                    $has_parameter = \Smt\Masterweb\Models\PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $item->id_permohonan_uji_klinik)
                      ->whereNull('deleted_at')
                      ->exists();
                  @endphp
                  
                  <div class="dropdown show m-1 d-inline-block">
                    <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink-{{ $item->id_permohonan_uji_klinik }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      Aksi
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink-{{ $item->id_permohonan_uji_klinik }}">
                      @if ($canKlinikVerifikasi)
                        @if ($skipPengambilan)
                          <a class="dropdown-item" href="{{ $penerimaUrl }}" title="Penerimaan Sample">
                            <i class="fa fa-inbox mr-1"></i> Penerimaan Sample
                          </a>
                        @else
                          <a class="dropdown-item js-pengambilan-sample-link" href="{{ $sampleUrl }}"
                            data-check-signature-url="{{ $checkSignatureUrl }}"
                            data-verification-url="{{ $verificationUrl }}"
                            title="Pengambilan Sample">
                            <i class="fa fa-hand-paper mr-1"></i> Pengambilan Sample
                          </a>
                        @endif
                      @endif
                      @if ($canKlinikVerifikasi || $isRegister)
                        <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.permohonan-uji-klinik-parameter', $item->id_permohonan_uji_klinik) }}" title="{{ $has_parameter ? 'Edit Parameter' : 'Masukkan Parameter' }}">{{ $has_parameter ? 'Edit Parameter' : 'Masukkan Parameter' }}</a>
                      @endif
                      @if ($canKlinikVerifikasi)
                        <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.verification', $item->id_permohonan_uji_klinik) }}" title="Verifikasi">Verifikasi</a>
                      @endif
                      <a href="{{ route('elits-permohonan-uji-klinik-2.haji.edit-pasien', [$haji->id_permohonan_uji_klinik_haji, $item->id_permohonan_uji_klinik]) }}" class="dropdown-item" title="Edit Pasien">Edit Pasien</a>
                      <a href="{{ route('elits-permohonan-uji-klinik-2.edit', $item->id_permohonan_uji_klinik) }}" class="dropdown-item" title="Edit Permohonan">Edit Permohonan</a>
                      <a class="dropdown-item btn-hapus" href="#hapus" data-id="{{ $item->id_permohonan_uji_klinik }}" data-nama="{{ $item->noregister_permohonan_uji_klinik }}" title="Hapus">Hapus</a>
                    </div>
                  </div>

                  @if ($canKlinikVerifikasi && $isEligiblePengambilan && !$skipPengambilan)
                    <a href="{{ $sampleUrl }}" class="btn btn-fw btn-secondary m-1 js-pengambilan-sample-link"
                      data-check-signature-url="{{ $checkSignatureUrl }}"
                      data-verification-url="{{ $verificationUrl }}"
                      title="Pengambilan Sample">
                      <i class="fa fa-vial"></i> Ambil Sample
                    </a>
                  @endif
                  @if ($canKlinikVerifikasi && $isEligiblePenerimaan)
                    <a href="{{ $penerimaUrl }}" class="btn btn-fw btn-info m-1" title="Penerimaan Sample">
                      <i class="fa fa-inbox"></i> Terima Sample
                    </a>
                  @endif

                  <div class="dropdown show m-1 d-inline-block">
                    <a class="btn btn-fw btn-light dropdown-toggle" href="#" role="button" id="dropdownMenuLinkPrint-{{ $item->id_permohonan_uji_klinik }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      Print
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLinkPrint-{{ $item->id_permohonan_uji_klinik }}">
                      <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-formulir', $item->id_permohonan_uji_klinik) }}" target="__blank" title="Print Formulir Uji Klinik">Print Informed Consent</a>
                      <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.print-lembar-persetujuan', $item->id_permohonan_uji_klinik) }}" target="__blank" title="Print Lembar Persetujuan">Print Lembar Persetujuan</a>
                      @if($has_parameter)
                        <a class="dropdown-item" href="{{ route('elits-persuratan.invoice.klinik', $item->id_permohonan_uji_klinik) }}" title="Print Invoice" target="__blank">Print Invoice</a>
                        @if ((int) ($item->status_pembayaran ?? 0) === 1)
                          <a class="dropdown-item" href="{{ route('elits-persuratan.nota.klinik', $item->id_permohonan_uji_klinik) }}" title="Print Nota" target="__blank">Print Nota</a>
                        @endif
                      @endif
                      <a class="dropdown-item pointer" data-href="{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', $item->id_permohonan_uji_klinik) }}" target="__blank" title="Print Hasil Klinik" data-toggle="modal" data-target="#signOptionModal-{{ $item->id_permohonan_uji_klinik }}">Print Hasil Klinik</a>
                      <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.print-amplop', $item->id_permohonan_uji_klinik) }}" target="__blank" title="Print Amplop Haji">Print Amplop Haji</a>
                      <a class="dropdown-item" href="{{ route('elits-permohonan-uji-klinik-2.print-label', ['permohonan_uji_klinik' => $item->id_permohonan_uji_klinik]) }}" target="__blank" title="Print Label">Print Label</a>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @foreach ($data as $item)
    <!-- Modal Sign Option untuk Print Hasil Klinik -->
    <div class="modal fade" id="signOptionModal-{{ $item->id_permohonan_uji_klinik }}" tabindex="-1" aria-labelledby="signOptionTitle-{{ $item->id_permohonan_uji_klinik }}" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="signOptionTitle-{{ $item->id_permohonan_uji_klinik }}">Pilih metode tanda tangan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="agendaNumber-{{ $item->id_permohonan_uji_klinik }}">Nomor Agenda</label>
              <input type="text" class="form-control" id="agendaNumber-{{ $item->id_permohonan_uji_klinik }}" name="agenda"
                placeholder="Masukkan nomor agenda">
            </div>
          </div>
          <div class="d-flex mx-auto m-2 justify-content-around">
            <a id="linkTTDManual-{{ $item->id_permohonan_uji_klinik }}" href="" target="_blank">
              <button class="btn text-center m-2 p-2 sign-opt">
                <img src="{{ asset('assets/admin/images/sign-icon.png') }}" width="80" height="80">
                <h5 class="mt-2">Tanda Tangan Manual</h5>
              </button>
            </a>
            <a id="linkTTDElektronik-{{ $item->id_permohonan_uji_klinik }}" href="" target="_blank">
              <button class="btn text-center m-2 p-2 sign-opt">
                <img src="{{ asset('assets/admin/images/logo/logo-bsre.png') }}" width="80" height="80">
                <h5 class="mt-2">Tanda Tangan Elektronik</h5>
              </button>
            </a>
          </div>
        </div>
      </div>
    </div>
  @endforeach

  <!-- SweetAlert JS -->
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>
@endsection

@section('scripts')
  <script>
    $(function() {
      function $checks(jenis) {
        if (jenis) {
          return $('.check-massal[data-jenis="' + jenis + '"]');
        }
        return $('.check-massal');
      }

      function updateMassalCounts() {
        $('#count-pengambilan-sample').text($checks('pengambilan').filter(':checked').length);
        $('#count-penerimaan-massal').text($checks('penerimaan').filter(':checked').length);
        $('#count-pengolah-massal').text($checks('pengolah').filter(':checked').length);
        var all = $checks().length;
        var checked = $checks().filter(':checked').length;
        $('#check-all-massal').prop('checked', all > 0 && all === checked);
      }

      function pengambilanDestination(sampleUrl, checkUrl, verificationUrl) {
        return $.ajax({
          url: checkUrl,
          type: 'GET',
          dataType: 'json',
          data: { sampling: 0 }
        }).then(function(resp) {
          if (resp && resp.status && resp.has_signatures) {
            return sampleUrl;
          }

          return verificationUrl
            + '?auto_signature=1&sampling=0&return_to='
            + encodeURIComponent(sampleUrl);
        }, function() {
          swal('Error', 'Gagal mengecek status tanda tangan.', 'error');
          return $.Deferred().reject().promise();
        });
      }

      $(document).on('click', '.js-pengambilan-sample-link', function(e) {
        e.preventDefault();
        var $link = $(this);
        var sampleUrl = $link.attr('href');

        pengambilanDestination(
          sampleUrl,
          $link.data('check-signature-url'),
          $link.data('verification-url')
        ).then(function(destination) {
          window.location.href = destination;
        });
      });

      function openPengambilanSample($checked) {
        var requests = [];
        $checked.each(function() {
          var $check = $(this);
          var sampleUrl = $check.data('sample-url');
          if (sampleUrl) {
            requests.push(pengambilanDestination(
              sampleUrl,
              $check.data('check-signature-url'),
              $check.data('verification-url')
            ));
          }
        });

        if (requests.length === 0) {
          swal('Perhatian', 'URL pengambilan sample tidak ditemukan.', 'warning');
          return;
        }

        $.when.apply($, requests).then(function() {
          var destinations = requests.length === 1
            ? [arguments[0]]
            : Array.prototype.slice.call(arguments);

          if (destinations.length === 1) {
            window.location.href = destinations[0];
            return;
          }

          swal({
            title: 'Pengambilan Sample',
            text: 'Buka pengambilan sample untuk ' + destinations.length + ' pasien? Pasien yang belum TTD akan diarahkan ke popup tanda tangan.',
            icon: 'info',
            buttons: true,
          }).then(function(ok) {
            if (!ok) return;
            destinations.forEach(function(url, idx) {
              if (idx === 0) {
                window.location.href = url;
              } else {
                window.open(url, '_blank');
              }
            });
          });
        });
      }

      function handlePengambilanClick() {
        var $checked = $checks('pengambilan').filter(':checked');

        if ($checked.length === 0) {
          var $allEligible = $checks('pengambilan');
          if ($allEligible.length === 0) {
            swal('Perhatian', 'Tidak ada pasien dengan status Pengambilan Sample.', 'warning');
            return;
          }

          swal({
            title: 'Belum ada yang dicentang',
            text: 'Ada ' + $allEligible.length + ' pasien siap pengambilan sample. Pilih semua sekarang?',
            icon: 'info',
            buttons: ['Batal', 'Pilih Semua'],
          }).then(function(ok) {
            if (!ok) return;
            $allEligible.prop('checked', true);
            updateMassalCounts();
            openPengambilanSample($allEligible);
          });
          return;
        }

        openPengambilanSample($checked);
      }

      function fillAndSubmit($form, $container, $checked, title) {
        $container.empty();
        $checked.each(function() {
          $container.append(
            $('<input>', { type: 'hidden', name: 'selected_ids[]', value: $(this).val() })
          );
        });

        swal({
          title: title,
          text: 'Lanjut untuk ' + $checked.length + ' pasien terpilih?',
          icon: 'info',
          buttons: true,
        }).then(function(ok) {
          if (ok) {
            $form.submit();
          }
        });
      }

      function handleMassalClick(jenis, title, emptyMsg, $form, $container) {
        var $checked = $checks(jenis).filter(':checked');

        if ($checked.length === 0) {
          var $allEligible = $checks(jenis);
          if ($allEligible.length === 0) {
            swal('Perhatian', emptyMsg, 'warning');
            return;
          }

          swal({
            title: 'Belum ada yang dicentang',
            text: 'Ada ' + $allEligible.length + ' pasien siap. Pilih semua sekarang?',
            icon: 'info',
            buttons: ['Batal', 'Pilih Semua'],
          }).then(function(ok) {
            if (!ok) return;
            $allEligible.prop('checked', true);
            updateMassalCounts();
            fillAndSubmit($form, $container, $allEligible, title);
          });
          return;
        }

        fillAndSubmit($form, $container, $checked, title);
      }

      if ($.fn.DataTable && $.fn.DataTable.isDataTable('#order-listing')) {
        try {
          $('#order-listing').DataTable().order([]).draw(false);
          $('#order-listing thead th').eq(0).removeClass('sorting sorting_asc sorting_desc');
        } catch (e) {}
      }

      $(document).on('change', '#check-all-massal', function() {
        $checks().prop('checked', $(this).is(':checked'));
        updateMassalCounts();
      });

      $(document).on('change', '.check-massal', function() {
        updateMassalCounts();
      });

      $(document).on('click', '#order-listing tbody tr.row-eligible-pengambilan td:first-child, #order-listing tbody tr.row-eligible-penerimaan td:first-child, #order-listing tbody tr.row-eligible-pengolah td:first-child', function(e) {
        if ($(e.target).is('input')) return;
        var $cb = $(this).find('.check-massal');
        if ($cb.length) {
          $cb.prop('checked', !$cb.prop('checked')).trigger('change');
        }
      });

      $('#btn-pengambilan-sample').on('click', function() {
        handlePengambilanClick();
      });

      $('#btn-penerimaan-massal').on('click', function() {
        handleMassalClick(
          'penerimaan',
          'Penerimaan Massal',
          'Tidak ada pasien dengan status Penerimaan Sample.',
          $('#form-penerimaan-massal'),
          $('#selected-ids-container')
        );
      });

      $('#btn-pengolah-massal').on('click', function() {
        handleMassalClick(
          'pengolah',
          'Pengolah Massal',
          'Tidak ada pasien dengan status Pemeriksaan.',
          $('#form-pengolah-massal'),
          $('#selected-ids-pengolah-container')
        );
      });

      updateMassalCounts();

      // Handle Print Hasil Klinik dengan sign option
      $('[data-target^="#signOptionModal-"]').on('click', function() {
        var href = $(this).data('href');
        var modalId = $(this).data('target');
        var id = modalId.replace('#signOptionModal-', '');
        
        // Set href untuk link TTD Manual dan Elektronik
        $('#linkTTDManual-' + id).attr('href', href + '?signoption=0');
        $('#linkTTDElektronik-' + id).attr('href', href + '?signoption=1');
        
        // Handle agenda number
        $(modalId).on('shown.bs.modal', function() {
          $('#agendaNumber-' + id).on('input', function() {
            var agenda = $(this).val();
            var baseHref = href.split('?')[0];
            $('#linkTTDManual-' + id).attr('href', baseHref + '?signoption=0&agenda=' + agenda);
            $('#linkTTDElektronik-' + id).attr('href', baseHref + '?signoption=1&agenda=' + agenda);
          });
        });
      });

      // Handle Hapus
      $(document).on('click', '#order-listing .btn-hapus', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        var kode = $btn.data('id');
        var name = $btn.data('nama');
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
              url: '{{ url('/elits-permohonan-uji-klinik-destroy-2') }}/' + kode,
              dataType: 'json',
              cache: false,
              timeout: 120000,
              success: function(response) {
                if (response && response.status == true) {
                  // Hapus baris langsung dari tabel supaya tidak terlihat lagi
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
                    text: response.pesan || "Data berhasil dihapus",
                    icon: "success"
                  })
                  .then(function() {
                    window.location.href = window.location.pathname + window.location.search +
                      (window.location.search ? '&' : '?') + '_ts=' + Date.now();
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
                var message = "System tidak dapat menghapus data!";
                if (xhr.responseJSON && xhr.responseJSON.pesan) {
                  message = xhr.responseJSON.pesan;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                  message = xhr.responseJSON.message;
                }
                swal("ERROR", message, "error");
              }
            });
          } else {
            swal("Cancelled", "Hapus data dibatalkan!", "error");
          }
        });
      });
    });
  </script>
@endsection

