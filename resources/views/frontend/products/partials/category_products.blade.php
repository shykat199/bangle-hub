{{-- /home/deshitest/public_html/resources/views/frontend/products/partials/category_products.blade.php --}}

{{-- ✅ Meta for AJAX result count update --}}
<div class="d-none"
     data-first="{{ $items->firstItem() ?? 0 }}"
     data-last="{{ $items->lastItem() ?? 0 }}"
     data-total="{{ $items->total() ?? 0 }}"></div>

@forelse($items as $product)
    <div class="col-xl-3 col-md-4 col-sm-6 col-6 mb--20 item_data">
        @include('frontend.products.partials.product_section')
    </div>
@empty
    <div class="col-12">
        <div class="alert alert-warning text-center">No Products Found !!</div>
    </div>
@endforelse

@if(method_exists($items, 'links'))
    <div class="col-12 text-center pt--20 nice-pagination">
        {!! $items->links() !!}
    </div>
@endif
