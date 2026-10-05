@extends('frontend.app')

@section('content')
<style>
    /* All Categories page. Accent = the site's brand colour; cards share the product cards' look. */
    .acp{ --acp-accent: {{ themeAccent('#be1e30') }}; --acp-soft: color-mix(in srgb, var(--acp-accent) 8%, #fff);
          --acp-ink: #111827; --acp-muted: #6b7280; --acp-line: #e8eaee; background: #fff; padding-bottom: 48px; }
    @media (min-width: 1400px){ .acp .container{ max-width: 1360px; } }

    .acp-crumbs{ display: flex; gap: 8px; margin: 0; padding: 16px 0 6px; list-style: none; font-size: 14px; color: var(--acp-muted); }
    .acp-crumbs li + li::before{ content: "\203A"; margin-right: 8px; color: #9ca3af; }
    .acp-crumbs a{ color: var(--acp-muted); text-decoration: none; }
    .acp-crumbs a:hover{ color: var(--acp-accent); }

    .acp-head{ display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin: 10px 0 22px; }
    .acp-title{ position: relative; margin: 0; padding-left: 16px; font-size: 30px; font-weight: 800; color: var(--acp-ink); line-height: 1.15; }
    .acp-title::before{ content: ""; position: absolute; left: 0; top: 4px; bottom: 4px; width: 5px; border-radius: 3px; background: var(--acp-accent); }
    .acp-sub{ margin: 6px 0 0 16px; font-size: 14.5px; color: var(--acp-muted); }
    .acp-search{ position: relative; width: min(340px, 100%); }
    .acp-search i{ position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 14px; pointer-events: none; }
    .acp-search input{
        width: 100%; height: 44px !important; padding: 0 14px 0 38px !important; border-radius: 10px; border: 1.5px solid var(--acp-line);
        font-size: 14.5px; background: #fff; line-height: 1.4 !important; outline: none; transition: border-color .2s ease, box-shadow .2s ease;
    }
    .acp-search input:focus{ border-color: var(--acp-accent); box-shadow: 0 0 0 4px color-mix(in srgb, var(--acp-accent) 12%, transparent); }

    .acp-grid{ display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 18px; }

    /* card: the whole card is the link (stretched ::after), subcategory chips sit above it */
    .acp-card{
        position: relative; display: flex; flex-direction: column; overflow: hidden; background: #fff;
        border: 1px solid var(--acp-line); border-radius: 12px;
        transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease;
    }
    .acp-card:hover, .acp-card:focus-within{
        border-color: color-mix(in srgb, var(--acp-accent) 35%, var(--acp-line));
        box-shadow: 0 16px 32px -18px rgba(15,23,42,.32); transform: translateY(-4px);
    }
    .acp-img{ position: relative; aspect-ratio: 4 / 3; overflow: hidden; background: #f6f7f9; display: flex; align-items: center; justify-content: center; }
    .acp-img img{
        width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease;
        filter: contrast(1.06) saturate(1.12) brightness(1.02); image-rendering: high-quality;
    }
    .acp-card:hover .acp-img img{ transform: scale(1.07); }
    .acp-img .acp-noimg{ width: 64px; height: 64px; border-radius: 16px; display: flex; align-items: center; justify-content: center; background: var(--acp-soft); color: var(--acp-accent); font-size: 26px; }
    .acp-count{
        position: absolute; top: 10px; left: 10px; padding: 4px 10px; border-radius: 6px;
        background: var(--acp-accent); color: #fff; font-size: 12px; font-weight: 700; line-height: 1.3;
        box-shadow: 0 4px 10px -4px rgba(0,0,0,.35);
    }
    .acp-count.is-empty{ background: #9ca3af; }

    .acp-body{ display: flex; flex-direction: column; flex: 1 1 auto; gap: 6px; padding: 12px 14px 14px; }
    .acp-name{ margin: 0; font-size: 16.5px; font-weight: 700; line-height: 1.3; color: var(--acp-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .acp-name a{ color: inherit !important; text-decoration: none !important; }
    .acp-name a::after{ content: ""; position: absolute; inset: 0; z-index: 1; }      /* whole card clickable */
    .acp-card:hover .acp-name{ color: var(--acp-accent); }
    .acp-meta{ font-size: 13px; color: var(--acp-muted); }
    .acp-chips{ position: relative; z-index: 2; display: flex; flex-wrap: wrap; gap: 6px; }
    .acp-chip{
        padding: 3px 9px; border-radius: 999px; border: 1px solid var(--acp-line); background: #fff;
        font-size: 12px; color: #374151 !important; text-decoration: none !important; white-space: nowrap; transition: all .2s ease;
    }
    .acp-chip:hover{ border-color: var(--acp-accent); background: var(--acp-soft); color: var(--acp-accent) !important; }
    .acp-chip.is-more{ border-style: dashed; color: var(--acp-muted) !important; }
    .acp-cta{ margin-top: auto; padding-top: 6px; font-size: 14px; font-weight: 700; color: var(--acp-accent); }
    .acp-cta i{ font-size: 11px; margin-left: 4px; transition: transform .2s ease; }
    .acp-card:hover .acp-cta i{ transform: translateX(4px); }
    .acp-card.is-empty .acp-cta{ color: var(--acp-muted); }

    .acp-none{ display: none; padding: 50px 10px; text-align: center; color: var(--acp-muted); font-size: 15px; }
    .acp-none i{ display: block; font-size: 30px; margin-bottom: 10px; color: #d1d5db; }

    @media (max-width: 1199.98px){ .acp-grid{ grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (max-width: 991.98px){ .acp-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; } }
    @media (max-width: 767.98px){
        .acp-title{ font-size: 23px; padding-left: 12px; }
        .acp-title::before{ width: 4px; }
        .acp-sub{ margin-left: 12px; font-size: 13px; }
        .acp-search{ width: 100%; }
        .acp-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .acp-body{ padding: 10px 10px 12px; gap: 4px; }
        .acp-name{ font-size: 14.5px; }
        .acp-meta, .acp-cta{ font-size: 12.5px; }
        .acp-chip{ font-size: 11px; padding: 2px 7px; }
        .acp-count{ font-size: 11px; padding: 3px 8px; top: 8px; left: 8px; }
    }
    @media (prefers-reduced-motion: reduce){ .acp-card, .acp-img img, .acp-cta i{ transition: none; } }
</style>

<main class="main-wrapper acp">
    <div class="container">
        <ol class="acp-crumbs" aria-label="Breadcrumb">
            <li><a href="{{ route('front.home') }}">Home</a></li>
            <li aria-current="page">All Categories</li>
        </ol>

        <div class="acp-head">
            <div>
                <h1 class="acp-title">All Categories</h1>
                <p class="acp-sub">{{ $cats->count() }} categories &middot; {{ $totalProducts }} products</p>
            </div>
            <label class="acp-search">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input type="search" id="acpSearch" placeholder="Search categories..." autocomplete="off" aria-label="Search categories">
            </label>
        </div>

        <div class="acp-grid" id="acpGrid">
            @foreach($cats as $cat)
                @php
                    $n = (int) $cat->products_count;
                    $subs = $cat->subcats->filter(fn ($s) => $s->products_count > 0)->values();
                    $searchText = mb_strtolower($cat->name . ' ' . $cat->subcats->pluck('name')->implode(' '));
                @endphp
                <article class="acp-card {{ $n === 0 ? 'is-empty' : '' }}" data-search="{{ $searchText }}">
                    <div class="acp-img">
                        @if($cat->has_image)
                            <img src="{{ asset('categories/' . $cat->image) }}" alt="{{ $cat->name }}" loading="lazy" decoding="async">
                        @else
                            <span class="acp-noimg" aria-hidden="true"><i class="fas fa-tags"></i></span>
                        @endif
                        <span class="acp-count {{ $n === 0 ? 'is-empty' : '' }}">{{ $n }} {{ $n === 1 ? 'Product' : 'Products' }}</span>
                    </div>
                    <div class="acp-body">
                        <h2 class="acp-name"><a href="{{ route('front.category', [$cat->url]) }}" title="{{ $cat->name }}">{{ $cat->name }}</a></h2>
                        <span class="acp-meta">
                            @if($cat->subcats->count())
                                {{ $cat->subcats->count() }} {{ $cat->subcats->count() === 1 ? 'subcategory' : 'subcategories' }}
                            @else
                                {{ $n > 0 ? 'Ready to order' : 'New products coming soon' }}
                            @endif
                        </span>
                        @if($subs->isNotEmpty())
                            <div class="acp-chips">
                                @foreach($subs->take(3) as $sub)
                                    <a class="acp-chip" href="{{ route('front.category', [$sub->url]) }}">{{ $sub->name }} ({{ $sub->products_count }})</a>
                                @endforeach
                                @if($subs->count() > 3)
                                    <a class="acp-chip is-more" href="{{ route('front.category', [$cat->url]) }}">+{{ $subs->count() - 3 }} more</a>
                                @endif
                            </div>
                        @endif
                        <span class="acp-cta">{{ $n > 0 ? 'View Products' : 'Browse' }} <i class="fas fa-arrow-right"></i></span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="acp-none" id="acpNone"><i class="fas fa-search"></i>No category matches your search.</div>
    </div>
</main>
@endsection

@push('js')
<script>
// Filter the category cards as the shopper types (matches category and subcategory names).
(function(){
    var input = document.getElementById('acpSearch');
    if (!input) return;
    var cards = Array.prototype.slice.call(document.querySelectorAll('#acpGrid .acp-card'));
    var none = document.getElementById('acpNone');
    input.addEventListener('input', function(){
        var q = input.value.trim().toLowerCase(), shown = 0;
        cards.forEach(function(c){
            var hit = !q || c.dataset.search.indexOf(q) !== -1;
            c.style.display = hit ? '' : 'none';
            if (hit) shown++;
        });
        none.style.display = shown ? 'none' : 'block';
    });
})();
</script>
@endpush
