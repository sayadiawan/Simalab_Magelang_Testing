{{-- Kop sama dengan cetak hasil (LHU) Kimia/Mikro/Klinik --}}
@include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._kop', [
    'showKop' => 1,
    'kopWidth' => $kopWidth ?? '100%',
])
