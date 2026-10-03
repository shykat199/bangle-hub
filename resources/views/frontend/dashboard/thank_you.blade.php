@extends('frontend.app')

@section('content')

@php
    $number = $order->user->mobile ?? $order->mobile;
@endphp

<main class="main-wrapper">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg rounded-4 text-center p-5" style="background: linear-gradient(135deg, #F5F7FA 0%, #FFFFFF 100%); font-family: 'Hind Siliguri', sans-serif;">
                    
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="text-success" width="90" height="90" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                        </svg>
                    </div>

                    <h1 class="fw-bold mb-3" style="font-family: 'Hind Siliguri', sans-serif; font-size: 3rem; color:#0FA298;">ধন্যবাদ !</h1>
                    <h3 class="text-muted mb-3" style="font-family: 'Hind Siliguri', sans-serif; font-size: 2rem;">আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে।</h3>
                    <p class="mb-4 px-lg-5" style="color:#555; font-size: 1.8rem; font-family: 'Hind Siliguri', sans-serif">
                        আমাদের একজন বিক্রয় প্রতিনিধি শীঘ্রই আপনার সাথে যোগাযোগ করবে অর্ডার নিশ্চিত করার জন্য।
                    </p>

                    <p class="mb-4" style="font-size: 1.8rem; font-family: 'Hind Siliguri', sans-serif">
                        আপনার অর্ডার নম্বর: 
                        <b>
                            <a href="{{ route('front.orders.show', ['order' => $order->id, 't' => $order->order_token]) }}" target="_blank" class="text-decoration-none">
                                #{{$order->id}}
                            </a>
                        </b>
                    </p>

                    <div class="d-flex justify-content-center flex-wrap gap-3">
                        <a href="{{ route('front.home') }}" class="btn btn-outline-primary rounded-pill px-5 py-3 shadow-sm fs-3" style="font-family: 'Hind Siliguri', sans-serif">হোমে ফিরে যান</a>
                        <a target="_blank" href="{{ route('front.orders.show', ['order' => $order->id, 't' => $order->order_token]) }}" class="btn btn-outline-primary rounded-pill px-5 py-3 shadow-sm fs-3" style="font-family: 'Hind Siliguri', sans-serif;">ইনভয়েস প্রিন্ট করুন</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Customer Percentage Check Logic
setTimeout(customerPercentageCheck, 500);

function customerPercentageCheck(){
    const customerPhone = {!! json_encode($number) !!};
    const customer_id = {!! json_encode($order->user_id) !!};
    
    if (!customerPhone) return;

    $.ajax({
        url: "{{ route('courierPercentage') }}",
        type: 'GET',
        dataType: 'json',
        data: {'phone': customerPhone, 'id': customer_id},
        success: function(response) {
            console.log('Customer percentage data fetched successfully.');
        },
        error: function(xhr, status, error) {
            console.error('Error fetching customer percentage:', error);
        }
    });
}

// 🟢 Dynamic GTM DataLayer & Facebook Pixel Logic for Purchase Event 🟢
function generateDataLayer(transaction_id) {
    var items = [];
    
    // Preparing Items Array
    @foreach($order->details as $detail)
        items.push({
            item_id: "{{ $detail->product_id }}",
            item_name: {!! json_encode($detail->product->name) !!}, 
            price: {{ (float) $detail->unit_price }},
            quantity: {{ (int) $detail->quantity }}
        });
    @endforeach

    let total = {{ (float) $order->final_amount }};
    let ship_charge = {{ (float) $order->shipping_charge }};

    // ✅ Shared Purchase Event ID (browser pixel + GTM + server CAPI dedup)
    const eventId = 'PUR_' + transaction_id;

    // ✅ GTM DataLayer Push (Safe & GA4 Compliant - PII Removed)
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ ecommerce: null });
    window.dataLayer.push({
        event: 'purchase',
        event_id: eventId,
        ecommerce: {
            currency: "BDT",
            value: total,
            shipping: ship_charge,
            transaction_id: transaction_id.toString(),
            items: items
        }
    });

@if(!$__hasGtm)
    // ✅ Facebook Pixel Push — only when GTM is NOT configured (GTM would otherwise fire it → double)
    if (typeof fbq === 'function') {
        fbq('track', 'Purchase', {
            value: total,
            currency: 'BDT',
            contents: items.map(i => ({
                id: i.item_id,
                quantity: i.quantity,
                item_price: i.price
            })),
            content_type: 'product',
            content_ids: items.map(i => i.item_id)
        }, { eventID: eventId });
    }
@endif

    // ✅ TikTok Pixel CompletePayment (TikTok-er Purchase-equivalent event)
    if (@json(!$__ttFromGtm) && typeof ttq !== 'undefined' && ttq.track) {
        ttq.track('CompletePayment', {
            value: total,
            currency: 'BDT',
            contents: items.map(i => ({
                content_id: i.item_id,
                content_type: 'product',
                content_name: i.item_name,
                price: i.price,
                quantity: i.quantity
            }))
        }, { event_id: eventId });
    }
}

// Trigger tracking events 500ms after page load.
// Fresh orders only: the invoice link is reopenable forever (?t= token), and
// pixel event_id dedup covers just 48h — firing on a late revisit would count
// a brand-new Purchase. Within 24h repeats are deduped by the shared eventId.
@if($order->created_at && $order->created_at->gt(now()->subHours(24)))
setTimeout(() => {
    generateDataLayer("{{ $order->id }}");
}, 500);
@endif
</script>
 
@endsection