@extends('masterweb::template.admin.layout')

@section('title')
  Data Analisa Klinik
@endsection

@section('content')
  <div class="card">
    <div class="card-header">
      Data Analisa Klinik
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="order-listing" class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Laboratorium</th>
                  <th>Nomor Registrasi</th>
                  <th>Tanggal Registrasi</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $no = 1;
                @endphp
                @foreach($data as $item)
                  <tr>
                    <td>{{ $no++ }}</td>
                    <td>Klinik</td>
                    <td>{{ $item->noregister_permohonan_uji_klinik }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tglregister_permohonan_uji_klinik)->translatedFormat('d F Y') }}</td>
                    <td>
                      @if (isset($listVerifications[$item->id_permohonan_uji_klinik]))
                        @php
                        $verification = $listVerifications[$item->id_permohonan_uji_klinik];
                        @endphp
                        @switch($verification->id_verification_activity)
                          @case(1)
                            <label class="badge badge-primary badge-pill w-50">{{ $verification->name }}</label>
                            @break
                          @case(2)
                            <label class="badge badge-warning badge-pill w-50">{{ $verification->name }}</label>
                            @break
                          @case(3)
                            <label class="badge badge-danger badge-pill w-50">{{ $verification->name }}</label>
                            @break
                          @case(4)
                            <label class="badge badge-info badge-pill w-50">{{ $verification->name }}</label>
                            @break
                          @case(5)
                            <label class="badge badge-success badge-pill w-50">{{ $verification->name }}</label>
                            @break
                          @default
                            <label class="badge badge-dark badge-pill w-50">{{ $verification->name }}</label>
                        @endswitch
                      @else
                        <label class="badge badge-secondary badge-pill w-50">No Status</label>
                      @endif
                    </td>
                    <td>
                        <a href="{{ route('elits-permohonan-uji-klinik-2.verification', $item->id_permohonan_uji_klinik) }}" type="button" class="btn btn-outline-success btn-lg btn-rounded" title="Verifikasi">
                            <i class="fa fa-check"></i>
                        </a>
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
