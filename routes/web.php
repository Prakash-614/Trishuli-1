<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;

Route::get('/', function () {
    return view('welcome');
});



Schedule::command('awario:sync')->hourly();