<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('/login', 'auth.login')
    ->name('login');

Route::view('/dashboard', 'dashboard')
    ->name('dashboard');