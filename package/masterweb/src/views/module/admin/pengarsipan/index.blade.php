@extends('masterweb::template.admin.layout')

@section('title')
    Pengarsipan Hasil
@endsection

@section('css')
<style>
    .arsip-page { --arsip-navy: #06283f; --arsip-teal: #0d8f7f; --arsip-muted: #5c6d75; }

    .arsip-hero {
        background: linear-gradient(135deg, #06283f 0%, #0b3a5c 50%, #0d8f7f 100%);
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        color: #fff;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 24px rgba(6, 40, 63, 0.18);
    }
    .arsip-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.14);
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.65rem;
    }
    .arsip-hero__title { font-size: 1.45rem; font-weight: 700; margin: 0 0 0.35rem; }
    .arsip-hero__sub { font-size: 13px; opacity: 0.9; margin: 0; max-width: 680px; line-height: 1.55; }

    .arsip-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 991px) { .arsip-stat-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .arsip-stat-grid { grid-template-columns: 1fr; } }

    .arsip-stat {
        background: #fff;
        border: 1px solid #e8eaed;
        border-radius: 10px;
        padding: 1rem 1.1rem;
        box-shadow: 0 1px 3px rgba(6,40,63,0.06);
    }
    .arsip-stat__icon {
        width: 36px; height: 36px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 16px; margin-bottom: 0.5rem;
    }
    .arsip-stat__icon--teal { background: #e7f4f2; color: #0d8f7f; }
    .arsip-stat__icon--blue { background: #e8f0fe; color: #1967d2; }
    .arsip-stat__icon--amber { background: #fef7e0; color: #e37400; }
    .arsip-stat__icon--purple { background: #f3e8fd; color: #8430ce; }
    .arsip-stat__val { font-size: 1.5rem; font-weight: 700; color: #202124; line-height: 1.2; }
    .arsip-stat__lbl { font-size: 12px; color: var(--arsip-muted); margin-top: 2px; }

    .arsip-panel {
        background: #fff;
        border: 1px solid #e8eaed;
        border-radius: 10px;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(6,40,63,0.06);
    }
    .arsip-panel__title {
        font-size: 14px;
        font-weight: 700;
        color: #202124;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .arsip-panel__title i { color: var(--arsip-teal); }

    .arsip-search {
        background: #f8f9fa;
        border: 1px solid #e8eaed;
        border-radius: 10px;
        padding: 1rem 1.1rem;
        margin-bottom: 1.25rem;
    }
    .arsip-search label { font-size: 12px; font-weight: 600; color: #3c4043; }

    .arsip-quick-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    @media (max-width: 767px) { .arsip-quick-grid { grid-template-columns: 1fr; } }

    .arsip-quick {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 0.85rem 1rem;
        border: 1px solid #e8eaed;
        border-radius: 10px;
        text-decoration: none !important;
        color: inherit !important;
        transition: border-color 0.15s, box-shadow 0.15s;
        background: #fff;
    }
    .arsip-quick:hover {
        border-color: #0d8f7f;
        box-shadow: 0 4px 12px rgba(13, 143, 127, 0.12);
    }
    .arsip-quick__icon {
        width: 40px; height: 40px; border-radius: 8px;
        background: #e7f4f2; color: #0d8f7f;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 18px;
    }
    .arsip-quick__title { font-size: 13px; font-weight: 700; color: #202124; margin-bottom: 2px; }
    .arsip-quick__desc { font-size: 11px; color: var(--arsip-muted); line-height: 1.45; margin: 0; }

    .arsip-table { font-size: 13px; margin-bottom: 0; }
    .arsip-table thead th {
        font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;
        color: #5f6368; border-top: none; white-space: nowrap;
    }
    .arsip-badge {
        display: inline-block; padding: 3px 8px; border-radius: 4px;
        font-size: 10px; font-weight: 700;
    }
    .arsip-badge--ok { background: #e6f4ea; color: #188038; }
    .arsip-badge--wait { background: #fef7e0; color: #e37400; }
    .arsip-badge--bidang { background: #e8f0fe; color: #174ea6; }

    .btn-arsip-print {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; font-size: 11px; font-weight: 600;
        border-radius: 4px; border: 1.5px solid #1967d2;
        color: #1967d2 !important; background: #fff !important;
        text-decoration: none !important; white-space: nowrap;
    }
    .btn-arsip-print:hover { background: #1967d2 !important; color: #fff !important; }
    .btn-arsip-print.disabled { opacity: 0.45; pointer-events: none; border-color: #dadce0; color: #9aa0a6 !important; }
</style>
@endsection

@section('content')
<div class="arsip-page">
    <div class="arsip-hero">
        <div class="arsip-hero__badge"><i class="ti-archive"></i> Akun Pengarsip Hasil</div>
        <h1 class="arsip-hero__title">Pusat Pengarsipan & Cetak Hasil</h1>
        <p class="arsip-hero__sub">
            Cetak dan arsipkan hasil pemeriksaan klinik & kesmas yang sudah divalidasi,
            kelola buku register, dan pantau riwayat cetak laboratorium.
        </p>
    </div>

    <div class="arsip-stat-grid">
        <div class="arsip-stat">
            <div class="arsip-stat__icon arsip-stat__icon--teal"><i class="fas fa-vial"></i></div>
            <div class="arsip-stat__val">{{ number_format($stats['klinik_siap_cetak']) }}</div>
            <div class="arsip-stat__lbl">Klinik divalidasi (30 hari)</div>
        </div>
        <div class="arsip-stat">
            <div class="arsip-stat__icon arsip-stat__icon--blue"><i class="fas fa-flask"></i></div>
            <div class="arsip-stat__val">{{ number_format($stats['kesmas_siap_cetak']) }}</div>
            <div class="arsip-stat__lbl">Kesmas divalidasi (30 hari)</div>
        </div>
        <div class="arsip-stat">
            <div class="arsip-stat__icon arsip-stat__icon--amber"><i class="fas fa-print"></i></div>
            <div class="arsip-stat__val">{{ number_format($stats['cetak_hari_ini']) }}</div>
            <div class="arsip-stat__lbl">Cetak hari ini</div>
        </div>
        <div class="arsip-stat">
            <div class="arsip-stat__icon arsip-stat__icon--purple"><i class="fas fa-calendar-alt"></i></div>
            <div class="arsip-stat__val">{{ number_format($stats['cetak_bulan_ini']) }}</div>
            <div class="arsip-stat__lbl">Cetak bulan ini</div>
        </div>
    </div>

    <div class="arsip-search">
        <form method="GET" action="{{ url('/pengarsipan') }}">
            <label for="arsip_q">Cari hasil untuk dicetak / diarsipkan</label>
            <div class="input-group mt-1">
                <input type="text" id="arsip_q" name="q" class="form-control form-control-sm"
                    value="{{ $q }}" placeholder="Nama pasien, no. lab, no. register, kode sampel kesmas...">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search mr-1"></i> Cari</button>
                    @if($q !== '')
                        <a href="{{ url('/pengarsipan') }}" class="btn btn-light btn-sm">Reset</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if($q !== '')
        <div class="arsip-panel">
            <div class="arsip-panel__title"><i class="fas fa-search"></i> Hasil pencarian: &ldquo;{{ $q }}&rdquo;</div>
            @if($searchResults->isEmpty())
                <p class="text-muted mb-0">Tidak ditemukan data klinik atau kesmas untuk kata kunci ini.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover arsip-table">
                        <thead>
                            <tr>
                                <th>Bidang</th>
                                <th>Identitas</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($searchResults as $row)
                                <tr>
                                    <td><span class="arsip-badge arsip-badge--bidang">{{ $row['bidang'] }}</span></td>
                                    <td>
                                        <strong>{{ $row['label'] }}</strong><br>
                                        <small class="text-muted">{{ $row['sub'] }}</small>
                                    </td>
                                    <td>
                                        <span class="arsip-badge {{ $row['status_ok'] ? 'arsip-badge--ok' : 'arsip-badge--wait' }}">
                                            {{ $row['status'] }}
                                        </span>
                                    </td>
                                    <td nowrap>
                                        @if(!empty($row['nomer_lab_url']))
                                            <a href="{{ $row['nomer_lab_url'] }}" class="btn-arsip-print ml-1">
                                                <i class="fas fa-hashtag"></i> Input No. Lab
                                            </a>
                                        @endif
                                        @if(!empty($row['print_url']))
                                            <a href="{{ $row['print_url'] }}" target="_blank" class="btn-arsip-print">
                                                <i class="fas fa-print"></i> Cetak Hasil
                                            </a>
                                        @else
                                            <span class="btn-arsip-print disabled"><i class="fas fa-print"></i> Belum valid</span>
                                        @endif
                                        @if(!empty($row['nota_url']))
                                            <a href="{{ $row['nota_url'] }}" target="_blank" class="btn-arsip-print ml-1">
                                                <i class="fas fa-file-invoice"></i> Nota
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <div class="arsip-panel" id="kesmas-nomer-lab">
        <div class="arsip-panel__title"><i class="fas fa-hashtag"></i> Kesmas — Input Nomor Lab</div>
        <p class="text-muted small mb-3">Input nomor lab adalah bagian dari pengarsipan. Isi nomor lab untuk kombinasi jenis sampel × laboratorium yang belum lengkap.</p>
        @if($pendingNomerLab->isEmpty())
            <p class="text-muted mb-0">Tidak ada permohonan kesmas yang menunggu input nomor lab pada daftar terbaru.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover arsip-table">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Kode Permohonan</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingNomerLab as $row)
                            <tr>
                                <td><strong>{{ $row['pelanggan'] }}</strong></td>
                                <td nowrap>{{ $row['kode'] }}</td>
                                <td>
                                    <span class="arsip-badge arsip-badge--wait">{{ $row['status'] }}</span>
                                </td>
                                <td nowrap>
                                    <a href="{{ $row['nomer_lab_url'] }}" class="btn-arsip-print">
                                        <i class="fas fa-hashtag"></i> Input Nomor Lab
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="arsip-panel">
        <div class="arsip-panel__title"><i class="fas fa-th-large"></i> Akses Cepat</div>
        <div class="arsip-quick-grid">
            <a href="#kesmas-nomer-lab" class="arsip-quick">
                <div class="arsip-quick__icon"><i class="fas fa-hashtag"></i></div>
                <div>
                    <div class="arsip-quick__title">Input Nomor Lab Kesmas</div>
                    <p class="arsip-quick__desc">Isi nomor lab permohonan yang belum lengkap.</p>
                </div>
            </a>
            <a href="{{ route('pengarsipan-dokumen.index') }}" class="arsip-quick">
                <div class="arsip-quick__icon"><i class="fas fa-folder-plus"></i></div>
                <div>
                    <div class="arsip-quick__title">Dokumen Arsip Tambahan</div>
                    <p class="arsip-quick__desc">Unggah, penomoran, dan cari dokumen pendukung arsip.</p>
                </div>
            </a>
            <a href="#kesmas-validated" class="arsip-quick">
                <div class="arsip-quick__icon"><i class="fas fa-file-medical"></i></div>
                <div>
                    <div class="arsip-quick__title">Cetak LHU Kesmas</div>
                    <p class="arsip-quick__desc">Lihat sampel kesmas yang sudah divalidasi dan cetak LHU.</p>
                </div>
            </a>
            <a href="{{ url('/register-result-clinic') }}" class="arsip-quick">
                <div class="arsip-quick__icon"><i class="fas fa-book"></i></div>
                <div>
                    <div class="arsip-quick__title">Buku Register Hasil Klinis</div>
                    <p class="arsip-quick__desc">Register bulanan hasil pemeriksaan klinik untuk arsip.</p>
                </div>
            </a>
            <a href="{{ url('/monitoring-sampling-penerima') }}" class="arsip-quick">
                <div class="arsip-quick__icon"><i class="fas fa-truck-loading"></i></div>
                <div>
                    <div class="arsip-quick__title">Monitoring Sampling</div>
                    <p class="arsip-quick__desc">Buku monitoring pengambilan & penerimaan sampel.</p>
                </div>
            </a>
            <a href="{{ url('/elits-samples/all') }}" class="arsip-quick">
                <div class="arsip-quick__icon"><i class="fas fa-database"></i></div>
                <div>
                    <div class="arsip-quick__title">Data Semua Sampel</div>
                    <p class="arsip-quick__desc">Cari sampel kesmas berdasarkan kode dan pelanggan.</p>
                </div>
            </a>
            <a href="{{ url('/report-jumlah-jenis-sampel') }}" class="arsip-quick">
                <div class="arsip-quick__icon"><i class="fas fa-chart-bar"></i></div>
                <div>
                    <div class="arsip-quick__title">Laporan Jenis Sampel</div>
                    <p class="arsip-quick__desc">Rekapitulasi jumlah per jenis sampel (klinik/kesmas).</p>
                </div>
            </a>
            <a href="{{ url('/activity-log') }}" class="arsip-quick">
                <div class="arsip-quick__icon"><i class="fas fa-history"></i></div>
                <div>
                    <div class="arsip-quick__title">Log Aktivitas Cetak</div>
                    <p class="arsip-quick__desc">Riwayat cetak & ekspor dokumen laboratorium.</p>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="arsip-panel">
                <div class="arsip-panel__title"><i class="fas fa-check-circle"></i> Klinik — Baru Divalidasi</div>
                @if($recentValidated->isEmpty())
                    <p class="text-muted mb-0">Belum ada hasil klinik divalidasi dalam 30 hari terakhir.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover arsip-table">
                            <thead>
                                <tr>
                                    <th>Pasien</th>
                                    <th>No. Lab</th>
                                    <th>Validasi</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentValidated as $row)
                                    <tr>
                                        <td>
                                            <strong>{{ $row['pasien'] }}</strong><br>
                                            <small class="text-muted">{{ $row['noregister'] }}</small>
                                        </td>
                                        <td nowrap>{{ $row['nomor_lab'] }}</td>
                                        <td nowrap>
                                            {{ $row['validated_at'] }}<br>
                                            <small class="text-muted">{{ $row['validator'] }}</small>
                                        </td>
                                        <td nowrap>
                                            <a href="{{ $row['print_url'] }}" target="_blank" class="btn-arsip-print">
                                                <i class="fas fa-print"></i> Cetak
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="arsip-panel" id="kesmas-validated">
                <div class="arsip-panel__title"><i class="fas fa-flask"></i> Kesmas — Baru Divalidasi</div>
                @if($recentValidatedKesmas->isEmpty())
                    <p class="text-muted mb-0">Belum ada hasil kesmas divalidasi.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover arsip-table">
                            <thead>
                                <tr>
                                    <th>Pelanggan</th>
                                    <th>Kode Sampel</th>
                                    <th>Validasi</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentValidatedKesmas as $row)
                                    <tr>
                                        <td><strong>{{ $row['pelanggan'] }}</strong></td>
                                        <td nowrap>{{ $row['kode'] }}</td>
                                        <td nowrap>
                                            {{ $row['validated_at'] }}<br>
                                            <small class="text-muted">{{ $row['validator'] }}</small>
                                        </td>
                                        <td nowrap>
                                            @if(!empty($row['print_url']))
                                                <a href="{{ $row['print_url'] }}" target="_blank" class="btn-arsip-print">
                                                    <i class="fas fa-print"></i> Cetak LHU
                                                </a>
                                            @else
                                                <span class="btn-arsip-print disabled" title="Laboratorium belum teridentifikasi">
                                                    <i class="fas fa-print"></i> LHU
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="arsip-panel">
                <div class="arsip-panel__title"><i class="fas fa-history"></i> Riwayat Cetak Terakhir</div>
                @if($recentPrints->isEmpty())
                    <p class="text-muted mb-0">Belum ada log cetak tercatat.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm arsip-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPrints as $log)
                                    <tr>
                                        <td nowrap>{{ $log['waktu'] }}</td>
                                        <td>{{ $log['pengguna'] }}</td>
                                        <td style="white-space:normal;">
                                            <span class="arsip-badge arsip-badge--bidang">{{ $log['bidang'] }}</span>
                                            {{ \Illuminate\Support\Str::limit($log['deskripsi'], 60) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
