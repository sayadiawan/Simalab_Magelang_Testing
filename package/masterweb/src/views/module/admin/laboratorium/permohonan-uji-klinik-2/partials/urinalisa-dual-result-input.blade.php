@php
    $masterOptionCsv = $item_satuan_klinik['parameter_satuan_klinik_option']
        ?? $item_satuan_klinik['option']
        ?? null;
    $requiresNamaJenisFlag = (int) (
        ($item_satuan_klinik['requires_nama_jenis'] ?? 0)
        || ($item_satuan_klinik['parameter_satuan_klinik_requires_nama_jenis'] ?? 0)
    ) === 1;
    $dualType = \Smt\Masterweb\Helpers\Smt::urinalisaDualColumnType(
        $item_satuan_klinik['nama_parameter_satuan_klinik'] ?? '',
        $masterOptionCsv,
        $requiresNamaJenisFlag
    );
    $existingHasil = $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '';
    $findings = \Smt\Masterweb\Helpers\Smt::parseUrinalisaDualFindings($existingHasil);
    $positivityOptions = ['Negatif', 'Positif'];

    $detailOptions = \Smt\Masterweb\Helpers\Smt::urinalisaDualDetailOptions($dualType, $masterOptionCsv);

    $matchDetail = function ($selectedDetail) use (&$detailOptions) {
        $selectedDetail = trim((string) $selectedDetail);
        if ($selectedDetail === '') {
            return '';
        }
        if (in_array($selectedDetail, $detailOptions, true)) {
            return $selectedDetail;
        }

        $savedGrade = \Smt\Masterweb\Helpers\Smt::extractUrinalisaGradeToken($selectedDetail);
        if ($savedGrade) {
            foreach ($detailOptions as $opt) {
                if (\Smt\Masterweb\Helpers\Smt::extractUrinalisaGradeToken($opt) === $savedGrade) {
                    return $opt;
                }
            }
        }

        if (
            strcasecmp($selectedDetail, 'Negatif') !== 0
            && strcasecmp($selectedDetail, 'Positif') !== 0
        ) {
            array_unshift($detailOptions, $selectedDetail);
        }

        return $selectedDetail;
    };

    $firstFinding = $findings[0] ?? ['positivity' => 'Negatif', 'detail' => '', 'name' => ''];
    $selectedPositivity = strcasecmp($firstFinding['positivity'] ?? '', 'Negatif') === 0
        ? 'Negatif'
        : 'Positif';
    $selectedDetail = $matchDetail($firstFinding['detail'] ?? '');

    $jenisRows = $requiresNamaJenisFlag
        ? \Smt\Masterweb\Helpers\Smt::buildUrinalisaJenisRowsForInput($existingHasil)
        : [];

    $jenisLabel = $dualType === 'kristal'
        ? 'kristal'
        : ($dualType === 'silinder' ? 'silinder' : 'hasil');
    $requiresNamaJenis = $requiresNamaJenisFlag;
    $allowMultiple = $requiresNamaJenis
        || in_array($dualType, ['kristal', 'silinder', 'lain-lain'], true);
    $showDetail = strcasecmp($selectedPositivity, 'Negatif') !== 0;
    $showName = $requiresNamaJenis && $showDetail;
@endphp
<div class="urinalisa-dual-input"
    data-param-no="{{ $no }}"
    data-dual-type="{{ $dualType }}"
    data-requires-nama-jenis="{{ $requiresNamaJenis ? '1' : '0' }}"
    data-allow-multiple="{{ $allowMultiple ? '1' : '0' }}">
    <div class="row no-gutters">
        <div class="{{ $requiresNamaJenis ? 'col-12' : 'col-md-5 pr-1' }}">
            <select class="form-control form-control-sm urinalisa-positivity-select"
                id="urinalisa_positivity_{{ $no }}"
                aria-label="Positif/Negatif">
                @foreach ($positivityOptions as $opt)
                    <option value="{{ $opt }}" @if ($selectedPositivity === $opt) selected @endif>
                        {{ $opt }}
                    </option>
                @endforeach
            </select>
        </div>
        @if (!$requiresNamaJenis)
        <div class="col-md-7 pl-1 urinalisa-detail-wrap"
            id="urinalisa_detail_wrap_{{ $no }}"
            style="{{ $showDetail ? '' : 'display:none;' }}">
            <select class="form-control form-control-sm urinalisa-detail-input"
                id="urinalisa_detail_{{ $no }}"
                aria-label="Grade {{ $jenisLabel }}"
                aria-required="true">
                <option value="">- Pilih -</option>
                @foreach ($detailOptions as $opt)
                    <option value="{{ $opt }}" @if (strcasecmp($selectedDetail, $opt) === 0) selected @endif>
                        {{ $opt }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>
    @if ($requiresNamaJenis)
    <div class="urinalisa-names mt-1"
        id="urinalisa_name_wrap_{{ $no }}"
        style="{{ $showName ? '' : 'display:none;' }}">
        @foreach ($jenisRows as $rowIndex => $jenisRow)
            @php
                $rowDetail = $matchDetail($jenisRow['detail'] ?? '');
            @endphp
            <div class="urinalisa-name-row{{ $rowIndex > 0 ? ' mt-1' : '' }}">
                <div class="d-flex align-items-center urinalisa-finding-row">
                    <div class="urinalisa-grade-col pr-1">
                        <select class="form-control form-control-sm urinalisa-detail-input"
                            @if ($rowIndex === 0) id="urinalisa_detail_{{ $no }}" @endif
                            aria-label="Grade {{ $jenisLabel }}"
                            aria-required="true">
                            <option value="">- Pilih -</option>
                            @foreach ($detailOptions as $opt)
                                <option value="{{ $opt }}" @if (strcasecmp($rowDetail, $opt) === 0) selected @endif>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="urinalisa-name-col flex-grow-1">
                        <input type="text"
                            class="form-control form-control-sm urinalisa-name-input"
                            @if ($rowIndex === 0) id="urinalisa_name_{{ $no }}" @endif
                            value="{{ $jenisRow['name'] ?? '' }}"
                            placeholder="Nama jenis {{ $jenisLabel }}, contoh: Ca. Oxalate"
                            aria-label="Nama jenis {{ $jenisLabel }}"
                            autocomplete="off">
                    </div>
                    @if ($rowIndex > 0)
                        <button type="button"
                            class="btn btn-sm btn-outline-danger urinalisa-remove-finding ml-1"
                            title="Hapus jenis"
                            aria-label="Hapus jenis">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif
    @if ($allowMultiple && $requiresNamaJenis)
    <button type="button"
        class="btn btn-sm btn-outline-primary urinalisa-add-finding mt-1"
        style="{{ $showName ? '' : 'display:none;' }}">
        <i class="fa fa-plus" aria-hidden="true"></i> Tambah jenis
    </button>
    @endif
    @if ($requiresNamaJenis)
        <small class="text-muted d-block mt-1">
            Hasil di badge: nama + <code>(+)</code> → contoh <strong>Ca. Oxalate (+)</strong>
        </small>
    @endif
    <small class="text-muted d-block mt-1 urinalisa-dual-help"
        title="Kolom 1: negatif/positif. Setiap jenis punya grade (+/++/+++) sendiri{{ $requiresNamaJenis ? '. Nama jenis bisa lebih dari satu.' : '' }}.">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        @if ($requiresNamaJenis)
            Pilih Positif → pilih grade dan ketik nama untuk setiap jenis.
            @if ($allowMultiple)
                Bila jenisnya lebih dari satu, klik <strong>Tambah jenis</strong>.
            @endif
        @else
            Pilih Positif → pilih grade (+/++/+++). Parameter ini tidak membutuhkan nama jenis.
        @endif
    </small>
    <div class="urinalisa-dual-actions" data-param-no="{{ $no }}"></div>
    <small class="text-danger d-none urinalisa-detail-required-hint mt-1"
        id="urinalisa_detail_hint_{{ $no }}">
        Pilih grade {{ $jenisLabel }} jika hasil Positif.
    </small>
    @if ($allowMultiple && $requiresNamaJenis)
    <div class="d-none urinalisa-finding-row-template" aria-hidden="true">
        <div class="urinalisa-name-row mt-1">
            <div class="d-flex align-items-center urinalisa-finding-row">
                <div class="urinalisa-grade-col pr-1">
                    <select class="form-control form-control-sm urinalisa-detail-input"
                        aria-label="Grade {{ $jenisLabel }}"
                        aria-required="true">
                        <option value="">- Pilih -</option>
                        @foreach ($detailOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="urinalisa-name-col flex-grow-1">
                    <input type="text"
                        class="form-control form-control-sm urinalisa-name-input"
                        value=""
                        placeholder="Nama jenis {{ $jenisLabel }}, contoh: Ca. Oxalate"
                        aria-label="Nama jenis {{ $jenisLabel }}"
                        autocomplete="off">
                </div>
                <button type="button"
                    class="btn btn-sm btn-outline-danger urinalisa-remove-finding ml-1"
                    title="Hapus jenis"
                    aria-label="Hapus jenis">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
