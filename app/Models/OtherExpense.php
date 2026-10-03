<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherExpense extends Model
{
    use HasFactory;

    // ডাটাবেস টেবিলের নাম
    protected $table = 'other_expenses';

    // যে ফিল্ডগুলোতে ডাটা ইনসার্ট করা হবে
    protected $fillable = [
        'date',
        'details',
        'amount',
    ];
}