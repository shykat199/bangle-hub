<?php
namespace App\Support;

use Exception;
use Illuminate\Support\Facades\Log;

class Formatter
{
    // Paths
    public static function signedFilePath(): string
    {
        return storage_path('app/cache/settings.json');
    }

    public static function lockFilePath(): string
    {
        return storage_path('framework/cache/.cache_stamp_' . date('Ymd'));
    }

    // ENTRYPOINT: call early in bootstrap
    public static function verify(): void
    {
        // 1. skip CLI / local / LICENSE_CHECK=off
        if (php_sapi_name() === 'cli') return;
        if (env('APP_ENV') === 'local') return;
        if (env('LICENSE_CHECK', 'on') === 'off') return;

        // 2. skip if today's lock exists
        $lock = self::lockFilePath();
        if (file_exists($lock)) return;

        // 3. load signed file
        $path = self::signedFilePath();
        if (!file_exists($path)) {
            self::handleFail('missing_file', $path);
        }

        $raw = file_get_contents($path);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            self::handleFail('invalid_json', $path);
        }

        // 4. verify signature (HMAC) -- implement hash check
        $signature = $data['signature'] ?? '';
        unset($data['signature']);
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $expected = hash_hmac('sha256', $payload, env('LICENSE_KEY',''));
        if (!hash_equals($expected, $signature)) {
            self::handleTamper('invalid_signature', $path, $raw);
        }

        // 5. check expiry if present
        if (!empty($data['expires_at']) && strtotime($data['expires_at']) < time()) {
            self::handleFail('expired', $data['expires_at']);
        }

        // 6. normalize host & compare
        $host = self::normalizeHost($_SERVER['HTTP_HOST'] ?? '');
        $allowed = array_map([self::class, 'normalizeHost'], $data['domains'] ?? []);
        if (!in_array($host, $allowed, true)) {
            self::handleFail('domain_not_allowed', $host);
        }

        // 7. success — write lock for today
        @file_put_contents($lock, date('c'));
        @chmod($lock, 0600);
    }

    public static function saveSigned(array $domains, array $meta = []): bool
    {
        if (empty($domains)) {
            throw new \Exception('No domains provided');
        }
    
        // 1. normalize domains
        $domains = array_map('trim', $domains);
        $domains = array_filter($domains); // remove empty
    
        // 2. build payload
        $data = [
            'domains' => $domains,
            'meta' => $meta,
            'issued_at' => date('c'),
            // 'expires_at' => date('c', strtotime('+1 year')), // optional
        ];
    
        // 3. encode JSON payload
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
        // 4. compute HMAC signature
        $key = env('LICENSE_KEY', '');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $signature = hash_hmac('sha256', $payload, $key);
    
        // 5. combine payload + signature
        $signed = [
            'domains' => $data['domains'],
            'meta' => $data['meta'],
            'issued_at' => $data['issued_at'],
            'signature' => $signature
        ];
    
        // 6. write atomically to file
        $path = self::signedFilePath();
        $dir = dirname($path);
        if (!file_exists($dir)) mkdir($dir, 0755, true);
    
        file_put_contents($path, json_encode($signed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    
        return true;
    }


    // Helpers to implement:
    public static function normalizeHost(string $h): string { /* strip scheme/port, lowercase */ }
    protected static function handleFail(string $code, $info = null): void { /* fail according to LICENSE_FAIL_MODE */ }
    protected static function handleTamper(string $code, string $file, ?string $raw = null): void { /* log, alert, create tamper file */ }
}
