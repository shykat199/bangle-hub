<?php

namespace App\Console\Commands;

use App\Support\ImageOptimizer;
use Illuminate\Console\Command;

class OptimizeProductImages extends Command
{
    protected $signature = 'images:optimize
        {--limit=0 : Stop after this many images (0 = all)}
        {--force : Regenerate copies that already exist}';

    protected $description = 'Create WebP/AVIF copies for product images uploaded before automatic optimization (products/ and thumb_products/)';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $done = 0; $before = 0; $after = 0;

        foreach (['thumb_products', 'products'] as $folder) {
            $dir = public_path($folder);
            if (!is_dir($dir)) continue;

            foreach (scandir($dir) as $file) {
                $path = $dir . '/' . $file;
                if (!is_file($path) || !preg_match('/\.(jpe?g|png|webp)$/i', $file)) continue;
                // the copies themselves ("x.jpg.webp") are not sources
                if (preg_match('/\.(jpe?g|png|webp)\.(webp|avif)$/i', $file)) continue;
                if (!$this->option('force') && (is_file($path . '.webp') || is_file($path . '.avif'))) continue;

                $written = ImageOptimizer::generate($path);
                if ($written) {
                    $before += filesize($path);
                    $after  += min($written);
                }
                $done++;

                if ($limit > 0 && $done >= $limit) break 2;
            }
        }

        $this->info("Processed {$done} image(s).");
        if ($before > 0) {
            $this->info(sprintf('Best copy vs original: %s -> %s (%d%% smaller)', $this->kb($before), $this->kb($after), round((1 - $after / $before) * 100)));
        }

        return self::SUCCESS;
    }

    private function kb(int $bytes): string
    {
        return number_format($bytes / 1024) . ' KB';
    }
}
