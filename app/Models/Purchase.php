<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseLine;
use App\Models\Supplier;
use App\Models\User;
use App\Models\PurchasePayment;

class Purchase extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function lines(){

        return $this->hasMany(PurchaseLine::class);
    }

    public function supplier(){

        return $this->belongsTo(User::class);
    }
    
    public function payments(){

        return $this->hasMany(PurchasePayment::class);
    }
}
