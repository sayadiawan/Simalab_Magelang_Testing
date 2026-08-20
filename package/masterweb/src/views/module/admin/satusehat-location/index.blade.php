@extends('masterweb::template.admin.layout')

@section('title')
    Satu Sehat Location Management
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-satusehat-location') }}">Satu Sehat
                                        Location</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-map-marker-alt mr-2"></i>Satu Sehat Location Management
            </h4>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle mr-1"></i>Kelola data lokasi Satu Sehat
                </p>
                <a href="{{ route('adm-satusehat-location-add') }}">
                    <button type="button" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus mr-2"></i>Tambah Data
                    </button>
                </a>
            </div>
            <div class="table-responsive">
                <table id="order-listing" class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Location</th>
                            <th>Kode Satu Sehat</th>
                            <th>Version</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                        @endphp
                        @foreach ($locations as $location)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>
                                    <strong>{{ $location->name_satusehat_location }}</strong>
                                </td>
                                <td>
                                    @if ($location->kode_satusehat_location)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle mr-1"></i>{{ $location->kode_satusehat_location }}
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Belum sync
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($location->version_satusehat_location === 'prd')
                                        <span class="badge badge-danger">Production</span>
                                    @else
                                        <span class="badge badge-info">Staging</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('adm-satusehat-location-edit', [$location->id_satusehat_location]) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <form onsubmit="return confirm('Apakah Anda yakin ingin menghapus location ini?')"
                                            class="d-inline"
                                            action="{{ route('adm-satusehat-location-delete', [$location->id_satusehat_location]) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
