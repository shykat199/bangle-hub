@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />

<style>
    :root{
        --ink:#0f172a;
        --muted:#6b7280;
        --soft:#f9fafb;
        --card:#ffffff;
        --border:#e5e7eb;
        --primary:#0ea5e9;
        --primary-soft:rgba(14,165,233,.08);
        --danger:#ef4444;
        --warning:#f59e0b;
    }

    body{ background:var(--soft); }
    .page-title-box{ margin-bottom: 10px; }
    .page-title-box h4{ font-weight: 700; color: var(--ink); }
    .order-layout{ max-width: 1180px; margin: 0 auto; }
    .order-card{ border: 0; border-radius: 20px; box-shadow: 0 10px 30px rgba(15,23,42,.08); overflow: hidden; }
    .order-card .card-body{ background: linear-gradient(to bottom, #f9fafb, #ffffff); padding: 20px 18px 22px; }
    @media (min-width: 768px){ .order-card .card-body{ padding: 28px 26px 26px; } }

    .section-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom: 10px; margin-top: 8px; }
    .section-title{ font-size: 15px; font-weight: 600; letter-spacing:.02em; color: var(--ink); display:flex; align-items:center; gap:8px; }
    .section-title span.badge-dot{ width:6px;height:6px; border-radius:999px; background:var(--primary); box-shadow:0 0 0 4px var(--primary-soft); }
    .section-sub{ font-size: 12px; color: var(--muted); }
    .section-box{ background:var(--card); border-radius:16px; border:1px solid var(--border); padding:14px 12px; margin-bottom: 14px; }
    @media (min-width: 768px){ .section-box{ padding:16px 16px; } }

    .form-label{ font-size: 12px; font-weight: 500; color: var(--muted); margin-bottom: 4px; }
    .form-control, .form-select, select.form-control{ border-radius: 10px !important; border:1px solid var(--border); font-size: 13px; padding: .4rem .65rem; }
    .form-control:focus, .form-select:focus, select.form-control:focus{ border-color: var(--primary); box-shadow: 0 0 0 .18rem var(--primary-soft); }
    textarea.form-control{ min-height:80px; resize: vertical; }

    .search-wrapper{ margin-top: 6px; }
    .search-card{ background: var(--card); border-radius: 16px; border:1px solid var(--border); padding: 10px 12px 12px; }
    .search-input{ position: relative; }
    .search-input input{ border-radius:999px !important; padding-left: 40px; font-size: 14px; }
    .search-input::before{ content:'🔍'; position:absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 14px; opacity:.65; pointer-events:none; }

    /* 🪄 MAGIC FILL CSS */
    .magic-box-wrapper { background: #fffbeb; border: 2px dashed #fcd34d; border-radius: 16px; padding: 15px; margin-bottom: 15px; position: relative; transition: all 0.3s ease; }
    .magic-box-wrapper:hover { border-color: #f59e0b; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.1); }
    .magic-box-title { font-weight: 700; color: #b45309; font-size: 14px; display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
    .magic-input { background: #fff; border: 1px solid #fde68a !important; border-radius: 12px !important; font-size: 14px; min-height: 60px !important; }
    .magic-input:focus { border-color: #f59e0b !important; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1) !important; }
    #btnMagicFill:active { transform: scale(0.95); }

    .table-wrapper{ margin-top: 10px; }
    .table-card{ background: var(--card); border-radius: 16px; border:1px solid var(--border); padding: 10px 10px 4px; }
    .table-responsive{ border-radius: 12px; }
    #product_table thead{ font-size: 12px; }
    #product_table tbody td{ vertical-align: middle; font-size: 13px; }
    #product_table img{ max-width:48px; border-radius:10px; }
    .quantity, .unit_price, .unit_discount{ max-width: 90px; padding-inline: 6px; font-size: 12px; }
    .row_total{ font-weight: 600; }
    .btn-remove-row{ border-radius: 999px; padding: 3px 7px; font-size: 11px; }

    @media (max-width: 767.98px){
        .section-box .row > [class^="col-"]{ margin-bottom: 8px; }
        .table-card{ padding: 8px; }
        .table-responsive{ border:0; }
        #product_table{ min-width:650px; }
        .btn.btn-success{ width: 100%; }
    }

    .redx-title, .pathao-title{ font-size: 13px; font-weight:600; color: var(--danger); margin-bottom: 8px; }
    .note-area textarea{ border-radius: 12px !important; }
    .footer-actions{ display:flex; justify-content:flex-end; margin-top: 6px; }
    @media (max-width: 575.98px){ .footer-actions{ justify-content:stretch; } }
    .btn-save-order{ border-radius: 999px; padding-inline: 26px; font-weight: 600; letter-spacing:.03em; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Order Create</li>
                </ol>
            </div>
            <h4 class="page-title">Order Create</h4>
        </div>
    </div>
</div>

<div class="row order-layout">
    <div class="col-12">
        <div class="card order-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.store')}}" id="ajax_form">
                    @csrf

                    {{-- Order Info --}}
                    <div class="section-header">
                        <div class="section-title"><span class="badge-dot"></span> Order Information</div>
                        <div class="section-sub">Date, status & basic order details</div>
                    </div>
                    <div class="section-box">
                        <div class="row g-2">
                            <div class="col-md-4 col-12">
                                <label class="form-label">Pick a Date</label>
                                <input type="date" class="form-control" required name="date" value="{{ date('Y-m-d') }}"/>
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label">Order Status</label>
                                <select class="form-control" name="status">
                                    @foreach($status as $key=>$s)
                                        @if($key === '') @continue @endif
                                        <option value="{{$key}}" {{ $key === 'Pending' ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Product Search --}}
                    <div class="search-wrapper">
                        <div class="section-header">
                            <div class="section-title"><span class="badge-dot"></span> Products</div>
                        </div>
                        <div class="search-card">
                            <div class="row justify-content-center">
                                <div class="col-md-8 col-12">
                                    <div class="search-input">
                                        <input type="text" id="search" class="form-control" placeholder="Search product by name or SKU...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Product Table --}}
                    <div class="table-wrapper">
                        <div class="table-card">
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0" id="product_table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Product</th>
                                            <th colspan="2">Variant</th>
                                            <th style="width: 120px;">Quantity</th>
                                            <th style="width: 150px;">Sell Price</th>
                                            <th style="width: 150px;">Discount</th>
                                            <th>Subtotal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="data"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- 🪄 NEW: MAGIC FILL AI BOX --}}
                    <div class="section-header mt-4">
                        <div class="section-title">
                            <span class="badge-dot" style="background: var(--warning); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);"></span>
                            Quick Paste (Magic Fill)
                        </div>
                    </div>
                    <div class="magic-box-wrapper shadow-sm">
                        <div class="magic-box-title">
                            <i class="mdi mdi-magic-staff fs-5"></i> Paste Customer Details Here
                        </div>
                        <div class="d-flex gap-2">
                            <textarea id="magicPasteBox" class="form-control magic-input" placeholder="e.g. রহিম, 01712345678, মিরপুর ১০, ঢাকা" rows="2"></textarea>
                            <button type="button" class="btn btn-warning text-dark fw-bold px-4 shadow-sm" id="btnMagicFill" style="border-radius: 12px; font-size:15px; border: 2px solid #f59e0b;">
                                <i class="mdi mdi-auto-fix fs-3 mb-1 d-block"></i> Fill
                            </button>
                        </div>
                    </div>

                    {{-- Customer & Courier --}}
                    <div class="section-header mt-3">
                        <div class="section-title"><span class="badge-dot"></span> Customer & Courier</div>
                    </div>
                    <div class="section-box">
                        <div class="row g-2">
                            <div class="col-md-3 col-12">
                                <label class="form-label">Courier</label>
                                <select class="form-control" name="courier_id">
                                    <option value="" data-charge="0">Select One</option>
                                    @foreach($couriers as $courier)
                                        <option value="{{ $courier->id }}"> {{ $courier->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Customer Name</label>
                                <input type="text" class="form-control" name="first_name" id="c_name" />
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Customer Mobile</label>
                                <input type="text" class="form-control" name="mobile" id="c_mobile" />
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label">Customer Address</label>
                                <textarea rows="3" name="shipping_address" id="c_address" class="form-control"></textarea>
                            </div>
                        </div>

                        {{-- Redx --}}
                        <div class="row for_redx d-none mt-2">
                            <div class="col-12 redx-title">These fields only for Redx</div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Choose Area</label>
                                <select class="form-control select2" id="area_select">
                                    <option value="">Select One</option>
                                    @foreach($areas as $key=>$area)
                                        <option value="{{ $area['id'] }}">{{ $area['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Area ID</label>
                                <input type="text" readonly class="form-control" id="area_id" name="area_id" value=""/>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Area Name</label>
                                <input type="text" readonly class="form-control" id="area_name" name="area_name" value=""/>
                            </div>
                        </div>

                        {{-- Pathao --}}
                        <div class="row for_pathao d-none mt-2">
                            <div class="col-12 pathao-title">These fields only for Pathao</div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Choose City</label>
                                <select class="form-control select2" id="city_select" name="city">
                                    <option value="">Select One</option>
                                    @foreach($cities as $key=>$city)
                                        <option value="{{ $city['city_id'] }}">{{ $city['city_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Choose Zone</label>
                                <select class="form-control select2" id="zone_select" name="state">
                                    <option value="">Select One</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Choose Area</label>
                                <select class="form-control select2" name="area_id" id="pathao_area_select">
                                    <option value="">Select One</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Item Weight</label>
                                <input type="number" class="form-control" id="weight" step="0.5" min="0.5" max="10" name="weight" value="0.5"/>
                            </div>
                        </div>
                    </div>

                    {{-- Payment & Total --}}
                    <div class="section-header mt-2">
                        <div class="section-title"><span class="badge-dot"></span> Payment Summary</div>
                    </div>
                    <div class="section-box">
                        <div class="row g-2">
                            <div class="col-md-3 col-12">
                                <label class="form-label">Delivery Charge</label>
                                <select class="form-control" name="delivery_charge_id" id="delevery_charge">
                                    <option value="" data-charge="0">Select One</option>
                                    @foreach($charges as $charge)
                                        <option value="{{ $charge->id }}" data-charge="{{ $charge->amount }}">{{ $charge->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Payment Method</label>
                                <select class="form-control" name="payment_method" id="payment_method">
                                    <option value="Cash on Delivery" selected>Cash on Delivery</option>
                                    <option value="bKash">bKash</option>
                                    <option value="Nagad">Nagad</option>
                                    <option value="Rocket">Rocket</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Total</label>
                                <input type="text" class="form-control" name="final_amount" id="purchase_total" readonly />
                                <input type="hidden" value="0" name="shipping_charge" id="shipping_charge" />
                                <input type="hidden" name="discount" id="discount_amount" value="0" />
                            </div>
                            <div class="col-md-3 col-12 d-none" id="sender_number_wrap">
                                <label class="form-label">Sender Number</label>
                                <input type="text" class="form-control" name="sender_number" id="sender_number" placeholder="017XXXXXXXX" />
                            </div>
                            <div class="col-md-3 col-12 d-none" id="transaction_id_wrap">
                                <label class="form-label">Transaction ID</label>
                                <input type="text" class="form-control" name="transaction_id" id="transaction_id" placeholder="Trx ID" />
                            </div>
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="section-header mt-2">
                        <div class="section-title"><span class="badge-dot"></span> Note</div>
                    </div>
                    <div class="section-box note-area">
                        <textarea class="form-control" name="note" id="c_note" placeholder="Write any special instruction..."></textarea>
                    </div>

                    <div class="footer-actions">
                        <button class="btn btn-success btn-save-order" type="submit">Save Order</button>
                    </div>

                </form>
            </div> 
        </div> 
    </div> 
</div> 
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
$(function(){
    $('#payment_method').on('change', function(){
        var isCod = $(this).val() === 'Cash on Delivery';
        $('#sender_number_wrap, #transaction_id_wrap').toggleClass('d-none', isCod);
        if (isCod) $('#sender_number, #transaction_id').val('');
    }).trigger('change');

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

    // Product Search Autocomplete
    $("#search").autocomplete({
        minLength: 2,
        source: function(request, response){
            $.getJSON("{{ route('admin.products.search') }}", { q: request.term }, function(list){
                if(list.length === 0){ toastr.error('Product Or Stock Not Found'); }
                response($.map(list, function(p){
                    return { label: (p.sku ? '['+p.sku+'] ' : '') + p.name, value: p.name, data : p };
                }));
            });
        },
        select: function(e, ui){
            e.preventDefault();
            addProductRow(ui.item.data);
            $(this).val('');
        }
    });

    // Add product to table
    function addProductRow(p){
        const hasVar     = Array.isArray(p.variations) && p.variations.length > 0;
        const defPrice   = hasVar ? bestPrice(p.variations[0]) : bestPrice(p);
        
        let primaryImgUrl = 'https://via.placeholder.com/50?text=No+Image';
        let fallbackImgUrl = 'https://via.placeholder.com/50?text=No+Image';

        let imgStr = p.image_url || p.image || '';

        if (imgStr) {
            if (imgStr.startsWith('http://') || imgStr.startsWith('https://')) {
                primaryImgUrl = imgStr; fallbackImgUrl = imgStr;
            } else {
                primaryImgUrl = '/products/' + imgStr; fallbackImgUrl = '/uploads/products/' + imgStr;
            }
        }

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
                         height="50" width="50" style="border-radius: 8px; object-fit:cover; border: 1px solid #eee;"/>
                </td>
                <td style="white-space: normal; min-width: 200px;">
                    ${escapeHtml(p.name)}
                    <input type="hidden" name="product_id[]" value="${p.id}"/>
                </td>
                <td colspan="2">${varSelectHtml}</td>
                <td>
                    <input class="form-control quantity" name="quantity[]" type="number" value="1" required min="1" style="max-width:90px;"/>
                </td>
                <td><input class="form-control unit_price" name="unit_price[]" type="number" value="${defPrice}" step="0.01" required style="max-width:90px;"/></td>
                <td><input class="form-control unit_discount" name="unit_discount[]" type="number" value="0" disabled style="max-width:90px; background-color:#f8fafc;"/></td>
                <td class="row_total fw-bold" style="font-size:14px; color:#0f172a;">${parseFloat(defPrice).toFixed(2)}</td>
                <td><a class="remove btn btn-sm btn-danger text-white"><i class="mdi mdi-delete"></i></a></td>
            </tr>
        `);

        $('tbody#data').prepend(row);
        calculateSum();
    }

    $(document).on('change', '.variant-select', function(){
        const price = parseFloat($(this).find('option:selected').data('price')) || 0;
        if(price > 0) $(this).closest('tr').find('.unit_price').val(price);
        calculateSum();
    });

    $(document).on('input', '.quantity, .unit_price', function(){ calculateSum(); });
    $(document).on('click', '.remove', function(){ $(this).closest('tr').remove(); calculateSum(); });

    function calculateSum(){
        let sub_total = 0; let row_discount = 0;
        let charge = Number($("#delevery_charge option:selected").data('charge')) || 0;

        $("#product_table tbody tr").each(function(){
            let qty = Number($(this).find('.quantity').val()) || 0;
            let price = Number($(this).find('.unit_price').val()) || 0;
            let discount = Number($(this).find('.unit_discount').val()) || 0; 
            let row_total = qty * price;
            
            row_discount += (qty * discount);
            $(this).find('.row_total').text(row_total.toFixed(2));
            sub_total += row_total;
        });

        sub_total += charge;
        $('#purchase_total').val(sub_total.toFixed(2));
        $('#discount_amount').val(row_discount.toFixed(2));
        $('#shipping_charge').val(charge.toFixed(2));
    }

    $('#delevery_charge').on('change', calculateSum);

    $('select[name="courier_id"]').on('change', function(){
        let courier_id = $(this).val();
        if(courier_id == 1){
            $('.for_redx').removeClass('d-none'); $('.for_pathao').addClass('d-none');
        }else if(courier_id == 2){
            $('.for_pathao').removeClass('d-none'); $('.for_redx').addClass('d-none');
        }else{
            $('.for_pathao, .for_redx').addClass('d-none');
        }
    });

    $(document).on('change', '#city_select', function(){
        let city = $(this).val();
        if(!city) return;
        $.ajax({
            url: "{{ url('/admin/zones-by-city') }}/" + city,
            type: 'GET', dataType: "json",
            success: function(res){
                if(res.success){
                    let html = "<option value=''>Select One</option>";
                    for(let i = 0; i < res.zones.length; i++){ html += "<option value='"+res.zones[i].zone_id+"' >"+res.zones[i].zone_name+"</option>"; }
                    $('#zone_select').html(html);
                }
            }
        });
    });

    $(document).on('change', '#zone_select', function(){
        let zone = $(this).val();
        if(!zone) return;
        $.ajax({
            url: "{{ url('/admin/areas-by-zone') }}/" + zone,
            type: 'GET', dataType: "json",
            success: function(res){
                if(res.success){
                    let html = "<option value=''>Select One</option>";
                    for(let j = 0; j < res.areas.length; j++){ html += "<option value='"+res.areas[j].area_id+"' >"+res.areas[j].area_name+"</option>"; }
                    $('select[name="area_id"]').html(html);
                }
            }
        });
    });

    $(document).on('change', '#area_select', function(){
        $('#area_id').val($(this).val() || '');
        $('#area_name').val($(this).find('option:selected').text() || '');
    });

    // ==========================================
    // 🪄 MAGIC FILL AI - SUPER ROBUST VERSION (BUG FIXED)
    // ==========================================
    window.fetchAddressDetails = function(addressText) {
        if(!addressText) return;
        $.ajax({
            url: "{{ route('admin.fetch.address.details') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}", address: addressText },
            success: function(response) {
                if(response.city_id) {
                    $('#city_select').val(response.city_id).trigger('change');
                    if(typeof toastr !== 'undefined') toastr.info('Pathao City Matched!');
                }
            },
            error: function() {
                console.log("Auto Area Fetch Failed.");
            }
        });
    }

    $(document).on('click', '#btnMagicFill', function(e) {
        e.preventDefault();
        let text = $('#magicPasteBox').val();
        
        // যদি বক্স খালি থাকে
        if(text.trim().length === 0) {
            if(typeof toastr !== 'undefined') toastr.warning('Please paste text first!');
            else alert('Please paste text first!');
            return;
        }

        // ১. মোবাইল নাম্বার আলাদা করা
        let phoneRegex = /(?:\+88|88)?(01[3-9]\d{2}[- ]?\d{6})/g;
        let phoneMatch = text.match(phoneRegex);
        
        if(phoneMatch && phoneMatch.length > 0) {
            let cleanPhone = phoneMatch[0].replace(/[- ]/g, ''); 
            $('#c_mobile').val(cleanPhone).css({'background-color':'#dcfce7', 'transition':'0.5s'});
            text = text.replace(phoneMatch[0], ''); 
            setTimeout(() => $('#c_mobile').css('background-color','#fff'), 1000);
        }

        // ২. অপ্রয়োজনীয় শব্দ মুছে ফেলা
        text = text.replace(/(Name|Mobile|Phone|Address|ঠিকানা|নাম|মোবাইল|ফোন|Contact)[:,-]?/gi, '').trim();

        // ৩. নাম আলাদা করা
        let parts = text.split(/[,\n-]/); 
        let extractedName = parts[0].trim();
        
        if(extractedName.length > 0 && extractedName.length < 50) {
            $('#c_name').val(extractedName).css({'background-color':'#dcfce7', 'transition':'0.5s'});
            text = text.replace(parts[0], ''); 
            setTimeout(() => $('#c_name').css('background-color','#fff'), 1000);
        }

        // ৪. ঠিকানা আলাদা করা (✅ REGEX BUG FIXED HERE)
        let extractedAddress = text.replace(/^[\s,\n-]+/, '').trim(); 
        
        if(extractedAddress.length > 0) {
            $('#c_address').val(extractedAddress).css({'background-color':'#dcfce7', 'transition':'0.5s'});
            setTimeout(() => $('#c_address').css('background-color','#fff'), 1000);

            // Pathao এরিয়া অটো সিলেক্ট করা
            if(typeof window.fetchAddressDetails === 'function'){
                window.fetchAddressDetails(extractedAddress);
            }
        }

        // ৫. কাজ শেষ, বক্স ক্লিয়ার করুন
        $('#magicPasteBox').val('');
        if(typeof toastr !== 'undefined') {
            toastr.success('✨ Magic Fill Applied!'); 
        } else {
            alert('✨ Magic Fill Applied!');
        }
    });

});
</script>
@endpush