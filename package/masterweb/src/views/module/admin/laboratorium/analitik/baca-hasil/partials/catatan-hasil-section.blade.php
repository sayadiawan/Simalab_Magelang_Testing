{{--
  Catatan footer LHU untuk halaman baca-hasil kesmas.

  Params:
    - $sample
    - $lab (opsional) — untuk default mikro (MBI)
--}}
@php
    $labContext = $lab ?? null;
    $isMikroCatatan = \Smt\Masterweb\Helpers\Smt::isKesmasMikroLab($labContext);
    $catatanHasilValue = old(
        'catatan_hasil',
        \Smt\Masterweb\Helpers\Smt::resolveCatatanHasilKesmasFormValue($sample, $labContext)
    );
    $defaultCatatanKesmas = \Smt\Masterweb\Helpers\Smt::defaultCatatanHasilKesmas($isMikroCatatan);
@endphp

<div class="card border-0 mb-4 catatan-hasil-section" style="background-color: #e3f2fd;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-sticky-note mr-2"></i>
                Catatan
            </h5>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2 mt-md-0" id="btn-reset-catatan-hasil"
                title="Kembalikan ke teks default">
                <i class="fa fa-undo"></i> Reset Default
            </button>
        </div>

        <div class="form-group mb-0">
            <label for="catatan_hasil" class="font-weight-bold text-muted" style="font-size: 12px;">
                Teks catatan pada footer laporan hasil uji
            </label>
            <textarea class="form-control shadow-sm" id="catatan_hasil" name="catatan_hasil" rows="5"
                placeholder="Catatan laporan...">{{ $catatanHasilValue }}</textarea>
            <small class="form-text text-muted mt-2 mb-0">
                Terisi otomatis dengan catatan standar. Bisa diubah per sampel dan akan tampil pada cetak hasil.
            </small>
        </div>
    </div>
</div>

<script>
(function () {
    var defaultCatatan = {!! json_encode($defaultCatatanKesmas) !!};
    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest ? e.target.closest('#btn-reset-catatan-hasil') : null;
        if (!btn) return;
        var el = document.getElementById('catatan_hasil');
        if (el) el.value = defaultCatatan;
    });
})();
</script>
