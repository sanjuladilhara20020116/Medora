<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Medora HMS API is running.',
    ]);
});

Route::prefix('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Authentication Routes
    |--------------------------------------------------------------------------
    */

    Route::post('/login', [AuthController::class, 'login'])
        ->name('api.auth.login');

    /*
    |--------------------------------------------------------------------------
    | Protected Authentication Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:api')->group(function () {

        Route::get('/me', [AuthController::class, 'me'])
            ->name('api.auth.me');

         Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.auth.logout');  
        
        Route::post('/refresh', [AuthController::class, 'refresh'])
    ->name('api.auth.refresh');

    });

});