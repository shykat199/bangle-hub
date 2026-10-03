<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdCost extends Model
{
    use HasFactory;

    // ডাটাবেস টেবিলের নাম
    protected $table = 'ad_costs';

    // যে ফিল্ডগুলোতে ডাটা ইনসার্ট করা হবে
    protected $fillable = [
        'date',
        'platform',
        'usd_amount',
        'dollar_rate',
        'total_cost',
    ];
}