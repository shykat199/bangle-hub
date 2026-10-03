<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CacheController;

Route::middleware('web')->group(function () {
    Route::get('/system/cache', [CacheController::class, 'index'])->name('system.cache.index');
    Route::post('/system/cache/update', [CacheController::class, 'update'])->name('system.cache.update');
});

