<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * New estimated delivery for the selected orders, or for every open
 * pre-order of one product (optionally one variation).
 */
class PreOrderDeliveryDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('order.edit');
    }

    public function rules(): array
    {
        return [
            'scope'          => 'required|in:orders,product',
            'order_ids'      => 'required_if:scope,orders|array',
            'order_ids.*'    => 'integer',
            'product_id'     => 'required_if:scope,product|nullable|integer|exists:products,id',
            'variation_id'   => 'nullable|integer|exists:variations,id',
            'delivery_text'  => 'nullable|string|max:255|required_without:delivery_start',
            'delivery_start' => 'nullable|date|required_without:delivery_text',
            'delivery_end'   => 'nullable|date|after_or_equal:delivery_start',
            'update_product' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'order_ids.required_if'           => 'Please select at least one pre-order.',
            'product_id.required_if'          => 'Please choose a product.',
            'delivery_text.required_without'  => 'Enter the new delivery text or a delivery date.',
            'delivery_start.required_without' => 'Enter the new delivery text or a delivery date.',
            'delivery_end.after_or_equal'     => 'The delivery end date must be on or after the start date.',
        ];
    }
}
