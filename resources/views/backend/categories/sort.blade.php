@extends('backend.app')
@section('content')

<style>
  h4, .form-label { color: #111827 !important; }
  .card { border: none !important; border-radius: 12px !important; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
  .page-title-box { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; }

  .cat-sort { list-style: none; margin: 0; padding: 0; }
  .cat-sort-item { margin-bottom: 8px; }
  .cat-sort-row {
    display: flex; align-items: center; gap: 12px; padding: 8px 12px;
    border: 1px solid #e5e7eb; border-radius: 10px; background: #fff;
  }
  .cat-handle { flex: 0 0 auto; font-size: 22px; line-height: 1; color: #9ca3af; cursor: grab; }
  .cat-handle:hover { color: #111827; }
  .cat-pos {
    flex: 0 0 auto; min-width: 28px; height: 28px; padding: 0 6px; border-radius: 8px; background: #f1f5f9;
    display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: #334155;
  }
  .cat-sort-row img { flex: 0 0 auto; width: 38px; height: 38px; border-radius: 8px; object-fit: cover; background: #f1f5f9; }
  .cat-name { flex: 1 1 auto; min-width: 0; font-weight: 600; color: #111827; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .cat-count { flex: 0 0 auto; font-size: 12.5px; color: #6b7280; }

  .cat-sort-sub { margin: 8px 0 0 40px; }
  .cat-sort-sub .cat-sort-row { padding: 6px 10px; background: #f8fafc; }
  .cat-sort-sub .cat-sort-item:last-child { margin-bottom: 0; }

  .cat-sort-item.sortable-ghost > .cat-sort-row { opacity: .35; }
  .cat-sort-item.sortable-chosen > .cat-sort-row { border-color: #2563eb; box-shadow: 0 6px 16px -8px rgba(37,99,235,.5); }
  .cat-sort-item.sortable-chosen .cat-handle { cursor: grabbing; }

  @media (max-width: 575.98px) {
    .cat-sort-sub { margin-left: 18px; }
    .cat-count { display: none; }
  }
</style>

<div class="row gy-4">
  <div class="col-12">
    <div class="page-title-box mb-3">
      <div>
        <h4 class="page-title mb-0 fw-bold">Sort Categories</h4>
        <small class="text-muted">The storefront menus show categories in this order</small>
      </div>
      <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary"><i class="mdi mdi-arrow-left"></i> Back to Categories</a>
    </div>
  </div>

  <div class="col-lg-8 col-md-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <span class="text-muted"><i class="mdi mdi-drag"></i> Drag a category by its handle to move it. The order saves by itself.</span>
          <small id="cat_sort_status" style="font-weight: 600;"></small>
        </div>

        @if($cats->isEmpty())
          <p class="text-muted mb-0">There are no categories yet.</p>
        @else
          <ul class="cat-sort" id="cat_sort" data-parent="">
            @foreach($cats as $cat)
              <li class="cat-sort-item" data-id="{{ $cat->id }}">
                <div class="cat-sort-row">
                  <i class="mdi mdi-drag cat-handle cat-handle-main" title="Drag to move"></i>
                  <span class="cat-pos">{{ $loop->iteration }}</span>
                  <img src="{{ getImage('categories', $cat->image) }}" alt="">
                  <span class="cat-name">{{ $cat->name }}</span>
                  @if($cat->subcats->count())
                    <span class="cat-count">{{ $cat->subcats->count() }} {{ $cat->subcats->count() === 1 ? 'subcategory' : 'subcategories' }}</span>
                  @endif
                </div>
                @if($cat->subcats->count())
                  <ul class="cat-sort cat-sort-sub" data-parent="{{ $cat->id }}">
                    @foreach($cat->subcats as $sub)
                      <li class="cat-sort-item" data-id="{{ $sub->id }}">
                        <div class="cat-sort-row">
                          <i class="mdi mdi-drag cat-handle cat-handle-sub" title="Drag to move"></i>
                          <span class="cat-pos">{{ $loop->iteration }}</span>
                          <span class="cat-name">{{ $sub->name }}</span>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @endif
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
$(function(){
  const $status = $('#cat_sort_status');
  const saveUrl = "{{ route('admin.categories.saveSort') }}";

  function setStatus(msg, isError){
    $status.text(msg).css('color', isError ? '#dc2626' : '#16a34a');
    if (!isError && msg) setTimeout(() => { if ($status.text() === msg) $status.text(''); }, 2000);
  }

  // Each list sorts on its own: the main categories, and every category's subcategories.
  $('.cat-sort').each(function(){
    const list = this;
    const isSub = $(list).hasClass('cat-sort-sub');

    Sortable.create(list, {
      animation: 150,
      handle: isSub ? '.cat-handle-sub' : '.cat-handle-main',
      draggable: '.cat-sort-item',
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      onEnd: function(evt){
        const $items = $(list).children('.cat-sort-item');
        $items.each(function(i){ $(this).children('.cat-sort-row').find('.cat-pos').text(i + 1); });
        if (evt.oldIndex === evt.newIndex) return;

        setStatus('Saving order…');
        $.post(saveUrl, {
          _token: '{{ csrf_token() }}',
          parent_id: $(list).data('parent') || '',
          ids: $items.map(function(){ return $(this).data('id'); }).get()
        })
          .done(() => setStatus('Order saved ✓'))
          .fail(() => setStatus('Could not save the order. Please try again.', true));
      }
    });
  });
});
</script>
@endpush
