<?php

namespace App\Support;

use App\Models\Information;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * The shop's SMTP account is typed into the admin settings page (the
 * `informations` table), not .env. Anything that sends mail calls this first —
 * same values SendOrderNotification applies for the admin's new-order email.
 */
class SiteMail
{
    /** Applies the admin's SMTP settings; returns the site settings row. */
    public static function configure(): ?Information
    {
        $settings = Information::first();

        // Mails go out while the customer / admin waits for the page, so an
        // unreachable SMTP server must give up quickly instead of hanging.
        if (config('mail.mailers.smtp.timeout') === null) {
            Config::set('mail.mailers.smtp.timeout', 10);
            Mail::purge('smtp');
        }

        if (!$settings || empty($settings->smtp_host) || empty($settings->smtp_user)) {
            return $settings;
        }

        $wanted = [
            'mail.mailers.smtp.host'     => $settings->smtp_host,
            'mail.mailers.smtp.port'     => $settings->smtp_port,
            'mail.mailers.smtp.username' => $settings->smtp_user,
            'mail.mailers.smtp.password' => $settings->smtp_pass,
            'mail.from.address'          => $settings->smtp_user,
            'mail.from.name'             => $settings->site_name ?? config('app.name'),
        ];

        $changed = false;
        foreach ($wanted as $key => $value) {
            if (config($key) !== $value) {
                Config::set($key, $value);
                $changed = true;
            }
        }

        // A mailer built before the settings were applied keeps its old transport.
        if ($changed) {
            Mail::purge('smtp');
        }

        return $settings;
    }
}
