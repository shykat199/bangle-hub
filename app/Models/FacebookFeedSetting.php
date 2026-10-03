<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookFeedSetting extends Model
{
    $setting = FacebookFeedSetting::first();

if (!$setting || !$setting->is_active) {
    abort(404);
    
    protected $table = 'facebook_feed_settings';
    protected $fillable = ['is_active'];
}
