<table>
  @if($isYearly)
    {{-- Export for entire year --}}
    @for($m = 1; $m <= 12; $m++)
      @php
        $monthData = $data[$m] ?? null;
        if (!$monthData) continue;
        
        $startDate = \Carbon\Carbon::create($year, $m, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $m, 1)->endOfMonth();
        $daysInMonth = $endDate->day;
        
        $kimiaParams = $monthData['kimia_params'] ?? ['GDN', 'GD 2 Jam PP', 'GDS', 'HbA1c', 'Cholesterol', 'LDL', 'HDL', 'Trigliserid', 'Asam Urat', 'Ureum', 'Creatinin', 'SGOT', 'SGPT'];
        $otherParams = $monthData['other_params'] ?? ['Darah rutin', 'Hemoglobin', 'LED', 'Widal', 'Golongan darah', 'HBsAg', 'Urin rutin', 'Tes Kehamilan', 'Tes Narkoba', 'NS1', 'Dengue IgG/IgM', 'Typhi IgG/IgM', 'Croschek TB', 'Feses'];
      @endphp
      
      @include('masterweb::module.admin.laboratorium.report.v2.klinik.report-annual.excel-table', [
        'monthData' => $monthData,
        'month' => $m,
        'year' => $year,
        'daysInMonth' => $daysInMonth,
        'kimiaParams' => $kimiaParams,
        'otherParams' => $otherParams,
        'reportTitle' => $reportTitle ?? 'Catatan Harian Pemeriksaan Unit Klinik',
        'isLast' => $m == 12
      ])
    @endfor
  @else
    {{-- Export for single month --}}
    @php
      $monthData = $data[$month] ?? null;
      if ($monthData) {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        $daysInMonth = $endDate->day;
        
        $kimiaParams = $monthData['kimia_params'] ?? ['GDN', 'GD 2 Jam PP', 'GDS', 'HbA1c', 'Cholesterol', 'LDL', 'HDL', 'Trigliserid', 'Asam Urat', 'Ureum', 'Creatinin', 'SGOT', 'SGPT'];
        $otherParams = $monthData['other_params'] ?? ['Darah rutin', 'Hemoglobin', 'LED', 'Widal', 'Golongan darah', 'HBsAg', 'Urin rutin', 'Tes Kehamilan', 'Tes Narkoba', 'NS1', 'Dengue IgG/IgM', 'Typhi IgG/IgM', 'Croschek TB', 'Feses'];
      }
    @endphp
    
    @if($monthData)
      @include('masterweb::module.admin.laboratorium.report.v2.klinik.report-annual.excel-table', [
        'monthData' => $monthData,
        'month' => $month,
        'year' => $year,
        'daysInMonth' => $daysInMonth,
        'kimiaParams' => $kimiaParams,
        'otherParams' => $otherParams,
        'reportTitle' => $reportTitle ?? 'Catatan Harian Pemeriksaan Unit Klinik',
        'isLast' => true
      ])
    @endif
  @endif
</table>

