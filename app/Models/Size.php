<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductStock;
class Size extends Model
{
    use HasFactory;

    protected $guarded=[];


    public function stocks(){

        return $this->hasMany(ProductStock::class,'size_id');
    }


    
}
