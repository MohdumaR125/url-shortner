<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\InvitationController;


Route::get('/s/{code}', [ShortUrlController::class, 'redirect']);
Route::post('/accept-invite', [InvitationController::class, 'accept']);



Route::middleware('auth:sanctum')->group(function () {

    Route::post('/short-urls', [ShortUrlController::class, 'store']);
    Route::get('/short-urls', [ShortUrlController::class, 'index']);

    // Invitations
    Route::post('/invite', [InvitationController::class, 'invite']);

    // for superadmin
    Route::get('/companies', [\App\Http\Controllers\CompanyController::class, 'index']);

    Route::get('/team-members', [\App\Http\Controllers\TeamController::class, 'index']);
});
