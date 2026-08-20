<ul style="list-style: none; margin: 0; padding: 0; margin-left: 3em;">
  <li>Baku Mutu : </li>
  @if (count($all_acuan_baku_mutu) > 1)
      @foreach ($all_acuan_baku_mutu as $all_acuan_baku_mutu)
          <li> {{ $all_acuan_baku_mutu->title_library }} </li>
      @endforeach
  @else
      @if (count($all_acuan_baku_mutu) > 0)
          <li> {{ $all_acuan_baku_mutu[0]->title_library }} </li>
      @else
          <li> Baku mutu belum diinput </li>
      @endif
  @endif
</ul>
