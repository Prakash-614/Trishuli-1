<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MentionReportController;
use App\Http\Controllers\ManualMentionController;

// Login routes (guests only)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Everything else requires login
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', fn () => redirect('/'));
    Route::get('/mentions-report', [MentionReportController::class, 'index'])->name('mentions.report');

    Route::get('/add-mention', [ManualMentionController::class, 'create'])->name('mentions.add-manual');
    Route::post('/add-mention/preview', [ManualMentionController::class, 'preview'])->name('mentions.preview');
    Route::post('/add-mention', [ManualMentionController::class, 'store'])->name('mentions.store');
    // PDF Export and Client Email routes
    Route::get('/report/export-pdf', [\App\Http\Controllers\ReportExportController::class, 'downloadPdf'])->name('report.export-pdf');
    Route::post('/report/send-email', [\App\Http\Controllers\ReportExportController::class, 'emailReport'])->name('report.send-email');
    
    
});