<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Notifications\DeadlineReminderNotification;
use Illuminate\Console\Command;

class SendAssignmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for upcoming assignment deadlines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assignments = Assignment::where('status', 'published')
            ->where('deadline', '<=', now()->addDay())
            ->where('deadline', '>', now())
            ->with(['class.enrollments.student'])
            ->get();

        $reminderCount = 0;

        foreach ($assignments as $assignment) {
            foreach ($assignment->class->enrollments as $enrollment) {
                if ($enrollment->status === 'active') {
                    $enrollment->student->notify(new DeadlineReminderNotification($assignment));
                    $reminderCount++;
                }
            }
        }

        $this->info("Sent {$reminderCount} deadline reminders for " . $assignments->count() . " assignments.");
        
        return Command::SUCCESS;
    }
}