@extends('backend.app')
@section('content')

<style>
  :root{
    --bc-border:#e5e7eb;
    --bc-soft:#fafafa;
    --bc-dark:#111827;
    --bc-muted:#6b7280;
    --success:#16a34a;
    --danger:#ef4444;
  }

  th, td, h4, .pr_list, .form-label { color:#000 !important; }

  .stock-stat{
    display:flex; align-items:center; gap:.75rem; height:100%;
    padding:.75rem 1rem; border-radius:12px; background:#fff;
    border:1px solid var(--bc-border); border-left:4px solid var(--stat-color);
    text-decoration:none; transition:box-shadow .15s, transform .15s;
  }
  .stock-stat:hover{ box-shadow:0 6px 16px rgba(15,23,42,.08); transform:translateY(-1px); }
  .stock-stat.active{ border-color:var(--stat-color); box-shadow:0 0 0 2px var(--stat-color) inset; }
  .stock-stat-icon{ font-size:26px; line-height:1; color:var(--stat-color); }
  .stock-stat-count{ display:block; font-size:1.35rem; font-weight:700; line-height:1.1; color:var(--bc-dark); }
  .stock-stat-label{ display:block; font-size:.8rem; color:var(--bc-muted); }

  .toolbar-sticky{
    position: sticky; top: 0; z-index: 6; background:#fff;
    padding:.5rem 0; border-bottom:1px solid #f1f1f1;
  }

  table.table{
    border-collapse:collapse !important;
    border-spacing:0 !important;
    background:#fff;
  }
  table.table thead th,
  table.table tbody td{
    border:1px solid var(--bc-border) !important;
    vertical-align:middle;
  }
  table.table thead th{
    background:#f8fafc !important;
    font-weight:800;
    white-space:nowrap;
  }

  .td-action{ white-space:nowrap; }
  .action-icons{
    display:flex;
    align-items:center;
    gap:10px;
  }
  .action-icon{
    display:inline-flex !important;
    align-items:center;
    justify-content:center;
    width:34px;
    height:34px;
    border:1px solid var(--bc-border);
    border-radius:8px;
    background:#fff;
    color:#111827;
    text-decoration:none;
  }
  .action-icon i{ font-size:18px; line-height:1; }
  .action-icon:hover{ background:#f3f4f6; }
  .action-icon.delete{ color:var(--danger) !important; }
  .action-icon.delete:hover{ background:#fee2e2; }

  table.table.table-hover tbody tr:hover{
    background:#f9fafb;
  }

  .bulk-info{
    font-size:.9rem; color:#555;
  }

  @media (max-width:576px){
    .table-responsive{ border:0; }
    table.table thead{ display:none; }
    table.table tbody tr{
      display:block; margin-bottom:10px; border:1px solid #eee; border-radius:10px; padding:10px;
    }
    table.table tbody td{
      display:flex; justify-content:space-between; gap:10px;
      border:0 !important; padding:.25rem 0 !important;
      font-size:13px !important;
    }
    table.table tbody td::before{
      content: attr(data-label);
      font-weight:600; color:#111;
    }
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">CRM</a></li>
          <li class="breadcrumb-item active pr_list">Product List</li>
        </ol>
      </div>
      <h4 class="page-title">Product List</h4>
    </div>
  </div>
</div>

@php
  $stockStats = [
    ''          => ['label' => 'All Products', 'color' => '#2563eb', 'icon' => 'mdi-package-variant-closed'],
    'in_stock'  => ['label' => 'In Stock',     'color' => '#16a34a', 'icon' => 'mdi-check-circle-outline'],
    'low_stock' => ['label' => 'Low Stock',    'color' => '#d97706', 'icon' => 'mdi-alert-outline'],
    'stock_out' => ['label' => 'Stock Out',    'color' => '#ef4444', 'icon' => 'mdi-close-circle-outline'],
  ];
@endphp
<div class="row g-2 mb-2 px-1">
  @foreach($stockStats as $key => $stat)
    <div class="col-6 col-lg-3">
      {{-- Keeps the category / brand / search filters, only switches the stock status. --}}
      <a href="{{ route('admin.products.index', array_filter(array_merge(request()->except(['stock_status', 'page']), ['stock_status' => $key]))) }}"
         class="stock-stat {{ (string) $stock_status === (string) $key ? 'active' : '' }}" style="--stat-color: {{ $stat['color'] }};">
        <span class="stock-stat-icon"><i class="mdi {{ $stat['icon'] }}"></i></span>
        <span>
          <span class="stock-stat-count">{{ $stockCounts[$key] }}</span>
          <span class="stock-stat-label">{{ $stat['label'] }}</span>
        </span>
      </a>
    </div>
  @endforeach
</div>

<div class="row">
  <div class="col-12 p-1">
    <div class="card">
      <div class="card-body">

        <div class="row mb-2 toolbar-sticky">
          <div class="col-md-8">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <a class="btn btn-sm btn-info recomm_update" href="{{ route('admin.recommendedUpdate')}}?is_recommended=1">Active (Home)</a>
              <a class="btn btn-sm btn-danger recomm_update" href="{{ route('admin.recommendedUpdate')}}?is_recommended=0">De-active (Home)</a>
              <a class="btn btn-sm btn-info show_update" href="{{ route('admin.showUpdate')}}?status=1">Show</a>
              <a class="btn btn-sm btn-danger show_update" href="{{ route('admin.showUpdate')}}?status=0">Hide</a>
              <a class="btn btn-sm btn-success recomm_update" href="{{ route('admin.checkoutPickUpdate')}}?is_checkout_pick=1">Add (Checkout)</a>
              <a class="btn btn-sm btn-warning recomm_update" href="{{ route('admin.checkoutPickUpdate')}}?is_checkout_pick=0">Remove (Checkout)</a>

              <span class="bulk-info ms-2">
                Selected: <strong id="bulkCount">0</strong>
              </span>
            </div>
          </div>

          <div class="col-md-4 text-xl-end mt-xl-0 mt-2">
            @can('product.create')
              <a href="{{ route('admin.products.create')}}" class="btn btn-danger mb-2 me-2">
                <i class="mdi mdi-basket me-1"></i> Add Product
              </a>
            @endcan
            <a href="{{ route('admin.productExport') }}" class="btn btn-light mb-2" style="color:#000;">Export</a>
          </div>

          <div class="col-12 mt-2">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.products.index') }}">
              <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control select2" id="category">
                  <option value="">{{ __('Select Category') }}</option>
                  @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ $category->id == $cat_id ? 'selected' : '' }}>{{ $category->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Brand</label>
                <select name="type_id" class="form-control select2" id="brand">
                  <option value="">{{ __('Select Brand') }}</option>
                  @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" {{ $brand->id == $brand_id ? 'selected' : '' }}>{{ $brand->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Product Type</label>
                <select name="product_type" class="form-control" onchange="this.form.submit()">
                  <option value="">All Types</option>
                  <option value="single" {{ $product_type == 'single' ? 'selected' : '' }}>Single</option>
                  <option value="variable" {{ $product_type == 'variable' ? 'selected' : '' }}>Variant</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Stock Status</label>
                <select name="stock_status" class="form-control" onchange="this.form.submit()">
                  <option value="">All ({{ $stockCounts[''] }})</option>
                  <option value="in_stock" {{ $stock_status == 'in_stock' ? 'selected' : '' }}>In Stock ({{ $stockCounts['in_stock'] }})</option>
                  <option value="low_stock" {{ $stock_status == 'low_stock' ? 'selected' : '' }}>Low Stock, {{ $lowLimit }} or less ({{ $stockCounts['low_stock'] }})</option>
                  <option value="stock_out" {{ $stock_status == 'stock_out' ? 'selected' : '' }}>Stock Out ({{ $stockCounts['stock_out'] }})</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="search" class="form-control" name="q" placeholder="Search..." value="{{ $q ?? '' }}">
              </div>
              <div class="col-md-2">
                <label class="form-label d-block">&nbsp;</label>
                <div class="d-flex gap-2">
                  <button class="btn btn-primary flex-fill">Filter</button>
                  <a href="{{ route('admin.products.index') }}" class="btn btn-light border flex-fill" style="color:#000;">Reset</a>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="col-md-12 col-sm-12 p-1">
          <div class="table-responsive">
            <table class="table table-centered mb-0 table-hover">
              <thead class="table-light">
                <tr>
                  <th style="width:10%">Action</th>
                  <th>
                    <div class="form-check mb-0">
                      <input type="checkbox" class="form-check-input check_all" id="check_all">
                    </div>
                  </th>
                  <th style="width:12%">Product</th>
                  <th style="width:8%">Sku</th>
                  <th>Image</th>
                  <th>Type</th>
                  <th>Category</th>
                  <th>Sell Price</th>
                  <th>Stock</th>
                  <th>Stock Status</th>
                  <th>Visibility</th>
                  <th style="width:12%;">Priority</th>
                  <th>Recommended</th>
                  <th>Popular</th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $item)
                  <tr>
                    <td data-label="Action" class="td-action">
                      <div class="action-icons">
                        @can('product.edit')
                          <a href="{{ route('admin.products.edit',[$item->id])}}" class="action-icon" title="Edit">
                            <i class="mdi mdi-square-edit-outline"></i>
                          </a>
                        @endcan
                        @can('product.edit')
                          <a href="javascript:void(0)" class="action-icon quick-stock-btn" title="Quick Stock Update"
                             data-url="{{ route('admin.products.quickStock', $item->id) }}"
                             data-save-url="{{ route('admin.products.quickStockUpdate', $item->id) }}">
                            <i class="mdi mdi-package-variant-closed"></i>
                          </a>
                        @endcan
                        @can('product.create')
                          <form action="{{ route('admin.products.duplicate', $item->id) }}" method="POST" class="d-inline duplicate-form">
                            @csrf
                            <button type="submit" class="action-icon duplicate border-0 bg-transparent p-0" title="Duplicate">
                              <i class="mdi mdi-content-copy"></i>
                            </button>
                          </form>
                        @endcan
                        @can('product.delete')
                          <a href="{{ route('admin.products.destroy',[$item->id])}}" class="delete action-icon" title="Delete">
                            <i class="mdi mdi-delete"></i>
                          </a>
                        @endcan
                      </div>
                    </td>

                    <td data-label="Select">
                      <input type="checkbox" class="checkbox" value="{{ $item->id}}">
                    </td>
                    <td data-label="Product">{{ $item->name }}</td>
                    <td data-label="Sku">{{ $item->sku }}</td>
                    <td data-label="Image">
                      <img src="{{ getImage('thumb_products',$item->image)}}" class="rounded-circle avatar-xs" alt="img">
                    </td>
                    <td data-label="Type">
                      @if($item->type === 'variable')
                        <span class="badge bg-primary">Variant</span>
                      @else
                        <span class="badge bg-info text-dark">Single</span>
                      @endif
                    </td>
                    <td data-label="Category">
                      {{ $item->category? $item->category->name : '' }}
                      @foreach($item->categories as $extraCat)
                        <span class="badge bg-light text-dark border">{{ $extraCat->name }}</span>
                      @endforeach
                    </td>
                    <td data-label="Sell Price">{{ number_format($item->sell_price,2) }}</td>
                    {{-- আগে শুধু products.stock_quantity দেখাত, তাই এডিট পেজ আর
                         প্রোডাক্ট ভিউয়ের সংখ্যার সাথে মিলত না। এখন একই resolveStock()। --}}
                    @php $stockQty = resolveStock($item); @endphp
                    <td data-label="Stock">{{ $stockQty }}</td>
                    <td data-label="Stock Status">
                      @if($stockQty <= 0)
                        <span class="badge bg-danger">Stock Out</span>
                      @elseif($stockQty <= $lowLimit)
                        <span class="badge bg-warning text-dark">Low Stock</span>
                      @else
                        <span class="badge bg-success">In Stock</span>
                      @endif
                    </td>
                    <td data-label="Visibility">{{ $item->status=='1' ? 'Show' : 'Hide' }}</td>
                    <td data-label="Priority">
                      <input type="number" min="0" class="priority-input form-control form-control-sm"
                             data-product-id="{{ $item->id }}"
                             value="{{ $item->priority }}"
                             style="max-width:80px"/>
                    </td>

                    <td data-label="Recommended">
                      @if($item->is_recommended == '1')
                        <span class="badge bg-success">Yes</span>
                      @else
                        <span class="badge bg-secondary">No</span>
                      @endif
                    </td>

                    <td data-label="Popular">
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-popular" type="checkbox" data-id="{{ $item->id }}" {{ $item->is_popular == 1 ? 'checked' : '' }} style="cursor: pointer;">
                        </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="mt-2">
            {!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
{{-- Quick stock update modal --}}
<div class="modal fade" id="quickStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form id="quickStockForm">
        <div class="modal-header">
          <h5 class="modal-title">Quick Stock Update</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="quickStockLoading" class="text-center py-4 text-muted">Loading...</div>
          <div id="quickStockBody" class="d-none">
            <div class="d-flex align-items-center gap-3 mb-3">
              <img id="quickStockImage" src="" alt="" class="rounded border" style="width:72px;height:72px;object-fit:cover;">
              <div>
                <div class="fw-bold" style="color:#000;"><span id="quickStockName"></span> <span id="quickStockBadge"></span></div>
                <small class="text-muted">SKU: <span id="quickStockSku"></span> &middot; Total stock: <span id="quickStockTotal"></span></small>
              </div>
            </div>
            <div id="quickStockRows"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="quickStockSave">Update Stock</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
(function(){
  const checkAll = document.querySelector('.check_all');
  const bulkCount = document.getElementById('bulkCount');

  function updateBulkCount(){
    const count = document.querySelectorAll('.checkbox:checked').length;
    if(bulkCount) bulkCount.textContent = count;
  }
  checkAll?.addEventListener('change', function(){
    document.querySelectorAll('.checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkCount();
  });
  document.addEventListener('change', e=>{
    if(e.target.classList?.contains('checkbox')) updateBulkCount();
  });

  document.addEventListener('click', function(e){
    const el = e.target.closest('a.delete');
    if(!el) return;
    if(!confirm('Delete this product?')) e.preventDefault();
  });

  document.addEventListener('submit', function(e){
    if(!e.target.classList.contains('duplicate-form')) return;
    if(!confirm('Duplicate this product?')) e.preventDefault();
  });

  @if(session('success'))
    if (window.toastr) toastr.success(@json(session('success')));
  @endif
  @if(session('error'))
    if (window.toastr) toastr.error(@json(session('error')));
  @endif

  let timer;
  function savePriority(productId, value){
    $.ajax({
      url: '/admin/update-priority/' + productId,
      type: 'POST',
      data: { priority: value, _token: '{{ csrf_token() }}' },
      success: function(){
        if (window.toastr) toastr.success('Priority updated');
      },
      error: function(){
        if (window.toastr) toastr.error('Failed to update priority');
      }
    });
  }

  $(document).on('input', '.priority-input', function(){
    const productId = $(this).data('product-id');
    const val = $(this).val();
    clearTimeout(timer);
    timer = setTimeout(()=> savePriority(productId, val), 400);
  });

  function getSelectedIds(){
    return Array.from(document.querySelectorAll('.checkbox:checked')).map(cb=>cb.value);
  }

  $(document).on('click', 'a.recomm_update, a.show_update', function(e){
    e.preventDefault();
    const url = $(this).attr('href');
    const product_ids = getSelectedIds();
    if(product_ids.length === 0){
      return window.toastr ? toastr.error('Please select product(s) first!') : alert('Please select product(s) first!');
    }
    $.ajax({
      type:'GET', url,
      data:{ 'product_ids[]': product_ids },
      beforeSend(){ $('body').css('cursor','wait'); },
      complete(){ $('body').css('cursor','default'); },
      success:function(res){
        if(res.status===true){
          if(window.toastr) toastr.success(res.msg);
          location.reload();
        }else{
          if(window.toastr) toastr.error(res.msg || 'Failed');
        }
      },
      error:function(){ if(window.toastr) toastr.error('Request failed'); }
    });
  });

  $(document).on('change', '.toggle-popular', function() {
      let id = $(this).data('id');
      let is_popular = $(this).is(':checked') ? 1 : 0;
      
      $.ajax({
          url: "{{ route('admin.product.togglePopular') }}",
          type: "POST",
          data: {
              _token: "{{ csrf_token() }}",
              id: id,
              is_popular: is_popular
          },
          success: function(res) {
              if(window.toastr) {
                  if(res.status) {
                      toastr.success(res.msg);
                  } else {
                      toastr.error(res.msg);
                  }
              }
          },
          error: function() {
              if(window.toastr) toastr.error('Failed to update popular status');
          }
      });
  });

})();

// Quick stock update
$(function(){
  const modalEl = document.getElementById('quickStockModal');
  if (!modalEl) return;
  const modal = new bootstrap.Modal(modalEl);
  let saveUrl = '';

  const lowLimit = {{ (int) $lowLimit }};

  // Same rule as the Stock Status column: 0 = out, 1..limit = low, above = in stock.
  function stockBadge(qty){
    qty = parseInt(qty, 10) || 0;
    if (qty <= 0) return '<span class="badge bg-danger">Stock Out</span>';
    if (qty <= lowLimit) return '<span class="badge bg-warning text-dark">Low Stock</span>';
    return '<span class="badge bg-success">In Stock</span>';
  }

  function stockRow(label, name, value, image){
    const row = $('<div class="d-flex align-items-center gap-2 mb-2 quick-stock-row"></div>');
    if (image) row.append($('<img class="rounded border" style="width:38px;height:38px;object-fit:cover;">').attr('src', image));
    row.append($('<div class="flex-grow-1" style="color:#000;"></div>').text(label));
    row.append($('<span class="quick-stock-row-badge"></span>').html(stockBadge(value)));
    row.append($('<input type="number" min="0" step="1" class="form-control quick-stock-input" style="width:110px;" required>').attr('name', name).val(value));
    return row;
  }

  // Badges and the total follow the numbers as they are typed.
  function refreshQuickStock(){
    let total = 0;
    $('#quickStockRows .quick-stock-row').each(function(){
      const qty = Math.max(0, parseInt($(this).find('.quick-stock-input').val(), 10) || 0);
      total += qty;
      $(this).find('.quick-stock-row-badge').html(stockBadge(qty));
    });
    $('#quickStockTotal').text(total);
    $('#quickStockBadge').html(stockBadge(total));
  }
  $(document).on('input', '.quick-stock-input', refreshQuickStock);

  $(document).on('click', '.quick-stock-btn', function(){
    saveUrl = $(this).data('save-url');
    $('#quickStockBody').addClass('d-none');
    $('#quickStockLoading').removeClass('d-none').text('Loading...');
    $('#quickStockSave').prop('disabled', true);
    modal.show();

    $.get($(this).data('url'), function(res){
      $('#quickStockImage').attr('src', res.image);
      $('#quickStockName').text(res.name);
      $('#quickStockSku').text(res.sku || '-');
      $('#quickStockTotal').text(res.stock);

      const rows = $('#quickStockRows').empty();
      if (res.is_variable) {
        res.variations.forEach(v => rows.append(stockRow(v.title, 'variations[' + v.id + ']', v.stock, v.image)));
      } else {
        rows.append(stockRow('Stock quantity', 'stock', res.stock, null));
      }

      refreshQuickStock();

      $('#quickStockLoading').addClass('d-none');
      $('#quickStockBody').removeClass('d-none');
      $('#quickStockSave').prop('disabled', false);
    }).fail(function(){
      $('#quickStockLoading').text('Could not load this product.');
    });
  });

  $('#quickStockForm').on('submit', function(e){
    e.preventDefault();
    $('#quickStockSave').prop('disabled', true);

    $.ajax({
      type: 'POST',
      url: saveUrl,
      data: $(this).serialize() + '&_token={{ csrf_token() }}',
      success: function(res){
        toastr.success(res.msg);
        // Reload so the stock number, badge and the counts at the top all refresh.
        setTimeout(() => window.location.reload(), 600);
      },
      error: function(xhr){
        $('#quickStockSave').prop('disabled', false);
        const errors = xhr.responseJSON && xhr.responseJSON.errors;
        toastr.error(errors ? Object.values(errors)[0][0] : 'Something went wrong!');
      }
    });
  });
});
</script>
@endpush
