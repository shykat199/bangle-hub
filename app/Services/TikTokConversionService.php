<?php

namespace App\Services;

use App\Models\Information;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TikTokConversionService
{
    private Client $client;
    private ?string $pixelId = null;
    private ?string $accessToken = null;
    private ?string $testEventCode = null;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://business-api.tiktok.com/open_api/v1.3/',
            'timeout'  => 10.0,
            'verify'   => true,
        ]);

        $info = null;
        if (class_exists(Information::class)) {
            try {
                $info = Information::first();
            } catch (\Throwable $e) {
                $info = null;
            }
        }

        $this->pixelId       = $info->tt_pixel_id        ?? env('TIKTOK_PIXEL_ID');
        $this->accessToken   = $info->tt_access_token    ?? env('TIKTOK_ACCESS_TOKEN');
        $this->testEventCode = $info->tt_test_event_code ?? env('TIKTOK_TEST_EVENT_CODE');
    }

    public function sendEvent(string $eventName, array $properties = [], ?string $eventId = null, array $override = []): array
    {
        try {
            if (empty($this->pixelId) || empty($this->accessToken)) {
                Log::warning('TikTok Events API skipped: missing Pixel ID or Access Token');
                return ['ok' => false, 'error' => 'Missing Configuration'];
            }

            $userData = $this->getUserData();
            if (!empty($override['user']) && is_array($override['user'])) {
                $userData = array_merge($userData, $override['user']);
            }
            $userData = $this->cleanArray($userData);

            $eventData = [
                'event'       => $eventName,
                'event_time'  => $override['event_time'] ?? time(),
                'event_id'    => $eventId ?: (string) (microtime(true) * 1000),
                'user'        => $userData,
                'page'        => [
                    'url' => $override['event_source_url'] ?? request()->fullUrl(),
                ],
            ];

            if (!empty($properties)) {
                $eventData['properties'] = $properties;
            }

            $payload = [
                'event_source'    => 'web',
                'event_source_id' => $this->pixelId,
                'data'            => [$eventData],
            ];

            if (!empty($this->testEventCode)) {
                $payload['test_event_code'] = $this->testEventCode;
            }

            $response = $this->client->post('event/track/', [
                'headers' => [
                    'Access-Token' => $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['code']) && $body['code'] !== 0) {
                Log::warning('TikTok Events API non-zero code [' . $eventName . ']: ' . json_encode($body));
            }

            return ['ok' => true, 'status' => $response->getStatusCode(), 'body' => $body];

        } catch (\Throwable $e) {
            Log::error('TikTok Events API Error [' . $eventName . ']: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendPurchase(array $data, ?string $eventId = null, array $userData = [], ?string $eventUrl = null): array
    {
        $properties = [
            'currency'     => $data['currency'] ?? 'BDT',
            'value'        => (float) ($data['value'] ?? 0),
            'content_type' => 'product',
            'content_ids'  => array_map('strval', $data['content_ids'] ?? []),
            'contents'     => $this->contents($data['contents'] ?? []),
            'description'  => 'Order #' . ($data['order_id'] ?? ''),
        ];

        $override = ['user' => $userData];
        if (!empty($eventUrl)) {
            $override['event_source_url'] = $eventUrl;
        }

        return $this->sendEvent('CompletePayment', $properties, $eventId, $override);
    }

    public function sendInitiateCheckout(array $data, ?string $eventId = null, array $userData = []): array
    {
        $properties = [
            'currency'     => $data['currency'] ?? 'BDT',
            'value'        => (float) ($data['value'] ?? 0),
            'content_type' => 'product',
            'content_ids'  => array_map('strval', $data['content_ids'] ?? []),
            'contents'     => $this->contents($data['contents'] ?? []),
        ];
        return $this->sendEvent('InitiateCheckout', $properties, $eventId, ['user' => $userData]);
    }

    public function sendViewContent(array $data, ?string $eventId = null, array $userData = []): array
    {
        $contents = $data['contents'] ?? [[
            'id'         => $data['product_id'] ?? '',
            'name'       => $data['product_name'] ?? '',
            'quantity'   => 1,
            'item_price' => $data['value'] ?? 0,
        ]];

        $properties = [
            'currency'     => $data['currency'] ?? 'BDT',
            'value'        => (float) ($data['value'] ?? 0),
            'content_type' => 'product',
            'content_ids'  => [(string) ($data['product_id'] ?? '')],
            'content_name' => $data['product_name'] ?? '',
            'contents'     => $this->contents($contents),
        ];
        return $this->sendEvent('ViewContent', $properties, $eventId, ['user' => $userData]);
    }

    public function sendAddToCart(array $data, ?string $eventId = null, array $userData = []): array
    {
        $properties = [
            'currency'     => $data['currency'] ?? 'BDT',
            'value'        => (float) ($data['value'] ?? 0),
            'content_type' => 'product',
            'content_ids'  => array_map('strval', $data['content_ids'] ?? []),
            'contents'     => $this->contents($data['contents'] ?? []),
        ];
        return $this->sendEvent('AddToCart', $properties, $eventId, ['user' => $userData]);
    }

    // TikTok matches on contents[].content_id; the browser pixel always sends this
    // shape, so the server payload has to agree or dedup keeps the poorer version.
    private function contents(array $items): array
    {
        return array_map(function ($c) {
            return [
                'content_id'   => (string) ($c['id'] ?? $c['content_id'] ?? ''),
                'content_type' => 'product',
                'content_name' => (string) ($c['name'] ?? $c['content_name'] ?? ''),
                'quantity'     => (int) ($c['quantity'] ?? 1),
                'price'        => (float) ($c['item_price'] ?? $c['price'] ?? 0),
            ];
        }, array_values($items));
    }

    private function getUserData(): array
    {
        $request = request();
        $data = [
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        if (!empty($_COOKIE['ttclid'])) $data['ttclid'] = $_COOKIE['ttclid'];
        if (!empty($_COOKIE['_ttp']))   $data['ttp']    = $_COOKIE['_ttp'];

        $user = Auth::user();
        if ($user) {
            if (!empty($user->email)) {
                $data['email'] = $this->hash($user->email);
            }
            $phone = $user->mobile ?? $user->phone ?? $user->phone_number ?? null;
            if (!empty($phone)) {
                $data['phone'] = $this->hashPhone($phone);
            }
        }

        return $data;
    }

    public function hash(?string $value): ?string
    {
        $value = trim(strtolower((string) $value));
        if ($value === '') return null;
        return hash('sha256', $value);
    }

    public function hashPhone(?string $value): ?string
    {
        $value = preg_replace('/\D+/', '', (string) $value);
        if ($value === '') return null;

        if (strlen($value) == 11 && str_starts_with($value, '0')) {
            $value = '88' . $value;
        }
        return hash('sha256', '+' . $value);
    }

    private function cleanArray(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $v = array_filter($v, fn($x) => $x !== null && $x !== '');
                if (!empty($v)) $out[$k] = $v;
            } else {
                if ($v !== null && $v !== '') $out[$k] = $v;
            }
        }
        return $out;
    }
}
