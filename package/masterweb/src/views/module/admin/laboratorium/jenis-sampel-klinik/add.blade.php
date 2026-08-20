@extends('masterweb::template.admin.layout')

@section('title')
    Tambah Jenis Sampel Klinik
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('elits-jenis-sampel-klinik.index') }}">Jenis Sampel Klinik</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Tambah</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="forms-sample" action="{{ route('elits-jenis-sampel-klinik.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name_jenis_sampel_klinik">Nama Jenis Sampel <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name_jenis_sampel_klinik" id="name_jenis_sampel_klinik"
                        value="{{ old('name_jenis_sampel_klinik') }}" required maxlength="100"
                        placeholder="Contoh: Darah, Serum, Plasma">
                </div>

                <div class="form-group">
                    <label for="code_jenis_sampel_klinik">Kode</label>
                    <input type="text" class="form-control" name="code_jenis_sampel_klinik" id="code_jenis_sampel_klinik"
                        value="{{ old('code_jenis_sampel_klinik') }}" maxlength="50"
                        placeholder="Opsional, contoh: DARAH">
                </div>

                <div class="form-group">
                    <label for="sort_order">Urutan</label>
                    <input type="number" class="form-control" name="sort_order" id="sort_order"
                        value="{{ old('sort_order', 0) }}" min="0">
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1"
                                {{ old('is_active', 1) ? 'checked' : '' }}>
                            Aktif
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                <a href="{{ route('elits-jenis-sampel-klinik.index') }}" class="btn btn-light">Kembali</a>
            </form>
        </div>
    </div>
@endsection
