@extends('frontend.app')
@section('content')

<style>
  :root{
    --primary:#00276C;
    --primary2:#033199;
    --bg:#f5f6fa;
    --card:#ffffff;
    --text:#0f172a;
    --muted:#64748b;
    --border:rgba(2,6,23,.10);
    --shadow:0 18px 50px rgba(0,39,108,.12);
    --shadow2:0 14px 30px rgba(0,39,108,.18);
    --radius:18px;
  }

  #otpModal { z-index: 99999 !important; }
  .otp-modal-content {
      border: none !important; border-radius: 20px !important;
      background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      text-align: center; overflow: hidden; position: relative;
  }
  .otp-modal-content::before {
      content: ""; position: absolute; top: 0; left: 0; right: 0;
      height: 6px; background: linear-gradient(90deg, #E2136E, #F6921E);
  }
  .otp-icon-box {
      width: 80px; height: 80px; background: #fdf2f7; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 10px auto 20px; color: #E2136E;
  }
  .otp-input {
      width: 100%; letter-spacing: 15px; text-align: center;
      font-size: 28px; font-weight: bold; color: #333;
      border: 2px solid #eee !important; border-radius: 12px !important;
      background: #fafafa; height: 65px; transition: all 0.3s ease;
      position: relative; z-index: 999999 !important;
  }
  .otp-input:focus {
      border-color: #E2136E !important; background: #fff;
      box-shadow: 0 5px 15px rgba(226, 19, 110, 0.1) !important; outline: none;
  }
  .btn-verify {
      background: linear-gradient(135deg, #E2136E 0%, #C90D5E 100%);
      border: none; padding: 12px; font-size: 18px; border-radius: 12px;
      box-shadow: 0 8px 20px rgba(226, 19, 110, 0.3);
      width: 100%; color: white; font-family: 'Hind Siliguri', sans-serif;
  }
  .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(226, 19, 110, 0.4); }

  input[type='text'], input[type='number'], #selectCourier {
    border: 1px solid rgba(0,39,108,.35) !important;
    border-radius: 14px !important;
    box-shadow: 0 6px 16px rgba(0,39,108,.06);
    transition: box-shadow .2s ease, border-color .2s ease, transform .2s ease;
  }
  input[type='text']:focus, input[type='number']:focus, #selectCourier:focus{
    border-color: rgba(0,39,108,.80) !important;
    box-shadow: 0 0 0 4px rgba(0,39,108,.14), 0 10px 22px rgba(0,39,108,.12) !important;
    outline: none !important; transform: translateY(-1px);
  }
  .form-group label, .hind{ font-family: 'Hind Siliguri', sans-serif; }

  .section-content{
    background: radial-gradient(circle at 12% 10%, rgba(0,39,108,.06), transparent 45%),
                radial-gradient(circle at 88% 14%, rgba(3,49,153,.06), transparent 42%),
                linear-gradient(180deg, #f5f6fa, #f5f6fa) !important;
  }
  aside.card, .orderDetails{
    background: var(--card) !important;
    border-radius: var(--radius) !important;
    box-shadow: var(--shadow) !important;
  }

  /* ============================================ */
  /* ✅ DELIVERY OPTION - ANIMATION + 2PX BORDER  */
  /* ============================================ */
  .ship-radio-wrap{
    display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px;
    justify-content:center; align-items: stretch; max-width: 620px; margin: 0 auto;
  }
  .ship-option{ position:relative; display:block; width: 100%; margin: 0; }
  .ship-option input[type="radio"]{
    position:absolute !important; inset:0 !important; opacity:0 !important;
    cursor:pointer !important; z-index: 3 !important; margin:0 !important;
  }

  .ship-card{
    position:relative; padding:14px 16px; border-radius:16px;
    background: linear-gradient(180deg, rgba(255,255,255,1), rgba(255,255,255,.98));
    border: 2px solid rgba(0,39,108,.12);
    text-align:center;
    box-shadow: 0 10px 22px rgba(2,6,23,.06);
    transition: all .35s cubic-bezier(.4, 0, .2, 1);
    height: 100%; min-height: 78px;
    display:flex; flex-direction:column; justify-content:center;
    overflow: hidden;
  }

  /* Checkmark badge - top right corner */
  .ship-card::after {
    content: "✓";
    position: absolute;
    top: 6px;
    right: 6px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary2));
    color: #fff;
    font-weight: 900;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(0) rotate(-180deg);
    transition: transform .4s cubic-bezier(.34, 1.56, .64, 1);
    box-shadow: 0 4px 10px rgba(0,39,108,.35);
    opacity: 0;
  }

  /* Subtle background shimmer that slides in */
  .ship-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(0,39,108,.05) 0%, rgba(3,49,153,.08) 100%);
    opacity: 0;
    transition: opacity .35s ease;
    z-index: 0;
  }
  .ship-title, .ship-amount { position: relative; z-index: 1; }

  .ship-title{
    font-family:'Hind Siliguri', sans-serif; font-weight:900;
    color:#0f172a; margin-bottom:6px; font-size:16px;
    transition: color .3s ease;
  }
  .ship-amount{
    display:inline-flex; align-items:center; justify-content:center; gap:6px;
    padding:6px 12px; border-radius:999px; font-weight:900; font-size:13px;
    color:#fff;
    background: linear-gradient(90deg, var(--primary) 0%, var(--primary2) 100%);
    transition: transform .3s ease;
  }

  .ship-option:hover .ship-card{
    transform: translateY(-2px);
    border-color: rgba(0,39,108,.32);
    box-shadow: 0 14px 28px rgba(0,39,108,.10);
  }

  /* ✅ SELECTED STATE - 2px solid primary border + animation */
  .ship-option input[type="radio"]:checked + .ship-card{
    border: 2px solid var(--primary);
    box-shadow: 0 0 0 4px rgba(0,39,108,.15), 0 18px 45px rgba(0,39,108,.25);
    transform: translateY(-3px);
    animation: selectedPop .5s cubic-bezier(.34, 1.56, .64, 1);
  }
  .ship-option input[type="radio"]:checked + .ship-card::before { opacity: 1; }
  .ship-option input[type="radio"]:checked + .ship-card::after {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
  .ship-option input[type="radio"]:checked + .ship-card .ship-title {
    color: var(--primary);
  }
  .ship-option input[type="radio"]:checked + .ship-card .ship-amount {
    transform: scale(1.08);
    box-shadow: 0 6px 14px rgba(0,39,108,.35);
  }

  @keyframes selectedPop {
    0%   { transform: translateY(0)   scale(1);    }
    40%  { transform: translateY(-5px) scale(1.04); }
    70%  { transform: translateY(-2px) scale(.99);  }
    100% { transform: translateY(-3px) scale(1);    }
  }

  /* Soft pulsing glow on selected card */
  @keyframes selectedGlow {
    0%, 100% { box-shadow: 0 0 0 4px rgba(0,39,108,.15), 0 18px 45px rgba(0,39,108,.25); }
    50%      { box-shadow: 0 0 0 6px rgba(0,39,108,.22), 0 22px 50px rgba(0,39,108,.32); }
  }
  .ship-option input[type="radio"]:checked + .ship-card {
    animation: selectedPop .5s cubic-bezier(.34, 1.56, .64, 1),
               selectedGlow 2.5s ease-in-out .5s infinite;
  }

  .payment-card-label { cursor: pointer; transition: all 0.2s; }
  .payment-card-label:hover { background-color: #f8f9fa; }
  input[name="payment_method"]:checked + label { color: #00276C !important; }

  #chk_btn{
    border-radius: 16px !important;
    background: linear-gradient(90deg, var(--primary) 0%, var(--primary2) 100%) !important;
    border: 1px solid rgba(255,255,255,.18) !important;
    box-shadow: 0 18px 40px rgba(0,39,108,.28) !important;
    transition: transform .18s ease, box-shadow .18s ease !important;
  }
  #chk_btn:hover{ transform: translateY(-2px) !important; filter: brightness(1.05) !important; }
  #chk_btn.is-disabled{ opacity:.65 !important; pointer-events:none !important; }

  #manual_payment_area { background: #f8fafc; border: 1px dashed #cbd5e1; }
  .manual-instruction-box {
      background: rgba(226, 19, 110, 0.08);
      border: 1px solid rgba(226, 19, 110, 0.2);
      color: #C90D5E;
  }
  .input-icon-wrap { position: relative; }
  .input-icon-wrap i {
      position: absolute; top: 50%; left: 16px; transform: translateY(-50%);
      color: var(--muted); z-index: 10; font-size: 15px;
  }
  .input-icon-wrap input { padding-left: 45px !important; }

  #coupon_code:focus { box-shadow: none !important; border-color: transparent !important; }

  /* Checkout add-ons. Horizontal scroll rather than a wrapping grid: on a
     phone this keeps the section one row tall so it never pushes the order
     button below the fold. */
  .reco-wrap { background:#fff; border:1px solid #e9ecef; border-radius:14px; padding:16px; margin-top:18px; }
  .reco-head { display:flex; align-items:baseline; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
  .reco-title { font-weight:700; font-size:16px; color:#222; font-family:'Hind Siliguri',sans-serif; }
  .reco-sub { font-size:12.5px; color:#8b93a1; font-family:'Hind Siliguri',sans-serif; }
  .reco-row { display:flex; gap:12px; overflow-x:auto; padding-bottom:6px; scrollbar-width:thin; }
  .reco-row::-webkit-scrollbar { height:5px; }
  .reco-row::-webkit-scrollbar-thumb { background:#d5d9e0; border-radius:4px; }
  .reco-card { flex:0 0 152px; border:1px solid #eceff3; border-radius:11px; padding:9px; background:#fcfdfe; display:flex; flex-direction:column; }
  .reco-card img { width:100%; height:96px; object-fit:contain; border-radius:7px; background:#fff; }
  .reco-name { font-size:12.5px; line-height:1.35; margin:7px 0 4px; color:#333; font-family:'Hind Siliguri',sans-serif; flex:1; }
  .reco-price { font-size:14px; font-weight:700; color:#00276C; margin-bottom:7px; }
  .reco-price s { font-size:11.5px; font-weight:400; color:#9aa1ad; margin-left:4px; }
  .reco-btn { width:100%; border:none; border-radius:7px; padding:7px 4px; font-size:12.5px; font-weight:700;
              background:#00276C; color:#fff; cursor:pointer; font-family:'Hind Siliguri',sans-serif; transition:.15s; }
  .reco-btn:hover { filter:brightness(1.15); }
  .reco-btn:disabled { background:#16624a; cursor:default; filter:none; }
  @media (max-width:576px){ .reco-card { flex-basis:136px; } .reco-card img { height:84px; } }

  @media (max-width: 575px){
    .ship-radio-wrap{ grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
    .ship-card::after { width: 20px; height: 20px; font-size: 11px; }
  }
</style>

@php
use App\Models\Information;
use App\Models\BanglaText;
use App\Models\ManualPayment;
use Illuminate\Support\Facades\DB;

$info = Information::first();
$bangla_text = BanglaText::first();
$cart = session()->get('cart', []);
$is_free = 0;
foreach($cart as $item){
    if(!empty($item['is_free_shipping']) && $item['is_free_shipping'] == 1){ $is_free = 1; break; }
}

$globalSetting = DB::table('delivery_charges')->first();
$isWeightBased = $globalSetting && $globalSetting->charge_type == 'weight_based';
@endphp

<main class="main-wrapper">
 <section class="section-content py-5" style="margin-top:60px; background: #f5f6fa;">
    <div class="container">
        <form action="{{ route('front.checkouts.store')}}" method="POST" id="checkout_form">
            @csrf

            @php if(!session()->has('order_token')){ session(['order_token' => (string) \Illuminate\Support\Str::uuid()]); } @endphp
            <input type="hidden" name="order_token" value="{{ session('order_token') }}">

            <div class="row justify-content-center g-4">
                <div class="col-lg-8 col-md-10">

                    <div class="card border-0 shadow-sm rounded-4 p-4 orderDetails bg-white mb-4">
                        @include('frontend.cart.details')
                    </div>

                    <aside class="card border-0 shadow-sm rounded-4 p-4">
                        <h4 class="mb-4 fw-bold" style="font-family: 'Hind Siliguri', sans-serif; text-align: justify;">
                            {{ $bangla_text->checkout_form_top_text }}
                        </h4>

                        <div class="mb-3">
                            <label class="form-label fs-2 text-dark hind">{{ $bangla_text->name_text }}</label>
                            <input type="text" name="first_name" id="name" class="form-control rounded-3" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-2 text-dark hind">{{ $bangla_text->mobile_text }}</label>
                            <input type="text" maxlength="11" name="mobile" id="mobile" class="form-control rounded-3" required placeholder="01XXXXXXXXX">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-2 text-dark hind">{{ $bangla_text->address_text }}</label>
                            <input type="text" name="shipping_address" id="address" class="form-control rounded-3" placeholder="জেলা, থানা, গ্রাম/মহল্লা" required>
                        </div>

                        <input type="hidden" name="ip_address" id="ip_address" value="">

                        @php
                            $shipping_value = [];
                            foreach($cart as $citem) { $shipping_value[] = $citem['is_free_shipping']; }
                            $hasPaidShipping = (in_array(null, $shipping_value) || $is_free != 1);
                        @endphp

                        <div class="mb-3">
                          <label class="form-label fs-2 text-dark hind d-block text-center">{{ $bangla_text->delivery_text }}</label>

                          @if($hasPaidShipping)
                              <div class="ship-radio-wrap">
                                @foreach($charges as $charge)
                                  <label class="ship-option">
                                    <input type="radio" name="delivery_charge_id_radio" class="charge_radio_radioui" value="{{ $charge->id }}" data-charge="{{ $charge->amount }}">
                                    <div class="ship-card">
                                      <div class="ship-title">{{ $charge->title }}</div>
                                      <div class="ship-amount" id="dynamic_charge_display_{{ $charge->id }}">{{ number_format($charge->amount, 2) }} ৳</div>
                                    </div>
                                  </label>
                                @endforeach
                              </div>
                              <select required name="delivery_charge_id" id="selectCourier" class="form-select d-none">
                                @foreach($charges as $charge) <option value="{{ $charge->id }}" data-charge="{{ $charge->amount }}">{{ $charge->title }}</option> @endforeach
                              </select>
                          @else
                            <div class="ship-radio-wrap">
                              <label class="ship-option">
                                <input type="radio" name="delivery_charge_id_radio" class="charge_radio_radioui" value="0" data-charge="0" checked>
                                <div class="ship-card"><div class="ship-title">ফ্রী শিপিং</div><div class="ship-amount">0.00 ৳</div></div>
                              </label>
                            </div>
                            <select name="delivery_charge_id" id="selectCourier" class="d-none"><option value="0" data-charge="0" selected>ফ্রী শিপিং</option></select>
                          @endif
                        </div>

                        <div class="mb-3 mt-4">
                            <h6 class="fw-bold mb-2 text-dark" style="font-family: 'Hind Siliguri', sans-serif;">পেমেন্ট মেথড সিলেক্ট করুন:</h6>

                            @if(isset($info->cod_active) && $info->cod_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="Cash on Delivery" id="payment_cod"
                                           checked
                                           class="form-check-input" name="payment_method"
                                           style="width: 20px; height: 20px; cursor: pointer;"
                                           onchange="togglePaymentAction('cod')">
                                    <label for="payment_cod" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        ক্যাশ অন ডেলিভারি (Cash on Delivery)
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($info->bkash_active) && $info->bkash_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #E2136E !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="bkash" id="payment_bkash"
                                           class="form-check-input" name="payment_method"
                                           style="width: 20px; height: 20px; cursor: pointer;"
                                           onchange="togglePaymentAction('bkash')">
                                    <label for="payment_bkash" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                        বিকাশ পেমেন্ট (bKash)
                                        <img src="{{ asset('frontend/images/bkash_logo.png') }}" alt="bKash" style="height: 20px; width: auto; object-fit: contain;">
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($info->eps_active) && $info->eps_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #17a2b8 !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="eps" id="payment_eps"
                                           class="form-check-input" name="payment_method"
                                           style="width: 20px; height: 20px; cursor: pointer;"
                                           onchange="togglePaymentAction('eps')">
                                    <label for="payment_eps" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        EPS পেমেন্ট (Easy Payment System)
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($info->nagad_active) && $info->nagad_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #ED1C24 !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="nagad" id="payment_nagad"
                                           class="form-check-input" name="payment_method"
                                           style="width: 20px; height: 20px; cursor: pointer;"
                                           onchange="togglePaymentAction('nagad')">
                                    <label for="payment_nagad" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                        নগদ পেমেন্ট (Nagad)
                                        <img src="{{ asset('frontend/images/nagad.png') }}" alt="Nagad" style="height: 20px; width: auto; object-fit: contain;">
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($info->uddoktapay_active) && $info->uddoktapay_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #28a745 !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="uddoktapay" id="payment_uddoktapay"
                                           class="form-check-input" name="payment_method"
                                           style="width: 20px; height: 20px; cursor: pointer;"
                                           onchange="togglePaymentAction('uddoktapay')">
                                    <label for="payment_uddoktapay" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        উদ্দোক্তাপে (UddoktaPay)
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if(isset($info->ssl_active) && $info->ssl_active == 1)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label" style="border: 1px solid #00276C !important;">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="online" id="payment_online"
                                           {{ (!isset($info->cod_active) || $info->cod_active == 0) && (!isset($info->bkash_active) || $info->bkash_active == 0) && (!isset($info->eps_active) || $info->eps_active == 0) && (!isset($info->nagad_active) || $info->nagad_active == 0) && (!isset($info->uddoktapay_active) || $info->uddoktapay_active == 0) ? 'checked' : '' }}
                                           class="form-check-input" name="payment_method"
                                           style="width: 20px; height: 20px; cursor: pointer;"
                                           onchange="togglePaymentAction('online')">
                                    <label for="payment_online" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        অনলাইন পেমেন্ট (Card / SSL)
                                    </label>
                                </div>
                            </div>
                            @endif

                            @php $activeManuals = ManualPayment::where('status', 1)->get(); @endphp
                            @foreach($activeManuals as $mp)
                            <div class="card bg-white border shadow-sm rounded-3 mb-2 payment-card-label">
                                <div class="card-body d-flex align-items-center gap-3 p-3">
                                    <input type="radio" value="{{ $mp->name }}" id="payment_{{ $mp->id }}"
                                           class="form-check-input" name="payment_method"
                                           style="width: 20px; height: 20px; cursor: pointer;"
                                           onchange="togglePaymentAction('manual', '{{ $mp->name }}', '{{ $mp->number }}', '{{ $mp->type }}')">
                                    <label for="payment_{{ $mp->id }}" class="fw-bold text-dark mb-0 w-100" style="font-family: 'Hind Siliguri', sans-serif; cursor: pointer;">
                                        {{ $mp->name }} ({{ $mp->type }})
                                    </label>
                                </div>
                            </div>
                            @endforeach

                            @if((!isset($info->cod_active) || $info->cod_active == 0) && (!isset($info->ssl_active) || $info->ssl_active == 0) && (!isset($info->bkash_active) || $info->bkash_active == 0) && (!isset($info->eps_active) || $info->eps_active == 0) && (!isset($info->nagad_active) || $info->nagad_active == 0) && (!isset($info->uddoktapay_active) || $info->uddoktapay_active == 0) && $activeManuals->count() == 0)
                                <div class="alert alert-danger mt-2">
                                    দুঃখিত, বর্তমানে কোনো পেমেন্ট মেথড চালু নেই। অনুগ্রহ করে কর্তৃপক্ষের সাথে যোগাযোগ করুন।
                                </div>
                            @endif
                        </div>

                        <div id="manual_payment_area" style="display: none;" class="mt-3 mb-3 p-4 rounded-4">
                            <div class="manual-instruction-box d-flex align-items-center mb-4 p-3 rounded-3">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>
                                    <p id="payment_instruction" class="mb-0 fw-bold hind" style="font-size: 15px;"></p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label text-dark hind fw-bold" style="font-size: 14px;">যে নাম্বার থেকে টাকা পাঠিয়েছেন <span class="text-danger">*</span></label>
                                        <div class="input-icon-wrap">
                                            <i class="fas fa-phone-alt"></i>
                                            <input type="text" name="sender_number" id="sender_number" class="form-control" placeholder="017XXXXXXXX">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label text-dark hind fw-bold" style="font-size: 14px;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                        <div class="input-icon-wrap">
                                            <i class="fas fa-receipt"></i>
                                            <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="TRX123456789">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(isset($info->ssl_terms_active) && $info->ssl_terms_active == 1)
                        <div class="mb-3 mt-3" id="terms_checkbox_area" style="display: none;">
                            <div class="form-check d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" value="1" style="width: 20px; height: 20px; cursor: pointer;">
                                <label class="form-check-label text-dark hind mb-0" for="agree_terms" style="cursor: pointer; font-size: 15px;">
                                    I agree to the
                                    <a href="{{ route('front.privacyPolicy') }}" target="_blank" class="text-primary text-decoration-none fw-bold">Privacy Policy</a>,
                                    <a href="{{ url('/page/terms-condition') }}" target="_blank" class="text-primary text-decoration-none fw-bold">Terms & Conditions</a>,
                                    and
                                    <a href="{{ route('front.returnPolicy') }}" target="_blank" class="text-primary text-decoration-none fw-bold">Return Policy</a>.
                                </label>
                            </div>
                            <small class="text-danger d-none fw-bold mt-2" id="terms_error">You must agree to the terms and policies to proceed.</small>
                        </div>
                        @endif

                        <input type="hidden" name="amount" id="ssl_amount" value="{{ $totalPrice }}">

                        @if(isset($info->coupon_visibility) && $info->coupon_visibility == 1)
                        <div class="coupon-section mb-4 mt-3">
                            <label class="fw-bold mb-2" style="color: #333; font-size: 16px; font-family: 'Hind Siliguri', sans-serif;">কুপন কোড (যদি থাকে)</label>
                            <div class="d-flex align-items-center p-1" style="border: 1px solid #ccc; border-radius: 8px; background: #fff; height: 55px;">
                                <input type="text" id="coupon_code" class="form-control border-0 shadow-none" placeholder="এখানে কোড লিখুন..."
                                       style="height: 100%; background: transparent; font-size: 15px; flex-grow: 1;">
                                <button type="button" id="coupon_btn" onclick="applyCoupon()"
                                        class="btn h-100"
                                        style="padding: 0 24px; font-weight: bold; background-color: #00276C; color: white; border-radius: 6px; border: none; font-size: 14px; white-space: nowrap;">
                                    APPLY
                                </button>
                            </div>
                            <small id="coupon_message" class="d-block mt-2 fw-bold"></small>
                        </div>
                        @endif

                        <div class="d-grid mt-4">
                            <button type="submit" id="chk_btn" class="btn btn-lg fw-bold main-bg fs-2 text-white rounded-3 hind">
                                {{ $bangla_text->order_confirm_text }}
                            </button>
                        </div>
                    </aside>
                </div>


                @if(!empty($recoProducts) && count($recoProducts))
                <div class="reco-wrap">
                    <div class="reco-head">
                        <span class="reco-title">{{ ($info->checkout_reco_title ?? null) ?: 'এগুলোও নিতে পারেন' }}</span>
                        <span class="reco-sub">এক ক্লিকেই অর্ডারে যোগ করুন</span>
                    </div>
                    <div class="reco-row">
                        @foreach($recoProducts as $rp)
                            @php
                                $rpPrice = ($rp->after_discount && $rp->after_discount > 0) ? $rp->after_discount : $rp->sell_price;
                                $rpImg   = $rp->thumb_image ? asset('thumb_products/'.$rp->thumb_image) : asset('products/'.$rp->image);
                            @endphp
                            <div class="reco-card" data-reco-id="{{ $rp->id }}">
                                <img src="{{ $rpImg }}" alt="{{ $rp->name }}" loading="lazy">
                                <div class="reco-name">{{ \Illuminate\Support\Str::limit($rp->name, 42) }}</div>
                                <div class="reco-price">
                                    ৳{{ number_format((float) $rpPrice, 0) }}
                                    @if($rp->after_discount && $rp->after_discount > 0 && $rp->sell_price > $rp->after_discount)
                                        <s>৳{{ number_format((float) $rp->sell_price, 0) }}</s>
                                    @endif
                                </div>
                                <button type="button" class="reco-btn" onclick="addRecoToCart({{ $rp->id }}, this)">
                                    <i class="fas fa-plus"></i> যোগ করুন
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @foreach($cart as $item)
                    <div class="cart-item-data"
                         data-product-id="{{ $item['product_id'] }}"
                         data-product-name="{{ $item['name'] }}"
                         data-category-name="{{ $item['category_name'] }}"
                         data-price="{{ $item['price'] }}"
                         data-quantity="{{ $item['quantity'] }}">
                    </div>
                @endforeach

                <input type="hidden" name="total_cart_price" value="{{ $totalPrice }}">
            </div>
        </form>
    </div>
</section>
</main>

<div class="modal fade" id="otpModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-modal-content p-4">
      <div class="modal-header border-0 pb-0 justify-content-end">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center pt-0 pb-4">
        <div class="otp-icon-box"><i class="fas fa-shield-alt fa-2x"></i></div>
        <h4 class="fw-bold mb-2 otp-title">মোবাইল ভেরিফিকেশন</h4>
        <p class="otp-subtitle">আপনার <span class="fw-bold text-dark" id="otp_sent_number"></span> নাম্বারে কোড পাঠানো হয়েছে।</p>
        <div class="form-group mb-4">
            <input type="text" id="otp_input" maxlength="4" class="form-control otp-input" placeholder="____" autocomplete="one-time-code" inputmode="numeric">
            <small class="text-danger mt-2 d-block fw-bold" id="otp_error"></small>
        </div>
        <button type="button" class="btn-verify" onclick="verifyOtpNow()">যাচাই করুন (Verify)</button>
        <div class="text-center mt-3">
             <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" id="resendOtpBtn" onclick="resendOtp()">
                 কোড পাননি? <span class="text-primary fw-bold">আবার পাঠান</span>
             </button>
        </div>
      </div>
    </div>
  </div>
</div>

@if(isset($info->bkash_active) && $info->bkash_active == 1)
    <button id="bKash_button" style="display: none;"></button>
@endif

@endsection

@push('js')
<script src="{{ asset('frontend/js/checkout.js')}}"></script>

@if(isset($info->bkash_active) && $info->bkash_active == 1)
    @php
        $bkashScriptUrl = (isset($info->bkash_sandbox) && $info->bkash_sandbox == 1)
            ? 'https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js'
            : 'https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js';
    @endphp
    <script src="{{ $bkashScriptUrl }}"></script>
@endif

<script>
var isTermsEnabled = {{ (isset($info->ssl_terms_active) && $info->ssl_terms_active == 1) ? 'true' : 'false' }};
var isWeightBased = {{ $isWeightBased ? 'true' : 'false' }};

var current_discount_val = {{ session('coupon_discount') ? session('coupon_discount') : 0 }};
var current_discount_type = "{{ session('discount_type') ? session('discount_type') : 'fixed' }}";

var paymentID;
var dynamicOrderId;
var successUrl = '';

fetch('https://api.ipify.org?format=json').then(r => r.json()).then(data => { const el = document.getElementById('ip_address'); if(el) el.value = data.ip; }).catch(console.error);

window.togglePaymentAction = function(method, name = '', number = '', type = '') {
    var form = document.getElementById('checkout_form');
    var btn = $('#chk_btn');
    var termsArea = $('#terms_checkbox_area');
    var manualArea = $('#manual_payment_area');
    var sNum = $('#sender_number');
    var tId = $('#transaction_id');

    if(method === 'online') {
        form.action = "{{ url('/pay') }}";
        btn.text('Pay Now (Card/SSL)');
        btn.removeClass('is-disabled').removeAttr('disabled');
        if(typeof isTermsEnabled !== 'undefined' && isTermsEnabled) termsArea.slideDown();
        manualArea.slideUp();
        sNum.removeAttr('required');
        tId.removeAttr('required');
    } else if(method === 'bkash') {
        form.action = "{{ route('front.checkouts.store') }}";
        btn.text('Pay with bKash');
        btn.removeClass('is-disabled').removeAttr('disabled');
        if(typeof isTermsEnabled !== 'undefined' && isTermsEnabled) termsArea.slideDown();
        manualArea.slideUp();
        sNum.removeAttr('required');
        tId.removeAttr('required');
    } else if(method === 'eps') {
        form.action = "{{ route('front.checkouts.store') }}";
        btn.text('Pay with EPS');
        btn.removeClass('is-disabled').removeAttr('disabled');
        if(typeof isTermsEnabled !== 'undefined' && isTermsEnabled) termsArea.slideDown();
        manualArea.slideUp();
        sNum.removeAttr('required');
        tId.removeAttr('required');
    } else if(method === 'nagad') {
        form.action = "{{ route('front.checkouts.store') }}";
        btn.text('Pay with Nagad');
        btn.removeClass('is-disabled').removeAttr('disabled');
        if(typeof isTermsEnabled !== 'undefined' && isTermsEnabled) termsArea.slideDown();
        manualArea.slideUp();
        sNum.removeAttr('required');
        tId.removeAttr('required');
    } else if(method === 'uddoktapay') {
        form.action = "{{ route('front.checkouts.store') }}";
        btn.text('Pay with UddoktaPay');
        btn.removeClass('is-disabled').removeAttr('disabled');
        if(typeof isTermsEnabled !== 'undefined' && isTermsEnabled) termsArea.slideDown();
        manualArea.slideUp();
        sNum.removeAttr('required');
        tId.removeAttr('required');
    } else if(method === 'manual') {
        form.action = "{{ route('front.checkouts.store') }}";
        btn.text("{{ $bangla_text->order_confirm_text }}");
        btn.removeClass('is-disabled').removeAttr('disabled');
        if(typeof isTermsEnabled !== 'undefined' && isTermsEnabled) termsArea.slideUp();
        $('#payment_instruction').text(`দয়া করে আপনার টোটাল বিল ${number} (${type}) নাম্বারে Send Money করুন। এরপর নিচের তথ্যগুলো দিন।`);
        manualArea.slideDown();
        sNum.attr('required', 'required');
        tId.attr('required', 'required');
    } else {
        form.action = "{{ route('front.checkouts.store') }}";
        btn.text("{{ $bangla_text->order_confirm_text }}");
        btn.removeClass('is-disabled').removeAttr('disabled');
        if(typeof isTermsEnabled !== 'undefined' && isTermsEnabled) termsArea.slideUp();
        manualArea.slideUp();
        sNum.removeAttr('required');
        tId.removeAttr('required');
    }
};

function applyCoupon() {
    var code = $('#coupon_code').val();
    var btn = $('#coupon_btn');
    var msg = $('#coupon_message');
    if(!code) { msg.text('Please enter a coupon code').css('color', 'red'); return; }

    btn.prop('disabled', true).text('Checking...');
    msg.text('');

    $.ajax({
        url: "{{ route('front.getCouponDiscount') }}",
        method: "GET",
        data: { code: code },
        success: function(response) {
            if(response.success) {
                let displayAmount = (response.discount_type === 'percent' || response.discount_type === 'percentage')
                                    ? response.amount + '%'
                                    : '৳' + response.amount;

                msg.text(response.msg + ' (' + displayAmount + ' ছাড়)').css('color', 'green');
                btn.prop('disabled', false).text('Apply');

                current_discount_val = parseFloat(response.amount);
                current_discount_type = response.discount_type;

                calculate_total();

            } else {
                msg.text(response.msg).css('color', 'red');
                btn.prop('disabled', false).text('Apply');
            }
        },
        error: function() {
            msg.text('Error!').css('color', 'red');
            btn.prop('disabled', false).text('Apply');
        }
    });
}

// Adds a checkout add-on straight from this page. The server recalculates the
// whole cart anyway, so a reload is the honest way to refresh the totals,
// delivery charge and free-shipping state without duplicating that maths here.
function addRecoToCart(productId, btn) {
    if (btn.disabled) return;

    var original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'যোগ হচ্ছে...';

    $.ajax({
        url: "{{ route('front.carts.storeCart') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            product_id: productId,
            quantity: 1,
            action_type: 'cart'
        },
        success: function (res) {
            if (res && res.success === false) {
                btn.disabled = false;
                btn.innerHTML = original;
                if (typeof toastr !== 'undefined') toastr.error(res.msg || 'যোগ করা গেল না');
                else alert(res.msg || 'যোগ করা গেল না');
                return;
            }
            btn.innerHTML = '<i class="fas fa-check"></i> যোগ হয়েছে';
            setTimeout(function () { window.location.reload(); }, 500);
        },
        error: function () {
            btn.disabled = false;
            btn.innerHTML = original;
            if (typeof toastr !== 'undefined') toastr.error('যোগ করা গেল না, আবার চেষ্টা করুন');
            else alert('যোগ করা গেল না, আবার চেষ্টা করুন');
        }
    });
}

function calculate_total() {
    var subtotal = parseFloat($('#subtotal').val() || 0);

    var discount_val = current_discount_val;
    var discount_type = current_discount_type;

    var final_discount = 0;
    if (discount_type === 'percentage' || discount_type === 'percent') {
        final_discount = (subtotal * discount_val) / 100;
    } else {
        final_discount = discount_val;
    }

    if(final_discount > 0) {
        $('.coupon_discount_display').text('- ৳' + final_discount.toFixed(0));
        $('.coupon_row').removeClass('d-none').addClass('d-flex');
    } else {
        $('.coupon_discount_display').text('');
        $('.coupon_row').removeClass('d-flex').addClass('d-none');
    }

    if(isWeightBased && !{{ $is_free ? 'true' : 'false' }}) {
        $('.charge_radio_radioui').each(function() {
            let radio = $(this);
            let cid = radio.val();

            if(cid && cid !== 'dynamic' && cid !== '0') {
                $.ajax({
                    url: "{{ route('front.getDeliveryChargeAjax') }}",
                    type: "POST",
                    data: {
                        delivery_charge_id: cid,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if(res.success) {
                            let dynamic_charge = parseFloat(res.charge);

                            $('#dynamic_charge_display_' + cid).text(dynamic_charge.toFixed(2) + ' ৳');
                            radio.data('charge', dynamic_charge);

                            if(radio.is(':checked')) {
                                $('#selectCourier').data('charge', dynamic_charge);
                                $('#d_charge').text('৳' + dynamic_charge.toFixed(0));
                                $('.delivery_charge').text('৳' + dynamic_charge.toFixed(0));

                                var total = subtotal + dynamic_charge - final_discount;
                                if(total < 0) total = 0;

                                $('.total').text('৳' + total.toFixed(0));
                                $('input[name="final_amount"]').val(total);
                                $('#ssl_amount').val(total);
                            }
                        }
                    }
                });
            }
        });
    } else {
        var shipping_charge = 0;
        var selected_shipping = $('input[name="delivery_charge_id_radio"]:checked');
        if (selected_shipping.length > 0) {
            shipping_charge = parseFloat(selected_shipping.data('charge'));
        } else {
            var selectVal = $('#selectCourier').find(':selected');
            if(selectVal.length > 0){ shipping_charge = parseFloat(selectVal.data('charge')); }
        }

        $('#d_charge').text('৳' + shipping_charge.toFixed(0));
        $('.delivery_charge').text('৳' + shipping_charge.toFixed(0));

        var total = subtotal + shipping_charge - final_discount;
        if(total < 0) total = 0;

        $('.total').text('৳' + total.toFixed(0));
        $('input[name="final_amount"]').val(total);
        $('#ssl_amount').val(total);
    }
}

function deleteCartItem(url) {
    if(confirm('Delete this item?')) {
        $.ajax({
            url: url, type: 'POST', data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(res) { window.location.reload(); },
            error: function() { alert('Error!'); }
        });
    }
}

$(document).ready(function(){
  $(document).on('change', 'input[name="delivery_charge_id_radio"]', function(){
      var val = $(this).val();
      if($('#selectCourier').is('select')) {
          $('#selectCourier').val(val).trigger('change');
      }
      calculate_total();
  });

  var $radios = $('input[name="delivery_charge_id_radio"]');
  if($radios.length && !$radios.filter(':checked').length){ $radios.first().prop('checked', true).trigger('change'); }
  calculate_total();

  var selectedPayment = $('input[name="payment_method"]:checked');
  if(selectedPayment.length > 0){
      selectedPayment.trigger('change');
  }
});

var autoSaveTimer;
function saveIncompleteData() {
    var mobile = $('#mobile').val();

    if (mobile && mobile.length >= 11) {
        var formData = $('#checkout_form').serialize();
        $.ajax({
            url: "{{ route('incompleteStore') }}",
            type: "POST",
            data: formData,
            success: function(response) {},
            error: function(err) {}
        });
    }
}

$(document).on('input', '#mobile', function() {
    var banglaDigits = {'০':'0', '১':'1', '২':'2', '৩':'3', '৪':'4', '৫':'5', '৬':'6', '৭':'7', '৮':'8', '৯':'9'};
    var value = $(this).val();

    var converted = value.replace(/[০-৯]/g, function(match) {
        return banglaDigits[match];
    });

    converted = converted.replace(/[^0-9]/g, '');

    $(this).val(converted);

    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(saveIncompleteData, 2000);
});

$(document).on('blur', '#mobile, #name, #address', function() {
    saveIncompleteData();
});

var isOtpVerified = false;
var otpSystemEnabled = {{ $info->otp_system ?? 0 }};
var timerInterval;
var isSendingOtp = false;

function startTimer(duration, display) {
    var timer = duration, seconds;
    clearInterval(timerInterval);
    $('#resendOtpBtn').prop('disabled', true).addClass('text-muted').removeClass('text-primary');
    timerInterval = setInterval(function () {
        seconds = parseInt(timer % 60, 10);
        seconds = seconds < 10 ? "0" + seconds : seconds;
        display.html("Wait (" + seconds + "s)");
        if (--timer < 0) {
            clearInterval(timerInterval);
            display.html("Resend Code");
            $('#resendOtpBtn').prop('disabled', false).removeClass('text-muted').addClass('text-primary');
        }
    }, 1000);
}

@if(isset($info->bkash_active) && $info->bkash_active == 1)
function initBkash() {
    bKash.init({
        paymentMode: 'checkout',
        paymentRequest: { "amount": "0", "intent": "sale" },
        createRequest: function (request) {
            $.ajax({
                url: "{{ route('bkash.create') }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", order_id: dynamicOrderId },
                success: function (data) {
                    if (data && data.paymentID != null) {
                        paymentID = data.paymentID;
                        bKash.create().onSuccess(data);
                    } else {
                        bKash.create().onError();
                        alert("Payment Error: " + (data.errorMessage || "Something went wrong"));
                        $('#chk_btn').removeClass('is-disabled').removeAttr('disabled').text("Pay with bKash");
                    }
                },
                error: function (err) {
                    bKash.create().onError();
                    alert("Server error while connecting to bKash.");
                    $('#chk_btn').removeClass('is-disabled').removeAttr('disabled').text("Pay with bKash");
                }
            });
        },
        executeRequestOnAuthorization: function () {
            $.ajax({
                url: "{{ route('bkash.execute') }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", paymentID: paymentID },
                success: function (data) {
                    if (data && data.paymentID != null && data.transactionStatus === 'Completed') {
                        window.location.href = successUrl;
                    } else {
                        bKash.execute().onError();
                        alert("Payment Failed! " + (data.errorMessage || ""));
                    }
                },
                error: function () {
                    bKash.execute().onError();
                    alert("Failed to execute bKash payment.");
                }
            });
        },
        onClose: function () {
            window.location.href = successUrl;
        }
    });
}
@endif

function submitOrderFinal() {
    var form = document.getElementById('checkout_form');
    var $form = $(form);
    var btn = $('#chk_btn');

    var paymentMethod = $('input[name="payment_method"]:checked').val();

    if(paymentMethod === 'online') {
        form.action = "{{ url('/pay') }}";
        form.submit();
        return;
    }

    btn.addClass('is-disabled').attr('disabled', 'disabled').text('Processing...');

    $.ajax({
        type: 'POST',
        url: "{{ route('front.checkouts.store') }}",
        data: $form.serialize(),
        success: function(res){
            if(res.success){
                if(paymentMethod === 'bkash') {
                    dynamicOrderId = res.order_id || res.url.split('/').pop();
                    successUrl = res.url;

                    @if(isset($info->bkash_active) && $info->bkash_active == 1)
                        initBkash();
                        setTimeout(() => { $('#bKash_button').click(); }, 300);
                    @endif
                } else if(paymentMethod === 'nagad') {
                    let finalOrderId = res.order_id || res.url.split('/').pop();
                    window.location.href = "{{ url('nagad/pay') }}/" + finalOrderId;
                } else if(paymentMethod === 'uddoktapay') {
                    let finalOrderId = res.order_id || res.url.split('/').pop();
                    window.location.href = "{{ url('uddoktapay/pay') }}/" + finalOrderId;
                } else if(res.url) {
                    window.location.href = res.url;
                } else {
                    location.reload();
                }
            } else {
                if(typeof toastr !== 'undefined') toastr.error(res.msg);
                btn.removeClass('is-disabled').removeAttr('disabled').text("{{ $bangla_text->order_confirm_text }}");
            }
        },
        error: function(err){
            btn.removeClass('is-disabled').removeAttr('disabled').text("{{ $bangla_text->order_confirm_text }}");
            if(typeof toastr !== 'undefined') toastr.error('Network Error');
        }
    });
}

function sendOtpBeforeSubmit() {
    if(isSendingOtp) return;
    var mobile = $('#mobile').val();

    if(!mobile || mobile.length !== 11 || !mobile.startsWith('01')) {
        alert('দয়া করে সঠিক ১১ ডিজিটের মোবাইল নাম্বার দিন (অবশ্যই ০১ দিয়ে শুরু হতে হবে)।');
        return;
    }

    isSendingOtp = true;
    $('#chk_btn').addClass('is-disabled').text('Sending OTP...');

    $.ajax({
        url: "{{ route('sendOtp') }}", type: "POST", data: { mobile: mobile, _token: "{{ csrf_token() }}" },
        success: function(res) {
            isSendingOtp = false;
            $('#chk_btn').removeClass('is-disabled').text("{{ $bangla_text->order_confirm_text }}");
            if(res.success) {
                $('#otp_sent_number').text(mobile);
                $('#otpModal').appendTo('body');
                var myModal = new bootstrap.Modal(document.getElementById('otpModal'));
                myModal.show();

                setTimeout(function() { $('#otp_input').focus(); }, 500);
                startTimer(30, $('#resendOtpBtn'));
            } else { alert(res.msg); }
        },
        error: function() { isSendingOtp = false; $('#chk_btn').removeClass('is-disabled').text("Submit"); }
    });
}

function verifyOtpNow() {
    var code = $('#otp_input').val();
    var mobile = $('#mobile').val();
    $.ajax({
        url: "{{ route('verifyOtp') }}", type: "POST", data: { otp: code, mobile: mobile, _token: "{{ csrf_token() }}" },
        success: function(res) {
            if(res.success) {
                isOtpVerified = true;
                bootstrap.Modal.getInstance(document.getElementById('otpModal')).hide();
                submitOrderFinal();
            } else { $('#otp_error').text(res.msg); }
        }
    });
}

(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('checkout_form');
    if(!form) return;

    form.addEventListener('submit', function(e){
      e.preventDefault();
      e.stopImmediatePropagation();

      var paymentMethod = $('input[name="payment_method"]:checked').val();

      if((paymentMethod === 'online' || paymentMethod === 'bkash' || paymentMethod === 'eps' || paymentMethod === 'nagad' || paymentMethod === 'uddoktapay') && typeof isTermsEnabled !== 'undefined' && isTermsEnabled) {
          if(!$('#agree_terms').is(':checked')) {
              $('#terms_error').removeClass('d-none');
              return;
          } else {
              $('#terms_error').addClass('d-none');
          }
      }

      if(paymentMethod !== 'online' && paymentMethod !== 'bkash' && paymentMethod !== 'eps' && paymentMethod !== 'nagad' && paymentMethod !== 'uddoktapay' && paymentMethod !== 'Cash on Delivery' && paymentMethod !== 'cod') {
          var sNumVal = $('#sender_number').val();
          var tIdVal = $('#transaction_id').val();
          if(!sNumVal) { alert('যে নাম্বার থেকে টাকা পাঠিয়েছেন তা লিখুন'); return; }
          if(!tIdVal) { alert('Transaction ID লিখুন'); return; }
      }

      if(paymentMethod === 'online' && !otpSystemEnabled) {
          form.action = "{{ url('/pay') }}";
          form.submit();
          return;
      }

      var mobile = $('#mobile').val();
      var address = $('#address').val();
      var name = $('#name').val();
      if(!name){ alert('Please enter your name'); return; }

      if(!mobile || mobile.length !== 11 || !mobile.startsWith('01')){
          alert('দয়া করে সঠিক ১১ ডিজিটের মোবাইল নাম্বার দিন (অবশ্যই ০১ দিয়ে শুরু হতে হবে)।');
          return;
      }

      if(!address){ alert('Please enter your address'); return; }

      if(otpSystemEnabled == 1 && !isOtpVerified) {
          sendOtpBeforeSubmit();
      } else {
          submitOrderFinal();
      }
    });
  });
})();

$(document).ready(function(){
    if (window.hasFiredInitiateCheckout) return;
    window.hasFiredInitiateCheckout = true;

    var items = [];
    var gtmItems = [];

    $('.cart-item-data').each(function() {
        items.push({
            id: $(this).data('product-id'),
            quantity: $(this).data('quantity'),
            price: $(this).data('price')
        });

        gtmItems.push({
            item_id: $(this).data('product-id').toString(),
            item_name: $(this).data('product-name'),
            item_category: $(this).data('category-name'),
            price: parseFloat($(this).data('price')),
            quantity: parseInt($(this).data('quantity'))
        });
    });

    var total = parseFloat($('input[name="total_cart_price"]').val() || 0);

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ ecommerce: null });
    window.dataLayer.push({
        event: "begin_checkout",
        event_id: {!! json_encode($icEventId ?? null) !!},
        ecommerce: {
            currency: "BDT",
            value: total,
            items: gtmItems
        }
    });

    if (@json(!$__hasGtm) && typeof fbq === 'function') {
        fbq('track', 'InitiateCheckout', {
            value: total,
            currency: 'BDT',
            content_ids: items.map(i => i.id),
            contents: items.map(i => ({ id: i.id, quantity: i.quantity, item_price: i.price })),
            content_type: 'product',
            num_items: items.length
        }, { eventID: {!! json_encode($icEventId ?? null) !!} || undefined });
    }

    if (@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
        ttq.track('InitiateCheckout', {
            value: total,
            currency: 'BDT',
            content_type: 'product',
            contents: gtmItems.map(i => ({
                content_id: i.item_id,
                content_type: 'product',
                content_name: i.item_name,
                price: i.price,
                quantity: i.quantity
            }))
        }, { event_id: {!! json_encode($ttIcEventId ?? null) !!} || undefined });
    }
});
</script>
@endpush