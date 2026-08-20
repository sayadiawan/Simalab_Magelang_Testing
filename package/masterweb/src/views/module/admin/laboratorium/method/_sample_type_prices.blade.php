{{-- Harga per jenis sampel (Kesmas / non-klinik); laboratorium tidak membedakan harga --}}
<div class="card mb-4 border-info">
    <div class="card-body">
        <h5 class="card-title text-info">Harga per jenis sampel (opsional)</h5>
        <p class="text-muted small mb-2">
            Untuk permohonan Kesmas / non-klinik: isi harga per jenis sampel bila berbeda dari harga total di atas.
            Kosong berarti memakai <strong>harga total</strong> (<code>ms_method.price_total_method</code>) untuk jenis sampel tersebut.
        </p>
        <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:55%">Jenis sampel</th>
                        <th>Harga (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (($sampletypes ?? collect()) as $st)
                        <tr>
                            <td><small>{{ $st->name_sample_type }}</small></td>
                            <td>
                                <input type="number" min="0" step="1" class="form-control form-control-sm"
                                    name="sample_type_price[{{ $st->id_sample_type }}]"
                                    value="{{ $sample_type_prices[$st->id_sample_type] ?? '' }}"
                                    placeholder="— default —">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
