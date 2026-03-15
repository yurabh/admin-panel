<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('app:clean-old-comments')->daily();

Schedule::command('app:publish-posts')->daily();

Schedule::command('app:set-role 6 admin')->sundays()->at('21:20');
