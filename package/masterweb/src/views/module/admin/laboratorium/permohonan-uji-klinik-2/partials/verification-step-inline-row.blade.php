@php
    $stepKey = $stepKey ?? 'step';
    $suffix = $suffix ?? '';
    $jamValue = $jamValue ?? '';
    $selectedPetugas = $selectedPetugas ?? '';
    $listPetugas = $listPetugas ?? [];
    $baseHref = $baseHref ?? '#';
    $buttonLabel = $buttonLabel ?? 'Input';
    $actionEnabled = $actionEnabled ?? true;
    $disabledTitle = $disabledTitle ?? 'Langkah sebelumnya belum selesai';
    $requirePetugas = $requirePetugas ?? false;
    $requireJam = $requireJam ?? false;
    $jamForStorage = '';
    if (!empty($jamValue)) {
        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', (string) $jamValue)) {
                $jamForStorage = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $jamValue)->format('Y-m-d H:i');
            } elseif (preg_match('/^\d{1,2}:\d{2}$/', (string) $jamValue)) {
                $tglBase = \Carbon\Carbon::parse($item->created_at ?? $item->tglregister_permohonan_uji_klinik ?? now())->format('Y-m-d');
                $jamForStorage = $tglBase . ' ' . $jamValue;
            } else {
                $jamForStorage = \Carbon\Carbon::parse($jamValue)->format('Y-m-d H:i');
            }
        } catch (\Exception $e) {
            $jamForStorage = '';
        }
    }
    $fieldId = $stepKey . $suffix;
    $listPetugas = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames($listPetugas);
    if ($selectedPetugas !== '') {
        $matchedPetugas = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::findMatchingPetugasName($selectedPetugas, $listPetugas);
        if ($matchedPetugas !== null) {
            $selectedPetugas = $matchedPetugas;
        } else {
            $listPetugas[] = $selectedPetugas;
            $listPetugas = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames($listPetugas);
            $matchedPetugas = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::findMatchingPetugasName($selectedPetugas, $listPetugas);
            if ($matchedPetugas !== null) {
                $selectedPetugas = $matchedPetugas;
            }
        }
    }
@endphp
<td>
    <input type="text" class="form-control verification-step-jam" name="start_date"
        id="verification_step_jam_{{ $fieldId }}" value="{{ $jamValue }}"
        data-step-key="{{ $stepKey }}" data-suffix="{{ $suffix }}"
        data-initial-jam="{{ $jamForStorage }}"
        @if(!$actionEnabled) disabled @endif
        @if($requireJam) required @endif>
</td>
<td class="d-none">
    <input type="hidden" class="verification-step-stop" name="stop_date"
        id="verification_step_stop_{{ $fieldId }}" value="{{ $jamValue }}"
        data-step-key="{{ $stepKey }}" data-suffix="{{ $suffix }}">
</td>
<td>
    <select class="verification-step-petugas" name="nama_petugas"
        id="verification_step_petugas_{{ $fieldId }}"
        data-step-key="{{ $stepKey }}" data-suffix="{{ $suffix }}"
        data-initial-petugas="{{ $selectedPetugas }}"
        @if(!$actionEnabled) disabled @endif
        @if($requirePetugas) required @endif>
        <option value="">-- Pilih Petugas --</option>
        @foreach ($listPetugas as $nama_petugas)
            <option value="{{ $nama_petugas }}" {{ $selectedPetugas == $nama_petugas ? 'selected' : '' }}>
                {{ $nama_petugas }}
            </option>
        @endforeach
    </select>
    <input type="hidden" class="verification-step-meta-jam" id="verification_meta_jam_{{ $fieldId }}"
        value="{{ $jamForStorage }}" data-step-key="{{ $stepKey }}" data-suffix="{{ $suffix }}">
    <input type="hidden" class="verification-step-meta-petugas" id="verification_meta_petugas_{{ $fieldId }}"
        value="{{ $selectedPetugas }}" data-step-key="{{ $stepKey }}" data-suffix="{{ $suffix }}">
</td>
<td class="text-center">
    @if($actionEnabled)
        <a href="javascript:void(0)"
            class="link-input-verification-step"
            data-step-key="{{ $stepKey }}"
            data-suffix="{{ $suffix }}"
            data-base-href="{{ $baseHref }}"
            data-jam="{{ $jamForStorage }}"
            data-petugas="{{ $selectedPetugas }}"
            data-require-jam="{{ $requireJam ? '1' : '0' }}"
            data-require-petugas="{{ $requirePetugas ? '1' : '0' }}">
            <button type="button" class="btn btn-primary verification-step-action-btn" @if($requireJam || $requirePetugas) disabled @endif>{{ $buttonLabel }}</button>
        </a>
    @else
        <button type="button" class="btn btn-primary" disabled title="{{ $disabledTitle }}">{{ $buttonLabel }}</button>
    @endif
</td>
