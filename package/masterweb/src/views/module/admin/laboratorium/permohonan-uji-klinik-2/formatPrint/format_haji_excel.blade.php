@php
    $manualNomorLab = !empty($klinikNumberSettings) && $klinikNumberSettings->is_nomor_lab_manual;
    $manualNomorSpesimen = !empty($klinikNumberSettings) && $klinikNumberSettings->is_nomor_spesimen_manual;
    $totalCols = 9 + ($manualNomorLab ? 1 : 0) + ($manualNomorSpesimen ? 1 : 0);
@endphp
<table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
    <thead>
        <tr>
            <th align="center" colspan="{{ $totalCols }}" style="border: 1px solid black;"><b> DATA HAJI {{ strtoupper($nama_haji) }},
                    {{ strtoupper(\Carbon\Carbon::parse($tgl_haji)->translatedFormat('d F Y')) }}
                </b></th>
        </tr>
    </thead>
</table>
<table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
    <thead>
        <tr style="background-color: green; border-collapse: collapse; border: 1px solid black;">
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 1cm; border: 1px solid black;">
                No.</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 7cm; border: 1px solid black;">
                Nama</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                NIK</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                Kelamin</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Tempat Lahir</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Tgl Lahir</th>
            <th scope="col"
                style="background-color: red; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Tgl Lahir (String)</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 5cm; border: 1px solid black;">
                Alamat Pasien</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                Pekerjaan</th>
            @if($manualNomorLab)
            <th scope="col"
                style="background-color: orange; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                Nomor Lab</th>
            @endif
            @if($manualNomorSpesimen)
            <th scope="col"
                style="background-color: orange; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                Nomor Sample</th>
            @endif
        </tr>
    </thead>
    <tbody style="border: 1px solid black;">
        {{-- contoh input  --}}
        <tr style="border: 1px solid black;">
            <th style="text-align: center; border: 1px solid black; background-color: #00dbf8;">1</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">Pasien A</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">3308123456789012</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">L</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">SIMLAB</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">02 Januari 1995</th>
            <th style="text-align: left; border: 1px solid black; background-color: red;">= TEXT(F4,"DD MMMM YYYY")
            </th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">Pengging RT 06/ RW 04</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">PNS</th>
            @if($manualNomorLab)
            <th style="text-align: left; border: 1px solid black; background-color: #ffe0b2;">1</th>
            @endif
            @if($manualNomorSpesimen)
            <th style="text-align: left; border: 1px solid black; background-color: #ffe0b2;">2832</th>
            @endif
        </tr>
        @foreach ($rows as $key => $val)
            <tr style="border: 1px solid black;">
                <td style="text-align: center; border: 1px solid black;">{{ $key + 1 }}.</td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black; background-color: red;">=
                    TEXT(F{{ $key + 5 }},"DD MMMM YYYY")</td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                @if($manualNomorLab)
                <td style="text-align: left; border: 1px solid black;">{{ ($nextLabNumber ?? 1) + $key }}</td>
                @endif
                @if($manualNomorSpesimen)
                <td style="text-align: left; border: 1px solid black;"></td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
