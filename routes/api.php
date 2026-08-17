<?php

use App\Http\Controllers\API\V1\SelectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function (): void {
    require base_path('routes/api/v1/auth.php');
});

Route::prefix('v1')->group(function (): void {
    require base_path('routes/api/v1/dashboard.php');
});

Route::prefix('v1')->group(function (): void {
    require base_path('routes/api/v1/employee.php');
});

Route::prefix('v1')->middleware(['locale', 'auth:sanctum'])->group(function (): void {
    Route::get('/selects', [SelectController::class, 'getSelects']);
});

