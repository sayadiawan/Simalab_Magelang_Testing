@php
    $compactNota = !empty($compactNota);
    $signaturePlaceholderHeight = $compactNota ? 16 : 30;
@endphp
<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 3px;">
    <tr>
        <td></td>
        <td style="text-align: center; vertical-align: top; padding: 3px;">
            @if (($document_type ?? 'nota') === 'nota' && ((isset($permohonan_uji->status_pembayaran) && (string)$permohonan_uji->status_pembayaran === "1") || ($sisa ?? 0) <= 0))
                <div style="margin-bottom: 2px;">
                    <span class="stamp-lunas-badge">LUNAS</span>
                </div>
                @if(!empty($permohonan_uji->tanggal_bayar))
                    Dibayar pada: {{ \Carbon\Carbon::parse($permohonan_uji->tanggal_bayar)->format('d/m/Y') }}
                @endif
            @elseif (($document_type ?? 'nota') === 'invoice')
                Dokumen tagihan — belum merupakan bukti pembayaran
            @endif
        </td>
        <td></td>
    </tr>
    <tr>
        <td width="33%" style="vertical-align: top;">
            *) Kritik dan Saran
            <br><b style="margin-left: 10px; font-size: 13px;">089 538 499 0489</b>
        </td>
        <td width="33%" style="text-align: center; padding-top: 5px;">
            Pelanggan
            <br>
            @if (!empty($permohonan_uji->signature_nota_pasien))
                <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                    <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_pasien) }}"
                        alt="TTD Pelanggan"
                        style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                </div>
            @else
                <div style="height: {{ $signaturePlaceholderHeight }}px;"></div>
            @endif
            <br>
            {{ $nama_customer ?? '-' }}
        </td>
        <td width="34%" style="text-align: center; padding-top: 5px;">
            Pendaftar
            <br>
            @if (!empty($permohonan_uji->signature_nota_petugas))
                <div style="height: 40px; display: flex; align-items: center; justify-content: center;">
                    <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_petugas) }}"
                        alt="TTD Petugas"
                        style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                </div>
            @else
                <div style="height: {{ $signaturePlaceholderHeight }}px;"></div>
            @endif
            <br>
            {{ $pendaftar ?? '-' }}
        </td>
    </tr>
</table>

