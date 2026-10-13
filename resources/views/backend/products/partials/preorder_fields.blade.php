{{--
    Product-level Pre-Order section of the create / edit form.
    $item is the product being edited (absent on create).
    Validated by App\Http\Requests\Backend\ProductPreOrderRequest.
--}}
@php
    $po     = $item ?? null;
    $poOn   = (bool) ($po->is_preorder ?? false);
    $poType = $po->preorder_payment_type ?? 'full';
    $poDate = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : '';
@endphp

<div class="row g-3" id="preorder_section">
  <div class="col-12">
    <input type="hidden" name="is_preorder" value="0">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" id="is_preorder" name="is_preorder" value="1" {{ $poOn ? 'checked' : '' }}>
      <label class="form-check-label" for="is_preorder">
        <strong>Accept pre-orders when this product is out of stock</strong>
      </label>
    </div>
    <small class="text-muted d-block">
      Off: an out-of-stock product cannot be purchased. On: customers can place a pre-order instead.
      <span class="po-variable-note">For a variable product, also switch it on for each variation in the table below — the values here are used wherever a variation leaves a field blank, and the limit here caps the total of all variations.</span>
    </small>
  </div>

  <div class="col-12 po-product-fields {{ $poOn ? '' : 'd-none' }}">
    <div class="row g-3">
      <div class="col-lg-3 col-md-6">
        <label class="form-label">Pre-Order Limit</label>
        <input type="number" min="1" step="1" name="preorder_limit" class="form-control" placeholder="Ex: 50 (blank = no limit)" value="{{ $po->preorder_limit ?? '' }}">
        @if($po)
          <small class="text-muted">Pre-ordered so far: <strong>{{ (int) $po->preorder_sold_qty }}</strong></small>
        @endif
      </div>

      <div class="col-lg-5 col-md-6">
        <label class="form-label">Estimated Delivery Text</label>
        <input type="text" name="preorder_delivery_text" maxlength="255" class="form-control" placeholder="Estimated Delivery: 15–20 October" value="{{ $po->preorder_delivery_text ?? '' }}">
        <small class="text-muted">Leave blank to build the text from the dates.</small>
      </div>

      <div class="col-lg-2 col-md-6">
        <label class="form-label">Delivery From</label>
        <input type="date" name="preorder_delivery_start" class="form-control" value="{{ $poDate($po->preorder_delivery_start ?? null) }}">
      </div>

      <div class="col-lg-2 col-md-6">
        <label class="form-label">Delivery To</label>
        <input type="date" name="preorder_delivery_end" class="form-control" value="{{ $poDate($po->preorder_delivery_end ?? null) }}">
      </div>

      <div class="col-lg-4 col-md-6">
        <label class="form-label">Payment Type</label>
        <select name="preorder_payment_type" id="preorder_payment_type" class="form-control">
          <option value="full" {{ $poType === 'full' ? 'selected' : '' }}>Full payment</option>
          <option value="advance" {{ $poType === 'advance' ? 'selected' : '' }}>Advance payment</option>
          <option value="both" {{ $poType === 'both' ? 'selected' : '' }}>Both (customer chooses)</option>
        </select>
      </div>

      <div class="col-lg-4 col-md-6 po-advance-fields {{ $poType === 'full' ? 'd-none' : '' }}">
        <label class="form-label">Advance Type</label>
        <select name="preorder_advance_type" class="form-control">
          <option value="fixed" {{ ($po->preorder_advance_type ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed amount (৳ per piece)</option>
          <option value="percentage" {{ ($po->preorder_advance_type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage of the price</option>
        </select>
      </div>

      <div class="col-lg-4 col-md-6 po-advance-fields {{ $poType === 'full' ? 'd-none' : '' }}">
        <label class="form-label">Advance Amount</label>
        <input type="number" step="any" min="0" name="preorder_advance_amount" class="form-control" placeholder="Ex: 500" value="{{ $po->preorder_advance_amount ?? '' }}">
        <small class="text-muted">Must be less than the product price.</small>
      </div>
    </div>
  </div>
</div>

{{-- A blank per-variation cell, copied into every variation row added with the + button. --}}
<template id="varPreorderCellTpl">@include('backend.products.partials.preorder_variation_cell', ['v' => null])</template>

@once
<style>
  .po-var { min-width: 150px; white-space: normal; text-align: left; }
  .po-var-panel { margin-top: 6px; padding: 8px; border: 1px dashed #cbd5e1; border-radius: 6px; background: #f8fafc; min-width: 230px; }
  .po-var-panel label { display: block; margin: 6px 0 2px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .3px; }
  .po-var-panel label:first-child { margin-top: 0; }
  .po-var-panel .po-pair { display: flex; gap: 6px; }
  .po-var-panel .po-pair > * { flex: 1 1 0; min-width: 0; }
</style>
<script>
  // Fills the Pre-Order cell of a variation row built in JavaScript.
  window.preorderVariationCell = function () {
    var tpl = document.getElementById('varPreorderCellTpl');
    return tpl ? tpl.innerHTML : '';
  };

  document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('is_preorder');
    var type   = document.getElementById('preorder_payment_type');
    if (!toggle || !type) return;

    function refresh() {
      document.querySelectorAll('.po-product-fields').forEach(function (el) { el.classList.toggle('d-none', !toggle.checked); });
      document.querySelectorAll('.po-advance-fields').forEach(function (el) { el.classList.toggle('d-none', type.value === 'full'); });
    }
    toggle.addEventListener('change', refresh);
    type.addEventListener('change', refresh);
    // select2 (edit form) fires jQuery events only
    if (window.jQuery) jQuery(type).on('change', refresh);
    refresh();

    function refreshVariation(cell) {
      var on = cell.querySelector('.po-var-toggle').value === '1';
      cell.querySelector('.po-var-panel').style.display = on ? '' : 'none';
    }
    document.addEventListener('change', function (e) {
      if (e.target.classList && e.target.classList.contains('po-var-toggle')) refreshVariation(e.target.closest('.po-var'));
    });
    document.querySelectorAll('.po-var').forEach(refreshVariation);
  });
</script>
@endonce
