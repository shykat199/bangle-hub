<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class FacebookConversionService
{
    // Persists across visits so anonymous ViewContent/AddToCart events can be
    // stitched to the same person as their eventual Purchase — the one
    // identifier available even before a visitor gives an email or phone.
    private const VISITOR_COOKIE = '_bc_vid';
    private const VISITOR_COOKIE_MINUTES = 60 * 24 * 365 * 2; // 2 years

    private Client $client;
    private $pixelId;
    private $accessToken;
    private ?string $visitorIdCache = null;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://graph.facebook.com/v19.0/',
            'timeout'  => 10.0,
            'verify'   => true,
        ]);

        $this->pixelId = $this->getConfig('fb_pixel_id', 'FACEBOOK_PIXEL_ID');
        $this->accessToken = $this->getConfig('fb_access_token', 'FACEBOOK_ACCESS_TOKEN');
    }

    private function getConfig($settingKey, $envKey)
    {
        if (function_exists('setting') && setting($settingKey)) {
            return setting($settingKey);
        }
        return config("services.facebook.{$settingKey}") ?? env($envKey);
    }

    public function sendEvent(string $eventName, array $customData = [], ?string $eventId = null, array $override = []): array
    {
        try {
            // "GTM only" means the shop owner has taken the whole Meta setup
            // into Google Tag Manager. Sending a server copy on top of that
            // would be counted as a second, separate conversion, because GTM's
            // tags carry GTM's own event ids and Meta has nothing to match on.
            // Gating here rather than at each call site means no controller can
            // leak an event past the setting.
            if (!\App\Support\MetaEventSource::serverSendsCapi()) {
                return ['ok' => false, 'error' => 'Server events disabled (GTM only mode)'];
            }

            if (empty($this->pixelId) || empty($this->accessToken)) {
                Log::warning('Facebook CAPI skipped: missing ID or Token');
                return ['ok' => false, 'error' => 'Missing Configuration'];
            }

            $userData = $this->getUserData();

            if (!empty($override['user_data']) && is_array($override['user_data'])) {
                $userData = array_merge($userData, $override['user_data']);
            }

            $userData = $this->cleanArray($userData);

            $eventData = [
                'event_name'       => $eventName,
                'event_time'       => $override['event_time'] ?? time(),
                'event_source_url' => $override['event_source_url'] ?? request()->fullUrl(),
                'action_source'    => $override['action_source'] ?? 'website',
                'user_data'        => $userData,
            ];

            if (!empty($eventId)) {
                $eventData['event_id'] = $eventId;
            }

            if (!empty($customData)) {
                $eventData['custom_data'] = $customData;
            }

            $payload = ['data' => [$eventData]];



            $testCode = $this->getConfig('fb_pixel_test_code', 'FACEBOOK_PIXEL_TEST_CODE');
            if ($testCode) {
                $payload['test_event_code'] = $testCode;
            }

            $response = $this->client->post($this->pixelId . '/events', [
                'query' => ['access_token' => $this->accessToken],
                'json'  => $payload,
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            return ['ok' => true, 'status' => $response->getStatusCode(), 'body' => json_decode($response->getBody()->getContents(), true)];

        } catch (\Throwable $e) {
            Log::error('Facebook CAPI Error [' . $eventName . ']: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Callers pass the product either as a bare 'product_id' (landing pages)
     * or as a ready-made 'content_ids' array (cart/checkout). Accept both, so
     * no event reaches Meta with an empty catalogue reference — an empty
     * content_ids costs product matching and retargeting even when the event
     * itself deduplicates correctly against the browser pixel.
     */
    private function resolveContentIds(array $data): array
    {
        if (!empty($data['content_ids'])) {
            return array_map('strval', (array) $data['content_ids']);
        }

        if (!empty($data['product_id'])) {
            return [(string) $data['product_id']];
        }

        return [];
    }

    public function sendAddToCart(array $data, ?string $eventId = null, array $userData = []): array
    {
        $customData = [
            'currency'     => $data['currency'] ?? 'BDT',
            'value'        => (float)($data['value'] ?? 0),
            'content_ids'  => $this->resolveContentIds($data),
            'content_type' => 'product',
            'contents'     => $data['contents'] ?? [],
        ];
        return $this->sendEvent('AddToCart', $customData, $eventId, $this->userOverride($userData));
    }

    public function sendViewContent(array $data, ?string $eventId = null, array $userData = []): array
    {
        $customData = [
            'currency'         => $data['currency'] ?? 'BDT',
            'value'            => (float)($data['value'] ?? 0),
            'content_ids'      => $this->resolveContentIds($data),
            'content_name'     => $data['product_name'] ?? '',
            'content_type'     => 'product',
            'content_category' => $data['content_category'] ?? null,
        ];
        return $this->sendEvent('ViewContent', $customData, $eventId, $this->userOverride($userData));
    }

    public function sendInitiateCheckout(array $data, ?string $eventId = null, array $userData = []): array
    {
        $customData = [
            'currency'     => $data['currency'] ?? 'BDT',
            'value'        => (float)($data['value'] ?? 0),
            'num_items'    => (int)($data['num_items'] ?? 1),
            'content_ids'  => $this->resolveContentIds($data),
            'contents'     => $data['contents'] ?? [],
            'content_type' => 'product',
        ];
        return $this->sendEvent('InitiateCheckout', $customData, $eventId, $this->userOverride($userData));
    }

    /**
     * Controllers have always passed hashed customer data as a third argument
     * to these three methods, but the signatures only accepted two — PHP drops
     * extra arguments to userland functions without a word, so the identifiers
     * that raise Meta's Event Match Quality never left the server.
     */
    private function userOverride(array $userData): array
    {
        return empty($userData) ? [] : ['user_data' => $userData];
    }

    public function sendPurchase(array $data, ?string $eventId = null, array $userData = [], ?string $eventUrl = null): array
    {
        $override = ['user_data' => $userData];
        
        if (!empty($eventUrl)) {
            $override['event_source_url'] = $eventUrl;
        }

        return $this->sendEvent('Purchase', $data, $eventId, $override);
    }

    private function getUserData(): array
    {
        $request = request();
        $data = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
        ];

        if (!empty($_COOKIE['_fbc'])) $data['fbc'] = $_COOKIE['_fbc'];
        if (!empty($_COOKIE['_fbp'])) $data['fbp'] = $_COOKIE['_fbp'];

        $user = Auth::user();
        if ($user) {
            if (!empty($user->email)) {
                $data['em'] = [$this->hash($user->email)];
            }
            if (!empty($user->phone) || !empty($user->phone_number)) {
                $phone = $user->phone ?? $user->phone_number;
                $data['ph'] = [$this->hashPhone($phone)];
            }
            if (!empty($user->name)) {
                $parts = explode(' ', $user->name, 2);
                $data['fn'] = [$this->hash($parts[0])];
                if (isset($parts[1])) {
                    $data['ln'] = [$this->hash($parts[1])];
                }
            }
        }

        // Logged-in users get their real id (stable across devices); everyone
        // else gets the persistent visitor cookie, minted on first sight.
        $externalId = ($user && !empty($user->id)) ? (string) $user->id : $this->getOrCreateVisitorId();
        if (!empty($externalId)) {
            $data['external_id'] = [$this->hash($externalId)];
        }

        return $data;
    }

    /**
     * A queued cookie only reaches the browser on the response — request()
     * still can't read it back until the *next* request. Landing pages fire
     * ViewContent and InitiateCheckout in the same request, so without this
     * cache a first-time visitor would get two different ids in one page load.
     */
    private function getOrCreateVisitorId(): ?string
    {
        if (!empty($this->visitorIdCache)) {
            return $this->visitorIdCache;
        }

        $existing = request()->cookie(self::VISITOR_COOKIE);
        if (!empty($existing)) {
            return $this->visitorIdCache = $existing;
        }

        try {
            $newId = bin2hex(random_bytes(16));
        } catch (\Throwable $e) {
            return null;
        }

        Cookie::queue(self::VISITOR_COOKIE, $newId, self::VISITOR_COOKIE_MINUTES);
        return $this->visitorIdCache = $newId;
    }

    public function hash(?string $value): ?string
    {
        $value = trim(strtolower((string)$value));
        if ($value === '') return null;
        return hash('sha256', $value);
    }

    public function hashPhone(?string $value): ?string
    {
        $value = preg_replace('/\D+/', '', (string)$value);
        if ($value === '') return null;
        
        if (strlen($value) == 11 && str_starts_with($value, '0')) {
            $value = '88' . $value;
        }
        return hash('sha256', $value);
    }

    private function cleanArray(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $v = array_values(array_filter($v, fn($x) => $x !== null && $x !== ''));
                if (!empty($v)) $out[$k] = $v;
            } else {
                if ($v !== null && $v !== '') $out[$k] = $v;
            }
        }
        return $out;
    }
}