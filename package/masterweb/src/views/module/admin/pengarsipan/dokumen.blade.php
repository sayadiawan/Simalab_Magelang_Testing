@extends('masterweb::template.admin.layout')

@section('title')
    Dokumen Arsip Tambahan
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
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.14); border-radius: 999px;
        padding: 4px 12px; font-size: 11px; font-weight: 700;
        letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 0.65rem;
    }
    .arsip-hero__title { font-size: 1.45rem; font-weight: 700; margin: 0 0 0.35rem; }
    .arsip-hero__sub { font-size: 13px; opacity: 0.9; margin: 0; max-width: 680px; line-height: 1.55; }

    .arsip-stat-grid {
        display: grid; grid-template-columns: repeat(2, 1fr);
        gap: 12px; margin-bottom: 1.25rem; max-width: 480px;
    }
    @media (max-width: 575px) { .arsip-stat-grid { grid-template-columns: 1fr; } }

    .arsip-stat {
        background: #fff; border: 1px solid #e8eaed; border-radius: 10px;
        padding: 1rem 1.1rem; box-shadow: 0 1px 3px rgba(6,40,63,0.06);
    }
    .arsip-stat__icon {
        width: 36px; height: 36px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 16px; margin-bottom: 0.5rem;
    }
    .arsip-stat__icon--green { background: #e6f4ea; color: #188038; }
    .arsip-stat__icon--teal { background: #e7f4f2; color: #0d8f7f; }
    .arsip-stat__val { font-size: 1.5rem; font-weight: 700; color: #202124; line-height: 1.2; }
    .arsip-stat__lbl { font-size: 12px; color: var(--arsip-muted); margin-top: 2px; }

    .arsip-panel {
        background: #fff; border: 1px solid #e8eaed; border-radius: 10px;
        padding: 1.1rem 1.25rem; margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(6,40,63,0.06);
    }
    .arsip-panel__title {
        font-size: 14px; font-weight: 700; color: #202124;
        margin-bottom: 0.85rem; display: flex; align-items: center; gap: 8px;
    }
    .arsip-panel__title i { color: var(--arsip-teal); }

    .arsip-search {
        background: #f8f9fa; border: 1px solid #e8eaed;
        border-radius: 10px; padding: 1rem 1.1rem; margin-bottom: 1.25rem;
    }
    .arsip-search label { font-size: 12px; font-weight: 600; color: #3c4043; }

    .arsip-table { font-size: 13px; margin-bottom: 0; }
    .arsip-table thead th {
        font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;
        color: #5f6368; border-top: none; white-space: nowrap;
    }
    .arsip-badge {
        display: inline-block; padding: 3px 8px; border-radius: 4px;
        font-size: 10px; font-weight: 700;
    }
    .arsip-badge--bidang { background: #e8f0fe; color: #174ea6; }

    .arsip-doc-form {
        background: #f8fbfa; border: 1px dashed #b8dfd8;
        border-radius: 10px; padding: 1rem 1.1rem; margin-bottom: 1rem;
    }
    .arsip-doc-form label { font-size: 12px; font-weight: 600; color: #3c4043; margin-bottom: 2px; }
    .arsip-doc-nomor-hint { font-size: 11px; color: #0d8f7f; font-weight: 600; }

    .btn-arsip-doc {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; font-size: 11px; font-weight: 600;
        border-radius: 4px; border: 1.5px solid #0d8f7f;
        color: #0d8f7f !important; background: #fff !important;
        text-decoration: none !important; white-space: nowrap; cursor: pointer;
    }
    .btn-arsip-doc:hover { background: #0d8f7f !important; color: #fff !important; }
    .btn-arsip-doc--danger { border-color: #d93025; color: #d93025 !important; }
    .btn-arsip-doc--danger:hover { background: #d93025 !important; color: #fff !important; }
    .arsip-doc-edit { display: none; margin-top: 6px; padding: 8px; background: #f1f3f4; border-radius: 6px; }
    .arsip-doc-edit.is-open { display: block; }

    .arsip-back-link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; color: #0d8f7f !important;
        text-decoration: none !important; margin-bottom: 1rem;
    }
    .arsip-back-link:hover { text-decoration: underline !important; }
</style>
@endsection

@section('content')
<div class="arsip-page">
    <a href="{{ url('/pengarsipan') }}" class="arsip-back-link">
        <i class="fas fa-arrow-left"></i> Kembali ke Pengarsipan Hasil
    </a>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="arsip-hero">
        <div class="arsip-hero__badge"><i class="ti-folder"></i> Menu Pengarsipan</div>
        <h1 class="arsip-hero__title">Dokumen Arsip Tambahan</h1>
        <p class="arsip-hero__sub">
            Unggah dokumen pendukung arsip, atur penomoran arsip, dan cari dokumen berdasarkan nomor, judul, atau referensi.
        </p>
    </div>

    <div class="arsip-stat-grid">
        <div class="arsip-stat">
            <div class="arsip-stat__icon arsip-stat__icon--green"><i class="fas fa-folder-open"></i></div>
            <div class="arsip-stat__val">{{ number_format($stats['total']) }}</div>
            <div class="arsip-stat__lbl">Total dokumen arsip</div>
        </div>
        <div class="arsip-stat">
            <div class="arsip-stat__icon arsip-stat__icon--teal"><i class="fas fa-calendar-check"></i></div>
            <div class="arsip-stat__val">{{ number_format($stats['tahun_ini']) }}</div>
            <div class="arsip-stat__lbl">Dokumen tahun {{ date('Y') }}</div>
        </div>
    </div>

    <div class="arsip-search">
        <form method="GET" action="{{ route('pengarsipan-dokumen.index') }}">
            <label for="doc_q">Cari dokumen arsip</label>
            <div class="row mt-1">
                <div class="col-md-5">
                    <input type="text" id="doc_q" name="q" class="form-control form-control-sm"
                        value="{{ $q }}" placeholder="No. arsip, judul, keterangan, nama file, referensi...">
                </div>
                <div class="col-md-3">
                    <select name="bidang" class="form-control form-control-sm">
                        <option value="">Semua bidang</option>
                        <option value="klinik" {{ $bidang === 'klinik' ? 'selected' : '' }}>Klinik</option>
                        <option value="kesmas" {{ $bidang === 'kesmas' ? 'selected' : '' }}>Kesmas</option>
                        <option value="umum" {{ $bidang === 'umum' ? 'selected' : '' }}>Umum</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search mr-1"></i> Cari</button>
                    @if($q !== '' || $bidang !== '')
                        <a href="{{ route('pengarsipan-dokumen.index') }}" class="btn btn-light btn-sm">Reset</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="arsip-panel">
        @if(getAction('create'))
            <div class="arsip-doc-form">
                <div class="mb-2"><strong style="font-size:13px;"><i class="fas fa-upload mr-1"></i> Unggah Dokumen Baru</strong></div>
                <form method="POST" action="{{ route('pengarsipan-dokumen.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 form-group mb-2">
                            <label for="doc_judul">Judul Dokumen *</label>
                            <input type="text" id="doc_judul" name="judul" class="form-control form-control-sm"
                                value="{{ old('judul') }}" required maxlength="255"
                                placeholder="Contoh: Surat permohonan, lampiran hasil...">
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label for="doc_bidang_upload">Bidang *</label>
                            <select id="doc_bidang_upload" name="bidang" class="form-control form-control-sm" required>
                                <option value="klinik" {{ old('bidang') === 'klinik' ? 'selected' : '' }}>Klinik</option>
                                <option value="kesmas" {{ old('bidang', 'kesmas') === 'kesmas' ? 'selected' : '' }}>Kesmas</option>
                                <option value="umum" {{ old('bidang') === 'umum' ? 'selected' : '' }}>Umum</option>
                            </select>
                        </div>
                        <div class="col-md-5 form-group mb-2">
                            <label for="doc_nomor">Nomor Arsip</label>
                            <input type="text" id="doc_nomor" name="nomor_arsip" class="form-control form-control-sm"
                                value="{{ old('nomor_arsip') }}" maxlength="100" placeholder="Kosongkan untuk nomor otomatis">
                            <div class="arsip-doc-nomor-hint mt-1">
                                Otomatis: <span id="hint-klinik">{{ $suggestedNomor['klinik'] }}</span>
                                · <span id="hint-kesmas">{{ $suggestedNomor['kesmas'] }}</span>
                                · <span id="hint-umum">{{ $suggestedNomor['umum'] }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label for="doc_file">Berkas *</label>
                            <input type="file" id="doc_file" name="file" class="form-control-file form-control-sm" required
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                            <small class="form-text text-muted">PDF, Word, Excel, gambar, ZIP — maks. 10 MB</small>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label for="doc_keterangan">Keterangan</label>
                            <input type="text" id="doc_keterangan" name="keterangan" class="form-control form-control-sm"
                                value="{{ old('keterangan') }}" maxlength="2000" placeholder="Catatan arsip (opsional)">
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label for="doc_ref_label">Referensi (opsional)</label>
                            <input type="text" id="doc_ref_label" name="ref_label" class="form-control form-control-sm"
                                value="{{ old('ref_label') }}" placeholder="Nama pasien / kode sampel / no. lab">
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <label for="doc_ref_bidang">Ref. bidang</label>
                            <select id="doc_ref_bidang" name="ref_bidang" class="form-control form-control-sm">
                                <option value="">—</option>
                                <option value="klinik" {{ old('ref_bidang') === 'klinik' ? 'selected' : '' }}>Klinik</option>
                                <option value="kesmas" {{ old('ref_bidang') === 'kesmas' ? 'selected' : '' }}>Kesmas</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label for="doc_ref_id">Ref. ID</label>
                            <input type="text" id="doc_ref_id" name="ref_id" class="form-control form-control-sm"
                                value="{{ old('ref_id') }}" placeholder="ID permohonan / sampel">
                        </div>
                        <div class="col-md-3 form-group mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-cloud-upload-alt mr-1"></i> Simpan Dokumen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        @if($q !== '' || $bidang !== '')
            <p class="text-muted mb-2" style="font-size:12px;">
                Hasil pencarian
                @if($q !== '') &ldquo;{{ $q }}&rdquo; @endif
                ({{ method_exists($dokumenResults, 'total') ? $dokumenResults->total() : 0 }} ditemukan)
            </p>
        @else
            <div class="arsip-panel__title"><i class="fas fa-list"></i> Daftar Dokumen Arsip</div>
        @endif

        @if($dokumenResults->isEmpty())
            <p class="text-muted mb-0">Belum ada dokumen arsip{{ ($q !== '' || $bidang !== '') ? ' untuk filter ini' : '' }}.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover arsip-table">
                    <thead>
                        <tr>
                            <th>No. Arsip</th>
                            <th>Judul / Referensi</th>
                            <th>Bidang</th>
                            <th>Berkas</th>
                            <th>Diunggah</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dokumenResults as $doc)
                            <tr>
                                <td nowrap><strong>{{ $doc->nomor_arsip ?: '—' }}</strong></td>
                                <td style="white-space:normal;">
                                    <strong>{{ $doc->judul }}</strong>
                                    @if($doc->ref_label)
                                        <br><small class="text-muted">Ref: {{ $doc->ref_label }}</small>
                                    @endif
                                    @if($doc->keterangan)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($doc->keterangan, 80) }}</small>
                                    @endif
                                </td>
                                <td nowrap><span class="arsip-badge arsip-badge--bidang">{{ strtoupper($doc->bidang) }}</span></td>
                                <td nowrap>
                                    <small>{{ $doc->file_name_original }}</small><br>
                                    <small class="text-muted">{{ $doc->file_size_human }}</small>
                                </td>
                                <td nowrap>
                                    {{ $doc->created_at ? $doc->created_at->format('d/m/Y H:i') : '—' }}<br>
                                    <small class="text-muted">{{ $doc->uploaded_by_name ?: '—' }}</small>
                                </td>
                                <td nowrap>
                                    <a href="{{ route('pengarsipan-dokumen.download', $doc->id_pengarsipan_dokumen) }}"
                                        class="btn-arsip-doc" title="Unduh"><i class="fas fa-download"></i></a>
                                    @if(getAction('update'))
                                        <button type="button" class="btn-arsip-doc ml-1 js-toggle-edit"
                                            data-target="edit-doc-{{ $doc->id_pengarsipan_dokumen }}" title="Edit penomoran">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('pengarsipan-dokumen.destroy', $doc->id_pengarsipan_dokumen) }}"
                                            class="d-inline ml-1" onsubmit="return confirm('Hapus dokumen arsip ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-arsip-doc btn-arsip-doc--danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <div id="edit-doc-{{ $doc->id_pengarsipan_dokumen }}" class="arsip-doc-edit">
                                            <form method="POST" action="{{ route('pengarsipan-dokumen.nomor', $doc->id_pengarsipan_dokumen) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="form-group mb-1">
                                                    <label class="mb-0" style="font-size:11px;">Nomor Arsip</label>
                                                    <input type="text" name="nomor_arsip" class="form-control form-control-sm"
                                                        value="{{ $doc->nomor_arsip }}" required maxlength="100">
                                                </div>
                                                <div class="form-group mb-1">
                                                    <label class="mb-0" style="font-size:11px;">Judul</label>
                                                    <input type="text" name="judul" class="form-control form-control-sm"
                                                        value="{{ $doc->judul }}" maxlength="255">
                                                </div>
                                                <div class="form-group mb-1">
                                                    <label class="mb-0" style="font-size:11px;">Keterangan</label>
                                                    <input type="text" name="keterangan" class="form-control form-control-sm"
                                                        value="{{ $doc->keterangan }}" maxlength="2000">
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($dokumenResults, 'links'))
                <div class="mt-2">{{ $dokumenResults->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    document.querySelectorAll('.js-toggle-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var el = document.getElementById(btn.getAttribute('data-target'));
            if (el) el.classList.toggle('is-open');
        });
    });

    var bidangSelect = document.getElementById('doc_bidang_upload');
    var hints = {
        klinik: document.getElementById('hint-klinik'),
        kesmas: document.getElementById('hint-kesmas'),
        umum: document.getElementById('hint-umum')
    };
    if (bidangSelect) {
        bidangSelect.addEventListener('change', function () {
            Object.keys(hints).forEach(function (k) {
                if (hints[k]) hints[k].style.fontWeight = (k === bidangSelect.value) ? '700' : '400';
            });
        });
        bidangSelect.dispatchEvent(new Event('change'));
    }
})();
</script>
@endsection
