@extends('masterweb::template.admin.layout')

@section('title')
    Jenis Sampel Klinik
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
                                <li class="breadcrumb-item active" aria-current="page"><span>Jenis Sampel Klinik</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex">
                <div class="mr-auto p-2">
                    <p class="text-muted mb-0">
                        Master data jenis sampel klinik (Darah, Serum, Plasma, dll).
                        Opsi ini dipakai di parameter satuan klinik, pengambilan sampel, dan penerimaan sampel.
                    </p>
                </div>
                <div class="p-2">
                    <a href="{{ route('elits-jenis-sampel-klinik.create') }}">
                        <button type="button" class="btn btn-info btn-icon-text">
                            Tambah Data
                            <i class="fa fa-plus btn-icon-append"></i>
                        </button>
                    </a>
                </div>
            </div>

            <div class="row">
                @if (session('status'))
                    <div class="col-12">
                        <div class="alert alert-success">{{ session('status') }}</div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="col-12">
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    </div>
                @endif

                <div class="col-12">
                    <div class="table-responsive">
                        <table id="order-listing" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Kode</th>
                                    <th>Status</th>
                                    <th>Urutan</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $item->name_jenis_sampel_klinik }}</td>
                                        <td>{{ $item->code_jenis_sampel_klinik ?: '-' }}</td>
                                        <td>
                                            @if ($item->is_active)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->sort_order }}</td>
                                        <td>
                                            <a href="{{ route('elits-jenis-sampel-klinik.edit', [$item->id_jenis_sampel_klinik]) }}">
                                                <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                            </a>
                                            <form onsubmit="return confirm('Hapus jenis sampel ini?')"
                                                class="d-inline"
                                                action="{{ route('elits-jenis-sampel-klinik.destroy', [$item->id_jenis_sampel_klinik]) }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-outline-danger btn-rounded btn-icon">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
