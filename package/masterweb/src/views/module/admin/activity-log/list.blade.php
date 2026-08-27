@extends('masterweb::template.admin.layout')

@section('title')
    Log Aktivitas Sistem
@endsection

@section('css')
<style>
    /* Badge aksi — kontras tinggi (hindari badge-secondary/light Bootstrap yang pudar) */
    .al-act-badge {
        display: inline-block;
        min-width: 62px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
        border-radius: 4px;
        color: #fff !important;
        line-height: 1.2;
        letter-spacing: 0.02em;
        box-shadow: 0 1px 2px rgba(0,0,0,.12);
    }
    .al-act-create   { background: #188038; }
    .al-act-update   { background: #e37400; }
    .al-act-delete   { background: #c5221f; }
    .al-act-print    { background: #1967d2; }
    .al-act-export   { background: #3c4043; }
    .al-act-view     { background: #5f6368; }
    .al-act-validate { background: #8430ce; }
    .al-act-confirm  { background: #00838f; }
    .al-act-login    { background: #188038; }
    .al-act-login_failed { background: #c5221f; }
    .al-act-logout   { background: #80868b; }
    .al-act-other    { background: #9aa0a6; color: #202124 !important; }

    .al-priv-badge {
        display: inline-block;
        background: #e8f0fe;
        color: #174ea6 !important;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 10px;
        border: 1px solid #c6dafc;
    }

    .al-bidang-label {
        display: inline-block;
        margin-top: 4px;
        font-size: 10px;
        font-weight: 600;
        color: #5f6368;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .al-ppt-kategori {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #174ea6;
        margin-bottom: 2px;
    }

    .btn-al-detail {
        display: inline-block;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        border: 1.5px solid #1967d2 !important;
        color: #1967d2 !important;
        background: #fff !important;
        border-radius: 4px;
        text-decoration: none !important;
        white-space: nowrap;
    }
    .btn-al-detail:hover {
        background: #1967d2 !important;
        color: #fff !important;
    }

    .al-page-header {
        margin-bottom: 1.5rem;
    }
    .al-page-header .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
        font-size: 12px;
    }
    .al-page-header .breadcrumb-item a {
        color: #5f6368;
        text-decoration: none;
    }
    .al-page-header .breadcrumb-item a:hover {
        color: #1a73e8;
        text-decoration: none;
    }
    .al-page-header .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
        color: #dadce0;
        padding: 0 0.5rem;
        font-weight: 400;
    }
    .al-page-header .breadcrumb-item.active {
        color: #202124;
        font-weight: 500;
    }
    .al-page-intro {
        margin-bottom: 1rem;
    }
    .al-page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #202124;
        margin: 0 0 0.4rem;
        line-height: 1.3;
    }
    .al-page-subtitle {
        font-size: 13px;
        color: #5f6368;
        margin: 0;
        line-height: 1.55;
        max-width: 720px;
    }
    .al-legend-box {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 14px;
        background: #f8f9fa;
        border: 1px solid #e8eaed;
        border-radius: 8px;
        padding: 0.65rem 1rem;
    }
    .al-legend-title {
        font-size: 11px;
        font-weight: 700;
        color: #5f6368;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
        margin-right: 2px;
    }
    .al-legend-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }
    .al-legend-badges .al-act-badge {
        min-width: auto;
        padding: 3px 10px;
        font-size: 10px;
        box-shadow: none;
    }
    .al-filter-box {
        background: #f8f9fa;
        border: 1px solid #e8eaed;
        border-radius: 8px;
        padding: 1rem 1.1rem 0.25rem;
        margin-bottom: 1.25rem;
    }
    .al-filter-box label {
        font-size: 12px;
        font-weight: 600;
        color: #3c4043;
        margin-bottom: 0.35rem;
    }
    .al-table thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #5f6368;
        border-top: none;
        white-space: nowrap;
    }
    .al-pagination {
        gap: 12px;
        padding-top: 0.5rem;
        border-top: 1px solid #e8eaed;
    }
    .al-pagination-info {
        font-size: 13px;
        line-height: 1.5;
    }
    .al-per-page-form label {
        font-size: 12px;
        font-weight: 600;
        color: #5f6368;
    }
    .al-pagination-nav .pagination {
        margin-bottom: 0;
    }
    .al-pagination-nav .page-link {
        font-size: 13px;
        padding: 0.35rem 0.65rem;
    }
    .al-scope-banner {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        background: #e8f0fe;
        border: 1px solid #c6dafc;
        border-radius: 8px;
        padding: 0.7rem 1rem;
        margin-bottom: 1.25rem;
        font-size: 13px;
        line-height: 1.5;
        color: #174ea6;
    }
    .al-scope-banner i {
        margin-top: 2px;
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="al-page-header">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/home') }}">Beranda</a>
                            </li>
                            @if(($scope['mode'] ?? '') === \Smt\Masterweb\Helpers\ActivityLogAccess::MODE_PRINT_EXPORT)
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/pengarsipan') }}">Pengarsipan Hasil</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Log Aktivitas Cetak</li>
                            @else
                                <li class="breadcrumb-item">Laporan</li>
                                <li class="breadcrumb-item active" aria-current="page">Log Aktivitas Sistem</li>
                            @endif
                        </ol>
                    </nav>

                    <div class="al-page-intro">
                        @if(($scope['mode'] ?? '') === \Smt\Masterweb\Helpers\ActivityLogAccess::MODE_PRINT_EXPORT)
                            <h1 class="al-page-title">Log Aktivitas Cetak</h1>
                            <p class="al-page-subtitle">
                                Riwayat pencetakan dan ekspor dokumen laboratorium — hasil klinik, LHU kesmas, nota, dan register.
                            </p>
                        @else
                            <h1 class="al-page-title">Log Aktivitas Sistem</h1>
                            <p class="al-page-subtitle">
                                Rekam jejak aktivitas pengguna — tambah, edit, hapus, cetak, validasi, dan lainnya.
                            </p>
                        @endif
                    </div>

                    <div class="al-legend-box">
                        <span class="al-legend-title">Legenda aksi</span>
                        <div class="al-legend-badges">
                            @if(($scope['mode'] ?? '') === \Smt\Masterweb\Helpers\ActivityLogAccess::MODE_PRINT_EXPORT)
                                <span class="al-act-badge al-act-print">Cetak</span>
                                <span class="al-act-badge al-act-export">Ekspor</span>
                            @else
                                <span class="al-act-badge al-act-create">Tambah</span>
                                <span class="al-act-badge al-act-update">Edit</span>
                                <span class="al-act-badge al-act-delete">Hapus</span>
                                <span class="al-act-badge al-act-print">Cetak</span>
                                <span class="al-act-badge al-act-validate">Validasi</span>
                                <span class="al-act-badge al-act-view">Lihat</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!empty($scope['label']))
                    <div class="al-scope-banner">
                        <i class="ti-info-alt"></i>
                        @if(!empty($scope['role_label']))
                            <strong>{{ $scope['role_label'] }}:</strong>
                        @endif
                        {{ $scope['label'] }}
                    </div>
                @endif

                <div class="al-filter-box">
                    <form method="GET" action="{{ url('/activity-log') }}">
                        <div class="row">
                            <div class="col-md-2 form-group">
                                <label>Dari tanggal</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>Sampai tanggal</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>Aksi</label>
                                <select name="action" class="form-control form-control-sm">
                                    <option value="">Semua aksi</option>
                                    @foreach($actionLabels as $key => $label)
                                        <option value="{{ $key }}" {{ $filters['action'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Akun PPT</label>
                                <select name="ppt_kategori" class="form-control form-control-sm">
                                    <option value="">Semua akun PPT</option>
                                    @foreach($pptKategoris as $key => $label)
                                        <option value="{{ $key }}" {{ ($filters['ppt_kategori'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Bidang</label>
                                <select name="bidang" class="form-control form-control-sm" {{ empty($scope['can_filter_bidang']) ? 'disabled' : '' }}>
                                    <option value="">Semua bidang</option>
                                    @foreach($bidangs as $b)
                                        <option value="{{ $b }}" {{ $filters['bidang'] === $b ? 'selected' : '' }}>{{ strtoupper($b) }}</option>
                                    @endforeach
                                    @if($bidangs->isEmpty())
                                        @foreach(['klinik','kesmas','admin','mobile','umum'] as $b)
                                            <option value="{{ $b }}" {{ $filters['bidang'] === $b ? 'selected' : '' }}>{{ strtoupper($b) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @if(empty($scope['can_filter_bidang']) && !empty($scope['bidang']))
                                    <input type="hidden" name="bidang" value="{{ $scope['bidang'] }}">
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Pengguna</label>
                                @if(!empty($scope['can_filter_user']))
                                    <select name="user_id" class="form-control form-control-sm">
                                        <option value="">Semua pengguna</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ $filters['user_id'] === $u->id ? 'selected' : '' }}>
                                                {{ $u->name }} ({{ $u->username }})
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->name ?? '-' }}" disabled>
                                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                @endif
                            </div>
                            <div class="col-md-8 form-group">
                                <label>Cari</label>
                                <input type="text" name="q" class="form-control form-control-sm" value="{{ $filters['q'] }}" placeholder="Objek, deskripsi, ID, modul, fitur PPT...">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Data per halaman</label>
                                <select name="per_page" class="form-control form-control-sm">
                                    @foreach($allowedPerPage as $option)
                                        <option value="{{ $option }}" {{ (int) $filters['per_page'] === (int) $option ? 'selected' : '' }}>
                                            {{ $option }} baris / halaman
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-flex pb-3">
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fa fa-filter mr-1"></i> Terapkan Filter
                            </button>
                            <a href="{{ url('/activity-log') }}" class="btn btn-light btn-sm mr-2">Reset</a>
                            @if(($scope['mode'] ?? '') === \Smt\Masterweb\Helpers\ActivityLogAccess::MODE_PRINT_EXPORT)
                                <a href="{{ url('/pengarsipan') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa fa-arrow-left mr-1"></i> Kembali ke Pengarsipan
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover al-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Objek</th>
                                <th>Fitur PPT</th>
                                <th>Deskripsi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $display = \Smt\Masterweb\Helpers\ActivityActionCatalog::enrichDisplay($log);
                                @endphp
                                <tr>
                                    <td nowrap>{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</td>
                                    <td>
                                        <strong>{{ $log->user_name ?: '-' }}</strong>
                                        @if($log->username)
                                            <br><small class="text-muted">{{ $log->username }}</small>
                                        @endif
                                        @if($log->privilege_level)
                                            <br><span class="al-priv-badge">{{ $log->privilege_level }}</span>
                                        @endif
                                    </td>
                                    <td nowrap>
                                        <span class="al-act-badge {{ $display['badge_class'] }}">{{ $display['action_label'] }}</span>
                                        <span class="al-bidang-label d-block">{{ strtoupper($log->bidang) }}</span>
                                    </td>
                                    <td style="max-width: 180px; white-space: normal;">
                                        <strong>{{ $display['subject_label'] ?: '-' }}</strong>
                                        @if($log->subject_id)
                                            <br><small class="text-muted">ID: {{ \Illuminate\Support\Str::limit($log->subject_id, 18) }}</small>
                                        @endif
                                    </td>
                                    <td style="max-width: 220px; white-space: normal;">
                                        @if(!empty($display['ppt_kategori_label']))
                                            <span class="al-ppt-kategori">{{ $display['ppt_kategori_label'] }}</span>
                                        @endif
                                        {{ $display['ppt_fitur'] ?: '-' }}
                                    </td>
                                    <td style="max-width: 280px; white-space: normal;">{{ \Illuminate\Support\Str::limit($log->description, 140) }}</td>
                                    <td nowrap>
                                        <a href="{{ url('/activity-log/' . $log->id_activity_log) }}" class="btn-al-detail">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada log aktivitas untuk filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="al-pagination d-flex flex-wrap justify-content-between align-items-center mt-3">
                    <div class="al-pagination-info">
                        @if($logs->total() > 0)
                            Menampilkan <strong>{{ number_format($logs->firstItem()) }}</strong>–<strong>{{ number_format($logs->lastItem()) }}</strong>
                            dari <strong>{{ number_format($logs->total()) }}</strong> log
                            <span class="text-muted">(halaman {{ $logs->currentPage() }} / {{ $logs->lastPage() }})</span>
                        @else
                            <span class="text-muted">Tidak ada log untuk filter ini.</span>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap align-items-center">
                        <form method="GET" action="{{ url('/activity-log') }}" class="form-inline al-per-page-form mr-3 mb-2 mb-md-0">
                            @foreach($filters as $key => $value)
                                @if($key !== 'per_page' && $value !== '' && $value !== null)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="al_per_page_bottom" class="mr-2 mb-0">Per halaman</label>
                            <select id="al_per_page_bottom" name="per_page" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
                                @foreach($allowedPerPage as $option)
                                    <option value="{{ $option }}" {{ (int) $filters['per_page'] === (int) $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </form>

                        @if($logs->hasPages())
                            <div class="al-pagination-nav">
                                {{ $logs->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
