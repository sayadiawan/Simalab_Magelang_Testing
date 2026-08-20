@extends('masterweb::template.admin.layout')

@section('title')
  Beranda
@endsection

@php
  use Carbon\Carbon;

  $user = Auth()->user();

  $hour = Carbon::now()->format('H'); // Get current hour in 24-hour format
  if ($hour < 10) {
      $greeting = 'Selamat Pagi';
  } elseif ($hour < 14){
    $greeting = 'Selamat Siang';
  }elseif ($hour < 18) {
      $greeting = 'Selamat Sore';
  } else {
      $greeting = 'Selamat Malam';
  }
@endphp

@section('content')
  <style>
    .dash-page {
      --dash-primary: #2D6BCF;
      --dash-primary-dark: #1e4a8e;
      --dash-accent: #667eea;
      --dash-accent-dark: #764ba2;
      --dash-surface: #ffffff;
      --dash-muted: #64748b;
      --dash-radius: 16px;
      --dash-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
      --dash-shadow-hover: 0 16px 48px rgba(15, 23, 42, 0.14);
    }

    .dash-hero {
      position: relative;
      overflow: hidden;
      border-radius: var(--dash-radius);
      background: linear-gradient(135deg, #1e3a8a 0%, #2D6BCF 45%, #667eea 100%);
      box-shadow: var(--dash-shadow);
      padding: 2rem 2.25rem;
      min-height: 200px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1.5rem;
    }

    .dash-hero::before {
      content: '';
      position: absolute;
      top: -40%;
      right: -10%;
      width: 420px;
      height: 420px;
      background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .dash-hero::after {
      content: '';
      position: absolute;
      bottom: -30%;
      left: 10%;
      width: 280px;
      height: 280px;
      background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .dash-hero__content {
      position: relative;
      z-index: 1;
      max-width: 620px;
    }

    .dash-hero__badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.35rem 0.85rem;
      border-radius: 999px;
      background: rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.95);
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      margin-bottom: 0.85rem;
      backdrop-filter: blur(4px);
    }

    .dash-hero__title {
      color: #fff;
      font-size: 1.85rem;
      font-weight: 700;
      line-height: 1.25;
      margin-bottom: 0.5rem;
    }

    .dash-hero__subtitle {
      color: rgba(255,255,255,0.88);
      font-size: 0.98rem;
      line-height: 1.6;
      margin-bottom: 1.25rem;
    }

    .dash-hero__cta {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.65rem 1.25rem;
      border-radius: 10px;
      background: #fff;
      color: var(--dash-primary);
      font-weight: 600;
      font-size: 0.9rem;
      border: none;
      box-shadow: 0 4px 14px rgba(0,0,0,0.12);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      text-decoration: none !important;
    }

    .dash-hero__cta:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.18);
      color: var(--dash-primary-dark);
    }

    .dash-hero__visual {
      position: relative;
      z-index: 1;
      flex-shrink: 0;
    }

    .dash-hero__visual img {
      width: 220px;
      max-width: 100%;
      filter: drop-shadow(0 12px 24px rgba(0,0,0,0.2));
    }

    .dash-section-title {
      font-size: 1.05rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .dash-section-title i {
      color: var(--dash-primary);
    }

    .dash-stat-card {
      border: none;
      border-radius: var(--dash-radius);
      box-shadow: var(--dash-shadow);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      height: 100%;
      min-height: 170px;
      overflow: hidden;
    }

    .dash-stat-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--dash-shadow-hover);
    }

    .dash-stat-card .card-body {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 1.5rem;
      text-align: center;
    }

    .dash-stat-card__icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: rgba(255,255,255,0.95);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 0.85rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .dash-stat-card__value {
      font-size: 2.25rem;
      font-weight: 800;
      color: #fff;
      line-height: 1;
      margin-bottom: 0.35rem;
    }

    .dash-stat-card__label {
      color: rgba(255,255,255,0.92);
      font-size: 0.85rem;
      font-weight: 500;
      margin: 0;
    }

    .dash-stat-card--blue { background: linear-gradient(135deg, #2D6BCF 0%, #1e4a8e 100%); }
    .dash-stat-card--blue .dash-stat-card__icon i { color: #2D6BCF; font-size: 1.75rem; }
    .dash-stat-card--green { background: linear-gradient(135deg, #22c55e 0%, #15803d 100%); }
    .dash-stat-card--green .dash-stat-card__icon i { color: #22c55e; font-size: 1.75rem; }
    .dash-stat-card--amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .dash-stat-card--amber .dash-stat-card__icon i { color: #f59e0b; font-size: 1.75rem; }
    .dash-stat-card--teal { background: linear-gradient(135deg, #06b6d4 0%, #0e7490 100%); }
    .dash-stat-card--teal .dash-stat-card__icon i { color: #06b6d4; font-size: 1.75rem; }
    .dash-stat-card--purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .dash-stat-card--purple .dash-stat-card__icon i { color: #667eea; font-size: 1.75rem; }

    .dash-mini-stat {
      border: none;
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(15,23,42,0.06);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border-left: 4px solid transparent;
      height: 100%;
    }

    .dash-mini-stat:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(15,23,42,0.1);
    }

    .dash-mini-stat--amber { border-left-color: #f59e0b; }
    .dash-mini-stat--green { border-left-color: #22c55e; }
    .dash-mini-stat--teal { border-left-color: #06b6d4; }

    .solab-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      box-shadow: var(--dash-shadow);
      transition: all 0.3s ease;
      border-radius: var(--dash-radius);
    }

    .solab-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--dash-shadow-hover);
    }

    .solab-card .counter {
      color: white !important;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .quick-action-card {
      transition: all 0.25s ease;
      border-radius: 14px !important;
    }

    .quick-action-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 30px rgba(15,23,42,0.12) !important;
    }

    .stats-card {
      border-radius: 14px !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stats-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(15,23,42,0.1) !important;
    }

    .counter { font-size: 2.25rem; font-weight: 800; }

    .dash-charts {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1.25rem;
      margin-top: 1.75rem;
    }

    .dash-chart-card {
      background: var(--dash-surface);
      border-radius: var(--dash-radius);
      box-shadow: var(--dash-shadow);
      padding: 1.5rem;
      min-height: 380px;
    }

    .dash-chart-card canvas {
      max-height: 300px;
    }

    .dash-chart-card__title {
      font-size: 0.95rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 2px solid #f1f5f9;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .dash-chart-card__title i { color: var(--dash-primary); }

    .dash-quick-bar {
      border: none;
      border-radius: var(--dash-radius);
      overflow: hidden;
      box-shadow: var(--dash-shadow);
      margin-top: 1.75rem;
    }

    .dash-quick-bar .card-body {
      background: linear-gradient(135deg, #1e3a8a 0%, #2D6BCF 60%, #3b82f6 100%);
      padding: 1.5rem 2rem;
    }

    .dash-quick-bar__item {
      text-align: center;
      padding: 0.5rem 1rem;
    }

    .dash-quick-bar__item h5 {
      color: #fff;
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
    }

    .dash-quick-bar__item .btn {
      background: rgba(255,255,255,0.95);
      color: var(--dash-primary);
      border: none;
      border-radius: 8px;
      font-weight: 600;
      padding: 0.45rem 1.1rem;
      transition: all 0.2s ease;
    }

    .dash-quick-bar__item .btn:hover {
      background: #fff;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .dash-panel {
      background: var(--dash-surface);
      border-radius: var(--dash-radius);
      box-shadow: var(--dash-shadow);
      padding: 1.5rem;
      margin-bottom: 1.25rem;
    }

    @media (max-width: 1023px) {
      .dash-hero {
        flex-direction: column;
        text-align: center;
        padding: 1.5rem;
      }

      .dash-hero__title { font-size: 1.45rem; }
      .dash-hero__visual img { width: 160px; }
      .dash-charts { grid-template-columns: 1fr; }
    }
  </style>

  <div class="dash-page">
    <div class="dash-hero">
      <div class="dash-hero__content">
        <div class="dash-hero__badge">
          <i class="fas fa-flask"></i> Sistem Laboratorium
        </div>
        <h1 class="dash-hero__title">{{ $greeting }}, {{ $user->name }}!</h1>
        <p class="dash-hero__subtitle">
          Selamat datang di dashboard Labkes Magelang. Pantau ringkasan permohonan uji,
          sampel, dan analisa laboratorium Anda di satu tempat.
        </p>
        @if(Auth::user()->getlevel->level == 'DKTR' || (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'KLI'))
          <a href="{{ url('elits-permohonan-uji-klinik-2') }}" class="dash-hero__cta">
            <i class="fas fa-clipboard-list"></i> Permohonan Uji Klinik
          </a>
        @elseif($user->level == '3382abf2-8518-42f9-91e1-096f25da8ae8')
          @if($user->laboratory_users == 'bbed2259-2826-4711-b0fc-abdad5aace22')
            <a href="{{ url('elits-analys/klinik') }}" class="dash-hero__cta">
              <i class="fas fa-microscope"></i> Data Analisa Klinik
            </a>
          @else
            <a href="{{ url('elits-analys') }}" class="dash-hero__cta">
              <i class="fas fa-microscope"></i> Data Analisa
            </a>
          @endif
        @else
          <a href="{{ url('elits-permohonan-uji') }}" class="dash-hero__cta">
            <i class="fas fa-plus-circle"></i> Buat Permohonan Uji
          </a>
        @endif
      </div>
      <div class="dash-hero__visual d-none d-md-block">
        <img src="{{ asset('assets/admin/images/scientist-looking-test-tube.png') }}" alt="Laboratorium">
      </div>
    </div>

    <div class="mt-4">
    @if (Auth::user()->getlevel->level == 'DKTR' || (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'KLI'))
      {{-- DKTR: Dashboard khusus Dokter - hanya informasi Klinik --}}
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-chart-pie"></i> Ringkasan Klinik</div>
        <div class="row">
          <div class="col-md-6 mb-4">
            <div class="card dash-stat-card dash-stat-card--blue">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-users"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $pasien_klinik }}">0</h3>
                <p class="dash-stat-card__label">Total Pasien</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 mb-4">
            <div class="card dash-stat-card dash-stat-card--teal">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-clipboard-list"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_permohonan_uji_klinik ?? 0 }}">0</h3>
                <p class="dash-stat-card__label">Permohonan Uji Klinik</p>
              </div>
            </div>
          </div>
          
          {{-- Total Sampel Klinik - DISEMBUNYIKAN --}}
          {{--
          <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border-radius: 15px; height: 200px;">
              <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
                <div class="bg-white rounded-circle p-3 mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                  <i class="fas fa-vial" style="font-size: 32px; color: #28a745;"></i>
                </div>
                <h3 class="counter text-white mb-1" data-target="{{ $total_sampel_klinik }}" style="font-size: 2.5rem; font-weight: bold;">0</h3>
                <p class="text-white mb-0" style="font-size: 14px; opacity: 0.9;">Total Sampel Klinik</p>
              </div>
            </div>
          </div>
          --}}
          
          {{-- Analisa Berjalan - DISEMBUNYIKAN --}}
          {{--
          <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border-radius: 15px; height: 200px;">
              <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
                <div class="bg-white rounded-circle p-3 mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                  <i class="fas fa-hourglass-half" style="font-size: 32px; color: #ffc107;"></i>
                </div>
                <h3 class="text-white mb-1" style="font-size: 2.5rem; font-weight: bold;">{{ $analisa_berjalan_klinik ?? 0 }}</h3>
                <p class="text-white mb-0" style="font-size: 14px; opacity: 0.9;">Analisa Berjalan</p>
              </div>
            </div>
          </div>
          --}}
          
          {{-- Analisa Selesai - DISEMBUNYIKAN --}}
          {{--
          <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); border-radius: 15px; height: 200px;">
              <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
                <div class="bg-white rounded-circle p-3 mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                  <i class="fas fa-check-circle" style="font-size: 32px; color: #17a2b8;"></i>
                </div>
                <h3 class="text-white mb-1" style="font-size: 2.5rem; font-weight: bold;">{{ $analisa_selesai_klinik ?? 0 }}</h3>
                <p class="text-white mb-0" style="font-size: 14px; opacity: 0.9;">Analisa Selesai</p>
              </div>
            </div>
          </div>
          --}}
        </div>
      </div>

      {{-- Statistik Tambahan untuk DKTR - DISEMBUNYIKAN --}}
      {{--
      <div class="col-12 mb-4">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #2D6BCF;">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Permohonan Uji Klinik</h6>
                    <h2 class="mb-0" style="font-weight: 700; color: #2D6BCF;">{{ $total_permohonan_uji_klinik ?? 0 }}</h2>
                    <small class="text-muted">Total permohonan yang telah dibuat</small>
                  </div>
                  <div class="bg-light rounded-circle p-4" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background-color: #e3f2fd !important;">
                    <i class="fas fa-clipboard-list" style="font-size: 36px; color: #2D6BCF;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #17a2b8;">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Status Permohonan</h6>
                    <h2 class="mb-0" style="font-weight: 700; color: #17a2b8;">{{ ($analisa_berjalan_klinik ?? 0) + ($analisa_selesai_klinik ?? 0) }}</h2>
                    <small class="text-muted">Total permohonan aktif</small>
                  </div>
                  <div class="bg-light rounded-circle p-4" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background-color: #e0f7fa !important;">
                    <i class="fas fa-chart-pie" style="font-size: 36px; color: #17a2b8;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      --}}

      {{-- Statistik Diagnosis Dokter untuk DKTR --}}
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-stethoscope"></i> Status Diagnosis</div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="card dash-mini-stat dash-mini-stat--amber">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Menunggu Diagnosis</h6>
                    <h2 class="mb-0" style="font-weight: 700; color: #d97706;">{{ $menunggu_diagnosis ?? 0 }}</h2>
                    <small class="text-muted">Perlu diagnosis dokter</small>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: #fff7ed;">
                    <i class="fas fa-stethoscope" style="font-size: 28px; color: #f59e0b;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 mb-3">
            <div class="card dash-mini-stat dash-mini-stat--green">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Sudah Terdiagnosis</h6>
                    <h2 class="mb-0" style="font-weight: 700; color: #16a34a;">{{ $sudah_terdiagnosis ?? 0 }}</h2>
                    <small class="text-muted">Selesai didiagnosis</small>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: #f0fdf4;">
                    <i class="fas fa-check-circle" style="font-size: 28px; color: #22c55e;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 mb-3">
            <div class="card dash-mini-stat dash-mini-stat--teal">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Dari Rujukan Dokter</h6>
                    <h2 class="mb-0" style="font-weight: 700; color: #0891b2;">{{ $dari_rujukan_dokter ?? 0 }}</h2>
                    <small class="text-muted">Permohonan rujukan</small>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: #ecfeff;">
                    <i class="fas fa-user-md" style="font-size: 28px; color: #06b6d4;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Quick Actions untuk DKTR --}}
      <div class="col-12">
        <div class="dash-panel">
            <div class="dash-section-title mb-3"><i class="fas fa-bolt"></i> Akses Cepat</div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <a href="{{ url('elits-permohonan-uji-klinik-2') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 12px; transition: all 0.3s ease; border-left: 4px solid #2D6BCF;">
                    <div class="card-body text-center p-4">
                      <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background-color: #e3f2fd !important;">
                        <i class="fas fa-clipboard-list" style="font-size: 32px; color: #2D6BCF;"></i>
                      </div>
                      <h6 class="text-dark mb-1" style="font-weight: 600;">Permohonan Uji Klinik</h6>
                      <small class="text-muted">Kelola permohonan uji</small>
                    </div>
                  </div>
                </a>
              </div>
              
              <div class="col-md-4 mb-3">
                <a href="{{ url('elits-analys/klinik') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 12px; transition: all 0.3s ease; border-left: 4px solid #28a745;">
                    <div class="card-body text-center p-4">
                      <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background-color: #e8f5e9 !important;">
                        <i class="fas fa-microscope" style="font-size: 32px; color: #28a745;"></i>
                      </div>
                      <h6 class="text-dark mb-1" style="font-weight: 600;">Data Analisa Klinik</h6>
                      <small class="text-muted">Lihat hasil analisa</small>
                    </div>
                  </div>
                </a>
              </div>
              
              <div class="col-md-4 mb-3">
                <a href="{{ url('mobile/dokter') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 12px; transition: all 0.3s ease; border-left: 4px solid #17a2b8;">
                    <div class="card-body text-center p-4">
                      <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background-color: #e0f7fa !important;">
                        <i class="fas fa-mobile-alt" style="font-size: 32px; color: #17a2b8;"></i>
                      </div>
                      <h6 class="text-dark mb-1" style="font-weight: 600;">Mobile Dokter</h6>
                      <small class="text-muted">Akses mobile</small>
                    </div>
                  </div>
                </a>
              </div>
            </div>
        </div>
      </div>
    @elseif (Auth::user()->getlevel->level == 'SOLAB')
      {{-- SOLAB: Dashboard yang lebih lengkap dan menarik --}}
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-hospital"></i> Ringkasan SOLAB</div>
      </div>
      <div class="col-12 mb-4">
        <div class="row">
          <div class="col-md-6 mb-4">
            <div class="card solab-card p-4 mb-2" style="height: 220px; border-radius: 15px;">
              <div class="d-flex align-items-center h-100">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-white rounded-circle p-3 me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                      <i class="fas fa-users" style="font-size: 24px; color: #2D6BCF;"></i>
                    </div>
                    <div>
                      <h5 class="text-white mb-0" style="font-weight: 600;">Total Pasien</h5>
                      <small class="text-white-50">Pasien Klinik</small>
                    </div>
                  </div>
                  <div class="text-center">
                    <h1 class="counter text-white mb-0" data-target="{{ $pasien_klinik }}" style="font-size: 3.5rem; font-weight: bold;">0</h1>
                    <small class="text-white-50">Pasien terdaftar</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 mb-4">
            <div class="card solab-card p-4 mb-2" style="height: 220px; border-radius: 15px;">
              <div class="d-flex align-items-center h-100">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-white rounded-circle p-3 me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                      <i class="fas fa-vial" style="font-size: 24px; color: #2D6BCF;"></i>
                    </div>
                    <div>
                      <h5 class="text-white mb-0" style="font-weight: 600;">Total Sampel</h5>
                      <small class="text-white-50">Sampel Klinik</small>
                    </div>
                  </div>
                  <div class="text-center">
                    <h1 class="counter text-white mb-0" data-target="{{ $total_sampel_klinik }}" style="font-size: 3.5rem; font-weight: bold;">0</h1>
                    <small class="text-white-50">Sampel Klinik</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Quick Stats untuk SOLAB --}}
      <div class="col-12 mb-4">
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm stats-card" style="border-radius: 10px;">
              <div class="card-body text-center p-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                  <i class="fas fa-chart-line text-primary" style="font-size: 24px;"></i>
                </div>
                <h6 class="text-muted mb-1">Analisa Berjalan</h6>
                <h4 class="text-primary mb-0">{{ $analisa_berjalan_klinik ?? 0 }}</h4>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm stats-card" style="border-radius: 10px;">
              <div class="card-body text-center p-4">
                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                  <i class="fas fa-check-circle text-success" style="font-size: 24px;"></i>
                </div>
                <h6 class="text-muted mb-1">Analisa Selesai</h6>
                <h4 class="text-success mb-0">{{ $analisa_selesai_klinik ?? 0 }}</h4>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm stats-card" style="border-radius: 10px;">
              <div class="card-body text-center p-4">
                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                  <i class="fas fa-clock text-warning" style="font-size: 24px;"></i>
                </div>
                <h6 class="text-muted mb-1">Permohonan Uji</h6>
                <h4 class="text-warning mb-0">{{ $total_permohonan_uji_klinik ?? 0 }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Quick Actions untuk SOLAB --}}
      <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body p-4">
            <h5 class="mb-4" style="color: #2D6BCF; font-weight: 600;">
              <i class="fas fa-bolt me-2"></i>Quick Actions
            </h5>
            <div class="row">
              <div class="col-md-3 mb-3">
                <a href="{{ url('elits-permohonan-uji-klinik-2') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 10px;">
                    <div class="card-body text-center p-3">
                      <i class="fas fa-clipboard-list text-primary mb-2" style="font-size: 28px;"></i>
                      <h6 class="text-dark mb-0">Permohonan Uji Klinik</h6>
                    </div>
                  </div>
                </a>
              </div>
              
              <div class="col-md-3 mb-3">
                <a href="{{ url('elits-analys') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 10px;">
                    <div class="card-body text-center p-3">
                      <i class="fas fa-microscope text-success mb-2" style="font-size: 28px;"></i>
                      <h6 class="text-dark mb-0">Data Analisa</h6>
                    </div>
                  </div>
                </a>
              </div>
              
              <div class="col-md-3 mb-3">
                <a href="{{ url('elits-inventories') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 10px;">
                    <div class="card-body text-center p-3">
                      <i class="fas fa-boxes text-info mb-2" style="font-size: 28px;"></i>
                      <h6 class="text-dark mb-0">Data Inventori</h6>
                    </div>
                  </div>
                </a>
              </div>
              
              <div class="col-md-3 mb-3">
                <a href="{{ url('stock-opname') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 10px;">
                    <div class="card-body text-center p-3">
                      <i class="fas fa-clipboard-check text-warning mb-2" style="font-size: 28px;"></i>
                      <h6 class="text-dark mb-0">Stok Opname</h6>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    @elseif (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'KIM')
      {{-- KIM: Dashboard khusus Lab Kimia --}}
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-flask"></i> Ringkasan Lab Kimia</div>
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--blue">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-clipboard-list"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_permohonan_uji }}">0</h3>
                <p class="dash-stat-card__label">Permohonan Uji Kimia</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--green">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-vial"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_sample }}">0</h3>
                <p class="dash-stat-card__label">Sampel Kimia</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--amber">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-hourglass-half"></i></div>
                <h3 class="dash-stat-card__value">{{ $total_berjalan }}</h3>
                <p class="dash-stat-card__label">Analisa Berjalan</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--teal">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-check-circle"></i></div>
                <h3 class="dash-stat-card__value">{{ $total_selesai }}</h3>
                <p class="dash-stat-card__label">Analisa Selesai</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    @elseif (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'MBI')
      {{-- MBI: Dashboard khusus Lab Mikrobiologi --}}
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-microscope"></i> Ringkasan Lab Mikrobiologi</div>
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--blue">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-clipboard-list"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_permohonan_uji }}">0</h3>
                <p class="dash-stat-card__label">Permohonan Mikrobiologi</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--green">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-vial"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_sample }}">0</h3>
                <p class="dash-stat-card__label">Sampel Mikrobiologi</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--amber">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-hourglass-half"></i></div>
                <h3 class="dash-stat-card__value">{{ $total_berjalan }}</h3>
                <p class="dash-stat-card__label">Analisa Berjalan</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--teal">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-check-circle"></i></div>
                <h3 class="dash-stat-card__value">{{ $total_selesai }}</h3>
                <p class="dash-stat-card__label">Analisa Selesai</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    @else
      {{-- User lain: Ringkasan Kesmas + Klinik --}}
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-chart-bar"></i> Ringkasan Laboratorium</div>
        <div class="row">
          <div class="col-md-4 col-lg-2 mb-3">
            <div class="card dash-stat-card dash-stat-card--blue">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-clipboard-list"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_permohonan_uji }}">0</h3>
                <p class="dash-stat-card__label">Permohonan Kesmas</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 col-lg-2 mb-3">
            <div class="card dash-stat-card dash-stat-card--green">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-vial"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_sample }}">0</h3>
                <p class="dash-stat-card__label">Sampel Kesmas</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 col-lg-2 mb-3">
            <div class="card dash-stat-card dash-stat-card--amber">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-hourglass-half"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_berjalan }}">0</h3>
                <p class="dash-stat-card__label">Analisa Berjalan</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 col-lg-2 mb-3">
            <div class="card dash-stat-card dash-stat-card--teal">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-check-circle"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ $total_selesai }}">0</h3>
                <p class="dash-stat-card__label">Analisa Selesai</p>
              </div>
            </div>
          </div>
          @if (!Auth::user()->laboratorium || Auth::user()->laboratorium->kode_laboratorium != 'KIM')
            <div class="col-md-4 col-lg-2 mb-3">
              <div class="card dash-stat-card dash-stat-card--purple">
                <div class="card-body">
                  <div class="dash-stat-card__icon"><i class="fas fa-notes-medical"></i></div>
                  <h3 class="counter dash-stat-card__value" data-target="{{ $total_permohonan_uji_klinik }}">0</h3>
                  <p class="dash-stat-card__label">Permohonan Klinik</p>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
              <div class="card dash-stat-card dash-stat-card--blue">
                <div class="card-body">
                  <div class="dash-stat-card__icon"><i class="fas fa-users"></i></div>
                  <h3 class="counter dash-stat-card__value" data-target="{{ $pasien_klinik }}">0</h3>
                  <p class="dash-stat-card__label">Pasien Klinik</p>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    @endif
    </div>

  @if (
    Auth::user()->getlevel->level != 'SOLAB' &&
    Auth::user()->getlevel->level != 'DKTR' &&
    (
      !Auth::user()->laboratorium ||
      Auth::user()->laboratorium->kode_laboratorium != 'KLI'
    )
  )
    <div class="dash-charts">
      <div class="dash-chart-card">
        <div class="dash-chart-card__title"><i class="fas fa-chart-line"></i> Permohonan per Bulan</div>
        <canvas id="chartPendapatan" height="280"></canvas>
      </div>
      <div class="dash-chart-card">
        <div class="dash-chart-card__title"><i class="fas fa-chart-bar"></i> Sampel berdasarkan Jenis</div>
        <canvas id="chartSample" height="280"></canvas>
      </div>
    </div>
  @endif

  @if (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'KIM')
    {{-- KIM: Quick links --}}
    <div class="dash-quick-bar card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="ti-agenda mr-2"></i> Data Analisa</h5>
            <a href="{{ url('elits-analys') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-database mr-2"></i> Data Semua Sampel</h5>
            <a href="{{ url('elits-samples/all') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-map-marked-alt mr-2"></i> Persebaran Data</h5>
            <a href="{{ url('dokter/dashboard') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
        </div>
      </div>
    </div>
  @elseif (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'MBI')
    {{-- MBI: Quick links --}}
    <div class="dash-quick-bar card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="ti-agenda mr-2"></i> Data Analisa</h5>
            <a href="{{ url('elits-analys') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-database mr-2"></i> Data Semua Sampel</h5>
            <a href="{{ url('elits-samples/all') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-map-marked-alt mr-2"></i> Persebaran Data</h5>
            <a href="{{ url('dokter/dashboard') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
        </div>
      </div>
    </div>
  @elseif (
    Auth::user()->getlevel->level != 'SOLAB' &&
    Auth::user()->getlevel->level != 'DKTR' &&
    (
      !Auth::user()->laboratorium ||
      (Auth::user()->laboratorium->kode_laboratorium != 'KLI' && Auth::user()->laboratorium->kode_laboratorium != 'KIM')
    )
  )
    <div class="dash-quick-bar card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="ti-agenda mr-2"></i> Permohonan Uji</h5>
            <a href="{{ url('elits-permohonan-uji') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-boxes mr-2"></i> Data Inventori</h5>
            <a href="{{ url('elits-inventories') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-clipboard-check mr-2"></i> Stok Opname</h5>
            <a href="{{ url('stock-opname') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
        </div>
      </div>
    </div>
  @endif
  </div>{{-- /.dash-page --}}
@endsection

@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js')}}"></script>
  <script>
    $(document).ready(function() {
      $('.counter').each(function() {
        let $this = $(this);
        let total = parseInt($this.data('target'), 10);
        if (isNaN(total)) return;
        let currentCount = 0;
        let increment = Math.max(1, Math.ceil(total / 80));

        function updateCounter() {
          if (currentCount < total) {
            currentCount += increment;
            if (currentCount > total) {
              currentCount = total;
            }
            $this.text(currentCount).addClass('counter');
            setTimeout(updateCounter, 10);
          }
        }

        updateCounter();
      });
    });
  </script>
  @if (
    Auth::user()->getlevel->level != 'SOLAB' &&
    Auth::user()->getlevel->level != 'DKTR' &&
    (
      !Auth::user()->laboratorium ||
      Auth::user()->laboratorium->kode_laboratorium != 'KLI'
    )
  )
  <script src="{{asset('assets/admin/cdn-local/js/chart.min.js')}}"></script>
  <script>
      const ctx = document.getElementById('chartPendapatan');
      if (ctx) {
        const chartCtx = ctx.getContext('2d');
        new Chart(chartCtx, {
      type: 'line',
      data: {
        labels: {!! json_encode($bulans) !!},
        datasets: [{
          label: 'Jumlah Permohonan',
          data: {!! json_encode($pendapatans) !!},
          borderColor: '#2D6BCF',
          backgroundColor: 'rgba(45, 107, 207, 0.1)',
          borderWidth: 2.5,
          fill: true,
          tension: 0.35,
          pointBackgroundColor: '#2D6BCF',
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
          x: { grid: { display: false } }
        }
      }
    });
      }

      const ctx2 = document.getElementById('chartSample');
      if (ctx2) {
        const chartCtx2 = ctx2.getContext('2d');

    const chartPalette = ['#2D6BCF', '#667eea', '#22c55e', '#f59e0b', '#06b6d4', '#764ba2', '#ef4444', '#8b5cf6'];
    const backgroundColors = {!! json_encode($sampleTypes) !!}.map((_, i) => chartPalette[i % chartPalette.length]);

        new Chart(chartCtx2, {
      type: 'bar',
      data: {
        labels: {!! json_encode($sampleTypes) !!},
        datasets: [{
          label: 'Total Sampel',
          data: {!! json_encode($countSample) !!},
          backgroundColor: backgroundColors,
          borderRadius: 8,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
          x: { grid: { display: false } }
        }
      }
    });
      }
  </script>
  @endif
@endsection
