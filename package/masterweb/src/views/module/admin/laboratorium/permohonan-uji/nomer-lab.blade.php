@extends('masterweb::template.admin.layout')

@section('title')
    Input Nomor Lab Kesmas
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('elits-permohonan-uji.index') }}">Permohonan Uji</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('elits-samples.index', [$id]) }}">Daftar Sampel</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Input Nomor Lab</li>
                </ol>
            </nav>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <span class="text-muted small d-block">Kode Permohonan</span>
                        <strong>{{ $permohonan_uji->code_permohonan_uji ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Pelanggan</span>
                        <strong>{{ optional($permohonan_uji->customer)->name_customer ?? '-' }}</strong>
                    </div>
                </div>
                <div class="col-md-6 text-md-right">
                    <a href="{{ route('elits-samples.index', [$id]) }}" class="btn btn-outline-secondary btn-sm mb-1">
                        <i class="fa fa-list"></i> Daftar Sampel
                    </a>
                    <a href="{{ route('elits-permohonan-uji.index') }}" class="btn btn-outline-secondary btn-sm mb-1">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
            <h5 class="mb-0" style="color: #1565c0;">
                <i class="fa fa-hashtag mr-2"></i>Nomor Laboratorium Kesmas
            </h5>
            <small class="text-muted">
                Satu nomor per kombinasi <strong>jenis sampel × laboratorium</strong>.
                Format: <code>449.5/01|02/{urut}/{{ $year }}</code> (01=Kimia, 02=Mikro).
                Kosongkan field untuk menghapus nomor yang sudah disimpan.
            </small>
        </div>
        <div class="card-body">
            @if ($rows->isEmpty())
                <div class="alert alert-warning mb-0">
                    Belum ada sampel pada permohonan ini. Tambah sampel terlebih dahulu, lalu input nomor lab.
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fa fa-info-circle mr-1"></i>
                    Nomor lab gabungan <strong>kesmas + klinik</strong>.
                    Terakhir terpakai:
                    <strong>{{ $lastLabNumber > 0 ? $lastLabNumber : 'belum ada' }}</strong>
                    → berikutnya:
                    <strong>{{ $nextPreview }}</strong>
                    (contoh Kimia:
                    <code>449.5/01/{{ $nextPreview }}/{{ $year }}</code>).
                    Field kosong diisi otomatis dari nomor berikutnya.
                </div>

                <form method="POST" action="{{ route('elits-permohonan-uji.nomer-lab.store', [$id]) }}" id="form-nomer-lab-kesmas">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Jenis Sampel</th>
                                    <th>Laboratorium</th>
                                    <th style="width: 80px;">Jml Sampel</th>
                                    <th style="min-width: 280px;">Nomor Lab (angka urut)</th>
                                    <th style="min-width: 200px;">Pratinjau</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <strong>{{ $row->code_sample_type }}</strong>
                                            — {{ $row->name_sample_type }}
                                            <input type="hidden" name="items[{{ $i }}][sample_type_id]" value="{{ $row->sample_type_id }}">
                                        </td>
                                        <td>
                                            {{ $row->nama_laboratorium }}
                                            <span class="badge badge-secondary">{{ $row->lab_seg }}</span>
                                            <input type="hidden" name="items[{{ $i }}][laboratorium_id]" value="{{ $row->laboratorium_id }}">
                                        </td>
                                        <td class="text-center">{{ $row->sample_count }}</td>
                                        <td>
                                            <div class="input-group input-group-sm" style="max-width: 320px;">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text font-weight-bold" style="letter-spacing: 0.3px;">
                                                        449.5/{{ $row->lab_seg }}/
                                                    </span>
                                                </div>
                                                <input type="text"
                                                       inputmode="numeric"
                                                       pattern="[0-9]*"
                                                       class="form-control text-center nomer-lab-urut"
                                                       name="items[{{ $i }}][nomer_lab]"
                                                       value="{{ $row->nomer_lab ?: ($row->nomer_lab_default ?? $nextPreview) }}"
                                                       placeholder="{{ $nextPreview }}"
                                                       data-lab-seg="{{ $row->lab_seg }}"
                                                       data-preview-id="preview-{{ $i }}"
                                                       autocomplete="off">
                                                <div class="input-group-append">
                                                    <span class="input-group-text font-weight-bold">/{{ $year }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code id="preview-{{ $i }}" class="nomer-lab-preview">
                                                @php $urutPreview = $row->nomer_lab ?: ($row->nomer_lab_default ?? $nextPreview); @endphp
                                                @if ($urutPreview)
                                                    449.5/{{ $row->lab_seg }}/{{ $urutPreview }}/{{ $row->year ?: $year }}
                                                @else
                                                    <span class="text-muted">Belum diisi</span>
                                                @endif
                                            </code>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                        <small class="text-muted">
                            Menyimpan akan memperbarui <code>tb_nomer_lab_kesmas</code> dan menyelaraskan
                            <code>tb_lab_num</code> untuk semua sampel pada kombinasi tersebut.
                        </small>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save mr-1"></i> Simpan Nomor Lab
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            var year = @json($year);
            document.querySelectorAll('.nomer-lab-urut').forEach(function(input) {
                var run = function() {
                    var d = String(input.value || '').replace(/\D/g, '');
                    input.value = d;
                    var seg = input.getAttribute('data-lab-seg') || '01';
                    var preview = document.getElementById(input.getAttribute('data-preview-id'));
                    if (!preview) return;
                    if (!d) {
                        preview.innerHTML = '<span class="text-muted">Belum diisi</span>';
                    } else {
                        preview.textContent = '449.5/' + seg + '/' + d + '/' + year;
                    }
                };
                input.addEventListener('input', run);
            });
        })();
    </script>
@endsection
