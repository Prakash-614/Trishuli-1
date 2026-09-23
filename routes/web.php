<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MentionReportController;
use App\Http\Controllers\ManualMentionController;

// Public Monitoring Pages (No Auth Required)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', fn() => redirect('/'));
Route::get('/mentions-report', [MentionReportController::class, 'index'])->name('mentions.report');

Route::get('/add-mention', [ManualMentionController::class, 'create'])->name('mentions.add-manual');
Route::post('/add-mention/preview', [ManualMentionController::class, 'preview'])->name('mentions.preview');
Route::post('/add-mention', [ManualMentionController::class, 'store'])->name('mentions.store');

// Optional Auth Routes (Keep for whenever you want to re-enable login later)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');