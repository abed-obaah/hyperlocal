<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Landing page (route:cache-safe — no closure).
Route::view('/', 'welcome');

// Admin / restaurant dashboard SPA, served under /dashboard (and any sub-path).
Route::get('/dashboard/{any?}', DashboardController::class)->where('any', '.*');
