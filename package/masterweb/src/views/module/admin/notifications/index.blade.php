@extends('masterweb::template.admin.layout')

@section('title')
    Semua Notifikasi
@endsection

@section('css')
<style>
    .notif-page { max-width: 860px; margin: 0 auto; }
    .notif-hero {
        background: linear-gradient(135deg, #06283f 0%, #0b3a5c 55%, #0d8f7f 100%);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        color: #fff;
        margin-bottom: 1rem;
    }
    .notif-hero h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.25rem; }
    .notif-hero p { margin: 0; font-size: 0.85rem; opacity: 0.9; }
    .notif-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.85rem;
    }
    .notif-filters { display: flex; gap: 0.35rem; }
    .notif-filters a {
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        color: #445;
        background: #eef1f4;
    }
    .notif-filters a.active { background: #0d8f7f; color: #fff; }
    .notif-card {
        background: #fff;
        border: 1px solid #e8ecef;
        border-radius: 10px;
        overflow: hidden;
    }
    .notif-row {
        display: flex;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f0f2f4;
        text-decoration: none !important;
        color: inherit !important;
    }
    .notif-row:last-child { border-bottom: 0; }
    .notif-row.is-unread { background: #f3fbf9; }
    .notif-row:hover { background: #eef8f6; }
    .notif-row__icon {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff; flex-shrink: 0;
    }
    .notif-row__icon.bg-warning { background: #ffb822; }
    .notif-row__icon.bg-info { background: #36a3f7; }
    .notif-row__icon.bg-primary { background: #5867dd; }
    .notif-row__icon.bg-success { background: #1dc9b7; }
    .notif-row__icon.bg-danger { background: #fd397a; }
    .notif-row__icon.bg-secondary { background: #6c757d; }
    .notif-row__title { font-weight: 700; font-size: 0.9rem; margin-bottom: 0.15rem; }
    .notif-row__msg { font-size: 0.8rem; color: #666; white-space: pre-line; margin: 0; }
    .notif-row__meta { font-size: 0.72rem; color: #99a3a8; margin-top: 0.25rem; }
    .notif-empty { padding: 2rem; text-align: center; color: #999; }
    .notif-pager { display: flex; gap: 0.5rem; justify-content: center; margin-top: 1rem; }
</style>
@endsection

@section('content')
@php
    $items = $initial['items'] ?? [];
    $unread = $initial['unread'] ?? 0;
    $pagination = $initial['pagination'] ?? ['page' => 1, 'last_page' => 1, 'total' => 0];
@endphp
<div class="notif-page">
    <div class="notif-hero">
        <h1>Semua Notifikasi</h1>
        <p>Event belum dibaca: <strong>{{ $unread }}</strong>. Worklist di halaman pemeriksaan tetap menjadi sumber kebenaran antrian kerja.</p>
    </div>

    <div class="notif-toolbar">
        <div class="notif-filters">
            <a href="{{ route('notifications.index') }}" class="{{ empty($filter) ? 'active' : '' }}">Semua</a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="{{ ($filter ?? '') === 'unread' ? 'active' : '' }}">Belum dibaca</a>
            <a href="{{ route('notifications.index', ['filter' => 'read']) }}" class="{{ ($filter ?? '') === 'read' ? 'active' : '' }}">Sudah dibaca</a>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary">Tandai semua terbaca</button>
        </form>
    </div>

    <div class="notif-card">
        @forelse ($items as $item)
            <a class="notif-row {{ !empty($item['unread']) ? 'is-unread' : '' }}"
               href="{{ $item['url'] ?: '#' }}"
               onclick="markNotifRead('{{ $item['id'] }}')">
                <div class="notif-row__icon bg-{{ $item['color'] ?? 'secondary' }}">
                    <i class="fas {{ $item['icon'] ?? 'fa-bell' }}"></i>
                </div>
                <div>
                    <div class="notif-row__title">{{ $item['title'] }}</div>
                    <p class="notif-row__msg">{{ $item['message'] }}</p>
                    <div class="notif-row__meta">{{ $item['created_human'] ?? $item['created_at'] }}</div>
                </div>
            </a>
        @empty
            <div class="notif-empty">Belum ada notifikasi.</div>
        @endforelse
    </div>

    @if (($pagination['last_page'] ?? 1) > 1)
        <div class="notif-pager">
            @if (($pagination['page'] ?? 1) > 1)
                <a class="btn btn-sm btn-light" href="{{ route('notifications.index', ['filter' => $filter, 'page' => $pagination['page'] - 1]) }}">Sebelumnya</a>
            @endif
            <span class="align-self-center text-muted small">Halaman {{ $pagination['page'] }} / {{ $pagination['last_page'] }}</span>
            @if (($pagination['page'] ?? 1) < ($pagination['last_page'] ?? 1))
                <a class="btn btn-sm btn-light" href="{{ route('notifications.index', ['filter' => $filter, 'page' => $pagination['page'] + 1]) }}">Berikutnya</a>
            @endif
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function markNotifRead(id) {
    if (!id) return;
    $.ajax({
        url: "{{ url('notifications') }}/" + id + "/read",
        type: "POST",
        data: { _token: "{{ csrf_token() }}" },
        async: true
    });
}
</script>
@endsection
