<style>
    td {
        vertical-align: top;
    }
</style>
@php
    $catatanHasilHtml = \Smt\Masterweb\Helpers\Smt::formatCatatanHasilKesmasHtml($sample ?? null, true);
@endphp
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td width="7%">Catatan</td>
        <td width="2%">:</td>
        <td>
            {!! $catatanHasilHtml !!}
        </td>
    </tr>
</table>
<br><br><br>
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        @include('masterweb::module.admin.laboratorium.sample.formatPrint._tanggal_cetak_footer_lhu')
    </tr>
    <tr>
        <td colspan="3" style="text-align: left; padding-left:80px;"></td>
    </tr>
    <tr>
        <td style="text-align: left; vertical-align: bottom;" width="33%">
            Mengetahui<br>
            Validator,
        </td>
        <td style="text-align: left" width="33%">
            Verifikator,
        </td>
        <td style="text-align: left" width="33%">
            Pemeriksa,
        </td>
    </tr>
    <tr>
        <td><br><br><br><br><br></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td style="text-align: left">
                @if (isset($data['validator']))
                    @if ($data['validator'] && isset($data['validator']->nama))
                        {{ $data['validator']->nama }}
                    @else
                        ______________________
                    @endif
                @endif
                @if (isset($validator))
                    @if ($validator && isset($validator->nama))
                        {{ $validator->nama }}
                    @else
                        ______________________
                    @endif
                @endif
            <br>
            NIP @if (isset($data['validator']))
                @if ($data['validator'] && isset($data['validator']->nip))
                    {{ preg_replace('/\s+/', '', (string) ($data['validator']->nip)) }}
                @endif
            @endif
            @if (isset($validator))
                @if ($validator && isset($validator->nip))
                    {{ preg_replace('/\s+/', '', (string) ($validator->nip)) }}
                @endif
            @endif
        </td>
        <td style="text-align: left">
                @if (isset($data['verifikator']))
                    @if ($data['verifikator'] && isset($data['verifikator']->nama))
                        {{ $data['verifikator']->nama }}
                    @else
                        ______________________
                    @endif
                @endif
                @if (isset($verifikator))
                    @if ($verifikator && isset($verifikator->nama))
                        {{ $verifikator->nama }}
                    @else
                        ______________________
                    @endif
                @endif
            <br>
            NIP @if (isset($data['verifikator']))
                @if ($data['verifikator'] && isset($data['verifikator']->nip))
                    {{ preg_replace('/\s+/', '', (string) ($data['verifikator']->nip)) }}
                @endif
            @endif
            @if (isset($verifikator))
                @if ($verifikator && isset($verifikator->nip))
                    {{ preg_replace('/\s+/', '', (string) ($verifikator->nip)) }}
                @endif
            @endif
        </td>
        <td style="text-align: left">
                @if (isset($data['pemeriksa']))
                @if ($data['pemeriksa'] && isset($data['pemeriksa']->nama))
                    {{ $data['pemeriksa']->nama }}
                    @else
                        ______________________
                    @endif
                @endif
                @if (isset($pemeriksa))
                    @if ($pemeriksa && isset($pemeriksa->nama))
                        {{ $pemeriksa->nama }}
                    @else
                        ______________________
                    @endif
                @endif
            <br>
            NIP @if (isset($data['pemeriksa']))
                @if ($data['pemeriksa'] && isset($data['pemeriksa']->nip))
                    {{ preg_replace('/\s+/', '', (string) ($data['pemeriksa']->nip)) }}
                @endif
            @endif
            @if (isset($pemeriksa))
                @if ($pemeriksa && isset($pemeriksa->nip))
                    {{ preg_replace('/\s+/', '', (string) ($pemeriksa->nip)) }}
                @endif
            @endif
        </td>
    </tr>
</table>
    <div style="position: fixed; bottom: 0px; text-align: left;">
        <p style="font-size: 12px; margin: 0; padding: 0;"><i>Dokumen ini ditandatangani secara elektronik menggunakan
                Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi
                Negara</i></p>
    </div>
