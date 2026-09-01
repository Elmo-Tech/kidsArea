<?php

use App\Http\Controllers\Api\V1\Employee\CheckInController;
use App\Http\Controllers\Api\V1\Employee\CheckOutController;
use App\Http\Controllers\Api\V1\Employee\ProfileController;
use App\Http\Controllers\Api\V1\Employee\TodayAttendanceController;
use App\Http\Controllers\Api\V1\Employee\CashShiftController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Employee\ActivitySessionController;
use App\Http\Controllers\Api\V1\Employee\PendingCashTransferController;

Route::middleware(['locale', 'auth:sanctum'])
    ->prefix('me')
    ->group(function (): void {

        Route::get('/profile', ProfileController::class);

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

        Route::prefix('cash-shift')
            ->middleware('permission:cash-shifts.employee-open')
            ->group(function (): void {
                Route::get('/', [CashShiftController::class, 'current']);
                Route::post('/open', [CashShiftController::class, 'open']);
                Route::post('/close', [CashShiftController::class, 'close']);
            });

         Route::get(
            '/activity-sessions',
            [ActivitySessionController::class, 'index']
        );

        Route::get(
            '/activity-sessions/{activitySession}/my-attendance',
            [ActivitySessionController::class, 'attendance']
        );

        Route::post(
            '/cash-shift/transfer-to-main',
            [CashShiftController::class, 'transferToMain']
        );

        Route::get('/cash-shift/pending-transfer', PendingCashTransferController::class);

    });

