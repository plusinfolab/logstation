<?php

namespace PlusinfoLab\Logstation\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'logstation:publish {--force : Overwrite existing files}';

    protected $description = 'Publish LogStation assets';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'logstation-assets',
            '--force' => $this->option('force'),
        ]);

        $this->info('LogStation assets published successfully!');

        return self::SUCCESS;
    }
}
