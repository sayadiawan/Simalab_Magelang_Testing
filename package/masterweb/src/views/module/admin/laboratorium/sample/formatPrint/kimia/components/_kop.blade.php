@php
  $showKop = isset($showKop) ? (int) $showKop : 1;
  $kopWidth = isset($kopWidth) && $kopWidth !== '' ? $kopWidth : '100%';
@endphp

@if ($showKop)
<table width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td style="text-align: center;">
      <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}"
      width="{{ $kopWidth }}">
    </td>
  </tr>
</table>
@else
<div style="height: 120px;"></div>
@endif