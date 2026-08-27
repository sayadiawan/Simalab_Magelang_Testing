@extends('masterweb::template.admin.layout')

@section('title')
    Detail Log Aktivitas
@endsection

@section('css')
<style>
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
    .al-ppt-kategori { font-weight: 600; color: #174ea6; }
    .al-page-header {
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid #e8eaed;
    }
    .al-page-header .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0.65rem;
        font-size: 13px;
    }
    .al-page-header .breadcrumb-item a {
        color: #1a73e8;
        text-decoration: none;
    }
    .al-page-header .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #9aa0a6;
        padding: 0 0.4rem;
    }
    .al-page-header .breadcrumb-item.active {
        color: #3c4043;
        font-weight: 600;
    }
    .al-page-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #202124;
        margin: 0;
    }
    .al-detail-table th {
        width: 170px;
        background: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        color: #3c4043;
        vertical-align: middle !important;
    }
    .al-detail-table td {
        font-size: 13px;
        vertical-align: middle !important;
    }
    .btn-al-back {
        font-size: 13px;
        font-weight: 600;
        border: 1.5px solid #dadce0;
        color: #3c4043;
        padding: 6px 14px;
        border-radius: 4px;
        text-decoration: none !important;
    }
    .btn-al-back:hover {
        background: #f1f3f4;
        color: #202124;
    }
</style>
@endsection

@section('content')
@php
    $display = \Smt\Masterweb\Helpers\ActivityActionCatalog::enrichDisplay($log);
@endphp

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="al-page-header d-flex justify-content-between align-items-start">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/home') }}"><i class="fa fa-home mr-1"></i>Beranda</a>
                                </li>
                                @if(($scope['mode'] ?? '') === \Smt\Masterweb\Helpers\ActivityLogAccess::MODE_PRINT_EXPORT)
                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/pengarsipan') }}">Pengarsipan Hasil</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/activity-log') }}">Log Aktivitas Cetak</a>
                                    </li>
                                @else
                                    <li class="breadcrumb-item">Laporan</li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/activity-log') }}">Log Aktivitas Sistem</a>
                                    </li>
                                @endif
                                <li class="breadcrumb-item active" aria-current="page">Detail</li>
                            </ol>
                        </nav>
                        <h1 class="al-page-title">
                            @if(($scope['mode'] ?? '') === \Smt\Masterweb\Helpers\ActivityLogAccess::MODE_PRINT_EXPORT)
                                Detail Log Cetak
                            @else
                                Detail Log Aktivitas
                            @endif
                        </h1>
                    </div>
                    <a href="{{ url('/activity-log') }}" class="btn-al-back"><i class="fa fa-arrow-left mr-1"></i> Kembali</a>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered al-detail-table">
                            <tr><th>Waktu</th><td>{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</td></tr>
                            <tr><th>Pengguna</th><td>{{ $log->user_name ?: '-' }} @if($log->username) ({{ $log->username }}) @endif</td></tr>
                            <tr><th>Privilege</th><td>{{ $log->privilege_level ?: '-' }}</td></tr>
                            <tr><th>Aksi</th>
                                <td>
                                    <span class="al-act-badge {{ $display['badge_class'] }}">{{ $display['action_label'] }}</span>
                                    <span class="text-muted ml-1">({{ $log->action }})</span>
                                </td>
                            </tr>
                            <tr><th>Bidang</th><td>{{ strtoupper($log->bidang) }}</td></tr>
                            <tr><th>Objek / Entitas</th><td>{{ $display['subject_label'] ?: '-' }}</td></tr>
                            <tr><th>ID Objek</th><td style="word-break: break-all;">{{ $log->subject_id ?: '-' }}</td></tr>
                            <tr><th>Deskripsi</th><td>{{ $log->description ?: '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered al-detail-table">
                            <tr><th>Akun PPT</th><td>{{ $display['ppt_kategori_label'] ?: '-' }}</td></tr>
                            <tr><th>Fitur PPT</th><td>{{ $display['ppt_fitur'] ?: '-' }}</td></tr>
                            <tr><th>Modul</th><td>{{ $log->module ?: '-' }}</td></tr>
                            <tr><th>Metode HTTP</th><td>{{ $log->http_method ?: '-' }}</td></tr>
                            <tr><th>Route</th><td style="word-break: break-all;">{{ $log->route_name ?: '-' }}</td></tr>
                            <tr><th>URL</th><td style="word-break: break-all; font-size: 12px;">{{ $log->url ?: '-' }}</td></tr>
                            <tr><th>IP Address</th><td>{{ $log->ip_address ?: '-' }}</td></tr>
                            <tr><th>User Agent</th><td style="word-break: break-all; font-size: 12px;">{{ $log->user_agent ?: '-' }}</td></tr>
                        </table>
                    </div>
                </div>

                @if(!empty($log->request_data))
                    <h5 class="mt-2 mb-3 font-weight-bold" style="font-size: 14px;">Data Request (Tambah/Edit)</h5>
                    <pre class="bg-light p-3 rounded border" style="max-height: 320px; overflow: auto; font-size: 12px;">{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif

                @if(!empty($log->metadata))
                    <h5 class="mt-4 mb-3 font-weight-bold" style="font-size: 14px;">Metadata Lengkap</h5>
                    <pre class="bg-light p-3 rounded border" style="max-height: 240px; overflow: auto; font-size: 12px;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
