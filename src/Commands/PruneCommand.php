<?php

namespace PlusinfoLab\Logstation\Commands;

use Illuminate\Console\Command;
use PlusinfoLab\Logstation\Facades\Logstation;

class PruneCommand extends Command
{
    protected $signature = 'logstation:prune {--days= : Number of days to retain}';
    protected $description = 'Prune old LogStation entries';

    public function handle(): int
    {
        $days = $this->option('days') ?? config('logstation.retention.days', 7);

        if (!$days) {
            $this->error('Retention days not configured. Set LOGSTATION_RETENTION_DAYS or use --days option.');
            return self::FAILURE;
        }

        $before = now()->subDays($days);

        $this->info("Pruning entries older than {$days} days ({$before->toDateTimeString()})...");

        $deleted = Logstation::prune($before);

        $this->info("Deleted {$deleted} entries.");

        return self::SUCCESS;
    }
}
