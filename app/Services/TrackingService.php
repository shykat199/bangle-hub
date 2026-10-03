<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TrackingService
{
    public function trackFacebook($eventName, $data = [])
    {
        $payload = [
            'data' => [[
                'event_name' => $eventName,
                'event_time' => time(),
                'action_source' => 'website',
                'event_id' => $data['event_id'] ?? uniqid(),
                'user_data' => [
                    'em' => [hash('sha256', $data['email'] ?? '')],
                    'ph' => [hash('sha256', $data['phone'] ?? '')],
                    'client_ip_address' => request()->ip(),
                    'client_user_agent' => request()->userAgent(),
                ],
                'custom_data' => $data['custom_data'] ?? [],
            ]]
        ];

        return Http::post("https://graph.facebook.com/v21.0/" . setting('fb_pixel_id') . "/events?access_token=" . setting('fb_access_token'), $payload);
    }
}
