<style>
    .drawer-form-label { font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 2px; text-transform: uppercase; }
    .drawer-form-control { font-size: 12px; padding: 6px 10px; border-radius: 6px; border-color: #cbd5e1; }
    .drawer-form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
    .drawer-section { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px; }
    .drawer-product-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .select2-container--default .select2-selection--single { border: 1px solid #cbd5e1; border-radius: 6px; height: 32px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 30px; font-size: 12px; }
    .ui-autocomplete { z-index: 9999999 !important; max-height: 200px; overflow-y: auto; font-size: 12px; }
</style>

<form id="drawer_edit_form">
    @csrf
    @method('PUT')
    
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2" style="border-bottom: 1px dashed #cbd5e1;">
        <h6 class="m-0 fw-bold text-primary">Invoice #{{ $order->invoice_no }}</h6>
        <span class="badge bg-light text-dark border"><i class="mdi mdi-clock-outline"></i> {{ $order->created_at->format('d M, h:i A') }}</span>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-6">
            <label class="drawer-form-label">Order Status</label>
            <select class="form-select drawer-form-control" name="status">
                {{-- '' (All Order) is a filter label, not a real status — never offer it while editing --}}
                @foreach($status as $key => $s)
                    @if($key === '') @continue @endif
                    <option value="{{ $key }}" {{ strcasecmp($key, trim($order->status ?? '')) === 0 ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6">
            <label class="drawer-form-label">Order Date</label>
            <input type="date" class="form-control drawer-form-control" value="{{ $order->date ?? $order->created_at->format('Y-m-d') }}" name="date" required/>
        </div>
    </div>

    <div class="drawer-section">
        <div class="row g-2">
            <div class="col-12">
                <label class="drawer-form-label">Customer Name</label>
                <input type="text" class="form-control drawer-form-control" value="{{ trim($order->first_name.' '.$order->last_name) }}" name="first_name" required/>
                {{-- Drawer shows the FULL name in one box (first + last combined). Post an empty last_name
                     so the full name is stored only in first_name; otherwise last_name re-appends on every
                     save and the customer name keeps growing ("John Doe" -> "John Doe Doe" -> ...). --}}
                <input type="hidden" name="last_name" value=""/>
            </div>
            <div class="col-6">
                <label class="drawer-form-label">Mobile Number</label>
                <input type="text" class="form-control drawer-form-control" value="{{ $order->mobile }}" name="mobile" required/>
            </div>
            <div class="col-6">
                <label class="drawer-form-label">IP Address</label>
                <input type="text" class="form-control drawer-form-control bg-light" value="{{ $order->ip_address ?? 'N/A' }}" readonly/>
            </div>
            <div class="col-12">
                <label class="drawer-form-label">Shipping Address</label>
                <textarea rows="2" name="shipping_address" id="drawer_shipping_address" class="form-control drawer-form-control" required>{{ $order->shipping_address }}</textarea>
            </div>
        </div>
    </div>

    @php
        $rawPaymentMethod = $order->payment_method ?? '';
        $paymentMethod = $rawPaymentMethod;
        if (strtolower($rawPaymentMethod) == 'eps') $paymentMethod = 'EPS';
        elseif (strtolower($rawPaymentMethod) == 'nagad') $paymentMethod = 'Nagad';

        $transactionId = $order->transaction_id ?? '';
        $paymentRecord = \App\Models\OrderPayment::where('order_id', $order->id)->latest()->first();
        
        if ($paymentRecord) {
            $recordMethod = strtolower($paymentRecord->payment_method);
            if (str_contains($recordMethod, 'bkash')) {
                $paymentMethod = 'bKash';
                $transactionId = $paymentRecord->transaction_id;
            } elseif (str_contains($recordMethod, 'nagad')) {
                $paymentMethod = 'Nagad';
                $transactionId = $paymentRecord->transaction_id;
            }
        }
    @endphp

    @if($paymentMethod != 'Cash on Delivery' && $paymentMethod != 'online' && $paymentMethod != null)
    <div class="drawer-section mt-2" style="border: 1px solid #17a2b8; background: #f0f9fa;">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="fw-bold text-info m-0" style="font-size: 11px; text-transform: uppercase;">
                <i class="mdi mdi-cash-multiple"></i> Payment Info
            </h6>
        </div>
        <table class="table table-sm table-borderless mb-0 mt-2" style="font-size: 12px;">
            <tr>
                <td width="40%" class="py-1 px-0 text-muted fw-bold">Payment Method:</td>
                <td class="py-1 px-0 fw-bold">{{ $paymentMethod }}</td>
            </tr>
            @if($order->sender_number)
            <tr>
                <td class="py-1 px-0 text-muted fw-bold">Sender Number:</td>
                <td class="py-1 px-0">{{ $order->sender_number }}</td>
            </tr>
            @endif
            <tr>
                <td class="py-1 px-0 text-muted fw-bold">Transaction ID:</td>
                <td class="py-1 px-0"><span class="badge bg-success">{{ $transactionId }}</span></td>
            </tr>
        </table>
    </div>
    @endif

    <div class="drawer-section mt-2" style="border: 1px solid #cce5ff; background: #f8fbff; margin-bottom: 10px;">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="fw-bold text-primary m-0" style="font-size: 11px; text-transform: uppercase;">
                <i class="mdi mdi-history"></i> Previous Order History
            </h6>
        </div>
        <div id="drawer_customer_history" style="max-height: 250px; overflow-y: auto;">
            <div class="text-center text-muted" style="font-size: 11px; padding: 10px 0;">
                <i class="mdi mdi-spin mdi-loading fs-5"></i><br>
                Loading history...
            </div>
        </div>
    </div>

    <div class="drawer-section mt-2" style="border: 1px dashed #f5c6cb; background: #fffafb;">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="fw-bold text-danger m-0" style="font-size: 11px; text-transform: uppercase;">
                <i class="mdi mdi-shield-alert"></i> Courier History (Fraud Check)
            </h6>
        </div>
        <div id="drawer_fraud_result">
            <div class="text-center text-muted" style="font-size: 11px; padding: 10px 0;">
                <i class="mdi mdi-spin mdi-loading fs-5"></i><br>
                Fetching data from API...
            </div>
        </div>
    </div>

    <h6 class="fw-bold text-muted mb-2" style="font-size: 11px; text-transform: uppercase;">Products</h6>
    <div class="mb-2 position-relative">
        <input type="text" id="drawer_product_search" class="form-control drawer-form-control" placeholder="🔍 Search to add product...">
    </div>

    <div id="drawer_product_list">
        @foreach($order->details as $line)
            @php
                $product   = $line->product;
                $variants  = $product->variations ?? collect();
                $selectedV = $line->variation;
                $priceNow  = $line->unit_price;
                $lineTotal = $priceNow * $line->quantity;
            @endphp
            <div class="drawer-product-card drawer-product-row">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex gap-2">
                        <img src="{{ function_exists('getImage') ? getImage('products', $product?->image ?? 'default-product.png') : asset('uploads/products/'.($product?->image ?? 'default-product.png')) }}" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 12px; line-height: 1.2;">{{ $product?->name ?? 'Product Not Found' }}</div>
                            <div class="text-muted" style="font-size: 10px;">SKU: {{ $product?->sku ?? 'N/A' }}</div>
                            <!-- ✅ Free Shipping Badge & Input -->
                            @if(isset($product) && $product->is_free_shipping == 1)
                                <span class="badge bg-success mt-1" style="font-size: 9px;"><i class="mdi mdi-truck-fast"></i> Free</span>
                            @endif
                            <input type="hidden" class="drawer_is_free_shipping" value="{{ $product?->is_free_shipping ?? 0 }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm text-danger p-0 remove-drawer-product"><i class="mdi mdi-close-circle fs-5"></i></button>
                </div>
                
                <div class="row g-2 align-items-end">
                    <div class="col-12">
                        @if($variants->count() > 0)
                            <select class="form-select drawer-form-control drawer-variant-select" name="variant_display[]">
                                @foreach($variants as $v)
                                    @php
                                        $text = $v->display_title ?? trim(($v->size_label ?? $v->size ?? '') . (($v->color_label ?? $v->color ?? '') ? ' - ' . ($v->color_label ?? $v->color) : '')) ?: 'Default';
                                        $vPrice = $v->discount_price ?? $v->price ?? $priceNow;
                                    @endphp
                                    <option value="{{ $v->id }}" data-price="{{ $vPrice }}" {{ $selectedV && $selectedV->id === $v->id ? 'selected' : '' }}>
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="text-muted" style="font-size:11px;">No Variants</div>
                        @endif
                        <input type="hidden" name="variation_id[]" class="drawer-hidden-variation-id" value="{{ $line->variation_id }}"/>
                        <input type="hidden" name="order_line_id[]" value="{{ $line->id }}"/>
                        <input type="hidden" name="product_id[]" value="{{ $line->product_id }}"/>
                        <input type="hidden" name="is_stock" value="{{ $product?->is_stock ?? 1 }}">
                    </div>
                    
                    <div class="col-4">
                        <label class="drawer-form-label">Qty</label>
                        <input type="number" class="form-control drawer-form-control drawer-qty" name="quantity[]" value="{{ $line->quantity }}" min="1" required>
                    </div>
                    <div class="col-4">
                        <label class="drawer-form-label">Price</label>
                        <input type="number" class="form-control drawer-form-control drawer-price" name="unit_price[]" value="{{ $priceNow }}" step="0.01" required>
                    </div>
                    <div class="col-4 text-end pb-1">
                        <div class="fw-bold text-dark">৳ <span class="drawer-row-total">{{ number_format($lineTotal, 2, '.', '') }}</span></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="drawer-section mt-3">
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="drawer-form-label">Delivery Charge</label>
                <select class="form-select drawer-form-control" name="delivery_charge_id" id="drawer_delivery_charge">
                    <option value="" data-charge="0">Select One</option>
                    @foreach($charges as $charge)
                        <option value="{{ $charge->id }}" {{ $charge->id == $order->delivery_charge_id ? 'selected' : '' }} data-charge="{{ $charge->amount }}">{{ $charge->title }} (৳{{$charge->amount}})</option>
                    @endforeach
                </select>
                <!-- ✅ Free Shipping Notice Under Select Box -->
                <div id="drawer_free_shipping_notice" style="display: none; font-size: 11px; color: #10b981; font-weight: bold; margin-top: 4px;">
                    <i class="mdi mdi-check-circle"></i> Free Shipping Applied (৳0)
                </div>
                <input type="hidden" value="{{ $order->shipping_charge ?? 0 }}" name="shipping_charge" id="drawer_shipping_charge_val"/>
            </div>
            <div class="col-6">
                <label class="drawer-form-label">Courier Service</label>
                <select class="form-select drawer-form-control" name="courier_id" id="drawer_courier_select">
                    <option value="" data-charge="0">Select One</option>
                    @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}" {{ $courier->id == $order->courier_id ? 'selected' : '' }}>{{ $courier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- এই দুটো আগে শুধু Edit পেজে ছিল — এখন drawer থেকেও দেওয়া যায় --}}
        <div class="row g-2 mt-1">
            <div class="col-8">
                <label class="drawer-form-label">Courier Tracking ID</label>
                <input type="text" class="form-control drawer-form-control" name="courier_tracking_id" value="{{ $order->courier_tracking_id }}" placeholder="Tracking / Consignment ID">
            </div>
            <div class="col-4">
                <label class="drawer-form-label">Weight (kg)</label>
                <input type="number" step="0.01" class="form-control drawer-form-control" name="weight" value="{{ $order->weight }}" placeholder="0.5">
            </div>
        </div>

        <div class="row g-2 mt-1 drawer-for-redx {{ $order->courier_id != 1 ? 'd-none' : '' }}">
            <div class="col-12">
                <label class="drawer-form-label text-danger">Redx Area</label>
                <select class="form-control select2-drawer" id="drawer_area_select" style="width: 100%;">
                    <option value="">Select Area</option>
                    @if($areas!==null)
                        @foreach($areas as $area)
                            <option value="{{ $area['id'] }}" {{ $order->area_id == $area['id'] ? 'selected' : '' }}>{{ $area['name'] }}</option>
                        @endforeach
                    @endif
                </select>
                <input type="hidden" id="drawer_area_id" name="redx_area_id" value="{{ $order->area_id }}"/>
                <input type="hidden" id="drawer_area_name" name="area_name" value="{{ $order->area_name }}"/>
            </div>
        </div>

        <div class="drawer-for-pathao {{ $order->courier_id != 2 ? 'd-none' : '' }} mt-2">
            <label class="drawer-form-label text-danger">Pathao Delivery Info</label>
            <div class="row g-2">
                <div class="col-12">
                    <select class="form-select drawer-form-control" id="drawer_city_select" name="city">
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                            <option value="{{ $city['city_id'] }}" {{ $order->city == $city['city_id'] ? 'selected' : '' }}>{{ $city['city_name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <select class="form-select drawer-form-control" id="drawer_zone_select" name="state">
                        <option value="{{ $order->state }}">Zone Selected</option>
                    </select>
                </div>
                <div class="col-6">
                    <select class="form-select drawer-form-control" id="drawer_pathao_area_id" name="pathao_area_id">
                        <option value="{{ $order->area_id }}">Area Selected</option>
                    </select>
                </div>
            </div>
        </div>
        
        @php
            $itemsTotal = 0;
            foreach($order->details as $line) {
                $itemsTotal += ($line->unit_price * $line->quantity);
            }
            $actual_coupon_discount = ($itemsTotal + ($order->shipping_charge ?? 0)) - $order->final_amount;
            if($actual_coupon_discount < 0) $actual_coupon_discount = 0;
        @endphp

        <div class="row g-2 mt-2">
            <div class="col-6">
                <label class="drawer-form-label text-danger">Discount (৳)</label>
                <input type="number" class="form-control drawer-form-control" name="discount" id="drawer_discount" value="{{ round($actual_coupon_discount, 2) }}" step="0.01">
            </div>
            <div class="col-6">
                <label class="drawer-form-label">Note (Optional)</label>
                <textarea class="form-control drawer-form-control" name="note" rows="1">{{ $order->note }}</textarea>
            </div>
        </div>

        <hr style="border-color: #cbd5e1; margin: 15px 0;">
        
        <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
            <span class="text-muted">Subtotal:</span>
            <span class="fw-bold">৳ <span id="drawer_sub_total">{{ number_format($itemsTotal, 2, '.', '') }}</span></span>
        </div>
        <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
            <span class="text-muted">Delivery Charge:</span>
            <span class="fw-bold">+ ৳ <span id="drawer_delivery_display">{{ number_format($order->shipping_charge ?? 0, 2, '.', '') }}</span></span>
        </div>
        <div class="d-flex justify-content-between mb-2" style="font-size: 13px;">
            <span class="text-danger fw-bold">Discount:</span>
            <span class="text-danger fw-bold">- ৳ <span id="drawer_discount_display">{{ number_format($actual_coupon_discount, 2, '.', '') }}</span></span>
        </div>
        
        <hr class="my-2">

        <div class="d-flex justify-content-between align-items-center">
            <div class="text-dark fw-bold" style="font-size: 14px;">Grand Total</div>
            <div class="fs-4 fw-bold text-primary">৳ <span id="drawer_grand_total">{{ (int)$order->final_amount }}</span></div>
            <input type="hidden" name="final_amount" id="drawer_final_amount_input" value="{{ $order->final_amount }}">
        </div>
    </div>

    <div class="mt-3">
        <button type="button" onclick="submitOrderForm(this)" class="btn btn-success w-100 fw-bold shadow-sm" style="height: 45px; border-radius: 8px;">
            <i class="mdi mdi-content-save"></i> Save Changes
        </button>
    </div>
</form>

<script>
    window.submitOrderForm = function(btnElem) {
        let form = $('#drawer_edit_form');
        let btn = $(btnElem);
        
        if(form.find('.drawer-product-row').length === 0) {
            if (typeof toastr !== 'undefined') toastr.error('Please add at least one product!');
            else alert('Please add at least one product!');
            return;
        }

        $.ajax({
            type: 'POST',
            url: "{{ route('admin.orders.update', $order->id) }}", 
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json' 
            },
            beforeSend: function() {
                btn.html('<i class="mdi mdi-spin mdi-loading"></i> Saving...').prop('disabled', true);
            },
            success: function(res) {
                if(res.status) {
                    if (typeof toastr !== 'undefined') toastr.success(res.msg || 'Order updated successfully!');
                    
                    btn.html('<i class="mdi mdi-content-save"></i> Save Changes').prop('disabled', false);
                    
                    if (typeof smartReload === 'function') {
                        smartReload(); 
                    } else if ($('.order_sts:checked').length) {
                        $('.order_sts:checked').trigger('change');
                    } else if ($('#submit_search').length) {
                        $('#submit_search').trigger('click');
                    }
                    
                    if (typeof closeRightPanel === 'function') closeRightPanel(); 
                    else if ($('.offcanvas').length) $('.offcanvas').offcanvas('hide');
                    
                } else {
                    if (typeof toastr !== 'undefined') toastr.error(res.msg || 'Something went wrong!');
                    btn.html('<i class="mdi mdi-content-save"></i> Save Changes').prop('disabled', false);
                }
            },
            error: function(err) {
                btn.html('<i class="mdi mdi-content-save"></i> Save Changes').prop('disabled', false);
                if(err.status === 422) {
                    let errors = err.responseJSON.errors;
                    for(let key in errors) {
                        if (typeof toastr !== 'undefined') toastr.error(errors[key][0]); 
                        else alert(errors[key][0]);
                    }
                } else {
                    if (typeof toastr !== 'undefined') toastr.error('Server error occurred!');
                    else alert('Server error occurred!');
                }
            }
        });
    };

    $(document).ready(function() {
        
        let customerMobile = "{{ $order->mobile }}";
        let historyUrl = "{{ route('admin.orders.customerHistory') }}"; 

        $.ajax({
            url: historyUrl,
            type: 'GET',
            data: { mobile: customerMobile },
            success: function(res) {
                if(res.html) {
                    let compactHtml = res.html.replace('table ', 'table table-sm text-center" style="font-size: 11px; ');
                    $('#drawer_customer_history').html(compactHtml);
                } else {
                    $('#drawer_customer_history').html('<div class="text-center text-muted" style="font-size:11px;">No history found.</div>');
                }
            }
        });

        let orderId = "{{ $order->id }}";
        let fraudUrl = "{{ url('/admin/order/fraud-check') }}/" + orderId; 

        $.ajax({
            url: fraudUrl,
            type: 'GET',
            success: function(response) {
                $('#drawer_fraud_result').html(response);
            }
        });

        if($('.select2-drawer').length) {
            $('.select2-drawer').select2({
                dropdownParent: $('#rightDetailsPanel'),
                width: '100%'
            });
        }

        // ✅ Updated calculation function for Free Shipping
        function calcDrawerTotal() {
            let itemsTotal = 0;
            let hasFreeShipping = false;
            
            $('#drawer_edit_form .drawer-product-row').each(function() {
                let qty = parseFloat($(this).find('.drawer-qty').val());
                let price = parseFloat($(this).find('.drawer-price').val());
                
                if (isNaN(qty) || qty < 0) qty = 0;
                if (isNaN(price) || price < 0) price = 0;
                
                let rowTotal = qty * price;
                $(this).find('.drawer-row-total').text(rowTotal.toFixed(2));
                
                itemsTotal += rowTotal;

                // Check for free shipping
                if($(this).find('.drawer_is_free_shipping').val() == '1') {
                    hasFreeShipping = true;
                }
            });

            let delivery = parseFloat($('#drawer_delivery_charge option:selected').attr('data-charge'));
            if(isNaN(delivery)) delivery = 0;

            // Apply free shipping logic
            if (hasFreeShipping) {
                delivery = 0;
                $('#drawer_delivery_charge').css('border-color', '#10b981'); // highlight green
                $('#drawer_free_shipping_notice').show();
            } else {
                $('#drawer_delivery_charge').css('border-color', '#cbd5e1'); // normal color
                $('#drawer_free_shipping_notice').hide();
            }

            $('#drawer_shipping_charge_val').val(delivery);

            let discount = parseFloat($('#drawer_discount').val());
            if(isNaN(discount) || discount < 0) discount = 0;

            let grandTotal = itemsTotal + delivery - discount;
            if(grandTotal < 0) grandTotal = 0;

            $('#drawer_sub_total').text(itemsTotal.toFixed(2));
            $('#drawer_delivery_display').text(delivery.toFixed(2));
            $('#drawer_discount_display').text(discount.toFixed(2));

            $('#drawer_grand_total').text(grandTotal.toFixed(2));
            $('#drawer_final_amount_input').val(grandTotal.toFixed(2));
        }

        calcDrawerTotal();

        $('#drawer_edit_form').off('input').on('input', '.drawer-qty, .drawer-price, #drawer_discount', calcDrawerTotal);
        $('#drawer_edit_form').off('change').on('change', '#drawer_delivery_charge', calcDrawerTotal);
        
        $('#drawer_edit_form').on('change', '.drawer-variant-select', function() {
            let $opt = $(this).find('option:selected');
            let price = parseFloat($opt.attr('data-price'));
            if(!isNaN(price)) {
                $(this).closest('.drawer-product-row').find('.drawer-price').val(price);
            }
            $(this).closest('.drawer-product-row').find('.drawer-hidden-variation-id').val($(this).val());
            calcDrawerTotal();
        });

        $('#drawer_edit_form').on('click', '.remove-drawer-product', function() {
            $(this).closest('.drawer-product-row').remove();
            calcDrawerTotal();
        });

        $('#drawer_courier_select').on('change', function() {
            let val = $(this).val();
            if(val == '1') {
                $('.drawer-for-redx').removeClass('d-none');
                $('.drawer-for-pathao').addClass('d-none');
            } else if(val == '2') {
                $('.drawer-for-pathao').removeClass('d-none');
                $('.drawer-for-redx').addClass('d-none');
            } else {
                $('.drawer-for-redx, .drawer-for-pathao').addClass('d-none');
            }
        });

        $('#drawer_area_select').on('change', function() {
            $('#drawer_area_id').val($(this).val());
            $('#drawer_area_name').val($(this).find('option:selected').text());
        });

        $('#drawer_city_select').on('change', function() {
            let city = $(this).val();
            let url = "{{ url('/admin/zones-by-city') }}/" + city;
            $('#drawer_zone_select').html("<option value=''>Select Zone</option>");
            $('#drawer_pathao_area_id').html("<option value=''>Select Area</option>");
            if(!city) return;

            $.get(url, function(res) {
                if(res.success && res.zones) {
                    let html = "<option value=''>Select Zone</option>";
                    res.zones.forEach(z => html += `<option value='${z.zone_id}'>${z.zone_name}</option>`);
                    $('#drawer_zone_select').html(html);
                }
            });
        });

        $('#drawer_zone_select').on('change', function() {
            let zone = $(this).val();
            let url = "{{ url('/admin/areas-by-zone') }}/" + zone;
            $('#drawer_pathao_area_id').html("<option value=''>Select Area</option>");
            if(!zone) return;

            $.get(url, function(res) {
                if(res.success && res.areas) {
                    let html = "<option value=''>Select Area</option>";
                    res.areas.forEach(a => html += `<option value='${a.area_id}'>${a.area_name}</option>`);
                    $('#drawer_pathao_area_id').html(html);
                }
            });
        });

        $('#drawer_shipping_address').on('change', function() {
            let address = $(this).val();
            if(address.length < 3) return;
            $.post("{{ route('admin.fetch.address.details') }}", { _token: "{{ csrf_token() }}", address: address }, function(res) {
                if(res.city_id) {
                    $("#drawer_city_select").val(res.city_id).trigger("change");
                    setTimeout(() => { if(res.zone_id) $("#drawer_zone_select").val(res.zone_id).trigger("change"); }, 500);
                    setTimeout(() => { if(res.area_id) $("#drawer_pathao_area_id").val(res.area_id).trigger("change"); }, 1000);
                }
            });
        });

        $("#drawer_product_search").autocomplete({
            appendTo: "#rightDetailsPanel", 
            minLength: 2,
            source: function(request, response){
                $.getJSON("{{ route('admin.products.search') }}", { q: request.term }, function(list){
                    response($.map(list, function(p){
                        return { label: (p.sku ? '['+p.sku+'] ' : '') + p.name, value: p.name, data : p };
                    }));
                });
            },
            select: function(e, ui){
                e.preventDefault();
                addDrawerProduct(ui.item.data);
                $(this).val('');
            }
        });

        function getBestDrawerPrice(obj) {
            const cands = [
                obj?.discount_price,
                obj?.after_discount_price,
                obj?.after_discount,
                obj?.price,
                obj?.sell_price,
                obj?.regular_price
            ];
            for (const v of cands) {
                const n = parseFloat(v);
                if (!isNaN(n) && n > 0) return n;
            }
            return 0;
        }

        function addDrawerProduct(p) {
            let hasVar = Array.isArray(p.variations) && p.variations.length > 0;
            
            let defPrice = hasVar ? getBestDrawerPrice(p.variations[0]) : getBestDrawerPrice(p);
            let firstVarId = hasVar ? (p.variations[0].id || '') : '';
            let imgUrl = p.image_url || p.image || ''; 
            if(!imgUrl.includes('http')) imgUrl = '/uploads/products/' + imgUrl;

            // ✅ Check and generate Free Shipping Badge for new product
            let isFreeShipping = (p.is_free_shipping == 1) ? 1 : 0;
            let freeBadge = isFreeShipping ? `<span class="badge bg-success mt-1" style="font-size: 9px;"><i class="mdi mdi-truck-fast"></i> Free</span>` : ``;

            let varHtml = hasVar ? `<select class="form-select drawer-form-control drawer-variant-select" name="variant_display[]">` + 
                p.variations.map(v => `<option value="${v.id}" data-price="${getBestDrawerPrice(v)}">${v.display_title || v.size_label || v.size || 'Variant'}</option>`).join('') + `</select>` 
                : `<div class="text-muted" style="font-size:11px;">No Variants</div>`;

            let row = `
            <div class="drawer-product-card drawer-product-row">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex gap-2">
                        <img src="${imgUrl}" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 12px; line-height: 1.2;">${p.name}</div>
                            <div class="text-muted" style="font-size: 10px;">SKU: ${p.sku || 'N/A'}</div>
                            ${freeBadge}
                            <input type="hidden" class="drawer_is_free_shipping" value="${isFreeShipping}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm text-danger p-0 remove-drawer-product"><i class="mdi mdi-close-circle fs-5"></i></button>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-12">
                        ${varHtml}
                        <input type="hidden" name="variation_id[]" class="drawer-hidden-variation-id" value="${firstVarId}"/>
                        <input type="hidden" name="order_line_id[]" value=""/>
                        <input type="hidden" name="product_id[]" value="${p.id}"/>
                        <input type="hidden" name="is_stock" value="${p.is_stock || 1}">
                    </div>
                    <div class="col-4">
                        <label class="drawer-form-label">Qty</label>
                        <input type="number" class="form-control drawer-form-control drawer-qty" name="quantity[]" value="1" min="1" required>
                    </div>
                    <div class="col-4">
                        <label class="drawer-form-label">Price</label>
                        <input type="number" class="form-control drawer-form-control drawer-price" name="unit_price[]" value="${defPrice}" step="0.01" required>
                    </div>
                    <div class="col-4 text-end pb-1">
                        <div class="fw-bold text-dark">৳ <span class="drawer-row-total">${defPrice.toFixed(2)}</span></div>
                    </div>
                </div>
            </div>`;

            $('#drawer_product_list').prepend(row);
            calcDrawerTotal();
        }

    });
</script>