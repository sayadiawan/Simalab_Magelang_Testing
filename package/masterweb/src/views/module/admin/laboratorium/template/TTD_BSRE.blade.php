<div>
    <span >Ditandatangani secara elektronik oleh:</span>
            <br>
            @if (isset($qrBase64))
                <img src="data:image/png;base64, {{ $qrBase64 }}" alt="QR Code" width="70" height="70">
            @endif
            <br>
            @php
                if (!isset($petugas)) {
                    $petugas = '';
                }

                if (!isset($nip)) {
                    $nip = '';
                }
            @endphp
            <span> <u><b>{{ $petugas }}</b></u></span>
            <br>
            <span>{{ $nip }}</span>
</div>
