<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Refuses any request carrying an uploaded file that could be executed by the
 * web server.
 *
 * Several upload paths (variation images, landing-page images and sliders,
 * about-us, career, home sections) named the saved file from the client's own
 * extension and moved it straight into public/, where Apache serves — and
 * runs — whatever it finds. Individually guarding each of those call sites
 * leaves the next one someone adds unprotected, so the check lives here, in
 * front of every request, once.
 *
 * This is a hard block on executable types, not a whitelist: some legitimate
 * uploads really are CSVs (the missing-parcel manifest) or videos (home
 * banners), and those stay allowed.
 */
class BlockDangerousUploads
{
    // Anything a web server may hand to an interpreter instead of the browser.
    const BLOCKED_EXTENSIONS = [
        'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8',
        'phtml', 'pht', 'phar', 'phps', 'inc',
        'cgi', 'pl', 'py', 'rb', 'jsp', 'jspx', 'asp', 'aspx', 'ashx', 'asmx',
        'sh', 'bash', 'exe', 'dll', 'so', 'bat', 'cmd', 'com', 'msi', 'jar',
        'htaccess', 'htpasswd', 'ini', 'conf',
    ];

    public function handle(Request $request, Closure $next)
    {
        foreach ($this->uploadedFiles($request->allFiles()) as $file) {
            if (!$this->isDangerous($file)) continue;

            Log::warning('Blocked dangerous upload', [
                'name' => $file->getClientOriginalName(),
                'ip'   => $request->ip(),
                'user' => optional($request->user())->id,
                'path' => $request->path(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status'  => false,
                    'msg'     => 'This file type is not allowed.',
                ], 422);
            }

            return back()->with('error', 'This file type is not allowed.');
        }

        return $next($request);
    }

    /**
     * Flattens the nested arrays Laravel returns for inputs like
     * variation_image[] or slider[0][image].
     */
    private function uploadedFiles($files): array
    {
        $flat = [];

        foreach ($files as $file) {
            if (is_array($file)) {
                $flat = array_merge($flat, $this->uploadedFiles($file));
            } elseif ($file instanceof UploadedFile) {
                $flat[] = $file;
            }
        }

        return $flat;
    }

    private function isDangerous(UploadedFile $file): bool
    {
        // Check every dot-separated part, so "shell.php.jpg" is caught too —
        // some server configurations happily execute that.
        $parts = explode('.', strtolower($file->getClientOriginalName()));
        array_shift($parts);

        foreach ($parts as $part) {
            if (in_array(trim($part), self::BLOCKED_EXTENSIONS, true)) {
                return true;
            }
        }

        return false;
    }
}
