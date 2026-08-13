<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RefreshTokenController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use Illuminate\Support\Facades\Route;





Route::prefix('/auth')->middleware('locale')
    ->group(function (): void {
        Route::name('auth.')
            ->group(function (): void {
                Route::post('/login', LoginController::class);

                Route::post('/refresh-token', RefreshTokenController::class)->middleware(['auth:sanctum', 'abilities:refresh',])->name('refresh-token');

                Route::post('/logout', LogoutController::class)->middleware('auth:sanctum')->name('logout');
            });
    });
