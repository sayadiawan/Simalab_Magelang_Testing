@extends('masterweb::template.admin.layout')
@section('title')
    Satu Sehat Location Management
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-map-marker-alt mr-2"></i>Edit Satu Sehat Location
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle mr-1"></i>Edit data lokasi Satu Sehat
                    </p>
                    <form enctype="multipart/form-data" class="forms-sample"
                        action="{{ route('adm-satusehat-location-update', [$location->id_satusehat_location]) }}"
                        method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_satusehat_location">
                                        <i class="fas fa-tag mr-1 text-primary"></i>Nama Location <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="name_satusehat_location"
                                        name="name_satusehat_location" placeholder="Masukkan nama location"
                                        value="{{ old('name_satusehat_location', $location->name_satusehat_location) }}"
                                        required>
                                    @error('name_satusehat_location')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="version_satusehat_location">
                                        <i class="fas fa-code-branch mr-1 text-primary"></i>Version <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-control-lg" id="version_satusehat_location"
                                        name="version_satusehat_location" required>
                                        <option value="">Pilih Version</option>
                                        <option value="prd"
                                            {{ old('version_satusehat_location', $location->version_satusehat_location) == 'prd' ? 'selected' : '' }}>
                                            Production (prd)
                                        </option>
                                        <option value="stg"
                                            {{ old('version_satusehat_location', $location->version_satusehat_location) == 'stg' ? 'selected' : '' }}>
                                            Staging (stg)
                                        </option>
                                    </select>
                                    <small class="text-muted mt-1 d-block">
                                        <i class="fas fa-lightbulb"></i> Pilih "Production" untuk sync dengan API Satu Sehat
                                    </small>
                                    @error('version_satusehat_location')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if ($location->kode_satusehat_location)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Kode Satu Sehat:</strong> {{ $location->kode_satusehat_location }}
                                <br>
                                <small>Location ini sudah terhubung dengan API Satu Sehat</small>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Belum terhubung dengan API:</strong> Location ini belum memiliki kode Satu Sehat.
                                Sistem akan otomatis membuat location di API saat menyimpan jika version = prd.
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">
                                        <i class="fas fa-align-left mr-1 text-primary"></i>Description
                                        <small class="text-muted">(Opsional)</small>
                                    </label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                        placeholder="Masukkan deskripsi location">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="physical_type_code">
                                        <i class="fas fa-building mr-1 text-primary"></i>Physical Type Code
                                        <small class="text-muted">(Opsional)</small>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="physical_type_code"
                                        name="physical_type_code" placeholder="Contoh: ro (Room)"
                                        value="{{ old('physical_type_code', 'ro') }}">
                                    <small class="text-muted mt-1 d-block">
                                        <i class="fas fa-info-circle"></i> Kode tipe fisik lokasi (default: ro = Room)
                                    </small>
                                    @error('physical_type_code')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="physical_type_display">
                                        <i class="fas fa-building mr-1 text-primary"></i>Physical Type Display
                                        <small class="text-muted">(Opsional)</small>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="physical_type_display"
                                        name="physical_type_display" placeholder="Contoh: Room"
                                        value="{{ old('physical_type_display', 'Room') }}">
                                    <small class="text-muted mt-1 d-block">
                                        <i class="fas fa-info-circle"></i> Nama tipe fisik lokasi (default: Room)
                                    </small>
                                    @error('physical_type_display')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">
                                        <i class="fas fa-toggle-on mr-1 text-primary"></i>Status
                                        <small class="text-muted">(Opsional)</small>
                                    </label>
                                    <select class="form-control form-control-lg" id="status" name="status">
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Informasi:</strong> Jika version adalah "Production" dan VERSION_SATUSEHAT = prd,
                            sistem akan otomatis mengupdate data di API Satu Sehat saat menyimpan.
                        </div>

                        <hr class="my-4">

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-warning btn-lg mr-2">
                                <i class="fas fa-save mr-2"></i>Update
                            </button>
                            <button type="button" class="btn btn-light btn-lg"
                                onclick="window.location.href='{{ route('adm-satusehat-location') }}'">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
