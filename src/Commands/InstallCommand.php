<?php

namespace PlusinfoLab\Logstation\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'logstation:install';

    protected $description = 'Install LogStation package';

    public function handle(): int
    {
        $this->info('Installing LogStation...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'logstation-config',
            '--force' => true,
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'logstation-migrations',
            '--force' => true,
        ]);

        // Run migrations
        if ($this->confirm('Would you like to run the migrations now?', true)) {
            $this->call('migrate');
        }

        // Publish assets
        $this->call('vendor:publish', [
            '--tag' => 'logstation-assets',
            '--force' => true,
        ]);

        $this->info('LogStation installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Visit /logstation in your browser');
        $this->line('2. Configure authorization in config/logstation.php');
        $this->line('3. Optionally configure a separate database connection');

        return self::SUCCESS;
    }
}
