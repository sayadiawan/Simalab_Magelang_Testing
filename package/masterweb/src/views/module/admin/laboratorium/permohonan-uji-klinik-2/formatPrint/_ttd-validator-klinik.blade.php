{{--
  Blok TTD tetap di kanan halaman.
  Kolom kanan mengecil mengikuti teks (nowrap).
  Bulan panjang → blok tetap nempel kanan, sisi kiri mundur, Kota Mungkid s/d NIP tetap lurus.
--}}
@php
    $tglTtdLabel = $tglTtdLabel ?? '';
    $fsTtd = isset($fs) ? (float) $fs : 12;
    $lhTtd = isset($lh) ? (float) $lh : 1.5;
    $validasiTtd = $validasi ?? null;
    $namaValidasiTtd = $nama_petugas_validasi ?? null;
    $signOptionTtd = $signOption ?? 0;
    $showManualTtd = !($validasiTtd && $namaValidasiTtd) || (int) $signOptionTtd === 0;
@endphp
<table class="no-break" width="100%" cellspacing="0" cellpadding="0" border="0"
    style="font-size: {{ $fsTtd }}pt !important; line-height: {{ $lhTtd }} !important; border-collapse: collapse;">
    <tr>
        <td style="width: 99%;">&nbsp;</td>
        <td style="width: 1%; white-space: nowrap; text-align: left; vertical-align: top; font-size: {{ $fsTtd }}pt !important; line-height: {{ $lhTtd }} !important;">
            <div style="white-space: nowrap; text-align: left;">Kota Mungkid, {{ $tglTtdLabel }}</div>
            @if ($showManualTtd)
            <div style="white-space: nowrap; text-align: left;">Validator</div>
            <div style="white-space: nowrap; text-align: left;"><br><br><br></div>
            <div style="white-space: nowrap; text-align: left;">dr. Sunantyo, M.P.H.</div>
            <div style="white-space: nowrap; text-align: left;">Pembina</div>
            <div style="white-space: nowrap; text-align: left;">NIP. 197001282000031001</div>
            @endif
        </td>
    </tr>
</table>
