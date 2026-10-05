{{--
    Filter panel for the shop + category pages. Rendered twice per page (desktop
    sidebar and mobile drawer); shop_filters_assets keeps the copies in sync, so
    nothing here may rely on an element id.

    Expects: $cats, $types, $sizes, $colors, $minDb, $maxDb, optional $cat (category page).
--}}
@php
    $sfPicked = fn ($key) => array_map('strval', (array) request($key, []));
    $sfCat    = $sfPicked('cat_id');
    $sfBrand  = $sfPicked('brand_id');
    $sfSize   = $sfPicked('size_id');
    $sfColor  = $sfPicked('color_id');
    $sfStock  = (string) request('stock_status', '');

    $sfLo  = (int) $minDb;
    $sfHi  = (int) $maxDb;
    $sfMin = max($sfLo, min($sfHi, (int) request('min_price', $sfLo)));
    $sfMax = max($sfLo, min($sfHi, (int) request('max_price', $sfHi)));
    if ($sfMin > $sfMax) { $sfMin = $sfLo; $sfMax = $sfHi; }

    $sfCurrency = ['BDT' => '৳', 'Dollar' => '$'][getInfo()->currency ?? 'BDT'] ?? '৳';
@endphp

<div class="filter-card sf-panel" data-sf-panel>
    <div class="filter-head">
        <div class="title">Filters <span class="sf-count" data-sf-count hidden></span></div>
        <button type="button" class="sf-clear" data-sf-clear hidden>Clear all</button>
    </div>

    <div class="filter-section">

        {{-- Category: a filter on the shop page, links to the other categories on a category page --}}
        @if($cats->count())
        <div class="filter-acc is-open">
            <button type="button" class="acc-btn">Category <i class="fas fa-chevron-up"></i></button>
            <div class="acc-body">
                <div class="pill-list">
                    @foreach($cats as $c)
                        @if(!empty($cat))
                            <a class="pill {{ ($c->id == $cat->id || $c->id == $cat->parent_id) ? 'active' : '' }}"
                               href="{{ route('front.category', [$c->url]) }}">{{ $c->name }}</a>
                        @else
                            <button type="button" class="pill {{ in_array((string) $c->id, $sfCat, true) ? 'active' : '' }}"
                                    data-sf="cat" data-id="{{ $c->id }}">{{ $c->name }}</button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Price --}}
        @if($sfHi > $sfLo)
        <div class="filter-acc is-open">
            <button type="button" class="acc-btn">Price <i class="fas fa-chevron-up"></i></button>
            <div class="acc-body">
                <div class="price-box">
                    <div class="sf-price-vals">
                        <span class="val"><small>Min</small> {{ $sfCurrency }} <b data-sf-min-label>{{ $sfMin }}</b></span>
                        <span class="val"><small>Max</small> {{ $sfCurrency }} <b data-sf-max-label>{{ $sfMax }}</b></span>
                    </div>
                    <div class="range-wrap">
                        <div class="track"><div class="fill" data-sf-fill></div></div>
                        <input type="range" data-sf-range="min" min="{{ $sfLo }}" max="{{ $sfHi }}" value="{{ $sfMin }}" step="1" aria-label="Minimum price">
                        <input type="range" data-sf-range="max" min="{{ $sfLo }}" max="{{ $sfHi }}" value="{{ $sfMax }}" step="1" aria-label="Maximum price">
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Stock status (pick one) --}}
        <div class="filter-acc is-open">
            <button type="button" class="acc-btn">Stock Status <i class="fas fa-chevron-up"></i></button>
            <div class="acc-body">
                <div class="pill-list">
                    <button type="button" class="pill {{ $sfStock === 'in_stock' ? 'active' : '' }}" data-sf="stock" data-id="in_stock">In Stock</button>
                    <button type="button" class="pill {{ $sfStock === 'stock_out' ? 'active' : '' }}" data-sf="stock" data-id="stock_out">Out of Stock</button>
                </div>
            </div>
        </div>

        {{-- Brand --}}
        @if($types->count())
        <div class="filter-acc is-open">
            <button type="button" class="acc-btn">Brand <i class="fas fa-chevron-up"></i></button>
            <div class="acc-body">
                <div class="pill-list">
                    @foreach($types as $t)
                        <button type="button" class="pill {{ in_array((string) $t->id, $sfBrand, true) ? 'active' : '' }}"
                                data-sf="brand" data-id="{{ $t->id }}">{{ $t->name }}</button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Color --}}
        @if($colors->count())
        <div class="filter-acc is-open">
            <button type="button" class="acc-btn">Color <i class="fas fa-chevron-up"></i></button>
            <div class="acc-body">
                <div class="pill-list sf-scroll">
                    @foreach($colors as $co)
                        @continue(trim((string) $co->name) === '')
                        <button type="button" class="pill {{ in_array((string) $co->id, $sfColor, true) ? 'active' : '' }}"
                                data-sf="color" data-id="{{ $co->id }}">
                            @if(preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', (string) $co->code))
                                <i class="sf-swatch" style="background: {{ $co->code }};"></i>
                            @endif
                            {{ $co->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Size --}}
        @if($sizes->count())
        <div class="filter-acc is-open">
            <button type="button" class="acc-btn">Size <i class="fas fa-chevron-up"></i></button>
            <div class="acc-body">
                <div class="pill-list sf-scroll">
                    @foreach($sizes as $sz)
                        @php $sfLabel = $sz->name ?? $sz->title ?? $sz->size ?? $sz->value ?? ''; @endphp
                        @continue(trim((string) $sfLabel) === '')
                        <button type="button" class="pill {{ in_array((string) $sz->id, $sfSize, true) ? 'active' : '' }}"
                                data-sf="size" data-id="{{ $sz->id }}">{{ $sfLabel }}</button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
