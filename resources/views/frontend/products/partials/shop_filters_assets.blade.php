{{--
    Styles + behaviour for shop_filters. Include once per page, after the page's own
    <style> (it adjusts a few of those rules). The page provides #product_data (grid),
    #resultText, #topbar and the #sort select.
--}}
<style>
:root{ --sf-accent: {{ themeAccent() }}; }
/* The pages' own styles were written around a blue palette; these re-tint the same
   pieces from the theme accent so the listing matches the rest of the site. */
:root{
    --bg: #f8f8fa;
    --sf-tint: color-mix(in srgb, var(--sf-accent) 6%, #fff);
    --sf-tint-strong: color-mix(in srgb, var(--sf-accent) 13%, #fff);
    --sf-line: color-mix(in srgb, var(--sf-accent) 22%, transparent);
}
.axil-breadcrumb-area{ background: linear-gradient(135deg, var(--sf-tint-strong), #fff 55%, var(--sf-tint)); }
.axil-breadcrumb li a, .mob-top .crumb a{ color: var(--muted) !important; }
.axil-breadcrumb li a:hover, .mob-top .crumb a:hover{ color: var(--sf-accent) !important; }
.axil-breadcrumb-item.active, .mob-top .crumb > span:last-child{ color: var(--sf-accent) !important; }
.sf-panel .filter-head{ background: linear-gradient(135deg, var(--sf-tint-strong), #fff 70%); }
.sf-panel .filter-acc .acc-btn{ background: var(--sf-tint); color: var(--sf-accent); }
.sf-panel .filter-acc .acc-btn i{ color: var(--sf-accent); }
.sf-panel .pill{ border-color: var(--sf-line); background: var(--sf-tint); }
.shop-topbar select:focus{ border-color: var(--sf-accent); }

/* a section is open when it carries .is-open (the category page's stylesheet has no rule for this) */
.sf-panel .filter-acc .acc-body{ display: none; }
.sf-panel .filter-acc.is-open .acc-body{ display: block; }

/* sidebar sits under the sticky header and scrolls on its own when it is taller than the screen */
.filters-col{ top: 92px; max-height: calc(100vh - 104px); overflow-y: auto; scrollbar-width: thin; }

.sf-panel .filter-head .title{ display: flex; align-items: center; gap: 8px; }
.sf-count{
    min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--sf-accent); color: #fff; font-size: 11px; font-weight: 800; letter-spacing: 0;
}
.sf-count[hidden], .sf-clear[hidden]{ display: none; }
.sf-clear{
    border: 0; background: none; padding: 4px 0; cursor: pointer;
    font-size: 12.5px; font-weight: 800; color: var(--sf-accent); text-decoration: underline;
}
.sf-panel .filter-acc .acc-btn{ font-family: inherit; }
.sf-panel .pill{ font-family: inherit; text-decoration: none !important; gap: 6px; width: auto; }
.sf-panel .pill:hover{ border-color: var(--sf-accent); }
.sf-panel .pill.active{ background: var(--sf-accent); border-color: var(--sf-accent); color: #fff !important; }
.sf-panel .pill.active::before{ content: "\2713"; font-size: 11px; font-weight: 900; }
.sf-panel .pill-list.sf-scroll{ max-height: 232px; overflow-y: auto; scrollbar-width: thin; padding-right: 2px; }
.sf-swatch{ width: 14px; height: 14px; border-radius: 50%; border: 1px solid rgba(2,6,23,.18); flex: 0 0 14px; }
.sf-price-vals{ display: flex; justify-content: space-between; gap: 8px; margin-bottom: 6px; }
.sf-price-vals .val{ font-weight: 900; font-size: 14px; color: var(--text); }
.sf-price-vals .val small{ display: block; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
.sf-panel .track .fill{ background: var(--sf-accent); }
.sf-panel .range-wrap input[type=range]::-webkit-slider-thumb{ background: var(--sf-accent); border-color: #fff; }
.sf-panel .range-wrap input[type=range]::-moz-range-thumb{
    width: 22px; height: 22px; border-radius: 999px; pointer-events: auto;
    background: var(--sf-accent); border: 2px solid #fff; box-shadow: 0 12px 18px rgba(2,6,23,.20);
}
.sf-panel .range-wrap input[type=range]::-moz-range-track{ background: transparent; }
.sf-panel .range-wrap input[type=range]{ cursor: pointer; }

/* the site's floating bottom nav and chat buttons would cover the drawer's button */
body:has(#mobileFilters.show) #footerNav,
body:has(#mobileFilters.show) .premium-float-stack{ display: none !important; }
/* mobile drawer: results button stays at the bottom */
.sf-drawer-foot{ padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--line); background: #fff; }
.sf-drawer-foot .btn-apply{ background: var(--sf-accent); color: #fff; }
.mob-top .dots-btn{ width: auto; padding: 0 12px; gap: 8px; font-weight: 900; font-size: 14px; border: 1px solid var(--line); background: #fff; }
.mob-top .dots-btn i{ font-size: 15px; }

/* grid: dim while a filter request is running; image boxes shimmer until the lazy image arrives */
#product_data{ transition: opacity .18s ease; }
#product_data.loading{ opacity: .5; pointer-events: none; }
.product-loading:after{
    content: ""; inset: 80px auto auto 50%; width: 38px; height: 38px; margin-left: -19px;
    background: none; backdrop-filter: none; border-radius: 50%;
    border: 3px solid rgba(100,116,139,.25); border-top-color: var(--sf-accent);
    animation: sfSpin .7s linear infinite;
}
.product-loading.loading:after{ display: block; }
@keyframes sfSpin{ to{ transform: rotate(360deg); } }
#product_data .axil-product .thumbnail{
    background: linear-gradient(90deg, #f1f5f9 25%, #e8edf3 37%, #f1f5f9 63%); background-size: 400% 100%;
    animation: sfShimmer 1.4s ease infinite;
}
@keyframes sfShimmer{ 0%{ background-position: 100% 50%; } 100%{ background-position: 0 50%; } }
@media (prefers-reduced-motion: reduce){ #product_data .axil-product .thumbnail, .product-loading:after{ animation: none; } }
</style>

@push('js')
<script>
// Shop / category filters: every change applies straight away over AJAX — no Apply
// button, no page reload. One state object drives both panels (sidebar + mobile
// drawer) and the address bar, so a filtered view can be reloaded or shared.
(function(){
    const BASE  = @json(url()->current());
    const QUERY = @json((string) request('q', ''));
    const MULTI = { cat: 'cat_id', brand: 'brand_id', size: 'size_id', color: 'color_id' };

    const grid   = document.getElementById('product_data');
    const panels = Array.from(document.querySelectorAll('[data-sf-panel]'));
    if(!grid || !panels.length) return;

    const firstRange = panels[0].querySelector('[data-sf-range="min"]');
    const LO = firstRange ? parseInt(firstRange.min, 10) : 0;
    const HI = firstRange ? parseInt(firstRange.max, 10) : 0;

    const sortEl = document.getElementById('sort');
    const state = {
        cat: [], brand: [], size: [], color: [], stock: '',
        min: firstRange ? parseInt(firstRange.value, 10) : LO,
        max: firstRange ? parseInt(panels[0].querySelector('[data-sf-range="max"]').value, 10) : HI,
        sort: sortEl ? sortEl.value : 'latest',
        page: 1
    };
    // what the server rendered as active is the starting state
    panels[0].querySelectorAll('[data-sf].active').forEach(function(el){
        const id = String(el.dataset.id);
        if(el.dataset.sf === 'stock') state.stock = id; else state[el.dataset.sf].push(id);
    });

    function activeCount(){
        return state.cat.length + state.brand.length + state.size.length + state.color.length
             + (state.stock ? 1 : 0) + ((state.min > LO || state.max < HI) ? 1 : 0);
    }

    // Push the state into every panel copy.
    function paint(){
        const n = activeCount();
        panels.forEach(function(panel){
            panel.querySelectorAll('[data-sf]').forEach(function(el){
                const id = String(el.dataset.id);
                el.classList.toggle('active', el.dataset.sf === 'stock' ? state.stock === id : state[el.dataset.sf].indexOf(id) !== -1);
            });
            const minR = panel.querySelector('[data-sf-range="min"]'), maxR = panel.querySelector('[data-sf-range="max"]');
            if(minR && maxR){
                minR.value = state.min; maxR.value = state.max;
                panel.querySelector('[data-sf-min-label]').textContent = state.min;
                panel.querySelector('[data-sf-max-label]').textContent = state.max;
                const span = Math.max(1, HI - LO), fill = panel.querySelector('[data-sf-fill]');
                fill.style.left  = ((state.min - LO) / span * 100) + '%';
                fill.style.right = (100 - (state.max - LO) / span * 100) + '%';
            }
            panel.querySelector('[data-sf-clear]').hidden = n === 0;
        });
        document.querySelectorAll('[data-sf-count]').forEach(function(el){ el.hidden = n === 0; el.textContent = n; });
    }

    function queryString(){
        const p = new URLSearchParams();
        if(QUERY) p.append('q', QUERY);
        Object.keys(MULTI).forEach(function(k){ state[k].forEach(function(id){ p.append(MULTI[k] + '[]', id); }); });
        if(state.stock) p.append('stock_status', state.stock);
        // only send a price range the customer actually narrowed
        if(state.min > LO || state.max < HI){ p.append('min_price', state.min); p.append('max_price', state.max); }
        if(state.sort && state.sort !== 'latest') p.append('sort', state.sort);
        if(state.page > 1) p.append('page', state.page);
        return p.toString();
    }

    let xhr = null, timer = null;
    function load(){
        const qs = queryString();
        const url = BASE + (qs ? '?' + qs : '');
        if(xhr) xhr.abort();
        grid.classList.add('loading');
        xhr = $.ajax({ type: 'GET', url: url })
            .done(function(html){
                grid.innerHTML = html;
                const meta = grid.querySelector('[data-first][data-last][data-total]');
                const total = meta ? meta.dataset.total : 0;
                const text = document.getElementById('resultText');
                if(meta && text) text.innerHTML = 'Showing <span>' + meta.dataset.first + ' – ' + meta.dataset.last + ' of ' + total + ' results</span>';
                document.querySelectorAll('[data-sf-done]').forEach(function(b){ b.textContent = 'Show ' + total + ' result' + (String(total) === '1' ? '' : 's'); });
                if(window.history && history.replaceState) history.replaceState(null, '', url);
                // bring the top of the list back if the customer had scrolled past it
                const bar = document.getElementById('topbar');
                if(bar && bar.getBoundingClientRect().top < 0) window.scrollTo({ top: window.scrollY + bar.getBoundingClientRect().top - 96, behavior: 'smooth' });
            })
            .fail(function(x, status){
                if(status !== 'abort' && window.toastr) toastr.error('Could not load products. Please try again.');
            })
            .always(function(x, status){ if(status !== 'abort') grid.classList.remove('loading'); });
    }
    function apply(delay){
        state.page = 1;
        paint();
        clearTimeout(timer);
        timer = setTimeout(load, delay || 0);
    }

    document.addEventListener('click', function(e){
        const acc = e.target.closest('.sf-panel .acc-btn');
        if(acc){ acc.closest('.filter-acc').classList.toggle('is-open'); return; }

        const pill = e.target.closest('.sf-panel [data-sf]');
        if(pill){
            const key = pill.dataset.sf, id = String(pill.dataset.id);
            if(key === 'stock'){
                state.stock = state.stock === id ? '' : id;
            }else{
                const i = state[key].indexOf(id);
                if(i === -1) state[key].push(id); else state[key].splice(i, 1);
            }
            apply(120);
            return;
        }

        if(e.target.closest('[data-sf-clear]')){
            state.cat = []; state.brand = []; state.size = []; state.color = []; state.stock = '';
            state.min = LO; state.max = HI;
            apply();
            return;
        }

        const pageLink = e.target.closest('#product_data .pagination a');
        if(pageLink){
            e.preventDefault();
            const page = parseInt(new URL(pageLink.href, window.location.href).searchParams.get('page') || '1', 10);
            state.page = page > 0 ? page : 1;
            clearTimeout(timer);
            load();
        }
    });

    // price sliders: labels follow the thumb, the request goes out once it is released
    document.addEventListener('input', function(e){
        const r = e.target.closest ? e.target.closest('.sf-panel [data-sf-range]') : null;
        if(!r) return;
        const v = parseInt(r.value, 10);
        if(r.dataset.sfRange === 'min') state.min = Math.min(v, state.max - 1);
        else state.max = Math.max(v, state.min + 1);
        paint();
    });
    document.addEventListener('change', function(e){
        if(e.target.closest && e.target.closest('.sf-panel [data-sf-range]')) apply();
        if(e.target === sortEl){ state.sort = sortEl.value; apply(); }
    });

    paint();
})();
</script>
@endpush
