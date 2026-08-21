@extends('masterweb::template.admin.layout')
@section('title')
    Paket Management
@endsection

@section('css')
    <style>
        .selected-parameters-container {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
            min-height: 150px;
            max-height: 400px;
            overflow-y: auto;
        }

        .selected-parameter-item {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            margin: 5px;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .selected-parameter-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .selected-parameter-item .remove-btn {
            background: rgba(255, 255, 255, 0.3);
            border: none;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: background 0.2s;
        }

        .selected-parameter-item .remove-btn:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .parameters-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }

        .parameters-count {
            font-weight: 600;
            color: #0b3a5c;
            font-size: 16px;
        }

        .clear-all-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .clear-all-btn:hover {
            background: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #dee2e6;
        }

        .select2-container {
            z-index: 9999;
        }

        .price-summary {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .price-summary h5 {
            color: white;
            margin-bottom: 15px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .price-row:last-child {
            border-bottom: none;
            font-size: 18px;
            font-weight: bold;
            padding-top: 15px;
            margin-top: 10px;
            border-top: 2px solid rgba(255, 255, 255, 0.4);
        }
    </style>
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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-packet') }}">Paket Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>edit</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">

            {{-- <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-packet.update', [$id])}}"
      method="POST"> --}}
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="name_packet">Nama Paket</label>
                <input type="text" class="form-control" id="name_packet" name="name_packet"
                    value="{{ $packet->name_packet }}" placeholder="Name Paket" required>
            </div>
            <div class="form-group">
                <label for="code_sampletype">
                    <i class="fas fa-list-check"></i> Parameter Pengujian
                </label>
                <p class="text-muted small">Pilih parameter dari dropdown di bawah ini. Parameter yang dipilih akan muncul
                    di bawah.</p>

                <select id="methodAttributes" name="methodAttributes" class="js-example-basic-multiple form-control"
                    style="width: 100%" required>
                    <option value="">Cari dan pilih parameter...</option>
                </select>

                <!-- Selected Parameters Display -->
                <div class="mt-3">
                    <div class="parameters-info">
                        <div class="parameters-count">
                            <i class="fas fa-check-circle"></i>
                            <span id="selected-count">0</span> Parameter Dipilih
                        </div>
                        <button type="button" class="clear-all-btn" id="clear-all-parameters" style="display: none;">
                            <i class="fas fa-trash"></i> Hapus Semua
                        </button>
                    </div>

                    <div class="selected-parameters-container" id="selected-parameters">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada parameter yang dipilih</p>
                            <small>Pilih parameter dari dropdown di atas</small>
                        </div>
                    </div>
                </div>

                <!-- Hidden sinkron tampilan (submit pakai array JS) -->
                <input type="hidden" id="selected-parameters-input" value="">
            </div>

            <div class="form-group">
                <label for="sample_type_id">Jenis Sample</label>
                <select id="sample_type_id" name="sample_type_id" class="js-customer-basic-multiple js-states form-control"
                    style="display:none; width: 100%;" required>
                    <option value="" selected disabled>Pilih Jenis Sampel</option>

                    @foreach ($sampletypes as $sampletype)
                        @if ($sampletype->id_sample_type == $packet->sample_type_id)
                            <option value="{{ $sampletype->id_sample_type }}" selected>{{ $sampletype->name_sample_type }}
                            </option>
                        @else
                            <option value="{{ $sampletype->id_sample_type }}">{{ $sampletype->name_sample_type }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group jenis_makanan"
                style="{{ isset($packet->jenis_makanan_id) ? 'display: block;' : 'display: none;' }}">
                <label for="jenis_makanan_id">Jenis Makanan</label>
                <select id="jenis_makanan_id" name="jenis_makanan_id"
                    class="js-customer-basic-multiple js-states form-control" style="display:none; width: 100%" required>
                    <option value="" selected disabled>Pilih Jenis Makanan</option>
                    @foreach ($all_jenis_makanan as $jenis_makanan)
                        @if ($jenis_makanan->id_jenis_makanan == $packet->jenis_makanan_id)
                            <option value="{{ $jenis_makanan->id_jenis_makanan }}" selected>
                                {{ $jenis_makanan->name_jenis_makanan }}
                            </option>
                        @else
                            <option value="{{ $jenis_makanan->id_jenis_makanan }}">
                                {{ $jenis_makanan->name_jenis_makanan }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>


            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="price_bahan_packet">
                            <i class="fas fa-flask"></i> Harga Bahan
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control" id="price_bahan_packet" name="price_bahan_packet"
                                value="{{ $packet->price_bahan_packet ?? 0 }}" placeholder="0" required min="0">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="price_sarana_packet">
                            <i class="fas fa-building"></i> Harga Sarana
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control" id="price_sarana_packet" name="price_sarana_packet"
                                value="{{ $packet->price_sarana_packet ?? 0 }}" placeholder="0" required min="0">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="price_jasa_packet">
                            <i class="fas fa-user-tie"></i> Harga Jasa
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control" id="price_jasa_packet" name="price_jasa_packet"
                                value="{{ $packet->price_jasa_packet ?? 0 }}" placeholder="0" required min="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Summary Card -->
            <div class="price-summary">
                <h5><i class="fas fa-calculator"></i> Ringkasan Harga</h5>
                <div class="price-row">
                    <span>Harga Bahan:</span>
                    <span id="display-price-bahan">Rp 0</span>
                </div>
                <div class="price-row">
                    <span>Harga Sarana:</span>
                    <span id="display-price-sarana">Rp 0</span>
                </div>
                <div class="price-row">
                    <span>Harga Jasa:</span>
                    <span id="display-price-jasa">Rp 0</span>
                </div>
                <div class="price-row">
                    <span><i class="fas fa-receipt"></i> Total:</span>
                    <span id="display-price-total">Rp 0</span>
                </div>
            </div>

            <input type="hidden" id="price_total_packet" name="price_total_packet"
                value="{{ $packet->price_total_packet ?? 0 }}">

            <button type="submit" class="btn btn-primary mr-2" id="submit">Simpan</button>
            <button onclick="goBack()" class="btn btn-light">Kembali</button>
            {{--
    </form> --}}
        </div>
    </div>

    <script>
        // Selected parameters storage
        let selectedParameters = {};

        function goBack() {
            window.history.back();
        }

        // Format number to Rupiah
        function formatRupiah(number) {
            return 'Rp ' + parseInt(number || 0).toLocaleString('id-ID');
        }

        // Update selected parameters display
        function updateSelectedParametersDisplay() {
            const container = $('#selected-parameters');
            const count = Object.keys(selectedParameters).length;

            $('#selected-count').text(count);

            if (count === 0) {
                container.html(`
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada parameter yang dipilih</p>
                    <small>Pilih parameter dari dropdown di atas</small>
                </div>
            `);
                $('#clear-all-parameters').hide();
            } else {
                let html = '';
                for (const [id, name] of Object.entries(selectedParameters)) {
                    html += `
                    <div class="selected-parameter-item" data-id="${id}">
                        <span>${name}</span>
                        <button type="button" class="remove-btn" onclick="removeParameter('${id}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                }
                container.html(html);
                $('#clear-all-parameters').show();
            }

            // Update hidden input
            $('#selected-parameters-input').val(Object.keys(selectedParameters).join(','));
        }

        // Remove parameter
        window.removeParameter = function(id) {
            delete selectedParameters[id];
            updateSelectedParametersDisplay();
        };

        // Clear all parameters
        $('#clear-all-parameters').click(function() {
            if (confirm('Apakah Anda yakin ingin menghapus semua parameter?')) {
                selectedParameters = {};
                updateSelectedParametersDisplay();
                $('#methodAttributes').val(null).trigger('change');
            }
        });

        // Calculate and update price total
        function pricetotal() {
            const bahan = parseInt($('#price_bahan_packet').val() || 0);
            const jasa = parseInt($('#price_jasa_packet').val() || 0);
            const sarana = parseInt($('#price_sarana_packet').val() || 0);
            const total = bahan + jasa + sarana;

            $('#price_total_packet').val(total);

            // Update display
            $('#display-price-bahan').text(formatRupiah(bahan));
            $('#display-price-jasa').text(formatRupiah(jasa));
            $('#display-price-sarana').text(formatRupiah(sarana));
            $('#display-price-total').text(formatRupiah(total));
        }

        // Price input handlers
        $('#price_bahan_packet, #price_jasa_packet, #price_sarana_packet').on('keyup change', function() {
            pricetotal();
        });

        // Submit handler
        $("#submit").click(function() {
            const methodAttributesArray = Object.keys(selectedParameters);

            if (methodAttributesArray.length === 0) {
                alert('Harap pilih minimal satu parameter!');
                return;
            }

            const name_packet = $("#name_packet").val();
            const sample_type_id = $("#sample_type_id").val();
            const bahan = parseInt($("#price_bahan_packet").val(), 10) || 0;
            const jasa = parseInt($("#price_jasa_packet").val(), 10) || 0;
            const sarana = parseInt($("#price_sarana_packet").val(), 10) || 0;
            const price_bahan_packet = String(bahan);
            const price_jasa_packet = String(jasa);
            const price_sarana_packet = String(sarana);
            const jenis_makanan_id = $("#jenis_makanan_id").val();
            let price_total_packet = $("#price_total_packet").val();
            if (price_total_packet === '' || price_total_packet === null) {
                price_total_packet = String(bahan + jasa + sarana);
            }

            let _token = "{{ csrf_token() }}";

            var url = "{{ route('elits-packet.update', ['#']) }}";
            url = url.replace('#', '{{ $id }}');

            $.ajax({
                url: url,
                type: "PUT",
                data: {
                    name_packet: name_packet,
                    methodAttributes: methodAttributesArray,
                    sample_type_id: sample_type_id,
                    price_bahan_packet: price_bahan_packet,
                    price_jasa_packet: price_jasa_packet,
                    jenis_makanan_id: jenis_makanan_id,
                    price_sarana_packet: price_sarana_packet,
                    price_total_packet: price_total_packet,
                    id: "id",
                    _token: _token
                },
                success: function(response) {
                    swal({
                        title: "Berhasil!",
                        text: "Paket berhasil diperbarui",
                        icon: "success"
                    }).then(function() {
                        window.location.href = "{{ route('elits-packet.index') }}";
                    });
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    var msg = (XMLHttpRequest.responseJSON && XMLHttpRequest.responseJSON.message) ? XMLHttpRequest.responseJSON.message : "Terjadi kesalahan";
                    if (XMLHttpRequest.responseJSON && XMLHttpRequest.responseJSON.errors) {
                        var lines = [];
                        $.each(XMLHttpRequest.responseJSON.errors, function(k, v) {
                            if ($.isArray(v)) {
                                lines.push(v.join(" "));
                            } else {
                                lines.push(String(v));
                            }
                        });
                        if (lines.length) {
                            msg = lines.join("\n");
                        }
                    }
                    swal({
                        title: "Error!",
                        text: msg,
                        icon: "error"
                    });
                }
            });
        });

        $(document).ready(function() {
            pricetotal();
            $.fn.select2.defaults.set("theme", "classic");

            $('#jenis_makanan_id').select2({
                placeholder: "Pilih Jenis Makanan",
                allowClear: true
            });

            $('#sample_type_id').select2({
                    placeholder: "Pilih Jenis Sampel",
                    allowClear: true
                })
                .on('change', function(e) {
                    var getID = $(this).select2('data');
                    if (getID[0].text.includes("Makanan")) {
                        $(".jenis_makanan").css("display", "block")
                    } else {
                        $(".jenis_makanan").css("display", "none")
                    }
                });

            // Initialize Select2 for parameters with AJAX
            $('#methodAttributes').select2({
                placeholder: "Cari dan pilih parameter...",
                allowClear: true,
                ajax: {
                    url: "{{ url('/api/method/') }}",
                    method: "post",
                    dataType: 'json',
                    delay: 250,
                    params: {
                        contentType: "application/json;",
                    },
                    data: function(term) {
                        return {
                            term: term.term || '',
                            page: term.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results,
                            pagination: data.pagination
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;

                // Check for duplicate
                if (selectedParameters[data.id]) {
                    swal({
                        title: "Duplikat!",
                        text: "Parameter '" + data.text + "' sudah dipilih",
                        icon: "warning"
                    });
                    $(this).val(null).trigger('change');
                    return;
                }

                // Add to selected parameters
                selectedParameters[data.id] = data.text;
                updateSelectedParametersDisplay();

                // Clear selection
                $(this).val(null).trigger('change');
            });

            // Pre-populate existing parameters
            @foreach ($packet_details as $packet_detail)
                selectedParameters['{{ $packet_detail->id_method }}'] = '{{ $packet_detail->params_method }}';
            @endforeach
            updateSelectedParametersDisplay();

            // Initialize price calculation
            pricetotal();
        });
    </script>
@endsection
