<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('awario:sync')->hourly();
Schedule::command('awario:classify')->hourlyAt(5); // runs 5 min after sync, giving sync time to finish