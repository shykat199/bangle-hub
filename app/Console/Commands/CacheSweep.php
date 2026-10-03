<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Support\Formatter;

class CacheSweep extends Command
{
    protected $signature = 'app:license-regenerate {--domains=*}';
    protected $description = 'Regenerate license domains JSON';

    public function handle()
    {
        $domains = $this->option('domains') ?: [];
        if (empty($domains)) {
            $this->error('No domains provided. Use --domains=example.com --domains=www.example.com');
            return 1;
        }
        Formatter::saveSigned($domains, ['issued_by' => 'cli']);
        $this->info('License file regenerated.');
        return 0;
    }
}
