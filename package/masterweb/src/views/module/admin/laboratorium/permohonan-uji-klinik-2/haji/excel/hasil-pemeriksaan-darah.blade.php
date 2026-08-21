@php
    // Hitung total kolom
    $fixedColumns = 6; // No, NO. SPECIMEN, NAMA, UMUR, JK, ALAMAT
    $totalParamColumns = 0;
    $kategoriColumns = [];
    
    // Hitung kolom untuk setiap kategori
    foreach (['DARAH RUTIN', 'KIMIA DARAH'] as $kategori) {
        $count = 0;
        if (isset($parameterColumns[$kategori]) && !empty($parameterColumns[$kategori])) {
            foreach ($parameterColumns[$kategori] as $param) {
                $count++; // Kolom untuk parameter utama
                if (!empty($param['sub_params'])) {
                    $count += count($param['sub_params']); // Kolom untuk sub parameter
                }
            }
        }
        $kategoriColumns[$kategori] = $count;
        $totalParamColumns += $count;
    }
    
    $totalColumns = $fixedColumns + $totalParamColumns + 1; // +1 untuk KETERANGAN
    $darahRutinCols = $kategoriColumns['DARAH RUTIN'] ?? 0;
    $kimiaDarahCols = $kategoriColumns['KIMIA DARAH'] ?? 0;
@endphp

<table>
    <!-- Header -->
    <tr>
        <td colspan="{{ $totalColumns }}" style="text-align: center; font-size: 14px; font-weight: bold; padding: 10px;">
            PEMERINTAH KABUPATEN MAGELANG<br>
            UPTD LABORATORIUM KESEHATAN<br>
            Jl. Soekarno-Hatta, Tegalrejo, SIMLAB Testing, Kode Pos 56192<br>
            Telp. (0293) 314197
        </td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="text-align: center; font-size: 16px; font-weight: bold; padding: 10px;">
            HASIL PEMERIKSAAN DARAH<br>
            CALON JAMAAH HAJI {{ date('Y', strtotime($haji->tgl_haji)) }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="padding: 5px;">
            No. Lab: {{ $data[0]['no_specimen'] ?? '-' }} | 
            Tgl. Terima: {{ $tgl_pemeriksaan }} | 
            Tgl. Keluar: {{ \Carbon\Carbon::now()->format('d F Y') }}
        </td>
    </tr>
    <tr></tr>
    
    <!-- Header Kategori (KIMIA DARAH / DARAH RUTIN) -->
    <tr>
        <td colspan="{{ $fixedColumns }}" style="border: 1px solid #000; background-color: #FFFFFF;"></td>
        @if($darahRutinCols > 0)
            <td colspan="{{ $darahRutinCols }}" style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #FFFFFF; font-weight: bold; font-size: 14px;">
                DARAH RUTIN
            </td>
        @endif
        @if($kimiaDarahCols > 0)
            <td colspan="{{ $kimiaDarahCols }}" style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #FFFFFF; font-weight: bold; font-size: 14px;">
                KIMIA DARAH
            </td>
        @endif
        <td colspan="1" style="border: 1px solid #000; background-color: #FFFFFF;"></td>
    </tr>
    
    <!-- Table Header -->
    <tr>
        <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">No.</th>
        <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">NO. SPECIMEN</th>
        <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">NAMA</th>
        <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">UMUR</th>
        <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">JK</th>
        <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">ALAMAT</th>
        
        @foreach (['DARAH RUTIN', 'KIMIA DARAH'] as $kategori)
            @if(isset($parameterColumns[$kategori]) && !empty($parameterColumns[$kategori]))
                @foreach ($parameterColumns[$kategori] as $param)
                    @php
                        $paramName = $param['name'];
                        $refRange = $param['ref_range'] ?? '';
                    @endphp
                    <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">
                        {{ strtoupper($paramName) }}
                        @if($refRange)
                            <br><small>{{ $refRange }}</small>
                        @endif
                    </th>
                    
                    @if(!empty($param['sub_params']))
                        @foreach ($param['sub_params'] as $subParam)
                            @php
                                $subParamName = $subParam['name'];
                                $subRefRange = $subParam['ref_range'] ?? '';
                            @endphp
                            <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">
                                {{ strtoupper($subParamName) }}
                                @if($subRefRange)
                                    <br><small>{{ $subRefRange }}</small>
                                @endif
                            </th>
                        @endforeach
                    @endif
                @endforeach
            @endif
        @endforeach
        
        <th style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #E9ECEF; font-weight: bold;">KETERANGAN</th>
    </tr>
    
    <!-- Data Rows -->
    @php
        $no = 1;
    @endphp
    @foreach ($data as $row)
        <tr>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $no++ }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $row['no_specimen'] }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ strtoupper($row['nama']) }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $row['umur'] }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $row['jk'] }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ strtoupper($row['alamat']) }}</td>
            
            @php
                $parameterResults = $row['parameter_results'] ?? [];
            @endphp
            
            @foreach (['DARAH RUTIN', 'KIMIA DARAH'] as $kategori)
                @if(isset($parameterColumns[$kategori]) && !empty($parameterColumns[$kategori]))
                    @foreach ($parameterColumns[$kategori] as $param)
                        @php
                            $paramId = $param['id'];
                            $hasil = $parameterResults[$kategori][$paramId] ?? '';
                        @endphp
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $hasil }}</td>
                        
                        @if(!empty($param['sub_params']))
                            @foreach ($param['sub_params'] as $subParam)
                                @php
                                    $subParamId = $subParam['id'];
                                    $subHasil = $parameterResults[$kategori][$paramId . '_sub'][$subParamId] ?? '';
                                @endphp
                                <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $subHasil }}</td>
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
            
            <td style="border: 1px solid #000; padding: 5px;">{{ $row['keterangan'] ?? '' }}</td>
        </tr>
    @endforeach
    
    <!-- Footer -->
    <tr></tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="padding: 20px; text-align: right;">
            —, {{ \Carbon\Carbon::now()->format('d F Y') }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="padding: 5px; text-align: center;">
            Dokter Penanggung Jawab<br>
            Laboratorium SIMLAB
        </td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="padding: 40px; text-align: center;">
            <br><br>
            (___________________________)
        </td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="padding: 5px; text-align: center;">
            dr. ISNAINI, Sp.PK<br>
            NIP. 197904232006042014
        </td>
    </tr>
</table>
