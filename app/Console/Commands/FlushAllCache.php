<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FlushAllCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear config, route, view and app cache at once';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('cache:clear');

        $this->info('✅ All caches have been cleared: config, route, view, and app.');
        return Command::SUCCESS;
    }
}
