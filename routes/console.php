<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('app:clean-old-comments')
    ->daily();

Schedule::command('app:publish-posts')
    ->daily();

$adminId = config('services.admin.id');

Schedule::command("app:set-role {$adminId} admin")
    ->sundays()
    ->at('21:20');

Schedule::command('queue:prune-failed --hours=48')
    ->monthly();
