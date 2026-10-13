<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

/** Filters of the admin Pre-Orders list. */
class PreOrderFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('order.view');
    }

    public function rules(): array
    {
        return [
            'q'             => 'nullable|string|max:100',
            'status'        => 'nullable|string|max:50',
            'product_id'    => 'nullable|integer',
            'payment_mode'  => 'nullable|in:full,advance',
            'delivery_from' => 'nullable|date',
            'delivery_to'   => 'nullable|date|after_or_equal:delivery_from',
        ];
    }

    public function messages(): array
    {
        return ['delivery_to.after_or_equal' => 'The delivery "to" date must be on or after the "from" date.'];
    }
}
