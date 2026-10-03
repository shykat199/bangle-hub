<?php

namespace App\Http\Traits;

trait DetectsOrderSource
{
    protected function detectOrderSource(): array
    {
        $sd = session('order_source_data', []);

        $utmSource   = $sd['utm_source']   ?? null;
        $utmMedium   = $sd['utm_medium']   ?? null;
        $utmCampaign = $sd['utm_campaign'] ?? null;
        $ttclid      = $sd['ttclid']       ?? ($_COOKIE['ttclid'] ?? null);
        $fbclid      = $sd['fbclid']       ?? null;
        $referer     = $sd['referer']      ?? null;

        $detected = 'Direct';

        if (!empty($ttclid)) {
            $detected = 'TikTok Ad';
        } elseif (!empty($fbclid)) {
            $detected = 'Facebook Ad';
        } elseif (!empty($utmSource)) {
            $s = strtolower($utmSource);
            if (str_contains($s, 'tiktok'))         $detected = 'TikTok';
            elseif (str_contains($s, 'facebook') || str_contains($s, 'fb') || str_contains($s, 'meta')) $detected = 'Facebook';
            elseif (str_contains($s, 'instagram') || str_contains($s, 'ig')) $detected = 'Instagram';
            elseif (str_contains($s, 'google'))     $detected = 'Google';
            elseif (str_contains($s, 'youtube'))    $detected = 'YouTube';
            elseif (str_contains($s, 'whatsapp') || str_contains($s, 'wa')) $detected = 'WhatsApp';
            elseif (str_contains($s, 'twitter') || str_contains($s, 'x.com')) $detected = 'Twitter';
            else $detected = ucfirst($utmSource);
        } elseif (!empty($referer)) {
            $host = strtolower((string) parse_url($referer, PHP_URL_HOST));
            if (str_contains($host, 'tiktok'))         $detected = 'TikTok';
            elseif (str_contains($host, 'facebook') || str_contains($host, 'fb.com')) $detected = 'Facebook';
            elseif (str_contains($host, 'instagram'))  $detected = 'Instagram';
            elseif (str_contains($host, 'google'))     $detected = 'Google';
            elseif (str_contains($host, 'youtube'))    $detected = 'YouTube';
            elseif (str_contains($host, 'whatsapp') || str_contains($host, 'wa.me')) $detected = 'WhatsApp';
            elseif (str_contains($host, 'twitter') || str_contains($host, 'x.com')) $detected = 'Twitter';
            elseif (str_contains($host, 'bing'))       $detected = 'Bing';
            elseif (str_contains($host, request()->getHost())) $detected = 'Direct';
            else $detected = 'Referral';
        }

        return [
            'source'       => $detected,
            'utm_source'   => $utmSource,
            'utm_medium'   => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'referer'      => $referer,
        ];
    }
}
