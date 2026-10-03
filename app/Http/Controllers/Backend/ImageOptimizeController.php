<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

/**
 * One-time repair for images uploaded before compression existed: product
 * images without a thumb_products/ copy, multi-MB banners served as-is, and
 * photographic PNGs (5-10x the weight of webp at the same quality). Runs in
 * small batches behind an auto-refresh so shared-hosting time limits are
 * never hit; originals of every rewritten banner are kept under
 * storage/app/image_backup/. Safe to re-run — finished work is skipped.
 *
 * Shared-hosting hardening: a ~18s wall-clock budget per request (hosts often
 * ignore set_time_limit), a decode-size check before every Image::make (a
 * 6000x4000 photo needs ~120MB — exceeding memory_limit is a FATAL, not an
 * exception), and a top-level catch that prints the real error instead of a
 * blank HTTP 500.
 */
class ImageOptimizeController extends Controller
{
    const THUMB_BATCH   = 10;
    const BANNER_BATCH  = 4;
    const CONVERT_BATCH = 3;
    const THUMB_SIZE    = 500;
    const QUALITY       = 80;
    const TIME_BUDGET   = 18; // seconds per request

    // photographic PNGs at/above this size are converted to webp
    const PNG_MIN       = 150 * 1024;
    const THUMB_PNG_MIN = 120 * 1024;

    // dirs scanned for oversized banners: [public subdir, max width, min bytes to bother]
    const BANNER_DIRS = [
        ['homeimages',                    1600, 250 * 1024],
        ['sliders',                       1600, 250 * 1024],
        ['mobile_sliders',                1000, 200 * 1024],
        ['uploads/home_categories/cover', 1400, 250 * 1024],
    ];

    // which DB columns hold the filenames of each banner dir — a png→webp
    // conversion renames the file, so every referencing row must follow
    const DIR_DB = [
        'sliders'                        => [['sliders', ['image']]],
        'mobile_sliders'                 => [['sliders', ['mobile_image']]],
        'homeimages'                     => [['home_section_images', ['image', 'mobile_image', 'left_image_1', 'left_image_2', 'left_image_3', 'left_image_4', 'right_image']]],
        'uploads/home_categories/cover'  => [['home_categories', ['cover_image']]],
    ];

    const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private $deadline;

    public function run()
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);
        $this->deadline = microtime(true) + self::TIME_BUDGET;

        try {
            $converted   = $this->convertPngBanners();
            $thumbWebps  = $this->webpThumbSiblings();
            $doneThumbs  = $this->makeMissingThumbs();
            $doneBanners = $this->compressOversizedBanners();

            $r = $this->countPending();
            $finished = array_sum($r) === 0;
        } catch (\Throwable $e) {
            return response(
                '<!DOCTYPE html><html><head><title>Image Optimize — Error</title></head><body '
                . 'style="font-family:sans-serif;max-width:640px;margin:60px auto;color:#1e293b">'
                . '<h2 style="color:#dc2626">Optimize error</h2>'
                . '<p>Please send this message to your developer:</p>'
                . '<pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;white-space:pre-wrap">'
                . e($e->getMessage()) . "\n\n" . e($e->getFile()) . ':' . $e->getLine()
                . '</pre><p><a href="">Try again</a></p></body></html>',
                200
            );
        }

        $html = '<!DOCTYPE html><html><head><title>Image Optimize</title>';
        if (!$finished) {
            $html .= '<meta http-equiv="refresh" content="1">';
        }
        $html .= '<style>body{font-family:sans-serif;max-width:560px;margin:60px auto;padding:0 16px;color:#1e293b}
            .card{border:1px solid #e2e8f0;border-radius:12px;padding:24px}
            .ok{color:#059669;font-weight:700}.run{color:#2563eb;font-weight:700}</style></head><body><div class="card">';
        $html .= '<h2>' . ($finished ? '<span class="ok">&#10004; All images optimized!</span>' : '<span class="run">Optimizing&hellip; (page refreshes itself)</span>') . '</h2>';
        $html .= '<p>This pass: ' . $doneThumbs . ' thumbnails, ' . $doneBanners . ' banners, ' . ($converted + $thumbWebps) . ' webp conversions.</p>';
        $html .= '<p>Remaining: ' . $r['thumbs'] . ' thumbnails, ' . $r['banners'] . ' banners, ' . $r['conversions'] . ' conversions.</p>';
        if ($finished) {
            $html .= '<p>Rewritten banner originals were backed up to <code>storage/app/image_backup/</code>. You can close this page.</p>';
        }
        $html .= '</div></body></html>';

        return response($html);
    }

    private function timeUp()
    {
        return microtime(true) > $this->deadline;
    }

    private function webpAvailable()
    {
        return function_exists('imagewebp');
    }

    private function memoryLimitBytes()
    {
        $v = ini_get('memory_limit');
        if ($v === false || (int) $v === -1) return 0; // unlimited
        $n = (int) $v;
        switch (strtolower(substr(trim($v), -1))) {
            case 'g': return $n * 1073741824;
            case 'm': return $n * 1048576;
            case 'k': return $n * 1024;
            default:  return $n;
        }
    }

    // decoding to a GD bitmap needs ~5 bytes/pixel; running out is a FATAL
    // error no try/catch can save, so refuse files that would not fit
    private function canDecode($path)
    {
        $info = @getimagesize($path);
        if (!$info || empty($info[0])) return true; // let Image::make fail catchably
        $needed = $info[0] * $info[1] * 5 + 16 * 1048576;
        $limit  = $this->memoryLimitBytes();
        if ($limit <= 0) return true;
        return (memory_get_usage(true) + $needed) < ($limit * 0.8);
    }

    private function imageFiles($dir)
    {
        if (!is_dir($dir)) return [];
        $out = [];
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            if (!is_file($dir . '/' . $f)) continue;
            if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), self::EXTENSIONS)) {
                $out[] = $f;
            }
        }
        return $out;
    }

    private function processedLogPath()
    {
        return storage_path('app/image_optimize_done.json');
    }

    private function processedLog()
    {
        $p = $this->processedLogPath();
        $data = is_file($p) ? json_decode(file_get_contents($p), true) : null;
        return is_array($data) ? $data : [];
    }

    private function saveLog($log)
    {
        @file_put_contents($this->processedLogPath(), json_encode($log));
    }

    // returns true only when a backup copy is confirmed on disk
    private function backupOriginal($sub, $f)
    {
        $backupDir = storage_path('app/image_backup/' . $sub);
        if (!is_dir($backupDir)) @mkdir($backupDir, 0755, true);
        $dest = $backupDir . '/' . $f;
        if (!file_exists($dest)) @copy(public_path($sub) . '/' . $f, $dest);
        return file_exists($dest);
    }

    /* ---------- pass 1: photographic PNG banners -> webp (+DB rename) ---------- */

    private function pendingPngConversions($log = null)
    {
        if (!$this->webpAvailable()) return [];
        $log = $log ?? $this->processedLog();
        $pending = [];
        foreach (self::BANNER_DIRS as [$sub, $maxW, $minBytes]) {
            $dir = public_path($sub);
            foreach ($this->imageFiles($dir) as $f) {
                if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) !== 'png') continue;
                if (isset($log['webp:' . $sub . '/' . $f])) continue;
                if (filesize($dir . '/' . $f) < self::PNG_MIN) continue;
                $pending[] = [$sub, $f, $maxW];
            }
        }
        return $pending;
    }

    private function convertPngBanners()
    {
        $log = $this->processedLog();
        $done = 0;
        foreach (array_slice($this->pendingPngConversions($log), 0, self::CONVERT_BATCH) as [$sub, $f, $maxW]) {
            if ($this->timeUp()) break;
            $dir = public_path($sub);
            $key = 'webp:' . $sub . '/' . $f;
            try {
                if (!$this->canDecode($dir . '/' . $f)) {
                    $log[$key] = ['at' => date('Y-m-d H:i:s'), 'error' => 'image too large for server memory'];
                    $done++;
                    continue;
                }
                if (!$this->backupOriginal($sub, $f)) {
                    $log[$key] = ['at' => date('Y-m-d H:i:s'), 'error' => 'backup copy failed, file left untouched'];
                    $done++;
                    continue;
                }

                $newName = pathinfo($f, PATHINFO_FILENAME) . '.webp';
                if (file_exists($dir . '/' . $newName)) {
                    $newName = pathinfo($f, PATHINFO_FILENAME) . '_w.webp';
                }

                $img = Image::make($dir . '/' . $f);
                if ($img->width() > $maxW) {
                    $img->resize($maxW, null, function ($c) {
                        $c->aspectRatio();
                        $c->upsize();
                    });
                }
                $img->save($dir . '/' . $newName, self::QUALITY);
                $img->destroy();

                foreach (self::DIR_DB[$sub] ?? [] as [$table, $columns]) {
                    foreach ($columns as $col) {
                        DB::table($table)->where($col, $f)->update([$col => $newName]);
                    }
                }

                @unlink($dir . '/' . $f);
                $log[$key] = ['at' => date('Y-m-d H:i:s'), 'to' => $newName, 'bytes' => filesize($dir . '/' . $newName)];
            } catch (\Throwable $e) {
                $log[$key] = ['at' => date('Y-m-d H:i:s'), 'error' => substr($e->getMessage(), 0, 120)];
            }
            $done++;
        }
        $this->saveLog($log);
        return $done;
    }

    /* ---------- pass 2: webp siblings for heavy PNG product thumbs ---------- */

    private function pendingThumbWebps()
    {
        if (!$this->webpAvailable()) return [];
        $thumbDir = public_path('thumb_products');
        $pending = [];
        foreach ($this->imageFiles($thumbDir) as $f) {
            if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) !== 'png') continue;
            if (file_exists($thumbDir . '/' . $f . '.webp')) continue;
            if (filesize($thumbDir . '/' . $f) < self::THUMB_PNG_MIN) continue;
            $pending[] = $f;
        }
        return $pending;
    }

    private function webpThumbSiblings()
    {
        $thumbDir = public_path('thumb_products');
        $done = 0;
        foreach (array_slice($this->pendingThumbWebps(), 0, self::THUMB_BATCH) as $f) {
            if ($this->timeUp()) break;
            try {
                // sibling keeps the full original name so getThumbImage() can
                // find it from the product's stored filename without any DB change
                if ($this->canDecode($thumbDir . '/' . $f)) {
                    $img = Image::make($thumbDir . '/' . $f);
                    $img->save($thumbDir . '/' . $f . '.webp', self::QUALITY);
                    $img->destroy();
                } else {
                    @copy($thumbDir . '/' . $f, $thumbDir . '/' . $f . '.webp');
                }
            } catch (\Throwable $e) {
                @copy($thumbDir . '/' . $f, $thumbDir . '/' . $f . '.webp');
            }
            $done++;
        }
        return $done;
    }

    /* ---------- pass 3: missing product thumbnails ---------- */

    private function pendingThumbs()
    {
        $prodDir  = public_path('products');
        $thumbDir = public_path('thumb_products');
        return array_values(array_filter($this->imageFiles($prodDir), function ($f) use ($thumbDir) {
            return !file_exists($thumbDir . '/' . $f);
        }));
    }

    private function makeMissingThumbs()
    {
        $prodDir  = public_path('products');
        $thumbDir = public_path('thumb_products');
        if (!is_dir($thumbDir)) @mkdir($thumbDir, 0755, true);

        $done = 0;
        foreach (array_slice($this->pendingThumbs(), 0, self::THUMB_BATCH) as $f) {
            if ($this->timeUp()) break;
            try {
                if (!$this->canDecode($prodDir . '/' . $f)) {
                    // full image as stand-in: no smaller, but never fatal and never retried
                    @copy($prodDir . '/' . $f, $thumbDir . '/' . $f);
                    $done++;
                    continue;
                }
                $img = Image::make($prodDir . '/' . $f);
                $img->resize(self::THUMB_SIZE, self::THUMB_SIZE, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });
                $img->save($thumbDir . '/' . $f, self::QUALITY);
                $img->destroy();
            } catch (\Throwable $e) {
                // undecodable file: copy as-is so it is never retried forever
                @copy($prodDir . '/' . $f, $thumbDir . '/' . $f);
            }
            $done++;
        }
        return $done;
    }

    /* ---------- pass 4: recompress remaining oversized banners in place ---------- */

    private function pendingBanners($log = null)
    {
        $log = $log ?? $this->processedLog();
        $webp = $this->webpAvailable();
        $pending = [];
        foreach (self::BANNER_DIRS as [$sub, $maxW, $minBytes]) {
            $dir = public_path($sub);
            foreach ($this->imageFiles($dir) as $f) {
                $isPng = strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'png';
                // big PNGs belong to the conversion pass, not in-place recompression
                if ($isPng && $webp && filesize($dir . '/' . $f) >= self::PNG_MIN) continue;
                if (isset($log[$sub . '/' . $f])) continue;
                if (filesize($dir . '/' . $f) < $minBytes) continue;
                $pending[] = [$sub, $f, $maxW];
            }
        }
        return $pending;
    }

    private function compressOversizedBanners()
    {
        $log = $this->processedLog();
        $done = 0;
        foreach (array_slice($this->pendingBanners($log), 0, self::BANNER_BATCH) as [$sub, $f, $maxW]) {
            if ($this->timeUp()) break;
            $path = public_path($sub) . '/' . $f;
            $key  = $sub . '/' . $f;
            try {
                if (!$this->canDecode($path)) {
                    $log[$key] = ['at' => date('Y-m-d H:i:s'), 'error' => 'image too large for server memory'];
                    $done++;
                    continue;
                }
                if (!$this->backupOriginal($sub, $f)) {
                    $log[$key] = ['at' => date('Y-m-d H:i:s'), 'error' => 'backup copy failed, file left untouched'];
                    $done++;
                    continue;
                }

                $img = Image::make($path);
                if ($img->width() > $maxW) {
                    $img->resize($maxW, null, function ($c) {
                        $c->aspectRatio();
                        $c->upsize();
                    });
                }
                $img->save($path, self::QUALITY);
                $img->destroy();
                $log[$key] = ['at' => date('Y-m-d H:i:s'), 'bytes' => filesize($path)];
            } catch (\Throwable $e) {
                $log[$key] = ['at' => date('Y-m-d H:i:s'), 'error' => substr($e->getMessage(), 0, 120)];
            }
            $done++;
        }

        $this->saveLog($log);
        return $done;
    }

    private function countPending()
    {
        return [
            'thumbs'      => count($this->pendingThumbs()),
            'banners'     => count($this->pendingBanners()),
            'conversions' => count($this->pendingPngConversions()) + count($this->pendingThumbWebps()),
        ];
    }
}
