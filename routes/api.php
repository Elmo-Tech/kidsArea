<?php

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

