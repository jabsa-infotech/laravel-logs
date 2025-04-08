<?php

use Illuminate\Support\Facades\Route;
use Jabsa\LaravelLogs\Http\Controllers\LogController;

Route::group([
    'prefix' => config('laravel-logs.route_prefix'),
    'as' => config('laravel-logs.route_name_prefix') . '.',
    'middleware' => 'web'
], function(){
    Route::get('/', [LogController::class, 'index'])->name('index');
    Route::delete('/destroy', [LogController::class, 'destroy'])->name('destroy');
});