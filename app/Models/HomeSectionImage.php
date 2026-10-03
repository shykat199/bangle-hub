<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSectionImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'text', 'image', 'mobile_image', 'section', 'link', 'is_for_small',
        'left_image_1', 'left_link_1', 'left_image_2', 'left_link_2',
        'left_image_3', 'left_link_3', 'left_image_4', 'left_link_4',
        'right_image', 'right_link', 'banner_video'
    ];
}