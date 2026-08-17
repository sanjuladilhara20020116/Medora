<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\LaboratoryController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PharmacyController;
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

/*
|--------------------------------------------------------------------------
| Electronic Medical Records
|--------------------------------------------------------------------------
*/
Route::prefix('medical-records')
    ->middleware('auth:api')
    ->group(function () {
        Route::middleware('role:ADMIN,DOCTOR,NURSE')->group(function () {
            Route::get('/', [MedicalRecordController::class, 'index'])
                ->name('api.medical-records.index');

            Route::get('/{medicalRecord}', [MedicalRecordController::class, 'show'])
                ->name('api.medical-records.show');

            Route::get('/{medicalRecord}/reports/{report}/download', [MedicalRecordController::class, 'downloadReport'])
                ->name('api.medical-records.reports.download');
        });

        Route::middleware('role:ADMIN,DOCTOR')->group(function () {
            Route::post('/', [MedicalRecordController::class, 'store'])
                ->name('api.medical-records.store');

            Route::put('/{medicalRecord}', [MedicalRecordController::class, 'update'])
                ->name('api.medical-records.update');

            Route::post('/{medicalRecord}/prescription', [MedicalRecordController::class, 'savePrescription'])
                ->name('api.medical-records.prescription.save');

            Route::post('/{medicalRecord}/reports', [MedicalRecordController::class, 'storeReport'])
                ->name('api.medical-records.reports.store');

            Route::delete('/{medicalRecord}/reports/{report}', [MedicalRecordController::class, 'destroyReport'])
                ->name('api.medical-records.reports.destroy');
        });
    });

/*
|--------------------------------------------------------------------------
| Laboratory Management
|--------------------------------------------------------------------------
*/
Route::prefix('lab-tests')
    ->middleware('auth:api')
    ->group(function () {
        Route::middleware('role:ADMIN,DOCTOR,NURSE,LAB_STAFF')->group(function () {
            Route::get('/', [LaboratoryController::class, 'tests'])
                ->name('api.lab-tests.index');
        });

        Route::middleware('role:ADMIN')->group(function () {
            Route::post('/', [LaboratoryController::class, 'storeTest'])
                ->name('api.lab-tests.store');
            Route::put('/{labTest}', [LaboratoryController::class, 'updateTest'])
                ->name('api.lab-tests.update');
            Route::delete('/{labTest}', [LaboratoryController::class, 'destroyTest'])
                ->name('api.lab-tests.destroy');
        });
    });

Route::prefix('lab-requests')
    ->middleware('auth:api')
    ->group(function () {
        Route::middleware('role:ADMIN,DOCTOR,NURSE,LAB_STAFF')->group(function () {
            Route::get('/', [LaboratoryController::class, 'requests'])
                ->name('api.lab-requests.index');
            Route::get('/{labRequest}', [LaboratoryController::class, 'showRequest'])
                ->name('api.lab-requests.show');
        });

        Route::middleware('role:ADMIN,DOCTOR')->group(function () {
            Route::post('/', [LaboratoryController::class, 'storeRequest'])
                ->name('api.lab-requests.store');
        });

        Route::middleware('role:ADMIN,LAB_STAFF')->group(function () {
            Route::patch('/{labRequest}/collect-sample', [LaboratoryController::class, 'collectSample'])
                ->name('api.lab-requests.collect-sample');
            Route::patch('/{labRequest}/start-processing', [LaboratoryController::class, 'startProcessing'])
                ->name('api.lab-requests.start-processing');
            Route::put('/{labRequest}/result', [LaboratoryController::class, 'saveResult'])
                ->name('api.lab-requests.result.save');
        });
    });

/*
|--------------------------------------------------------------------------
| Pharmacy Management
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {
    Route::middleware('role:ADMIN,PHARMACIST')->group(function () {
        Route::get('/medicine-categories', [PharmacyController::class, 'categories'])
            ->name('api.medicine-categories.index');
        Route::post('/medicine-categories', [PharmacyController::class, 'storeCategory'])
            ->name('api.medicine-categories.store');
        Route::put('/medicine-categories/{medicineCategory}', [PharmacyController::class, 'updateCategory'])
            ->name('api.medicine-categories.update');
        Route::delete('/medicine-categories/{medicineCategory}', [PharmacyController::class, 'destroyCategory'])
            ->name('api.medicine-categories.destroy');

        Route::get('/medicines', [PharmacyController::class, 'medicines'])
            ->name('api.medicines.index');
        Route::post('/medicines', [PharmacyController::class, 'storeMedicine'])
            ->name('api.medicines.store');
        Route::put('/medicines/{medicine}', [PharmacyController::class, 'updateMedicine'])
            ->name('api.medicines.update');
        Route::delete('/medicines/{medicine}', [PharmacyController::class, 'destroyMedicine'])
            ->name('api.medicines.destroy');

        Route::get('/medicine-stocks', [PharmacyController::class, 'stocks'])
            ->name('api.medicine-stocks.index');
        Route::post('/medicine-stocks', [PharmacyController::class, 'receiveStock'])
            ->name('api.medicine-stocks.store');

        Route::get('/pharmacy/alerts', [PharmacyController::class, 'alerts'])
            ->name('api.pharmacy.alerts');
        Route::get('/pharmacy/prescriptions', [PharmacyController::class, 'prescriptions'])
            ->name('api.pharmacy.prescriptions.index');
        Route::get('/pharmacy/prescriptions/{prescription}', [PharmacyController::class, 'showPrescription'])
            ->name('api.pharmacy.prescriptions.show');
        Route::post('/pharmacy/prescriptions/{prescription}/dispenses', [PharmacyController::class, 'dispensePrescription'])
            ->name('api.pharmacy.prescriptions.dispense');
    });
});
