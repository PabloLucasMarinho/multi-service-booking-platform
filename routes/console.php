<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:rebooking')->dailyAt('09:00');
Schedule::command('appointments:mark-no-show')->dailyAt('00:05');
