<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('listas-precios:renovar')
    ->timezone('America/Bogota')
    ->cron('0 6,9,12,15,17 * * *')
    ->withoutOverlapping();

Schedule::command('listas-precios:renovar')
    ->timezone('America/Bogota')
    ->dailyAt('14:03')
    ->withoutOverlapping();