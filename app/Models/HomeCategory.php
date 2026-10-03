<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Type;
use App\Models\Product;
use App\Models\Category;

class HomeCategory extends Model
{
    use HasFactory;
    protected $guarded=[];
    
    public function category(){

        return $this->belongsTo(Category::class);
    }
}
