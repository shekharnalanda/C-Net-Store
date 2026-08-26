<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('store:status', function (): void {
    $this->info('C-Net Store is configured.');
});

