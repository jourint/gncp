<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Чистка старых записей Telescope ежедневно
Schedule::command('telescope:prune')->daily();

// Запуск бэкапа каждый день в 3 часа ночи
Schedule::command('db:backup-tg')->dailyAt('03:00');
