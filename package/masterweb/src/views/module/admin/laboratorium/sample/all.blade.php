@extends('masterweb::template.admin.layout')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 style="margin:0;">Daftar Semua Sample</h5>
                <form method="GET" action="{{ route('elits-samples.all') }}" class="form-inline">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control mr-2"
                        placeholder="Cari kode, pelanggan, titik pengambilan, lab...">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="p-3">
                    <a href="{{ route('elits-samples.all') }}"
                        class="btn btn-sm {{ request('type') == null ? 'btn-primary' : 'btn-light' }} mr-1">Semua</a>
                    <a href="{{ route('elits-samples.all', ['type' => 'mikrobiologi']) }}"
                        class="btn btn-sm {{ request('type') == 'mikrobiologi' ? 'btn-primary' : 'btn-light' }} mr-1">Mikrobiologi</a>
                    <a href="{{ route('elits-samples.all', ['type' => 'kimia']) }}"
                        class="btn btn-sm {{ request('type') == 'kimia' ? 'btn-primary' : 'btn-light' }} mr-1">Kimia</a>
                    <a href="{{ route('elits-samples.all', ['type' => 'klinik']) }}"
                        class="btn btn-sm {{ request('type') == 'klinik' ? 'btn-primary' : 'btn-light' }}">Klinik</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:50px;">No</th>
                                <th style="width:120px;">
                                    Nomer Sample
                                    <br><small class="text-muted font-weight-normal">Saat Pendaftaran</small>
                                </th>
                                <th style="width:120px;">
                                    Nomer Lab
                                    <br><small class="text-muted font-weight-normal">Saat Selesai</small>
                                </th>
                                <th>Kode / No. Register</th>
                                <th>Nama Pelanggan / Pasien</th>
                                <th>Detail</th>
                                <th>Lab</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($samples as $index => $row)
                                <tr>
                                    <td>{{ ($samples->currentPage() - 1) * $samples->perPage() + $index + 1 }}</td>

                                    {{-- Nomer Sample (global sequential, lahir saat pendaftaran) --}}
                                    <td>
                                        <div class="text-muted" style="font-size:11px;">{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y') : '-' }}</div>
                                        <span class="badge badge-secondary" style="font-size:13px; letter-spacing:1px;">
                                            {{ str_pad($row->global_seq, 3, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>

                                    {{-- Nomer Lab (global sequential, lahir saat selesai) --}}
                                    <td>
                                        @if (!empty($row->nomer_lab))
                                            <span class="badge badge-success" style="font-size:13px; letter-spacing:1px;">
                                                {{ str_pad($row->nomer_lab, 3, '0', STR_PAD_LEFT) }}
                                            </span>
                                        @else
                                            <span class="text-muted" title="Belum selesai">—</span>
                                        @endif
                                    </td>

                                    <td>{{ $row->codesample_samples ?? '-' }}</td>
                                    <td>{{ $row->name_pelanggan ?? '-' }}</td>
                                    <td>{!! $row->detail_sample ?? '-' !!}</td>
                                    <td>
                                        @if ($row->lab_name)
                                            <span class="badge badge-info">{{ $row->lab_name }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Data tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $samples->links() }}
            </div>
        </div>
    </div>
@endsection
