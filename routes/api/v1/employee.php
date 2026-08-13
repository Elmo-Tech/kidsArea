<?php

use App\Http\Controllers\Api\V1\Employee\CheckInController;
use App\Http\Controllers\Api\V1\Employee\CheckOutController;
use App\Http\Controllers\Api\V1\Employee\TodayAttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['locale', 'auth:sanctum'])
    ->prefix('me')
    ->group(function (): void {

        Route::post(
            '/attendance/check-in',
            CheckInController::class
        );

        Route::post(
            '/attendance/check-out',
            CheckOutController::class
        );

        Route::get(
            '/attendance/today',
            TodayAttendanceController::class
        );
    });
