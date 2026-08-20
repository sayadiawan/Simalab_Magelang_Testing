@php
  $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($sample ?? null);
@endphp

<p style="margin: 0; padding: 0"> Keterangan : </p>

@if ($keteranganMetodeCustom !== '')
<ul class="keterangan">
  <li>
    <span>{{ $keteranganMetodeCustom }}</span>
  </li>
</ul>
@elseif(isset($x_baku_mutu))
<ul class="keterangan">
  @foreach ($x_baku_mutu as $baku_mutu)
  <li>
    <span style="white-space: pre;">{!! $baku_mutu !!}</span>
  </li>
  @endforeach
</ul>
@else
<ul class="keterangan">
  @if (count($all_acuan_baku_mutu) > 1)
  @foreach ($all_acuan_baku_mutu as $all_acuan_baku_mutu)
  <li>
    <span> {{ $all_acuan_baku_mutu->title_library }} </span>
  </li>
  @endforeach
  @else
  @if (count($all_acuan_baku_mutu) > 0)
  <li>
    <span style="padding-right: 3em"> Baku Mutu : </span>
    <span> {{ $all_acuan_baku_mutu[0]->title_library }} </span>
  </li>
  @else
  <li> Baku mutu belum diinput </li>
  @endif
  @endif
</ul>
@endif

<table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 1px;">
  <tr>
    <td style="text-align: left">Demikian hasil pemeriksaan ini untuk dapat digunakan seperlunya.</td>
  </tr>
</table>
