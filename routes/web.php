<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')
    ->name('home');

Route::view('/login', 'auth.login')
    ->name('login');

Route::view('/dashboard', 'dashboard')
    ->name('dashboard');

Route::view('/doctor-dashboard', 'doctor-dashboard')
    ->name('doctor.dashboard');

Route::view('/patient-portal', 'patient-portal')
    ->name('patient.portal');

Route::view(
    '/patients',
    'patients.index'
)->name('patients.index');

Route::view(
    '/patients/create',
    'patients.form'
)->name('patients.create');

Route::get(
    '/patients/{patientId}/edit',
    function (int $patientId) {

        return view(
            'patients.form',
            compact('patientId')
        );
    }
)->whereNumber('patientId')
    ->name('patients.edit');

Route::get(
    '/patients/{patientId}',
    function (int $patientId) {

        return view(
            'patients.show',
            compact('patientId')
        );
    }
)->whereNumber('patientId')
    ->name('patients.show');

Route::view(
    '/departments',
    'departments.index'
)->name('departments.index');

Route::view(
    '/doctors',
    'doctors.index'
)->name('doctors.index');

Route::view(
    '/doctors/create',
    'doctors.form'
)->name('doctors.create');

Route::get(
    '/doctors/{doctorId}/edit',
    function (int $doctorId) {

        return view(
            'doctors.form',
            compact('doctorId')
        );
    }
)
    ->whereNumber('doctorId')
    ->name('doctors.edit');

Route::get(
    '/doctors/{doctorId}',
    function (int $doctorId) {

        return view(
            'doctors.show',
            compact('doctorId')
        );
    }
)
    ->whereNumber('doctorId')
    ->name('doctors.show');

Route::view(
    '/appointments',
    'appointments.index'
)->name('appointments.index');

Route::view(
    '/appointments/create',
    'appointments.form'
)->name('appointments.create');

Route::get(
    '/appointments/{appointmentId}/edit',
    function (int $appointmentId) {

        return view(
            'appointments.form',
            compact('appointmentId')
        );
    }
)
    ->whereNumber('appointmentId')
    ->name('appointments.edit');

Route::get(
    '/appointments/{appointmentId}',
    function (int $appointmentId) {

        return view(
            'appointments.show',
            compact('appointmentId')
        );
    }
)
    ->whereNumber('appointmentId')
    ->name('appointments.show');

Route::view(
    '/medical-records',
    'medical-records.index'
)->name('medical-records.index');

Route::view(
    '/medical-records/create',
    'medical-records.form'
)->name('medical-records.create');

Route::get(
    '/medical-records/{medicalRecordId}/edit',
    function (int $medicalRecordId) {
        return view('medical-records.form', compact('medicalRecordId'));
    }
)->whereNumber('medicalRecordId')
    ->name('medical-records.edit');

Route::get(
    '/medical-records/{medicalRecordId}',
    function (int $medicalRecordId) {
        return view('medical-records.show', compact('medicalRecordId'));
    }
)->whereNumber('medicalRecordId')
    ->name('medical-records.show');

Route::view(
    '/laboratory',
    'laboratory.index'
)->name('laboratory.index');

Route::view(
    '/laboratory/requests/create',
    'laboratory.request-form'
)->name('laboratory.requests.create');

Route::get(
    '/laboratory/requests/{labRequestId}',
    function (int $labRequestId) {
        return view('laboratory.show', compact('labRequestId'));
    }
)->whereNumber('labRequestId')
    ->name('laboratory.requests.show');

Route::view(
    '/laboratory/tests',
    'laboratory.tests'
)->name('laboratory.tests');

Route::view(
    '/pharmacy',
    'pharmacy.index'
)->name('pharmacy.index');

Route::view(
    '/pharmacy/catalogue',
    'pharmacy.catalogue'
)->name('pharmacy.catalogue');

Route::view(
    '/pharmacy/stock',
    'pharmacy.stock'
)->name('pharmacy.stock');

Route::view(
    '/pharmacy/prescriptions',
    'pharmacy.prescriptions'
)->name('pharmacy.prescriptions');

Route::get(
    '/pharmacy/prescriptions/{prescriptionId}',
    function (int $prescriptionId) {
        return view('pharmacy.dispense', compact('prescriptionId'));
    }
)->whereNumber('prescriptionId')
    ->name('pharmacy.prescriptions.dispense');

Route::view(
    '/billing',
    'billing.index'
)->name('billing.index');

Route::view(
    '/billing/invoices/create',
    'billing.form'
)->name('billing.invoices.create');

Route::get(
    '/billing/invoices/{invoiceId}',
    function (int $invoiceId) {
        return view('billing.show', compact('invoiceId'));
    }
)->whereNumber('invoiceId')
    ->name('billing.invoices.show');

Route::view(
    '/staff',
    'staff.index'
)->name('staff.index');

Route::view(
    '/staff/employees/create',
    'staff.form'
)->name('staff.employees.create');

Route::get(
    '/staff/employees/{employeeId}/edit',
    function (int $employeeId) {
        return view('staff.form', compact('employeeId'));
    }
)->whereNumber('employeeId')
    ->name('staff.employees.edit');

Route::view(
    '/staff/attendance',
    'staff.attendance'
)->name('staff.attendance');

Route::view(
    '/staff/leave-requests',
    'staff.leaves'
)->name('staff.leave-requests');

Route::view(
    '/reports',
    'reports.index'
)->name('reports.index');
