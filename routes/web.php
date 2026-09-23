<?php

use App\Http\Controllers\MentionReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mentions-report', [MentionReportController::class, 'index'])->name('mentions.report');

use App\Http\Controllers\ManualMentionController;

Route::get('/add-mention', [ManualMentionController::class, 'create'])->name('mentions.add-manual');
Route::post('/add-mention/preview', [ManualMentionController::class, 'preview'])->name('mentions.preview');
Route::post('/add-mention', [ManualMentionController::class, 'store'])->name('mentions.store');
