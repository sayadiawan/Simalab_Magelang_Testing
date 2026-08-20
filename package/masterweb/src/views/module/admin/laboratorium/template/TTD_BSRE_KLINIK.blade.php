{{-- Template tandatangan elektronik untuk laporan hasil --}}

<table style="border-collapse: collapse;">
    <tr>

        <td style="text-align: center">
            <span style="font-size: 10pt !important">Ditandatangani secara elektronik oleh:</span>
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
            <span style="font-size: 10pt !important"> <u><b>{{ $petugas }}</b></u></span>
            <br>
            <span style="font-size: 10pt !important">{{ $nip }}</span>
        </td>
    </tr>
</table>
