<?php

use App\Exceptions\BusinessLogicException;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\ResolveApiLocale;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'locale' => ResolveApiLocale::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'permission' => PermissionMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (
            BusinessLogicException $exception
        ) {
            return ApiResponse::error(
                message: $exception->getMessage(),
                errors: null,
                status: $exception->status
            );
        });
    })->create();
