<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 🕗 Daily email reminder for upcoming events
Schedule::command('email:send-event-reminders')
    ->dailyAt('08:00')
    ->timezone('Asia/Manila');
