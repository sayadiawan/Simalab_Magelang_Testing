
{{--Template tandatangan elektronik untuk print verifikasi--}}

<div style="border: 1px solid black; height: 15px; width: 110px; padding: 1px;">
  <img style="float: left; margin-right: 2px;" src="{{ public_path("assets/admin/images/logo/logo-bsre.png") }}" height="13" width="13">
  <div style="font-size: 5px;">
    <span>Ditandatangani secara elektronik oleh:</span>
    <br>
    @php
      if (!isset($petugas)){
        $petugas = "";
      }

      if (!isset($nip)){
        $nip = "";
      }
    @endphp
    <b>{{ $petugas }}</b>
    <br>
    <span>{{ $nip }}</span>
  </div>
</div>
