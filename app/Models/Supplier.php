<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase;

class Supplier extends Model
{
    use HasFactory;
    
    protected $guarded=[];
    
    public function purchase(){

        return $this->hasMany(Purchase::class);
    }
}
