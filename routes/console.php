<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Tasks
Schedule::command('wa:send-notifications --due-date')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->description('Kirim notifikasi WhatsApp untuk invoice yang jatuh tempo hari ini')
    ->withoutOverlapping();

Schedule::command('wa:send-notifications --overdue')
    ->dailyAt('10:00')
    ->timezone('Asia/Jakarta')
    ->description('Kirim notifikasi WhatsApp untuk invoice yang sudah lewat jatuh tempo')
    ->withoutOverlapping();
