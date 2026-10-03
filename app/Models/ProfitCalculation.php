<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfitCalculation extends Model
{
    protected $fillable = [
        'user_id', 'product_name', 'product_category', 'note',
        'cost_price', 'selling_price', 'marketing_cost', 'shipping_cost',
        'extra_shipping_profit', 'per_return_loss',
        'quantity', 'return_percentage', 'call_cancel_percentage', 'target_delivered_units',
        'net_profit', 'gross_profit', 'total_selling', 'total_cost',
        'profit_margin', 'roi', 'sold_units', 'return_units',
        'health_status', 'is_favorite', 'formula_version',
    ];

    protected $casts = [
        'cost_price' => 'float', 'selling_price' => 'float',
        'marketing_cost' => 'float', 'shipping_cost' => 'float',
        'extra_shipping_profit' => 'float', 'per_return_loss' => 'float',
        'quantity' => 'integer', 'return_percentage' => 'float',
        'call_cancel_percentage' => 'float', 'target_delivered_units' => 'integer',
        'net_profit' => 'float', 'gross_profit' => 'float',
        'total_selling' => 'float', 'total_cost' => 'float',
        'profit_margin' => 'float', 'roi' => 'float',
        'sold_units' => 'float', 'return_units' => 'float',
        'is_favorite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function inputsArray(): array
    {
        return [
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'marketing_cost' => $this->marketing_cost,
            'shipping_cost' => $this->shipping_cost,
            'quantity' => $this->quantity,
            'return_percentage' => $this->return_percentage,
            'extra_shipping_profit' => $this->extra_shipping_profit,
            'per_return_loss' => $this->per_return_loss,
            'call_cancel_percentage' => $this->call_cancel_percentage,
            'target_delivered_units' => $this->target_delivered_units,
        ];
    }
}
