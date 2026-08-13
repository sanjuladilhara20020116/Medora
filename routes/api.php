<?php
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;

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

/*
|--------------------------------------------------------------------------
| Administrator Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:api',
    'role:ADMIN',
])->group(function () {

    Route::get(
        '/dashboard/admin',
        [DashboardController::class, 'admin']
    )->name('api.dashboard.admin');

});

/*
|--------------------------------------------------------------------------
| Patient Management
|--------------------------------------------------------------------------
*/

Route::prefix('patients')
    ->middleware('auth:api')
    ->group(function () {

        /*
         * View patients
         */
        Route::middleware(
            'role:ADMIN,DOCTOR,NURSE,RECEPTIONIST'
        )->group(function () {

            Route::get(
                '/',
                [PatientController::class, 'index']
            )->name('api.patients.index');

            Route::get(
                '/{patient}',
                [PatientController::class, 'show']
            )->name('api.patients.show');

        });


        /*
         * Register and update patients
         */
        Route::middleware(
            'role:ADMIN,RECEPTIONIST'
        )->group(function () {

            Route::post(
                '/',
                [PatientController::class, 'store']
            )->name('api.patients.store');

            Route::put(
                '/{patient}',
                [PatientController::class, 'update']
            )->name('api.patients.update');

            Route::patch(
                '/{patient}',
                [PatientController::class, 'update']
            );

        });


        /*
         * Patient documents
         */
        Route::middleware(
            'role:ADMIN,DOCTOR,NURSE,RECEPTIONIST'
        )->group(function () {

            Route::post(
                '/{patient}/documents',
                [
                    PatientController::class,
                    'storeDocument',
                ]
            )->name(
                'api.patients.documents.store'
            );

        });


        /*
         * ADMIN-only destructive actions
         */
        Route::middleware(
            'role:ADMIN'
        )->group(function () {

            Route::delete(
                '/{patient}',
                [PatientController::class, 'destroy']
            )->name('api.patients.destroy');

            Route::delete(
                '/{patient}/documents/{document}',
                [
                    PatientController::class,
                    'destroyDocument',
                ]
            )->name(
                'api.patients.documents.destroy'
            );

        });

    });