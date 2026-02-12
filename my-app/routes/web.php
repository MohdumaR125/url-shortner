<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('accept-invitation', function () {
    return Inertia::render('auth/AcceptInvitation', [
        'token' => request()->query('token'),
    ]);
})->name('accept-invitation');

require __DIR__ . '/settings.php';
