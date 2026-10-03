<?php

namespace App\Support;

use App\Models\Information;

/**
 * Single answer to "who sends the Meta events?", shared by the blades and by
 * every server-side CAPI call.
 *
 * The shop owner picks one of three arrangements in Settings. Each is
 * internally consistent on its own, so whichever is chosen the events are
 * counted exactly once:
 *
 *   theme     Browser events come from this site's own code, and the server
 *             sends the Conversions API copy. Both use the same event id, so
 *             Meta merges them. Nothing to configure — this is the default.
 *
 *   gtm       Browser events come from Google Tag Manager, the server still
 *             sends the Conversions API copy. The site publishes the event id
 *             into the dataLayer, so GTM's Meta tag must be told to use it —
 *             otherwise Meta cannot merge the two and counts every sale twice.
 *
 *   gtm_only  Everything comes from Google Tag Manager; the server sends
 *             nothing. Impossible to double count, but the Conversions API is
 *             switched off, so sales from browsers that block the pixel
 *             (Safari/iOS, ad blockers) stop being attributed.
 */
class MetaEventSource
{
    const THEME    = 'theme';
    const GTM      = 'gtm';
    const GTM_ONLY = 'gtm_only';

    private static ?string $cached = null;

    public static function current(): string
    {
        if (self::$cached !== null) return self::$cached;

        try {
            $value = Information::query()->value('fb_event_source');
        } catch (\Throwable $e) {
            $value = null;
        }

        $value = strtolower(trim((string) $value));

        self::$cached = in_array($value, [self::THEME, self::GTM, self::GTM_ONLY], true)
            ? $value
            : self::THEME;

        return self::$cached;
    }

    /** Should the site's own blades fire fbq() themselves? */
    public static function themeFiresBrowserEvents(): bool
    {
        return self::current() === self::THEME;
    }

    /** Should the server send the Conversions API copy? */
    public static function serverSendsCapi(): bool
    {
        return self::current() !== self::GTM_ONLY;
    }

    /** Test hook — the cached value would otherwise survive a settings change mid-request. */
    public static function forget(): void
    {
        self::$cached = null;
    }
}
