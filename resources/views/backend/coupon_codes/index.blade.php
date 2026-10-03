@extends('backend.app')
@section('content')

@php
use App\Models\Information;
$info = Information::first();

@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Coupon Code Manage</li>
                </ol>
            </div>
            <h4 class="page-title">Coupon Code Manage</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-sm-12 col-md-4">            
        @can('size.create')
        <div class="card">
            <div class="card-header">
                <h4> Coupon Code Create</h4>
            </div>
            <div class="card-body">
   
                <form method="POST" action="{{ route('admin.coupon_codes.store')}}" id="ajax_form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label  class="form-label">Coupon Code</label>
                                <input type="text" name="code" class="form-control" placeholder="Coupon Code">
                            </div>
                            
                          	<div class="mb-3">
                                <label  class="form-label">Discount Type</label>
                              <select class="form-control" name="discount_type">
                                	<option value="fixed">Fixed</option>
                                	<option value="percentage">Percentage</option>
                              </select>
                            </div>
                          
                            <div class="mb-3">
                                <label  class="form-label">Discount Amount</label>
                                <input type="number" step="any" name="amount" class="form-control">
                            </div>
                          
                          	<div class="mb-3">
                                <label  class="form-label">Minimum Purchase</label>
                                <input type="number" step="any" name="minimum_amount" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label  class="form-label">Date Start</label>
                                <input type="date" step="any" name="start" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label  class="form-label">Date End</label>
                                <input type="date" step="any" name="end" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Per Customer Limit <small class="text-muted">(একজন কতবার — 0 = unlimited)</small></label>
                                <input type="number" name="per_customer_limit" min="0" value="0" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Total Limit <small class="text-muted">(মোট কতবার — 0 = unlimited)</small></label>
                                <input type="number" name="total_limit" min="0" value="0" class="form-control">
                            </div>

                        </div>

                        <div class="col-lg-12">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>

                </form>
            
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div>   
    @endcan
    <div class="col-sm-12 col-md-8">
        <div class="card">
            <div class="card-body">
   

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0 table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Coupon Code</th>
                              	<th>Discount Type</th>
                                <th>Discount Amount</th>
                              	<th>Minimum Purchase</th>
                                <th>Date Start</th>
                                <th>Date End</th>
                                <th>Used (Orders)</th>
                                <th style="width: 125px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key=> $item)
                            <tr>
                                <td> {{$key+1}} </td>
                                <td> {{$item->code}} </td>
                              	<td> {{$item->discount_type}} </td>
                                <td> {{$item->amount}} </td>
                              	<td> {{$item->minimum_amount}} </td>
                                <td> {{$item->start}}</td>
                                <td> {{$item->end}}</td>
                                <td>
                                    @php $u = isset($usage) ? ($usage[$item->code] ?? null) : null; @endphp
                                    <span class="badge {{ ($u->orders_count ?? 0) > 0 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $u->orders_count ?? 0 }} orders
                                    </span>
                                    @if($u && $u->orders_amount > 0)
                                        <small class="text-muted d-block">৳{{ number_format($u->orders_amount) }}</small>
                                    @endif
                                    @if(($item->per_customer_limit ?? 0) > 0 || ($item->total_limit ?? 0) > 0)
                                        <small class="text-muted d-block">limit: {{ ($item->per_customer_limit ?? 0) ?: '∞' }}/জন &middot; মোট {{ ($item->total_limit ?? 0) ?: '∞' }}</small>
                                    @endif
                                </td>
                                <td>
                                @can('size.edit')
                                    <a href="{{ route('admin.coupon_codes.edit',[$item->id])}}" class="action-icon btn_modal"> 
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                @endcan
                                @can('size.delete')
                                    <a href="{{ route('admin.coupon_codes.destroy',[$item->id])}}" class="delete action-icon"> <i class="mdi mdi-delete"></i></a>
                                @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card-body-->
        </div>
      
      <div class="row mb-2">
         <form method="GET" action="{{ route('admin.status.coupon')}}" id="">
                    @csrf
        <div class="col-md-3" style="margin-bottom: 10px;">
                            <div class="form-group">
                               <strong for="role">Coupon Manage</strong>
                                <select class="form-select" class="coupon_visibility" name="coupon_visibility">                                
                                <option value="1" {{$info->coupon_visibility == 1 ?'selected':''}} >On</option>                               
                                <option value="0" {{$info->coupon_visibility == 0 ?'selected':''}} >Off</option>  
                               </select>
                            </div>                            
          
                        </div>
        
        				<div class="mb-3">
                                <button type="submit" class="btn btn-primary">Save</button>
                        </div>
        </form>
      </div>

      {{-- ============ EXIT-INTENT COUPON POPUP SETTINGS ============ --}}
      @php $epi = getInfo(); @endphp
      <div class="card mt-2">
          <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                  <h4 class="header-title mb-0">🎁 Exit Popup (Leave-করার সময় Coupon Offer)</h4>
                  @if(isset($popupStats) && $popupStats)
                      <div>
                          <span class="badge bg-info">Popup দেখানো হয়েছে: {{ (int) $popupStats->shows }} বার</span>
                          <span class="badge bg-primary">Code copy: {{ (int) $popupStats->copies }} বার</span>
                      </div>
                  @endif
              </div>
              <p class="text-muted" style="font-size:12px">Customer কিছু না নিয়ে চলে যেতে চাইলে (mobile-এ back button, desktop-এ mouse উপরে নিলে) coupon-সহ popup দেখাবে। সবকিছু এখান থেকেই নিয়ন্ত্রণ করুন।</p>

              @if(session('success'))
                  <div class="alert alert-success">{{ session('success') }}</div>
              @endif

              <form method="POST" action="{{ route('admin.exit_popup.save') }}">
                  @csrf
                  <div class="row">
                      <div class="col-md-3 mb-2">
                          <label class="form-label"><strong>Popup চালু?</strong></label>
                          <select class="form-select" name="exit_popup_active">
                              <option value="1" {{ ($epi->exit_popup_active ?? 0) == 1 ? 'selected' : '' }}>On</option>
                              <option value="0" {{ ($epi->exit_popup_active ?? 0) == 0 ? 'selected' : '' }}>Off</option>
                          </select>
                      </div>
                      <div class="col-md-3 mb-2">
                          <label class="form-label"><strong>কোন Coupon দেখাবে?</strong></label>
                          <select class="form-select" name="exit_popup_coupon_id">
                              <option value="">-- Coupon বাছুন --</option>
                              @foreach($items as $c)
                                  <option value="{{ $c->id }}" {{ ($epi->exit_popup_coupon_id ?? '') == $c->id ? 'selected' : '' }}>
                                      {{ $c->code }} ({{ $c->discount_type == 'fixed' ? '৳'.$c->amount : $c->amount.'%' }})
                                  </option>
                              @endforeach
                          </select>
                      </div>
                      <div class="col-md-2 mb-2">
                          <label class="form-label"><strong>কত ঘণ্টা পরপর?</strong></label>
                          <input type="number" class="form-control" name="exit_popup_hours" min="0" max="720"
                                 value="{{ $epi->exit_popup_hours ?? 24 }}">
                          <small class="text-muted">0 দিলে প্রতি visit-এ একবার</small>
                      </div>
                      <div class="col-md-2 mb-2">
                          <label class="form-label"><strong>Desktop-এ?</strong></label>
                          <select class="form-select" name="exit_popup_desktop">
                              <option value="1" {{ ($epi->exit_popup_desktop ?? 1) == 1 ? 'selected' : '' }}>On</option>
                              <option value="0" {{ ($epi->exit_popup_desktop ?? 1) == 0 ? 'selected' : '' }}>Off</option>
                          </select>
                      </div>
                      <div class="col-md-2 mb-2">
                          <label class="form-label"><strong>Mobile-এ (back button)?</strong></label>
                          <select class="form-select" name="exit_popup_mobile">
                              <option value="1" {{ ($epi->exit_popup_mobile ?? 1) == 1 ? 'selected' : '' }}>On</option>
                              <option value="0" {{ ($epi->exit_popup_mobile ?? 1) == 0 ? 'selected' : '' }}>Off</option>
                          </select>
                      </div>
                      <div class="col-md-2 mb-2">
                          <label class="form-label"><strong>Desktop delay (সেকেন্ড)</strong></label>
                          <input type="number" class="form-control" name="exit_popup_delay_seconds" min="0" max="120"
                                 value="{{ $epi->exit_popup_delay_seconds ?? 5 }}">
                          <small class="text-muted">Page-এ এত সেকেন্ড থাকার পরে popup সক্রিয় হবে</small>
                      </div>
                      <div class="col-md-2 mb-2">
                          <label class="form-label"><strong>সর্বোচ্চ কতবার দেখাবে?</strong></label>
                          <input type="number" class="form-control" name="exit_popup_max_shows" min="1" max="10"
                                 value="{{ $epi->exit_popup_max_shows ?? 1 }}">
                          <small class="text-muted">Customer বন্ধ করলেও এতবার পর্যন্ত আবার দেখাবে (প্রতি সময়সীমায়)</small>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-md-4 mb-2">
                          <label class="form-label"><strong>কোথায় দেখাবে?</strong></label>
                          @php $epScopeVal = $epi->exit_popup_scope ?? 'all'; @endphp
                          <select class="form-select" name="exit_popup_scope" id="exitPopupScope">
                              <option value="all" {{ $epScopeVal == 'all' ? 'selected' : '' }}>সব জায়গায় (website + landing)</option>
                              <option value="site" {{ $epScopeVal == 'site' ? 'selected' : '' }}>শুধু মূল website-এ</option>
                              <option value="landing" {{ $epScopeVal == 'landing' ? 'selected' : '' }}>শুধু landing page-গুলোতে</option>
                              <option value="products" {{ $epScopeVal == 'products' ? 'selected' : '' }}>নির্দিষ্ট product-এ</option>
                          </select>
                      </div>
                      <div class="col-md-8 mb-2" id="exitPopupProductsWrap" style="{{ $epScopeVal == 'products' ? '' : 'display:none;' }}">
                          <label class="form-label"><strong>কোন কোন product-এ?</strong> <small class="text-muted">(Ctrl চেপে একাধিক select করুন)</small></label>
                          @php $epSelIds = array_map('intval', json_decode($epi->exit_popup_product_ids ?? '[]', true) ?: []); @endphp
                          <select class="form-select" name="exit_popup_product_ids[]" multiple size="6">
                              @foreach(($productsList ?? []) as $p)
                                  <option value="{{ $p->id }}" {{ in_array((int) $p->id, $epSelIds, true) ? 'selected' : '' }}>{{ $p->name }}</option>
                              @endforeach
                          </select>
                          <small class="text-muted">শুধু এই product-গুলোর page ও এদের landing page-এ popup আসবে</small>
                      </div>
                  </div>
                  <script>
                      document.getElementById('exitPopupScope').addEventListener('change', function(){
                          document.getElementById('exitPopupProductsWrap').style.display = this.value === 'products' ? '' : 'none';
                      });
                  </script>
                  <div class="row">
                      <div class="col-md-4 mb-2">
                          <label class="form-label"><strong>Popup Title</strong></label>
                          <input type="text" class="form-control" name="exit_popup_title" maxlength="255"
                                 placeholder="যাওয়ার আগে একটু দাঁড়ান!"
                                 value="{{ $epi->exit_popup_title ?? '' }}">
                      </div>
                      <div class="col-md-8 mb-2">
                          <label class="form-label"><strong>Popup Message</strong></label>
                          <input type="text" class="form-control" name="exit_popup_text" maxlength="2000"
                                 placeholder="আপনার জন্য বিশেষ ছাড়! নিচের কুপন কোডটি checkout-এ ব্যবহার করলেই ছাড় পেয়ে যাবেন।"
                                 value="{{ $epi->exit_popup_text ?? '' }}">
                      </div>
                  </div>
                  <button type="submit" class="btn btn-success mt-1">Exit Popup Settings Save</button>
              </form>
          </div>
      </div>

      <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 