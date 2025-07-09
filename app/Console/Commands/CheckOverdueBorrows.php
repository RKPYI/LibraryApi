<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Borrow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckOverdueBorrows extends Command
{
    protected $signature = 'borrows:check-overdue';

    protected $description = 'Check all borrowed books and mark them as overdue if past due_date';

    public function handle()
    {
        $borrows = Borrow::where('status', 'borrowed')
            ->whereDate('due_date', '<', now())
            ->get();

        foreach ($borrows as $borrow) {
            $borrow->update(['status' => 'overdue']);
            $this->info("Marked borrow ID {$borrow->id} as overdue.");
            Log::info("Marked borrow ID {$borrow->id} as overdue.");
        }

        $this->info('Overdue check complete.');
    }
}
