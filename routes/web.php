<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('/login', 'auth.login')
    ->name('login');

Route::view('/dashboard', 'dashboard')
    ->name('dashboard');

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