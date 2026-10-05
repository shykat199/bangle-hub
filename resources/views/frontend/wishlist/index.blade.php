@extends('frontend.app')

@section('content')
@php $wlAccent = themeAccent('#e11d2e'); @endphp

<style>
    .wl-page{ background: #f8f8fa; padding: 28px 0 56px; min-height: 60vh; }
    .wl-head{ display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 8px 16px; margin-bottom: 20px; }
    .wl-head h1{ margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -.015em; color: #0f172a; }
    .wl-head p{ margin: 4px 0 0; font-size: 14px; color: #64748b; }
    .wl-note{ font-size: 13px; color: #64748b; }
    .wl-note a{ color: {{ $wlAccent }}; font-weight: 700; text-decoration: underline; }

    .wl-grid{ display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; }
    @media (max-width: 1199.98px){ .wl-grid{ grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (max-width: 991.98px){ .wl-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 575.98px){ .wl-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; } .wl-head h1{ font-size: 22px; } }

    .wl-item{ display: flex; flex-direction: column; gap: 8px; min-width: 0; transition: opacity .25s ease, transform .25s ease; }
    .wl-item.is-leaving{ opacity: 0; transform: scale(.96); }
    .wl-item > .axil-product{ flex: 1 1 auto; }
    .wl-actions{ display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; }
    .wl-actions .wl-act.is-primary{ min-width: 0; overflow: hidden; }
    @media (max-width: 575.98px){ .wl-act{ height: 38px; padding: 0 8px; font-size: 12px; gap: 4px; } .wl-act.is-remove{ width: 38px; } .wl-actions{ gap: 6px; } }
    .wl-act{
        height: 40px; padding: 0 12px; border-radius: 10px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        font-size: 13px; font-weight: 700; line-height: 1; white-space: nowrap; text-decoration: none !important;
        transition: background .18s ease, color .18s ease, border-color .18s ease;
    }
    .wl-act.is-primary{ background: {{ $wlAccent }}; color: #fff !important; border: 1.5px solid {{ $wlAccent }}; }
    .wl-act.is-primary:hover{ filter: brightness(.94); }
    .wl-act.is-primary[disabled]{ background: #e2e8f0; border-color: #e2e8f0; color: #64748b !important; cursor: not-allowed; filter: none; }
    .wl-act.is-remove{ width: 40px; padding: 0; background: #fff; color: #64748b; border: 1.5px solid #e2e8f0; }
    .wl-act.is-remove:hover{ color: {{ $wlAccent }}; border-color: {{ $wlAccent }}; }

    .wl-empty{
        max-width: 460px; margin: 40px auto 0; padding: 40px 24px; text-align: center;
        background: #fff; border: 1px solid #eaecf0; border-radius: 22px;
    }
    .wl-empty svg{ width: 48px; height: 48px; fill: none; stroke: {{ $wlAccent }}; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
    .wl-empty h2{ margin: 14px 0 6px; font-size: 20px; font-weight: 700; color: #0f172a; }
    .wl-empty p{ margin: 0 0 18px; font-size: 14px; color: #64748b; }
    .wl-empty .wl-act{ display: inline-flex; height: 44px; padding: 0 22px; }
</style>

<main class="main-wrapper wl-page">
    <div class="container">

        <div class="wl-head">
            <div>
                <h1>My Wishlist</h1>
                <p><span id="wlTotal">{{ $items->count() }}</span> saved <span id="wlNoun">{{ $items->count() === 1 ? 'product' : 'products' }}</span></p>
            </div>
            @guest
                <div class="wl-note">
                    Saved on this device only. <a href="{{ route('login') }}">Log in</a> to keep your wishlist on your account.
                </div>
            @endguest
        </div>

        <div class="wl-grid" id="wlGrid" @if($items->isEmpty()) hidden @endif>
            @foreach($items as $product)
                @php
                    $wlOut      = !productIsOrderable($product);
                    $wlVariable = $product->type === 'variable';
                    $wlUrl      = route('front.products.show', ['product' => $product->slug ?: $product->id]);
                    $wlVarId    = optional($product->variation)->id ?? optional($product->variations->first())->id;
                @endphp
                <div class="wl-item" data-wl-item="{{ $product->id }}">
                    @include('frontend.products.partials.product_section')

                    <div class="wl-actions">
                        @if($wlOut)
                            <button type="button" class="wl-act is-primary" disabled>Out of Stock</button>
                        @elseif($wlVariable)
                            <a href="{{ $wlUrl }}" class="wl-act is-primary"><i class="far fa-eye"></i> Choose Options</a>
                        @else
                            <button type="button" class="wl-act is-primary ajax-add-btn"
                                    data-url="{{ route('front.carts.store') }}"
                                    data-product="{{ $product->id }}"
                                    data-variation="{{ $wlVarId }}"
                                    data-token="{{ csrf_token() }}">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        @endif
                        <button type="button" class="wl-act is-remove wl-toggle" data-product="{{ $product->id }}"
                                title="Remove from Wishlist" aria-label="Remove from Wishlist">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="wl-empty" id="wlEmpty" @if($items->isNotEmpty()) hidden @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5s-7.5-4.4-9.6-9.1C1 8.1 2.9 4.5 6.5 4.5c2 0 3.6 1 4.6 2.5.3.4.6.4.9 0 1-1.5 2.6-2.5 4.6-2.5 3.6 0 5.5 3.6 4.1 6.9-2.1 4.7-9.6 9.1-9.6 9.1z"/></svg>
            <h2>Your wishlist is empty</h2>
            <p>Tap the heart on any product to save it here for later.</p>
            <a href="{{ route('front.products.index') }}" class="wl-act is-primary">Browse Products</a>
        </div>

    </div>
</main>
@endsection

@push('js')
<script>
// On this page, un-saving a product (heart or bin) takes its card out of the list.
document.addEventListener('wishlist:changed', function(e){
    if(e.detail.saved) return;
    var item = document.querySelector('[data-wl-item="' + e.detail.id + '"]');
    if(!item) return;
    item.classList.add('is-leaving');
    setTimeout(function(){
        item.remove();
        var left = document.querySelectorAll('[data-wl-item]').length;
        document.getElementById('wlTotal').textContent = left;
        document.getElementById('wlNoun').textContent = left === 1 ? 'product' : 'products';
        if(left === 0){
            document.getElementById('wlGrid').hidden = true;
            document.getElementById('wlEmpty').hidden = false;
        }
    }, 250);
});
</script>
@endpush
