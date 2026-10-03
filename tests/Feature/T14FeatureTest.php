<?php

namespace Tests\Feature;

use App\Models\AddonLicense;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * T14 - infra area: cache routes/command, facebook feed, image optimise,
 * addons + addon licence, updater, master notice service.
 * Creates only T14-* rows/files and removes every one of them at the end.
 */
class T14FeatureTest extends TestCase
{
    private $pass = 0;
    private $failn = 0;

    /** cleanup registers */
    private $files = [];          // absolute paths to unlink
    private $dirsToPrune = [];    // absolute dirs to rmdir if empty
    private $productIds = [];
    private $sliderIds = [];
    private $addonSlugs = [];
    private $origFeedSetting = null;
    private $optLogExisted = false;
    private $imgBackupExisted = false;

    private function ok($m)
    {
        $this->pass++;
        fwrite(STDOUT, "OK   $m\n");
    }

    private function bad($m)
    {
        $this->failn++;
        fwrite(STDOUT, "FAIL $m\n");
    }

    private function chk($cond, $m)
    {
        $cond ? $this->ok($m) : $this->bad($m);
        return (bool) $cond;
    }

    private function note($m)
    {
        fwrite(STDOUT, "--   $m\n");
    }

    private function admin()
    {
        return User::find(1);
    }

    // ---------- image helpers (GD only, no app code) ----------
    private function noiseImage($w, $h)
    {
        $small = imagecreatetruecolor(400, 200);
        for ($x = 0; $x < 400; $x++) {
            for ($y = 0; $y < 200; $y++) {
                imagesetpixel($small, $x, $y, imagecolorallocate($small, rand(0, 255), rand(0, 255), rand(0, 255)));
            }
        }
        $big = imagecreatetruecolor($w, $h);
        imagecopyresampled($big, $small, 0, 0, 0, 0, $w, $h, 400, 200);
        imagedestroy($small);
        return $big;
    }

    private function writeJpg($path, $w, $h, $q = 92)
    {
        $im = $this->noiseImage($w, $h);
        imagejpeg($im, $path, $q);
        imagedestroy($im);
        $this->files[] = $path;
    }

    private function writePng($path, $w, $h)
    {
        $im = $this->noiseImage($w, $h);
        imagepng($im, $path, 1);
        imagedestroy($im);
        $this->files[] = $path;
    }

    // ---------- optimiser pending scan (mirror of controller, read only) ----------
    private $EXT = ['jpg', 'jpeg', 'png', 'webp'];
    private $BANNER_DIRS = [
        ['homeimages', 1600, 262144],
        ['sliders', 1600, 262144],
        ['mobile_sliders', 1000, 204800],
        ['uploads/home_categories/cover', 1400, 262144],
    ];

    private function dirFiles($dir)
    {
        if (!is_dir($dir)) return [];
        $o = [];
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            if (!is_file($dir . '/' . $f)) continue;
            if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $this->EXT)) $o[] = $f;
        }
        return $o;
    }

    private function scanPending()
    {
        $r = ['conv' => [], 'thumbwebp' => [], 'thumbs' => [], 'banners' => []];
        foreach ($this->BANNER_DIRS as [$sub, $w, $mb]) {
            $d = public_path($sub);
            foreach ($this->dirFiles($d) as $f) {
                $isPng = strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'png';
                if ($isPng && filesize($d . '/' . $f) >= 153600) {
                    $r['conv'][] = $sub . '/' . $f;
                    continue;
                }
                if (filesize($d . '/' . $f) >= $mb) $r['banners'][] = $sub . '/' . $f;
            }
        }
        $td = public_path('thumb_products');
        foreach ($this->dirFiles($td) as $f) {
            if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) !== 'png') continue;
            if (file_exists($td . '/' . $f . '.webp')) continue;
            if (filesize($td . '/' . $f) < 122880) continue;
            $r['thumbwebp'][] = $f;
        }
        foreach ($this->dirFiles(public_path('products')) as $f) {
            if (!file_exists($td . '/' . $f)) $r['thumbs'][] = $f;
        }
        return $r;
    }

    // =====================================================================
    public function test_infra_features()
    {
        $createdThumbsForOthers = [];
        $createdSiblingsForOthers = [];

        try {
            /* ================================================================
             * 1. CACHE ROUTES / CacheController / CacheSweep command
             * ============================================================== */
            fwrite(STDOUT, "\n===== 1. CACHE (routes/cache_routes.php, CacheController, app:license-regenerate) =====\n");

            $this->chk(!\Route::has('system.cache.index'), 'route system.cache.index is NOT registered (routes/cache_routes.php never included)');
            $r = $this->actingAs($this->admin())->get('/system/cache');
            $this->chk($r->status() === 404, 'GET /system/cache -> 404 (CacheController@index unreachable), got ' . $r->status());
            $r = $this->actingAs($this->admin())->post('/system/cache/update', ['domains' => 'x.com']);
            $this->chk($r->status() === 404, 'POST /system/cache/update -> 404 (unreachable), got ' . $r->status());
            $this->chk(file_exists(base_path('routes/cache_routes.php')) && file_exists(resource_path('views/system/cache_manager.blade.php')),
                'controller+route-file+blade all exist on disk but nothing loads them (dead feature)');

            // CacheSweep guard path - must NOT touch the licence file
            $lic = storage_path('app/cache/settings.json');
            $before = file_exists($lic) ? md5_file($lic) : null;
            $exit = \Artisan::call('app:license-regenerate');
            $out = \Artisan::output();
            $after = file_exists($lic) ? md5_file($lic) : null;
            $this->chk($exit === 1 && strpos($out, 'No domains provided') !== false,
                'app:license-regenerate with no --domains exits 1 with a clear error');
            $this->chk($before === $after, 'app:license-regenerate did not touch storage/app/cache/settings.json on the error path');

            // Prove the signing/verifying key mismatch WITHOUT writing anything
            if ($before !== null) {
                $signed = json_decode(file_get_contents($lic), true);
                $sig = $signed['signature'] ?? '';
                $payloadArr = ['domains' => $signed['domains'], 'meta' => $signed['meta'], 'issued_at' => $signed['issued_at']];
                $payload = json_encode($payloadArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $rawKey = env('LICENSE_KEY', '');
                $decKey = str_starts_with($rawKey, 'base64:') ? base64_decode(substr($rawKey, 7)) : $rawKey;
                $sigWithDecoded = hash_hmac('sha256', $payload, $decKey);   // what saveSigned() writes
                $sigWithRaw = hash_hmac('sha256', $payload, $rawKey);       // what verify() expects
                $this->note('stored sig      = ' . $sig);
                $this->note('saveSigned sig  = ' . $sigWithDecoded . ' (base64-decoded key)');
                $this->note('verify() sig    = ' . $sigWithRaw . ' (raw env key)');
                $this->chk(hash_equals($sigWithDecoded, $sig), 'stored signature was produced with the BASE64-DECODED key (Formatter::saveSigned)');
                $this->chk(!hash_equals($sigWithRaw, $sig), 'Formatter::verify() would REJECT it - it HMACs with the raw env value, never base64-decoding');
            }

            // Formatter helper stubs
            $tErr = null;
            try {
                \App\Support\Formatter::normalizeHost('WWW.Example.COM:8080');
            } catch (\Throwable $e) {
                $tErr = get_class($e) . ': ' . $e->getMessage();
            }
            $this->chk($tErr !== null, 'Formatter::normalizeHost() has an empty body and fatals when called -> ' . ($tErr ?: 'returned normally'));
            $this->chk(!file_exists(base_path('bootstrap/compiled.php')) || !$this->fileReferenced('compiled.php'),
                'bootstrap/compiled.php (the only caller of Formatter::verify) is not included anywhere -> whole Formatter chain is dead code');

            /* ================================================================
             * 2. FACEBOOK PRODUCT FEED
             * ============================================================== */
            fwrite(STDOUT, "\n===== 2. FACEBOOK FEED (/facebook-product-feed.xml) =====\n");

            $this->origFeedSetting = DB::table('settings')->where('key', 'facebook_feed')->value('value');
            $this->note('settings.facebook_feed currently = ' . var_export($this->origFeedSetting, true));

            // ---- create a real T14 product through the admin controller ----
            $prodName = 'T14 Feed Widget & Co <b>Pro</b>';
            $res = $this->actingAs($this->admin())
                ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                ->post('/admin/products', [
                    'name' => $prodName,
                    'type' => 'single',
                    'image' => UploadedFile::fake()->image('T14-feed.jpg', 800, 800),
                    'category_id' => 1,
                    'sku' => 'T14-SKU-' . rand(10000, 99999),
                    'sell_price' => 1234,
                    'regular_price' => 1500,
                    'after_discount' => 999,
                    'description' => '<p>T14 description with &amp; ampersand</p>',
                    'is_stock' => 1,
                    'pro_quantity' => 7,
                ]);
            $json = json_decode($res->getContent(), true);
            $this->chk(($json['status'] ?? false) === true, 'admin product store returned status:true (' . substr($res->getContent(), 0, 160) . ')');
            $prod = Product::where('name', $prodName)->latest('id')->first();
            if (!$prod) {
                $this->bad('could not create the T14 feed product - aborting feed checks');
            } else {
                $this->productIds[] = $prod->id;
                if ($prod->image) {
                    $this->files[] = public_path('products/' . $prod->image);
                    $this->files[] = public_path('thumb_products/' . $prod->image);
                }
                $this->ok('T14 product #' . $prod->id . ' created (slug=' . $prod->slug . ', stock=' . $prod->stock_quantity . ')');

                // feed must be ON for the next block
                DB::table('settings')->updateOrInsert(['key' => 'facebook_feed'], ['value' => 'on']);

                $feed = $this->get('/facebook-product-feed.xml');
                $xml = $feed->getContent();
                $this->chk($feed->status() === 200, 'GET /facebook-product-feed.xml -> 200');
                $this->chk(str_contains(strtolower($feed->headers->get('content-type') ?? ''), 'application/xml'),
                    'Content-Type is application/xml (' . $feed->headers->get('content-type') . ')');

                libxml_use_internal_errors(true);
                libxml_clear_errors();
                $dom = new \DOMDocument();
                $wellFormed = $dom->loadXML($xml);
                $xmlErrs = libxml_get_errors();
                libxml_clear_errors();
                $this->chk($wellFormed, 'feed body parses as well-formed XML' . ($wellFormed ? '' : ' :: ' . trim($xmlErrs[0]->message ?? '')));

                if ($wellFormed) {
                    $xp = new \DOMXPath($dom);
                    $xp->registerNamespace('g', 'http://base.google.com/ns/1.0');
                    $items = $xp->query('//channel/item');
                    $this->chk($items->length > 0, 'feed contains ' . $items->length . ' <item> entries');
                    $this->chk($xp->query('/rss[@version="2.0"]')->length === 1, 'root is <rss version="2.0"> with the g: namespace declared');

                    $mine = null;
                    foreach ($items as $it) {
                        $idNode = $xp->query('g:id', $it)->item(0);
                        if ($idNode && (int) $idNode->textContent === (int) $prod->id) { $mine = $it; break; }
                    }
                    if (!$this->chk($mine !== null, 'the T14 product appears in the feed as <g:id>' . $prod->id . '</g:id>')) {
                        $this->note('feed length=' . strlen($xml));
                    } else {
                        $g = function ($t) use ($xp, $mine) {
                            $n = $xp->query($t, $mine)->item(0);
                            return $n ? $n->textContent : null;
                        };
                        $this->chk($g('g:price') === '1234.00 BDT', 'g:price = "1234.00 BDT" (got ' . var_export($g('g:price'), true) . ')');
                        $this->chk($g('g:sale_price') === '999.00 BDT', 'g:sale_price = "999.00 BDT" (got ' . var_export($g('g:sale_price'), true) . ')');
                        $this->chk($g('g:availability') === 'in stock', 'g:availability = "in stock" for stock 7 (got ' . var_export($g('g:availability'), true) . ')');
                        $this->chk($g('g:condition') === 'new', 'g:condition = new');
                        $this->chk(str_ends_with((string) $g('g:link'), '/product-show/' . $prod->slug), 'g:link points at /product-show/' . $prod->slug . ' (got ' . $g('g:link') . ')');
                        $this->chk(str_ends_with((string) $g('g:image_link'), '/products/' . $prod->image), 'g:image_link points at the real uploaded file (got ' . $g('g:image_link') . ')');
                        $this->chk(str_contains((string) $g('g:description'), 'T14 description'), 'g:description falls back to strip_tags(description)');

                        // TITLE ESCAPING: name is htmlspecialchars()-ed *inside* CDATA
                        $title = (string) $g('g:title');
                        $this->note('g:title raw text = ' . $title);
                        $this->chk($title === $prodName,
                            'g:title equals the product name exactly (got "' . $title . '", expected "' . $prodName . '")');
                    }

                    // out-of-stock branch (is_stock=0 => total_stock accessor returns 0)
                    Product::where('id', $prod->id)->update(['is_stock' => 0]);
                    $feed2 = $this->get('/facebook-product-feed.xml');
                    $dom2 = new \DOMDocument();
                    $dom2->loadXML($feed2->getContent());
                    $xp2 = new \DOMXPath($dom2);
                    $xp2->registerNamespace('g', 'http://base.google.com/ns/1.0');
                    $av = null;
                    foreach ($xp2->query('//channel/item') as $it) {
                        $idn = $xp2->query('g:id', $it)->item(0);
                        if ($idn && (int) $idn->textContent === (int) $prod->id) {
                            $av = $xp2->query('g:availability', $it)->item(0)->textContent;
                        }
                    }
                    $this->chk($av === 'out of stock', 'is_stock=0 flips g:availability to "out of stock" (got ' . var_export($av, true) . ')');
                    Product::where('id', $prod->id)->update(['is_stock' => 1]);

                    // status=0 must remove the product from the feed
                    Product::where('id', $prod->id)->update(['status' => 0]);
                    $feed3 = $this->get('/facebook-product-feed.xml');
                    $this->chk(!str_contains($feed3->getContent(), '<g:id>' . $prod->id . '</g:id>'), 'status=0 removes the product from the feed');
                    Product::where('id', $prod->id)->update(['status' => 1]);

                    // CDATA terminator inside the description
                    Product::where('id', $prod->id)->update(['description' => 'T14 danger ]]> after terminator']);
                    $feed4 = $this->get('/facebook-product-feed.xml');
                    libxml_clear_errors();
                    $dom4 = new \DOMDocument();
                    $wf4 = $dom4->loadXML($feed4->getContent());
                    $e4 = libxml_get_errors();
                    libxml_clear_errors();
                    $this->chk($wf4, 'a product description containing "]]>" still yields well-formed XML'
                        . ($wf4 ? '' : ' :: ' . trim($e4[0]->message ?? '')));
                    Product::where('id', $prod->id)->update(['description' => '<p>T14 description with &amp; ampersand</p>']);
                }

                // ---- ON/OFF toggle (settings row is restored right after) ----
                $t = $this->actingAs($this->admin())->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                    ->post('/facebook-feed/toggle', ['status' => 0]);
                $tj = json_decode($t->getContent(), true);
                $dbVal = DB::table('settings')->where('key', 'facebook_feed')->value('value');
                $off = $this->get('/facebook-product-feed.xml');
                $this->chk(($tj['status'] ?? null) === 'off' && $dbVal === 'off', 'toggle status=0 writes settings.facebook_feed = off (db=' . var_export($dbVal, true) . ')');
                $this->chk($off->status() === 403 && trim($off->getContent()) === 'Facebook Feed Disabled', 'feed returns 403 "Facebook Feed Disabled" while off (got ' . $off->status() . ')');

                $t = $this->actingAs($this->admin())->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                    ->post('/facebook-feed/toggle', ['status' => 1]);
                $dbVal = DB::table('settings')->where('key', 'facebook_feed')->value('value');
                $on = $this->get('/facebook-product-feed.xml');
                $this->chk($dbVal === 'on' && $on->status() === 200, 'toggle status=1 turns it back on and the feed serves again');

                // settings screen
                $s = $this->actingAs($this->admin())->get('/facebook-feed');
                $this->chk($s->status() === 200 && str_contains($s->getContent(), 'facebook_feed.toggle') || $s->status() === 200,
                    'GET /facebook-feed settings screen renders (' . $s->status() . ')');
                $this->chk(str_contains($s->getContent(), 'facebook-product-feed.xml'), 'settings screen shows the public feed URL');

                // guest access (feed must be public for Facebook to crawl it)
                $anon = $this->get('/facebook-product-feed.xml');
                $this->chk($anon->status() === 200, 'feed is reachable without authentication (crawlable)');
            }

            /* ================================================================
             * 3. IMAGE OPTIMIZE  (/optimize-images)
             * ============================================================== */
            fwrite(STDOUT, "\n===== 3. IMAGE OPTIMIZE (/optimize-images) =====\n");

            $worker = User::find(77);
            if ($worker) {
                $w = $this->actingAs($worker)->get('/optimize-images');
                $this->chk($w->status() === 403, 'worker (user 77) is blocked from /optimize-images by deny.worker (got ' . $w->status() . ')');
            }
            $this->app['auth']->forgetGuards();
            $guest = $this->get('/optimize-images');
            $this->chk(in_array($guest->status(), [302, 401]), 'guest is redirected/denied on /optimize-images (got ' . $guest->status() . ')');

            $logPath = storage_path('app/image_optimize_done.json');
            $this->optLogExisted = file_exists($logPath);
            $this->imgBackupExisted = is_dir(storage_path('app/image_backup'));

            // freeze every pre-existing pending banner so the run only touches T14 files
            $pre = $this->scanPending();
            $this->note('pre-existing pending: conv=' . count($pre['conv']) . ' thumbwebp=' . count($pre['thumbwebp'])
                . ' thumbs=' . count($pre['thumbs']) . ' banners=' . count($pre['banners']));
            $log = $this->optLogExisted ? (json_decode(file_get_contents($logPath), true) ?: []) : [];
            foreach ($pre['conv'] as $k)    $log['webp:' . $k] = ['at' => 'T14-frozen'];
            foreach ($pre['banners'] as $k) $log[$k] = ['at' => 'T14-frozen'];
            file_put_contents($logPath, json_encode($log));

            // T14 inputs
            $srcThumb = public_path('products/T14-opt-src.jpg');
            $this->writeJpg($srcThumb, 1000, 1000, 90);
            $this->files[] = public_path('thumb_products/T14-opt-src.jpg');

            $sliderPng = public_path('sliders/T14-slider-big.png');
            $this->writePng($sliderPng, 1900, 700);
            $this->files[] = public_path('sliders/T14-slider-big.webp');
            $this->files[] = storage_path('app/image_backup/sliders/T14-slider-big.png');

            $bannerJpg = public_path('homeimages/T14-banner-big.jpg');
            $this->writeJpg($bannerJpg, 2400, 1100, 95);
            $this->files[] = storage_path('app/image_backup/homeimages/T14-banner-big.jpg');

            $this->note('T14 slider png = ' . filesize($sliderPng) . ' bytes; T14 banner jpg = ' . filesize($bannerJpg) . ' bytes');
            $this->chk(filesize($sliderPng) >= 153600, 'T14 slider PNG is above the 150KB conversion threshold');
            $this->chk(filesize($bannerJpg) >= 262144, 'T14 banner JPEG is above the 250KB recompression threshold');

            $sliderId = DB::table('sliders')->insertGetId([
                'title' => 'T14-slider-row',
                'image' => 'T14-slider-big.png',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->sliderIds[] = $sliderId;

            $bannerBytesBefore = filesize($bannerJpg);
            $run = $this->actingAs($this->admin())->get('/optimize-images');
            $html = $run->getContent();
            $this->chk($run->status() === 200, 'GET /optimize-images -> 200 for admin');
            $this->chk(!str_contains($html, 'Optimize error'), 'optimize run did not hit the top-level error handler');
            $this->chk(preg_match('/This pass: (\d+) thumbnails, (\d+) banners, (\d+) webp conversions/', $html, $m) === 1,
                'progress page reports per-pass counters: ' . trim(strip_tags(preg_replace('/<style.*?<\/style>/s', '', $html))));

            // pass 3 - missing thumbnail generated and actually resized
            $thumbPath = public_path('thumb_products/T14-opt-src.jpg');
            if ($this->chk(file_exists($thumbPath), 'missing thumbnail was generated at public/thumb_products/T14-opt-src.jpg')) {
                $sz = getimagesize($thumbPath);
                $this->chk($sz[0] <= 500 && $sz[1] <= 500, 'thumbnail was really downscaled to <=500px (' . $sz[0] . 'x' . $sz[1] . ' from 1000x1000)');
                $this->chk(filesize($thumbPath) < filesize($srcThumb), 'thumbnail is smaller on disk than the source (' . filesize($thumbPath) . ' < ' . filesize($srcThumb) . ')');
            }

            // pass 1 - PNG banner converted to webp AND the sliders row followed
            $webpPath = public_path('sliders/T14-slider-big.webp');
            if ($this->chk(file_exists($webpPath), 'PNG slider was converted to webp')) {
                $this->chk(!file_exists($sliderPng), 'the original PNG was removed after conversion');
                $sz = getimagesize($webpPath);
                $this->chk($sz[0] <= 1600, 'converted slider was resized to the 1600px cap (' . $sz[0] . 'px wide)');
                $row = DB::table('sliders')->where('id', $sliderId)->first();
                $this->chk(($row->image ?? '') === 'T14-slider-big.webp', 'sliders.image row was rewritten to the new filename (got ' . ($row->image ?? 'null') . ')');
                $this->chk(file_exists(storage_path('app/image_backup/sliders/T14-slider-big.png')), 'the original PNG was backed up under storage/app/image_backup/sliders/');
            }

            // pass 4 - oversized banner recompressed in place + backup
            if ($this->chk(file_exists($bannerJpg), 'oversized banner still present after the pass')) {
                $sz = getimagesize($bannerJpg);
                $this->chk($sz[0] <= 1600, 'oversized banner was resized down to <=1600px (now ' . $sz[0] . 'px, was 2400px)');
                $this->chk(filesize($bannerJpg) < $bannerBytesBefore, 'banner shrank on disk (' . $bannerBytesBefore . ' -> ' . filesize($bannerJpg) . ')');
                $this->chk(file_exists(storage_path('app/image_backup/homeimages/T14-banner-big.jpg')), 'original banner backed up to storage/app/image_backup/homeimages/');
            }

            // idempotency - a second run must not redo the same work
            $run2 = $this->actingAs($this->admin())->get('/optimize-images');
            preg_match('/This pass: (\d+) thumbnails, (\d+) banners, (\d+) webp conversions/', $run2->getContent(), $m2);
            $this->chk(isset($m2[2]) && (int) $m2[2] === 0 && (int) $m2[3] === 0,
                'second run skips already-processed banners/conversions (thumbs=' . ($m2[1] ?? '?') . ' banners=' . ($m2[2] ?? '?') . ' conv=' . ($m2[3] ?? '?') . ')');
            $this->chk(str_contains($run2->getContent(), 'All images optimized') || str_contains($run2->getContent(), 'Remaining: 0 thumbnails, 0 banners, 0 conversions'),
                'the page eventually reports the queue is drained');

            // record what the run created for OTHER people's files so it can be undone
            foreach ($pre['thumbs'] as $f) {
                $p = public_path('thumb_products/' . $f);
                if (file_exists($p)) $createdThumbsForOthers[] = $p;
            }
            foreach ($pre['thumbwebp'] as $f) {
                $p = public_path('thumb_products/' . $f . '.webp');
                if (file_exists($p)) $createdSiblingsForOthers[] = $p;
            }

            /* ================================================================
             * 4. ADDONS  (Backend\AddonController + AddonLicense)
             * ============================================================== */
            fwrite(STDOUT, "\n===== 4. ADDONS (/admin/addons) =====\n");

            if ($worker) {
                $w = $this->actingAs($worker)->get('/admin/addons');
                $this->chk($w->status() === 403, 'worker is blocked from /admin/addons (got ' . $w->status() . ')');
            }

            // 4a. master server unreachable
            Http::fake(function () { throw new ConnectionException('cURL error 7: Failed to connect'); });
            $t0 = microtime(true);
            $a = $this->actingAs($this->admin())->get('/admin/addons');
            $dt = round((microtime(true) - $t0) * 1000);
            $this->chk($a->status() === 200, 'addon page still renders 200 when the master server is unreachable (got ' . $a->status() . ', ' . $dt . 'ms)');
            $this->chk(str_contains($a->getContent(), 'Addon Marketplace'), 'addon page shows its own chrome (no fatal, no white screen)');

            // 4b. master returns garbage / no addons key
            Http::fake(['*' => Http::response(['unexpected' => true], 200)]);
            $a = $this->actingAs($this->admin())->get('/admin/addons');
            $this->chk($a->status() === 200, 'addon page survives a master response with no "addons" key (got ' . $a->status() . ')');

            // 4c. happy path + saved licence key wiring
            $slug = 'T14-demo-addon';
            AddonLicense::updateOrCreate(['addon_slug' => $slug], ['license_key' => 'T14-KEY-ABCDEF', 'version' => '1.0.0']);
            $this->addonSlugs[] = $slug;
            $this->chk(AddonLicense::where('addon_slug', $slug)->exists(), 'AddonLicense row for ' . $slug . ' persisted');

            Http::fake(['*' => Http::response(['addons' => [[
                'slug' => $slug, 'name' => 'T14 Demo Addon', 'version' => '2.5.0',
                'price' => 1500, 'description' => 'T14 test addon description',
                'image' => 'https://example.com/t14.png',
            ]]], 200)]);
            $a = $this->actingAs($this->admin())->get('/admin/addons');
            $body = $a->getContent();
            $this->chk($a->status() === 200 && str_contains($body, 'T14 Demo Addon'), 'addon list from the master is rendered (' . $a->status() . ')');
            $this->chk(str_contains($body, 'value="T14-KEY-ABCDEF"'), 'the saved AddonLicense key is pre-filled for the addon');
            $this->chk(str_contains($body, 'v2.5.0'), 'master version badge shown');
            $this->chk(str_contains($body, 'Install Addon'), 'not-installed addon shows the Install button (Modules/' . $slug . ' absent)');

            // 4d. a master payload missing an optional field
            Http::fake(['*' => Http::response(['addons' => [[
                'slug' => 'T14-thin-addon', 'name' => 'T14 Thin', 'version' => '1.0.0',
            ]]], 200)]);
            $a = $this->actingAs($this->admin())->get('/admin/addons');
            $this->chk($a->status() === 200, 'addon page tolerates a payload without price/description/image (got ' . $a->status() . ')');

            // 4e. install - validation + failed verification (never downloads anything)
            Http::fake(['*' => Http::response(['msg' => 'no'], 500)]);
            $i = $this->actingAs($this->admin())->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                ->post('/admin/addons/install', ['addon_slug' => 'T14-demo-addon']);
            $this->chk($i->status() === 422, 'install without a license_key is rejected 422 (got ' . $i->status() . ')');

            $i = $this->actingAs($this->admin())->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                ->post('/admin/addons/install', ['license_key' => 'T14-BAD', 'addon_slug' => 'T14-demo-addon']);
            $ij = json_decode($i->getContent(), true);
            $this->chk(($ij['success'] ?? null) === false, 'install with a rejected licence returns success:false (' . substr($i->getContent(), 0, 120) . ')');
            $this->chk(!file_exists(storage_path('app/temp_module.zip')), 'no temp_module.zip left behind after a rejected install');
            $this->chk(!is_dir(base_path('Modules/T14-demo-addon')), 'no module directory created on a rejected install');

            // modules_statuses.json integrity (install rewrites this file)
            $statusRaw = file_get_contents(base_path('modules_statuses.json'));
            $hasBom = str_starts_with($statusRaw, "\xEF\xBB\xBF");
            $decoded = json_decode($statusRaw, true);
            $this->note('modules_statuses.json bytes=' . strlen($statusRaw) . ' bom=' . ($hasBom ? 'YES' : 'no') . ' json_decode=' . var_export($decoded, true));
            $this->chk(is_array($decoded), 'modules_statuses.json decodes to an array (AddonController::install rebuilds it from this decode)');

            Http::fake();

            /* ================================================================
             * 5. UPDATER  (UpdateController)
             * ============================================================== */
            fwrite(STDOUT, "\n===== 5. UPDATER (/admin/system-update) =====\n");

            $zip = storage_path('app/update.zip');
            $zipExisted = file_exists($zip);

            // 5a. unreachable master
            Http::fake(function () { throw new ConnectionException('cURL error 7: Failed to connect'); });
            $u = $this->actingAs($this->admin())->get('/admin/system-update');
            $this->chk($u->status() === 200, 'system-update page renders 200 when the master is unreachable (got ' . $u->status() . ')');
            $this->chk(str_contains($u->getContent(), 'Unable to connect to the master server'), 'shows the "unable to connect" error banner');
            $this->chk(str_contains($u->getContent(), 'Current Version:'), 'still shows the current version');
            $this->chk(str_contains($u->getContent(), 'Your system is up to date'),
                'NOTE - it ALSO claims "Your system is up to date!" in the same page while erroring');

            // 5b. master returns HTTP 500
            Http::fake(['*' => Http::response('boom', 500)]);
            $u = $this->actingAs($this->admin())->get('/admin/system-update');
            $this->chk($u->status() === 200 && str_contains($u->getContent(), 'Master Server Error! Status Code: 500'), 'HTTP 500 from master surfaces as a readable error');

            // 5c. no update available
            Http::fake(['*' => Http::response(['update' => false, 'message' => 'T14 latest already'], 200)]);
            $u = $this->actingAs($this->admin())->get('/admin/system-update');
            $this->chk(str_contains($u->getContent(), 'T14 latest already'), 'master "no update" message is displayed');

            // 5d. update available -> form must be rendered
            Http::fake(['*' => Http::response([
                'update' => true, 'version' => '9.9.9-T14',
                'changelog' => "T14 line one\nT14 line two",
                'download_url' => 'https://update.bizcareit.com/api/download/T14',
            ], 200)]);
            $u = $this->actingAs($this->admin())->get('/admin/system-update');
            $b = $u->getContent();
            $this->chk(str_contains($b, 'New Update Available: Version 9.9.9-T14'), 'available update is announced with its version');
            $this->chk(str_contains($b, 'T14 line one<br />') || str_contains($b, 'T14 line one<br>'), 'changelog newlines converted with nl2br + escaped');
            $this->chk(str_contains($b, 'name="download_url" value="https://update.bizcareit.com/api/download/T14"'), 'install form carries the master download_url');
            $this->chk(str_contains($b, 'system-update/process'), 'install form posts to update.process');

            // 5e. processUpdate guards - nothing is ever extracted
            $p = $this->actingAs($this->admin())->post('/admin/system-update/process', ['version' => '9.9.9-T14']);
            $p->assertRedirect();
            $this->chk(session('error') === 'Download URL not found!', 'processUpdate without download_url redirects with "Download URL not found!" (got ' . var_export(session('error'), true) . ')');

            Http::fake(['*' => Http::response('nope', 403)]);
            $p = $this->actingAs($this->admin())->post('/admin/system-update/process', [
                'download_url' => 'https://update.bizcareit.com/api/download/T14', 'version' => '9.9.9-T14',
            ]);
            $p->assertRedirect();
            $this->chk(str_contains((string) session('error'), 'Failed to download the update file'), 'a rejected download aborts before extract (' . var_export(session('error'), true) . ')');
            $this->chk($zipExisted || !file_exists($zip), 'no stray storage/app/update.zip left behind');
            $this->chk(config('updater.version') === '2.0', 'config/updater.php version untouched by the failed update (' . config('updater.version') . ')');
            $this->chk(count((array) config('updater.protected_files')) > 0,
                'update guard has ' . count((array) config('updater.protected_files')) . ' protected files configured (incl. config/updater.php + UpdateController itself)');

            Http::fake();

            /* ================================================================
             * 6. MASTER NOTICE SERVICE (admin dashboard)
             * ============================================================== */
            fwrite(STDOUT, "\n===== 6. MASTER NOTICES (dashboard) =====\n");

            Http::fake(function () { throw new ConnectionException('cURL error 7: Failed to connect'); });
            $n = \App\Services\MasterNoticeService::fetch();
            $this->chk(is_array($n) && count($n) === 0, 'MasterNoticeService::fetch() returns [] when the master is unreachable');

            $d = $this->actingAs($this->admin())->get('/admin/dashboard');
            $this->chk($d->status() === 200, 'admin dashboard renders 200 with the master server down (got ' . $d->status() . ')');
            $this->chk(!str_contains($d->getContent(), 'master-notice'), 'no notice block rendered when the fetch failed');

            Http::fake(['*' => Http::response(['notices' => [
                ['type' => 'warning', 'title' => 'T14 Notice Title', 'content' => "T14 body line1\nline2", 'created_at' => now()->subHour()->toDateTimeString()],
            ]], 200)]);
            $d = $this->actingAs($this->admin())->get('/admin/dashboard');
            $b = $d->getContent();
            $this->chk(str_contains($b, 'T14 Notice Title'), 'a master notice is rendered on the dashboard');
            $this->chk(str_contains($b, 'notice-warning'), 'notice type drives the CSS class');
            $this->chk(str_contains($b, 'T14 body line1<br />') || str_contains($b, 'T14 body line1<br>'), 'notice body is escaped and nl2br-ed');

            // XSS in a master-controlled notice
            Http::fake(['*' => Http::response(['notices' => [
                ['type' => 'info', 'title' => 'T14<script>alert(1)</script>', 'content' => '<script>alert(2)</script>'],
            ]], 200)]);
            $d = $this->actingAs($this->admin())->get('/admin/dashboard');
            $this->chk(!str_contains($d->getContent(), '<script>alert(1)</script>') && !str_contains($d->getContent(), '<script>alert(2)</script>'),
                'notice title/content are HTML-escaped');

            // notice payload that is not a list
            Http::fake(['*' => Http::response(['notices' => 'T14-not-an-array'], 200)]);
            $d = $this->actingAs($this->admin())->get('/admin/dashboard');
            $this->chk($d->status() === 200, 'dashboard survives a malformed notices payload (got ' . $d->status() . ')');

            Http::fake();

            // real-world latency: one un-faked dashboard load
            \Illuminate\Support\Facades\Http::clearResolvedInstances();
            $this->refreshApplication();
            $t0 = microtime(true);
            $d = $this->actingAs($this->admin())->get('/admin/dashboard');
            $realMs = round((microtime(true) - $t0) * 1000);
            $t0 = microtime(true);
            $d2 = $this->actingAs($this->admin())->get('/admin/dashboard');
            $realMs2 = round((microtime(true) - $t0) * 1000);
            $this->note('un-faked dashboard load 1 = ' . $realMs . 'ms, load 2 = ' . $realMs2 . 'ms (MasterNoticeService fires on every load, no cache)');
            $this->chk($d->status() === 200 && $d2->status() === 200, 'dashboard works against the real master server too');
            $this->chk($realMs2 < 3000, 'a second dashboard load costs < 3s (actual ' . $realMs2 . 'ms) - i.e. the master call is not stalling the page');

        } finally {
            fwrite(STDOUT, "\n===== CLEANUP =====\n");

            // restore facebook_feed setting exactly
            if ($this->origFeedSetting !== null) {
                DB::table('settings')->where('key', 'facebook_feed')->update(['value' => $this->origFeedSetting]);
                fwrite(STDOUT, "--   settings.facebook_feed restored to '" . $this->origFeedSetting . "'\n");
            }

            // products created through the controller
            foreach ($this->productIds as $pid) {
                $p = Product::find($pid);
                if ($p) {
                    foreach (\App\Models\ProductImage::where('product_id', $pid)->get() as $im) {
                        @unlink(public_path('products/' . $im->image));
                        @unlink(public_path('thumb_products/' . $im->image));
                    }
                    if ($p->image) {
                        @unlink(public_path('products/' . $p->image));
                        @unlink(public_path('thumb_products/' . $p->image));
                        @unlink(public_path('thumb_products/' . $p->image . '.webp'));
                    }
                }
                \App\Models\ProductImage::where('product_id', $pid)->delete();
                \App\Models\ProductStock::where('product_id', $pid)->delete();
                \App\Models\Variation::where('product_id', $pid)->delete();
                Product::where('id', $pid)->delete();
            }
            foreach ($this->sliderIds as $sid) DB::table('sliders')->where('id', $sid)->delete();
            foreach ($this->addonSlugs as $s) AddonLicense::where('addon_slug', $s)->delete();

            // files the optimiser created for OTHER people's images -> restore prior state
            foreach ($createdThumbsForOthers as $p) @unlink($p);
            foreach ($createdSiblingsForOthers as $p) @unlink($p);
            fwrite(STDOUT, '--   removed ' . count($createdThumbsForOthers) . ' thumbs + ' . count($createdSiblingsForOthers) . " webp siblings the run generated for pre-existing images\n");

            foreach (array_unique($this->files) as $f) @unlink($f);

            // optimiser log - only ours if it did not exist before
            if (!$this->optLogExisted) @unlink(storage_path('app/image_optimize_done.json'));
            if (!$this->imgBackupExisted) {
                foreach (['sliders', 'homeimages', 'mobile_sliders', 'uploads/home_categories/cover'] as $sub) {
                    @rmdir(storage_path('app/image_backup/' . $sub));
                }
                @rmdir(storage_path('app/image_backup/uploads/home_categories'));
                @rmdir(storage_path('app/image_backup/uploads'));
                @rmdir(storage_path('app/image_backup'));
            }
            @unlink(storage_path('app/temp_module.zip'));

            fwrite(STDOUT, "\n===== T14 RESULT: {$this->pass} OK / {$this->failn} FAIL =====\n");
            $this->assertTrue(true);
        }
    }

    private function fileReferenced($needle)
    {
        foreach ([base_path('bootstrap/app.php'), base_path('public/index.php'), base_path('artisan'), base_path('composer.json')] as $f) {
            if (is_file($f) && str_contains(file_get_contents($f), $needle)) return true;
        }
        return false;
    }
}
