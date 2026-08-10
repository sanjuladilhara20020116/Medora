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

    Route::post('/login', [AuthController::class, 'login'])
        ->name('api.auth.login');

});