<?php

namespace App\Support;

/**
 * Next-gen copies of uploaded images.
 *
 * For "photo.jpg" this writes "photo.jpg.webp" and "photo.jpg.avif" beside it
 * (the same sibling naming /optimize-images already uses for thumbnails). The
 * original file is never touched, so anything that needs a plain JPEG/PNG — the
 * Facebook feed, old browsers — keeps working; getImage() hands the smaller
 * copy to browsers that can show it.
 *
 * A copy is only kept when it is actually smaller than what it replaces.
 */
class ImageOptimizer
{
    /** Visually lossless for product photos at these sizes; lower starts to soften fine detail (chains, stones). */
    public const WEBP_QUALITY = 82;
    public const AVIF_QUALITY = 62;

    private const SOURCE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Create the WebP / AVIF siblings of an image file.
     *
     * @return array<string,int> bytes written per format, e.g. ['webp' => 41200, 'avif' => 28900]
     */
    public static function generate(string $path): array
    {
        $written = [];

        if (!is_file($path) || !in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::SOURCE_EXTENSIONS, true)) {
            return $written;
        }

        $image = self::decode($path);
        if (!$image) {
            return $written;
        }

        $smallest = filesize($path);
        $isWebp   = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp';

        try {
            // a WebP upload is already WebP — it only gets an AVIF copy
            if (!$isWebp && function_exists('imagewebp')) {
                $size = self::write($path . '.webp', fn ($target) => imagewebp($image, $target, self::WEBP_QUALITY), $smallest);
                if ($size) {
                    $written['webp'] = $size;
                    $smallest = $size;
                }
            }

            if (function_exists('imageavif')) {
                // speed 6: default trade-off; slower settings barely shrink product photos further
                $size = self::write($path . '.avif', fn ($target) => imageavif($image, $target, self::AVIF_QUALITY, 6), $smallest);
                if ($size) {
                    $written['avif'] = $size;
                }
            }
        } finally {
            imagedestroy($image);
        }

        return $written;
    }

    /** Remove the optimized copies of a file (call when the original is deleted or replaced). */
    public static function deleteSiblings(string $path): void
    {
        foreach (['.webp', '.avif'] as $suffix) {
            if (is_file($path . $suffix)) {
                @unlink($path . $suffix);
            }
        }
    }

    /**
     * The best copy the current browser can show, as a path relative to public/.
     * AVIF only when the request says the browser accepts it; WebP otherwise
     * (supported by every browser in use); the original when no copy exists.
     */
    public static function best(string $relativePath): string
    {
        $absolute = public_path($relativePath);

        if (self::browserAcceptsAvif() && is_file($absolute . '.avif')) {
            return $relativePath . '.avif';
        }

        if (is_file($absolute . '.webp')) {
            return $relativePath . '.webp';
        }

        return $relativePath;
    }

    private static function browserAcceptsAvif(): bool
    {
        return str_contains((string) request()->header('Accept', ''), 'image/avif');
    }

    /** Load a file into a true-colour GD image with its transparency intact. */
    private static function decode(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) {
            return null;
        }

        // GD holds the whole picture uncompressed; skip anything that would not fit in memory
        $needed = $info[0] * $info[1] * 5;
        $limit  = self::memoryLimit();
        if ($limit > 0 && memory_get_usage() + $needed > $limit) {
            return null;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default        => false,
        };

        if (!$image) {
            return null;
        }

        if (!imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /** Encode to a temp file, keep it only if it beats $mustBeat bytes. Returns the size kept, or 0. */
    private static function write(string $target, callable $encode, int $mustBeat): int
    {
        $temp = $target . '.tmp';

        try {
            $ok = @$encode($temp);
        } catch (\Throwable $e) {
            $ok = false;
        }

        $size = ($ok && is_file($temp)) ? filesize($temp) : 0;

        if ($size > 0 && $size < $mustBeat) {
            rename($temp, $target);
            return $size;
        }

        @unlink($temp);
        @unlink($target); // a stale copy from an earlier, different upload must not be served

        return 0;
    }

    private static function memoryLimit(): int
    {
        $raw = trim((string) ini_get('memory_limit'));
        if ($raw === '' || $raw === '-1') {
            return 0;
        }

        $value = (int) $raw;

        return match (strtolower(substr($raw, -1))) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
