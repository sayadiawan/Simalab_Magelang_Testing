@extends('masterweb::template.admin.layout')

@section('title')
    Petugas Management
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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-petugas') }}">Petugas</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex">
                <div class="mr-auto p-2">
                </div>

                <div class="p-2">
                    <a href="{{ route('adm-petugas-add') }}">
                        <button type="button" class="btn btn-info btn-icon-text">
                            Tambah Data
                            <i class="fa fa-plus btn-icon-append"></i>
                        </button>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="order-listing" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>NIP</th>
                                    <th>Gelar</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $no = 1;
                                @endphp
                                @foreach ($listPetugas as $petugas)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $petugas->nama }}</td>
                                        <td>{{ $petugas->nik }}</td>
                                        <td>{{ $petugas->nip ?? '-' }}</td>
                                        <td>{{ $petugas->gelar ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('adm-petugas-edit', [$petugas->id_petugas]) }}">
                                                <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                            </a>
                                            <form onsubmit="return confirm('Delete this user permanently?')"
                                                class="d-inline"
                                                action="{{ route('adm-petugas-delete', [$petugas->id_petugas]) }}"
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
