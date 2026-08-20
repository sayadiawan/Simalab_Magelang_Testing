{{--
  Keterangan acuan metode untuk halaman baca-hasil kesmas.

  Params:
    - $sample
    - $laboratoriummethods
--}}
@php
    $acuanData = \Smt\Masterweb\Helpers\Smt::collectAcuanBakuMutuFromLaboratoriumMethods($laboratoriummethods ?? []);
    $keteranganMetodeValue = old(
        'keterangan_metode',
        \Smt\Masterweb\Helpers\Smt::resolveKeteranganMetodeBacaHasilFormValue(
            $sample,
            $laboratoriummethods ?? []
        )
    );
    $keteranganMetodeDefault = \Smt\Masterweb\Helpers\Smt::composeKeteranganMetodeDefault(
        $acuanData['unique_acuan'] ?? []
    );
@endphp

<div class="card border-0 mb-4 keterangan-metode-section" style="background-color: #e8f5e9;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h5 class="mb-0 font-weight-bold text-success">
                <i class="fa fa-book mr-2"></i>
                Keterangan Acuan Metode
            </h5>
            <button type="button" class="btn btn-sm btn-outline-success mt-2 mt-md-0" data-toggle="modal" data-target="#keteranganMetodeBacaHasilModal">
                <i class="fa fa-edit"></i> Ubah / Lihat Acuan
            </button>
        </div>

        <div class="keterangan-metode-display border rounded p-3 bg-white">
            <div class="d-flex flex-wrap">
                <div class="font-weight-bold mr-2" style="min-width: 110px;">Keterangan</div>
                <div class="flex-grow-1">
                    <span class="mr-1">:</span>
                    <span id="keterangan_metode_display">{{ $keteranganMetodeValue !== '' ? $keteranganMetodeValue : '-' }}</span>
                </div>
            </div>
            <small class="form-text text-muted mt-2 mb-0">
                Diisi otomatis dari acuan baku mutu tiap parameter. Bisa disesuaikan manual dan akan tampil pada cetak hasil.
            </small>
        </div>

        <input type="hidden" name="keterangan_metode" id="keterangan_metode" value="{{ $keteranganMetodeValue }}">
    </div>
</div>

<style>
    #keteranganMetodeBacaHasilModal .modal-dialog.modal-body-scrollable {
        max-height: calc(100vh - 2rem);
        margin: 1rem auto;
    }

    #keteranganMetodeBacaHasilModal .modal-dialog.modal-body-scrollable .modal-content {
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    #keteranganMetodeBacaHasilModal .modal-dialog.modal-body-scrollable .modal-header,
    #keteranganMetodeBacaHasilModal .modal-dialog.modal-body-scrollable .modal-footer {
        flex-shrink: 0;
    }

    #keteranganMetodeBacaHasilModal .modal-dialog.modal-body-scrollable .modal-body {
        flex: 1 1 auto;
        overflow-y: auto;
        max-height: calc(100vh - 12rem);
        -webkit-overflow-scrolling: touch;
    }

    #keteranganMetodeBacaHasilModal .keterangan-metode-table-scroll {
        max-height: min(50vh, 420px);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    #keteranganMetodeBacaHasilModal .keterangan-metode-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
        box-shadow: 0 1px 0 #dee2e6;
    }
</style>

<div class="modal fade" id="keteranganMetodeBacaHasilModal" tabindex="-1" role="dialog" aria-labelledby="keteranganMetodeBacaHasilModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-body-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="keteranganMetodeBacaHasilModalLabel">
                    <i class="fa fa-book mr-2"></i>
                    Acuan Baku Mutu per Parameter
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive keterangan-metode-table-scroll mb-4">
                    <table class="table table-sm table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 22%;">Parameter</th>
                                <th style="width: 28%;">Metode</th>
                                <th>Acuan Baku Mutu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($acuanData['parameters'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['parameter'] }}</td>
                                    <td>{!! $row['metode'] !== '-' ? e($row['metode']) : '<span class="text-muted">-</span>' !!}</td>
                                    <td>
                                        @if (!empty($row['acuan']))
                                            {{ $row['acuan_text'] }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Tidak ada parameter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="form-group mb-2">
                    <label for="keterangan_metode_editor" class="font-weight-bold">Teks Keterangan (cetak hasil)</label>
                    <textarea id="keterangan_metode_editor" class="form-control" rows="4" placeholder="Contoh: Berdasarkan Peraturan Menteri Kesehatan Nomor 2 Tahun 2023">{{ $keteranganMetodeValue }}</textarea>
                </div>
                <small class="form-text text-muted">
                    Pada cetak hasil akan ditampilkan sebagai: <strong>Keterangan :</strong> [teks di bawah]
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnResetKeteranganMetode" data-default="{{ e($keteranganMetodeDefault) }}">
                    <i class="fa fa-sync"></i> Ambil dari Acuan
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSaveKeteranganMetode">
                    <i class="fa fa-check"></i> Terapkan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        function syncKeteranganMetodeDisplay(value) {
            var text = (value || '').trim();
            $('#keterangan_metode').val(text);
            $('#keterangan_metode_display').text(text !== '' ? text : '-');
        }

        $('#keteranganMetodeBacaHasilModal').on('show.bs.modal', function () {
            $('#keterangan_metode_editor').val($('#keterangan_metode').val());
        });

        $('#btnSaveKeteranganMetode').on('click', function () {
            syncKeteranganMetodeDisplay($('#keterangan_metode_editor').val());
            $('#keteranganMetodeBacaHasilModal').modal('hide');
        });

        $('#btnResetKeteranganMetode').on('click', function () {
            var defaultText = $(this).data('default') || '';
            $('#keterangan_metode_editor').val(defaultText);
        });
    })();
</script>
