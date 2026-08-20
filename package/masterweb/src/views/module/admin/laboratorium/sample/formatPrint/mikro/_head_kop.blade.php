@php
    $showKop = isset($showKop) ? (int) $showKop : 1;
@endphp

<table width="100%" cellspacing="0" cellpadding="0">
    @if ($showKop)
        <tr>
            <td align="center"> <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}"
                    width="100%" style="margin-bottom: 4px;">
            </td>
        </tr>
        <tr></tr>
    @else
        <tr>
            <td style="height: 120px;"></td>
        </tr>
    @endif
    <tr>
        <td align="center" class="page-title" style="font-size: 13pt">
            <strong><u>LAPORAN HASIL UJI</u></strong>
        </td>
    </tr>
</table>
