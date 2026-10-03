<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ComboProduct;
use App\Models\Product;


class Combo extends Model
{
    use HasFactory;
    protected $guarded=[];
    
    public function products(){
        
        return $this->hasMany(ComboProduct::class,'combo_id');
    }
    
    public function product(){
        
        return $this->belongsTo(Product::class,'product_id');
    }
}
