<?php
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;

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

    /*
|--------------------------------------------------------------------------
| Department Management
|--------------------------------------------------------------------------
*/

Route::prefix('departments')
    ->middleware('auth:api')
    ->group(function () {

        Route::middleware(
            'role:ADMIN,DOCTOR,NURSE,RECEPTIONIST'
        )->group(function () {

            Route::get(
                '/',
                [
                    DepartmentController::class,
                    'index',
                ]
            );

            Route::get(
                '/{department}',
                [
                    DepartmentController::class,
                    'show',
                ]
            );
        });


        Route::middleware(
            'role:ADMIN'
        )->group(function () {

            Route::post(
                '/',
                [
                    DepartmentController::class,
                    'store',
                ]
            );

            Route::put(
                '/{department}',
                [
                    DepartmentController::class,
                    'update',
                ]
            );

            Route::delete(
                '/{department}',
                [
                    DepartmentController::class,
                    'destroy',
                ]
            );
        });
    });


/*
|--------------------------------------------------------------------------
| Doctor Management
|--------------------------------------------------------------------------
*/

Route::prefix('doctors')
    ->middleware('auth:api')
    ->group(function () {

        Route::middleware(
            'role:ADMIN,DOCTOR,NURSE,RECEPTIONIST'
        )->group(function () {

            Route::get(
                '/',
                [
                    DoctorController::class,
                    'index',
                ]
            );

            Route::get(
                '/{doctor}',
                [
                    DoctorController::class,
                    'show',
                ]
            );
        });


        Route::middleware(
            'role:ADMIN'
        )->group(function () {

            Route::post(
                '/',
                [
                    DoctorController::class,
                    'store',
                ]
            );

            Route::put(
                '/{doctor}',
                [
                    DoctorController::class,
                    'update',
                ]
            );

            Route::delete(
                '/{doctor}',
                [
                    DoctorController::class,
                    'destroy',
                ]
            );


            Route::post(
                '/{doctor}/schedules',
                [
                    DoctorController::class,
                    'storeSchedule',
                ]
            );


            Route::delete(
                '/{doctor}/schedules/{schedule}',
                [
                    DoctorController::class,
                    'destroySchedule',
                ]
            );
        });
    });

/*
|--------------------------------------------------------------------------
| Appointment Management
|--------------------------------------------------------------------------
*/

Route::prefix('appointments')
    ->middleware('auth:api')
    ->group(function () {

        /*
         * Important:
         * /availability must appear before /{appointment}
         */

        Route::middleware(
            'role:ADMIN,DOCTOR,NURSE,RECEPTIONIST'
        )->group(function () {

            Route::get(
                '/availability',
                [
                    AppointmentController::class,
                    'availability',
                ]
            )->name(
                'api.appointments.availability'
            );


            Route::get(
                '/',
                [
                    AppointmentController::class,
                    'index',
                ]
            )->name(
                'api.appointments.index'
            );


            Route::get(
                '/{appointment}',
                [
                    AppointmentController::class,
                    'show',
                ]
            )->name(
                'api.appointments.show'
            );
        });


        /*
         * ADMIN / RECEPTIONIST booking
         */

        Route::middleware(
            'role:ADMIN,RECEPTIONIST'
        )->group(function () {

            Route::post(
                '/',
                [
                    AppointmentController::class,
                    'store',
                ]
            )->name(
                'api.appointments.store'
            );


            Route::put(
                '/{appointment}',
                [
                    AppointmentController::class,
                    'update',
                ]
            )->name(
                'api.appointments.update'
            );
        });


        /*
         * Appointment workflow
         */

        Route::middleware(
            'role:ADMIN,DOCTOR,NURSE,RECEPTIONIST'
        )->group(function () {

            Route::patch(
                '/{appointment}/status',
                [
                    AppointmentController::class,
                    'updateStatus',
                ]
            )->name(
                'api.appointments.status'
            );
        });
    });    