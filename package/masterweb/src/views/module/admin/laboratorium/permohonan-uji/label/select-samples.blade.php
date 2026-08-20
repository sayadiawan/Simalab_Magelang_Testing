@extends('masterweb::template.admin.layout')

@section('title')
    Pilih Sampel untuk Cetak Label
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('elits-permohonan-uji.index') }}">Permohonan
                                        Uji</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Pilih Sampel untuk Cetak
                                        Label</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="card-title">Label sampel — {{ $permohonan_uji->customer->name_customer ?? 'Permohonan Uji' }}</h4>
                    @if ($permohonan_uji->customer)
                        <p class="text-muted">Pelanggan: {{ $permohonan_uji->customer->name_customer }}</p>
                    @endif
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if ($samples->count() > 0)
                <form id="form-select-samples" method="GET" action="{{ route('elits-label-permohonan-uji.print') }}"
                    target="_blank">
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-sm btn-primary" id="select-all">Pilih Semua</button>
                            <button type="button" class="btn btn-sm btn-secondary" id="deselect-all">Batal Pilih
                                Semua</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">
                                        <input type="checkbox" id="check-all">
                                    </th>
                                    <th>No</th>
                                    <th>Kode Sampel</th>
                                    <th>Jenis Sampel</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($samples as $index => $sample)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="samples[]" value="{{ $sample->id_samples }}"
                                                class="sample-checkbox">
                                        </td>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</td>
                                        <td>{{ $sample->sampleType->name_sample_type ?? '-' }}</td>
                                        <td>{{ $sample->date_sending ? \Carbon\Carbon::parse($sample->date_sending)->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-icon-text" id="btn-print" disabled>
                                <i class="fa fa-print"></i> Cetak Label Terpilih
                            </button>
                            <a href="{{ route('elits-permohonan-uji.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </div>
                </form>
            @else
                <div class="alert alert-warning">
                    Tidak ada sampel untuk permohonan uji ini.
                </div>
                <a href="{{ route('elits-permohonan-uji.index') }}" class="btn btn-secondary">Kembali</a>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Check all checkbox
            $('#check-all').on('change', function() {
                $('.sample-checkbox').prop('checked', $(this).prop('checked'));
                updatePrintButton();
            });

            // Select all button
            $('#select-all').on('click', function() {
                $('.sample-checkbox').prop('checked', true);
                $('#check-all').prop('checked', true);
                updatePrintButton();
            });

            // Deselect all button
            $('#deselect-all').on('click', function() {
                $('.sample-checkbox').prop('checked', false);
                $('#check-all').prop('checked', false);
                updatePrintButton();
            });

            // Individual checkbox change
            $('.sample-checkbox').on('change', function() {
                updateCheckAll();
                updatePrintButton();
            });

            // Update check all checkbox
            function updateCheckAll() {
                var total = $('.sample-checkbox').length;
                var checked = $('.sample-checkbox:checked').length;
                $('#check-all').prop('checked', total === checked);
            }

            // Update print button state
            function updatePrintButton() {
                var checked = $('.sample-checkbox:checked').length;
                if (checked > 0) {
                    $('#btn-print').prop('disabled', false);
                } else {
                    $('#btn-print').prop('disabled', true);
                }
            }

            // Form submit
            $('#form-select-samples').on('submit', function(e) {
                var checked = $('.sample-checkbox:checked').length;
                if (checked === 0) {
                    e.preventDefault();
                    alert('Pilih minimal 1 sampel untuk dicetak!');
                    return false;
                }
                // Form will submit with samples[] array automatically
            });
        });
    </script>
@endsection
