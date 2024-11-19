<?php

use Dbiz\SpinWheelTool\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Route;

$controller = app('spinwheeltool.controller');

Route::middleware(['nova', Authorize::class])->group(function ()  use ($controller) {
	Route::post('/admin/save', [$controller, 'store']);
});

Route::middleware('web','throttle:100,1')->group(function () use ($controller) {
	Route::get('/config', [$controller, 'index']);
	Route::get('/check-spin', [$controller, 'checkSpin']);
    Route::post('/spin', [$controller, 'spin']);
    Route::post('/submit-info', [$controller, 'submitCustomerInfo']);
});
