<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array  sendEvent(string $eventName, array $properties = [], ?string $eventId = null, array $override = [])
 * @method static array  sendPurchase(array $data, ?string $eventId = null, array $userData = [], ?string $eventUrl = null)
 * @method static array  sendInitiateCheckout(array $data, ?string $eventId = null, array $userData = [])
 * @method static array  sendViewContent(array $data, ?string $eventId = null, array $userData = [])
 * @method static array  sendAddToCart(array $data, ?string $eventId = null, array $userData = [])
 * @method static ?string hash(?string $value)
 * @method static ?string hashPhone(?string $value)
 */
class TikTokConversion extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tiktok-conversion';
    }
}
