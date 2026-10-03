<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureOrderSource
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && !$request->is('admin/*') && !$request->is('api/*')) {
            $session = $request->session();

            if (!$session->has('order_source_data')) {
                $hasUrlSource = $request->has('utm_source')
                             || $request->has('ttclid')
                             || $request->has('fbclid')
                             || $request->has('utm_medium')
                             || $request->has('utm_campaign');

                $refererHeader = $request->headers->get('referer');
                $currentHost   = $request->getHost();
                $refererHost   = $refererHeader ? parse_url($refererHeader, PHP_URL_HOST) : null;
                $externalReferer = $refererHost && $currentHost
                    && !str_contains(strtolower($refererHost), strtolower($currentHost));

                if ($hasUrlSource || $externalReferer) {
                    $session->put('order_source_data', [
                        'utm_source'   => $request->input('utm_source'),
                        'utm_medium'   => $request->input('utm_medium'),
                        'utm_campaign' => $request->input('utm_campaign'),
                        'ttclid'       => $request->input('ttclid'),
                        'fbclid'       => $request->input('fbclid'),
                        'referer'      => $externalReferer ? $refererHeader : null,
                        'landing_url'  => $request->fullUrl(),
                        'captured_at'  => now()->toDateTimeString(),
                    ]);
                }
            }

            if (!empty($_COOKIE['ttclid']) || $request->has('ttclid')) {
                $ttclid = $request->input('ttclid') ?: $_COOKIE['ttclid'];
                @setcookie('ttclid', $ttclid, time() + (60 * 60 * 24 * 90), '/');
            }
        }

        return $next($request);
    }
}
