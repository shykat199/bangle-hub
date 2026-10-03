<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddonLicense extends Model
{
    use HasFactory;
    
    protected $fillable = ['addon_slug', 'license_key', 'version'];
}