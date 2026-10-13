<?php

namespace App\Http\Requests\Backend;

use App\Services\PreOrderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Bulk/single move of pre-orders: Pre-Order → Processing → Ready to Ship. */
class PreOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('order.edit');
    }

    public function rules(): array
    {
        return [
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'integer',
            'status'      => ['required', Rule::in(array_values(PreOrderService::FLOW))],
        ];
    }

    public function messages(): array
    {
        return ['order_ids.required' => 'Please select at least one pre-order.'];
    }
}
