<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $addHttpCookie = true;

    protected $except = [
        '/pay',
        '/pay-via-ajax',
        '/success',
        '/cancel',
        '/fail',
        '/ipn',
        '/courier-webhook',
        'uddoktapay/success',
        'uddoktapay/cancel',
        'api/webhook/manydial',
        '/api/webhook/manydial'
    ];
}