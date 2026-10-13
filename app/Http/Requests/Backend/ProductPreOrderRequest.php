<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The "Pre-Order" part of the product create/edit form: the product-level
 * section plus one set of fields per variation row (var_* arrays, posted in
 * the same order as size_id[] / price[]).
 *
 * Injected into ProductController::store()/update() next to the plain
 * Request, so the rest of the form keeps its existing validation.
 */
class ProductPreOrderRequest extends FormRequest
{
    private const FIELDS = [
        'preorder_limit', 'preorder_delivery_text', 'preorder_delivery_start', 'preorder_delivery_end',
        'preorder_payment_type', 'preorder_advance_type', 'preorder_advance_amount',
    ];

    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->can('product.create') || $user->can('product.edit'));
    }

    public function rules(): array
    {
        return [
            'is_preorder'             => 'nullable|boolean',
            'preorder_limit'          => 'nullable|integer|min:1',
            'preorder_delivery_text'  => 'nullable|string|max:255',
            'preorder_delivery_start' => 'nullable|date',
            'preorder_delivery_end'   => 'nullable|date|after_or_equal:preorder_delivery_start',
            'preorder_payment_type'   => 'nullable|in:full,advance,both',
            'preorder_advance_type'   => 'nullable|in:fixed,percentage',
            'preorder_advance_amount' => 'nullable|numeric|min:0',

            'var_is_preorder'             => 'nullable|array',
            'var_preorder_limit'          => 'nullable|array',
            'var_preorder_delivery_text'  => 'nullable|array',
            'var_preorder_delivery_start' => 'nullable|array',
            'var_preorder_delivery_end'   => 'nullable|array',
            'var_preorder_payment_type'   => 'nullable|array',
            'var_preorder_advance_type'   => 'nullable|array',
            'var_preorder_advance_amount' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'preorder_delivery_end.after_or_equal' => 'The delivery end date must be on or after the start date.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $isVariable = $this->input('type') === 'variable';
            $productOn  = $this->boolean('is_preorder');
            $productPrice = $this->effectivePrice($this->input('sell_price'), $this->input('after_discount'));

            if ($productOn && in_array($this->productPaymentType(), ['advance', 'both'], true)) {
                $type   = $this->input('preorder_advance_type');
                $amount = (float) $this->input('preorder_advance_amount');

                if (!$type || $amount <= 0) {
                    $validator->errors()->add('preorder_advance_amount', 'Enter the advance type and amount, or choose Full payment.');
                } elseif ($problem = $this->advanceProblem($type, $amount, $isVariable ? null : $productPrice)) {
                    $validator->errors()->add('preorder_advance_amount', $problem);
                }
            }

            if (!$isVariable) {
                return;
            }

            // One message key for all rows: the admin form marks errors by
            // input name and cannot address "field.3"-style array keys.
            foreach ((array) $this->input('var_is_preorder', []) as $i => $on) {
                if ((int) $on !== 1) {
                    continue;
                }

                $row = 'Variation ' . ($i + 1) . ': ';
                $price = $this->effectivePrice($this->input("price.$i"), $this->input("after_discount_price.$i")) ?: $productPrice;

                $limit = $this->input("var_preorder_limit.$i");
                if ($limit !== null && $limit !== '' && (!ctype_digit((string) $limit) || (int) $limit < 1)) {
                    $validator->errors()->add('preorder_variations', $row . 'the pre-order limit must be a whole number of 1 or more.');
                }

                $start = $this->input("var_preorder_delivery_start.$i");
                $end   = $this->input("var_preorder_delivery_end.$i");
                if (($start && !strtotime($start)) || ($end && !strtotime($end))) {
                    $validator->errors()->add('preorder_variations', $row . 'the delivery dates are not valid dates.');
                } elseif ($start && $end && strtotime($end) < strtotime($start)) {
                    $validator->errors()->add('preorder_variations', $row . 'the delivery end date must be on or after the start date.');
                }

                $paymentType = $this->input("var_preorder_payment_type.$i") ?: ($productOn ? $this->productPaymentType() : 'full');
                if (!in_array($paymentType, ['full', 'advance', 'both'], true)) {
                    $validator->errors()->add('preorder_variations', $row . 'the payment type is not valid.');
                    continue;
                }
                if ($paymentType === 'full') {
                    continue;
                }

                $ownAmount = $this->input("var_preorder_advance_amount.$i");
                if ($ownAmount !== null && $ownAmount !== '') {
                    [$type, $amount] = [$this->input("var_preorder_advance_type.$i"), (float) $ownAmount];
                } else {
                    [$type, $amount] = $productOn
                        ? [$this->input('preorder_advance_type'), (float) $this->input('preorder_advance_amount')]
                        : [null, 0.0];
                }

                if (!in_array($type, ['fixed', 'percentage'], true) || $amount <= 0) {
                    $validator->errors()->add('preorder_variations', $row . 'set an advance type and amount, or choose Full payment.');
                } elseif ($problem = $this->advanceProblem($type, $amount, $price)) {
                    $validator->errors()->add('preorder_variations', $row . lcfirst($problem));
                }
            }
        });
    }

    private function productPaymentType(): string
    {
        return $this->input('preorder_payment_type') ?: 'full';
    }

    private function effectivePrice($price, $afterDiscount): float
    {
        $price = (float) $price;
        $afterDiscount = (float) $afterDiscount;

        return ($afterDiscount > 0 && $afterDiscount < $price) ? $afterDiscount : $price;
    }

    /** An advance has to be less than the price, otherwise it is a full payment. */
    private function advanceProblem(string $type, float $amount, ?float $price): ?string
    {
        if ($type === 'percentage') {
            return $amount >= 100 ? 'The advance percentage must be less than 100.' : null;
        }

        if ($price !== null && $amount >= $price) {
            return 'The advance amount must be less than the product price (' . rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.') . ').';
        }

        return null;
    }

    /** Product-level columns. Switched off = the settings are cleared. */
    public function productData(): array
    {
        return $this->preorderColumns(
            $this->boolean('is_preorder'),
            fn ($field) => $this->input($field),
            'full'
        );
    }

    /** Columns for the variation posted at row $i. */
    public function variationData(int $i): array
    {
        return $this->preorderColumns(
            (int) $this->input("var_is_preorder.$i") === 1,
            fn ($field) => $this->input('var_' . $field . '.' . $i),
            null // blank payment type = same as the product
        );
    }

    private function preorderColumns(bool $on, callable $value, ?string $defaultPaymentType): array
    {
        $data = ['is_preorder' => $on];

        foreach (self::FIELDS as $field) {
            $raw = $on ? $value($field) : null;
            $data[$field] = ($raw === null || $raw === '') ? null : $raw;
        }

        $data['preorder_payment_type'] = $data['preorder_payment_type'] ?: $defaultPaymentType;

        // No advance without an amount (and none at all on "Full payment").
        if ($data['preorder_payment_type'] === 'full' || (float) $data['preorder_advance_amount'] <= 0) {
            $data['preorder_advance_type'] = null;
            $data['preorder_advance_amount'] = null;
        }

        return $data;
    }
}
