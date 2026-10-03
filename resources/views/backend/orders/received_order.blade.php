@php
  $loopItems = isset($received_order) ? $received_order : ($items ?? collect());
@endphp

<div class="table-responsive w-100" id="orderTableWrap">
    <table class="table order-table table-sm w-100" style="font-size: 13px; table-layout: auto;">
        <thead class="bg-light align-middle text-nowrap">
            <tr>
                <th style="width:20px" class="ps-1 pe-0 text-center"><input type="checkbox" class="form-check-input check_all m-0"></th>
                <th style="width:25px" class="ps-0 pe-1 text-center">Sl</th>
                <th class="px-1 text-center">Action</th>
                <th class="px-1">Invoice</th>
                <th class="px-1">Customer Info</th>
                <th class="ps-1 pe-0" style="width: 160px;">Products</th>
                <th class="ps-0 pe-1" style="width: 90px;">Status</th>
                <th class="px-1" style="width: 110px;">Source</th>
                <th class="ps-1 pe-0" style="width: 70px;">Fraud Check</th>
                <th class="ps-0 pe-1" style="width: 100px;">Payment</th>
                <th class="px-1">Courier</th>
                <th class="px-1 text-center">Assigned</th>
                <th class="px-1 text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loopItems as $key => $item)
            <tr class="order-row align-middle" data-id="{{ $item->id }}">
                <td class="ps-1 pe-0 text-center">
                    <input type="checkbox" class="order_checkbox form-check-input m-0" value="{{ $item->id}}">
                </td>
                
                <td class="ps-0 pe-1 text-center fw-bold text-muted">
                    {{ method_exists($loopItems, 'firstItem') ? ($loopItems->firstItem() + $key) : $loop->iteration }}
                </td>
                
                <td class="px-1" style="width: 40px;">
                    <div class="d-flex flex-column align-items-center gap-1">
                        <a href="{{ route('admin.orders.edit',[$item->id])}}" target="_blank" class="btn btn-sm btn-outline-primary p-1 lh-1 action-icon" title="Edit"><i class="mdi mdi-pencil-outline" style="font-size: 14px;"></i></a>
                        <a href="javascript:void(0);" data-id="{{ $item->id }}" class="btn btn-sm btn-outline-info p-1 lh-1 action-icon view-activity-log" title="Activity Log"><i class="mdi mdi-history" style="font-size: 14px;"></i></a>
                        @can('order.delete')
                            <a href="{{ route('admin.orders.destroy',[$item->id])}}" class="btn btn-sm btn-outline-danger p-1 lh-1 delete-order-btn action-icon" title="Delete"><i class="mdi mdi-trash-can-outline" style="font-size: 14px;"></i></a>
                        @endcan
                    </div>
                </td>

                <td class="px-1">
                    <div class="cell-stack lh-sm text-nowrap">
                        <span class="fw-bold text-primary">#{{ $item->invoice_no }}</span>
                        <span class="text-muted d-block" style="font-size: 12px;">ID: {{ $item->id }}</span>
                        <span class="text-muted d-block" style="font-size: 11px;">{{ $item->created_at->format('d M y') }}</span>
                        <span class="text-muted d-block" style="font-size: 10px;"><i class="mdi mdi-clock-outline"></i> {{ $item->created_at->format('h:i A') }}</span>
                    </div>
                </td>

                <td class="px-1" style="max-width: 150px; white-space: normal;">
                    <div class="cell-stack lh-sm">
                        <span class="fw-bold d-block">{{ trim($item->first_name.' '.$item->last_name) }}</span>
                        <a class="text-primary text-decoration-none fw-bold d-block" style="font-size: 12px;" href="tel:{{ preg_replace('/\D/','',$item->mobile) }}"><i class="mdi mdi-phone"></i> {{ $item->mobile }}</a>
                        
                        @if(!empty($item->shipping_address))
                            <div class="badge bg-light text-dark border text-wrap text-start mt-1 p-1 w-100" style="font-size: 11px; line-height: 1.4 !important; font-weight: 500;">
                                <i class="mdi mdi-map-marker text-danger" style="font-size: 12px;"></i> {{ $item->shipping_address }}
                            </div>
                        @endif

                        {{-- অর্ডার নোট — আগে লিস্টে কোথাও দেখাত না, শুধু ইনভয়েস/ডিটেইলসে ছিল --}}
                        @if(!empty(trim($item->note)))
                            <div class="badge text-wrap text-start mt-1 p-1 w-100" style="font-size: 11px; line-height: 1.4 !important; font-weight: 500; background: #fff8e1; color: #8a6d00; border: 1px dashed #f0c000;" title="{{ $item->note }}">
                                <i class="mdi mdi-note-text-outline" style="font-size: 12px;"></i> {{ \Illuminate\Support\Str::limit($item->note, 70) }}
                            </div>
                        @endif
                        
                        @php
                            $mobileCount = \App\Models\Order::where('mobile', $item->mobile)->count();
                            $historyClass = $mobileCount > 1 ? 'bg-warning text-dark' : 'bg-success text-white';
                        @endphp
                        <button type="button" class="badge {{ $historyClass }} border-0 view-history-btn mt-1 w-100 py-1 px-0 text-uppercase" data-mobile="{{ $item->mobile }}" style="font-size: 10px;">
                            <i class="mdi mdi-history"></i> History ({{ $mobileCount }})
                        </button>
                    </div>
                </td>

                <td class="ps-1 pe-0" style="width: 160px;">
                    <div class="d-flex flex-column align-items-start gap-1">
                        @foreach($item->details as $detail)
                            @php
                                // অর্ডার করা color-এর নিজস্ব ছবি আগে, না থাকলে product-এর মূল ছবি (fallback)
                                $__imgFile = optional($detail->variation)->image ?: (optional($detail->product)->image ?? 'default-product.png');
                                $__imgUrl  = function_exists('getImage') ? getImage('products', $__imgFile) : asset('uploads/products/'.$__imgFile);
                            @endphp
                            <div class="bg-light border rounded p-1 lh-sm d-flex gap-1" style="font-size: 11px; width: 150px; min-width: 150px;">
                                <a href="{{ $__imgUrl }}" target="_blank" title="Click to view full image" style="flex: 0 0 36px;">
                                    <img src="{{ $__imgUrl }}" alt="product"
                                         style="width: 36px; height: 36px; border-radius: 4px; object-fit: cover; display: block; cursor: zoom-in;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/no_found.png') }}';">
                                </a>
                                <div class="text-start" style="min-width: 0; flex: 1 1 auto;">
                                    <div class="fw-bold text-dark text-wrap mb-1" title="{{ $detail->product->name ?? 'Unavailable' }}">
                                        {{ \Illuminate\Support\Str::words($detail->product->name ?? 'Unavailable', 2, '...') }}
                                        @if(isset($detail->product) && $detail->product->is_free_shipping == 1)
                                            <span class="text-success ms-1 text-nowrap" style="font-size: 10px;"><i class="mdi mdi-truck-fast"></i> Free</span>
                                        @endif
                                    </div>

                                    <div class="text-muted" style="font-size: 10px;">
                                        <span class="d-block"><b>SKU:</b> {{ $detail->product['sku'] ?? 'N/A' }}</span>

                                        @php
                                            $rawVar = $detail->variant_name ?? ($detail->variation->display_title ?? 'N/A');
                                            $formattedVar = str_replace([', ', ','], '<br>', $rawVar);
                                        @endphp
                                        <span class="d-block" style="word-break: break-word;"><b>Var:</b> {!! $formattedVar !!}</span>

                                        <div class="mt-1">
                                            <b>Qty:</b> <span class="text-danger fw-bold" style="font-size: 12px;">{{ $detail->quantity ?? 1 }}</span>
                                            @if(isset($detail->package_id) && $detail->package_id)
                                                | <b class="text-primary">Pkg:</b> <span class="fw-bold">{{ $detail->package->qty ?? '' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </td>

                <td class="ps-0 pe-1" style="width: 90px;">
                    <a class="btn_modal text-decoration-none" href="{{ route('admin.orderStatus', $item->id)}}">
                        @php
                            $sColor = 'bg-secondary'; $s = strtolower($item->status);
                            if($s == 'pending') $sColor = 'bg-warning text-dark';
                            elseif($s == 'confirmed') $sColor = 'bg-primary text-white';
                            elseif($s == 'processing') $sColor = 'bg-info text-white';
                            elseif(in_array($s, ['shipped','courier'])) $sColor = 'bg-dark text-white';
                            elseif(in_array($s, ['delivered','complete'])) $sColor = 'bg-success text-white';
                            elseif(in_array($s, ['returning','return received','cancelled','cancell','return'])) $sColor = 'bg-danger text-white';
                            elseif($s == 'return missing') $sColor = 'bg-warning text-dark fw-bold border border-warning';
                        @endphp
                        <span class="badge {{ $sColor }} w-100 py-1 px-0" style="font-size: 11px;">{{ ucfirst($item->status) }}</span>
                    </a>
                </td>

                @include('backend.orders.partials.source_column', ['item' => $item])

                <td class="ps-1 pe-0" style="width: 70px;">
                    @php $percent = $item->getCourierPercent(); @endphp
                    @php $color = $percent>50 ? '#10b981' : ($percent>20?'#f59e0b':'#ef4444'); @endphp
                    <div class="d-flex align-items-center gap-1 text-nowrap">
                        <span style="color:{{$color}}; font-weight:800; font-size: 12px;">{{$percent}}%</span>
                        <a href="javascript:void(0);" data-url="{{route('admin.fraudOrderCheck',$item->id)}}" class="btn-fraud text-muted"><i class="mdi mdi-magnify" style="font-size: 16px;"></i></a>
                    </div>
                </td>

                <!-- Updated Payment TD -->
                <td class="ps-0 pe-1" style="max-width: 100px;">
                    <a class="btn_modal text-decoration-none d-inline-block mb-1" href="{{ route('admin.order_payments.edit', $item->id)}}">
                        <span class="badge {{ strtolower($item->payment_status) == 'paid' ? 'bg-success' : 'bg-danger' }} p-1" style="font-size: 11px;">{{ $item->payment_status ?? 'Due' }}</span>
                    </a>
                    
                    @php
                        $rawPaymentMethod = $item->payment_method ?? '';
                        $paymentMethod = $rawPaymentMethod;
                        if (strtolower($rawPaymentMethod) == 'eps') $paymentMethod = 'EPS';
                        elseif (strtolower($rawPaymentMethod) == 'nagad') $paymentMethod = 'Nagad';

                        $transactionId = $item->transaction_id ?? '';
                        $paymentRecord = \App\Models\OrderPayment::where('order_id', $item->id)->latest()->first();
                        
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
                        <div style="font-size: 11px; line-height: 1.2;">
                            <span class="badge bg-info text-dark border border-info mb-1 p-1 d-inline-block text-truncate" style="max-width: 100%; font-size: 10px;">{{ $paymentMethod }}</span><br>
                            
                            <span class="text-success fw-bold d-block user-select-all" 
                                  style="font-size: 9px; cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
                                  onclick="navigator.clipboard.writeText('{{ $transactionId }}'); alert('TrxID Copied!');" 
                                  title="Click to copy TrxID">
                                <i class="mdi mdi-content-copy text-muted" style="font-size: 10px;"></i> {{ $transactionId }}
                            </span>
                            
                            <!-- Removed A/C text, added small font size and text-truncate -->
                            @if($item->sender_number)
                                <strong class="d-block mt-1 text-truncate text-muted" style="font-size: 9px;" title="Sender Number: {{ $item->sender_number }}">{{ $item->sender_number }}</strong>
                            @endif
                        </div>
                    @elseif($transactionId)
                        <div class="text-muted mt-1 lh-1 text-center text-truncate user-select-all" 
                             style="font-size:9px; cursor:pointer;" 
                             onclick="navigator.clipboard.writeText('{{ $transactionId }}'); alert('TrxID Copied!');" 
                             title="Click to copy TrxID">
                            <i class="mdi mdi-content-copy"></i> {{ $transactionId }}
                        </div>
                    @endif
                </td>

                <td class="px-1" style="max-width: 110px;">
                    <div class="cell-stack lh-sm text-wrap">
                        <span class="fw-bold d-block text-truncate" style="font-size: 13px;">{{ $item->courier ? $item->courier->name : '—' }}</span>
                        <span class="text-muted d-block text-truncate" style="font-size: 12px;">{{$item->courier_tracking_id ?? ''}}</span>
                        <span class="text-info fw-bold d-block text-truncate" style="font-size: 11px;">{{ $item->courier_status ?? '' }}</span>
                    </div>
                </td>

                <td class="px-1 text-center">
                    <span class="badge bg-light text-dark border py-1 px-2 text-truncate" style="font-size: 11px; max-width: 60px;">{{ $item->assign ? $item->assign->username : 'Admin' }}</span>
                </td>

                <td class="px-1 text-end text-nowrap">
                    <div class="d-flex flex-column align-items-end gap-1">
                        <span class="fw-bold text-dark" style="font-size: 13px;">৳{{ (int) $item->final_amount }}</span>
                        @if(isset($item->shipping_charge) && $item->shipping_charge == 0)
                            <span class="badge bg-success text-center" style="font-size: 10px; padding: 3px 4px;"><i class="mdi mdi-truck-check"></i> Free Ship</span>
                        @else
                            <span class="badge bg-light text-muted border text-center" style="font-size: 10px; padding: 3px 4px;">Ship: ৳{{ (int) ($item->shipping_charge ?? 0) }}</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center py-4 text-muted" style="font-size: 14px;">
                    <i class="mdi mdi-package-variant-closed fs-3 d-block mb-1"></i>
                    No orders found!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($loopItems,'links'))
  <div class="mt-2 ajax-pagination d-flex justify-content-end" style="font-size: 13px;">
    {!! urldecode(str_replace("/?","?",$loopItems->appends(Request::all())->render())) !!}
  </div>
@endif