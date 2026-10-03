@extends('backend.app')
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<style>
    /* Tumar deya same CSS gulo ekhane use korte parba, ami just main form structure ta dicchi jate courier fields na thake */
    :root { --bg:#f8fafc; --card:#ffffff; --muted:#6b7280; --text:#111827; --primary:#0ea5e9; --border:#e5e7eb; }
    body { background:var(--bg) }
    .order-card { border:0; border-radius:20px; box-shadow:0 10px 24px rgba(2,6,23,.06); overflow:hidden; }
    .order-card .card-body { padding:26px; }
    .table-wrap { margin-top:10px; }
    .table-responsive { border-radius:.9rem; background:var(--card); padding:6px; border:1px solid var(--border); }
    .search input { height:46px; border-radius:999px; padding-left:40px; }
    .sticky-actions { text-align:right; margin-top:16px; }
    .quantity, .unit_price, .unit_discount { max-width: 90px; }
    #product_table img { border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center mb-3">
            <h4 class="page-title mb-0">New Cash Sale (Shop)</h4>
        </div>
    </div>
</div>

<div class="row order-layout">
    <div class="col-12">
        <div class="card order-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.cash_sales.store') }}" id="ajax_form">
                    @csrf
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}" required name="date"/>
                        </div>
                    </div>

                    {{-- Product Search --}}
                    <div class="row mt-3 mb-2">
                        <div class="col-md-8 mx-auto">
                            <div class="search" style="position: relative;">
                                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); opacity: 0.6;">🔍</span>
                                <input type="text" id="search" class="form-control" placeholder="Search product by name or SKU...">
                            </div>
                        </div>
                    </div>

                    {{-- Product Table --}}
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0" id="product_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Product</th>
                                        <th>Variant</th>
                                        <th style="width:110px;">Quantity</th>
                                        <th style="width:120px;">Price</th>
                                        <th style="width:120px;">Discount</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="data">
                                    {{-- JS generated --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Customer Info & Total --}}
                    <div class="row g-3 mt-4">
                        <div class="col-md-4">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" name="first_name" placeholder="Walk-in Customer" value="Walk-in Customer" required/>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Customer Mobile</label>
                            <input type="text" class="form-control" name="mobile" placeholder="Optional"/>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-danger fw-bold">Grand Total (৳)</label>
                            <input type="text" class="form-control fw-bold" value="0" name="final_amount" id="purchase_total" readonly style="font-size: 18px; color: #dc2626;"/>
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label">Customer Address</label>
                            <textarea rows="2" name="shipping_address" class="form-control" placeholder="Optional"></textarea>
                        </div>
                    </div>

                    <div class="sticky-actions">
                        <button class="btn btn-success" type="submit" style="padding: 10px 25px; font-weight: bold; border-radius: 50px;">
                            Complete Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
$(function(){
    
    // Helper function for finding the best price
    function bestPrice(obj){
        const cands = [
            obj?.discount_price, obj?.after_discount_price, obj?.after_discount,
            obj?.price, obj?.sell_price, obj?.regular_price
        ];
        for (const v of cands){
            const n = parseFloat(v);
            if(!isNaN(n) && n > 0) return n;
        }
        return 0;
    }

    function escapeHtml(str){
        return String(str ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'","&#39;");
    }

    $("#search").autocomplete({
        minLength: 2,
        source: function(request, response){
            $.getJSON("{{ route('admin.products.search') }}", { q: request.term }, function(list){
                if(list.length === 0){
                    toastr.error('Product Or Stock Not Found');
                }
                response($.map(list, function(p){
                    return { 
                        label: (p.sku ? '['+p.sku+'] ' : '') + p.name, 
                        value: p.name, 
                        data : p 
                    };
                }));
            });
        },
        select: function(e, ui){
            e.preventDefault();
            addProductRow(ui.item.data);
            $(this).val('');
        }
    });

    function addProductRow(p){
        const hasVar     = Array.isArray(p.variations) && p.variations.length > 0;
        const defPrice   = hasVar ? bestPrice(p.variations[0]) : bestPrice(p);

        // ✅ FIXED Image Path exactly like Order Create
        let primaryImgUrl = 'https://via.placeholder.com/50?text=No+Image';
        let fallbackImgUrl = 'https://via.placeholder.com/50?text=No+Image';

        let imgStr = p.image_url || p.image || '';

        if (imgStr) {
            if (imgStr.startsWith('http://') || imgStr.startsWith('https://')) {
                primaryImgUrl = imgStr;
                fallbackImgUrl = imgStr;
            } else {
                primaryImgUrl = '/products/' + imgStr;
                fallbackImgUrl = '/uploads/products/' + imgStr;
            }
        }

        // ✅ Variant Dropdown Logic Added
        const varSelectHtml = hasVar
            ? `<select class="form-control variant-select" name="variation_id[]" style="min-width: 120px;">
                ${p.variations.map(v => {
                    const vPrice = bestPrice(v);
                    const title  = v.title || v.text || `${v.size || v.size_label || ''}${(v.color || v.color_label) ? ' - ' + (v.color || v.color_label) : ''}` || 'Default';
                    return `<option value="${v.id}" data-price="${vPrice}">${escapeHtml(title)}</option>`;
                }).join('')}
               </select>`
            : `<input type="hidden" name="variation_id[]" value="0"/><span class="text-muted" style="font-size:12px;">Default</span>`;

        const row = $(`
            <tr>
                <td>
                    <img src="${primaryImgUrl}" 
                         onerror="this.onerror=null; this.src='${fallbackImgUrl}'; if(this.src.includes('undefined')) this.src='https://via.placeholder.com/50?text=No+Image';" 
                         height="50" width="50" style="object-fit:cover; border: 1px solid #eee;"/>
                </td>
                <td style="white-space: normal; min-width: 200px;">
                    ${escapeHtml(p.name)}
                    <input type="hidden" name="product_id[]" value="${p.id}"/>
                </td>
                <td>${varSelectHtml}</td>
                <td><input class="form-control quantity" name="quantity[]" type="number" value="1" required min="1"/></td>
                <td><input class="form-control unit_price" name="unit_price[]" type="number" step="0.01" value="${defPrice}"/></td>
                <td><input class="form-control unit_discount" name="unit_discount[]" type="number" value="0" disabled style="background-color:#f8fafc;"/></td>
                <td class="row_total fw-bold" style="font-size:14px; color:#0f172a;">${parseFloat(defPrice).toFixed(2)}</td>
                <td><a class="remove btn btn-sm btn-danger text-white"><i class="mdi mdi-delete"></i></a></td>
            </tr>
        `);
        
        $('#data').prepend(row);
        recalcTotal();
    }

    // Variant change
    $(document).on('change', '.variant-select', function(){
        const price = parseFloat($(this).find('option:selected').data('price')) || 0;
        if(price > 0){
            $(this).closest('tr').find('.unit_price').val(price);
        }
        recalcTotal();
    });

    $('#product_table').on('input', '.quantity, .unit_price', function(){
        recalcTotal();
    });

    $('#product_table').on('click', '.remove', function(){
        $(this).closest('tr').remove();
        recalcTotal();
    });

    function recalcTotal(){
        let total = 0;
        $('#product_table tbody tr').each(function(){
            const qty = parseFloat($(this).find('.quantity').val() || 0);
            const price = parseFloat($(this).find('.unit_price').val() || 0);
            const discount = parseFloat($(this).find('.unit_discount').val() || 0); // Currently disabled, but logic is ready
            
            const rowTotal = (qty * price);
            $(this).find('.row_total').text(rowTotal.toFixed(2));
            
            total += rowTotal;
        });
        $('#purchase_total').val(total.toFixed(2));
    }
});
</script>
@endpush