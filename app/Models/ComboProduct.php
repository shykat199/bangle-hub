<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Size;

class ComboProduct extends Model
{
    use HasFactory;
    protected $guarded=[];
    
    public function product(){
        
        return $this->belongsTo(Product::class,'product_id');
    }
    
    public function size(){
        
        return $this->belongsTo(Size::class,'size_id');
    }
    
}
