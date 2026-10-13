<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreorderDeliveryLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_date'     => 'date',
        'new_date'     => 'date',
        'old_end_date' => 'date',
        'new_end_date' => 'date',
        'notified_at'  => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
