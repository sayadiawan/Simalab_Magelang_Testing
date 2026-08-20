@extends('masterweb::template.admin.layout')

@section('title')
  Riwayat Nomor - {{ $haji->nama_haji }}
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
                <li class="breadcrumb-item"><a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}">Daftar Pasien</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Riwayat Nomor</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h4 class="card-title mb-0">Riwayat penggantian nomor — {{ $haji->nama_haji }}</h4>
        <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}" class="btn btn-outline-secondary">
          Kembali ke Daftar Pasien
        </a>
      </div>

      <p class="text-muted">
        Catatan ini hanya terisi jika nomor spesimen/lab <strong>berubah setelah tersimpan</strong>.
        Alokasi nomor baru saat daftar/import tidak dicatat sebagai penggantian.
      </p>

      <form method="GET" class="form-inline mb-3">
        <input type="text" name="q" class="form-control mr-2 mb-2" placeholder="Cari nomor lama/baru…" value="{{ request('q') }}">
        <button type="submit" class="btn btn-primary mb-2">Cari</button>
      </form>

      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Pasien</th>
              <th>Kolom</th>
              <th>Nomor lama</th>
              <th>Nomor baru</th>
              <th>Sumber</th>
              <th>Petugas</th>
            </tr>
          </thead>
          <tbody>
            @forelse($history as $row)
              @php
                $puk = $pasienById[$row->subject_id] ?? null;
                $nama = optional(optional($puk)->pasien)->nama_pasien ?: '-';
                $labels = [
                  'nourut_permohonan_uji_klinik' => 'No. Spesimen (urut)',
                  'noregister_permohonan_uji_klinik' => 'No. Register',
                  'nomer_lab' => 'No. Lab',
                  'nomor_lab_manual' => 'No. Lab (manual)',
                  'nomor_spesimen_manual' => 'No. Spesimen (manual)',
                ];
              @endphp
              <tr>
                <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i:s') }}</td>
                <td>{{ $nama }}</td>
                <td>{{ $labels[$row->field_name] ?? $row->field_name }}</td>
                <td>{{ $row->old_value !== null && $row->old_value !== '' ? $row->old_value : '—' }}</td>
                <td><strong>{{ $row->new_value !== null && $row->new_value !== '' ? $row->new_value : '—' }}</strong></td>
                <td>{{ $row->source }}{{ $row->note ? ' — ' . $row->note : '' }}</td>
                <td>{{ optional($row->creator)->name ?: '—' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted">Belum ada penggantian nomor pada rombongan ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(method_exists($history, 'links'))
        {{ $history->links() }}
      @endif
    </div>
  </div>
@endsection
