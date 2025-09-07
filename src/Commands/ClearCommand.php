<?php

namespace PlusinfoLab\Logstation\Commands;

use Illuminate\Console\Command;
use PlusinfoLab\Logstation\Facades\Logstation;

class ClearCommand extends Command
{
    protected $signature = 'logstation:clear';
    protected $description = 'Clear all LogStation entries';

    public function handle(): int
    {
        if (!$this->confirm('Are you sure you want to delete ALL log entries?')) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        $this->info('Clearing all entries...');

        $deleted = Logstation::clear();

        $this->info("Deleted {$deleted} entries.");

        return self::SUCCESS;
    }
}
