@extends('masterweb::template.admin.layout')
@section('title')
  Penataan Layout Kategori Parameter
@endsection

@section('content')
<style>
  .layout-container {
    display: grid;
    grid-template-columns: 300px 1fr 350px;
    gap: 20px;
    height: calc(100vh - 200px);
  }
  
  .panel {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow-y: auto;
  }
  
  .panel-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #2d3748;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  /* Category List Styles */
  .category-list {
    min-height: 200px;
  }
  
  .category-card {
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    cursor: move;
    transition: all 0.2s;
  }
  
  .category-card:hover {
    background: #e9ecef;
    border-color: #0b3a5c;
  }
  
  .category-card.active {
    background: #e3f2fd;
    border-color: #2196f3;
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
  }
  
  .category-card.sortable-ghost {
    opacity: 0.4;
  }
  
  .category-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
  }
  
  .category-code {
    background: #0b3a5c;
    color: white;
    border-radius: 4px;
    padding: 4px 10px;
    font-weight: 600;
    font-size: 14px;
    min-width: 40px;
    text-align: center;
  }
  
  .category-name-input {
    border: none;
    background: transparent;
    font-weight: 500;
    flex: 1;
    padding: 4px;
    border-radius: 4px;
  }
  
  .category-name-input:focus {
    outline: none;
    background: white;
    border: 1px solid #0b3a5c;
  }
  
  .category-meta {
    display: flex;
    gap: 10px;
    font-size: 12px;
    color: #718096;
    margin-left: 50px;
  }
  
  .category-meta select {
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 12px;
  }
  
  /* Parameter Item Styles */
  .parameter-drop-zone {
    min-height: 100px;
    background: #f8f9fa;
    border: 2px dashed #cbd5e0;
    border-radius: 6px;
    padding: 15px;
    margin-top: 15px;
  }
  
  .parameter-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 8px;
    cursor: move;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
  }
  
  .parameter-item:hover {
    background: #f7fafc;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }
  
  .parameter-item.sortable-ghost {
    opacity: 0.4;
  }
  
  .parameter-drag-handle {
    color: #a0aec0;
    font-size: 16px;
  }
  
  .parameter-name {
    flex: 1;
    font-size: 14px;
    color: #2d3748;
  }
  
  .parameter-price {
    font-size: 12px;
    color: #718096;
  }
  
  .parameter-remove {
    color: #e53e3e;
    cursor: pointer;
    font-size: 16px;
    opacity: 0;
    transition: opacity 0.2s;
  }
  
  .parameter-item:hover .parameter-remove {
    opacity: 1;
  }
  
  /* Available Parameters Styles */
  .available-parameters {
    max-height: 600px;
    overflow-y: auto;
  }
  
  .available-parameter {
    background: #f8f9fa;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 8px;
    cursor: move;
    transition: all 0.2s;
  }
  
  .available-parameter:hover {
    background: #e9ecef;
    border-color: #0b3a5c;
  }
  
  .available-parameter.sortable-ghost {
    opacity: 0.4;
  }
  
  /* Buttons */
  .btn-add-category {
    width: 100%;
    background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
    color: white;
    border: none;
    padding: 10px;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
  }
  
  .btn-add-category:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(11, 58, 92, 0.4);
  }
  
  .btn-save {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #10b981;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    z-index: 1000;
    transition: all 0.2s;
  }
  
  .btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
  }
  
  .empty-state {
    text-align: center;
    padding: 40px;
    color: #a0aec0;
  }
  
  /* Grid Builder Styles */
  .grid-config {
    background: #f7fafc;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
  }
  
  .grid-config label {
    font-weight: 600;
    color: #4a5568;
    margin-right: 5px;
  }
  
  .grid-config input {
    width: 60px;
    padding: 6px 10px;
    border: 1px solid #cbd5e0;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
  }
  
  .grid-builder {
    display: grid;
    gap: 8px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    min-height: 200px;
  }
  
  .grid-cell {
    background: white;
    border: 2px dashed #cbd5e0;
    border-radius: 6px;
    padding: 12px;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    position: relative;
    cursor: pointer;
  }
  
  .grid-cell:hover {
    border-color: #0b3a5c;
    background: #e7f4f2;
  }
  
  .grid-cell.occupied {
    background: #e6f3ff;
    border: 2px solid #4299e1;
    border-style: solid;
  }
  
  .grid-cell.drag-over {
    background: #fff5e6;
    border-color: #f6ad55;
    transform: scale(1.02);
  }
  
  .grid-cell-content {
    text-align: center;
    width: 100%;
  }
  
  .grid-cell-label {
    font-size: 13px;
    color: #2d3748;
    font-weight: 500;
    word-break: break-word;
  }
  
  .grid-cell-price {
    font-size: 11px;
    color: #718096;
    margin-top: 3px;
  }
  
  .grid-cell-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #fc8181;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
  }
  
  .grid-cell.occupied:hover .grid-cell-remove {
    display: flex;
  }
  
  .grid-cell-empty {
    color: #a0aec0;
    font-size: 12px;
  }
  
  .grid-preview-info {
    background: #edf2f7;
    padding: 10px 12px;
    border-radius: 4px;
    font-size: 13px;
    color: #4a5568;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .save-grid-btn {
    background: #48bb78;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
  }
  
  .save-grid-btn:hover {
    background: #38a169;
  }
  
  .save-grid-btn:disabled {
    background: #cbd5e0;
    cursor: not-allowed;
  }
  
  .save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    background: #10b981;
    color: white;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: none;
    z-index: 9999;
    animation: slideIn 0.3s;
  }
  
  @keyframes slideIn {
    from {
      transform: translateX(400px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  .search-box {
    margin-bottom: 15px;
    position: relative;
  }
  
  .search-box input {
    width: 100%;
    padding: 8px 12px 8px 35px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
  }
  
  .search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
  }
</style>

<div class="row">
  <div class="col-12 grid-margin stretch-card">
    <div class="card">
      <div class="template-demo">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a>
            </li>
            <li class="breadcrumb-item">
              <a href="{{ url('/elits-parameter-paket-klinik') }}">Parameter Paket Klinik</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              <span>Penataan Layout Kategori</span>
            </li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white;">
        <h4 class="mb-0" style="color: white;">
          <i class="fa fa-th-large mr-2"></i>
          Penataan Layout Kategori Parameter
        </h4>
        <p class="mb-0 mt-2" style="font-size: 14px; opacity: 0.9;">
          Atur urutan kategori, posisi kolom, dan parameter yang ditampilkan pada halaman permohonan uji
        </p>
      </div>
      
      <div class="card-body">
        <div class="layout-container">
          <!-- Left Panel: Categories List -->
          <div class="panel">
            <div class="panel-title">
              Daftar Kategori
              <button type="button" class="btn btn-sm btn-primary" onclick="addCategory()">
                <i class="fa fa-plus"></i>
              </button>
            </div>
            
            <div id="categories-list" class="category-list">
              @foreach($categories as $category)
                <div class="category-card" 
                     data-id="{{ $category->id_param_category_layout }}" 
                     onclick="selectCategory(this)">
                  <div class="category-header">
                    <div class="category-code">{{ $category->category_code }}</div>
                    <input type="text" 
                           class="category-name-input" 
                           value="{{ $category->category_name }}"
                           onchange="updateCategoryName(this)"
                           onclick="event.stopPropagation()">
                  </div>
                  <div class="category-meta">
                    <label>Lebar:</label>
                    <select class="category-width" onchange="updateCategoriesOrder()" onclick="event.stopPropagation()">
                      <option value="3" {{ $category->column_width == 3 ? 'selected' : '' }}>col-3</option>
                      <option value="4" {{ $category->column_width == 4 ? 'selected' : '' }}>col-4</option>
                      <option value="6" {{ $category->column_width == 6 ? 'selected' : '' }}>col-6</option>
                      <option value="12" {{ $category->column_width == 12 ? 'selected' : '' }}>col-12</option>
                    </select>
                    <span>{{ count($category->categoryItems) }} item</span>
                  </div>
                  <div class="category-meta" style="margin-top: 5px;">
                    <label>Kolom Kosong:</label>
                    <select class="category-empty-position" onchange="updateCategoriesOrder()" onclick="event.stopPropagation()">
                      <option value="none" {{ ($category->empty_column_position ?? 'none') == 'none' ? 'selected' : '' }}>Tidak Ada</option>
                      <option value="left" {{ ($category->empty_column_position ?? 'none') == 'left' ? 'selected' : '' }}>Kiri</option>
                      <option value="right" {{ ($category->empty_column_position ?? 'none') == 'right' ? 'selected' : '' }}>Kanan</option>
                    </select>
                  </div>
                  <div class="category-meta" style="margin-top: 5px;">
                    <label>Grid:</label>
                    <input type="number" class="grid-rows" min="0" max="20" value="{{ $category->grid_rows ?? 0 }}" 
                           style="width: 50px; padding: 2px 5px;" 
                           placeholder="Baris" onclick="event.stopPropagation()"> 
                    x 
                    <input type="number" class="grid-columns" min="1" max="6" value="{{ $category->grid_columns ?? 3 }}" 
                           style="width: 50px; padding: 2px 5px;" 
                           placeholder="Kolom" onclick="event.stopPropagation()">
                    <small style="display: block; color: #718096; margin-top: 3px;">0 baris = auto</small>
                  </div>
                </div>
              @endforeach
            </div>
            
            <button type="button" class="btn-add-category mt-3" onclick="addCategory()">
              <i class="fa fa-plus mr-2"></i>
              Tambah Kategori Baru
            </button>
          </div>
          
          <!-- Middle Panel: Visual Grid Builder -->
          <div class="panel">
            <div class="panel-title" id="selected-category-title">
              Pilih kategori untuk mengedit
            </div>
            
            <div id="selected-category-content">
              <div class="empty-state">
                <i class="fa fa-mouse-pointer fa-3x mb-3"></i>
                <p>Klik kategori di sebelah kiri untuk mulai mengedit</p>
              </div>
            </div>
            
            <!-- Grid Builder (will be shown when category selected) -->
            <div id="grid-builder-container" style="display: none;">
              <div class="grid-preview-info">
                <span id="grid-size-info">Grid: 0 x 0</span>
                <button class="save-grid-btn" onclick="saveGridPositions()" id="save-grid-btn">
                  <i class="fa fa-save mr-1"></i>
                  Simpan Posisi
                </button>
              </div>
              
              <div class="grid-config">
                <div>
                  <label>Baris:</label>
                  <input type="number" id="grid-rows-input" min="0" max="20" value="0" 
                         onchange="regenerateGrid()" placeholder="0=auto">
                </div>
                <div>
                  <label>Kolom:</label>
                  <input type="number" id="grid-columns-input" min="1" max="6" value="3" 
                         onchange="regenerateGrid()">
                </div>
                <small style="color: #718096; flex: 1 0 100%;">
                  <i class="fa fa-info-circle"></i>
                  Drag & drop parameter dari panel kanan ke grid di bawah
                </small>
              </div>
              
              <div id="grid-builder" class="grid-builder">
                <!-- Grid cells will be generated here -->
              </div>
            </div>
          </div>
          
          <!-- Right Panel: Available Parameters -->
          <div class="panel">
            <div class="panel-title">
              Parameter Tersedia
            </div>
            
            <div class="search-box">
              <i class="fa fa-search"></i>
              <input type="text" id="search-parameter" placeholder="Cari parameter..." onkeyup="filterParameters()">
            </div>
            
            <div id="available-parameters" class="available-parameters">
              @foreach($allPakets as $paket)
                <div class="available-parameter" 
                     data-id="{{ $paket->id_parameter_paket_klinik }}"
                     data-name="{{ $paket->name_parameter_paket_klinik }}"
                     data-price="{{ $paket->harga_parameter_paket_klinik }}">
                  <div class="parameter-name">{{ $paket->name_parameter_paket_klinik }}</div>
                  <div class="parameter-price">Rp {{ number_format($paket->harga_parameter_paket_klinik, 0, ',', '.') }}</div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<button type="button" class="btn-save" onclick="saveLayout()">
  <i class="fa fa-save mr-2"></i>
  Simpan Layout
</button>

<div class="save-indicator" id="saveIndicator">
  <i class="fa fa-check-circle mr-2"></i>
  <span id="saveIndicatorText">Layout berhasil disimpan!</span>
</div>
@endsection

@section('scripts')
<script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>
{{-- SortableJS dari CDN dipindah ke file lokal --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script> --}}
<script src="{{ asset('assets/admin/cdn-local/js/sortable.min.js') }}"></script>

<script>
var categoriesData = @json($categories);
var selectedCategoryId = null;

// Set berisi semua paket_id yang sudah terpetakan di grid MANAPUN (lintas kategori)
var globalMappedIds = new Set();
(function() {
  categoriesData.forEach(function(cat) {
    if (cat.category_items) {
      cat.category_items.forEach(function(item) {
        if (item.id_parameter_paket_klinik) {
          globalMappedIds.add(item.id_parameter_paket_klinik);
        }
      });
    }
  });
})();

/**
 * Sinkronisasi tampilan "Parameter Tersedia":
 * sembunyikan yang sudah mapped, tampilkan yang belum.
 */
function syncAvailableList() {
  $('#available-parameters .available-parameter').each(function() {
    var id = $(this).data('id');
    if (globalMappedIds.has(id)) {
      $(this).hide();
    } else {
      // Hanya tampilkan jika memenuhi filter pencarian aktif
      var search = $('#search-parameter').val().toLowerCase();
      var name = ($(this).data('name') || '').toLowerCase();
      if (!search || name.indexOf(search) !== -1) {
        $(this).show();
      }
    }
  });
}

$(document).ready(function() {
  initializeSortables();

  // Sembunyikan parameter yang sudah terpetakan di grid pada saat pertama load
  syncAvailableList();
  
  // Auto-select first category if available
  if (categoriesData.length > 0) {
    $('.category-card').first().click();
  }
});

function initializeSortables() {
  // Sortable for categories list
  var categoriesList = document.getElementById('categories-list');
  Sortable.create(categoriesList, {
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd: function(evt) {
      updateCategoriesOrder();
    }
  });
  
  // Sortable for available parameters (clone on drag)
  var availableParams = document.getElementById('available-parameters');
  Sortable.create(availableParams, {
    group: {
      name: 'parameters',
      pull: 'clone',
      put: false
    },
    animation: 150,
    sort: false,
    ghostClass: 'sortable-ghost'
  });
}

// Global variables for grid
var currentGridCategory = null;
var gridData = {}; // {row_col: {paket_id, name, price}}

function selectCategory(element) {
  // Remove active class from all
  $('.category-card').removeClass('active');
  $(element).addClass('active');
  
  selectedCategoryId = $(element).data('id');
  var category = categoriesData.find(c => c.id_param_category_layout === selectedCategoryId);
  
  if (category) {
    renderGridBuilder(category);
  }
}

function renderGridBuilder(category) {
  currentGridCategory = category;
  var title = category.category_code + '. ' + category.category_name;
  $('#selected-category-title').html('<i class="fa fa-th mr-2"></i>' + title + ' - Visual Grid Builder');
  
  // Hide empty state, show grid builder
  $('#selected-category-content .empty-state').hide();
  $('#grid-builder-container').show();
  
  // Set grid config
  var gridRows = parseInt(category.grid_rows) || 0;
  var gridColumns = parseInt(category.grid_columns) || 3;
  $('#grid-rows-input').val(gridRows);
  $('#grid-columns-input').val(gridColumns);
  
  // Load existing items into gridData
  gridData = {};
  console.log('Loading category items:', category.category_items);
  
  if (category.category_items && category.category_items.length > 0) {
    category.category_items.forEach(function(item) {
      console.log('Item:', item);
      if (item.parameter_paket_klinik) {
        // Check if item has grid position
        if (item.row_position && item.column_position) {
          var key = item.row_position + '_' + item.column_position;
          gridData[key] = {
            paket_id: item.parameter_paket_klinik.id_parameter_paket_klinik,
            name: item.parameter_paket_klinik.name_parameter_paket_klinik,
            price: item.parameter_paket_klinik.harga_parameter_paket_klinik
          };
          console.log('Added to gridData[' + key + ']:', gridData[key]);
        } else {
          // If no grid position, auto-assign based on sort_order
          var sortOrder = item.sort_order || 1;
          var row = Math.ceil(sortOrder / gridColumns);
          var col = ((sortOrder - 1) % gridColumns) + 1;
          var key = row + '_' + col;
          
          // Only add if position is not occupied
          if (!gridData[key]) {
            gridData[key] = {
              paket_id: item.parameter_paket_klinik.id_parameter_paket_klinik,
              name: item.parameter_paket_klinik.name_parameter_paket_klinik,
              price: item.parameter_paket_klinik.harga_parameter_paket_klinik
            };
            console.log('Auto-assigned to gridData[' + key + ']:', gridData[key]);
          }
        }
      }
    });
  }
  
  console.log('Final gridData:', gridData);
  
  // Generate grid
  generateGrid(gridRows, gridColumns);
}

function generateGrid(rows, columns) {
  var gridBuilder = $('#grid-builder');
  gridBuilder.empty();
  
  // Update info
  $('#grid-size-info').text('Grid: ' + (rows === 0 ? 'Auto' : rows) + ' x ' + columns);
  
  // Set grid columns
  gridBuilder.css('grid-template-columns', 'repeat(' + columns + ', 1fr)');
  
  // Calculate actual rows
  var totalItems = Object.keys(gridData).length;
  var actualRows = rows > 0 ? rows : Math.ceil(totalItems / columns) || 3;
  
  // Generate cells
  for (var r = 1; r <= actualRows; r++) {
    for (var c = 1; c <= columns; c++) {
      var key = r + '_' + c;
      var item = gridData[key];
      
      var cell = $('<div>')
        .addClass('grid-cell')
        .attr('data-row', r)
        .attr('data-col', c);
      
      // Add event listeners using native JS
      cell[0].addEventListener('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
      });
      
      cell[0].addEventListener('dragleave', function(e) {
        $(this).removeClass('drag-over');
      });
      
      cell[0].addEventListener('drop', function(e) {
        dropParameter(e);
      });
      
      if (item) {
        cell.addClass('occupied');
        cell.append(
          '<button class="grid-cell-remove" onclick="removeCellParameter(this, event)">×</button>' +
          '<div class="grid-cell-content">' +
          '<div class="grid-cell-label">' + item.name + '</div>' +
          '<div class="grid-cell-price">Rp ' + formatNumber(item.price) + '</div>' +
          '</div>'
        );
        cell.attr('data-paket-id', item.paket_id);
      } else {
        cell.append('<div class="grid-cell-empty">Baris ' + r + ', Kolom ' + c + '</div>');
      }
      
      gridBuilder.append(cell);
    }
  }
}

// Also update in dropParameter function
function updateCellWithItem(cell, data) {
  cell.addClass('occupied');
  cell.attr('data-paket-id', data.paket_id);
  cell.html(
    '<button class="grid-cell-remove" onclick="removeCellParameter(this, event)">×</button>' +
    '<div class="grid-cell-content">' +
    '<div class="grid-cell-label">' + data.name + '</div>' +
    '<div class="grid-cell-price">Rp ' + formatNumber(data.price || 0) + '</div>' +
    '</div>'
  );
}

function regenerateGrid() {
  var rows = parseInt($('#grid-rows-input').val()) || 0;
  var columns = parseInt($('#grid-columns-input').val()) || 3;
  
  // Update category grid config in left panel
  $('.category-card.active').find('.grid-rows').val(rows);
  $('.category-card.active').find('.grid-columns').val(columns);
  
  // Update current category data
  if (currentGridCategory) {
    currentGridCategory.grid_rows = rows;
    currentGridCategory.grid_columns = columns;
  }
  
  // Save grid config
  updateCategoriesOrder();
  
  // Regenerate grid
  generateGrid(rows, columns);
}

function renderCategoryContent(category) {
  var title = category.category_code + '. ' + category.category_name;
  $('#selected-category-title').html('<i class="fa fa-edit mr-2"></i>' + title);
  
  var html = '<div class="parameter-drop-zone" id="category-parameters" data-category-id="' + category.id_param_category_layout + '">';
  
  if (category.category_items && category.category_items.length > 0) {
    category.category_items.forEach(function(item) {
      if (item.parameter_paket_klinik) {
        html += '<div class="parameter-item" data-paket-id="' + item.parameter_paket_klinik.id_parameter_paket_klinik + '">';
        html += '<span class="parameter-drag-handle"><i class="fa fa-bars"></i></span>';
        html += '<div class="parameter-name">' + item.parameter_paket_klinik.name_parameter_paket_klinik + '</div>';
        html += '<div class="parameter-price">Rp ' + formatNumber(item.parameter_paket_klinik.harga_parameter_paket_klinik) + '</div>';
        html += '<span class="parameter-remove" onclick="removeParameter(this)"><i class="fa fa-times"></i></span>';
        html += '</div>';
      }
    });
  } else {
    html += '<div class="empty-state">';
    html += '<i class="fa fa-inbox fa-2x mb-2"></i>';
    html += '<p>Seret parameter dari kanan untuk menambahkan</p>';
    html += '</div>';
  }
  
  html += '</div>';
  
  $('#selected-category-content').html(html);
  
  // Initialize sortable for category parameters
  var dropZone = document.getElementById('category-parameters');
  Sortable.create(dropZone, {
    group: 'parameters',
    animation: 150,
    handle: '.parameter-drag-handle',
    ghostClass: 'sortable-ghost',
    onAdd: function(evt) {
      // When item is dropped from available parameters
      var item = evt.item;
      var paketId = $(item).data('id');
      var paketName = $(item).data('name');
      var paketPrice = $(item).data('price');
      
      // Replace with formatted parameter item
      $(item).replaceWith(
        '<div class="parameter-item" data-paket-id="' + paketId + '">' +
        '<span class="parameter-drag-handle"><i class="fa fa-bars"></i></span>' +
        '<div class="parameter-name">' + paketName + '</div>' +
        '<div class="parameter-price">Rp ' + formatNumber(paketPrice) + '</div>' +
        '<span class="parameter-remove" onclick="removeParameter(this)"><i class="fa fa-times"></i></span>' +
        '</div>'
      );
      
      updateCategoryData();
    },
    onEnd: function(evt) {
      updateCategoryData();
    }
  });
}

function removeParameter(element) {
  swal({
    title: "Hapus parameter?",
    text: "Parameter akan dihapus dari kategori ini",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  }).then((willDelete) => {
    if (willDelete) {
      $(element).closest('.parameter-item').remove();
      updateCategoryData();
    }
  });
}

function updateCategoryData() {
  if (!selectedCategoryId) return;
  
  var items = [];
  $('#category-parameters .parameter-item').each(function(index) {
    items.push({
      paket_id: $(this).data('paket-id'),
      sort: index + 1
    });
  });
  
  // Update local categoriesData
  var category = categoriesData.find(c => c.id_param_category_layout === selectedCategoryId);
  if (category) {
    // Update category items count in left panel
    var card = $('.category-card[data-id="' + selectedCategoryId + '"]');
    card.find('.category-meta span').last().text(items.length + ' item');
  }
  
  // Save to server
  $.ajax({
    type: 'POST',
    url: '{{ route("elits-parameter-paket-klinik.updateCategoryItems") }}',
    data: {
      _token: '{{ csrf_token() }}',
      category_id: selectedCategoryId,
      items: items
    },
    success: function(response) {
      if (response.status) {
        showSaveIndicator(response.pesan, 'success');
      }
    }
  });
}

function updateCategoriesOrder() {
  var categories = [];
  $('#categories-list .category-card').each(function(index) {
    var card = $(this);
    categories.push({
      id: card.data('id'),
      code: card.find('.category-code').text(),
      name: card.find('.category-name-input').val(),
      width: parseInt(card.find('.category-width').val()),
      empty_position: card.find('.category-empty-position').val(),
      grid_rows: parseInt(card.find('.grid-rows').val()) || 0,
      grid_columns: parseInt(card.find('.grid-columns').val()) || 3,
      sort: index + 1
      // Don't send is_active - let it stay as is in database
    });
  });
  
  // Update server
  $.ajax({
    type: 'POST',
    url: '{{ route("elits-parameter-paket-klinik.updateCategoryLayout") }}',
    data: {
      _token: '{{ csrf_token() }}',
      categories: categories
    },
    success: function(response) {
      if (response.status) {
        showSaveIndicator('Urutan kategori berhasil diupdate!', 'success');
      }
    }
  });
}

function updateCategoryName(input) {
  updateCategoriesOrder();
}

function updateCategoryWidth(select) {
  updateCategoriesOrder();
}

function addCategory() {
  swal({
    title: "Tambah Kategori Baru",
    text: "Masukkan nama kategori:",
    content: {
      element: "input",
      attributes: {
        placeholder: "Contoh: FESES",
        type: "text",
      },
    },
    buttons: true,
  }).then((name) => {
    if (name) {
      $.ajax({
        type: 'POST',
        url: '{{ route("elits-parameter-paket-klinik.addCategory") }}',
        data: {
          _token: '{{ csrf_token() }}',
          category_name: name,
          category_code: String.fromCharCode(65 + $('.category-card').length), // A, B, C, ...
          column_width: 4
        },
        success: function(response) {
          if (response.status) {
            swal("Success!", response.pesan, "success").then(() => {
              location.reload();
            });
          }
        },
        error: function() {
          swal("Error!", "Gagal menambah kategori!", "error");
        }
      });
    }
  });
}

function saveLayout() {
  updateCategoriesOrder();
  swal("Success!", "Layout berhasil disimpan!", "success");
}

function filterParameters() {
  var search = $('#search-parameter').val().toLowerCase();
  $('#available-parameters .available-parameter').each(function() {
    var id   = $(this).data('id');
    var name = ($(this).data('name') || '').toLowerCase();
    // Sembunyikan yang sudah mapped, terlepas dari filter teks
    if (globalMappedIds.has(id)) {
      $(this).hide();
      return;
    }
    if (name.indexOf(search) !== -1) {
      $(this).show();
    } else {
      $(this).hide();
    }
  });
}

function showSaveIndicator(message, type) {
  var indicator = $('#saveIndicator');
  var textEl = $('#saveIndicatorText');
  
  textEl.text(message);
  
  if (type === 'success') {
    indicator.css('background', '#10b981');
  } else {
    indicator.css('background', '#ef4444');
  }
  
  indicator.fadeIn(300);
  
  setTimeout(function() {
    indicator.fadeOut(300);
  }, 3000);
}

function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ==================== DRAG & DROP FUNCTIONS FOR GRID ====================

// Initialize draggable for available parameters
function initializeDraggableParameters() {
  $('.available-parameter').each(function() {
    $(this).attr('draggable', 'true');
    
    this.addEventListener('dragstart', function(e) {
      var data = {
        paket_id: $(this).data('id'),
        name: $(this).data('name'),
        price: $(this).data('price')
      };
      e.dataTransfer.setData('application/json', JSON.stringify(data));
      e.dataTransfer.effectAllowed = 'copy';
      $(this).css('opacity', '0.5');
    });
    
    this.addEventListener('dragend', function(e) {
      $(this).css('opacity', '1');
    });
  });
}

// Call on page load
$(document).ready(function() {
  initializeDraggableParameters();
});

function dropParameter(event) {
  event.preventDefault();
  event.stopPropagation();
  $(event.currentTarget).removeClass('drag-over');
  
  var cell = $(event.currentTarget);
  var row = cell.data('row');
  var col = cell.data('col');
  var key = row + '_' + col;
  
  // Check if cell is already occupied
  if (gridData[key]) {
    swal("Info!", "Posisi ini sudah terisi. Hapus parameter yang ada terlebih dahulu.", "info");
    return;
  }
  
  try {
    // Try to get data from different formats
    var dataStr = event.dataTransfer.getData('application/json') || 
                   event.dataTransfer.getData('text/plain') ||
                   event.dataTransfer.getData('text');
    
    if (!dataStr) {
      console.error('No data found in drag event');
      return;
    }
    
    var data = JSON.parse(dataStr);
    
    // Validate data
    if (!data.paket_id || !data.name) {
      console.error('Invalid data structure:', data);
      return;
    }
    
    // Check if parameter already exists in grid
    var alreadyExists = false;
    Object.keys(gridData).forEach(function(k) {
      if (gridData[k] && gridData[k].paket_id === data.paket_id) {
        alreadyExists = true;
      }
    });
    
    if (alreadyExists) {
      swal("Info!", "Parameter ini sudah ada di grid. Setiap parameter hanya boleh ditambahkan sekali.", "info");
      return;
    }
    
    // Add to gridData
    gridData[key] = {
      paket_id: data.paket_id,
      name: data.name,
      price: data.price || 0
    };
    
    // Update cell display using helper function
    updateCellWithItem(cell, gridData[key]);

    // Tandai sebagai mapped & sembunyikan dari "Parameter Tersedia"
    globalMappedIds.add(data.paket_id);
    syncAvailableList();
    
    showSaveIndicator('Parameter ditambahkan! Jangan lupa klik "Simpan Posisi"', 'success');
  } catch (e) {
    console.error('Error dropping parameter:', e);
    console.error('Event data:', event.dataTransfer.types);
  }
}

function removeCellParameter(button, e) {
  if (e) {
    e.stopPropagation();
  }
  
  var cell = $(button).closest('.grid-cell');
  var row = cell.data('row');
  var col = cell.data('col');
  var key = row + '_' + col;

  // Simpan data sebelum dihapus agar bisa dikembalikan ke "Parameter Tersedia"
  var removedItem = gridData[key] ? Object.assign({}, gridData[key]) : null;
  
  // Remove from gridData
  delete gridData[key];
  
  // Reset cell display
  cell.removeClass('occupied');
  cell.removeAttr('data-paket-id');
  cell.html('<div class="grid-cell-empty">Baris ' + row + ', Kolom ' + col + '</div>');
  
  // Re-attach event listeners
  cell[0].addEventListener('dragover', function(e) {
    e.preventDefault();
    $(this).addClass('drag-over');
  });
  
  cell[0].addEventListener('dragleave', function(e) {
    $(this).removeClass('drag-over');
  });
  
  cell[0].addEventListener('drop', function(e) {
    dropParameter(e);
  });

  // Cek apakah paket_id ini masih terpetakan di kategori lain
  if (removedItem) {
    var stillMapped = false;
    // Cek di categoriesData (kategori lain, bukan yang sedang aktif)
    categoriesData.forEach(function(cat) {
      if (cat.id_param_category_layout !== selectedCategoryId && cat.category_items) {
        cat.category_items.forEach(function(item) {
          if (item.id_parameter_paket_klinik === removedItem.paket_id) {
            stillMapped = true;
          }
        });
      }
    });
    // Cek di gridData saat ini (jika paket muncul di cell lain kategori aktif)
    Object.keys(gridData).forEach(function(k) {
      if (gridData[k] && gridData[k].paket_id === removedItem.paket_id) {
        stillMapped = true;
      }
    });

    if (!stillMapped) {
      // Kembalikan ke "Parameter Tersedia" jika belum ada di DOM
      globalMappedIds.delete(removedItem.paket_id);
      var existing = $('#available-parameters .available-parameter[data-id="' + removedItem.paket_id + '"]');
      if (existing.length === 0) {
        // Tambahkan elemen baru ke list
        var html = '<div class="available-parameter" ' +
          'data-id="' + removedItem.paket_id + '" ' +
          'data-name="' + removedItem.name + '" ' +
          'data-price="' + (removedItem.price || 0) + '">' +
          '<div class="parameter-name">' + removedItem.name + '</div>' +
          '<div class="parameter-price">Rp ' + formatNumber(removedItem.price || 0) + '</div>' +
          '</div>';
        $('#available-parameters').append(html);
        // Tambahkan drag handler untuk elemen baru
        var newEl = $('#available-parameters .available-parameter[data-id="' + removedItem.paket_id + '"]')[0];
        if (newEl) {
          $(newEl).attr('draggable', 'true');
          newEl.addEventListener('dragstart', function(ev) {
            var d = { paket_id: $(this).data('id'), name: $(this).data('name'), price: $(this).data('price') };
            ev.dataTransfer.setData('application/json', JSON.stringify(d));
            ev.dataTransfer.effectAllowed = 'copy';
            $(this).css('opacity', '0.5');
          });
          newEl.addEventListener('dragend', function() { $(this).css('opacity', '1'); });
        }
      }
      syncAvailableList();
    }
  }
  
  showSaveIndicator('Parameter dihapus! Jangan lupa klik "Simpan Posisi"', 'success');
}

function saveGridPositions() {
  if (!currentGridCategory) {
    swal("Error!", "Tidak ada kategori yang dipilih.", "error");
    return;
  }
  
  // Prepare items data
  var items = [];
  Object.keys(gridData).forEach(function(key) {
    var parts = key.split('_');
    items.push({
      paket_id: gridData[key].paket_id,
      row: parseInt(parts[0]),
      column: parseInt(parts[1])
    });
  });
  
  $('#save-grid-btn').prop('disabled', true).text('Menyimpan...');
  
  // Save to server
  $.ajax({
    type: 'POST',
    url: '{{ route("elits-parameter-paket-klinik.updateItemGridPosition") }}',
    data: {
      _token: '{{ csrf_token() }}',
      category_id: currentGridCategory.id_param_category_layout,
      items: items
    },
    success: function(response) {
      if (response.status) {
        swal("Sukses!", response.pesan, "success").then(() => {
          location.reload();
        });
      } else {
        swal("Error!", response.pesan, "error");
      }
    },
    error: function() {
      swal("Error!", "Gagal menyimpan posisi parameter.", "error");
    },
    complete: function() {
      $('#save-grid-btn').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Simpan Posisi');
    }
  });
}
</script>
@endsection
