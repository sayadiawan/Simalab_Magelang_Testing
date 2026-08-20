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
    border-color: #667eea;
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
    background: #667eea;
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
    border: 1px solid #667eea;
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
    border-color: #667eea;
  }
  
  .available-parameter.sortable-ghost {
    opacity: 0.4;
  }
  
  /* Buttons */
  .btn-add-category {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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
      <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
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
                </div>
              @endforeach
            </div>
            
            <button type="button" class="btn-add-category mt-3" onclick="addCategory()">
              <i class="fa fa-plus mr-2"></i>
              Tambah Kategori Baru
            </button>
          </div>
          
          <!-- Middle Panel: Selected Category Details -->
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
var categoriesData = @json($categories);
var selectedCategoryId = null;

$(document).ready(function() {
  initializeSortables();
  
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

function selectCategory(element) {
  // Remove active class from all
  $('.category-card').removeClass('active');
  $(element).addClass('active');
  
  selectedCategoryId = $(element).data('id');
  var category = categoriesData.find(c => c.id_param_category_layout === selectedCategoryId);
  
  if (category) {
    renderCategoryContent(category);
  }
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
    var name = $(this).data('name').toLowerCase();
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
</script>
@endsection
