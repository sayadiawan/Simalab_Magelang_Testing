@extends('masterweb::template.admin.layout')

@section('title')
  Master Client
@endsection

@section('content')

  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/adm-client') }}">Master Client</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
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
          {{-- <div id="datepicker-popup" class="input-group date datepicker">
                <input type="text" class="form-control">
                <span class="input-group-addon input-group-append border-left">
                    <span class="far fa-calendar input-group-text"></span>
                </span>
            </div> --}}
        </div>

        <div class="p-2">
          <div class="">
            <a href="{{ route('adm-client.create') }}">
              <button type="submit" class="btn btn-info mb-2"><i class="fa fa-plus-circle mr-2"></i>Tambah Data</button>
            </a>

            <button type="submit" class="btn btn-light mb-2"><i class="far fa-file-excel mr-2"></i>Ekspor</button>
            <button type="submit" class="btn btn-light mb-2"><i class="fa fa-print mr-2"></i>Cetak</button>
          </div>
        </div>
      </div>

      <div class="row">
        @if (session('status'))
          <div class="alert alert-success">
            {{ session('status') }}
          </div>
        @endif
        <div class="col-12">
          <div class="table-responsive">
            <table id="order-listing" class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Urutan</th>
                  <th>Images</th>
                  <th>Publish</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $no = 1;
                @endphp
                @foreach ($data as $data)
                  <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $data->urutan }}</td>
                    <td><a href="{{ asset('assets/public/images/client/' . $data->file_client) }}" target="_blank"><img
                          src="{{ asset('assets/public/images/client/' . $data->file_client) }}"></a></td>
                    <td><label class="badge badge-info"></label>
                      @if ($data->publish == 1)
                        <a href="{{ url('adm-client/publish/' . $data->id_client) }}"><label
                            class="badge badge-info">Aktif</label></a>
                      @else
                        <a href="{{ url('adm-client/publish/' . $data->id_client) }}"><label
                            class="badge badge-danger">Tidak Aktif</label></a>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('adm-client.edit', [$data->id_client]) }}">
                        <button type="button" class="btn btn-dark btn-rounded btn-icon" data-toggle="tooltip"
                          data-custom-class="tooltip-dark" data-placement="top" title=""
                          data-original-title="Edit Data">
                          <i class="fas fa-pencil-alt"></i>
                        </button>
                      </a>
                      <form onsubmit="return confirm('Apakah anda yakin ingin menghapus data ini?')"
                        class="d-inline" action="{{ route('adm-client.destroy', [$data->id_client]) }}"
                        method="POST">

                        @csrf

                        <input type="hidden" name="_method" value="DELETE">

                        <button type="submit" class="btn btn-danger btn-rounded btn-icon" data-toggle="tooltip"
                          data-custom-class="tooltip-danger" data-placement="top" title="Hapus Data">
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
