{{--
  Keterangan pasien haji + rombongan (nama_haji dari master tb_permohonan_uji_klinik_haji).

  Params:
    - $info_haji : ['is_haji'=>bool, 'nama_haji'=>?string, 'tgl_haji'=>?string]
    - $mode      : badge|compact|table-rows|alert (default: alert)
    - $thWidth   : optional width for table-rows th
--}}
@php
    $infoHaji = $info_haji ?? null;
    if (!is_array($infoHaji)) {
        $infoHaji = ['is_haji' => false, 'nama_haji' => null, 'tgl_haji' => null];
    }
    $isPasienHaji = !empty($infoHaji['is_haji']);
    $namaRombongan = trim((string) ($infoHaji['nama_haji'] ?? ''));
    $tglHaji = $infoHaji['tgl_haji'] ?? null;
    $mode = $mode ?? 'alert';
    $thWidth = $thWidth ?? '250px';
@endphp

@if ($isPasienHaji)
    @if ($mode === 'badge')
        <span class="badge badge-success badge-pill px-3 py-2 mr-2" style="font-size: 13px;" title="Pasien pemeriksaan haji">
            <i class="fa fa-mosque mr-1"></i> Haji
        </span>
        @if ($namaRombongan !== '')
            <span class="badge badge-info badge-pill px-3 py-2 mr-2" style="font-size: 13px;" title="Rombongan / Data Haji Dari">
                <i class="fa fa-users mr-1"></i> {{ $namaRombongan }}
            </span>
        @endif
    @elseif ($mode === 'compact')
        <div class="patient-data-compact-item">
            <i class="fa fa-mosque text-success"></i>
            <strong>Haji:</strong>
            <span>
                @if ($namaRombongan !== '')
                    {{ $namaRombongan }}
                @else
                    Pasien Haji
                @endif
            </span>
        </div>
    @elseif ($mode === 'table-rows')
        <tr>
            <th width="{{ $thWidth }}">Keterangan</th>
            <td>
                <span class="badge badge-success">
                    <i class="fa fa-mosque"></i> Pasien Haji
                </span>
            </td>
        </tr>
        <tr>
            <th width="{{ $thWidth }}">Rombongan</th>
            <td>
                @if ($namaRombongan !== '')
                    <strong>{{ $namaRombongan }}</strong>
                    @if (!empty($tglHaji))
                        <span class="text-muted ml-1">({{ \Carbon\Carbon::parse($tglHaji)->locale('id')->isoFormat('D MMMM Y') }})</span>
                    @endif
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
        </tr>
    @else
        {{-- alert (default) --}}
        <div class="alert alert-success d-flex align-items-center mb-3" role="alert" style="border-left: 4px solid #28a745;">
            <div class="mr-3" style="font-size: 1.5rem; line-height: 1;">
                <i class="fa fa-mosque"></i>
            </div>
            <div>
                <strong>Pasien Haji</strong>
                @if ($namaRombongan !== '')
                    <div class="mt-1">
                        Rombongan pemeriksaan dari:
                        <strong>{{ $namaRombongan }}</strong>
                        @if (!empty($tglHaji))
                            <span class="text-muted">
                                — {{ \Carbon\Carbon::parse($tglHaji)->locale('id')->isoFormat('D MMMM Y') }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
