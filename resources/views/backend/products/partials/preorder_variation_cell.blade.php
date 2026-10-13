{{--
    Pre-Order settings of ONE variation row. Every input is a var_*[] array so
    the values line up with size_id[] / price[] of the same row.
    $v is the variation (null for a new row). Blank fields fall back to the
    product-level Pre-Order section.
--}}
@php
    $vOn   = (bool) ($v->is_preorder ?? false);
    $vDate = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : '';
@endphp
<div class="po-var">
  <select name="var_is_preorder[]" class="form-select form-select-sm po-var-toggle" title="Pre-order for this variation">
    <option value="0" {{ $vOn ? '' : 'selected' }}>Off</option>
    <option value="1" {{ $vOn ? 'selected' : '' }}>On</option>
  </select>

  <div class="po-var-panel" style="{{ $vOn ? '' : 'display:none;' }}">
    <label>Limit @if($v)<span class="text-muted fw-normal">(sold: {{ (int) $v->preorder_sold_qty }})</span>@endif</label>
    <input type="number" min="1" step="1" name="var_preorder_limit[]" class="form-control form-control-sm" placeholder="Blank = no own limit" value="{{ $v->preorder_limit ?? '' }}">

    <label>Delivery text</label>
    <input type="text" maxlength="255" name="var_preorder_delivery_text[]" class="form-control form-control-sm" placeholder="Blank = same as product" value="{{ $v->preorder_delivery_text ?? '' }}">

    <label>Delivery from / to</label>
    <div class="po-pair">
      <input type="date" name="var_preorder_delivery_start[]" class="form-control form-control-sm" value="{{ $vDate($v->preorder_delivery_start ?? null) }}">
      <input type="date" name="var_preorder_delivery_end[]" class="form-control form-control-sm" value="{{ $vDate($v->preorder_delivery_end ?? null) }}">
    </div>

    <label>Payment</label>
    <select name="var_preorder_payment_type[]" class="form-select form-select-sm">
      <option value="" {{ empty($v->preorder_payment_type) ? 'selected' : '' }}>Same as product</option>
      <option value="full" {{ ($v->preorder_payment_type ?? '') === 'full' ? 'selected' : '' }}>Full payment</option>
      <option value="advance" {{ ($v->preorder_payment_type ?? '') === 'advance' ? 'selected' : '' }}>Advance payment</option>
      <option value="both" {{ ($v->preorder_payment_type ?? '') === 'both' ? 'selected' : '' }}>Both</option>
    </select>

    <label>Advance (blank = same as product)</label>
    <div class="po-pair">
      <select name="var_preorder_advance_type[]" class="form-select form-select-sm">
        <option value="fixed" {{ ($v->preorder_advance_type ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed ৳</option>
        <option value="percentage" {{ ($v->preorder_advance_type ?? '') === 'percentage' ? 'selected' : '' }}>Percent %</option>
      </select>
      <input type="number" step="any" min="0" name="var_preorder_advance_amount[]" class="form-control form-control-sm" placeholder="Amount" value="{{ $v->preorder_advance_amount ?? '' }}">
    </div>
  </div>
</div>
