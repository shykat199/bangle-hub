<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Expense;

class Expense extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function type(){

        return $this->belongsTo(Type::class);
    }

    public function products(){

        return $this->hasMany(Product::class);
    }

}
