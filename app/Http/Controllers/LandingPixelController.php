<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Information;

class LandingPixelController extends Controller
{
    public function initiateCheckout(Request $request)
    {
        $info = Information::first();

        $pixelId = $info->fb_pixel_id ?? null;

        // ✅ CAPI token (Meta Events Manager -> Pixel -> Settings -> Conversions API -> Generate token)
        $accessToken = $info->fb_access_token ?? null;

        // ✅ Test Events code (optional). You can store it in DB like: test_event_code
        $testEventCode = $request->input('test_event_code') ?? ($info->test_event_code ?? null);

        if (!$pixelId || !$accessToken) {
            return response()->json([
                'ok' => false,
                'msg' => 'Pixel ID / Access Token missing',
                'pixel_id_found' => !empty($pixelId),
                'token_found' => !empty($accessToken),
            ], 200);
        }

        $eventId = (string) $request->input('event_id', '');
        $payload = (array) $request->input('payload', []);
        $pageUrl = (string) $request->input('page_url', $request->fullUrl());

        if (!$eventId) {
            return response()->json(['ok' => false, 'msg' => 'event_id missing'], 200);
        }

        // ✅ Server-side dedupe (session-based)
        $dedupeKey = 'lp_ic_' . $eventId;
        if (session()->has($dedupeKey)) {
            return response()->json(['ok' => true, 'deduped' => true, 'event_id' => $eventId], 200);
        }
        session()->put($dedupeKey, 1);

        // best-effort user data
        $ip = $request->ip();
        $ua = $request->userAgent();

        // ✅ Build event data for CAPI
        $eventData = [
            'event_name' => 'InitiateCheckout',
            'event_time' => time(),
            'event_id' => $eventId,
            'event_source_url' => $pageUrl,
            'action_source' => 'website',
            'user_data' => [
                'client_ip_address' => $ip,
                'client_user_agent' => $ua,
            ],
            'custom_data' => [
                'currency' => $payload['currency'] ?? 'BDT',
                'value' => $payload['value'] ?? 0,
                'content_type' => $payload['content_type'] ?? 'product',
                'content_ids' => $payload['content_ids'] ?? [],
                'contents' => $payload['contents'] ?? [],
                'num_items' => $payload['num_items'] ?? null,
            ],
        ];

        $endpoint = "https://graph.facebook.com/v20.0/{$pixelId}/events";

        // ✅ request body
        $body = [
            'data' => [$eventData],
            'access_token' => $accessToken,
        ];

        // ✅ add test_event_code (only when available)
        if (!empty($testEventCode)) {
            $body['test_event_code'] = $testEventCode;
        }

        // ✅ Logging for debug
        Log::info('LP CAPI InitiateCheckout => sending', [
            'pixel_id' => $pixelId,
            'event_id' => $eventId,
            'has_test_event_code' => !empty($testEventCode),
            'page_url' => $pageUrl,
        ]);

        try {
            $resp = Http::timeout(10)->asJson()->post($endpoint, $body);

            Log::info('LP CAPI InitiateCheckout <= response', [
                'status' => $resp->status(),
                'json' => $resp->json(),
                'raw' => $resp->body(),
            ]);

            return response()->json([
                'ok' => $resp->successful(),
                'status' => $resp->status(),
                'event_id' => $eventId,
                'test_event_code_used' => !empty($testEventCode),
                'fb_response' => $resp->json(),
            ], 200);

        } catch (\Throwable $e) {

            Log::error('LP CAPI InitiateCheckout ERROR', [
                'event_id' => $eventId,
                'msg' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ], 200);
        }
    }
}
