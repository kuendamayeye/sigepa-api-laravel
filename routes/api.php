<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ResourceController;
use Illuminate\Support\Facades\Route;

/** @var array<int, string> $resources */
$resources = array_keys(config('sigepa.resources', []));

Route::prefix('v1')->group(function () use ($resources): void {
    // Autenticação pública
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware('auth.api')->group(function () use ($resources): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/profile', [AuthController::class, 'profile']);
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

        // Estatísticas (declaradas antes de /{resource}/{id})
        Route::get('/{resource}/statistics/overview', [ResourceController::class, 'statistics'])
            ->whereIn('resource', $resources);

        // Leitura
        Route::get('/{resource}', [ResourceController::class, 'index'])
            ->whereIn('resource', $resources);
        Route::get('/{resource}/{id}', [ResourceController::class, 'show'])
            ->whereIn('resource', $resources);

        // Escrita: administrador e operador
        Route::middleware('sigepa.role:admin,operador')->group(function () use ($resources): void {
            Route::post('/{resource}', [ResourceController::class, 'store'])
                ->whereIn('resource', $resources);
            Route::patch('/{resource}/{id}', [ResourceController::class, 'update'])
                ->whereIn('resource', $resources);
            Route::put('/{resource}/{id}', [ResourceController::class, 'update'])
                ->whereIn('resource', $resources);
            Route::delete('/{resource}/{id}', [ResourceController::class, 'destroy'])
                ->whereIn('resource', $resources);
            Route::post('/{resource}/{id}/{action}', [ResourceController::class, 'toggleActive'])
                ->whereIn('resource', $resources)
                ->whereIn('action', ['activate', 'deactivate']);
        });
    });
});
