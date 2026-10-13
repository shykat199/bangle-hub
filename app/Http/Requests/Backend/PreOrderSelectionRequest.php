<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

/** A plain selection of pre-orders (e.g. "send due payment reminder"). */
class PreOrderSelectionRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return ['order_ids.required' => 'Please select at least one pre-order.'];
    }
}
