<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use \Illuminate\Support\Facades\Schedule;
use \App\Console\Commands\TestimonySubmission;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// повторяем каждый месяц после 20 числа каждый день
Schedule::command(TestimonySubmission::class)->cron('0 12 20-31 * *');
