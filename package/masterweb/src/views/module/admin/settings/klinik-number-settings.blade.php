@extends('masterweb::template.admin.layout')

@section('title', 'Setting Nomor Lab & Spesimen Klinik')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Setting Nomor Lab & Spesimen Klinik</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Setting Nomor Lab & Spesimen</li>
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
                                <i class="fa fa-cog mr-2"></i>Pengaturan Global Nomor Lab & Spesimen
                            </h3>
                        </div>
                        <form id="settingsForm">
                            @csrf
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle mr-2"></i>
                                    <strong>Informasi:</strong> Setting ini berlaku untuk semua permohonan uji klinik. 
                                    Jika diaktifkan, form tambah/edit akan menampilkan field input manual.
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                            id="is_nomor_lab_manual" name="is_nomor_lab_manual" 
                                            value="1" {{ $settings->is_nomor_lab_manual ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_nomor_lab_manual">
                                            <strong>Input Manual Nomor Laboratorium</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Jika diaktifkan, form akan menampilkan field untuk input manual nomor laboratorium. 
                                        Jika tidak diisi, sistem akan menggunakan nomor otomatis.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                            id="is_nomor_spesimen_manual" name="is_nomor_spesimen_manual" 
                                            value="1" {{ $settings->is_nomor_spesimen_manual ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_nomor_spesimen_manual">
                                            <strong>Input Manual Nomor Spesimen</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Jika diaktifkan, form akan menampilkan field untuk input manual nomor spesimen. 
                                        Jika tidak diisi, sistem akan menggunakan nomor otomatis.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="description">Keterangan</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                        placeholder="Keterangan setting (opsional)">{{ $settings->description ?? '' }}</textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save mr-2"></i>Simpan Setting
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                    <i class="fa fa-undo mr-2"></i>Reset
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
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Menyimpan...');
        
        $.ajax({
            url: '{{ route("klinik-number-settings.update") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status) {
                    swal({
                        title: "Berhasil!",
                        text: response.message,
                        icon: "success",
                        button: "OK"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: response.message,
                        icon: "error"
                    });
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan setting';
                swal({
                    title: "Error!",
                    text: errorMsg,
                    icon: "error"
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endsection
