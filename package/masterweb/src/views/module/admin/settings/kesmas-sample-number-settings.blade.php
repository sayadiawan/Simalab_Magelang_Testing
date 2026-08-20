@extends('masterweb::template.admin.layout')

@section('title', 'Setting Nomor Sampel & Laboratorium Kesmas')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Setting Nomor Sampel & Laboratorium Kesmas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Setting Kesmas (sampel)</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa fa-cog mr-2"></i>Pengaturan input manual (halaman tambah sampel Kesmas)
                            </h3>
                        </div>
                        <form id="kesmasSampleSettingsForm">
                            @csrf
                            <div class="card-body">
                                @if (!\Smt\Masterweb\Models\KesmasSampleNumberSettings::tableExists())
                                    <div class="alert alert-danger">
                                        <strong><i class="fa fa-database mr-2"></i>Tabel belum ada.</strong>
                                        Jalankan <kbd>php artisan migrate</kbd> pada project ini, lalu muat ulang halaman.
                                    </div>
                                @endif
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle mr-2"></i>
                                    <strong>Informasi:</strong> Opsi nomor sampel mengatur apakah kode sampel
                                    diisi manual di form <code>/elits-samples/create/…</code>.
                                    Nomor laboratorium Kesmas (<code>449.5/01|02/{urut}/tahun</code>)
                                    ditetapkan di <strong>akhir pemeriksaan / pengesahan hasil</strong>
                                    (otomatis; opsional isi urut manual di pengesahan).
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input"
                                            id="is_nomor_sampel_manual" name="is_nomor_sampel_manual"
                                            value="1" {{ $settings->is_nomor_sampel_manual ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_nomor_sampel_manual">
                                            <strong>Input manual nomor / kode sampel</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Kode sampel penuh per lab (Kimia / Mikrobiologi), misalnya format
                                        <code>AM.01/0123/2026</code>. Sistem tidak menimpa dengan urutan otomatis.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input"
                                            id="is_nomor_laboratorium_manual" name="is_nomor_laboratorium_manual"
                                            value="1" {{ $settings->is_nomor_laboratorium_manual ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_nomor_laboratorium_manual">
                                            <strong>Izinkan override nomor laboratorium di pengesahan</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Jika aktif: di pengesahan hasil, petugas boleh mengisi nomor urut lab
                                        manual (atau kosongkan untuk otomatis). Digunakan untuk
                                        <code>tb_nomer_lab_kesmas</code> / LHU. Tidak diisi saat tambah sampel.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="description">Keterangan</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                        placeholder="Catatan internal (opsional)">{{ $settings->description ?? '' }}</textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save mr-2"></i>Simpan
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                    <i class="fa fa-undo mr-2"></i>Muat ulang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#kesmasSampleSettingsForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Menyimpan...');

        $.ajax({
            url: '{{ route("kesmas-sample-number-settings.update") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status) {
                    swal({
                        title: "Berhasil",
                        text: response.message,
                        icon: "success",
                        button: "OK"
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    swal({ title: "Error", text: response.message, icon: "error" });
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Gagal menyimpan.';
                swal({ title: "Error", text: msg, icon: "error" });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endsection
