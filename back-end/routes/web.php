<?php

use App\Http\Controllers\DailyStatsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonthlyStatsController;
use App\Http\Controllers\WeeklyStatsController;
use App\Http\Controllers\XmlImportController;
use App\Http\Controllers\YearlyStatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// XML Import
Route::get('/xml/import', [XmlImportController::class, 'index'])->name('xml.import');
Route::post('/xml/import', [XmlImportController::class, 'store'])->name('xml.import.store');

// Statistics
Route::get('/stats/daily', [DailyStatsController::class, 'index'])->name('stats.daily');
Route::get('/stats/weekly', [WeeklyStatsController::class, 'index'])->name('stats.weekly');
Route::get('/stats/monthly', [MonthlyStatsController::class, 'index'])->name('stats.monthly');
Route::get('/stats/yearly', [YearlyStatsController::class, 'index'])->name('stats.yearly');