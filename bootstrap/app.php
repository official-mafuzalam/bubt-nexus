<?php

use App\Console\Commands\SendAssignmentReminders;
use App\Console\Commands\CloseOverdueAssignments;
use App\Console\Commands\CheckSchedulerStatus;
use App\Console\Commands\CleanupSubmissions;
use App\Console\Commands\AssignmentStats;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\TrackUserActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Combine web middleware to avoid duplicate append calls
        $middleware->web(append: [
            TrackUserActivity::class,
            CheckUserStatus::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withCommands([
        SendAssignmentReminders::class,
        CloseOverdueAssignments::class,
        // CheckSchedulerStatus::class,
        // CleanupSubmissions::class,
        // AssignmentStats::class,
        // Add other commands here as needed
    ])
    ->withSchedule(function (Schedule $schedule) {
        // Assignment System Commands
    
        // Send assignment reminders daily at 9 AM
        $schedule->command('assignments:remind')
            ->dailyAt('09:00')
            ->description('Send assignment deadline reminders')
            ->onOneServer()
            ->withoutOverlapping(30); // Prevent overlapping for 30 minutes
    
        // Close overdue assignments every hour
        $schedule->command('assignments:close-overdue')
            ->hourly()
            ->description('Close overdue assignments')
            ->onOneServer()
            ->withoutOverlapping(10); // Prevent overlapping for 10 minutes
    
        // Check scheduler status every 5 minutes
        $schedule->command('scheduler:status')
            ->everyFiveMinutes()
            ->description('Monitor scheduler status')
            ->withoutOverlapping();

        // Cleanup old submissions weekly on Sunday at 3 AM
        $schedule->command('submissions:cleanup --days=90')
            ->weeklyOn(0, '03:00') // Sunday at 3 AM
            ->description('Clean up old submissions (90+ days)')
            ->onOneServer()
            ->withoutOverlapping(60);

        // System Maintenance Commands
    
        // Run queue worker (if using queues)
        $schedule->command('queue:work --stop-when-empty')
            ->everyMinute()
            ->withoutOverlapping()
            ->description('Process queue jobs')
            ->runInBackground();

        // Clear cache monthly on 1st at 2 AM
        $schedule->command('cache:clear')
            ->monthlyOn(1, '02:00')
            ->description('Clear application cache')
            ->onOneServer();

        // Optimize clear weekly
        $schedule->command('optimize:clear')
            ->weeklyOn(0, '01:00') // Sunday at 1 AM
            ->description('Clear all caches')
            ->onOneServer();

        // Model pruning (if using Laravel's model pruning feature)
        $schedule->command('model:prune')
            ->dailyAt('04:00')
            ->description('Prune old model records')
            ->onOneServer();

        // Database backup (if using spatie/laravel-backup package)
        // Uncomment if you have spatie/laravel-backup installed
        /*
        $schedule->command('backup:run')
            ->dailyAt('02:00')
            ->description('Backup database and files')
            ->onOneServer();
        */

        // Log cleanup (if using spatie/laravel-log-cleaner package)
        // Uncomment if you have log cleanup needs
        /*
        $schedule->command('log:clean')
            ->dailyAt('05:00')
            ->description('Clean old log files')
            ->onOneServer();
        */

        // Send daily assignment summary to faculty (optional - you can create this command)
        // $schedule->command('assignments:daily-summary')
        //     ->dailyAt('17:00') // 5 PM daily
        //     ->description('Send daily assignment summary to faculty')
        //     ->onOneServer();
    
        // Update assignment statistics cache hourly
        // $schedule->command('assignments:cache-stats')
        //     ->hourly()
        //     ->description('Update assignment statistics cache')
        //     ->onOneServer();
    
        // Send weekly activity report on Monday at 9 AM
        // $schedule->command('reports:weekly-activity')
        //     ->weeklyOn(1, '09:00') // Monday at 9 AM
        //     ->description('Send weekly activity report')
        //     ->onOneServer();
    })
    ->create();