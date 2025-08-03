<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class FormatCodeWithPint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:format';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Format codebase using Laravel Pint (PSR-12 standard)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎯 Running Laravel Pint to format your code...');

        $process = Process::fromShellCommandline('./vendor/bin/pint');

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if ($process->isSuccessful()) {
            $this->info('✅ Code formatted successfully.');

            return Command::SUCCESS;
        }

        $this->error('❌ Failed to format code.');

        return Command::FAILURE;
    }
}
