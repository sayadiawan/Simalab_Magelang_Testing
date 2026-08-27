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

  $videoDir = 'assets/admin/video';
  $heroVideoPath = null;
  $heroPosterPath = null;
  $heroVideoCandidates = [
      'hero-lab.mp4',
      'mixkit-scientist-mixing-liquids-in-a-laboratory-4719-hd-ready.mp4',
      'mixkit-laboratory-worker-looking-at-a-test-tube-21454-hd-ready.mp4',
      'mixkit-woman-working-with-samples-in-laboratory-21457-hd-ready.mp4',
      'mixkit-drops-filling-a-lab-tube-17456-hd-ready.mp4',
  ];
  foreach ($heroVideoCandidates as $candidate) {
      $relative = $videoDir . '/' . $candidate;
      if (is_file(public_path($relative))) {
          $heroVideoPath = $relative;
          break;
      }
  }
  $hasHeroVideo = !empty($heroVideoPath);
  foreach (['hero-poster.jpg', 'poster-pengujian.jpg'] as $posterCandidate) {
      $relative = $videoDir . '/' . $posterCandidate;
      if (is_file(public_path($relative))) {
          $heroPosterPath = $relative;
          break;
      }
  }
  $hasHeroPoster = !empty($heroPosterPath);
@endphp

@section('content')
  <style>
    .dash-page {
      --dash-primary: #0b3a5c;
      --dash-primary-dark: #06283f;
      --dash-accent: #0d8f7f;
      --dash-accent-dark: #0a7a6c;
      --dash-surface: #ffffff;
      --dash-muted: #5c6d75;
      --dash-radius: 12px;
      --dash-shadow: 0 1px 2px rgba(6, 40, 63, 0.06), 0 1px 3px rgba(6, 40, 63, 0.1);
      --dash-shadow-hover: 0 10px 20px rgba(6, 40, 63, 0.12), 0 3px 6px rgba(6, 40, 63, 0.08);
    }

    .dash-hero {
      position: relative;
      overflow: hidden;
      isolation: isolate;
      border-radius: var(--dash-radius);
      background: linear-gradient(135deg, #06283f 0%, #0b3a5c 42%, #0d8f7f 100%);
      box-shadow: var(--dash-shadow-hover);
      padding: 2rem 2.25rem;
      min-height: 200px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1.5rem;
    }

    .dash-hero__media {
      position: absolute;
      inset: 0;
      z-index: 0;
      overflow: hidden;
      pointer-events: none;
    }

    .dash-hero__video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transform: scaleX(-1) scale(1.08);
      filter: grayscale(0.85) contrast(1.05) brightness(0.52);
    }

    .dash-hero__tint {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, #06283f 0%, #0b3a5c 45%, #0d8f7f 100%);
      mix-blend-mode: color;
      opacity: 0.88;
    }

    .dash-hero__veil {
      position: absolute;
      inset: 0;
      background:
        linear-gradient(105deg, rgba(6, 40, 63, 0.92) 0%, rgba(6, 40, 63, 0.62) 42%, rgba(10, 90, 88, 0.78) 100%),
        radial-gradient(ellipse 65% 55% at 18% 85%, rgba(22, 168, 146, 0.22), transparent 72%);
    }

    .dash-hero__glow {
      position: absolute;
      inset: 0;
      z-index: 1;
      pointer-events: none;
      background:
        radial-gradient(circle at 88% 18%, rgba(255,255,255,0.14) 0%, transparent 42%),
        radial-gradient(circle at 12% 88%, rgba(255,255,255,0.08) 0%, transparent 38%);
    }

    .dash-hero::before,
    .dash-hero::after {
      display: none;
    }

    .dash-hero__content {
      position: relative;
      z-index: 2;
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
      background: transparent !important;
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

    .dash-stat-card__value,
    .dash-stat-card .counter {
      font-size: 2.25rem;
      font-weight: 800;
      color: #fff !important;
      line-height: 1;
      margin-bottom: 0.35rem;
    }

    .dash-stat-card__label {
      color: rgba(255,255,255,0.95) !important;
      font-size: 0.85rem;
      font-weight: 600;
      margin: 0;
    }

    .dash-stat-card--blue { background: linear-gradient(135deg, #0b3a5c 0%, #06283f 100%) !important; }
    .dash-stat-card--blue .dash-stat-card__icon i { color: #0b3a5c; font-size: 1.75rem; }
    .dash-stat-card--green { background: linear-gradient(135deg, #0d8f7f 0%, #0a7a6c 100%) !important; }
    .dash-stat-card--green .dash-stat-card__icon i { color: #0d8f7f; font-size: 1.75rem; }
    .dash-stat-card--amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; }
    .dash-stat-card--amber .dash-stat-card__icon i { color: #f59e0b; font-size: 1.75rem; }
    .dash-stat-card--teal { background: linear-gradient(135deg, #16a892 0%, #0d8f7f 100%) !important; }
    .dash-stat-card--teal .dash-stat-card__icon i { color: #16a892; font-size: 1.75rem; }
    .dash-stat-card--purple { background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%) !important; }
    .dash-stat-card--purple .dash-stat-card__icon i { color: #0d8f7f; font-size: 1.75rem; }

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
      background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
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
      grid-template-columns: 1.35fr 1fr;
      gap: 1.25rem;
      margin-top: 1.75rem;
    }

    .dash-charts-head {
      grid-column: 1 / -1;
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 0.25rem;
      position: relative;
      z-index: 5;
    }

    .dash-charts-head > div:first-child {
      position: relative;
      z-index: 2;
      flex: 1 1 280px;
      min-width: 0;
    }

    .dash-chart-actions {
      position: relative;
      z-index: 1;
      flex: 1 1 320px;
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
      justify-content: flex-end;
    }

    .dash-charts-head h3 {
      margin: 0;
      font-size: 1.1rem;
      font-weight: 800;
      color: #06283f;
      letter-spacing: -0.02em;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .dash-charts-head h3 i { color: #0d8f7f; }

    .dash-charts-head p {
      margin: 4px 0 0;
      font-size: 13px;
      color: #5c6d75;
    }

    .dash-chart-tabs {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      padding: 0.28rem;
      border-radius: 999px;
      background: #e7f4f2;
      border: 1px solid #d5e8e4;
      margin-top: 0.75rem;
      position: relative;
      z-index: 3;
    }

    .dash-chart-tab {
      border: none;
      background: transparent;
      color: #0b3a5c;
      font-weight: 700;
      font-size: 0.82rem;
      padding: 0.42rem 1rem;
      border-radius: 999px;
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
      position: relative;
      z-index: 1;
      pointer-events: auto;
    }

    .dash-chart-tab:hover { background: rgba(13, 143, 127, 0.12); }

    .dash-chart-tab.is-active {
      background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
      color: #fff;
      box-shadow: 0 4px 12px rgba(11, 58, 92, 0.22);
    }

    .dash-chart-range {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 0.55rem;
      padding: 0.55rem 0.7rem;
      border-radius: 12px;
      background: #f3f8f7;
      border: 1px solid #d5e8e4;
    }

    .dash-chart-range__field {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }

    .dash-chart-range__field label {
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: #5c6d75;
      margin: 0;
    }

    .dash-chart-range__field input[type="date"] {
      border: 1px solid #c9dbd7;
      border-radius: 8px;
      padding: 0.4rem 0.55rem;
      font-size: 0.82rem;
      font-weight: 600;
      color: #06283f;
      background: #fff;
      font-family: inherit;
      min-width: 9.5rem;
    }

    .dash-chart-range__field input[type="date"]:focus {
      outline: none;
      border-color: #0d8f7f;
      box-shadow: 0 0 0 3px rgba(13, 143, 127, 0.15);
    }

    .dash-chart-range__sep {
      align-self: center;
      color: #8a9a9f;
      font-weight: 700;
      padding-bottom: 0.15rem;
    }

    .dash-chart-range__apply {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border: none;
      border-radius: 8px;
      padding: 0.45rem 0.9rem;
      font-size: 0.78rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      color: #fff;
      background: linear-gradient(135deg, #0b3a5c, #0d8f7f);
      box-shadow: 0 4px 12px rgba(11, 58, 92, 0.18);
    }

    .dash-chart-range__apply:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 16px rgba(11, 58, 92, 0.24);
    }

    .dash-chart-range__reset {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      border: 1px solid #dbe5e3;
      border-radius: 8px;
      padding: 0.45rem 0.75rem;
      font-size: 0.78rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      color: #0b3a5c;
      background: #fff;
      text-decoration: none;
    }

    .dash-chart-range__reset:hover {
      background: #e7f4f2;
      border-color: #0d8f7f;
      color: #0b3a5c;
      text-decoration: none;
    }

    .btn-chart-dl {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      border: none;
      border-radius: 8px;
      padding: 9px 14px;
      font-size: 12.5px;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s;
    }

    .btn-chart-dl--primary {
      background: linear-gradient(135deg, #0b3a5c, #0d8f7f);
      color: #fff;
      box-shadow: 0 6px 16px rgba(11, 58, 92, 0.22);
    }

    .btn-chart-dl--primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 22px rgba(11, 58, 92, 0.28);
    }

    .btn-chart-dl--ghost {
      background: #fff;
      color: #0b3a5c;
      border: 1px solid #dbe5e3;
    }

    .btn-chart-dl--ghost:hover {
      background: #e7f4f2;
      border-color: #0d8f7f;
      color: #0d8f7f;
      transform: translateY(-1px);
    }

    .dash-chart-card {
      position: relative;
      background: #fff;
      border-radius: var(--dash-radius);
      box-shadow: var(--dash-shadow);
      padding: 1.25rem 1.35rem 1.1rem;
      min-height: 420px;
      overflow: hidden;
      isolation: isolate;
    }

    .dash-chart-card::before {
      content: "";
      position: absolute;
      inset: 0;
      z-index: 0;
      background:
        radial-gradient(ellipse 60% 50% at 100% 0%, rgba(13, 143, 127, 0.1), transparent 60%),
        radial-gradient(ellipse 50% 45% at 0% 100%, rgba(11, 58, 92, 0.08), transparent 55%);
      pointer-events: none;
    }

    .dash-chart-card--dark {
      background: linear-gradient(155deg, #06283f 0%, #0b3a5c 48%, #0a5a58 100%);
      color: #fff;
    }

    .dash-chart-card--dark::before {
      background:
        radial-gradient(ellipse 55% 45% at 85% 15%, rgba(42, 211, 182, 0.22), transparent 60%),
        radial-gradient(ellipse 40% 40% at 10% 90%, rgba(255,255,255,0.06), transparent 55%);
    }

    .dash-chart-card__top {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 0.85rem;
    }

    .dash-chart-card__title {
      font-size: 0.98rem;
      font-weight: 800;
      color: #06283f;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      border: none;
      padding: 0;
    }

    .dash-chart-card__title small {
      display: block;
      font-size: 11.5px;
      font-weight: 500;
      color: #5c6d75;
      margin-top: 3px;
      letter-spacing: 0;
    }

    .dash-chart-card__title i { color: #0d8f7f; }

    .dash-chart-card--dark .dash-chart-card__title {
      color: #fff;
    }

    .dash-chart-card--dark .dash-chart-card__title small {
      color: rgba(255,255,255,0.7);
    }

    .dash-chart-card--dark .dash-chart-card__title i {
      color: #7fd4c8;
    }

    .dash-chart-card__canvas-wrap {
      position: relative;
      z-index: 1;
      height: 320px;
    }

    .dash-chart-card--donut .dash-chart-card__canvas-wrap {
      height: 300px;
      max-width: 340px;
      margin: 0 auto;
    }

    .dash-chart-card--paket {
      grid-column: 1 / -1;
    }

    .dash-chart-card--paket.is-hidden {
      display: none !important;
    }

    .dash-chart-card--paket .dash-chart-card__canvas-wrap {
      height: 340px;
      max-width: none;
      margin: 0;
    }

    .dash-chart-meta {
      position: relative;
      z-index: 1;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 12px;
    }

    .dash-chart-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 11.5px;
      font-weight: 700;
      background: #e7f4f2;
      color: #0b3a5c;
    }

    .dash-chart-card--dark .dash-chart-pill {
      background: rgba(255,255,255,0.12);
      color: #fff;
    }

    .dash-chart-pill strong {
      font-weight: 800;
      color: #0d8f7f;
    }

    .dash-chart-card--dark .dash-chart-pill strong {
      color: #7fd4c8;
    }

    @media (max-width: 1023px) {
      .dash-charts {
        grid-template-columns: 1fr;
      }

      .dash-chart-card,
      .dash-chart-card__canvas-wrap {
        min-height: 0;
      }

      .dash-chart-card__canvas-wrap {
        height: 280px;
      }
    }

    .dash-quick-bar {
      border: none;
      border-radius: var(--dash-radius);
      overflow: hidden;
      box-shadow: var(--dash-shadow);
      margin-top: 1.75rem;
      background: linear-gradient(135deg, #06283f 0%, #0b3a5c 55%, #0d8f7f 100%) !important;
    }

    .dash-quick-bar .card-body {
      background: transparent !important;
      padding: 1.5rem 2rem;
    }

    .dash-quick-bar__item {
      text-align: center;
      padding: 0.5rem 1rem;
    }

    .dash-quick-bar__item h5,
    .dash-quick-bar__item h5 i {
      color: #fff !important;
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
    }

    .dash-quick-bar__item .btn {
      background: #fff !important;
      color: #0b3a5c !important;
      border: none !important;
      border-radius: 8px;
      font-weight: 600;
      padding: 0.45rem 1.1rem;
      transition: all 0.2s ease;
    }

    .dash-quick-bar__item .btn:hover {
      background: #e7f4f2 !important;
      color: #06283f !important;
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
      .dash-charts { grid-template-columns: 1fr; }
    }
  </style>

  <div class="dash-page">
    <div class="dash-hero">
      @if ($hasHeroVideo)
        <div class="dash-hero__media" aria-hidden="true">
          <video class="dash-hero__video" id="dashHeroVideo" muted loop playsinline preload="none"
            @if ($hasHeroPoster) poster="{{ asset($heroPosterPath) }}" @endif>
            <source data-src="{{ asset($heroVideoPath) }}" type="video/mp4">
          </video>
          <span class="dash-hero__tint"></span>
          <span class="dash-hero__veil"></span>
        </div>
        <span class="dash-hero__glow" aria-hidden="true"></span>
      @endif
      <div class="dash-hero__content">
        <div class="dash-hero__badge">
          <i class="fas fa-flask"></i> SIMLAB · Lingkungan pengujian
        </div>
        <h1 class="dash-hero__title">{{ $greeting }}, {{ $user->name }}!</h1>
        <p class="dash-hero__subtitle">
          Selamat datang di dashboard SIMLAB. Pantau ringkasan permohonan uji,
          sampel, dan analisa laboratorium Anda di satu tempat.
        </p>
        @if(Auth::user()->getlevel->level == 'BNDR')
          <a href="{{ url('elits-pendapatan-klinik') }}" class="dash-hero__cta">
            <i class="fas fa-coins"></i> Dashboard Bendahara
          </a>
        @elseif(Auth::user()->getlevel->level == 'DKTR' || (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'KLI'))
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
    </div>

    <div class="mt-4">
    @if (Auth::user()->getlevel->level == 'BNDR')
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-wallet"></i> Ringkasan Bendahara</div>
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--green">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-hand-holding-usd"></i></div>
                <h3 class="dash-stat-card__value">Rp {{ number_format($totalPendapatanKlinik ?? 0, 0, ',', '.') }}</h3>
                <p class="dash-stat-card__label">Pendapatan Klinik</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--teal">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-flask"></i></div>
                <h3 class="dash-stat-card__value">Rp {{ number_format($totalPendapatanKesmas ?? 0, 0, ',', '.') }}</h3>
                <p class="dash-stat-card__label">Pendapatan Kesmas</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--blue">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-receipt"></i></div>
                <h3 class="counter dash-stat-card__value" data-target="{{ ($jumlahNotaKlinik ?? 0) + ($jumlahNotaKesmas ?? 0) }}">0</h3>
                <p class="dash-stat-card__label">Jumlah Nota</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card dash-stat-card dash-stat-card--amber">
              <div class="card-body">
                <div class="dash-stat-card__icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3 class="dash-stat-card__value">Rp {{ number_format(($totalPendapatanKlinik ?? 0) + ($totalPendapatanKesmas ?? 0), 0, ',', '.') }}</h3>
                <p class="dash-stat-card__label">Total Pendapatan</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    @elseif (Auth::user()->getlevel->level == 'DKTR' || (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'KLI'))
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
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #0b3a5c;">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Permohonan Uji Klinik</h6>
                    <h2 class="mb-0" style="font-weight: 700; color: #0b3a5c;">{{ $total_permohonan_uji_klinik ?? 0 }}</h2>
                    <small class="text-muted">Total permohonan yang telah dibuat</small>
                  </div>
                  <div class="bg-light rounded-circle p-4" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background-color: #e3f2fd !important;">
                    <i class="fas fa-clipboard-list" style="font-size: 36px; color: #0b3a5c;"></i>
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
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 12px; transition: all 0.3s ease; border-left: 4px solid #0b3a5c;">
                    <div class="card-body text-center p-4">
                      <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background-color: #e3f2fd !important;">
                        <i class="fas fa-clipboard-list" style="font-size: 32px; color: #0b3a5c;"></i>
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
    @elseif (Auth::user()->getlevel->level == 'SOLK')
      {{-- Pengambil Sampel Klinik --}}
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-hospital"></i> Ringkasan Pengambil Sampel Klinik</div>
      </div>
      <div class="col-12 mb-4">
        <div class="row">
          <div class="col-md-6 mb-4">
            <div class="card solab-card p-4 mb-2" style="height: 220px; border-radius: 15px;">
              <div class="d-flex align-items-center h-100">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-white rounded-circle p-3 me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                      <i class="fas fa-users" style="font-size: 24px; color: #0b3a5c;"></i>
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
                      <i class="fas fa-vial" style="font-size: 24px; color: #0b3a5c;"></i>
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

      <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body p-4">
            <h5 class="mb-4" style="color: #0b3a5c; font-weight: 600;">
              <i class="fas fa-bolt me-2"></i>Aksi Cepat
            </h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <a href="{{ url('elits-permohonan-uji-klinik/verifikasi/lists?status_filter=pengambilan_sample') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 10px;">
                    <div class="card-body text-center p-3">
                      <i class="fas fa-vial text-primary mb-2" style="font-size: 28px;"></i>
                      <h6 class="text-dark mb-0">Pengambilan Sampel Klinik</h6>
                    </div>
                  </div>
                </a>
              </div>
              
              <div class="col-md-6 mb-3">
                <a href="{{ url('monitoring-sampling-penerima') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 10px;">
                    <div class="card-body text-center p-3">
                      <i class="fas fa-clipboard-list text-success mb-2" style="font-size: 28px;"></i>
                      <h6 class="text-dark mb-0">Monitoring Sampling</h6>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    @elseif (Auth::user()->getlevel->level == 'SOLM')
      {{-- Pengambil Sampel Kesmas --}}
      <div class="col-12 mb-2">
        <div class="dash-section-title"><i class="fas fa-flask"></i> Ringkasan Pengambil Sampel Kesmas</div>
      </div>
      <div class="col-12 mb-4">
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm stats-card" style="border-radius: 10px;">
              <div class="card-body text-center p-4">
                <h6 class="text-muted mb-1">Permohonan Uji</h6>
                <h4 class="text-primary mb-0">{{ $total_permohonan_uji ?? 0 }}</h4>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm stats-card" style="border-radius: 10px;">
              <div class="card-body text-center p-4">
                <h6 class="text-muted mb-1">Total Sampel</h6>
                <h4 class="text-info mb-0">{{ $total_sample ?? 0 }}</h4>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm stats-card" style="border-radius: 10px;">
              <div class="card-body text-center p-4">
                <h6 class="text-muted mb-1">Analisa Berjalan</h6>
                <h4 class="text-warning mb-0">{{ $total_berjalan ?? 0 }}</h4>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm stats-card" style="border-radius: 10px;">
              <div class="card-body text-center p-4">
                <h6 class="text-muted mb-1">Analisa Selesai</h6>
                <h4 class="text-success mb-0">{{ $total_selesai ?? 0 }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-body p-4">
            <h5 class="mb-4" style="color: #0b3a5c; font-weight: 600;">
              <i class="fas fa-bolt me-2"></i>Aksi Cepat
            </h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <a href="{{ url('elits-permohonan-uji') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 10px;">
                    <div class="card-body text-center p-3">
                      <i class="fas fa-file-medical text-primary mb-2" style="font-size: 28px;"></i>
                      <h6 class="text-dark mb-0">Permohonan Uji Kesmas</h6>
                    </div>
                  </div>
                </a>
              </div>
              <div class="col-md-6 mb-3">
                <a href="{{ url('elits-analys?status_filter=pengambilan_sample') }}" class="text-decoration-none">
                  <div class="card border-0 shadow-sm h-100 quick-action-card" style="border-radius: 10px;">
                    <div class="card-body text-center p-3">
                      <i class="fas fa-vial text-success mb-2" style="font-size: 28px;"></i>
                      <h6 class="text-dark mb-0">Pengambilan Sampel Kesmas</h6>
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

  {{-- Grafik: tab Kesmas / Klinik / Keuangan --}}
  @php
    $showChartKesmas = $showChartKesmas ?? true;
    $showChartKlinik = $showChartKlinik ?? true;
    $showChartKeuangan = $showChartKeuangan ?? true;
    $defaultChartTab = $defaultChartTab ?? ($showChartKesmas ? 'kesmas' : 'klinik');
    if ($defaultChartTab === 'keuangan' && $showChartKeuangan) {
      $activeChart = 'keuangan';
    } elseif ($defaultChartTab === 'klinik' && $showChartKlinik) {
      $activeChart = 'klinik';
    } elseif ($defaultChartTab === 'kesmas' && $showChartKesmas) {
      $activeChart = 'kesmas';
    } else {
      $activeChart = $showChartKesmas ? 'kesmas' : 'klinik';
    }
    $chartFrom = $chartFrom ?? now()->subMonths(11)->startOfMonth()->format('Y-m-d');
    $chartTo = $chartTo ?? now()->format('Y-m-d');
    $rangeLabel = \Carbon\Carbon::parse($chartFrom)->format('d/m/Y') . ' – ' . \Carbon\Carbon::parse($chartTo)->format('d/m/Y');
    $keuanganIsMoney = $chartKeuanganIsMoney ?? true;
    if ($activeChart === 'keuangan') {
      $chartMonths = $chartKeuanganMonths ?? [];
      $chartSeries = $chartKeuanganSeriesTotal ?? [];
      $chartSampleLabels = $chartKeuanganLabels ?? [];
      $chartSampleValues = $chartKeuanganValues ?? [];
      $donutTitle = $chartKeuanganDonutTitle ?? 'Komposisi pendapatan';
      $donutSub = $chartKeuanganDonutSub ?? 'Total nota Kesmas vs Klinik';
      $trendSub = $chartKeuanganTrendSub ?? ('Pendapatan (total nota) per bulan (' . $rangeLabel . ')');
    } elseif ($activeChart === 'kesmas' && $showChartKesmas) {
      $chartMonths = $chartKesmasMonths ?? ($bulans ?? []);
      $chartSeries = $chartKesmasSeries ?? ($pendapatans ?? []);
      $chartSampleLabels = $chartKesmasLabels ?? ($sampleTypes ?? []);
      $chartSampleValues = $chartKesmasValues ?? ($countSample ?? []);
      $labHint = !empty($chartLabScopeLabel) ? ' · ' . $chartLabScopeLabel : '';
      $donutTitle = 'Komposisi sampel';
      $donutSub = 'Proporsi sampel berdasarkan jenis' . $labHint;
      $trendSub = 'Volume permohonan uji Kesmas per bulan (' . $rangeLabel . ')' . $labHint;
    } else {
      $chartMonths = $chartKlinikMonths ?? ($bulans ?? []);
      $chartSeries = $chartKlinikSeries ?? ($pendapatans ?? []);
      $chartSampleLabels = $chartKlinikLabels ?? ($sampleTypes ?? []);
      $chartSampleValues = $chartKlinikValues ?? ($countSample ?? []);
      $labHint = !empty($chartLabScopeLabel) ? ' · ' . $chartLabScopeLabel : '';
      $donutTitle = 'Pemeriksaan Haji vs Non-Haji';
      $donutSub = 'Proporsi pemeriksaan klinik berdasarkan jenis' . $labHint;
      $trendSub = 'Jumlah pemeriksaan klinik per bulan (' . $rangeLabel . ')' . $labHint;
    }
    $chartTotalPermohonan = collect($chartSeries)->sum();
    $chartPeak = count($chartSeries) ? max($chartSeries) : 0;
    $chartAvg = count($chartSeries) ? round($chartTotalPermohonan / max(count($chartSeries), 1), 1) : 0;
    $chartSampleTotal = collect($chartSampleValues)->sum();
  @endphp
  <div class="dash-charts" id="dashChartsShowcase">
      <div class="dash-charts-head">
        <div>
          <h3><i class="fas fa-chart-area"></i> Analitik laboratorium
            @if (!empty($chartLabScopeLabel))
              <small style="font-weight:500;opacity:.85;">· {{ $chartLabScopeLabel }}</small>
            @endif
          </h3>
          <p>
            @if (Auth::user()->getlevel->level == 'BNDR')
              Pantau tren dan komposisi nota <strong>Lunas</strong> vs <strong>Belum Lunas</strong> pada dashboard keuangan.
            @elseif (!empty($chartLabScopeLabel) && ($showChartKesmas ?? true) && !($showChartKlinik ?? true))
              Grafik Kesmas difilter untuk laboratorium <strong>{{ $chartLabScopeLabel }}</strong>.
            @elseif (!empty($chartLabScopeLabel) && ($showChartKlinik ?? true) && !($showChartKesmas ?? true))
              Grafik Klinik untuk laboratorium <strong>{{ $chartLabScopeLabel }}</strong>.
            @else
              Pilih tab dan rentang tanggal untuk mengendalikan grafik <strong>Kesmas</strong>, <strong>Klinik</strong>, atau <strong>Keuangan</strong>.
            @endif
          </p>
          <div class="dash-chart-tabs" role="tablist" aria-label="Jenis grafik">
            @if ($showChartKesmas)
              <button type="button" class="dash-chart-tab {{ $activeChart === 'kesmas' ? 'is-active' : '' }}" data-chart-tab="kesmas" role="tab" aria-selected="{{ $activeChart === 'kesmas' ? 'true' : 'false' }}">
                <i class="fas fa-flask"></i> Grafik Kesmas
              </button>
            @endif
            @if ($showChartKlinik)
              <button type="button" class="dash-chart-tab {{ $activeChart === 'klinik' ? 'is-active' : '' }}" data-chart-tab="klinik" role="tab" aria-selected="{{ $activeChart === 'klinik' ? 'true' : 'false' }}">
                <i class="fas fa-notes-medical"></i> Grafik Klinik
              </button>
            @endif
            @if ($showChartKeuangan ?? true)
              <button type="button" class="dash-chart-tab {{ $activeChart === 'keuangan' ? 'is-active' : '' }}" data-chart-tab="keuangan" role="tab" aria-selected="{{ $activeChart === 'keuangan' ? 'true' : 'false' }}">
                <i class="fas fa-coins"></i> Keuangan
              </button>
            @endif
          </div>
        </div>
        <div class="dash-chart-actions">
          <form method="GET" action="{{ url()->current() }}" class="dash-chart-range" id="formChartRange" aria-label="Filter rentang tanggal grafik">
            <input type="hidden" name="chart_tab" id="chartTabInput" value="{{ $activeChart }}">
            <div class="dash-chart-range__field">
              <label for="chartFrom">Dari</label>
              <input type="date" id="chartFrom" name="chart_from" value="{{ $chartFrom }}" required>
            </div>
            <span class="dash-chart-range__sep" aria-hidden="true">—</span>
            <div class="dash-chart-range__field">
              <label for="chartTo">Sampai</label>
              <input type="date" id="chartTo" name="chart_to" value="{{ $chartTo }}" required>
            </div>
            <button type="submit" class="dash-chart-range__apply">
              <i class="fas fa-filter"></i> Terapkan
            </button>
            <a href="{{ url()->current() }}?chart_tab={{ $activeChart }}" class="dash-chart-range__reset" title="Reset ke 12 bulan terakhir">
              <i class="fas fa-undo"></i> Reset
            </a>
          </form>
          <button type="button" class="btn-chart-dl btn-chart-dl--ghost" id="btnDlTrend">
            <i class="fas fa-download"></i> Unduh tren
          </button>
          <button type="button" class="btn-chart-dl btn-chart-dl--ghost" id="btnDlSample">
            <i class="fas fa-download"></i> Unduh komposisi
          </button>
          <button type="button" class="btn-chart-dl btn-chart-dl--primary" id="btnDlAll">
            <i class="fas fa-file-image"></i> Unduh semua (HD)
          </button>
        </div>
      </div>

      @if (Auth::user()->getlevel->level == 'BNDR')
        <div class="row mb-3">
          <div class="col-md-4 mb-3">
            <div class="card dash-mini-stat dash-mini-stat--green h-100">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Pendapatan Klinik</h6>
                    <h4 class="mb-0" style="font-weight: 700; color: #16a34a;">Rp {{ number_format($totalPendapatanKlinik ?? 0, 0, ',', '.') }}</h4>
                    <small class="text-muted">{{ number_format($jumlahNotaKlinik ?? 0) }} nota</small>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: #f0fdf4;">
                    <i class="fas fa-hand-holding-usd" style="font-size: 28px; color: #22c55e;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="card dash-mini-stat dash-mini-stat--teal h-100">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Pendapatan Kesmas</h6>
                    <h4 class="mb-0" style="font-weight: 700; color: #0891b2;">Rp {{ number_format($totalPendapatanKesmas ?? 0, 0, ',', '.') }}</h4>
                    <small class="text-muted">{{ number_format($jumlahNotaKesmas ?? 0) }} nota</small>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: #ecfeff;">
                    <i class="fas fa-money-bill-wave" style="font-size: 28px; color: #06b6d4;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="card dash-mini-stat dash-mini-stat--amber h-100">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Total Pendapatan</h6>
                    <h4 class="mb-0" style="font-weight: 700; color: #d97706;">Rp {{ number_format(($totalPendapatanKlinik ?? 0) + ($totalPendapatanKesmas ?? 0), 0, ',', '.') }}</h4>
                    <small class="text-muted">{{ number_format(($jumlahNotaKlinik ?? 0) + ($jumlahNotaKesmas ?? 0)) }} nota</small>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: #fff7ed;">
                    <i class="fas fa-file-invoice-dollar" style="font-size: 28px; color: #f59e0b;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      <div class="dash-chart-card dash-chart-card--dark" id="cardChartTrend">
        <div class="dash-chart-card__top">
          <div class="dash-chart-card__title">
            <div>
              <i class="fas fa-wave-square"></i> <span id="chartTrendHeading">{{ $activeChart === 'keuangan' ? ($chartKeuanganTrendHeading ?? 'Tren pendapatan (total nota)') : ($activeChart === 'klinik' ? 'Tren pemeriksaan' : 'Tren permohonan') }}</span>
              <small id="chartTrendSub">{{ $trendSub }}</small>
            </div>
          </div>
        </div>
        <div class="dash-chart-card__canvas-wrap">
          <canvas id="chartPendapatan"></canvas>
        </div>
        <div class="dash-chart-meta">
          @if ($activeChart === 'keuangan' && $keuanganIsMoney)
            <span class="dash-chart-pill">Total <strong id="metaTrendTotal">Rp {{ number_format($chartTotalPermohonan, 0, ',', '.') }}</strong></span>
            <span class="dash-chart-pill">Puncak <strong id="metaTrendPeak">Rp {{ number_format($chartPeak, 0, ',', '.') }}</strong></span>
            <span class="dash-chart-pill">Rata-rata <strong id="metaTrendAvg">Rp {{ number_format(round($chartAvg), 0, ',', '.') }}</strong>/bln</span>
          @else
            <span class="dash-chart-pill">Total <strong id="metaTrendTotal">{{ number_format($chartTotalPermohonan) }}</strong></span>
            <span class="dash-chart-pill">Puncak <strong id="metaTrendPeak">{{ number_format($chartPeak) }}</strong></span>
            <span class="dash-chart-pill">Rata-rata <strong id="metaTrendAvg">{{ $chartAvg }}</strong>/bln</span>
          @endif
        </div>
      </div>

      <div class="dash-chart-card dash-chart-card--donut" id="cardChartSample">
        <div class="dash-chart-card__top">
          <div class="dash-chart-card__title">
            <div>
              <i class="fas fa-chart-pie"></i> <span id="chartDonutHeading">{{ $donutTitle }}</span>
              <small id="chartDonutSub">{{ $donutSub }}</small>
            </div>
          </div>
        </div>
        <div class="dash-chart-card__canvas-wrap">
          <canvas id="chartSample"></canvas>
        </div>
        <div class="dash-chart-meta">
          <span class="dash-chart-pill">Jenis <strong id="metaDonutKinds">{{ count($chartSampleLabels) }}</strong></span>
          @if ($activeChart === 'keuangan' && $keuanganIsMoney)
            <span class="dash-chart-pill">Total <strong id="metaDonutTotal">Rp {{ number_format($chartSampleTotal, 0, ',', '.') }}</strong></span>
          @else
            <span class="dash-chart-pill">Total <strong id="metaDonutTotal">{{ number_format($chartSampleTotal) }}</strong></span>
          @endif
        </div>
      </div>

      @php
        $paketLabelsInit = $chartKlinikPaketLabels ?? [];
        $paketValuesInit = $chartKlinikPaketValues ?? [];
        $paketTotalInit = collect($paketValuesInit)->sum();
        $showPaketInit = ($activeChart === 'klinik');
      @endphp
      <div class="dash-chart-card dash-chart-card--paket {{ $showPaketInit ? '' : 'is-hidden' }}" id="cardChartPaket">
        <div class="dash-chart-card__top">
          <div class="dash-chart-card__title">
            <div>
              <i class="fas fa-boxes"></i> <span>Paket sering dipilih</span>
              <small>10 paket pemeriksaan klinik dengan frekuensi tertinggi</small>
            </div>
          </div>
        </div>
        <div class="dash-chart-card__canvas-wrap">
          <canvas id="chartPaketKlinik"></canvas>
        </div>
        <div class="dash-chart-meta">
          <span class="dash-chart-pill">Paket top <strong id="metaPaketKinds">{{ count($paketLabelsInit) }}</strong></span>
          <span class="dash-chart-pill">Total dipilih <strong id="metaPaketTotal">{{ number_format($paketTotalInit) }}</strong></span>
        </div>
      </div>
    </div>

  @if (Auth::user()->getlevel->level == 'BNDR')
    <div class="dash-quick-bar card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-coins mr-2"></i> Pendapatan Klinik</h5>
            <a href="{{ url('elits-pendapatan-klinik') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-money-check-alt mr-2"></i> Pendapatan Non-Klinik</h5>
            <a href="{{ url('elits-pendapatan-nonklinik') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
          <div class="col-md-4 dash-quick-bar__item">
            <h5><i class="fas fa-receipt mr-2"></i> Semua Pemeriksaan</h5>
            <a href="{{ url('elits-permohonan-uji-klinik/verifikasi/lists') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
        </div>
      </div>
    </div>
  @elseif (Auth::user()->laboratorium && Auth::user()->laboratorium->kode_laboratorium == 'KIM')
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
            <a href="{{ route('klinik.analisis-hasil-wilayah') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
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
            <a href="{{ route('klinik.analisis-hasil-wilayah') }}"><button type="button" class="btn btn-sm">Selengkapnya</button></a>
          </div>
        </div>
      </div>
    </div>
  @elseif (
    Auth::user()->getlevel->level != 'SOLK' &&
    Auth::user()->getlevel->level != 'SOLM' &&
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
    (function () {
      var heroVideo = document.getElementById('dashHeroVideo');
      if (!heroVideo) return;

      var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (reducedMotion) return;

      var source = heroVideo.querySelector('source[data-src]');
      if (!source) return;

      source.src = source.getAttribute('data-src');
      source.removeAttribute('data-src');
      heroVideo.preload = 'auto';
      heroVideo.load();

      var playPromise = heroVideo.play();
      if (playPromise && typeof playPromise.catch === 'function') {
        playPromise.catch(function () {});
      }

      if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              var resumed = heroVideo.play();
              if (resumed && typeof resumed.catch === 'function') {
                resumed.catch(function () {});
              }
            } else {
              heroVideo.pause();
            }
          });
        }, { threshold: 0.15 });
        observer.observe(heroVideo);
      }
    })();
  </script>
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
    <script src="{{ asset('assets/admin/cdn-local/js/chart.min.js') }}?v={{ @filemtime(public_path('assets/admin/cdn-local/js/chart.min.js')) ?: 0 }}"></script>
  <script>
    (function () {
      if (!document.getElementById('dashChartsShowcase')) return;

      var datasets = {
        kesmas: {
          months: {!! json_encode($chartKesmasMonths ?? []) !!},
          series: {!! json_encode($chartKesmasSeries ?? []) !!},
          labels: {!! json_encode($chartKesmasLabels ?? []) !!},
          values: {!! json_encode($chartKesmasValues ?? []) !!},
          paketLabels: [],
          paketValues: [],
          showPaket: false,
          money: false,
          trendSub: 'Volume permohonan uji Kesmas per bulan ({{ \Carbon\Carbon::parse($chartFrom)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($chartTo)->format('d/m/Y') }})',
          donutTitle: 'Komposisi sampel',
          donutSub: 'Proporsi sampel berdasarkan jenis'
        },
        klinik: {
          months: {!! json_encode($chartKlinikMonths ?? []) !!},
          series: {!! json_encode($chartKlinikSeries ?? []) !!},
          seriesHaji: {!! json_encode($chartKlinikSeriesHaji ?? []) !!},
          seriesNonHaji: {!! json_encode($chartKlinikSeriesNonHaji ?? []) !!},
          multi: true,
          labels: {!! json_encode($chartKlinikLabels ?? []) !!},
          values: {!! json_encode($chartKlinikValues ?? []) !!},
          paketLabels: {!! json_encode($chartKlinikPaketLabels ?? []) !!},
          paketValues: {!! json_encode($chartKlinikPaketValues ?? []) !!},
          showPaket: true,
          money: false,
          trendSub: 'Jumlah pemeriksaan klinik per bulan ({{ \Carbon\Carbon::parse($chartFrom)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($chartTo)->format('d/m/Y') }})',
          donutTitle: 'Pemeriksaan Haji vs Non-Haji',
          donutSub: 'Proporsi pemeriksaan klinik berdasarkan jenis'
        },
        keuangan: {
          months: {!! json_encode($chartKeuanganMonths ?? []) !!},
          series: {!! json_encode($chartKeuanganSeriesTotal ?? []) !!},
          seriesKesmas: {!! json_encode($chartKeuanganSeriesKesmas ?? []) !!},
          seriesKlinik: {!! json_encode($chartKeuanganSeriesKlinik ?? []) !!},
          seriesPrimaryLabel: {!! json_encode($chartKeuanganSeriesPrimaryLabel ?? 'Kesmas') !!},
          seriesSecondaryLabel: {!! json_encode($chartKeuanganSeriesSecondaryLabel ?? 'Klinik') !!},
          multi: true,
          keuangan: true,
          money: {!! json_encode((bool) ($chartKeuanganIsMoney ?? true)) !!},
          showKesmas: {!! json_encode((bool) ($chartKeuanganShowPrimary ?? ($showChartKesmas ?? true))) !!},
          labels: {!! json_encode($chartKeuanganLabels ?? []) !!},
          values: {!! json_encode($chartKeuanganValues ?? []) !!},
          paketLabels: [],
          paketValues: [],
          showPaket: false,
          trendHeading: {!! json_encode($chartKeuanganTrendHeading ?? 'Tren pendapatan (total nota)') !!},
          trendSub: {!! json_encode($chartKeuanganTrendSub ?? ('Pendapatan sesuai total nota per bulan (' . \Carbon\Carbon::parse($chartFrom)->format('d/m/Y') . ' – ' . \Carbon\Carbon::parse($chartTo)->format('d/m/Y') . ')')) !!},
          donutTitle: {!! json_encode($chartKeuanganDonutTitle ?? 'Komposisi pendapatan') !!},
          donutSub: {!! json_encode($chartKeuanganDonutSub ?? ('Total nota Kesmas vs Klinik · Kesmas ' . number_format($jumlahNotaKesmas ?? 0) . ' nota · Klinik ' . number_format($jumlahNotaKlinik ?? 0) . ' nota')) !!}
        }
      };

      var activeTab = {!! json_encode($activeChart) !!};
      if (!datasets[activeTab]) {
        activeTab = {!! json_encode($showChartKesmas ? 'kesmas' : 'klinik') !!};
        if (!datasets[activeTab]) {
          activeTab = 'klinik';
        }
      }

      var brand = {
        navy: '#0b3a5c',
        navyDeep: '#06283f',
        teal: '#0d8f7f',
        tealBright: '#16a892',
        mint: '#7fd4c8',
        white: '#ffffff'
      };
      var palette = ['#16a892', '#0b3a5c', '#2ad3b6', '#f59e0b', '#38bdf8', '#0a7a6c', '#fb7185', '#a78bfa'];
      var chartTrend = null;
      var chartSample = null;
      var chartPaket = null;

      function fmt(n) { return Number(n || 0).toLocaleString('id-ID'); }
      function fmtRp(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
      }
      function summarize(series) {
        var total = series.reduce(function (a, b) { return a + Number(b || 0); }, 0);
        var peak = series.length ? Math.max.apply(null, series.map(Number)) : 0;
        var avg = series.length ? Math.round((total / series.length) * 10) / 10 : 0;
        return { total: total, peak: peak, avg: avg };
      }
      function gradientStroke(ctx, area) {
        var g = ctx.createLinearGradient(0, area.bottom, 0, area.top);
        g.addColorStop(0, 'rgba(13, 143, 127, 0.05)');
        g.addColorStop(0.45, 'rgba(22, 168, 146, 0.35)');
        g.addColorStop(1, 'rgba(127, 212, 200, 0.55)');
        return g;
      }
      function updateMeta(pack) {
        var seriesForMeta = pack.series || [];
        if (pack.keuangan) {
          seriesForMeta = pack.series || [];
        } else if (pack.multi) {
          seriesForMeta = (pack.months || []).map(function (_, i) {
            return Number((pack.seriesHaji || [])[i] || 0) + Number((pack.seriesNonHaji || [])[i] || 0);
          });
        }
        var s = summarize(seriesForMeta);
        var donutTotal = (pack.values || []).reduce(function (a, b) { return a + Number(b || 0); }, 0);
        var el;
        var asMoney = !!pack.money;
        if ((el = document.getElementById('metaTrendTotal'))) el.textContent = asMoney ? fmtRp(s.total) : fmt(s.total);
        if ((el = document.getElementById('metaTrendPeak'))) el.textContent = asMoney ? fmtRp(s.peak) : fmt(s.peak);
        if ((el = document.getElementById('metaTrendAvg'))) el.textContent = asMoney ? fmtRp(Math.round(s.avg)) : s.avg;
        if ((el = document.getElementById('metaDonutKinds'))) el.textContent = (pack.labels || []).length;
        if ((el = document.getElementById('metaDonutTotal'))) el.textContent = asMoney ? fmtRp(donutTotal) : fmt(donutTotal);
        if ((el = document.getElementById('chartTrendSub'))) el.textContent = pack.trendSub;
        if ((el = document.getElementById('chartDonutHeading'))) el.textContent = pack.donutTitle;
        if ((el = document.getElementById('chartDonutSub'))) el.textContent = pack.donutSub;
        if ((el = document.getElementById('chartTrendHeading'))) {
          el.textContent = pack.keuangan ? (pack.trendHeading || 'Tren pendapatan (total nota)') : (pack.multi ? 'Tren pemeriksaan' : 'Tren permohonan');
        }
      }
      function buildTrendDatasets(pack) {
        if (pack.keuangan) {
          var sets = [];
          if (pack.showKesmas) {
            sets.push({
              label: pack.seriesPrimaryLabel || 'Kesmas',
              data: pack.seriesKesmas || [],
              borderColor: brand.mint,
              borderWidth: 3,
              fill: false,
              tension: 0.42,
              pointRadius: 4,
              pointHoverRadius: 7,
              pointBackgroundColor: brand.white,
              pointBorderColor: brand.mint,
              pointBorderWidth: 3
            });
          }
          sets.push({
            label: pack.seriesSecondaryLabel || 'Klinik',
            data: pack.seriesKlinik || [],
            borderColor: '#f59e0b',
            borderWidth: 3,
            fill: false,
            tension: 0.42,
            pointRadius: 4,
            pointHoverRadius: 7,
            pointBackgroundColor: brand.white,
            pointBorderColor: '#f59e0b',
            pointBorderWidth: 3
          });
          return sets;
        }
        if (pack.multi) {
          return [
            {
              label: 'Non-Haji',
              data: pack.seriesNonHaji || [],
              borderColor: brand.mint,
              borderWidth: 3,
              fill: false,
              tension: 0.42,
              pointRadius: 4,
              pointHoverRadius: 7,
              pointBackgroundColor: brand.white,
              pointBorderColor: brand.mint,
              pointBorderWidth: 3
            },
            {
              label: 'Haji',
              data: pack.seriesHaji || [],
              borderColor: '#f59e0b',
              borderWidth: 3,
              fill: false,
              tension: 0.42,
              pointRadius: 4,
              pointHoverRadius: 7,
              pointBackgroundColor: brand.white,
              pointBorderColor: '#f59e0b',
              pointBorderWidth: 3
            }
          ];
        }
        return [{
          label: 'Permohonan',
          data: pack.series || [],
          borderColor: brand.mint,
          borderWidth: 3,
          fill: true,
          tension: 0.42,
          pointRadius: 5,
          pointHoverRadius: 8,
          pointBackgroundColor: brand.white,
          pointBorderColor: brand.mint,
          pointBorderWidth: 3,
          pointHoverBackgroundColor: brand.tealBright,
          pointHoverBorderColor: brand.white,
          backgroundColor: function (context) {
            var chart = context.chart;
            var area = chart.chartArea;
            if (!area) return 'rgba(22,168,146,0.2)';
            return gradientStroke(chart.ctx, area);
          }
        }];
      }

      function syncTabUi(tab) {
        document.querySelectorAll('.dash-chart-tab').forEach(function (btn) {
          var on = btn.getAttribute('data-chart-tab') === tab;
          btn.classList.toggle('is-active', on);
          btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });
      }

      function applyDataset(tab) {
        if (!datasets[tab]) {
          return;
        }
        var pack = datasets[tab];
        activeTab = tab;
        syncTabUi(tab);
        var tabInput = document.getElementById('chartTabInput');
        if (tabInput) tabInput.value = tab;
        var resetLink = document.querySelector('.dash-chart-range__reset');
        if (resetLink) {
          var base = resetLink.getAttribute('href').split('?')[0];
          resetLink.setAttribute('href', base + '?chart_tab=' + encodeURIComponent(tab));
        }
        updateMeta(pack);
        try {
          if (chartTrend) {
            chartTrend.data.labels = pack.months || [];
            chartTrend.data.datasets = buildTrendDatasets(pack);
            chartTrend.options.plugins.legend.display = !!pack.multi;
            chartTrend.options.plugins.tooltip.callbacks.label = function (ctx) {
              var unit = pack.money ? '' : ' pemeriksaan';
              var val = pack.money ? fmtRp(ctx.parsed.y) : (ctx.parsed.y.toLocaleString('id-ID') + unit);
              return ' ' + (ctx.dataset.label || (pack.money ? 'Pendapatan' : 'Pemeriksaan')) + ': ' + val;
            };
            if (chartTrend.options.scales && chartTrend.options.scales.y && chartTrend.options.scales.y.ticks) {
              chartTrend.options.scales.y.ticks.callback = function (value) {
                return pack.money ? fmtRp(value) : value;
              };
            }
            chartTrend.update();
          }
          if (chartSample && chartSample.data.datasets[0]) {
            chartSample.data.labels = pack.labels || [];
            chartSample.data.datasets[0].data = pack.values || [];
            chartSample.data.datasets[0].backgroundColor = (pack.labels || []).map(function (_, i) {
              return palette[i % palette.length];
            });
            chartSample.options.plugins.tooltip.callbacks = chartSample.options.plugins.tooltip.callbacks || {};
            chartSample.options.plugins.tooltip.callbacks.label = function (ctx) {
              var v = Number(ctx.parsed || ctx.raw || 0);
              return ' ' + ctx.label + ': ' + (pack.money ? fmtRp(v) : v.toLocaleString('id-ID'));
            };
            chartSample._isMoney = !!pack.money;
            chartSample.update();
          }
          var paketCard = document.getElementById('cardChartPaket');
          if (paketCard) {
            if (pack.showPaket) paketCard.classList.remove('is-hidden');
            else paketCard.classList.add('is-hidden');
          }
          if (chartPaket) {
            var pLabels = (pack.paketLabels || []).slice().reverse();
            var pValues = (pack.paketValues || []).slice().reverse();
            chartPaket.data.labels = pLabels;
            chartPaket.data.datasets[0].data = pValues;
            chartPaket.data.datasets[0].backgroundColor = pLabels.map(function (_, i) {
              return palette[i % palette.length];
            });
            chartPaket.update();
            var el;
            if ((el = document.getElementById('metaPaketKinds'))) el.textContent = (pack.paketLabels || []).length;
            if ((el = document.getElementById('metaPaketTotal'))) {
              el.textContent = (pack.paketValues || []).reduce(function (a, b) { return a + Number(b || 0); }, 0).toLocaleString('id-ID');
            }
          }
        } catch (err) {
          console.error('Gagal memperbarui grafik tab:', tab, err);
        }
      }

      var tabList = document.querySelector('.dash-chart-tabs');
      if (tabList) {
        tabList.addEventListener('click', function (e) {
          var btn = e.target.closest('.dash-chart-tab');
          if (!btn) return;
          e.preventDefault();
          applyDataset(btn.getAttribute('data-chart-tab'));
        });
      }
      function downloadCanvas(sourceCanvas, filename, dark) {
        var padX = 48, padTop = 92, padBottom = 56;
        var w = Math.max(sourceCanvas.width, 1100);
        var scale = w / sourceCanvas.width;
        var h = Math.round(sourceCanvas.height * scale) + padTop + padBottom;
        var out = document.createElement('canvas');
        out.width = w; out.height = h;
        var c = out.getContext('2d');
        if (dark) {
          var bg = c.createLinearGradient(0, 0, w, h);
          bg.addColorStop(0, '#06283f'); bg.addColorStop(0.55, '#0b3a5c'); bg.addColorStop(1, '#0a5a58');
          c.fillStyle = bg;
        } else {
          c.fillStyle = '#f5f8f7'; c.fillRect(0, 0, w, h); c.fillStyle = '#ffffff';
        }
        c.fillRect(0, 0, w, h);
        c.fillStyle = dark ? '#ffffff' : brand.navyDeep;
        c.font = '700 28px Manrope, Segoe UI, sans-serif';
        c.fillText('SIMLAB', padX, 42);
        c.fillStyle = dark ? brand.mint : brand.teal;
        c.font = '600 14px Manrope, Segoe UI, sans-serif';
        c.fillText('Lingkungan pengujian · Analitik ' + (activeTab === 'keuangan' ? 'Keuangan' : (activeTab === 'klinik' ? 'Klinik' : 'Kesmas')), padX, 66);
        c.drawImage(sourceCanvas, padX, padTop, w - padX * 2, Math.round(sourceCanvas.height * scale));
        c.fillStyle = dark ? 'rgba(255,255,255,0.55)' : '#5c6d75';
        c.font = '500 12px Manrope, Segoe UI, sans-serif';
        c.fillText('Diekspor ' + new Date().toLocaleString('id-ID') + '  ·  SIMLAB Testing', padX, h - 22);
        var link = document.createElement('a');
        link.download = filename;
        link.href = out.toDataURL('image/png', 1);
        link.click();
      }
      function composeReport() {
        var c1 = chartTrend ? chartTrend.canvas : null;
        var c2 = chartSample ? chartSample.canvas : null;
        if (!c1 && !c2) return;
        var padX = 40, padTop = 110, padBottom = 48, gap = 28, panelW = 720;
        var leftH = c1 ? Math.round(c1.height * (panelW / c1.width)) : 0;
        var rightW = 520;
        var rightH = c2 ? Math.round(c2.height * (rightW / c2.width)) : 0;
        var contentH = Math.max(leftH, rightH);
        var out = document.createElement('canvas');
        out.width = padX * 2 + panelW + gap + rightW;
        out.height = padTop + contentH + padBottom + 40;
        var c = out.getContext('2d');
        var bg = c.createLinearGradient(0, 0, out.width, out.height);
        bg.addColorStop(0, '#f5f8f7'); bg.addColorStop(1, '#e7f4f2');
        c.fillStyle = bg; c.fillRect(0, 0, out.width, out.height);
        var header = c.createLinearGradient(0, 0, out.width, 0);
        header.addColorStop(0, '#06283f'); header.addColorStop(1, '#0d8f7f');
        c.fillStyle = header; c.fillRect(0, 0, out.width, 84);
        c.fillStyle = '#ffffff';
        c.font = '800 30px Manrope, Segoe UI, sans-serif';
        c.fillText('SIMLAB', padX, 40);
        c.font = '600 14px Manrope, Segoe UI, sans-serif';
        c.fillStyle = 'rgba(255,255,255,0.85)';
        c.fillText('Laporan analitik ' + (activeTab === 'keuangan' ? 'Keuangan' : (activeTab === 'klinik' ? 'Klinik' : 'Kesmas')) + ' · Lingkungan pengujian', padX, 64);
        function roundRect(ctx, x, y, w, h, r, fill) {
          ctx.beginPath();
          ctx.moveTo(x + r, y);
          ctx.arcTo(x + w, y, x + w, y + h, r);
          ctx.arcTo(x + w, y + h, x, y + h, r);
          ctx.arcTo(x, y + h, x, y, r);
          ctx.arcTo(x, y, x + w, y, r);
          ctx.closePath();
          ctx.fillStyle = fill; ctx.fill();
        }
        if (c1) { roundRect(c, padX, padTop, panelW, leftH + 24, 16, '#0b3a5c'); c.drawImage(c1, padX + 12, padTop + 12, panelW - 24, leftH); }
        if (c2) { roundRect(c, padX + panelW + gap, padTop, rightW, rightH + 24, 16, '#ffffff'); c.drawImage(c2, padX + panelW + gap + 12, padTop + 12, rightW - 24, rightH); }
        c.fillStyle = '#5c6d75';
        c.font = '500 12px Manrope, Segoe UI, sans-serif';
        c.fillText('Diekspor ' + new Date().toLocaleString('id-ID') + '  ·  SIMLAB Testing', padX, out.height - 24);
        var link = document.createElement('a');
        link.download = 'simlab-analitik-' + activeTab + '-' + Date.now() + '.png';
        link.href = out.toDataURL('image/png', 1);
        link.click();
      }

      var initPack = datasets[activeTab] || datasets.klinik;
      if (typeof Chart === 'undefined') {
        console.error('Chart.js tidak termuat — tab grafik tidak dapat diperbarui.');
        syncTabUi(activeTab);
        return;
      }
      var elTrend = document.getElementById('chartPendapatan');
      if (elTrend) {
        chartTrend = new Chart(elTrend.getContext('2d'), {
          type: 'line',
          data: {
            labels: initPack.months || [],
            datasets: buildTrendDatasets(initPack)
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 900, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
              legend: {
                display: !!initPack.multi,
                labels: { color: 'rgba(255,255,255,0.9)', font: { weight: '700', size: 11 }, boxWidth: 12, padding: 14 }
              },
              tooltip: {
                backgroundColor: 'rgba(6, 40, 63, 0.94)',
                titleFont: { weight: '700', size: 13 },
                bodyFont: { weight: '600', size: 13 },
                padding: 12,
                cornerRadius: 8,
                displayColors: true,
                callbacks: {
                  label: function (ctx) {
                    var pack = datasets[activeTab] || {};
                    if (pack.money) {
                      return ' ' + (ctx.dataset.label || 'Pendapatan') + ': ' + fmtRp(ctx.parsed.y);
                    }
                    return ' ' + (ctx.dataset.label || 'Pemeriksaan') + ': ' + ctx.parsed.y.toLocaleString('id-ID') + ' pemeriksaan';
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.08)', drawBorder: false },
                ticks: {
                  color: 'rgba(255,255,255,0.75)',
                  font: { size: 11, weight: '600' },
                  callback: function (value) {
                    var pack = datasets[activeTab] || {};
                    return pack.money ? fmtRp(value) : value;
                  }
                }
              },
              x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.8)', font: { size: 11, weight: '600' } } }
            }
          }
        });
      }

      var elSample = document.getElementById('chartSample');
      if (elSample) {
        chartSample = new Chart(elSample.getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: initPack.labels || [],
            datasets: [{
              data: initPack.values || [],
              backgroundColor: (initPack.labels || []).map(function (_, i) { return palette[i % palette.length]; }),
              borderWidth: 0,
              hoverOffset: 8
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
              legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { weight: '600', size: 11 } } },
              tooltip: { backgroundColor: 'rgba(6, 40, 63, 0.94)', padding: 12, cornerRadius: 8 }
            }
          },
          plugins: [{
            id: 'centerText',
            afterDraw: function (chart) {
              var width = chart.width, height = chart.height, c = chart.ctx;
              var meta = chart.getDatasetMeta(0);
              if (!meta || !meta.data || !meta.data.length) return;
              var total = chart.data.datasets[0].data.reduce(function (a, b) { return a + Number(b || 0); }, 0);
              c.save();
              c.textAlign = 'center'; c.textBaseline = 'middle';
              c.fillStyle = '#0b3a5c';
              c.font = '800 18px Manrope, Segoe UI, sans-serif';
              var centerVal = chart._isMoney ? ('Rp ' + total.toLocaleString('id-ID', { maximumFractionDigits: 0 })) : total.toLocaleString('id-ID');
              c.fillText(centerVal, width / 2, height / 2 - 6);
              c.fillStyle = '#5c6d75';
              c.font = '600 11px Manrope, Segoe UI, sans-serif';
              c.fillText('total', width / 2, height / 2 + 14);
              c.restore();
            }
          }]
        });
      }


      var elPaket = document.getElementById('chartPaketKlinik');
      if (elPaket) {
        var initPaketLabels = ((initPack.paketLabels || []).slice()).reverse();
        var initPaketValues = ((initPack.paketValues || []).slice()).reverse();
        chartPaket = new Chart(elPaket.getContext('2d'), {
          type: 'bar',
          data: {
            labels: initPaketLabels,
            datasets: [{
              label: 'Dipilih',
              data: initPaketValues,
              backgroundColor: initPaketLabels.map(function (_, i) { return palette[i % palette.length]; }),
              borderRadius: 8,
              borderSkipped: false,
              maxBarThickness: 28
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 900, easing: 'easeOutQuart' },
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: 'rgba(6, 40, 63, 0.94)',
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                  label: function (ctx) {
                    return ' ' + ctx.parsed.x.toLocaleString('id-ID') + ' kali dipilih';
                  }
                }
              }
            },
            scales: {
              x: {
                beginAtZero: true,
                grid: { color: 'rgba(11, 58, 92, 0.08)', drawBorder: false },
                ticks: { color: '#5c6d75', font: { size: 11, weight: '600' } }
              },
              y: {
                grid: { display: false },
                ticks: { color: '#0b3a5c', font: { size: 11, weight: '700' } }
              }
            }
          }
        });
      }

      updateMeta(initPack);
      if (chartSample) {
        chartSample._isMoney = !!(initPack && initPack.money);
      }
      var paketCardInit = document.getElementById('cardChartPaket');
      if (paketCardInit) {
        if (initPack && initPack.showPaket) paketCardInit.classList.remove('is-hidden');
        else paketCardInit.classList.add('is-hidden');
      }
      syncTabUi(activeTab);
      var formRange = document.getElementById('formChartRange');
      if (formRange) {
        formRange.addEventListener('submit', function (e) {
          var fromEl = document.getElementById('chartFrom');
          var toEl = document.getElementById('chartTo');
          if (fromEl && toEl && fromEl.value && toEl.value && fromEl.value > toEl.value) {
            e.preventDefault();
            alert('Tanggal "Dari" tidak boleh lebih besar dari tanggal "Sampai".');
          }
        });
      }
      var btnTrend = document.getElementById('btnDlTrend');
      var btnSample = document.getElementById('btnDlSample');
      var btnAll = document.getElementById('btnDlAll');
      if (btnTrend) btnTrend.addEventListener('click', function () {
        if (chartTrend) downloadCanvas(chartTrend.canvas, 'simlab-tren-' + activeTab + '.png', true);
      });
      if (btnSample) btnSample.addEventListener('click', function () {
        if (chartSample) downloadCanvas(chartSample.canvas, 'simlab-komposisi-' + activeTab + '.png', false);
      });
      if (btnAll) btnAll.addEventListener('click', composeReport);
    })();
  </script>

@endsection
