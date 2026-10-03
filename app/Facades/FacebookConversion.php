<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class FacebookConversion extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'facebook-conversion';
    }
}