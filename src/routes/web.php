<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('laravel-logs.route_prefix'),
    'middleware' => 'web'
], function(){
    Route::get('/', function () {
        return view('laravel-logs::hello');
    });
});