@php
    use Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2 as KlinikCtrl;
    $petugasDiisiPelangganValue = KlinikCtrl::PETUGAS_PENGAMBIL_DIISI_PELANGGAN;
    $petugasDiisiPelangganLabel = KlinikCtrl::PETUGAS_PENGAMBIL_DIISI_PELANGGAN_LABEL;
    $jamValue = $jamValue ?? $defaultJamPengambilSample;
    $selectedPetugas = $selectedPetugas ?? '';
    $inputIdSuffix = $inputIdSuffix ?? '';
    $sampleCount = $sampleCount ?? 1;
    $jamForInputLink = '';
    if (!empty($jamValue)) {
        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', (string) $jamValue)) {
                $jamForInputLink = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $jamValue)->format('Y-m-d H:i');
            } elseif (preg_match('/^\d{1,2}:\d{2}$/', (string) $jamValue)) {
                $tglBase = \Carbon\Carbon::today()->format('Y-m-d');
                $jamForInputLink = $tglBase . ' ' . $jamValue;
            } else {
                $jamForInputLink = \Carbon\Carbon::parse($jamValue)->format('Y-m-d H:i');
            }
        } catch (\Exception $e) {
            $jamForInputLink = '';
        }
    }
    $inputSampleBaseUrl = route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', [
        $item->id_permohonan_uji_klinik,
        $sampleCount,
    ]);
    $saveMetaUrl = route('elits-permohonan-uji-klinik-2.save-pengambil-sample-meta', $item->id_permohonan_uji_klinik);
@endphp
{{-- Jangan bungkus <td> dengan <form> (invalid HTML); browser memutus form sehingga simpan gagal --}}
<td>
    <input type="text" class="form-control pengambil-start-date" name="start_date"
        id="start_date_sample{{ $inputIdSuffix }}" value="{{ $jamValue }}" required
        data-suffix="{{ $inputIdSuffix }}"
        data-save-meta-url="{{ $saveMetaUrl }}"
        data-sample-count="{{ $sampleCount }}"
        data-initial-jam="{{ $jamForInputLink }}"
        @if(!$hasPemeriksaan) disabled @endif>
</td>
<td class="d-none">
    <input type="hidden" class="pengambil-stop-date" name="stop_date"
        id="stop_date_sample{{ $inputIdSuffix }}" value="{{ $jamValue }}"
        data-suffix="{{ $inputIdSuffix }}">
</td>
<td>
    <select class="pengambil-nama-petugas" name="nama_petugas"
        id="namaPetugasPengambilanSampel{{ $inputIdSuffix }}" required
        data-suffix="{{ $inputIdSuffix }}"
        data-initial-petugas="{{ $selectedPetugas }}"
        @if(!$hasPemeriksaan) disabled @endif>
        <option value="">-- Pilih Petugas --</option>
        <option value="{{ $petugasDiisiPelangganValue }}"
            {{ $selectedPetugas === $petugasDiisiPelangganValue ? 'selected' : '' }}>
            {{ $petugasDiisiPelangganLabel }}
        </option>
        @foreach ($listPetugasPengambilSample as $nama_petugas)
            <option value="{{ $nama_petugas }}" {{ $selectedPetugas == $nama_petugas ? 'selected' : '' }}>
                {{ $nama_petugas }}
            </option>
        @endforeach
    </select>
    <input type="hidden" class="pengambil-meta-jam" id="pengambil_meta_jam{{ $inputIdSuffix }}"
        value="{{ $jamForInputLink }}" data-suffix="{{ $inputIdSuffix }}">
    <input type="hidden" class="pengambil-meta-petugas" id="pengambil_meta_petugas{{ $inputIdSuffix }}"
        value="{{ $selectedPetugas }}" data-suffix="{{ $inputIdSuffix }}">
</td>
<td class="text-center">
    @if($hasPemeriksaan)
        <a href="{{ $inputSampleBaseUrl }}"
            class="link-input-pengambil-sample"
            data-suffix="{{ $inputIdSuffix }}"
            data-base-href="{{ $inputSampleBaseUrl }}"
            data-save-meta-url="{{ $saveMetaUrl }}"
            data-sample-count="{{ $sampleCount }}"
            data-jam="{{ $jamForInputLink }}"
            data-petugas="{{ $selectedPetugas }}">
            <button type="button" class="btn btn-primary">Input</button>
        </a>
    @else
        <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
    @endif
    <button type="button" class="btn btn-warning" data-toggle="modal"
        data-sampling="{{ max(0, (int) $sampleCount - 1) }}"
        data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
</td>
