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