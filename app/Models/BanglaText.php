<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanglaText extends Model
{
    use HasFactory;
    
    protected $guarded=['id'];
    
    protected $table = 'bangla_text';
    
    public $timestamps = false;
}
