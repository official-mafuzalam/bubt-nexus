<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use Illuminate\Console\Command;

class CloseOverdueAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:close-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close assignments whose deadline has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assignments = Assignment::where('status', 'published')
            ->where('deadline', '<', now())
            ->get();

        $closedCount = 0;

        foreach ($assignments as $assignment) {
            $assignment->update(['status' => 'closed']);
            
            // Update any pending submissions to 'late'
            $assignment->submissions()
                ->where('status', 'pending')
                ->update(['status' => 'late']);
                
            $closedCount++;
        }

        $this->info("Closed {$closedCount} overdue assignments.");
        
        return Command::SUCCESS;
    }
}