<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('store:status', function (): void {
    $this->info('C-Net Store is configured.');
});

Schedule::command('store:release-expired-reservations')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('store:catalog-audit')->dailyAt('02:10')->withoutOverlapping();
Schedule::command('store:image-library-audit')->weeklyOn(1, '02:20')->withoutOverlapping();
