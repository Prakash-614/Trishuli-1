<?php
use App\Http\Controllers\MentionReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;

Route::get('/', function () {
    return view('welcome');
});



Schedule::command('awario:sync')->hourly();
Route::get('/mentions-report', [MentionReportController::class, 'index'])->name('mentions.report');