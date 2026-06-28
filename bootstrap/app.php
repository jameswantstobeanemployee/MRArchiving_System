<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global web middleware
        $middleware->web(append: [
            \App\Http\Middleware\ActiveUserMiddleware::class,
        ]);

        // Named middleware aliases
        $middleware->alias([
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
            'active.user' => \App\Http\Middleware\ActiveUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('checkouts:mark-overdue')->dailyAt('00:05');
        $schedule->command('backups:run-scheduled')->everyFifteenMinutes();
        $schedule->command('retention:send-alerts')->dailyAt('08:00');
        $schedule->command('charts:purge-destroyed')->dailyAt('02:00');
        $schedule->command('notifications:daily-digest')->dailyAt('08:05');
    })
    ->create();
