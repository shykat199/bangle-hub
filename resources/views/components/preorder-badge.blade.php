{{--
    <x-preorder-badge />                         small pill (cart rows, order lists, tables)
    <x-preorder-badge variant="card" />          product card, sits with the card's other badges
    <x-preorder-badge variant="image" />         large, across the corner of a product image
    <x-preorder-badge closed />                  greyed out "Pre-Order Closed"
    <x-preorder-badge label="Pre-Order" />       custom text

    Colours come from the storefront's own accent, so it follows the theme.
--}}
@props(['variant' => 'inline', 'closed' => false, 'label' => null])

@once
<style>
    .preorder-badge{
        --po-a: {{ themeAccent('#be1e30') }};
        display: inline-flex; align-items: center; gap: 5px; vertical-align: middle;
        padding: 3px 9px; border-radius: 999px; white-space: nowrap;
        font-family: inherit; font-size: 11px; font-weight: 800; line-height: 1.4; letter-spacing: .08em; text-transform: uppercase;
        color: #fff; background: linear-gradient(135deg, #f59e0b 0%, color-mix(in srgb, var(--po-a) 85%, #f59e0b) 100%);
        box-shadow: 0 4px 10px -4px rgba(0,0,0,.35);
    }
    .preorder-badge svg{ width: 1.1em; height: 1.1em; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
    .preorder-badge--card{ border-radius: 5px; font-size: 11.5px; padding: 3px 8px; }
    .preorder-badge--image{ font-size: 13px; padding: 7px 14px; border-radius: 8px; letter-spacing: .12em; box-shadow: 0 8px 18px -8px rgba(0,0,0,.5); }
    .preorder-badge.is-closed{ background: #6b7280; }
    @media (max-width: 575.98px){
        .preorder-badge--card{ font-size: 10px; padding: 2px 6px; letter-spacing: .05em; }
        .preorder-badge--image{ font-size: 11px; padding: 5px 10px; }
    }
</style>
@endonce

<span {{ $attributes->class(['preorder-badge', 'preorder-badge--' . $variant, 'is-closed' => $closed]) }}>
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
    <span>{{ $label ?? ($closed ? 'Pre-Order Closed' : 'Pre-Order') }}</span>
</span>
