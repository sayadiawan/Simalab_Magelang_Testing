@extends('masterweb::template.admin.layout')
@section('title')
    Add Data Jenis Sarana
@endsection

@section('css')
    <style>
        .parameter-section {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .parameter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .parameter-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .parameter-count {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .sortable-container {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 15px;
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
        }

        .sortable-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 12px;
            cursor: move;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sortable-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .sortable-ghost {
            opacity: 0.4;
            background: #e9ecef !important;
        }

        .sortable-drag {
            opacity: 0.8;
            cursor: grabbing !important;
        }

        .drag-handle {
            cursor: grab;
            padding: 0 10px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 18px;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .remove-item {
            cursor: pointer;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .remove-item:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .order-badge {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            min-width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
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

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .clear-all-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .clear-all-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .info-text {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #1565c0;
        }

        .select2-container--classic .select2-selection--multiple {
            border-radius: 8px !important;
            border: 2px solid #e0e0e0 !important;
        }

        .select2-container--classic .select2-selection--multiple:focus {
            border-color: #667eea !important;
        }

        /* Search Parameter Styles */
        .search-parameter-wrapper {
            position: relative;
            margin-bottom: 15px;
        }

        .search-input-container {
            position: relative;
        }

        .search-parameter-input {
            padding-left: 40px;
            padding-right: 40px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            font-size: 15px;
            transition: all 0.3s;
        }

        .search-parameter-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }

        .search-loading {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .search-result-item {
            padding: 12px 15px;
            cursor: pointer !important;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            user-select: none;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .search-result-item:hover .add-icon {
            color: white;
        }

        .search-result-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8f9fa;
        }

        .search-result-item.disabled:hover {
            background: #f8f9fa;
            color: inherit;
        }

        .add-icon {
            color: #667eea;
            font-size: 18px;
            transition: all 0.2s;
        }

        .search-no-results {
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }

        .search-no-results i {
            font-size: 32px;
            margin-bottom: 10px;
            color: #dee2e6;
        }

        .selected-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
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
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-sampletypes') }}">Data Jenis Sarana</a>
                                </li>

                                <li class="breadcrumb-item active" aria-current="page">
                                    <span>create</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <form enctype="multipart/form-data" class="forms-sample" id="form"
                action="{{ route('elits-sampletypes.store') }}" method="POST">

                @csrf

                <div class="form-group">
                    <label for="name_sampletype">Nama Jenis Sarana<span style="color: red">*</span></label>
                    <input type="text" class="form-control" id="name_sampletype" name="name_sampletype"
                        placeholder="Name Sample Type">
                </div>

                <div class="form-group">
                    <label for="code_sample_type">Kode Jenis Sarana</label>
                    <input type="text" class="form-control" id="code_sample_type" name="code_sample_type"
                        placeholder="Code Sample Type">
                </div>

                <!-- Parameter Wajib Section -->
                <div class="parameter-section">
                    <div class="parameter-header">
                        <div class="parameter-title">
                            <i class="fas fa-star text-warning"></i>
                            <span>Parameter Wajib</span>
                            <span class="text-danger">*</span>
                        </div>
                        <div class="action-buttons">
                            <span class="parameter-count" id="method-count">
                                <i class="fas fa-list"></i> 0 Parameter
                            </span>
                            <button type="button" class="clear-all-btn" id="clear-method" style="display: none;">
                                <i class="fas fa-trash"></i> Hapus Semua
                            </button>
                        </div>
                    </div>

                    <div class="info-text">
                        <i class="fas fa-info-circle"></i>
                        Cari parameter dengan mengetik, kemudian klik untuk menambahkan. Drag & drop untuk mengatur urutan
                    </div>

                    <div class="search-parameter-wrapper">
                        <div class="search-input-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchMethodAttributes" class="form-control search-parameter-input"
                                placeholder="🔍 Ketik untuk mencari parameter wajib..." autocomplete="off">
                            <div class="search-loading" id="searchMethodLoading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="search-results" id="searchMethodResults" style="display: none;"></div>
                    </div>

                    <select id="methodAttributes" name="methodAttributes[]" class="form-control" style="display: none;"
                        multiple="multiple">
                    </select>

                    <div class="sortable-container mt-3" id="sortableMethodList">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada parameter wajib yang dipilih</p>
                            <small>Cari dan klik parameter di atas untuk menambahkan</small>
                        </div>
                    </div>
                </div>

                <!-- Parameter Tambahan Section -->
                <div class="parameter-section">
                    <div class="parameter-header">
                        <div class="parameter-title">
                            <i class="fas fa-plus-circle text-success"></i>
                            <span>Parameter Tambahan</span>
                        </div>
                        <div class="action-buttons">
                            <span class="parameter-count" id="method-plus-count">
                                <i class="fas fa-list"></i> 0 Parameter
                            </span>
                            <button type="button" class="clear-all-btn" id="clear-method-plus" style="display: none;">
                                <i class="fas fa-trash"></i> Hapus Semua
                            </button>
                        </div>
                    </div>

                    <div class="info-text">
                        <i class="fas fa-info-circle"></i>
                        Parameter tambahan bersifat opsional. Cari dan klik untuk menambahkan, drag & drop untuk mengatur
                        urutan
                    </div>

                    <div class="search-parameter-wrapper">
                        <div class="search-input-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchMethodPlusAttributes"
                                class="form-control search-parameter-input"
                                placeholder="🔍 Ketik untuk mencari parameter tambahan..." autocomplete="off">
                            <div class="search-loading" id="searchMethodPlusLoading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="search-results" id="searchMethodPlusResults" style="display: none;"></div>
                    </div>

                    <select id="methodPlusAttributes" name="methodPlusAttributes[]" class="form-control"
                        style="display: none;" multiple="multiple"></select>

                    <div class="sortable-container mt-3" id="sortableMethodPlusList">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada parameter tambahan yang dipilih</p>
                            <small>Cari dan klik parameter di atas untuk menambahkan</small>
                        </div>
                    </div>
                </div>
            </form>
            <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
            <button type="button" onclick="goBack()" class="btn btn-light">Kembali</button>
        </div>
    </div>


    <!-- SortableJS CDN -->
    <script src="{{ asset('assets/admin/cdn-local/js/sortable.min.js') }}"></script>

    <script>
        function goBack() {
            window.history.back();
        }

        $(document).ready(function() {

            // Custom Search Variables
            let searchTimeout = null;
            const searchDelay = 300;

            // Initialize Sortable for Parameter Wajib
            var sortableMethod = new Sortable(document.getElementById('sortableMethodList'), {
                animation: 200,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function() {
                    updateOrderBadges('sortableMethodList');
                }
            });

            // Initialize Sortable for Parameter Tambahan
            var sortableMethodPlus = new Sortable(document.getElementById('sortableMethodPlusList'), {
                animation: 200,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function() {
                    updateOrderBadges('sortableMethodPlusList');
                }
            });

            // Update counter and clear button visibility
            function updateCounter(listId) {
                const count = $('#' + listId + ' .sortable-item').length;
                const countId = listId === 'sortableMethodList' ? '#method-count' : '#method-plus-count';
                const clearId = listId === 'sortableMethodList' ? '#clear-method' : '#clear-method-plus';

                $(countId).html(`<i class="fas fa-list"></i> ${count} Parameter`);

                if (count > 0) {
                    $(clearId).show();
                } else {
                    $(clearId).hide();
                }
            }

            // Update empty state
            function updateEmptyState(listId) {
                const count = $('#' + listId + ' .sortable-item').length;
                const emptyState = $('#' + listId + ' .empty-state');

                if (count === 0) {
                    const message = listId === 'sortableMethodList' ?
                        'Belum ada parameter wajib yang dipilih' :
                        'Belum ada parameter tambahan yang dipilih';

                    if (emptyState.length === 0) {
                        $('#' + listId).append(`
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>${message}</p>
                                <small>Cari dan klik parameter di atas untuk menambahkan</small>
                            </div>
                        `);
                    }
                } else {
                    emptyState.remove();
                }
            }

            // Function to update order badges
            function updateOrderBadges(listId) {
                $('#' + listId + ' .order-badge').each(function(index) {
                    $(this).text(index + 1);
                });
            }

            // Function to add item to sortable list
            function addToSortableList(listId, itemId, itemText) {
                // Check for duplicate
                if ($('#' + listId + ' .sortable-item[data-id="' + itemId + '"]').length > 0) {
                    swal({
                        title: "Duplikat!",
                        text: "Parameter '" + itemText + "' sudah dipilih",
                        icon: "warning"
                    });
                    return false;
                }

                var orderNum = $('#' + listId + ' .sortable-item').length + 1;
                var listItem = `
          <div class="sortable-item d-flex justify-content-between align-items-center" data-id="${itemId}">
            <div class="d-flex align-items-center flex-grow-1">
              <span class="drag-handle mr-2">
                <i class="fa fa-grip-vertical"></i>
              </span>
              <div class="order-badge mr-2">${orderNum}</div>
              <span>${itemText}</span>
            </div>
            <span class="remove-item" onclick="removeItem(this, '${listId}', '${itemId}')">
              <i class="fa fa-times"></i>
            </span>
          </div>
        `;
                $('#' + listId).append(listItem);
                updateCounter(listId);
                updateEmptyState(listId);
                return true;
            }

            // Function to remove item from sortable list
            window.removeItem = function(element, listId, itemId) {
                $(element).closest('.sortable-item').remove();
                updateOrderBadges(listId);
                updateCounter(listId);
                updateEmptyState(listId);

                // Also remove from select2
                var selectId = listId === 'sortableMethodList' ? '#methodAttributes' : '#methodPlusAttributes';
                var selectedValues = $(selectId).val() || [];
                var index = selectedValues.indexOf(itemId.toString());
                if (index > -1) {
                    selectedValues.splice(index, 1);
                    $(selectId).val(selectedValues).trigger('change');
                }
            }

            // Clear all buttons
            $('#clear-method').on('click', function() {
                if (confirm('Apakah Anda yakin ingin menghapus semua parameter wajib?')) {
                    $('#sortableMethodList .sortable-item').remove();
                    $('#methodAttributes').val(null).trigger('change');
                    updateCounter('sortableMethodList');
                    updateEmptyState('sortableMethodList');
                }
            });

            $('#clear-method-plus').on('click', function() {
                if (confirm('Apakah Anda yakin ingin menghapus semua parameter tambahan?')) {
                    $('#sortableMethodPlusList .sortable-item').remove();
                    $('#methodPlusAttributes').val(null).trigger('change');
                    updateCounter('sortableMethodPlusList');
                    updateEmptyState('sortableMethodPlusList');
                }
            });

            // Custom Search Function
            function searchParameters(searchTerm, resultsContainerId, loadingId, listId, selectId) {
                const $results = $('#' + resultsContainerId);
                const $loading = $('#' + loadingId);

                if (!searchTerm || searchTerm.length < 2) {
                    $results.hide();
                    return;
                }

                $loading.show();

                $.ajax({
                    url: "{{ url('/api/method/') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        term: searchTerm,
                        page: 1
                    },
                    success: function(response) {
                        $loading.hide();

                        if (response.results && response.results.length > 0) {
                            let html = '';
                            response.results.forEach(function(item) {
                                const isSelected = $('#' + listId +
                                    ' .sortable-item[data-id="' + item.id + '"]').length > 0;
                                const disabledClass = isSelected ? 'disabled' : '';
                                const badge = isSelected ?
                                    '<span class="selected-badge">✓ Dipilih</span>' :
                                    '<i class="fas fa-plus-circle add-icon"></i>';

                                html += `
                                    <div class="search-result-item ${disabledClass}" data-id="${item.id}" data-text="${item.text}">
                                        <span>${item.text}</span>
                                        ${badge}
                                    </div>
                                `;
                            });
                            $results.html(html).show();
                        } else {
                            $results.html(`
                                <div class="search-no-results">
                                    <i class="fas fa-search"></i>
                                    <p>Tidak ada parameter ditemukan</p>
                                    <small>Coba kata kunci lain</small>
                                </div>
                            `).show();
                        }
                    },
                    error: function() {
                        $loading.hide();
                        $results.html(`
                            <div class="search-no-results">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>Terjadi kesalahan</p>
                                <small>Silakan coba lagi</small>
                            </div>
                        `).show();
                    }
                });
            }

            // Search Method Attributes (Parameter Wajib)
            $('#searchMethodAttributes').on('input', function() {
                const searchTerm = $(this).val();

                clearTimeout(searchTimeout);

                if (searchTerm.length < 2) {
                    $('#searchMethodResults').hide();
                    $('#searchMethodLoading').hide();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    searchParameters(
                        searchTerm,
                        'searchMethodResults',
                        'searchMethodLoading',
                        'sortableMethodList',
                        'methodAttributes'
                    );
                }, searchDelay);
            });

            // Click on search result (Method Attributes)
            $(document).on('click', '#searchMethodResults .search-result-item:not(.disabled)', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Search result clicked!'); // Debug

                const itemId = $(this).data('id');
                const itemText = $(this).data('text');

                console.log('Item ID:', itemId, 'Text:', itemText); // Debug

                const added = addToSortableList('sortableMethodList', itemId, itemText);

                console.log('Added:', added); // Debug

                if (added) {
                    // Update select hidden
                    const currentVals = $('#methodAttributes').val() || [];
                    currentVals.push(itemId.toString());
                    $('#methodAttributes').val(currentVals);

                    // Clear search
                    $('#searchMethodAttributes').val('');
                    $('#searchMethodResults').hide();

                    // Show success feedback
                    $(this).addClass('disabled').find('.add-icon')
                        .removeClass('fa-plus-circle').addClass('fa-check')
                        .parent().append('<span class="selected-badge ml-2">✓ Ditambahkan</span>');

                    setTimeout(function() {
                        $('#searchMethodResults').hide();
                    }, 500);
                }
            });

            // Search Method Plus Attributes (Parameter Tambahan)
            $('#searchMethodPlusAttributes').on('input', function() {
                const searchTerm = $(this).val();

                clearTimeout(searchTimeout);

                if (searchTerm.length < 2) {
                    $('#searchMethodPlusResults').hide();
                    $('#searchMethodPlusLoading').hide();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    searchParameters(
                        searchTerm,
                        'searchMethodPlusResults',
                        'searchMethodPlusLoading',
                        'sortableMethodPlusList',
                        'methodPlusAttributes'
                    );
                }, searchDelay);
            });

            // Click on search result (Method Plus Attributes)
            $(document).on('click', '#searchMethodPlusResults .search-result-item:not(.disabled)', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Search Plus result clicked!'); // Debug

                const itemId = $(this).data('id');
                const itemText = $(this).data('text');

                console.log('Item ID:', itemId, 'Text:', itemText); // Debug

                const added = addToSortableList('sortableMethodPlusList', itemId, itemText);

                console.log('Added:', added); // Debug

                if (added) {
                    // Update select hidden
                    const currentVals = $('#methodPlusAttributes').val() || [];
                    currentVals.push(itemId.toString());
                    $('#methodPlusAttributes').val(currentVals);

                    // Clear search
                    $('#searchMethodPlusAttributes').val('');
                    $('#searchMethodPlusResults').hide();

                    // Show success feedback
                    $(this).addClass('disabled').find('.add-icon')
                        .removeClass('fa-plus-circle').addClass('fa-check')
                        .parent().append('<span class="selected-badge ml-2">✓ Ditambahkan</span>');

                    setTimeout(function() {
                        $('#searchMethodPlusResults').hide();
                    }, 500);
                }
            });

            // Close search results when clicking outside
            $(document).on('click', function(e) {
                const $target = $(e.target);
                // Don't close if clicking inside search wrapper or on search results
                if (!$target.closest('.search-parameter-wrapper').length &&
                    !$target.closest('.search-results').length) {
                    $('.search-results').hide();
                }
            });

            // Old Select2 code removed - now using custom search interface above

            $('.btn-simpan').on('click', function() {
                // Collect method IDs from sortable list and update select element
                var methodAttributeIds = [];
                var orderedMethodIds = [];
                $('#sortableMethodList .sortable-item').each(function(index) {
                    var methodId = $(this).data('id');
                    methodAttributeIds.push(methodId.toString());
                    orderedMethodIds.push({
                        id: methodId.toString(),
                        order: index + 1
                    });
                });

                // Validate that at least one required parameter is selected
                if (methodAttributeIds.length === 0) {
                    swal({
                        title: "Error!",
                        text: "Parameter wajib tidak boleh kosong!",
                        icon: "warning"
                    });
                    return false;
                }

                // Clear existing options and add new ones
                $('#methodAttributes').empty();
                methodAttributeIds.forEach(function(id) {
                    $('#methodAttributes').append('<option value="' + id + '" selected>' + id +
                        '</option>');
                });

                // Collect method plus IDs from sortable list and update select element
                var methodPlusAttributeIds = [];
                var orderedMethodPlusIds = [];
                $('#sortableMethodPlusList .sortable-item').each(function(index) {
                    var methodId = $(this).data('id');
                    methodPlusAttributeIds.push(methodId.toString());
                    orderedMethodPlusIds.push({
                        id: methodId.toString(),
                        order: index + 1
                    });
                });

                // Clear existing options and add new ones for methodPlusAttributes
                $('#methodPlusAttributes').empty();
                if (methodPlusAttributeIds.length > 0) {
                    methodPlusAttributeIds.forEach(function(id) {
                        $('#methodPlusAttributes').append('<option value="' + id + '" selected>' +
                            id + '</option>');
                    });
                }

                // Add hidden inputs for order
                $('#form input[name="methodOrder"]').remove();
                $('#form input[name="methodPlusOrder"]').remove();

                $('#form').append('<input type="hidden" name="methodOrder" value=\'' + JSON.stringify(
                    orderedMethodIds) + '\'>');
                $('#form').append('<input type="hidden" name="methodPlusOrder" value=\'' + JSON.stringify(
                    orderedMethodPlusIds) + '\'>');

                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    document.location = '/elits-sampletypes';
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    console.log(value);
                                    pesan += value + '. <br>';
                                    wrapper.innerHTML = pesan;
                                });

                                swal({
                                    title: "Error!",
                                    content: wrapper,
                                    icon: "warning"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: response.pesan,
                                    icon: "warning"
                                });
                            }
                        }
                    },
                    error: function() {
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                })
            })
        });
    </script>
@endsection
