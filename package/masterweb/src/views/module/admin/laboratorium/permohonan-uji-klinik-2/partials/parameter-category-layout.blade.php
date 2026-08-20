@php
    $paket = $paket ?? [];
    $paket_extra = $paket_extra ?? [];
@endphp

@if(isset($categoryLayouts) && count($categoryLayouts) > 0)
    @foreach($categoryLayouts as $category)
        <div class="category-header">
            {{ $category->category_code }}. {{ $category->category_name }}
        </div>

        @php
            $gridRows = $category->grid_rows ?? 0;
            $gridColumns = $category->grid_columns ?? 3;
            $items = $category->categoryItems ?? collect();
            $useGrid = $items->where('row_position', '!=', null)->count() > 0;

            if ($useGrid) {
                $grid = [];
                $maxRow = 0;
                foreach ($items as $item) {
                    if ($item->parameterPaketKlinik && $item->row_position && $item->column_position) {
                        $row = (int) $item->row_position;
                        $col = (int) $item->column_position;

                        if (!isset($grid[$row])) {
                            $grid[$row] = [];
                        }
                        $grid[$row][$col] = $item;

                        if ($row > $maxRow) {
                            $maxRow = $row;
                        }
                    }
                }
                ksort($grid);
                $actualRows = $gridRows > 0 ? (int) $gridRows : max($maxRow, 1);
            }
        @endphp

        @if($useGrid)
            <div class="parameter-list" style="grid-template-columns: repeat({{ $gridColumns }}, 1fr);">
                @for($r = 1; $r <= $actualRows; $r++)
                    @for($c = 1; $c <= $gridColumns; $c++)
                        @if(isset($grid[$r][$c]) && $grid[$r][$c]->parameterPaketKlinik)
                            @php $item = $grid[$r][$c]; @endphp
                            <div class="parameter-item">
                                <input type="checkbox" class="form-check-input"
                                    name="jenis_parameters[{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}][pakets][]"
                                    value="{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}_{{ $item->parameterPaketKlinik->harga_parameter_paket_klinik }}"
                                    @if (in_array($item->parameterPaketKlinik->name_parameter_paket_klinik, array_column($paket, 'name_parameter_paket_klinik'))) checked @endif
                                    id="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                <label for="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                    {{ $item->parameterPaketKlinik->name_parameter_paket_klinik }}
                                </label>
                            </div>
                        @else
                            <div class="parameter-item parameter-empty">
                                <span style="opacity: 0;">&nbsp;</span>
                            </div>
                        @endif
                    @endfor
                @endfor
            </div>
        @else
            <div class="parameter-list">
                @if($category->categoryItems && count($category->categoryItems) > 0)
                    @foreach($category->categoryItems as $item)
                        @if($item->parameterPaketKlinik)
                            <div class="parameter-item">
                                <input type="checkbox" class="form-check-input"
                                    name="jenis_parameters[{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}][pakets][]"
                                    value="{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}_{{ $item->parameterPaketKlinik->harga_parameter_paket_klinik }}"
                                    @if (in_array($item->parameterPaketKlinik->name_parameter_paket_klinik, array_column($paket, 'name_parameter_paket_klinik'))) checked @endif
                                    id="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                <label for="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                    {{ $item->parameterPaketKlinik->name_parameter_paket_klinik }}
                                </label>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        @endif
    @endforeach
@else
    <div class="category-header">A. HEMATOLOGI</div>
    <div class="parameter-list">
        @php
            $hematologi_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                $nama = strtolower($jenis->name_parameter_jenis_klinik);
                return str_contains($nama, 'darah') ||
                    str_contains($nama, 'hematologi') ||
                    str_contains($nama, 'hemoglobin');
            });
            $hematologi_pakets = collect();
            foreach ($hematologi_jenis as $jenis) {
                if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                    $hematologi_pakets = $hematologi_pakets->merge($jenis->pakets);
                }
            }
            $hematologi_pakets = $hematologi_pakets->sortBy('sort')->unique('id_parameter_paket_klinik')->values();
        @endphp
        @foreach ($hematologi_pakets as $paket_item)
            <div class="parameter-item">
                <input type="checkbox" class="form-check-input"
                    name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                    value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                    @if (in_array($paket_item->name_parameter_paket_klinik, array_column($paket, 'name_parameter_paket_klinik'))) checked @endif
                    id="hematologi_{{ $paket_item->id_parameter_paket_klinik }}">
                <label for="hematologi_{{ $paket_item->id_parameter_paket_klinik }}">{{ $paket_item->name_parameter_paket_klinik }}</label>
            </div>
        @endforeach
    </div>

    <div class="category-header">B. URIN</div>
    <div class="parameter-list">
        @php
            $urin_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                $nama = strtolower($jenis->name_parameter_jenis_klinik);
                return str_contains($nama, 'urin') ||
                    str_contains($nama, 'urine') ||
                    str_contains($nama, 'narkoba');
            });
            $urin_pakets = collect();
            foreach ($urin_jenis as $jenis) {
                if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                    $urin_pakets = $urin_pakets->merge($jenis->pakets);
                }
            }
            $urin_pakets = $urin_pakets->sortBy('sort')->unique('id_parameter_paket_klinik')->values();
        @endphp
        @foreach ($urin_pakets as $paket_item)
            <div class="parameter-item">
                <input type="checkbox" class="form-check-input"
                    name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                    value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                    @if (in_array($paket_item->name_parameter_paket_klinik, array_column($paket, 'name_parameter_paket_klinik'))) checked @endif
                    id="urin_{{ $paket_item->id_parameter_paket_klinik }}">
                <label for="urin_{{ $paket_item->id_parameter_paket_klinik }}">{{ $paket_item->name_parameter_paket_klinik }}</label>
            </div>
        @endforeach
    </div>

    <div class="category-header">C. IMUNOLOGI</div>
    <div class="parameter-list">
        @php
            $imunologi_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                $nama = strtolower($jenis->name_parameter_jenis_klinik);
                return str_contains($nama, 'imunologi') ||
                    str_contains($nama, 'serologi') ||
                    str_contains($nama, 'widal') ||
                    str_contains($nama, 'dengue') ||
                    str_contains($nama, 'hepatitis');
            });
            $imunologi_pakets = collect();
            foreach ($imunologi_jenis as $jenis) {
                if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                    $imunologi_pakets = $imunologi_pakets->merge($jenis->pakets);
                }
            }
            $imunologi_pakets = $imunologi_pakets->sortBy('sort')->unique('id_parameter_paket_klinik')->values();
        @endphp
        @foreach ($imunologi_pakets as $paket_item)
            <div class="parameter-item">
                <input type="checkbox" class="form-check-input"
                    name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                    value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                    @if (in_array($paket_item->name_parameter_paket_klinik, array_column($paket, 'name_parameter_paket_klinik'))) checked @endif
                    id="imunologi_{{ $paket_item->id_parameter_paket_klinik }}">
                <label for="imunologi_{{ $paket_item->id_parameter_paket_klinik }}">{{ $paket_item->name_parameter_paket_klinik }}</label>
            </div>
        @endforeach
    </div>

    <div class="category-header">D. KIMIA DARAH</div>
    <div class="parameter-list">
        @php
            $kimia_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                $nama = strtolower($jenis->name_parameter_jenis_klinik);
                return str_contains($nama, 'kimia') || str_contains($nama, 'klinik');
            });
            $kimia_pakets = collect();
            foreach ($kimia_jenis as $jenis) {
                if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                    $kimia_pakets = $kimia_pakets->merge($jenis->pakets);
                }
            }
            $kimia_pakets = $kimia_pakets->sortBy('sort')->unique('id_parameter_paket_klinik')->values();
        @endphp
        @foreach ($kimia_pakets as $paket_item)
            <div class="parameter-item">
                <input type="checkbox" class="form-check-input"
                    name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                    value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                    @if (in_array($paket_item->name_parameter_paket_klinik, array_column($paket, 'name_parameter_paket_klinik'))) checked @endif
                    id="kimia_{{ $paket_item->id_parameter_paket_klinik }}">
                <label for="kimia_{{ $paket_item->id_parameter_paket_klinik }}">{{ $paket_item->name_parameter_paket_klinik }}</label>
            </div>
        @endforeach
    </div>
@endif

@if ($parameter_paket_extra->isNotEmpty())
    <div class="category-header">PAKET EXTRA</div>
    <div class="parameter-list">
        @foreach ($parameter_paket_extra as $val)
            <div class="parameter-item">
                <input type="checkbox" class="form-check-input"
                    name="paket_extra[{{ $val->id_parameter_paket_extra }}]"
                    value="{{ $val->id_parameter_paket_extra }}_{{ $val->harga_parameter_paket_extra }}"
                    {{ in_array($val->id_parameter_paket_extra, $paket_extra) ? 'checked' : '' }}
                    id="extra_{{ $val->id_parameter_paket_extra }}">
                <label for="extra_{{ $val->id_parameter_paket_extra }}">{{ $val->nama_parameter_paket_extra }}</label>
            </div>
        @endforeach
    </div>
@endif
