{{-- Variant rows: "+ Add new…" in the Size / Color dropdowns creates the
     option in the sizes / colors table and selects it, without leaving the page. --}}
<script>
(function(){
  const NEW_VALUE = '__new__';
  const SELECTOR = 'select[name="size_id[]"], select[name="color_id[]"]';
  const created = { size: [], color: [] };

  function typeOf(select){ return select.name === 'size_id[]' ? 'size' : 'color'; }

  // Rows added later come from a template rendered at page load, so they
  // need the options created since then.
  function sync(select){
    const addOpt = select.querySelector('option[value="' + NEW_VALUE + '"]');
    created[typeOf(select)].forEach(function(item){
      if (select.querySelector('option[value="' + item.id + '"]')) return;
      select.insertBefore(new Option(item.name, item.id), addOpt);
    });
  }

  $(document).on('focus mousedown', SELECTOR, function(){
    sync(this);
    if (this.value !== NEW_VALUE) $(this).data('prev', this.value);
  });

  $(document).on('change', SELECTOR, function(){
    const select = this;
    if (select.value !== NEW_VALUE) { $(select).data('prev', select.value); return; }

    const type = typeOf(select);
    const prev = $(select).data('prev') || '';
    const name = (window.prompt('New ' + type + ' name:') || '').trim();
    if (!name) { select.value = prev; return; }

    $.ajax({
      url: "{{ route('admin.products.variantOption') }}",
      method: 'POST',
      data: { _token: "{{ csrf_token() }}", type: type, name: name },
      success: function(res){
        if (!res || !res.status) {
          select.value = prev;
          toastr.error((res && res.msg) || 'Could not add ' + type);
          return;
        }
        if (!created[type].some(function(i){ return String(i.id) === String(res.id); })) {
          created[type].push({ id: res.id, name: res.name });
        }
        document.querySelectorAll(SELECTOR).forEach(sync);
        select.value = res.id;
        $(select).data('prev', select.value);
        toastr.success(res.msg);
      },
      error: function(xhr){
        select.value = prev;
        const errors = xhr.responseJSON && xhr.responseJSON.errors;
        toastr.error(errors ? Object.values(errors)[0][0] : 'Could not add ' + type);
      }
    });
  });
})();
</script>
